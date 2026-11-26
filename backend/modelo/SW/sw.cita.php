<?php
/* =====================================================================
   sw.cita.php - Servicio de citas (PLANTILLA)
   Todavía no existe Cita.php: cuando la hagas, descomenta el require
   y rellena cada función como en sw.vehiculo.php
   ===================================================================== */
require_once "sw.comun.php";
//require_once "Cita.php";

$request = leerPeticion();
$datos = $request["data"][0] ?? [];

switch ($request["action"] ?? "") {
    case "insert":
        insertarCita($datos);
        break;
    case "get":
        obtenerCita($datos);
        break;
    case "getPorVehiculo":
        obtenerCitasVehiculo($datos);
        break;
    case "update":
        actualizarCita($datos);
        break;
    case "delete":
        borrarCita($datos);
        break;
    default:
        enviarRespuesta(false, "Accion no reconocida");
}

function insertarCita(array $datos): void {
    comprobarSesion();
    //TODO: validar fecha/hora, crear new Cita(...) y llamar a insertCita()
    enviarRespuesta(false, "Todavía no implementado");
}

function obtenerCita(array $datos): void {
    comprobarSesion();
    //TODO: new Cita($datos["id_cita"]) y obtenerCita()
    enviarRespuesta(false, "Todavía no implementado");
}

function obtenerCitasVehiculo(array $datos): void {
    comprobarSesion();
    //TODO: new Cita(null, $datos["id_vehiculo"]) y obtenerPorVehiculo()
    enviarRespuesta(false, "Todavía no implementado");
}

function actualizarCita(array $datos): void {
    comprobarSesion();
    //TODO: validar, crear la Cita con su id y llamar a updateCita()
    enviarRespuesta(false, "Todavía no implementado");
}

function borrarCita(array $datos): void {
    comprobarSesion();
    //TODO: new Cita($datos["id_cita"]) y deleteCita()
    enviarRespuesta(false, "Todavía no implementado");
}