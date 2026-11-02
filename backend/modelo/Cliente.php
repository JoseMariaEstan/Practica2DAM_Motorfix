<?php
require_once __DIR__ . "/Conexion.php";

class Cliente {
    private $id_cliente;
    private $id_usuario;
    private $dni;
    private $nombre;
    private $apellidos;
    private $telefono;
    private $email;
    private $direccion;
    private $poblacion;
    private $codigo_postal;

    //iniciar las variables con valor null, nos permite crear el objeto sin pasarle parametros
    public function __construct($id_cliente = null, $id_usuario = null, $dni = null, $nombre = null,
                                $apellidos = null, $telefono = null, $email = null, $direccion = null,
                                $poblacion = null, $codigo_postal = null) {
        $this->id_cliente    = $id_cliente;
        $this->id_usuario    = $id_usuario;
        $this->dni           = $dni;
        $this->nombre        = $nombre;
        $this->apellidos     = $apellidos;
        $this->telefono      = $telefono;
        $this->email         = $email;
        $this->direccion     = $direccion;
        $this->poblacion     = $poblacion;
        $this->codigo_postal = $codigo_postal;
    }
    // ---------- GETTERS ----------
    public function getIdCliente(){
        return $this->id_cliente;
    }
    public function getIdUsuario(){
        return $this->id_usuario;
    }
    public function getDni(){
        return $this->dni;
    }
    public function getNombre(){
        return $this->nombre;
    }
    public function getApellidos(){
        return $this->apellidos;
    }
    public function getTelefono(){
        return $this->telefono;
    }
    public function getEmail(){
        return $this->email;
    }
    public function getDireccion(){
        return $this->direccion;
    }
    public function getPoblacion(){
        return $this->poblacion;
    }
    public function getCodigoPostal(){
        return $this->codigo_postal;
    }

    // ---------- SETTERS ----------
    public function setIdCliente($id_cliente){
        $this->id_cliente = $id_cliente;
    }
    public function setIdUsuario($id_usuario){
        $this->id_usuario = $id_usuario;
    }
    public function setDni($dni){
        $this->dni = $dni;
    }
    public function setNombre($nombre){
        $this->nombre = $nombre;
    }
    public function setApellidos($apellidos){
        $this->apellidos = $apellidos;
    }
    public function setTelefono($telefono){
        $this->telefono = $telefono;
    }
    public function setEmail($email){
        $this->email = $email;
    }
    public function setDireccion($direccion){
        $this->direccion = $direccion;
    }
    public function setPoblacion($poblacion){
        $this->poblacion = $poblacion;
    }
    public function setCodigoPostal($codigo_postal){
        $this->codigo_postal = $codigo_postal;
    }


}