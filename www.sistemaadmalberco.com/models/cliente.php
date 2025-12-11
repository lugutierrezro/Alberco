<?php
/**
 * Modelo Cliente
 * Sistema de Gestión Alberco
 */

require_once 'database.php';

class Cliente extends BaseModel {
    protected $table = 'tb_clientes';
    protected $primaryKey = 'id_cliente';
    
    /**
     * Buscar cliente por documento
     * @param string $numeroDocumento
     * @return array|false
     */
    public function getByDocumento($numeroDocumento) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE numero_documento = :documento 
                    AND estado_registro = 'ACTIVO'";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':documento', $numeroDocumento);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            $this->logError('getByDocumento', $e);
            return false;
        }
    }
    
    /**
     * Buscar cliente por teléfono
     * @param string $telefono
     * @return array|false
     */
    public function getByTelefono($telefono) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE telefono = :telefono 
                    AND estado_registro = 'ACTIVO'";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            $this->logError('getByTelefono', $e);
            return false;
        }
    }
    
    /**
     * Buscar cliente por código
     * @param string $codigo
     * @return array|false
     */
    public function getByCodigo($codigo) {
        $result = $this->findBy('codigo_cliente', $codigo);
        return !empty($result) ? $result[0] : false;
    }
    
    /**
     * Obtener clientes frecuentes
     * @param int $limit
     * @return array
     */
    public function getClientesFrecuentes($limit = 20) {
        try {
            $sql = "SELECT * FROM {$this->table}
                    WHERE estado_registro = 'ACTIVO'
                    AND tipo_cliente IN ('FRECUENTE', 'VIP')
                    ORDER BY total_compras DESC, ultima_compra DESC
                    LIMIT :limit";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            $this->logError('getClientesFrecuentes', $e);
            return [];
        }
    }
    
    /**
     * Obtener clientes VIP
     * @return array
     */
    public function getClientesVIP() {
        return $this->query(
            "SELECT * FROM {$this->table}
            WHERE tipo_cliente = 'VIP'
            AND estado_registro = 'ACTIVO'
            ORDER BY total_compras DESC"
        );
    }
    
    /**
     * Actualizar puntos de fidelidad
     * @param int $clienteId
     * @param int $puntos
     * @param string $operacion 'sumar' o 'restar'
     * @return bool
     */
    public function actualizarPuntos($clienteId, $puntos, $operacion = 'sumar') {
        try {
            $operador = ($operacion === 'sumar') ? '+' : '-';
            
            $sql = "UPDATE {$this->table} 
                    SET puntos_fidelidad = GREATEST(0, puntos_fidelidad {$operador} :puntos)
                    WHERE id_cliente = :cliente_id";
            
            return $this->execute($sql, [
                ':puntos' => $puntos,
                ':cliente_id' => $clienteId
            ]);
        } catch(PDOException $e) {
            $this->logError('actualizarPuntos', $e);
            return false;
        }
    }
    
    /**
     * Actualizar datos de compra del cliente
     * @param int $clienteId
     * @param float $totalCompra
     * @return bool
     */
    public function actualizarDatosCompra($clienteId, $totalCompra) {
        try {
            $puntos = floor($totalCompra / 10); // 1 punto por cada S/10
            
            $sql = "UPDATE {$this->table} 
                    SET total_compras = total_compras + :total,
                        ultima_compra = NOW(),
                        puntos_fidelidad = puntos_fidelidad + :puntos
                    WHERE id_cliente = :cliente_id";
            
            return $this->execute($sql, [
                ':total' => $totalCompra,
                ':puntos' => $puntos,
                ':cliente_id' => $clienteId
            ]);
        } catch(PDOException $e) {
            $this->logError('actualizarDatosCompra', $e);
            return false;
        }
    }
    
    /**
     * Actualizar tipo de cliente según total de compras
     * @param int $clienteId
     * @return bool
     */
    public function actualizarTipoCliente($clienteId) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET tipo_cliente = CASE 
                        WHEN total_compras >= 5000 THEN 'VIP'
                        WHEN total_compras >= 1000 THEN 'FRECUENTE'
                        WHEN total_compras > 0 THEN 'OCASIONAL'
                        ELSE 'NUEVO'
                    END
                    WHERE id_cliente = :cliente_id";
            
            return $this->execute($sql, [':cliente_id' => $clienteId]);
        } catch(PDOException $e) {
            $this->logError('actualizarTipoCliente', $e);
            return false;
        }
    }
    
    /**
     * Canjear puntos
     * @param int $clienteId
     * @param int $puntos
     * @return bool
     */
    public function canjearPuntos($clienteId, $puntos) {
        try {
            // Verificar que tenga suficientes puntos
            $cliente = $this->getById($clienteId);
            
            if (!$cliente || $cliente['puntos_fidelidad'] < $puntos) {
                return false;
            }
            
            return $this->actualizarPuntos($clienteId, $puntos, 'restar');
        } catch(Exception $e) {
            $this->logError('canjearPuntos', $e);
            return false;
        }
    }
    
    /**
     * Buscar clientes
     * @param string $search
     * @return array
     */
    public function search($search) {
        try {
            $sql = "SELECT * FROM {$this->table}
                    WHERE (nombre LIKE :search 
                        OR apellidos LIKE :search
                        OR telefono LIKE :search
                        OR numero_documento LIKE :search
                        OR email LIKE :search
                        OR codigo_cliente LIKE :search)
                    AND estado_registro = 'ACTIVO'
                    ORDER BY nombre, apellidos
                    LIMIT 50";
            
            $searchTerm = "%$search%";
            
            return $this->query($sql, [':search' => $searchTerm]);
        } catch(PDOException $e) {
            $this->logError('search', $e);
            return [];
        }
    }
    
    /**
     * Obtener historial de compras del cliente
     * @param int $clienteId
     * @param int $limit
     * @return array
     */
    public function getHistorialCompras($clienteId, $limit = 20) {
        try {
            $sql = "SELECT v.*, 
                    tc.nombre_tipo as tipo_comprobante,
                    mp.nombre_metodo as metodo_pago
                    FROM tb_ventas v
                    INNER JOIN tb_tipo_comprobante tc ON v.id_tipo_comprobante = tc.id_tipo_comprobante
                    INNER JOIN tb_metodos_pago mp ON v.id_metodo_pago = mp.id_metodo
                    WHERE v.id_cliente = :cliente_id
                    AND v.estado_registro = 'ACTIVO'
                    AND v.estado_venta = 'completada'
                    ORDER BY v.fecha_venta DESC
                    LIMIT :limit";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':cliente_id', $clienteId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            $this->logError('getHistorialCompras', $e);
            return [];
        }
    }
    
    /**
     * Obtener pedidos activos del cliente
     * @param int $clienteId
     * @return array
     */
    public function getPedidosActivos($clienteId) {
        try {
            $sql = "SELECT p.*, e.nombre_estado, e.color
                    FROM tb_pedidos p
                    INNER JOIN tb_estados e ON p.id_estado = e.id_estado
                    WHERE p.id_cliente = :cliente_id
                    AND p.estado_registro = 'ACTIVO'
                    AND p.id_estado NOT IN (5, 6)
                    ORDER BY p.fecha_pedido DESC";
            
            return $this->query($sql, [':cliente_id' => $clienteId]);
        } catch(PDOException $e) {
            $this->logError('getPedidosActivos', $e);
            return [];
        }
    }
    
    /**
     * Estadísticas del cliente
     * @param int $clienteId
     * @return array
     */
    public function getEstadisticas($clienteId) {
        try {
            $sql = "SELECT 
                    COUNT(DISTINCT v.id_venta) as total_compras,
                    COALESCE(SUM(v.total), 0) as total_gastado,
                    COALESCE(AVG(v.total), 0) as ticket_promedio,
                    MAX(v.fecha_venta) as ultima_compra,
                    c.puntos_fidelidad,
                    c.tipo_cliente
                    FROM tb_clientes c
                    LEFT JOIN tb_ventas v ON c.id_cliente = v.id_cliente 
                        AND v.estado_venta = 'completada'
                        AND v.estado_registro = 'ACTIVO'
                    WHERE c.id_cliente = :cliente_id
                    GROUP BY c.id_cliente";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':cliente_id' => $clienteId]);
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            $this->logError('getEstadisticas', $e);
            return [];
        }
    }
    
    /**
     * Verificar si el documento ya existe
     * @param string $numeroDocumento
     * @param int $excludeId
     * @return bool
     */
    public function documentoExists($numeroDocumento, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                    WHERE numero_documento = :documento";
            
            if ($excludeId) {
                $sql .= " AND id_cliente != :exclude_id";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $params = [':documento' => $numeroDocumento];
            
            if ($excludeId) {
                $params[':exclude_id'] = $excludeId;
            }
            
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            return $result['total'] > 0;
        } catch(PDOException $e) {
            $this->logError('documentoExists', $e);
            return false;
        }
    }
    
    /**
     * Guardar preferencias del cliente
     * @param int $clienteId
     * @param array $preferencias
     * @return bool
     */
    public function guardarPreferencias($clienteId, $preferencias) {
        return $this->update($clienteId, [
            'preferencias' => json_encode($preferencias)
        ]);
    }
    
    /**
     * Obtener preferencias del cliente
     * @param int $clienteId
     * @return array
     */
    public function getPreferencias($clienteId) {
        $cliente = $this->getById($clienteId);
        
        if ($cliente && !empty($cliente['preferencias'])) {
            return json_decode($cliente['preferencias'], true);
        }
        
        return [];
    }
}
