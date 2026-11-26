<?php
/* =====================================================================
   sw.usuario.php - Servicio de usuarios
   Acciones: registro, login, logout, get, update, updateContra, delete
   ===================================================================== */
require_once "sw.comun.php";
require_once "Usuario.php";
require_once "Cliente.php";

$request = leerPeticion();
$datos = $request["data"][0] ?? [];

switch ($request["action"] ?? "") { //el ?? evita un warning si no llega ninguna accion
    case "registro":
        registrarUsuario($datos);
        break;
    case "login":
        loginUsuario($datos);
        break;
    case "logout":
        logoutUsuario();
        break;
    case "get":
        obtenerUsuario();
        break;
    case "update":
        actualizarUsuario($datos);
        break;
    case "updateContra":
        actualizarContra($datos);
        break;
    case "delete":
        borrarUsuario($datos);
        break;
    default:
        enviarRespuesta(false, "Accion no reconocida");
}

//Registro: crea el usuario y su cliente asociado
function registrarUsuario(array $datos): void {
    //Datos que llegan del formulario, ya limpios
    $nombre    = trim($datos["nombre"] ?? "");
    $apellidos = trim($datos["apellidos"] ?? "");
    $dni       = strtoupper(trim($datos["dni"] ?? ""));
    $telefono  = trim($datos["telefono"] ?? "");
    $email     = strtolower(trim($datos["email"] ?? ""));
    $password  = $datos["password"] ?? "";
    $password2 = $datos["password2"] ?? "";

    $errores = [];
    if ($nombre == "" || $apellidos == "") {
        $errores[] = "El nombre y los apellidos son obligatorios";
    }
    if (!validarDni($dni)) {
        $errores[] = "El DNI no tiene un formato válido (8 números y una letra)";
    }
    if (!validarTelefono($telefono)) {
        $errores[] = "El teléfono debe tener 9 números";
    }
    if (!validarEmail($email)) {
        $errores[] = "El email no es válido";
    }
    if (!validarPassword($password)) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres";
    }
    if ($password != $password2) {
        $errores[] = "Las contraseñas no coinciden";
    }
    if (!empty($errores)) {
        enviarRespuesta(false, implode(". ", $errores));
    }

    //Comprobamos que el email no este ya registrado
    $usuarioBuscar = new Usuario(null, $email);
    if (!empty($usuarioBuscar->getByEmail())) {
        enviarRespuesta(false, "Ya existe una cuenta con ese email");
    }

    //Transaccion: o se guardan el usuario y el cliente, o no se guarda ninguno
    $conexion = BD::getInstance()->getConexion();
    $conexion->beginTransaction();

    //1. Se crea el usuario (la contraseña la cifra insertUsuario)
    $usuarioA = new Usuario(null, $email, $password, $nombre, $apellidos, "cliente", 1);
    if (!$usuarioA->insertUsuario()) {
        $conexion->rollBack();
        enviarRespuesta(false, "No se pudo crear el usuario");
    }

    //2. Se crea el cliente con el id del usuario que se acaba de crear
    $clienteA = new Cliente(null, $usuarioA->getIdUsuario(), $dni, $nombre, $apellidos, $telefono, $email);
    if (!$clienteA->insertCliente()) {
        $conexion->rollBack();
        enviarRespuesta(false, "No se pudo crear el cliente (¿el DNI ya está registrado?)");
    }

    $conexion->commit();

    //Nunca devolvemos la contraseña
    enviarRespuesta(true, "Cuenta creada correctamente", [[
        "id_usuario" => $usuarioA->getIdUsuario(),
        "id_cliente" => $clienteA->getIdCliente(),
        "nombre"     => $nombre,
        "email"      => $email
    ]]);
}

//Login: comprueba email y contraseña y guarda el usuario en la sesión
function loginUsuario(array $datos): void {
    $email    = strtolower(trim($datos["email"] ?? ""));
    $password = $datos["password"] ?? "";

    $usuario = new Usuario(null, $email);
    $resultado = $usuario->getByEmail();

    //Mismo mensaje si no existe el email o si la contraseña está mal (no damos pistas)
    if (empty($resultado) || !$usuario->verificarPassword($password, $resultado[0]["password"])) {
        enviarRespuesta(false, "Email o contraseña incorrectos");
    }

    $fila = $resultado[0];
    $_SESSION["id_usuario"] = $fila["id_usuario"];
    $_SESSION["rol"]        = $fila["rol"];
    $_SESSION["nombre"]     = $fila["nombre"];

    enviarRespuesta(true, "Bienvenido, " . $fila["nombre"], [[
        "id_usuario" => $fila["id_usuario"],
        "nombre"     => $fila["nombre"],
        "rol"        => $fila["rol"]
    ]]);
}

function logoutUsuario(): void {
    session_destroy();
    enviarRespuesta(true, "Sesión cerrada");
}

//Devuelve los datos del usuario logueado
function obtenerUsuario(): void {
    comprobarSesion();
    $usuario = new Usuario($_SESSION["id_usuario"]);
    $resultado = $usuario->obtenerUsuario();
    if ($resultado === false) {
        enviarRespuesta(false, "No se pudo obtener el usuario");
    }
    enviarRespuesta(true, "", $resultado);
}

function actualizarUsuario(array $datos): void {
    comprobarSesion(["admin"]);
    $email = strtolower(trim($datos["email"] ?? ""));
    if (!validarEmail($email)) {
        enviarRespuesta(false, "El email no es válido");
    }
    $usuario = new Usuario($datos["id_usuario"] ?? null, $email, null,
                           trim($datos["nombre"] ?? ""), trim($datos["apellidos"] ?? ""),
                           $datos["rol"] ?? "cliente", $datos["activo"] ?? 1);
    if (!$usuario->updateUsuario()) {
        enviarRespuesta(false, "No se pudo actualizar el usuario");
    }
    enviarRespuesta(true, "Usuario actualizado");
}

//Cambia la contraseña del usuario logueado
function actualizarContra(array $datos): void {
    comprobarSesion();
    $password  = $datos["password"] ?? "";
    $password2 = $datos["password2"] ?? "";
    if (!validarPassword($password)) {
        enviarRespuesta(false, "La contraseña debe tener al menos 8 caracteres");
    }
    if ($password != $password2) {
        enviarRespuesta(false, "Las contraseñas no coinciden");
    }
    $usuario = new Usuario($_SESSION["id_usuario"], null, $password);
    if (!$usuario->updateContra()) {
        enviarRespuesta(false, "No se pudo cambiar la contraseña");
    }
    enviarRespuesta(true, "Contraseña cambiada");
}

function borrarUsuario(array $datos): void {
    comprobarSesion(["admin"]);
    $usuario = new Usuario($datos["id_usuario"] ?? null);
    if (!$usuario->deleteUsuario()) {
        enviarRespuesta(false, "No se pudo borrar el usuario");
    }
    enviarRespuesta(true, "Usuario borrado");
}