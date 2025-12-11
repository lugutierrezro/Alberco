<?php
/**
 * Modelo TipoComprobante
 */

require_once 'database.php';

class TipoComprobante extends BaseModel {
    protected $table = 'tb_tipo_comprobante';
    protected $primaryKey = 'id_tipo_comprobante';
    
    /**
     * Obtener siguiente correlativo
     */
    public function getNextCorrelativo($tipoComprobanteId) {
        try {
            $sql = "SELECT serie, correlativo_actual + 1 as next_correlativo
                    FROM {$this->table}
                    WHERE id_tipo_comprobante = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $tipoComprobanteId]);
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            $this->logError('getNextCorrelativo', $e);
            return false;
        }
    }
    
    /**
     * Actualizar correlativo
     */
    public function updateCorrelativo($tipoComprobanteId, $nuevoCorrelativo) {
        return $this->update($tipoComprobanteId, [
            'correlativo_actual' => $nuevoCorrelativo
        ]);
    }
    
    /**
     * Incrementar correlativo
     */
    public function incrementarCorrelativo($tipoComprobanteId) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET correlativo_actual = correlativo_actual + 1
                    WHERE id_tipo_comprobante = :id";
            
            return $this->execute($sql, [':id' => $tipoComprobanteId]);
        } catch(PDOException $e) {
            $this->logError('incrementarCorrelativo', $e);
            return false;
        }
    }
    
    /**
     * Generar número de comprobante completo
     */
    public function generarNumeroComprobante($tipoComprobanteId) {
        $data = $this->getNextCorrelativo($tipoComprobanteId);
        
        if ($data) {
            return $data['serie'] . '-' . str_pad($data['next_correlativo'], 8, '0', STR_PAD_LEFT);
        }
        
        return false;
    }
}
