<?php
/**
 * Modelo Notificacion
 */

require_once 'database.php';

class Notificacion extends BaseModel {
    protected $table = 'tb_notificaciones';
    protected $primaryKey = 'id_notificacion';
    
    /**
     * Crear notificación
     */
    public function crearNotificacion($data) {
        return $this->create($data);
    }
    
    /**
     * Obtener notificaciones de un usuario
     */
    public function getByUsuario($userId, $soloNoLeidas = false) {
        try {
            $sql = "SELECT n.*, p.nro_pedido
                    FROM {$this->table} n
                    LEFT JOIN tb_pedidos p ON n.id_pedido = p.id_pedido
                    WHERE n.id_usuario_destino = :user_id
                    AND n.estado_registro = 'ACTIVO'";
            
            if ($soloNoLeidas) {
                $sql .= " AND n.leido = 0";
            }
            
            $sql .= " ORDER BY n.fecha_notificacion DESC LIMIT 50";
            
            return $this->query($sql, [':user_id' => $userId]);
        } catch(PDOException $e) {
            $this->logError('getByUsuario', $e);
            return [];
        }
    }
    
    /**
     * Marcar como leída
     */
    public function marcarComoLeida($notificacionId) {
        return $this->update($notificacionId, [
            'leido' => 1,
            'fecha_lectura' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Marcar todas como leídas
     */
    public function marcarTodasComoLeidas($userId) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET leido = 1, fecha_lectura = NOW()
                    WHERE id_usuario_destino = :user_id 
                    AND leido = 0";
            
            return $this->execute($sql, [':user_id' => $userId]);
        } catch(PDOException $e) {
            $this->logError('marcarTodasComoLeidas', $e);
            return false;
        }
    }
    
    /**
     * Contar no leídas
     */
    public function contarNoLeidas($userId) {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM {$this->table}
                    WHERE id_usuario_destino = :user_id 
                    AND leido = 0
                    AND estado_registro = 'ACTIVO'";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            $result = $stmt->fetch();
            
            return (int)$result['total'];
        } catch(PDOException $e) {
            $this->logError('contarNoLeidas', $e);
            return 0;
        }
    }
    
    /**
     * Enviar notificación a múltiples usuarios
     */
    public function notificarMultiple($usuariosIds, $tipo, $titulo, $mensaje, $idPedido = null, $prioridad = 'normal') {
        try {
            $this->beginTransaction();
            
            foreach ($usuariosIds as $userId) {
                $this->create([
                    'id_pedido' => $idPedido,
                    'id_usuario_destino' => $userId,
                    'tipo' => $tipo,
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'prioridad' => $prioridad
                ]);
            }
            
            $this->commit();
            return true;
        } catch(Exception $e) {
            $this->rollback();
            $this->logError('notificarMultiple', $e);
            return false;
        }
    }
    
    /**
     * Limpiar notificaciones antiguas
     */
    public function limpiarAntiguas($diasAntiguedad = 30) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET estado_registro = 'INACTIVO'
                    WHERE fecha_notificacion < DATE_SUB(NOW(), INTERVAL :dias DAY)
                    AND leido = 1";
            
            return $this->execute($sql, [':dias' => $diasAntiguedad]);
        } catch(PDOException $e) {
            $this->logError('limpiarAntiguas', $e);
            return false;
        }
    }
}
