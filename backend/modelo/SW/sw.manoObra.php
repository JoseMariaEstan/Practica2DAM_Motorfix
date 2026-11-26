<?php
/* =====================================================================
   sw.manoObra.php - Servicio web de mano de obra
   Acciones: imputarHoras y borrarHoras (mecánico)
   La parte comun (sesion, leer la peticion, clases...) esta en sw.pagina.php
   ===================================================================== */
require_once __DIR__ . "/sw.pagina.php";

switch ($request["action"]) {
    case "imputarHoras":
        //Añade una linea de mano de obra a una cita que el mecanico tiene en reparacion
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
            $msg = "Solo se pueden imputar horas a una cita en reparación";
            break;
        }
        if ($linea["descripcion"] == "" || $linea["horas"] == "" || $linea["horas"] <= 0) {
            $success = false;
            $msg = "Escribe el trabajo hecho y un número de horas mayor que 0";
            break;
        }

        //El precio por hora no lo manda el navegador: se usa el del taller (45 por defecto en la clase)
        $manoObraA = new ManoObra(null, $linea["id_cita"], $linea["descripcion"], $linea["horas"]);
        $success = $manoObraA->insertManoObra();
        $msg = $success ? "Horas imputadas correctamente" : "No se pudieron imputar las horas";
        break;

    case "borrarHoras":
        //Por si el mecanico se equivoca al imputar: borra una linea de horas
        if (!$esMecanico) {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $manoObraA = new ManoObra($request["data"][0]["id_mano_obra"]);
        $lineaGuardada = $manoObraA->obtenerManoObra();
        if (empty($lineaGuardada)) {
            $success = false;
            $msg = "Esa línea no existe";
            break;
        }

        //La linea tiene que ser de una cita suya y que siga en reparacion
        $citaA = new Cita($lineaGuardada[0]["id_cita"]);
        $guardada = $citaA->obtenerCita();
        if ($guardada[0]["id_mecanico"] != $_SESSION["id_usuario"] || $guardada[0]["estado"] != "en_reparacion") {
            $success = false;
            $msg = "Solo puedes borrar líneas de tus citas en reparación";
            break;
        }

        $success = $manoObraA->deleteManoObra();
        $msg = $success ? "Horas borradas correctamente" : "No se pudieron borrar las horas";
        break;

    default:
        $success = false;
        $msg = "Accion no reconocida";
        break;
}

//Se envia la respuesta con el formato { data, success, msg }
enviarRespuesta($success, $msg, $data);
