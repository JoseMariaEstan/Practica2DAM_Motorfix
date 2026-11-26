<?php
/* =====================================================================
   sw.pagina.php - Código común que usan todos los sw.*.php
   Cada sw.*.php hace require de este archivo en la primera línea, y con eso ya tiene:
   - Las clases del modelo
   - La sesión iniciada y la respuesta en JSON
   - $request con la petición, y $success, $msg y $data preparados
   - $esOficina y $esMecanico para comprobar permisos
   - Funciones para responder, comprobar la sesión y validar datos
   ===================================================================== */

//Las clases estan en la carpeta de arriba (modelo), por eso el /../
require_once __DIR__ . "/../Usuario.php";
require_once __DIR__ . "/../Cliente.php";
require_once __DIR__ . "/../Vehiculo.php";
require_once __DIR__ . "/../Cita.php";
require_once __DIR__ . "/../ManoObra.php";
require_once __DIR__ . "/../Repuesto.php";
require_once __DIR__ . "/../Factura.php";

//Arranca la sesion. PHP guarda los datos en el servidor y le da al navegador
//una cookie (PHPSESSID) para reconocerlo en las siguientes peticiones
session_start();

//Indicar que es una respuesta JSON, es decir, para que la web reconozca el archivo
header("Content-Type: application/json; charset=UTF-8");

//Zona horaria de España, para que time() y date() den la hora de aqui al comprobar las fechas de las citas
date_default_timezone_set("Europe/Madrid");

// Obtener el contenido del cuerpo de la petición
$contenidoJSON = file_get_contents('php://input');
// Usa true como segundo parametro para obtener un array asociativo en lugar de un objeto
$request = json_decode($contenidoJSON, true);

$success = true;
$msg = "";
$data = [];

//Si la peticion no trae accion, la dejamos vacia para que vaya al default
if (!isset($request["action"])) {
    $request["action"] = "";
}

//Oficina y admin pueden usar las acciones del panel de oficina
$esOficina = isset($_SESSION["rol"]) && ($_SESSION["rol"] == "oficina" || $_SESSION["rol"] == "admin");

//Las acciones del panel del mecanico solo las puede usar un mecanico
$esMecanico = isset($_SESSION["rol"]) && $_SESSION["rol"] == "mecanico";

/* ---------- Petición y respuesta ---------- */

//Lee el cuerpo de la petición y lo devuelve como array asociativo
//Si no llega nada o el JSON está mal, devuelve un array vacío
function leerPeticion(): array {
    $contenidoJSON = file_get_contents('php://input');
    $request = json_decode($contenidoJSON, true); //true -> array asociativo en lugar de objeto
    return is_array($request) ? $request : [];
}

//Envía la respuesta con el formato { data, success, msg } y termina el script
function enviarRespuesta(bool $success, string $msg, array $data = []): void {
    echo json_encode([
        "data"    => $data,
        "success" => $success,
        "msg"     => $msg
    ]);
    exit; //así no hace falta usar break ni arrastrar variables
}

/* ---------- Sesión ---------- */

//Corta la petición si no hay nadie logueado
//Si se le pasan roles, además comprueba que el usuario tenga uno de ellos
function comprobarSesion(array $roles = []): void {
    if (!isset($_SESSION["id_usuario"])) {
        enviarRespuesta(false, "Tienes que iniciar sesión");
    }
    if (!empty($roles) && !in_array($_SESSION["rol"], $roles)) {
        enviarRespuesta(false, "No tienes permiso para hacer esto");
    }
}

/* ---------- Validaciones ---------- */
//Validamos también en el servidor, porque el HTML se puede saltar fácilmente

function validarDni(string $dni): bool {
    return preg_match('/^[0-9]{8}[A-Z]$/', $dni) === 1;
}

function validarTelefono(string $telefono): bool {
    return preg_match('/^[0-9]{9}$/', $telefono) === 1;
}

function validarEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validarPassword(string $password): bool {
    return strlen($password) >= 8;
}

//Matrícula española actual: 4 números y 3 letras sin vocales (1234BCD)
function validarMatricula(string $matricula): bool {
    return preg_match('/^[0-9]{4}[BCDFGHJKLMNPRSTVWXYZ]{3}$/', $matricula) === 1;
}
