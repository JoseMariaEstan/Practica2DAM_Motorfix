<?php
require_once __DIR__ . "/conexion.php";

class Cita {
    private $id_cita;
    private $id_vehiculo;
    private $id_mecanico;
    private $fecha_cita;
    private $motivo;
    private $estado;
    private $diagnostico;
    private $observaciones;

    //iniciar las variables con valor null, nos permite crear el objeto sin pasarle parametros
    //la cita empieza como pendiente hasta que oficina la confirma
    public function __construct($id_cita = null, $id_vehiculo = null, $fecha_cita = null, $motivo = null,
                                $estado = 'pendiente', $id_mecanico = null, $diagnostico = null, $observaciones = null) {
        $this->id_cita       = $id_cita;
        $this->id_vehiculo   = $id_vehiculo;
        $this->fecha_cita    = $fecha_cita;
        $this->motivo        = $motivo;
        $this->estado        = $estado;
        $this->id_mecanico   = $id_mecanico;
        $this->diagnostico   = $diagnostico;
        $this->observaciones = $observaciones;
    }

    // El cliente pide la cita: solo hace falta el vehiculo, la fecha y el motivo
    public function insertCita() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "INSERT INTO cita (id_vehiculo, fecha_cita, motivo, estado)
                    VALUES (:id_vehiculo, :fecha_cita, :motivo, :estado)";
            $stmt = $conexion->prepare($sql);

            $resultado = $stmt->execute([
                ':id_vehiculo' => $this->id_vehiculo,
                ':fecha_cita'  => $this->fecha_cita,
                ':motivo'      => $this->motivo,
                ':estado'      => $this->estado
            ]);

            $this->id_cita = $conexion->lastInsertId();
            return $resultado;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Devuelve la cita por su id
    public function obtenerCita() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "SELECT * FROM cita WHERE id_cita = :id_cita";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_cita' => $this->id_cita]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Devuelve todas las citas de un cliente ("Mis citas").
    // La cita no tiene el cliente, se llega a el por el vehiculo
    public function obtenerPorCliente($id_cliente) {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "SELECT c.*, v.matricula, v.marca, v.modelo
                    FROM cita c
                    JOIN vehiculo v ON v.id_vehiculo = c.id_vehiculo
                    WHERE v.id_cliente = :id_cliente
                    ORDER BY c.fecha_cita DESC";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_cliente' => $id_cliente]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Devuelve las citas asignadas a un mecanico (su hoja de trabajo)
    public function obtenerPorMecanico() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "SELECT c.*, v.matricula, v.marca, v.modelo
                    FROM cita c
                    JOIN vehiculo v ON v.id_vehiculo = c.id_vehiculo
                    WHERE c.id_mecanico = :id_mecanico
                    ORDER BY c.fecha_cita";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_mecanico' => $this->id_mecanico]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Devuelve las citas que estan en un estado (oficina: ver las pendientes de confirmar)
    public function obtenerPorEstado() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "SELECT c.*, v.matricula, v.marca, v.modelo
                    FROM cita c
                    JOIN vehiculo v ON v.id_vehiculo = c.id_vehiculo
                    WHERE c.estado = :estado
                    ORDER BY c.fecha_cita";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':estado' => $this->estado]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Cambia la fecha, el motivo, el diagnostico y las observaciones
    public function updateCita() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "UPDATE cita SET fecha_cita = :fecha_cita, motivo = :motivo,
                    diagnostico = :diagnostico, observaciones = :observaciones
                    WHERE id_cita = :id_cita";
            $stmt = $conexion->prepare($sql);

            return $stmt->execute([
                ':id_cita'       => $this->id_cita,
                ':fecha_cita'    => $this->fecha_cita,
                ':motivo'        => $this->motivo,
                ':diagnostico'   => $this->diagnostico,
                ':observaciones' => $this->observaciones
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Cambia el estado: pendiente -> confirmada -> en_reparacion -> finalizada -> facturada (o cancelada)
    public function updateEstado() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "UPDATE cita SET estado = :estado WHERE id_cita = :id_cita";
            $stmt = $conexion->prepare($sql);

            return $stmt->execute([
                ':id_cita' => $this->id_cita,
                ':estado'  => $this->estado
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Oficina asigna el mecanico que va a hacer la reparacion
    public function asignarMecanico() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "UPDATE cita SET id_mecanico = :id_mecanico WHERE id_cita = :id_cita";
            $stmt = $conexion->prepare($sql);

            return $stmt->execute([
                ':id_cita'     => $this->id_cita,
                ':id_mecanico' => $this->id_mecanico
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Devuelve false si la cita ya tiene factura (la FK de factura tiene ON DELETE RESTRICT).
    // Las horas y repuestos de la cita se borran solos (ON DELETE CASCADE)
    public function deleteCita() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "DELETE FROM cita WHERE id_cita = :id_cita";
            $stmt = $conexion->prepare($sql);

            return $stmt->execute([':id_cita' => $this->id_cita]);
        } catch (PDOException $e) {
            return false;
        }
    }
}