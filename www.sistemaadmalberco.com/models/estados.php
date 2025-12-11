<?php
/**
 * Modelo Estado
 */

require_once 'database.php';

class Estado extends BaseModel {
    protected $table = 'tb_estados';
    protected $primaryKey = 'id_estado';
    
    /**
     * Obtener todos ordenados
     */
    public function getAllOrdered() {
        return $this->query(
            "SELECT * FROM {$this->table}
            WHERE estado_registro = 'ACTIVO'
            ORDER BY orden ASC"
        );
    }
    
    /**
     * Obtener por nombre
     */
    public function getByNombre($nombreEstado) {
        $result = $this->findBy('nombre_estado', $nombreEstado);
        return !empty($result) ? $result[0] : false;
    }
    
    /**
     * Obtener estados para notificación
     */
    public function getParaNotificar() {
        return $this->query(
            "SELECT * FROM {$this->table}
            WHERE notificar_cliente = 1 
            AND estado_registro = 'ACTIVO'
            ORDER BY orden"
        );
    }
}
