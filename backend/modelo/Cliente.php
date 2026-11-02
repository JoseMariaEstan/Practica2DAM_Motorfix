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

    public function insertCliente() {
        try {
            $conexion = Conexion::getInstancia()->getConexion();
            $sql = "INSERT INTO cliente (id_usuario, dni, nombre, apellidos, telefono, email, direccion, poblacion, codigo_postal)
                    VALUES (:id_usuario, :dni, :nombre, :apellidos, :telefono, :email, :direccion, :poblacion, :codigo_postal)";
            $stmt = $conexion->prepare($sql);

            $resultado = $stmt->execute([
                ':id_usuario'    => $this->id_usuario,   //null si lo da de alta oficina sin cuenta web
                ':dni'           => $this->dni,
                ':nombre'        => $this->nombre,
                ':apellidos'     => $this->apellidos,
                ':telefono'      => $this->telefono,
                ':email'         => $this->email,
                ':direccion'     => $this->direccion,
                ':poblacion'     => $this->poblacion,
                ':codigo_postal' => $this->codigo_postal
            ]);

            $this->id_cliente = $conexion->lastInsertId();
            return $resultado;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Devuelve el cliente por su id
    public function getCliente() {
        try {
            $conexion = Conexion::getInstancia()->getConexion();
            $sql = "SELECT * FROM cliente WHERE id_cliente = :id_cliente";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_cliente' => $this->id_cliente]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Devuelve la ficha del cliente que corresponde a un usuario (el que ha iniciado sesión)
    public function obtenerPorUsuario() {
        try {
            $conexion = Conexion::getInstancia()->getConexion();
            $sql = "SELECT * FROM cliente WHERE id_usuario = :id_usuario";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_usuario' => $this->id_usuario]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Busca un cliente por DNI (buscador de oficina y comprobar duplicados antes de insertar)
    public function obtenerPorDni() {
        try {
            $conexion = Conexion::getInstancia()->getConexion();
            $sql = "SELECT * FROM cliente WHERE dni = :dni";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':dni' => $this->dni]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateCliente() {
        try {
            $conexion = Conexion::getInstancia()->getConexion();
            $sql = "UPDATE cliente SET dni = :dni, nombre = :nombre, apellidos = :apellidos, telefono = :telefono,
                    email = :email, direccion = :direccion, poblacion = :poblacion, codigo_postal = :codigo_postal
                    WHERE id_cliente = :id_cliente";
            $stmt = $conexion->prepare($sql);

            return $stmt->execute([
                ':id_cliente'    => $this->id_cliente,
                ':dni'           => $this->dni,
                ':nombre'        => $this->nombre,
                ':apellidos'     => $this->apellidos,
                ':telefono'      => $this->telefono,
                ':email'         => $this->email,
                ':direccion'     => $this->direccion,
                ':poblacion'     => $this->poblacion,
                ':codigo_postal' => $this->codigo_postal
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Devuelve false si el cliente tiene vehículos (la FK tiene ON DELETE RESTRICT)
    public function deleteCliente() {
        try {
            $conexion = Conexion::getInstancia()->getConexion();
            $sql = "DELETE FROM cliente WHERE id_cliente = :id_cliente";
            $stmt = $conexion->prepare($sql);

            return $stmt->execute([':id_cliente' => $this->id_cliente]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
//TODO
//Deberia poner una funcion para el rol oficina que le permita obtener un listado de clientes?