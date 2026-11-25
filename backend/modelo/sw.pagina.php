<?php 
require_once __DIR__ . "/Usuario.php";
require_once __DIR__ . "/Cliente.php";
require_once __DIR__ . "/Vehiculo.php";
require_once __DIR__ . "/Cita.php";
require_once __DIR__ . "/ManoObra.php";
require_once __DIR__ . "/Repuesto.php";

//Arranca la sesion. PHP guarda los datos en el servidor y le da al navegador
//una cookie (PHPSESSID) para reconocerlo en las siguientes peticiones
session_start();

//Indicar que es una respuesta JSON, es decir, para que la web reconozca el archivo
header("Content-Type: application/json; charset=UTF-8");

//Zona horaria de España, para que time() y date() den la hora de aqui al comprobar las fechas de las citas
date_default_timezone_set("Europe/Madrid");

// Obtener el contenido del cuerpo de la petición
$contenidoJSON = file_get_contents('php://input');
// Usa true como segundo parametro para obtener un array asociativo en lugar de un objeto
$request = json_decode($contenidoJSON, true);

$success = true;
$msg = "";
$data = [];
$arrSalida = [];

//Si la peticion no trae accion, la dejamos vacia para que vaya al default
if (!isset($request["action"])) {
    $request["action"] = "";
}

//Oficina y admin pueden usar las acciones del panel de oficina
$esOficina = isset($_SESSION["rol"]) && ($_SESSION["rol"] == "oficina" || $_SESSION["rol"] == "admin");

//Las acciones del panel del mecanico solo las puede usar un mecanico
$esMecanico = isset($_SESSION["rol"]) && $_SESSION["rol"] == "mecanico";

