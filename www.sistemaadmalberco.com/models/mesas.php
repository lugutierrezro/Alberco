<?php
/**
 * Modelo Mesa
 */

require_once 'database.php';

class Mesa extends BaseModel
{
    protected $table = 'tb_mesas';
    protected $primaryKey = 'id_mesa';

    /**
     * Obtener mesas por zona
     */
    public function getByZona($zona)
    {
        try {
            return $this->query(
                "SELECT * FROM {$this->table}
                WHERE zona = :zona 
                AND estado_registro = 'ACTIVO'
                ORDER BY numero_mesa",
                [':zona' => $zona]
            );
        } catch (PDOException $e) {
            $this->logError('getByZona', $e);
            return [];
        }
    }

    /**
     * Obtener mesas disponibles
     */
    public function getDisponibles()
    {
        try {
            return $this->query(
                "SELECT * FROM {$this->table}
                WHERE estado = 'disponible' 
                AND estado_registro = 'ACTIVO'
                ORDER BY zona, numero_mesa"
            );
        } catch (PDOException $e) {
            $this->logError('getDisponibles', $e);
            return [];
        }
    }

    /**
     * Obtener mesas ocupadas con pedido actual
     */
    public function getOcupadas()
    {
        try {
            return $this->query(
                "SELECT m.*, 
                    p.id_pedido,
                    p.nro_pedido,
                    p.total,
                    c.nombre as cliente_nombre,
                    e.nombre_estado
                FROM {$this->table} m
                LEFT JOIN tb_pedidos p ON m.id_mesa = p.id_mesa 
                    AND p.estado_registro = 'ACTIVO'
                    AND p.id_estado NOT IN (5, 6)
                LEFT JOIN tb_clientes c ON p.id_cliente = c.id_cliente
                LEFT JOIN tb_estados e ON p.id_estado = e.id_estado
                WHERE m.estado = 'ocupada' 
                AND m.estado_registro = 'ACTIVO'
                ORDER BY m.zona, m.numero_mesa"
            );
        } catch (PDOException $e) {
            $this->logError('getOcupadas', $e);
            return [];
        }
    }

    /**
     * Cambiar estado de mesa
     */
    public function cambiarEstado($mesaId, $nuevoEstado)
    {
        try {
            $estadosPermitidos = ['disponible', 'ocupada', 'reservada', 'mantenimiento'];
            
            if (!in_array($nuevoEstado, $estadosPermitidos)) {
                throw new Exception("Estado no válido: $nuevoEstado");
            }
            
            return $this->update($mesaId, [
                'estado' => $nuevoEstado,
                'fyh_actualizacion' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            $this->logError('cambiarEstado', $e);
            return false;
        }
    }

    /**
     * Liberar mesa (marcar como disponible)
     */
    public function liberar($mesaId)
    {
        return $this->cambiarEstado($mesaId, 'disponible');
    }

    /**
     * Ocupar mesa
     */
    public function ocupar($mesaId)
    {
        return $this->cambiarEstado($mesaId, 'ocupada');
    }

    /**
     * Reservar mesa
     */
    public function reservar($mesaId)
    {
        return $this->cambiarEstado($mesaId, 'reservada');
    }

    /**
     * Verificar si número de mesa existe
     */
    public function numeroExists($numero, $excludeId = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                    WHERE numero_mesa = :numero
                    AND estado_registro = 'ACTIVO'";

            if ($excludeId) {
                $sql .= " AND {$this->primaryKey} != :exclude_id";
            }

            $stmt = $this->pdo->prepare($sql);
            $params = [':numero' => $numero];

            if ($excludeId) {
                $params[':exclude_id'] = $excludeId;
            }

            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['total'] > 0;
        } catch (PDOException $e) {
            $this->logError('numeroExists', $e);
            return false;
        }
    }

    /**
     * Obtener zonas únicas
     */
    public function getZonas()
    {
        try {
            $sql = "SELECT DISTINCT zona 
                    FROM {$this->table} 
                    WHERE estado_registro = 'ACTIVO'
                    ORDER BY zona";

            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            $this->logError('getZonas', $e);
            return [];
        }
    }

    /**
     * Estadísticas de mesas
     */
    public function getEstadisticas()
    {
        try {
            $sql = "SELECT 
                    COUNT(*) as total_mesas,
                    SUM(CASE WHEN estado = 'disponible' THEN 1 ELSE 0 END) as disponibles,
                    SUM(CASE WHEN estado = 'ocupada' THEN 1 ELSE 0 END) as ocupadas,
                    SUM(CASE WHEN estado = 'reservada' THEN 1 ELSE 0 END) as reservadas,
                    SUM(CASE WHEN estado = 'mantenimiento' THEN 1 ELSE 0 END) as mantenimiento,
                    SUM(capacidad) as capacidad_total
                    FROM {$this->table}
                    WHERE estado_registro = 'ACTIVO'";

            $stmt = $this->pdo->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logError('getEstadisticas', $e);
            return false;
        }
    }
}
