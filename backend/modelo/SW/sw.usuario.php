<?php
/* =====================================================================
   sw.usuario.php - Servicio web de usuarios
   Acciones: login, registro, sesion, logout y listarMecanicos (panel de oficina)
   La parte comun (sesion, leer la peticion, clases...) esta en sw.pagina.php
   ===================================================================== */
require_once __DIR__ . "/sw.pagina.php";

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

    default:
        $success = false;
        $msg = "Accion no reconocida";
        break;
}

//Se envia la respuesta con el formato { data, success, msg }
enviarRespuesta($success, $msg, $data);
