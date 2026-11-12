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
    // Se llama antes de insertFactura, los importes no los escribe nadie a mano
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

            //round(..., 2) para que queden con dos decimales, como en una factura real
            $this->subtotal    = round($this->total_mano_obra + $this->total_repuestos, 2);
            $this->iva_importe = round($this->subtotal * $this->iva_porcentaje / 100, 2);
            $this->total       = round($this->subtotal + $this->iva_importe, 2);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

}