<?php
require_once __DIR__ . "/conexion.php";

class Factura {
    private $id_factura;
    private $id_cita;
    private $numero;
    private $total_mano_obra;
    private $total_repuestos;
    private $subtotal;
    private $iva_porcentaje;
    private $iva_importe;
    private $total;
    private $estado;
    private $id_usuario_emisor;

    //iniciar las variables con valor null, nos permite crear el objeto sin pasarle parametros
    //el IVA por defecto es el 21% y la factura empieza como pendiente de pagar
    public function __construct($id_factura = null, $id_cita = null, $id_usuario_emisor = null,
                                $estado = 'pendiente', $iva_porcentaje = 21) {
        $this->id_factura        = $id_factura;
        $this->id_cita           = $id_cita;
        $this->id_usuario_emisor = $id_usuario_emisor;
        $this->estado            = $estado;
        $this->iva_porcentaje    = $iva_porcentaje;
    }

    // Suma las horas y los repuestos de la cita y calcula subtotal, IVA y total.
    // Se calcula aqui (en el modelo) y no en el sw: asi cualquier pagina que cree una factura
    // usa el mismo calculo, y los importes nunca los manda el navegador
    public function calcularTotales() {
        try {
            $conexion = BD::getInstance()->getConexion();

            //Mano de obra: horas * precio por hora de cada linea
            $sql = "SELECT SUM(horas * precio_hora) AS total FROM mano_obra WHERE id_cita = :id_cita";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_cita' => $this->id_cita]);
            //si la cita no tiene horas, SUM devuelve null y con ?? 0 lo dejamos en 0
            $this->total_mano_obra = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]["total"] ?? 0;

            //Repuestos: cantidad * precio unitario de cada pieza
            $sql = "SELECT SUM(cantidad * precio_unitario) AS total FROM repuesto WHERE id_cita = :id_cita";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_cita' => $this->id_cita]);
            //si la cita no tiene repuestos, SUM devuelve null y con ?? 0 lo dejamos en 0
            $this->total_repuestos = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]["total"] ?? 0;

            //round para mantener los resultados con dos decimales
            $this->subtotal    = round($this->total_mano_obra + $this->total_repuestos, 2);
            $this->iva_importe = round($this->subtotal * $this->iva_porcentaje / 100, 2);
            $this->total       = round($this->subtotal + $this->iva_importe, 2);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Crea el numero de la factura: MF-año-numero de 4 cifras (MF-2026-0001, MF-2026-0002...)
    private function generarNumero() {
        $conexion = BD::getInstance()->getConexion();
        $anio = date("Y");

        //Se cuentan las facturas que ya hay este año para saber que numero toca
        $sql = "SELECT COUNT(*) AS cantidad FROM factura WHERE numero LIKE :anio";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':anio' => "MF-" . $anio . "-%"]);
        $siguiente = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]["cantidad"] + 1;

        //str_pad rellena con ceros por la izquierda hasta 4 cifras (1 -> 0001)
        return "MF-" . $anio . "-" . str_pad($siguiente, 4, "0", STR_PAD_LEFT);
    }

    // Guarda la factura. Antes hay que llamar a calcularTotales() para tener los importes
    public function insertFactura() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $this->numero = $this->generarNumero();

            $sql = "INSERT INTO factura (id_cita, numero, total_mano_obra, total_repuestos, subtotal,
                    iva_porcentaje, iva_importe, total, estado, id_usuario_emisor)
                    VALUES (:id_cita, :numero, :total_mano_obra, :total_repuestos, :subtotal,
                    :iva_porcentaje, :iva_importe, :total, :estado, :id_usuario_emisor)";
            $stmt = $conexion->prepare($sql);

            $resultado = $stmt->execute([
                ':id_cita'           => $this->id_cita,
                ':numero'            => $this->numero,
                ':total_mano_obra'   => $this->total_mano_obra,
                ':total_repuestos'   => $this->total_repuestos,
                ':subtotal'          => $this->subtotal,
                ':iva_porcentaje'    => $this->iva_porcentaje,
                ':iva_importe'       => $this->iva_importe,
                ':total'             => $this->total,
                ':estado'            => $this->estado,
                ':id_usuario_emisor' => $this->id_usuario_emisor
            ]);

            $this->id_factura = $conexion->lastInsertId();
            return $resultado;
        } catch (PDOException $e) {
            //Falla si la cita ya tiene factura (id_cita es UNIQUE en la tabla)
            return false;
        }
    }

    // Devuelve la factura por su id
    public function obtenerFactura() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "SELECT * FROM factura WHERE id_factura = :id_factura";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_factura' => $this->id_factura]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Devuelve la factura de una cita (cada cita tiene como mucho una)
    public function obtenerPorCita() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "SELECT * FROM factura WHERE id_cita = :id_cita";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_cita' => $this->id_cita]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Devuelve todas las facturas de un cliente ("Mis facturas").
    // La factura no tiene el cliente, se llega a el por cita -> vehiculo -> cliente
    public function obtenerPorCliente($id_cliente) {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "SELECT f.*, v.matricula, v.marca, v.modelo, c.motivo
                    FROM factura f
                    JOIN cita c ON c.id_cita = f.id_cita
                    JOIN vehiculo v ON v.id_vehiculo = c.id_vehiculo
                    WHERE v.id_cliente = :id_cliente
                    ORDER BY f.fecha_emision DESC";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':id_cliente' => $id_cliente]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Devuelve todas las facturas del taller (panel de oficina), la mas nueva primero
    public function obtenerTodas() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "SELECT f.*, v.matricula, v.marca, v.modelo
                    FROM factura f
                    JOIN cita c ON c.id_cita = f.id_cita
                    JOIN vehiculo v ON v.id_vehiculo = c.id_vehiculo
                    ORDER BY f.fecha_emision DESC";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Cambia el estado de la factura (pendiente, pagada o anulada)
    public function updateEstado() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "UPDATE factura SET estado = :estado WHERE id_factura = :id_factura";
            $stmt = $conexion->prepare($sql);

            return $stmt->execute([
                ':id_factura' => $this->id_factura,
                ':estado'     => $this->estado
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteFactura() {
        try {
            $conexion = BD::getInstance()->getConexion();
            $sql = "DELETE FROM factura WHERE id_factura = :id_factura";
            $stmt = $conexion->prepare($sql);

            return $stmt->execute([':id_factura' => $this->id_factura]);
        } catch (PDOException $e) {
            return false;
        }
    }
}