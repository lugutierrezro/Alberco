<?php
/**
 * Modelo MetodoPago
 */

require_once 'database.php';

class MetodoPago extends BaseModel {
    protected $table = 'tb_metodos_pago';
    protected $primaryKey = 'id_metodo';
    
    /**
     * Obtener métodos activos
     */
    public function getActivos() {
        return $this->query(
            "SELECT * FROM {$this->table}
            WHERE estado_registro = 'ACTIVO'
            ORDER BY nombre_metodo"
        );
    }
    
    /**
     * Verificar si requiere referencia
     */
    public function requiereReferencia($metodoId) {
        $metodo = $this->getById($metodoId);
        return $metodo ? (bool)$metodo['requiere_referencia'] : false;
    }
    
    /**
     * Obtener comisión
     */
    public function getComision($metodoId) {
        $metodo = $this->getById($metodoId);
        return $metodo ? (float)$metodo['comision_porcentaje'] : 0;
    }
    
    /**
     * Calcular monto con comisión
     */
    public function calcularMontoConComision($metodoId, $monto) {
        $comision = $this->getComision($metodoId);
        $montoComision = $monto * ($comision / 100);
        
        return [
            'monto_original' => $monto,
            'comision_porcentaje' => $comision,
            'monto_comision' => $montoComision,
            'monto_total' => $monto + $montoComision
        ];
    }
}
