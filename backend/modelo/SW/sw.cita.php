<?php
/* =====================================================================
   sw.cita.php - Servicio web de citas
   Acciones: cliente: pedirCita, misCitas, cancelarCita
   oficina: citasOficina, confirmarCita, rechazarCita
   mecánico: misTrabajos, detalleTrabajo, cambiarEstadoTrabajo
   La parte comun (sesion, leer la peticion, clases...) esta en sw.pagina.php
   ===================================================================== */
require_once __DIR__ . "/sw.pagina.php";

switch ($request["action"]) {
    case "pedirCita":
        //Solo un cliente con la sesion iniciada puede pedir cita
        if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "cliente") {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $cita = $request["data"][0];

        //El vehiculo tiene que ser suyo: se compara con el id_cliente de la sesion,
        //no con uno que mande el navegador (eso se podria cambiar a mano)
        $vehiculoA = new Vehiculo($cita["id_vehiculo"]);
        $vehiculo = $vehiculoA->obtenerVehiculo();
        if (empty($vehiculo) || $vehiculo[0]["id_cliente"] != $_SESSION["id_cliente"]) {
            $success = false;
            $msg = "Ese vehículo no es tuyo";
            break;
        }

        if ($cita["fecha_cita"] == "" || $cita["motivo"] == "") {
            $success = false;
            $msg = "La fecha y el motivo son obligatorios";
            break;
        }

        //La fecha llega del formulario como "2026-11-20T09:30". strtotime la pasa a segundos
        //para poder compararla con la hora actual (time()). Si no es una fecha valida devuelve false
        $momentoCita = strtotime($cita["fecha_cita"]);
        if ($momentoCita === false) {
            $success = false;
            $msg = "La fecha no es válida";
            break;
        }
        if ($momentoCita <= time()) {
            $success = false;
            $msg = "La fecha de la cita tiene que ser posterior a hoy";
            break;
        }
        //Se guarda con el formato de MySQL: 2026-11-20 09:30:00
        $cita["fecha_cita"] = date("Y-m-d H:i:s", $momentoCita);

        $citaA = new Cita(null, $cita["id_vehiculo"], $cita["fecha_cita"], $cita["motivo"]);
        $success = $citaA->insertCita();
        $msg = $success ? "Cita solicitada. El taller te la confirmará" : "No se pudo pedir la cita";
        break;

    case "misCitas":
        //Solo un cliente con la sesion iniciada, y solo ve las suyas
        if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "cliente") {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $citaA = new Cita();
        $resultado = $citaA->obtenerPorCliente($_SESSION["id_cliente"]);
        if ($resultado === false) {
            $success = false;
            $msg = "Error al obtener las citas";
        } else {
            $data = $resultado;
            $msg = "Citas obtenidas correctamente";
        }
        break;

    case "cancelarCita":
        //Solo un cliente con la sesion iniciada puede cancelar sus citas
        if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "cliente") {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $citaA = new Cita($request["data"][0]["id_cita"]);
        $guardada = $citaA->obtenerCita();
        if (empty($guardada)) {
            $success = false;
            $msg = "Esa cita no existe";
            break;
        }

        //La cita no guarda el cliente: se mira de quien es el vehiculo de la cita
        $vehiculoA = new Vehiculo($guardada[0]["id_vehiculo"]);
        $vehiculo = $vehiculoA->obtenerVehiculo();
        if (empty($vehiculo) || $vehiculo[0]["id_cliente"] != $_SESSION["id_cliente"]) {
            $success = false;
            $msg = "Esa cita no es tuya";
            break;
        }

        //Solo se puede cancelar si el taller todavia no ha empezado a trabajar en el coche
        if ($guardada[0]["estado"] != "pendiente" && $guardada[0]["estado"] != "confirmada") {
            $success = false;
            $msg = "Esta cita ya no se puede cancelar";
            break;
        }

        //No se borra: se cambia el estado a cancelada para que quede en el historial
        $citaA = new Cita($request["data"][0]["id_cita"], null, null, null, "cancelada");
        $success = $citaA->updateEstado();
        $msg = $success ? "Cita cancelada correctamente" : "No se pudo cancelar la cita";
        break;

    case "citasOficina":
        //Oficina ve todas las citas del taller, con el cliente y el mecanico de cada una
        if (!$esOficina) {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $citaA = new Cita();
        $resultado = $citaA->obtenerTodas();
        if ($resultado === false) {
            $success = false;
            $msg = "Error al obtener las citas";
        } else {
            $data = $resultado;
            $msg = "Citas obtenidas correctamente";
        }
        break;

    case "confirmarCita":
        //Oficina confirma la cita y le asigna el mecanico que la va a hacer.
        //Tambien sirve para cambiar de mecanico una cita que ya estaba confirmada
        if (!$esOficina) {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $id_cita = $request["data"][0]["id_cita"];
        $id_mecanico = $request["data"][0]["id_mecanico"];

        $citaA = new Cita($id_cita);
        $guardada = $citaA->obtenerCita();
        if (empty($guardada)) {
            $success = false;
            $msg = "Esa cita no existe";
            break;
        }
        if ($guardada[0]["estado"] != "pendiente" && $guardada[0]["estado"] != "confirmada") {
            $success = false;
            $msg = "Solo se pueden confirmar las citas pendientes o confirmadas";
            break;
        }

        //Se comprueba que el id que llega es de verdad un mecanico activo
        $usuarioA = new Usuario();
        $mecanicos = $usuarioA->obtenerMecanicos();
        $nombreMecanico = "";
        foreach ($mecanicos as $mecanico) {
            if ($mecanico["id_usuario"] == $id_mecanico) {
                $nombreMecanico = $mecanico["nombre"];
            }
        }
        if ($nombreMecanico == "") {
            $success = false;
            $msg = "Elige un mecánico válido";
            break;
        }

        //Primero se asigna el mecanico y despues se cambia el estado a confirmada
        $citaA = new Cita($id_cita, null, null, null, "confirmada", $id_mecanico);
        $success = $citaA->asignarMecanico() && $citaA->updateEstado();
        $msg = $success ? "Cita confirmada y asignada a " . $nombreMecanico : "No se pudo confirmar la cita";
        break;

    case "rechazarCita":
        //Oficina cancela una cita que no se puede atender (pendiente o confirmada)
        if (!$esOficina) {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $citaA = new Cita($request["data"][0]["id_cita"]);
        $guardada = $citaA->obtenerCita();
        if (empty($guardada)) {
            $success = false;
            $msg = "Esa cita no existe";
            break;
        }
        if ($guardada[0]["estado"] != "pendiente" && $guardada[0]["estado"] != "confirmada") {
            $success = false;
            $msg = "Esta cita ya no se puede cancelar";
            break;
        }

        $citaA = new Cita($request["data"][0]["id_cita"], null, null, null, "cancelada");
        $success = $citaA->updateEstado();
        $msg = $success ? "Cita cancelada correctamente" : "No se pudo cancelar la cita";
        break;

    case "misTrabajos":
        //Hoja de trabajo: las citas que oficina le ha asignado al mecanico de la sesion
        if (!$esMecanico) {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $citaA = new Cita(null, null, null, null, null, $_SESSION["id_usuario"]);
        $resultado = $citaA->obtenerPorMecanico();
        if ($resultado === false) {
            $success = false;
            $msg = "Error al obtener los trabajos";
        } else {
            $data = $resultado;
            $msg = "Trabajos obtenidos correctamente";
        }
        break;

    case "detalleTrabajo":
        //Devuelve las horas y los repuestos que lleva imputados una cita del mecanico
        if (!$esMecanico) {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $id_cita = $request["data"][0]["id_cita"];
        $citaA = new Cita($id_cita);
        $guardada = $citaA->obtenerCita();
        if (empty($guardada) || $guardada[0]["id_mecanico"] != $_SESSION["id_usuario"]) {
            $success = false;
            $msg = "Esa cita no está asignada a ti";
            break;
        }

        $manoObraA = new ManoObra(null, $id_cita);
        $repuestoA = new Repuesto(null, $id_cita);
        //Se devuelve un solo objeto con las dos listas
        $data = [[
            "horas" => $manoObraA->obtenerPorCita(),
            "repuestos" => $repuestoA->obtenerPorCita()
        ]];
        $msg = "Detalle obtenido correctamente";
        break;

    case "cambiarEstadoTrabajo":
        //El mecanico empieza la reparacion (confirmada -> en_reparacion)
        //o la termina (en_reparacion -> finalizada)
        if (!$esMecanico) {
            $success = false;
            $msg = "No tienes permiso para hacer esto";
            break;
        }

        $id_cita = $request["data"][0]["id_cita"];
        $estadoNuevo = $request["data"][0]["estado"];

        $citaA = new Cita($id_cita);
        $guardada = $citaA->obtenerCita();
        if (empty($guardada) || $guardada[0]["id_mecanico"] != $_SESSION["id_usuario"]) {
            $success = false;
            $msg = "Esa cita no está asignada a ti";
            break;
        }

        //Solo se permiten estos dos pasos, en este orden
        $estadoActual = $guardada[0]["estado"];
        $pasoValido = ($estadoActual == "confirmada" && $estadoNuevo == "en_reparacion")
                   || ($estadoActual == "en_reparacion" && $estadoNuevo == "finalizada");
        if (!$pasoValido) {
            $success = false;
            $msg = "No se puede pasar de " . $estadoActual . " a " . $estadoNuevo;
            break;
        }

        //Para finalizar tiene que haber imputado algo, si no la factura saldria a 0
        if ($estadoNuevo == "finalizada") {
            $manoObraA = new ManoObra(null, $id_cita);
            $repuestoA = new Repuesto(null, $id_cita);
            if (empty($manoObraA->obtenerPorCita()) && empty($repuestoA->obtenerPorCita())) {
                $success = false;
                $msg = "Imputa las horas o los repuestos antes de finalizar la reparación";
                break;
            }
        }

        $citaA = new Cita($id_cita, null, null, null, $estadoNuevo);
        $success = $citaA->updateEstado();
        $msg = $success ? "Estado cambiado correctamente" : "No se pudo cambiar el estado";
        break;

    default:
        $success = false;
        $msg = "Accion no reconocida";
        break;
}

//Se envia la respuesta con el formato { data, success, msg }
enviarRespuesta($success, $msg, $data);
