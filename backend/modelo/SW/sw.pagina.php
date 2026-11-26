<?php
/* =====================================================================
   sw.comun.php - Código genérico que usan todos los sw.*.php
   - Respuesta JSON, leer la petición y devolver la respuesta
   - Control de sesión
   - Validaciones reutilizables
   ===================================================================== */

session_start();

//Indicar que es una respuesta JSON, es decir, para que la web reconozca el archivo
header("Content-Type: application/json; charset=UTF-8");

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