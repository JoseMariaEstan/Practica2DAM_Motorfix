<?php
/* =====================================================================
   sw.cliente.php - Servicio de clientes
   Acciones: get, getMio, update, delete
   (el insert se hace en el registro de sw.usuario.php)
   ===================================================================== */
require_once __DIR__ . "/sw.pagina.php";

$request = leerPeticion();
$datos = $request["data"][0] ?? [];

switch ($request["action"] ?? "") {
    case "get":
        obtenerCliente($datos);
        break;
    case "getMio":
        obtenerMiCliente();
        break;
    case "update":
        actualizarCliente($datos);
        break;
    case "delete":
        borrarCliente($datos);
        break;
    default:
        enviarRespuesta(false, "Accion no reconocida");
}

//Un cliente por su id (para oficina/admin)
function obtenerCliente(array $datos): void {
    comprobarSesion(["oficina", "admin"]);
    $cliente = new Cliente($datos["id_cliente"] ?? null);
    $resultado = $cliente->obtenerCliente();
    if (empty($resultado)) {
        enviarRespuesta(false, "No se encontró el cliente");
    }
    enviarRespuesta(true, "", $resultado);
}

//El cliente del usuario que ha iniciado sesión
function obtenerMiCliente(): void {
    comprobarSesion();
    $cliente = new Cliente(null, $_SESSION["id_usuario"]);
    $resultado = $cliente->obtenerPorUsuario();
    if (empty($resultado)) {
        enviarRespuesta(false, "No se encontró el cliente");
    }
    enviarRespuesta(true, "", $resultado);
}

function actualizarCliente(array $datos): void {
    comprobarSesion();
    $dni      = strtoupper(trim($datos["dni"] ?? ""));
    $telefono = trim($datos["telefono"] ?? "");
    $email    = strtolower(trim($datos["email"] ?? ""));

    $errores = [];
    if (!validarDni($dni)) {
        $errores[] = "El DNI no tiene un formato válido";
    }
    if (!validarTelefono($telefono)) {
        $errores[] = "El teléfono debe tener 9 números";
    }
    if (!validarEmail($email)) {
        $errores[] = "El email no es válido";
    }
    if (!empty($errores)) {
        enviarRespuesta(false, implode(". ", $errores));
    }

    $cliente = new Cliente($datos["id_cliente"] ?? null, null, $dni,
                           trim($datos["nombre"] ?? ""), trim($datos["apellidos"] ?? ""),
                           $telefono, $email,
                           trim($datos["direccion"] ?? ""), trim($datos["poblacion"] ?? ""),
                           trim($datos["codigo_postal"] ?? ""));
    if (!$cliente->updateCliente()) {
        enviarRespuesta(false, "No se pudo actualizar el cliente");
    }
    enviarRespuesta(true, "Cliente actualizado");
}

function borrarCliente(array $datos): void {
    comprobarSesion(["oficina", "admin"]);
    $cliente = new Cliente($datos["id_cliente"] ?? null);
    if (!$cliente->deleteCliente()) {
        //La FK tiene ON DELETE RESTRICT
        enviarRespuesta(false, "No se pudo borrar el cliente (¿tiene vehículos?)");
    }
    enviarRespuesta(true, "Cliente borrado");
}