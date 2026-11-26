<?php
/* =====================================================================
   sw.vehiculo.php - Servicio web de vehículos
   Acciones: misVehiculos, insertVehiculo, updateVehiculo y deleteVehiculo (cliente)
   La parte comun (sesion, leer la peticion, clases...) esta en sw.pagina.php
   ===================================================================== */
require_once __DIR__ . "/sw.pagina.php";

switch ($request["action"]) {
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

    default:
        $success = false;
        $msg = "Accion no reconocida";
        break;
}

//Se envia la respuesta con el formato { data, success, msg }
enviarRespuesta($success, $msg, $data);
