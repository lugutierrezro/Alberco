<?php
/**
 * Modelo Tracking Delivery
 * Sistema de Gestión Alberco
 */

require_once 'database.php';

class TrackingDelivery extends BaseModel {
    protected $table = 'tb_tracking_delivery';
    protected $primaryKey = 'id_tracking';
    protected $softDelete = false; // No usar soft delete en tracking
    
    /**
     * Registrar posición GPS del delivery
     * @param array $data
     * @return array
     */
    public function registrarPosicion($data) {
        try {
            $sql = "CALL sp_registrar_tracking_delivery(
                :id_pedido, :id_empleado, :latitud, :longitud,
                :velocidad, :rumbo, :bateria, :distancia_restante,
                :tiempo_estimado, @p_mensaje
            )";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_pedido' => $data['id_pedido'],
                ':id_empleado' => $data['id_empleado'],
                ':latitud' => $data['latitud'],
                ':longitud' => $data['longitud'],
                ':velocidad' => $data['velocidad'] ?? null,
                ':rumbo' => $data['rumbo'] ?? null,
                ':bateria' => $data['bateria'] ?? null,
                ':distancia_restante' => $data['distancia_restante'] ?? null,
                ':tiempo_estimado' => $data['tiempo_estimado'] ?? null
            ]);
            
            $result = $this->pdo->query("SELECT @p_mensaje as mensaje")->fetch();
            
            return [
                'success' => true,
                'mensaje' => $result['mensaje']
            ];
            
        } catch(PDOException $e) {
            $this->logError('registrarPosicion', $e);
            
            return [
                'success' => false,
                'mensaje' => 'Error al registrar posición: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Registrar posición manual (sin stored procedure)
     * @param array $data
     * @return int|false
     */
    public function registrarPosicionManual($data) {
        try {
            // Agregar datos adicionales
            $data['ip_dispositivo'] = $_SERVER['REMOTE_ADDR'] ?? null;
            $data['dispositivo_info'] = json_encode([
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'timestamp' => time()
            ]);
            
            return $this->create($data);
        } catch(Exception $e) {
            $this->logError('registrarPosicionManual', $e);
            return false;
        }
    }
    
    /**
     * Obtener última posición del delivery
     * @param int $pedidoId
     * @return array|false
     */
    public function getUltimaPosicion($pedidoId) {
        try {
            $sql = "CALL sp_obtener_posicion_delivery(:id_pedido)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id_pedido', $pedidoId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            $this->logError('getUltimaPosicion', $e);
            return false;
        }
    }
    
    /**
     * Obtener todos los deliveries activos con su posición
     * @return array
     */
    public function getDeliveriesActivos() {
        try {
            $sql = "SELECT * FROM vw_tracking_delivery_actual";
            
            return $this->query($sql);
        } catch(PDOException $e) {
            $this->logError('getDeliveriesActivos', $e);
            return [];
        }
    }
    
    /**
     * Obtener historial de posiciones de un pedido
     * @param int $pedidoId
     * @param int $limit
     * @return array
     */
    public function getHistorialPosiciones($pedidoId, $limit = 50) {
        try {
            $sql = "SELECT * FROM {$this->table}
                    WHERE id_pedido = :pedido_id
                    ORDER BY fecha_registro DESC
                    LIMIT :limit";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':pedido_id', $pedidoId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            $this->logError('getHistorialPosiciones', $e);
            return [];
        }
    }
    
    /**
     * Obtener ruta completa del delivery (para mostrar en mapa)
     * @param int $pedidoId
     * @return array
     */
    public function getRutaCompleta($pedidoId) {
        try {
            $sql = "SELECT 
                    latitud,
                    longitud,
                    velocidad,
                    rumbo,
                    fecha_registro,
                    TIMESTAMPDIFF(SECOND, LAG(fecha_registro) OVER (ORDER BY fecha_registro), fecha_registro) as segundos_transcurridos
                    FROM {$this->table}
                    WHERE id_pedido = :pedido_id
                    ORDER BY fecha_registro ASC";
            
            return $this->query($sql, [':pedido_id' => $pedidoId]);
        } catch(PDOException $e) {
            $this->logError('getRutaCompleta', $e);
            return [];
        }
    }
    
    /**
     * Calcular distancia recorrida
     * @param int $pedidoId
     * @return float Distancia en kilómetros
     */
    public function calcularDistanciaRecorrida($pedidoId) {
        try {
            $posiciones = $this->getHistorialPosiciones($pedidoId, 1000);
            
            if (count($posiciones) < 2) {
                return 0;
            }
            
            $distanciaTotal = 0;
            
            for ($i = 1; $i < count($posiciones); $i++) {
                $lat1 = $posiciones[$i - 1]['latitud'];
                $lon1 = $posiciones[$i - 1]['longitud'];
                $lat2 = $posiciones[$i]['latitud'];
                $lon2 = $posiciones[$i]['longitud'];
                
                $distanciaTotal += $this->calcularDistanciaHaversine($lat1, $lon1, $lat2, $lon2);
            }
            
            return round($distanciaTotal, 2);
        } catch(Exception $e) {
            $this->logError('calcularDistanciaRecorrida', $e);
            return 0;
        }
    }
    
    /**
     * Calcular distancia entre dos puntos GPS (Fórmula de Haversine)
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Distancia en kilómetros
     */
    private function calcularDistanciaHaversine($lat1, $lon1, $lat2, $lon2) {
        $radioTierra = 6371; // Radio de la Tierra en km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $radioTierra * $c;
    }
    
    /**
     * Obtener velocidad promedio del delivery
     * @param int $pedidoId
     * @return float Velocidad en km/h
     */
    public function getVelocidadPromedio($pedidoId) {
        try {
            $sql = "SELECT AVG(velocidad) as velocidad_promedio
                    FROM {$this->table}
                    WHERE id_pedido = :pedido_id
                    AND velocidad IS NOT NULL
                    AND velocidad > 0";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':pedido_id' => $pedidoId]);
            $result = $stmt->fetch();
            
            return $result ? round($result['velocidad_promedio'], 2) : 0;
        } catch(PDOException $e) {
            $this->logError('getVelocidadPromedio', $e);
            return 0;
        }
    }
    
    /**
     * Obtener tiempo total de entrega
     * @param int $pedidoId
     * @return int Tiempo en minutos
     */
    public function getTiempoTotalEntrega($pedidoId) {
        try {
            $sql = "SELECT 
                    TIMESTAMPDIFF(MINUTE, MIN(fecha_registro), MAX(fecha_registro)) as tiempo_minutos
                    FROM {$this->table}
                    WHERE id_pedido = :pedido_id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':pedido_id' => $pedidoId]);
            $result = $stmt->fetch();
            
            return $result ? (int)$result['tiempo_minutos'] : 0;
        } catch(PDOException $e) {
            $this->logError('getTiempoTotalEntrega', $e);
            return 0;
        }
    }
    
    /**
     * Obtener estadísticas de tracking de un delivery
     * @param int $empleadoId
     * @param string $fecha
     * @return array
     */
    public function getEstadisticasDelivery($empleadoId, $fecha = null) {
        try {
            $fecha = $fecha ?? date('Y-m-d');
            
            $sql = "SELECT 
                    COUNT(DISTINCT t.id_pedido) as total_entregas,
                    COALESCE(SUM(t.distancia_restante), 0) as distancia_total,
                    COALESCE(AVG(t.velocidad), 0) as velocidad_promedio,
                    COALESCE(AVG(t.bateria_porcentaje), 0) as bateria_promedio,
                    COUNT(t.id_tracking) as total_posiciones
                    FROM {$this->table} t
                    INNER JOIN tb_pedidos p ON t.id_pedido = p.id_pedido
                    WHERE t.id_empleado_delivery = :empleado_id
                    AND DATE(t.fecha_registro) = :fecha";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':empleado_id' => $empleadoId,
                ':fecha' => $fecha
            ]);
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            $this->logError('getEstadisticasDelivery', $e);
            return [];
        }
    }
    
    /**
     * Limpiar tracking antiguo (más de 30 días)
     * @param int $dias
     * @return bool
     */
    public function limpiarTrackingAntiguo($dias = 30) {
        try {
            $sql = "DELETE FROM {$this->table}
                    WHERE fecha_registro < DATE_SUB(NOW(), INTERVAL :dias DAY)";
            
            return $this->execute($sql, [':dias' => $dias]);
        } catch(PDOException $e) {
            $this->logError('limpiarTrackingAntiguo', $e);
            return false;
        }
    }
    
    /**
     * Verificar si el delivery está en movimiento
     * @param int $pedidoId
     * @return bool
     */
    public function estaEnMovimiento($pedidoId) {
        try {
            $sql = "SELECT velocidad
                    FROM {$this->table}
                    WHERE id_pedido = :pedido_id
                    ORDER BY fecha_registro DESC
                    LIMIT 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':pedido_id' => $pedidoId]);
            $result = $stmt->fetch();
            
            // Considera en movimiento si velocidad > 1 km/h
            return $result && $result['velocidad'] > 1;
        } catch(PDOException $e) {
            $this->logError('estaEnMovimiento', $e);
            return false;
        }
    }
    
    /**
     * Obtener tiempo estimado de llegada basado en posición actual
     * @param int $pedidoId
     * @return array
     */
    public function getETA($pedidoId) {
        try {
            $posicion = $this->getUltimaPosicion($pedidoId);
            
            if (!$posicion) {
                return null;
            }
            
            return [
                'distancia_restante' => $posicion['distancia_restante'],
                'tiempo_estimado' => $posicion['tiempo_estimado_llegada'],
                'hora_estimada' => date('H:i', strtotime("+{$posicion['tiempo_estimado_llegada']} minutes")),
                'velocidad_actual' => $posicion['velocidad']
            ];
        } catch(Exception $e) {
            $this->logError('getETA', $e);
            return null;
        }
    }
}
