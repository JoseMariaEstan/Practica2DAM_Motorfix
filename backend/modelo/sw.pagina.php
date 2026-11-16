<?php 
require_once __DIR__ . "/Usuario.php";
require_once __DIR__ . "/Cliente.php";
require_once __DIR__ . "/Vehiculo.php";
require_once __DIR__ . "/Cita.php";

//Arranca la sesion. PHP guarda los datos en el servidor y le da al navegador
//una cookie (PHPSESSID) para reconocerlo en las siguientes peticiones
session_start();

//Indicar que es una respuesta JSON, es decir, para que la web reconozca el archivo
header("Content-Type: application/json; charset=UTF-8");

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
            $msg = "Las contraseñas no