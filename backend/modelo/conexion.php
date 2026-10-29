<?php
class BD {
    private static $instance = null;
    private $conexion;

    private function __construct() {
        //Deberia poner esta informacion  en otro archivo por seguridad--Preguntar victor
        $host = "localhost";
        $db = "motorfix";
        $user = "root";
        $pass = "";

        $this->conexion = new PDO(
            "mysql:host=$host;dbname=$db;charset=utf8",
            $user,
            $pass
        );
        $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    private function __clone(){}
    public function __wakeup()
    {
        throw new Exception('No serializable');
    }


    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConexion() {
        return $this->conexion;
    }
}