switch ($request["action"]) {
    case "login":
        $email = $request["data"][0]["email"];
        $password = $request["data"][0]["password"];

        //Se busca el usuario por su email
        $usuarioA = new Usuario(null, $email);
        $resultado = $usuarioA->obtenerPorEmail();

        if ($resultado === false) {
            $success = false;
            $msg = "Error al consultar la base de datos";
        } else if (empty($resultado) || !$usuarioA->verificarPassword($password, $resultado[0]["password"])) {
            //Mismo mensaje si no existe el email o si la contraseña esta mal,
            //asi no se puede saber que emails estan registrados
            $success = false;
            $msg = "Email o contraseña incorrectos";
        } else {
            $usuario = $resultado[0];
            unset($usuario["password"]); //la contraseña no se devuelve nunca

            //Guardamos en la sesion quien ha entrado y con que rol
            $_SESSION["id_usuario"] = $usuario["id_usuario"];
            $_SESSION["nombre"] = $usuario["nombre"];
            $_SESSION["rol"] = $usuario["rol"];

            //Si es cliente guardamos tambien su id_cliente, que es el que usan sus vehiculos y citas
            if ($usuario["rol"] == "cliente") {
                $clienteA = new Cliente(null, $usuario["id_usuario"]);
                $_SESSION["id_cliente"] = $clienteA->obtenerPorUsuario()[0]["id_cliente"];
            }

            $data = [$usuario];
            $msg = "Bienvenido, " . $usuario["nombre"];
        }
        break;

    case "registro":
        //Se le pasan todos los datos del formulario de registro
        $usuario = $request["data"][0];
        $usuario["dni"] = strtoupper($usuario["dni"]);

        //Comprobaciones en el servidor (el JS se puede saltar)
        if (!preg_match('/^[0-9]{8}[A-Z]$/', $usuario["dni"])) {
            $success = false;
            $msg = "El DNI tiene que tener 8 números y una letra";
            break;
        }
        if (!filter_var($usuario["email"], FILTER_VALIDATE_EMAIL)) {
            $success = false;
            $msg = "El email no es válido";
            break;
        }
        if ($usuario["password"] != $usuario["password2"]) {
            $success = false;
            $msg = "Las contraseñas no coinciden";
            break;
        }

        //El email no puede estar ya registrado
        $usuarioA = new Usuario(null, $usuario["email"]);
        if (!empty($usuarioA->obtenerPorEmail())) {
            $success = false;
            $msg = "Ya existe una cuenta con ese email";
            break;
        }

        //1. Se crea el usuario (datos de acceso). La contraseña la cifra insertUsuario
        $usuarioA = new Usuario(null, $usuario["email"], $usuario["password"], $usuario["nombre"], $usuario["apellidos"], "cliente", 1);
        $success = $usuarioA->insertUsuario();
        if (!$success) {
            $msg = "No se pudo crear el usuario";
            break;
        }

        //2. Se crea su ficha de cliente con el id del usuario que se acaba de crear
        $clienteA = new Cliente(null, $usuarioA->getIdUsuario(), $usuario["dni"], $usuario["nombre"], $usuario["apellidos"], $usuario["telefono"], $usuario["email"]);
        $success = $clienteA->insertCliente();
        if (!$success) {
            //Si falla el cliente borramos el usuario, para que no quede un usuario sin ficha
            $usuarioA->deleteUsuario();
            $msg = "No se pudo crear el cliente (puede que el DNI ya esté registrado)";
            break;
        }

        //Como despues del registro entra directamente al area de cliente, iniciamos ya su sesion
        $_SESSION["id_usuario"] = $usuarioA->getIdUsuario();
        $_SESSION["nombre"] = $usuario["nombre"];
        $_SESSION["rol"] = "cliente";
        $_SESSION["id_cliente"] = $clienteA->getIdCliente();

        $data = [[
            "id_usuario" => $usuarioA->getIdUsuario(),
            "nombre" => $usuario["nombre"],
            "email" => $usuario["email"]
        ]];
        $msg = "Cuenta creada correctamente";
        break;

    case "sesion":
        //Lo llama cada panel al cargarse para saber si hay alguien dentro y quien es
        if (!isset($_SESSION["id_usuario"])) {
            $success = false;
            $msg = "No has iniciado sesión";
        } else {
            $data = [[
                "id_usuario" => $_SESSION["id_usuario"],
                "nombre" => $_SESSION["nombre"],
                "rol" => $_SESSION["rol"]
            ]];
        }
        break;

    case "logout":
        //Borra los datos de la sesion y la cierra
        $_SESSION = [];
        session_destroy();
        $msg = "Sesión cerrada";
        break;

    case "pedirCita":
        //Solo un cliente con la sesion iniciada puede pedir cita
        if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "cliente") {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $cita = $request["data"][0];

        //El vehiculo tiene que ser suyo: se compara con el id_cliente de la sesion,
        //no con uno que mande el navegador (eso se podria cambiar a mano)
        $vehiculoA = new Vehiculo($cita["id_vehiculo"]);
        $vehiculo = $vehiculoA->obtenerVehiculo();
        if (empty($vehiculo) || $vehiculo[0]["id_cliente"] != $_SESSION["id_cliente"]) {
            $success = false;
            $msg = "Ese vehículo no es tuyo";
            break;
        }

        if ($cita["fecha_cita"] == "" || $cita["motivo"] == "") {
            $success = false;
            $msg = "La fecha y el motivo son obligatorios";
            break;
        }

        //La fecha llega del formulario como "2026-11-20T09:30". strtotime la pasa a segundos
        //para poder compararla con la hora actual (time()). Si no es una fecha valida devuelve false
        $momentoCita = strtotime($cita["fecha_cita"]);
        if ($momentoCita === false) {
            $success = false;
            $msg = "La fecha no es válida";
            break;
        }
        if ($momentoCita <= time()) {
            $success = false;
            $msg = "La fecha de la cita tiene que ser posterior a hoy";
            break;
        }
        //Se guarda con el formato de MySQL: 2026-11-20 09:30:00
        $cita["fecha_cita"] = date("Y-m-d H:i:s", $momentoCita);

        $citaA = new Cita(null, $cita["id_vehiculo"], $cita["fecha_cita"], $cita["motivo"]);
        $success = $citaA->insertCita();
        $msg = $success ? "Cita solicitada. El taller te la confirmará" : "No se pudo pedir la cita";
        break;

    case "misCitas":
        //Solo un cliente con la sesion iniciada, y solo ve las suyas
        if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "cliente") {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $citaA = new Cita();
        $resultado = $citaA->obtenerPorCliente($_SESSION["id_cliente"]);
        if ($resultado === false) {
            $success = false;
            $msg = "Error al obtener las citas";
        } else {
            $data = $resultado;
            $msg = "Citas obtenidas correctamente";
        }
        break;

    case "cancelarCita":
        //Solo un cliente con la sesion iniciada puede cancelar sus citas
        if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "cliente") {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $citaA = new Cita($request["data"][0]["id_cita"]);
        $guardada = $citaA->obtenerCita();
        if (empty($guardada)) {
            $success = false;
            $msg = "Esa cita no existe";
            break;
        }

        //La cita no guarda el cliente: se mira de quien es el vehiculo de la cita
        $vehiculoA = new Vehiculo($guardada[0]["id_vehiculo"]);
        $vehiculo = $vehiculoA->obtenerVehiculo();
        if (empty($vehiculo) || $vehiculo[0]["id_cliente"] != $_SESSION["id_cliente"]) {
            $success = false;
            $msg = "Esa cita no es tuya";
            break;
        }

        //Solo se puede cancelar si el taller todavia no ha empezado a trabajar en el coche
        if ($guardada[0]["estado"] != "pendiente" && $guardada[0]["estado"] != "confirmada") {
            $success = false;
            $msg = "Esta cita ya no se puede cancelar";
            break;
        }

        //No se borra: se cambia el estado a cancelada para que quede en el historial
        $citaA = new Cita($request["data"][0]["id_cita"], null, null, null, "cancelada");
        $success = $citaA->updateEstado();
        $msg = $success ? "Cita cancelada correctamente" : "No se pudo cancelar la cita";
        break;

    case "misVehiculos":
        //Solo un cliente con la sesion iniciada, y solo ve los suyos
        if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "cliente") {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $vehiculoA = new Vehiculo(null, $_SESSION["id_cliente"]);
        $resultado = $vehiculoA->obtenerPorCliente();
        if ($resultado === false) {
            $success = false;
            $msg = "Error al obtener los vehículos";
        } else {
            $data = $resultado; //puede estar vacio si todavia no tiene vehiculos
            $msg = "Vehículos obtenidos correctamente";
        }
        break;

    case "insertVehiculo":
        if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "cliente") {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $vehiculo = $request["data"][0];
        //La matricula se guarda siempre igual: en mayusculas y sin espacios (1234 abc -> 1234ABC)
        $vehiculo["matricula"] = strtoupper(str_replace(" ", "", $vehiculo["matricula"]));

        if ($vehiculo["matricula"] == "" || $vehiculo["marca"] == "" || $vehiculo["modelo"] == "") {
            $success = false;
            $msg = "La matrícula, la marca y el modelo son obligatorios";
            break;
        }

        //El id_cliente sale de la sesion: el cliente solo puede añadir vehiculos a su nombre
        //Los campos opcionales vacios se guardan como null
        $vehiculoA = new Vehiculo(null, $_SESSION["id_cliente"], $vehiculo["matricula"], $vehiculo["marca"], $vehiculo["modelo"],
            $vehiculo["anio"] == "" ? null : $vehiculo["anio"],
            $vehiculo["color"] == "" ? null : $vehiculo["color"],
            $vehiculo["kilometros"] == "" ? null : $vehiculo["kilometros"],
            $vehiculo["combustible"] == "" ? null : $vehiculo["combustible"]);
        $success = $vehiculoA->insertVehiculo();
        //La matricula es UNIQUE en la tabla, si ya existe el insert falla
        $msg = $success ? "Vehículo añadido correctamente" : "No se pudo añadir el vehículo (puede que la matrícula ya esté registrada)";
        break;

    case "updateVehiculo":
        if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "cliente") {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $vehiculo = $request["data"][0];
        $vehiculo["matricula"] = strtoupper(str_replace(" ", "", $vehiculo["matricula"]));

        //Antes de modificar se comprueba que el vehiculo es del cliente de la sesion
        $vehiculoA = new Vehiculo($vehiculo["id_vehiculo"]);
        $guardado = $vehiculoA->obtenerVehiculo();
        if (empty($guardado) || $guardado[0]["id_cliente"] != $_SESSION["id_cliente"]) {
            $success = false;
            $msg = "Ese vehículo no es tuyo";
            break;
        }

        if ($vehiculo["matricula"] == "" || $vehiculo["marca"] == "" || $vehiculo["modelo"] == "") {
            $success = false;
            $msg = "La matrícula, la marca y el modelo son obligatorios";
            break;
        }

        $vehiculoA = new Vehiculo($vehiculo["id_vehiculo"], $_SESSION["id_cliente"], $vehiculo["matricula"], $vehiculo["marca"], $vehiculo["modelo"],
            $vehiculo["anio"] == "" ? null : $vehiculo["anio"],
            $vehiculo["color"] == "" ? null : $vehiculo["color"],
            $vehiculo["kilometros"] == "" ? null : $vehiculo["kilometros"],
            $vehiculo["combustible"] == "" ? null : $vehiculo["combustible"]);
        $success = $vehiculoA->updateVehiculo();
        $msg = $success ? "Vehículo actualizado correctamente" : "No se pudo actualizar el vehículo (puede que la matrícula ya esté registrada)";
        break;

    case "deleteVehiculo":
        if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "cliente") {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        //Antes de borrar se comprueba que el vehiculo es del cliente de la sesion
        $vehiculoA = new Vehiculo($request["data"][0]["id_vehiculo"]);
        $guardado = $vehiculoA->obtenerVehiculo();
        if (empty($guardado) || $guardado[0]["id_cliente"] != $_SESSION["id_cliente"]) {
            $success = false;
            $msg = "Ese vehículo no es tuyo";
            break;
        }

        $success = $vehiculoA->deleteVehiculo();
        //Si el vehiculo tiene citas no se puede borrar (la FK de cita tiene ON DELETE RESTRICT)
        $msg = $success ? "Vehículo borrado correctamente" : "No se puede borrar el vehículo porque tiene citas";
        break;

    //---------- PANEL DE OFICINA ----------
    case "citasOficina":
        //Oficina ve todas las citas del taller, con el cliente y el mecanico de cada una
        if (!$esOficina) {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $citaA = new Cita();
        $resultado = $citaA->obtenerTodas();
        if ($resultado === false) {
            $success = false;
            $msg = "Error al obtener las citas";
        } else {
            $data = $resultado;
            $msg = "Citas obtenidas correctamente";
        }
        break;

    case "listarMecanicos":
        //Para rellenar el desplegable de "Asignar mecanico"
        if (!$esOficina) {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $usuarioA = new Usuario();
        $resultado = $usuarioA->obtenerMecanicos();
        if ($resultado === false) {
            $success = false;
            $msg = "Error al obtener los mecánicos";
        } else {
            $data = $resultado;
            $msg = "Mecánicos obtenidos correctamente";
        }
        break;

    case "confirmarCita":
        //Oficina confirma la cita y le asigna el mecanico que la va a hacer.
        //Tambien sirve para cambiar de mecanico una cita que ya estaba confirmada
        if (!$esOficina) {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $id_cita = $request["data"][0]["id_cita"];
        $id_mecanico = $request["data"][0]["id_mecanico"];

        $citaA = new Cita($id_cita);
        $guardada = $citaA->obtenerCita();
        if (empty($guardada)) {
            $success = false;
            $msg = "Esa cita no existe";
            break;
        }
        if ($guardada[0]["estado"] != "pendiente" && $guardada[0]["estado"] != "confirmada") {
            $success = false;
            $msg = "Solo se pueden confirmar las citas pendientes o confirmadas";
            break;
        }

        //Se comprueba que el id que llega es de verdad un mecanico activo
        $usuarioA = new Usuario();
        $mecanicos = $usuarioA->obtenerMecanicos();
        $nombreMecanico = "";
        foreach ($mecanicos as $mecanico) {
            if ($mecanico["id_usuario"] == $id_mecanico) {
                $nombreMecanico = $mecanico["nombre"];
            }
        }
        if ($nombreMecanico == "") {
            $success = false;
            $msg = "Elige un mecánico válido";
            break;
        }

        //Primero se asigna el mecanico y despues se cambia el estado a confirmada
        $citaA = new Cita($id_cita, null, null, null, "confirmada", $id_mecanico);
        $success = $citaA->asignarMecanico() && $citaA->updateEstado();
        $msg = $success ? "Cita confirmada y asignada a " . $nombreMecanico : "No se pudo confirmar la cita";
        break;

    case "rechazarCita":
        //Oficina cancela una cita que no se puede atender (pendiente o confirmada)
        if (!$esOficina) {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $citaA = new Cita($request["data"][0]["id_cita"]);
        $guardada = $citaA->obtenerCita();
        if (empty($guardada)) {
            $success = false;
            $msg = "Esa cita no existe";
            break;
        }
        if ($guardada[0]["estado"] != "pendiente" && $guardada[0]["estado"] != "confirmada") {
            $success = false;
            $msg = "Esta cita ya no se puede cancelar";
            break;
        }

        $citaA = new Cita($request["data"][0]["id_cita"], null, null, null, "cancelada");
        $success = $citaA->updateEstado();
        $msg = $success ? "Cita cancelada correctamente" : "No se pudo cancelar la cita";
        break;

    default:
        $success = false;
        $msg = "Accion no reconocida";
        break;
}

$arrSalida = [
    "data" => $data,
    "success" => $success,
    "msg" => $msg
];

echo json_encode($arrSalida);