<?php
/**
 * Modelo MovimientoCaja
 */

require_once 'database.php';

class MovimientoCaja extends BaseModel {
    protected $table = 'tb_movimientos_caja';
    protected $primaryKey = 'id_movimiento';
    
    /**
     * Registrar movimiento
     */
    public function registrarMovimiento($data) {
        try {
            $movimientoId = $this->create($data);
            
            return [
                'success' => true,
                'id_movimiento' => $movimientoId,
                'mensaje' => 'Movimiento registrado correctamente'
            ];
        } catch(Exception $e) {
            $this->logError('registrarMovimiento', $e);
            
            return [
                'success' => false,
                'mensaje' => 'Error al registrar movimiento: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener movimientos del día
     */
    public function getMovimientosDelDia($fecha = null) {
        $fecha = $fecha ?? date('Y-m-d');
        
        return $this->query(
            "SELECT * FROM vw_movimientos_caja_detalle
            WHERE fecha = :fecha
            ORDER BY hora DESC",
            [':fecha' => $fecha]
        );
    }
    
    /**
     * Obtener movimientos por período
     */
    public function getMovimientosPeriodo($fechaInicio, $fechaFin, $tipo = null) {
        try {
            $sql = "SELECT * FROM vw_movimientos_caja_detalle
                    WHERE fecha BETWEEN :fecha_inicio AND :fecha_fin";
            
            $params = [
                ':fecha_inicio' => $fechaInicio,
                ':fecha_fin' => $fechaFin
            ];
            
            if ($tipo) {
                $sql .= " AND tipo_movimiento = :tipo";
                $params[':tipo'] = $tipo;
            }
            
            $sql .= " ORDER BY fecha DESC, hora DESC";
            
            return $this->query($sql, $params);
        } catch(PDOException $e) {
            $this->logError('getMovimientosPeriodo', $e);
            return [];
        }
    }
    
    /**
     * Obtener total de ingresos del día
     */
    public function getTotalIngresosDelDia($fecha = null) {
        $fecha = $fecha ?? date('Y-m-d');
        
        try {
            $sql = "SELECT COALESCE(SUM(monto), 0) as total
                    FROM {$this->table}
                    WHERE DATE(fecha_movimiento) = :fecha
                    AND tipo_movimiento = 'ingreso'
                    AND estado_movimiento = 'completado'
                    AND estado_registro = 'ACTIVO'";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':fecha' => $fecha]);
            $result = $stmt->fetch();
            
            return (float)$result['total'];
        } catch(PDOException $e) {
            $this->logError('getTotalIngresosDelDia', $e);
            return 0;
        }
    }
    
    /**
     * Obtener total de egresos del día
     */
    public function getTotalEgresosDelDia($fecha = null) {
        $fecha = $fecha ?? date('Y-m-d');
        
        try {
            $sql = "SELECT COALESCE(SUM(monto), 0) as total
                    FROM {$this->table}
                    WHERE DATE(fecha_movimiento) = :fecha
                    AND tipo_movimiento = 'egreso'
                    AND estado_movimiento = 'completado'
                    AND estado_registro = 'ACTIVO'";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':fecha' => $fecha]);
            $result = $stmt->fetch();
            
            return (float)$result['total'];
        } catch(PDOException $e) {
            $this->logError('getTotalEgresosDelDia', $e);
            return 0;
        }
    }
    
    /**
     * Obtener resumen del día
     */
    public function getResumenDelDia($fecha = null) {
        $fecha = $fecha ?? date('Y-m-d');
        
        return [
            'fecha' => $fecha,
            'total_ingresos' => $this->getTotalIngresosDelDia($fecha),
            'total_egresos' => $this->getTotalEgresosDelDia($fecha),
            'saldo' => $this->getTotalIngresosDelDia($fecha) - $this->getTotalEgresosDelDia($fecha)
        ];
    }
}
