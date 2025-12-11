<?php
/**
 * Modelo Pedido
 * Sistema de Gestión Alberco
 */

require_once 'database.php';

class Pedido extends BaseModel {
    protected $table = 'tb_pedidos';
    protected $primaryKey = 'id_pedido';
    
    /**
     * Crear pedido completo con stored procedure
     * @param array $pedidoData
     * @param array $detalles
     * @return array
     */
    public function crearPedido($pedidoData, $detalles) {
        try {
            // Convertir detalles a JSON
            $detalleJSON = json_encode($detalles);
            
            // Llamar al stored procedure
            $sql = "CALL sp_crear_pedido(
                :tipo_pedido, :id_mesa, :id_cliente, :id_usuario,
                :direccion_entrega, :latitud, :longitud, :observaciones,
                :detalle, @p_id_pedido, @p_numero_comanda, @p_mensaje
            )";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':tipo_pedido' => $pedidoData['tipo_pedido'],
                ':id_mesa' => $pedidoData['id_mesa'] ?? null,
                ':id_cliente' => $pedidoData['id_cliente'],
                ':id_usuario' => $pedidoData['id_usuario'],
                ':direccion_entrega' => $pedidoData['direccion_entrega'] ?? null,
                ':latitud' => $pedidoData['latitud'] ?? null,
                ':longitud' => $pedidoData['longitud'] ?? null,
                ':observaciones' => $pedidoData['observaciones'] ?? null,
                ':detalle' => $detalleJSON
            ]);
            
            // Obtener valores de salida
            $result = $this->pdo->query("SELECT @p_id_pedido as id_pedido, @p_numero_comanda as numero_comanda, @p_mensaje as mensaje")->fetch();
            
            return [
                'success' => true,
                'id_pedido' => $result['id_pedido'],
                'numero_comanda' => $result['numero_comanda'],
                'mensaje' => $result['mensaje']
            ];
            
        } catch(PDOException $e) {
            $this->logError('crearPedido', $e);
            
            return [
                'success' => false,
                'mensaje' => 'Error al crear el pedido: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Cambiar estado del pedido
     * @param int $pedidoId
     * @param int $estadoId
     * @param int $userId
     * @param string $observaciones
     * @return array
     */
    public function cambiarEstado($pedidoId, $estadoId, $userId, $observaciones = '') {
        try {
            $sql = "CALL sp_cambiar_estado_pedido(:id_pedido, :id_estado, :id_usuario, :observaciones, @p_mensaje)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_pedido' => $pedidoId,
                ':id_estado' => $estadoId,
                ':id_usuario' => $userId,
                ':observaciones' => $observaciones
            ]);
            
            $result = $this->pdo->query("SELECT @p_mensaje as mensaje")->fetch();
            
            // Si el estado es "Entregado" (5), actualizar venta vinculada a "completada"
            if ($estadoId == 5) {
                $stmtVenta = $this->pdo->prepare("
                    UPDATE tb_ventas 
                    SET estado_venta = 'completada'
                    WHERE id_pedido = ? AND estado_venta = 'pendiente'
                ");
                $stmtVenta->execute([$pedidoId]);
                
                if ($stmtVenta->rowCount() > 0) {
                    error_log("Venta vinculada al pedido #$pedidoId actualizada a 'completada'");
                }
            }
            
            return [
                'success' => true,
                'mensaje' => $result['mensaje']
            ];
            
        } catch(PDOException $e) {
            $this->logError('cambiarEstado', $e);
            
            return [
                'success' => false,
                'mensaje' => 'Error al cambiar estado: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener pedidos activos
     * @return array
     */
    public function getPedidosActivos() {
        try {
            $sql = "SELECT * FROM vw_pedidos_activos";
            
            return $this->query($sql);
        } catch(PDOException $e) {
            $this->logError('getPedidosActivos', $e);
            return [];
        }
    }
    
    /**
     * Obtener pedido completo con detalles
     * @param int $pedidoId
     * @return array|false
     */
    public function getPedidoCompleto($pedidoId) {
        try {
            // Obtener datos del pedido
            $sql = "SELECT p.*, 
                    c.nombre as cliente_nombre,
                    c.apellidos as cliente_apellidos,
                    c.telefono as cliente_telefono,
                    c.direccion as cliente_direccion,
                    e.nombre_estado,
                    e.color as color_estado,
                    m.numero_mesa,
                    ed.nombres as delivery_nombres,
                    ed.apellidos as delivery_apellidos,
                    ed.celular as delivery_celular,
                    u.username as usuario_registro
                    FROM {$this->table} p
                    INNER JOIN tb_clientes c ON p.id_cliente = c.id_cliente
                    INNER JOIN tb_estados e ON p.id_estado = e.id_estado
                    LEFT JOIN tb_mesas m ON p.id_mesa = m.id_mesa
                    LEFT JOIN tb_empleados ed ON p.id_empleado_delivery = ed.id_empleado
                    INNER JOIN tb_usuarios u ON p.id_usuario_registro = u.id_usuario
                    WHERE p.id_pedido = :pedido_id
                    AND p.estado_registro = 'ACTIVO'";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':pedido_id' => $pedidoId]);
            $pedido = $stmt->fetch();
            
            if (!$pedido) {
                return false;
            }
            
            // Obtener detalles del pedido
            $pedido['detalles'] = $this->getDetallePedido($pedidoId);
            
            return $pedido;
        } catch(PDOException $e) {
            $this->logError('getPedidoCompleto', $e);
            return false;
        }
    }
    
    /**
     * Obtener detalle de productos del pedido
     * @param int $pedidoId
     * @return array
     */
    public function getDetallePedido($pedidoId) {
        try {
            $sql = "SELECT dp.*, 
                    p.codigo as producto_codigo,
                    p.nombre as producto_nombre,
                    p.imagen as producto_imagen,
                    c.nombre_categoria
                    FROM tb_detalle_pedidos dp
                    INNER JOIN tb_almacen p ON dp.id_producto = p.id_producto
                    INNER JOIN tb_categorias c ON p.id_categoria = c.id_categoria
                    WHERE dp.id_pedido = :pedido_id
                    AND dp.estado_registro = 'ACTIVO'
                    ORDER BY dp.id_detalle";
            
            return $this->query($sql, [':pedido_id' => $pedidoId]);
        } catch(PDOException $e) {
            $this->logError('getDetallePedido', $e);
            return [];
        }
    }
    
    /**
     * Obtener historial de seguimiento de pedido
     * @param int $pedidoId
     * @return array
     */
    public function getHistorialSeguimiento($pedidoId) {
        try {
            $sql = "CALL sp_historial_pedido(:id_pedido)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id_pedido', $pedidoId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            $this->logError('getHistorialSeguimiento', $e);
            return [];
        }
    }
    
    /**
     * Asignar delivery a pedido
     * @param int $pedidoId
     * @param int $empleadoId
     * @param int $userId
     * @return array
     */
    public function asignarDelivery($pedidoId, $empleadoId, $userId) {
        try {
            $sql = "CALL sp_asignar_delivery(:id_pedido, :id_empleado, :id_usuario, @p_mensaje)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_pedido' => $pedidoId,
                ':id_empleado' => $empleadoId,
                ':id_usuario' => $userId
            ]);
            
            $result = $this->pdo->query("SELECT @p_mensaje as mensaje")->fetch();
            
            return [
                'success' => true,
                'mensaje' => $result['mensaje']
            ];
            
        } catch(PDOException $e) {
            $this->logError('asignarDelivery', $e);
            
            return [
                'success' => false,
                'mensaje' => 'Error al asignar delivery: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener pedidos por tipo
     * @param string $tipo
     * @return array
     */
    public function getPedidosByTipo($tipo) {
        try {
            $sql = "SELECT p.*, 
                    c.nombre as cliente_nombre,
                    c.telefono as cliente_telefono,
                    e.nombre_estado,
                    e.color as color_estado,
                    m.numero_mesa
                    FROM {$this->table} p
                    INNER JOIN tb_clientes c ON p.id_cliente = c.id_cliente
                    INNER JOIN tb_estados e ON p.id_estado = e.id_estado
                    LEFT JOIN tb_mesas m ON p.id_mesa = m.id_mesa
                    WHERE p.tipo_pedido = :tipo
                    AND p.estado_registro = 'ACTIVO'
                    ORDER BY p.fecha_pedido DESC
                    LIMIT 100";
            
            return $this->query($sql, [':tipo' => $tipo]);
        } catch(PDOException $e) {
            $this->logError('getPedidosByTipo', $e);
            return [];
        }
    }
    
    /**
     * Obtener pedidos de un delivery
     * @param int $empleadoId
     * @param bool $soloActivos
     * @return array
     */
    public function getPedidosByDelivery($empleadoId, $soloActivos = true) {
        try {
            $sql = "SELECT p.*, 
                    c.nombre as cliente_nombre,
                    c.telefono as cliente_telefono,
                    e.nombre_estado,
                    e.color as color_estado
                    FROM {$this->table} p
                    INNER JOIN tb_clientes c ON p.id_cliente = c.id_cliente
                    INNER JOIN tb_estados e ON p.id_estado = e.id_estado
                    WHERE p.id_empleado_delivery = :empleado_id
                    AND p.estado_registro = 'ACTIVO'";
            
            if ($soloActivos) {
                $sql .= " AND p.id_estado NOT IN (5, 6)";
            }
            
            $sql .= " ORDER BY p.fecha_pedido DESC";
            
            return $this->query($sql, [':empleado_id' => $empleadoId]);
        } catch(PDOException $e) {
            $this->logError('getPedidosByDelivery', $e);
            return [];
        }
    }
    
    /**
     * Calificar pedido
     * @param int $pedidoId
     * @param int $calificacion
     * @param string $comentario
     * @return bool
     */
    public function calificarPedido($pedidoId, $calificacion, $comentario = '') {
        // Validar calificación
        if ($calificacion < 1 || $calificacion > 5) {
            return false;
        }
        
        return $this->update($pedidoId, [
            'calificacion' => $calificacion,
            'comentario_cliente' => $comentario
        ]);
    }
    
    /**
     * Actualizar tiempo estimado de entrega
     * @param int $pedidoId
     * @param int $minutos
     * @return bool
     */
    public function actualizarTiempoEstimado($pedidoId, $minutos) {
        try {
            $fechaEstimada = date('Y-m-d H:i:s', strtotime("+{$minutos} minutes"));
            
            return $this->update($pedidoId, [
                'fecha_estimada_entrega' => $fechaEstimada,
                'tiempo_estimado_minutos' => $minutos
            ]);
        } catch(Exception $e) {
            $this->logError('actualizarTiempoEstimado', $e);
            return false;
        }
    }
    
    /**
     * Vincular pedido con venta
     * @param int $pedidoId
     * @param int $ventaId
     * @return bool
     */
    public function vincularConVenta($pedidoId, $ventaId) {
        return $this->update($pedidoId, [
            'id_venta' => $ventaId
        ]);
    }
    
    /**
     * Obtener pedidos pendientes de asignación de delivery
     * @return array
     */
    public function getPedidosPendientesDelivery() {
        try {
            $sql = "SELECT p.*, 
                    c.nombre as cliente_nombre,
                    c.telefono as cliente_telefono,
                    c.direccion as cliente_direccion
                    FROM {$this->table} p
                    INNER JOIN tb_clientes c ON p.id_cliente = c.id_cliente
                    WHERE p.tipo_pedido = 'delivery'
                    AND p.id_empleado_delivery IS NULL
                    AND p.id_estado IN (1, 2, 3)
                    AND p.estado_registro = 'ACTIVO'
                    ORDER BY p.fecha_pedido ASC";
            
            return $this->query($sql);
        } catch(PDOException $e) {
            $this->logError('getPedidosPendientesDelivery', $e);
            return [];
        }
    }
    
    /**
     * Buscar pedidos
     * @param string $search
     * @return array
     */
    public function search($search) {
        try {
            $sql = "SELECT p.*, 
                    c.nombre as cliente_nombre,
                    e.nombre_estado
                    FROM {$this->table} p
                    INNER JOIN tb_clientes c ON p.id_cliente = c.id_cliente
                    INNER JOIN tb_estados e ON p.id_estado = e.id_estado
                    WHERE (p.nro_pedido LIKE :search 
                        OR p.numero_comanda LIKE :search
                        OR c.nombre LIKE :search
                        OR c.telefono LIKE :search)
                    AND p.estado_registro = 'ACTIVO'
                    ORDER BY p.fecha_pedido DESC
                    LIMIT 50";
            
            return $this->query($sql, [':search' => "%$search%"]);
        } catch(PDOException $e) {
            $this->logError('search', $e);
            return [];
        }
    }
    
    /**
     * Estadísticas de pedidos por período
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return array
     */
    public function getEstadisticasPeriodo($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT 
                    COUNT(*) as total_pedidos,
                    SUM(CASE WHEN tipo_pedido = 'mesa' THEN 1 ELSE 0 END) as pedidos_mesa,
                    SUM(CASE WHEN tipo_pedido = 'para_llevar' THEN 1 ELSE 0 END) as pedidos_para_llevar,
                    SUM(CASE WHEN tipo_pedido = 'delivery' THEN 1 ELSE 0 END) as pedidos_delivery,
                    SUM(CASE WHEN id_estado = 5 THEN 1 ELSE 0 END) as pedidos_entregados,
                    SUM(CASE WHEN id_estado = 6 THEN 1 ELSE 0 END) as pedidos_cancelados,
                    COALESCE(SUM(total), 0) as total_ventas,
                    COALESCE(AVG(total), 0) as ticket_promedio,
                    COALESCE(AVG(calificacion), 0) as calificacion_promedio
                    FROM {$this->table}
                    WHERE DATE(fecha_pedido) BETWEEN :fecha_inicio AND :fecha_fin
                    AND estado_registro = 'ACTIVO'";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':fecha_inicio' => $fechaInicio,
                ':fecha_fin' => $fechaFin
            ]);
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            $this->logError('getEstadisticasPeriodo', $e);
            return [];
        }
    }
}
