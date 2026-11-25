<?php
require_once __DIR__ . "/conexion.php";

//Cada repuesto es una pieza o material que se ha usado en una cita:
//que pieza, cuantas unidades y a que precio la unidad
class Repuesto {
    private $id_repuesto;
    private $id_cita;
    private $descripcion;
    private $cantidad;
    private $precio_unitario;

    //iniciar las variables con valor null, nos permite crear el objeto sin pasarle parametros
    public function __construct($id_repuesto = null, $id_cita = null, $descripcion = null,
                                $cantidad = 1, $precio_unitario = null) {
        $this->id_repuesto     = $id_repuesto;
        $this->id_cita         = $id_cita;
        $this->descripcion     = $descripcion;
        $this->cantidad        = $cantidad;
        $this->precio_unitario = $precio_unitario;
    }

    public function insertRepuesto() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "INSERT INTO repuesto (id_cita, descripcion, cantidad, precio_unitario)
                    VALUES (:id_cita, :descripcion, :cantidad, :precio_unitario)";
            $stmt = $conexion->prepare($sql);

            $resultado = $stmt->execute([
                ':id_cita'         => $this->id_cita,
                ':descripcion'     => $this->descripcion,
                ':cantidad'        => $this->cantidad,
                ':precio_unitario' => $this->precio_unitario
            ]);

            $this->id_repuesto = $conexion->lastInsertId();
            return $resultado;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Devuelve un repuesto por su id
    public function obtenerRepuesto() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "SELECT * FROM repuesto WHERE id_repuesto = :id_repuesto";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_repuesto' => $this->id_repuesto]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Devuelve todos los repuestos usados en una cita
    public function obtenerPorCita() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "SELECT * FROM repuesto WHERE id_cita = :id_cita ORDER BY id_repuesto";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_cita' => $this->id_cita]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateRepuesto() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "UPDATE repuesto SET descripcion = :descripcion, cantidad = :cantidad, precio_unitario = :precio_unitario
                    WHERE id_repuesto = :id_repuesto";
            $stmt = $conexion->prepare($sql);

            return $stmt->execute([
                ':id_repuesto'     => $this->id_repuesto,
                ':descripcion'     => $this->descripcion,
                ':cantidad'        => $this->cantidad,
                ':precio_unitario' => $this->precio_unitario
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteRepuesto() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "DELETE FROM repuesto WHERE id_repuesto = :id_repuesto";
            $stmt = $conexion->prepare($sql);

            return $stmt->execute([':id_repuesto' => $this->id_repuesto]);
        } catch (PDOException $e) {
            return false;
        }
    }
}