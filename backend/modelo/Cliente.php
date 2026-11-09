<?php
require_once __DIR__ . "/conexion.php";
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
    public function obtenerCliente() {
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