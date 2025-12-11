<?php
/**
 * Modelo ArqueoCaja
 */

require_once 'database.php';

class ArqueoCaja extends BaseModel {
    protected $table = 'tb_arqueo_caja';
    protected $primaryKey = 'id_arqueo';
    
    /**
     * Abrir caja
     */
    public function abrirCaja($fecha, $saldoInicial, $userId) {
        try {
            // Verificar si ya existe un arqueo para esta fecha
            $arqueoExistente = $this->getByFecha($fecha);
            
            if ($arqueoExistente) {
                return [
                    'success' => false,
                    'mensaje' => 'Ya existe un arqueo de caja para esta fecha'
                ];
            }
            
            $arqueoId = $this->create([
                'fecha_arqueo' => $fecha,
                'hora_apertura' => date('H:i:s'),
                'saldo_inicial' => $saldoInicial,
                'id_usuario_apertura' => $userId,
                'estado' => 'abierto'
            ]);
            
            return [
                'success' => true,
                'id_arqueo' => $arqueoId,
                'mensaje' => 'Caja abierta correctamente'
            ];
        } catch(Exception $e) {
            $this->logError('abrirCaja', $e);
            
            return [
                'success' => false,
                'mensaje' => 'Error al abrir caja: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Cerrar caja
     */
    public function cerrarCaja($fecha, $saldoReal, $userId, $observaciones = '') {
        try {
            $sql = "CALL sp_cierre_caja(:fecha, :saldo_real, :id_usuario, :observaciones, @p_mensaje)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':fecha' => $fecha,
                ':saldo_real' => $saldoReal,
                ':id_usuario' => $userId,
                ':observaciones' => $observaciones
            ]);
            
            $result = $this->pdo->query("SELECT @p_mensaje as mensaje")->fetch();
            
            return [
                'success' => true,
                'mensaje' => $result['mensaje']
            ];
        } catch(Exception $e) {
            $this->logError('cerrarCaja', $e);
            
            return [
                'success' => false,
                'mensaje' => 'Error al cerrar caja: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener arqueo por fecha
     */
    public function getByFecha($fecha) {
        $result = $this->findBy('fecha_arqueo', $fecha);
        return !empty($result) ? $result[0] : false;
    }
    
    /**
     * Obtener arqueo del día actual
     */
    public function getArqueoHoy() {
        return $this->getByFecha(date('Y-m-d'));
    }
    
    /**
     * Verificar si caja está abierta
     */
    public function cajaAbierta($fecha = null) {
        $fecha = $fecha ?? date('Y-m-d');
        $arqueo = $this->getByFecha($fecha);
        
        return $arqueo && $arqueo['estado'] === 'abierto';
    }
    
    /**
     * Obtener resumen de caja diario
     */
    public function getResumenDiario($fecha = null) {
        $fecha = $fecha ?? date('Y-m-d');
        
        try {
            $sql = "SELECT * FROM vw_resumen_caja_diario
                    WHERE fecha_arqueo = :fecha";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':fecha' => $fecha]);
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            $this->logError('getResumenDiario', $e);
            return false;
        }
    }
    
    /**
     * Obtener arqueos por período
     */
    public function getArqueosPeriodo($fechaInicio, $fechaFin) {
        return $this->query(
            "SELECT * FROM vw_resumen_caja_diario
            WHERE fecha_arqueo BETWEEN :fecha_inicio AND :fecha_fin
            ORDER BY fecha_arqueo DESC",
            [
                ':fecha_inicio' => $fechaInicio,
                ':fecha_fin' => $fechaFin
            ]
        );
    }
}
