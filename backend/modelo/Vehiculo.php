<?php
require_once __DIR__ . "/Conexion.php";

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
}