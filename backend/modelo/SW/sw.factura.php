<?php
/* =====================================================================
   sw.factura.php - Servicio de facturas (PLANTILLA)
   Todavía no existe Factura.php: cuando la hagas, descomenta el require
   y rellena cada función como en sw.vehiculo.php
   ===================================================================== */
require_once "sw.comun.php";
//require_once "Factura.php";

$request = leerPeticion();
$datos = $request["data"][0] ?? [];

switch ($request["action"] ?? "") {
    case "insert":
        insertarFactura($datos);
        break;
    case "get":
        obtenerFactura($datos);
        break;
    case "getPorCliente":
        obtenerFacturasCliente($datos);
        break;
    case "update":
        actualizarFactura($datos);
        break;
    case "delete":
        borrarFactura($datos);
        break;
    default:
        enviarRespuesta(false, "Accion no reconocida");
}

//Las facturas solo las crea/modifica/borra la oficina o el admin
function insertarFactura(array $datos): void {
    comprobarSesion(["oficina", "admin"]);
    //TODO: validar importes, crear new Factura(...) y llamar a insertFactura()
    enviarRespuesta(false, "Todavía no implementado");
}

function obtenerFactura(array $datos): void {
    comprobarSesion();
    //TODO: new Factura($datos["id_factura"]) y obtenerFactura()
    enviarRespuesta(false, "Todavía no implementado");
}

function obtenerFacturasCliente(array $datos): void {
    comprobarSesion();
    //TODO: new Factura(null, $datos["id_cliente"]) y obtenerPorCliente()
    enviarRespuesta(false, "Todavía no implementado");
}

function actualizarFactura(array $datos): void {
    comprobarSesion(["oficina", "admin"]);
    //TODO: validar, crear la Factura con su id y llamar a updateFactura()
    enviarRespuesta(false, "Todavía no implementado");
}

function borrarFactura(array $datos): void {
    comprobarSesion(["oficina", "admin"]);
    //TODO: new Factura($datos["id_factura"]) y deleteFactura()
    enviarRespuesta(false, "Todavía no implementado");
}