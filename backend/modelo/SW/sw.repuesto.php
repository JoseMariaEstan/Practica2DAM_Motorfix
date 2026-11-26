<?php
/* =====================================================================
   sw.repuesto.php - Servicio web de repuestos
   Acciones: imputarRepuesto y borrarRepuesto (mecánico)
   La parte comun (sesion, leer la peticion, clases...) esta en sw.pagina.php
   ===================================================================== */
require_once __DIR__ . "/sw.pagina.php";

switch ($request["action"]) {
    case "imputarRepuesto":
        //Añade un repuesto a una cita que el mecanico tiene en reparacion
        if (!$esMecanico) {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $linea = $request["data"][0];
        $citaA = new Cita($linea["id_cita"]);
        $guardada = $citaA->obtenerCita();
        if (empty($guardada) || $guardada[0]["id_mecanico"] != $_SESSION["id_usuario"]) {
            $success = false;
            $msg = "Esa cita no está asignada a ti";
            break;
        }
        if ($guardada[0]["estado"] != "en_reparacion") {
            $success = false;
            $msg = "Solo se pueden imputar repuestos a una cita en reparación";
            break;
        }
        if ($linea["descripcion"] == "" || $linea["cantidad"] == "" || $linea["cantidad"] <= 0
            || $linea["precio_unitario"] == "" || $linea["precio_unitario"] < 0) {
            $success = false;
            $msg = "Escribe la pieza, una cantidad mayor que 0 y el precio";
            break;
        }

        $repuestoA = new Repuesto(null, $linea["id_cita"], $linea["descripcion"], $linea["cantidad"], $linea["precio_unitario"]);
        $success = $repuestoA->insertRepuesto();
        $msg = $success ? "Repuesto añadido correctamente" : "No se pudo añadir el repuesto";
        break;

    case "borrarRepuesto":
        //Por si el mecanico se equivoca al imputar: borra un repuesto
        if (!$esMecanico) {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $repuestoA = new Repuesto($request["data"][0]["id_repuesto"]);
        $lineaGuardada = $repuestoA->obtenerRepuesto();
        if (empty($lineaGuardada)) {
            $success = false;
            $msg = "Ese repuesto no existe";
            break;
        }

        $citaA = new Cita($lineaGuardada[0]["id_cita"]);
        $guardada = $citaA->obtenerCita();
        if ($guardada[0]["id_mecanico"] != $_SESSION["id_usuario"] || $guardada[0]["estado"] != "en_reparacion") {
            $success = false;
            $msg = "Solo puedes borrar líneas de tus citas en reparación";
            break;
        }

        $success = $repuestoA->deleteRepuesto();
        $msg = $success ? "Repuesto borrado correctamente" : "No se pudo borrar el repuesto";
        break;

    default:
        $success = false;
        $msg = "Accion no reconocida";
        break;
}

//Se envia la respuesta con el formato { data, success, msg }
enviarRespuesta($success, $msg, $data);
