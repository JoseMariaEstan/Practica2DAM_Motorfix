<?php
require_once __DIR__ . "/Conexion.php";

class Usuario {
    private $id_usuario;
    private $email;
    private $password;
    private $nombre;
    private $apellidos;
    private $rol;
    private $activo;

    //iniciar las variables con valor null, nos permite crear el objeto sin pasarle parametros
    public function __construct($id_usuario = null, $email = null, $password = null, $nombre = null,
                                $apellidos = null, $rol = 'cliente', $activo = 1) {
        $this->id_usuario = $id_usuario;
        $this->email      = $email;
        $this->password   = $password;
        $this->nombre     = $nombre;
        $this->apellidos  = $apellidos;
        $this->rol        = $rol;
        $this->activo     = $activo;
    }

    //Getters
    public function getIdUsuario(){
        return $this->id_usuario;
    }
    public function getEmail(){
        return $this->email;
    }
    public function getNombre(){
        return $this->nombre;
    }
    public function getApellidos(){
        return $this->apellidos;
    }
    public function getRol(){
        return $this->rol;
    }
    public function getActivo(){
        return $this->activo;
    }

//Settters
    public function setIdUsuario($id_usuario){
        $this->id_usuario = $id_usuario;
    }
    public function setEmail($email){
        $this->email = $email;
    }
    public function setPassword($password){
        $this->password = $password;
    }
    public function setNombre($nombre){
        $this->nombre = $nombre;
    }
    public function setApellidos($apellidos){
        $this->apellidos = $apellidos;
    }
    public function setRol($rol){
        $this->rol = $rol;
    }
    public function setActivo($activo){
        $this->activo = $activo;
    }

}