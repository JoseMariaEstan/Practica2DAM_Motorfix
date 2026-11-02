<?php
require_once __DIR__ . "/BD.php";
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
    //Getters
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

    //Setters
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
            $conexion = BD::getInstance()->getConexion();
            $sql = "INSERT INTO cliente (id_usuario, dni, nombre, apellidos, telefono, email, direccion, poblacion, codigo_postal)
                    VALUES (:id_usuario, :dni, :nombre, :apellidos, :telefono, :email, :direccion, :poblacion, :codigo_postal)";
            $stmt = $conexion->prepare($sql);

            $resultado = $stmt->execute([
                ':id_usuario'    => $this->id_usuario,   
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
            $conexion = BD::getInstance()->getConexion();
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
            $conexion = BD::getInstance()->getConexion();
            $sql = "SELECT * FROM cliente WHERE id_usuario = :id_usuario";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_usuario' => $this->id_usuario]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateCliente() {
        try {
            $conexion = BD::getInstance()->getConexion();
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
            $conexion = BD::getInstance()->getConexion();
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
//Añadir al rol de oficina la opcion de buscar cliente por dni?