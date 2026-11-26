<?php
/* =====================================================================
   sw.vehiculo.php - Servicio de vehículos
   Acciones: insert, get, getPorCliente, update, delete
   ===================================================================== */
require_once "sw.comun.php";
require_once "Vehiculo.php";

$request = leerPeticion();
$datos = $request["data"][0] ?? [];

switch ($request["action"] ?? "") {
    case "insert":
        insertarVehiculo($datos);
        break;
    case "get":
        obtenerVehiculo($datos);
        break;
    case "getPorCliente":
        obtenerVehiculosCliente($datos);
        break;
    case "update":
        actualizarVehiculo($datos);
        break;
    case "delete":
        borrarVehiculo($datos);
        break;
    default:
        enviarRespuesta(false, "Accion no reconocida");
}

//Valida los datos del formulario del vehículo y devuelve la lista de errores
function validarVehiculo(array $datos): array {
    $errores = [];
    if (!validarMatricula($datos["matricula"])) {
        $errores[] = "La matrícula no es válida (ej: 1234BCD)";
    }
    if ($datos["marca"] == "" || $datos["modelo"] == "") {
        $errores[] = "La marca y el modelo son obligatorios";
    }
    if ($datos["anio"] !== "" && ($datos["anio"] < 1900 || $datos["anio"] > date("Y") + 1)) {
        $errores[] = "El año no es válido";
    }
    if ($datos["kilometros"] !== "" && $datos["kilometros"] < 0) {
        $errores[] = "Los kilómetros no pueden ser negativos";
    }
    return $errores;
}

//Limpia los datos que llegan del formulario
function limpiarVehiculo(array $datos): array {
    return [
        "matricula"   => strtoupper(str_replace([" ", "-"], "", $datos["matricula"] ?? "")),
        "marca"       => trim($datos["marca"] ?? ""),
        "modelo"      => trim($datos["modelo"] ?? ""),
        "anio"        => trim($datos["anio"] ?? ""),
        "color"       => trim($datos["color"] ?? ""),
        "kilometros"  => trim($datos["kilometros"] ?? ""),
        "combustible" => trim($datos["combustible"] ?? "")
    ];
}

function insertarVehiculo(array $datos): void {
    comprobarSesion();
    $v = limpiarVehiculo($datos);
    $errores = validarVehiculo($v);
    if (!empty($errores)) {
        enviarRespuesta(false, implode(". ", $errores));
    }

    $vehiculo = new Vehiculo(null, $datos["id_cliente"] ?? null, $v["matricula"], $v["marca"],
                             $v["modelo"], $v["anio"], $v["color"], $v["kilometros"], $v["combustible"]);
    if (!$vehiculo->insertVehiculo()) {
        enviarRespuesta(false, "No se pudo guardar el vehículo (¿la matrícula ya existe?)");
    }
    enviarRespuesta(true, "Vehículo guardado");
}

function obtenerVehiculo(array $datos): void {
    comprobarSesion();
    $vehiculo = new Vehiculo($datos["id_vehiculo"] ?? null);
    $resultado = $vehiculo->obtenerVehiculo();
    if (empty($resultado)) {
        enviarRespuesta(false, "No se encontró el vehículo");
    }
    enviarRespuesta(true, "", $resultado);
}

function obtenerVehiculosCliente(array $datos): void {
    comprobarSesion();
    $vehiculo = new Vehiculo(null, $datos["id_cliente"] ?? null);
    $resultado = $vehiculo->obtenerPorCliente();
    if ($resultado === false) {
        enviarRespuesta(false, "No se pudieron obtener los vehículos");
    }
    enviarRespuesta(true, "", $resultado); //puede ser un array vacío si no tiene vehículos
}

function actualizarVehiculo(array $datos): void {
    comprobarSesion();
    $v = limpiarVehiculo($datos);
    $errores = validarVehiculo($v);
    if (!empty($errores)) {
        enviarRespuesta(false, implode(". ", $errores));
    }

    $vehiculo = new Vehiculo($datos["id_vehiculo"] ?? null, null, $v["matricula"], $v["marca"],
                             $v["modelo"], $v["anio"], $v["color"], $v["kilometros"], $v["combustible"]);
    if (!$vehiculo->updateVehiculo()) {
        enviarRespuesta(false, "No se pudo actualizar el vehículo");
    }
    enviarRespuesta(true, "Vehículo actualizado");
}

function borrarVehiculo(array $datos): void {
    comprobarSesion();
    $vehiculo = new Vehiculo($datos["id_vehiculo"] ?? null);
    if (!$vehiculo->deleteVehiculo()) {
        enviarRespuesta(false, "No se pudo borrar el vehículo (¿tiene citas?)");
    }
    enviarRespuesta(true, "Vehículo borrado");
}