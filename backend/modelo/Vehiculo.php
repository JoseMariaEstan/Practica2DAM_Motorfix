<?php
require_once __DIR__ . "/BD.php";

class Vehiculo {
    private $id_vehiculo;
    private $id_cliente;
    private $matricula;
    private $marca;
    private $modelo;
    private $anio;
    private $color;
    private $kilometros;
    private $combustible;

    //iniciar las variables con valor null, nos permite crear el objeto sin pasarle parametros |VISTO EN CLASE|
    public function __construct($id_vehiculo = null, $id_cliente = null, $matricula = null, $marca = null,$modelo = null, $anio = null, $color = null, $kilometros = null, $combustible = null) {
        $this->id_vehiculo = $id_vehiculo;
        $this->id_cliente  = $id_cliente;
        $this->matricula   = $matricula;
        $this->marca       = $marca;
        $this->modelo      = $modelo;
        $this->anio        = $anio;
        $this->color       = $color;
        $this->kilometros  = $kilometros;
        $this->combustible = $combustible;
    }

    // Getters
    public function getIdVehiculo(){
        return $this->id_vehiculo;
    }
    public function getIdCliente(){
        return $this->id_cliente;
    }
    public function getMatricula(){
        return $this->matricula;
    }
    public function getMarca(){
        return $this->marca;
    }
    public function getModelo(){
        return $this->modelo;
    }
    public function getAnio(){
        return $this->anio;
    }
    public function getColor(){
        return $this->color;
    }
    public function getKilometros(){
        return $this->kilometros;
    }
    public function getCombustible(){
        return $this->combustible;
    }

    // Setters
    public function setIdVehiculo($id_vehiculo){
        $this->id_vehiculo = $id_vehiculo;
    }
    public function setIdCliente($id_cliente){
        $this->id_cliente = $id_cliente;
    }
    public function setMatricula($matricula){
        $this->matricula = $matricula;
    }
    public function setMarca($marca){
        $this->marca = $marca;
    }
    public function setModelo($modelo){
        $this->modelo = $modelo;
    }
    public function setAnio($anio){
        $this->anio = $anio;
    }
    public function setColor($color){
        $this->color = $color;
    }
    public function setKilometros($kilometros){
        $this->kilometros = $kilometros;
    }
    public function setCombustible($combustible){
        $this->combustible = $combustible;
    }
    
    public function insertVehiculo() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "INSERT INTO vehiculo (id_cliente, matricula, marca, modelo, anio, color, kilometros, combustible)
                    VALUES (:id_cliente, :matricula, :marca, :modelo, :anio, :color, :kilometros, :combustible)";
            $stmt = $conexion->prepare($sql);
 
            $resultado = $stmt->execute([
                ':id_cliente'  => $this->id_cliente,
                ':matricula'   => $this->matricula,
                ':marca'       => $this->marca,
                ':modelo'      => $this->modelo,
                ':anio'        => $this->anio,
                ':color'       => $this->color,
                ':kilometros'  => $this->kilometros,
                ':combustible' => $this->combustible
            ]);
 
            $this->id_vehiculo = $conexion->lastInsertId();
            return $resultado;
        } catch (PDOException $e) {
            return false;
        }
    }
    // Devuelve el vehículo por su id
    public function obtenerVehiculo() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "SELECT * FROM vehiculo WHERE id_vehiculo = :id_vehiculo";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_vehiculo' => $this->id_vehiculo]);
 
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
    // Devuelve todos los vehículos de un cliente
    public function obtenerPorCliente() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "SELECT * FROM vehiculo WHERE id_cliente = :id_cliente ORDER BY marca, modelo";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_cliente' => $this->id_cliente]);
 
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
    //Se modifican solo los datos estrictamente  del vehiculo
    public function updateVehiculo() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "UPDATE vehiculo SET matricula = :matricula, marca = :marca, modelo = :modelo, anio = :anio,
                    color = :color, kilometros = :kilometros, combustible = :combustible
                    WHERE id_vehiculo = :id_vehiculo";
            $stmt = $conexion->prepare($sql);
 
            return $stmt->execute([
                ':id_vehiculo' => $this->id_vehiculo,
                ':matricula'   => $this->matricula,
                ':marca'       => $this->marca,
                ':modelo'      => $this->modelo,
                ':anio'        => $this->anio,
                ':color'       => $this->color,
                ':kilometros'  => $this->kilometros,
                ':combustible' => $this->combustible
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
 
    // Devuelve false si el vehículo tiene citas
    public function deleteVehiculo() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "DELETE FROM vehiculo WHERE id_vehiculo = :id_vehiculo";
            $stmt = $conexion->prepare($sql);
 
            return $stmt->execute([':id_vehiculo' => $this->id_vehiculo]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
//TODO
//Buscador por matricula en el rol de oficina?