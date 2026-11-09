<?php 
require_once __DIR__ . "/Usuario.php";
require_once __DIR__ . "/Cliente.php";
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

        $data = [[
            "id_usuario" => $usuarioA->getIdUsuario(),
            "nombre" => $usuario["nombre"],
            "email" => $usuario["email"]
        ]];
        $msg = "Cuenta creada correctamente";
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