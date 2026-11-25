<?php
require_once __DIR__ . "/conexion.php";

//Cada linea de mano de obra es un trabajo que el mecanico ha hecho en una cita:
//que ha hecho, cuantas horas y a que precio la hora
class ManoObra {
    private $id_mano_obra;
    private $id_cita;
    private $descripcion;
    private $horas;
    private $precio_hora;

    //iniciar las variables con valor null, nos permite crear el objeto sin pasarle parametros
    //el precio por hora por defecto es el del taller (45 euros)
    public function __construct($id_mano_obra = null, $id_cita = null, $descripcion = null,
                                $horas = null, $precio_hora = 45) {
        $this->id_mano_obra = $id_mano_obra;
        $this->id_cita      = $id_cita;
        $this->descripcion  = $descripcion;
        $this->horas        = $horas;
        $this->precio_hora  = $precio_hora;
    }

    public function insertManoObra() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "INSERT INTO mano_obra (id_cita, descripcion, horas, precio_hora)
                    VALUES (:id_cita, :descripcion, :horas, :precio_hora)";
            $stmt = $conexion->prepare($sql);

            $resultado = $stmt->execute([
                ':id_cita'     => $this->id_cita,
                ':descripcion' => $this->descripcion,
                ':horas'       => $this->horas,
                ':precio_hora' => $this->precio_hora
            ]);

            $this->id_mano_obra = $conexion->lastInsertId();
            return $resultado;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Devuelve una linea por su id
    public function obtenerManoObra() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "SELECT * FROM mano_obra WHERE id_mano_obra = :id_mano_obra";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_mano_obra' => $this->id_mano_obra]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Devuelve todas las lineas de una cita (lo que se ve en la hoja de trabajo y en la factura)
    public function obtenerPorCita() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "SELECT * FROM mano_obra WHERE id_cita = :id_cita ORDER BY id_mano_obra";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_cita' => $this->id_cita]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateManoObra() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "UPDATE mano_obra SET descripcion = :descripcion, horas = :horas, precio_hora = :precio_hora
                    WHERE id_mano_obra = :id_mano_obra";
            $stmt = $conexion->prepare($sql);

            return $stmt->execute([
                ':id_mano_obra' => $this->id_mano_obra,
                ':descripcion'  => $this->descripcion,
                ':horas'        => $this->horas,
                ':precio_hora'  => $this->precio_hora
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteManoObra() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "DELETE FROM mano_obra WHERE id_mano_obra = :id_mano_obra";
            $stmt = $conexion->prepare($sql);

            return $stmt->execute([':id_mano_obra' => $this->id_mano_obra]);
        } catch (PDOException $e) {
            return false;
        }
    }
}