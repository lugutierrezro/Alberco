<?php
/**
 * Modelo Venta - Completo
 * Sistema de Gestión Alberco
 */

require_once 'database.php';

class Venta extends BaseModel {
    protected $table = 'tb_ventas';
    protected $primaryKey = 'id_venta';
    
    /**
     * Registrar venta completa con stored procedure
     * @param array $ventaData Datos de la venta
     * @param array $detalles Array de productos
     * @return array
     */
    public function registrarVenta($ventaData, $detalles) {
        try {
            $this->beginTransaction();
            
            // Convertir detalles a JSON
            $detalleJSON = json_encode($detalles);
            
            // Llamar al stored procedure
            $sql = "CALL sp_registrar_venta(
                :id_cliente, :id_usuario, :id_tipo_comprobante, :id_metodo_pago,
                :referencia_pago, :subtotal, :igv, :descuento, :total, :monto_recibido,
                :detalle, @p_id_venta, @p_nro_venta, @p_mensaje
            )";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_cliente' => $ventaData['id_cliente'],
                ':id_usuario' => $ventaData['id_usuario'],
                ':id_tipo_comprobante' => $ventaData['id_tipo_comprobante'],
                ':id_metodo_pago' => $ventaData['id_metodo_pago'],
                ':referencia_pago' => $ventaData['referencia_pago'] ?? null,
                ':subtotal' => $ventaData['subtotal'],
                ':igv' => $ventaData['igv'] ?? 0,
                ':descuento' => $ventaData['descuento'] ?? 0,
                ':total' => $ventaData['total'],
                ':monto_recibido' => $ventaData['monto_recibido'],
                ':detalle' => $detalleJSON
            ]);
            
            // Obtener valores de salida
            $result = $this->pdo->query("SELECT @p_id_venta as id_venta, @p_nro_venta as nro_venta, @p_mensaje as mensaje")->fetch();
            
            $this->commit();
            
            return [
                'success' => true,
                'id_venta' => $result['id_venta'],
                'nro_venta' => $result['nro_venta'],
                'mensaje' => $result['mensaje']
            ];
            
        } catch(PDOException $e) {
            $this->rollback();
            $this->logError('registrarVenta', $e);
            
            return [
                'success' => false,
                'mensaje' => 'Error al registrar la venta: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Registrar venta manual (sin stored procedure)
     * @param array $ventaData
     * @param array $detalles
     * @return array
     */
    public function registrarVentaManual($ventaData, $detalles) {
        try {
            $this->beginTransaction();
            
            // Obtener siguiente número de venta
            $nroVenta = $this->getNextNroVenta();
            
            // Obtener serie y correlativo del comprobante
            $comprobante = $this->getNextComprobante($ventaData['id_tipo_comprobante']);
            
            // Calcular vuelto
            $vuelto = $ventaData['monto_recibido'] - $ventaData['total'];
            
            // Preparar datos para insertar
            $dataVenta = [
                'nro_venta' => $nroVenta,
                'serie_comprobante' => $comprobante['serie'],
                'numero_comprobante' => $comprobante['numero'],
                'id_cliente' => $ventaData['id_cliente'],
                'id_usuario' => $ventaData['id_usuario'],
                'id_tipo_comprobante' => $ventaData['id_tipo_comprobante'],
                'id_metodo_pago' => $ventaData['id_metodo_pago'],
                'referencia_pago' => $ventaData['referencia_pago'] ?? null,
                'subtotal' => $ventaData['subtotal'],
                'igv' => $ventaData['igv'] ?? 0,
                'descuento' => $ventaData['descuento'] ?? 0,
                'total' => $ventaData['total'],
                'monto_recibido' => $ventaData['monto_recibido'],
                'vuelto' => $vuelto,
                'estado_venta' => 'completada',
                'observaciones' => $ventaData['observaciones'] ?? null
            ];
            
            // Insertar venta
            $ventaId = $this->create($dataVenta);
            
            if (!$ventaId) {
                throw new Exception('Error al crear la venta');
            }
            
            // Insertar detalle de venta
            foreach ($detalles as $detalle) {
                $dataDetalle = [
                    'id_venta' => $ventaId,
                    'id_producto' => $detalle['id_producto'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'descuento' => $detalle['descuento'] ?? 0,
                    'subtotal' => $detalle['subtotal']
                ];
                
                $this->insertDetalle($dataDetalle);
                
                // Actualizar stock del producto
                $this->updateStock($detalle['id_producto'], $detalle['cantidad']);
            }
            
            // Actualizar correlativo del comprobante
            $this->updateCorrelativoComprobante($ventaData['id_tipo_comprobante']);
            
            // Registrar movimiento en caja
            $this->registrarMovimientoCaja($ventaId, $ventaData['total'], $ventaData['id_usuario'], $nroVenta, $comprobante);
            
            // Actualizar datos del cliente
            $this->actualizarDatosCliente($ventaData['id_cliente'], $ventaData['total']);
            
            $this->commit();
            
            return [
                'success' => true,
                'id_venta' => $ventaId,
                'nro_venta' => $nroVenta,
                'comprobante' => $comprobante['serie'] . '-' . $comprobante['numero'],
                'mensaje' => 'Venta registrada correctamente'
            ];
            
        } catch(Exception $e) {
            $this->rollback();
            $this->logError('registrarVentaManual', $e);
            
            return [
                'success' => false,
                'mensaje' => 'Error al registrar la venta: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener ventas con detalles completos
     * @param array $filters
     * @return array
     */
    public function getVentasWithDetails($filters = []) {
        try {
            $sql = "SELECT v.*, 
                    c.nombre as cliente_nombre,
                    c.apellidos as cliente_apellidos,
                    c.numero_documento as cliente_documento,
                    c.telefono as cliente_telefono,
                    tc.nombre_tipo as tipo_comprobante,
                    tc.codigo_sunat,
                    mp.nombre_metodo as metodo_pago,
                    u.username as usuario,
                    e.nombres as empleado_nombres,
                    e.apellidos as empleado_apellidos
                    FROM {$this->table} v
                    INNER JOIN tb_clientes c ON v.id_cliente = c.id_cliente
                    INNER JOIN tb_tipo_comprobante tc ON v.id_tipo_comprobante = tc.id_tipo_comprobante
                    INNER JOIN tb_metodos_pago mp ON v.id_metodo_pago = mp.id_metodo
                    INNER JOIN tb_usuarios u ON v.id_usuario = u.id_usuario
                    LEFT JOIN tb_empleados e ON u.id_empleado = e.id_empleado
                    WHERE v.estado_registro = 'ACTIVO'";
            
            $params = [];
            
            if (!empty($filters['fecha_inicio'])) {
                $sql .= " AND DATE(v.fecha_venta) >= :fecha_inicio";
                $params[':fecha_inicio'] = $filters['fecha_inicio'];
            }
            
            if (!empty($filters['fecha_fin'])) {
                $sql .= " AND DATE(v.fecha_venta) <= :fecha_fin";
                $params[':fecha_fin'] = $filters['fecha_fin'];
            }
            
            if (!empty($filters['id_cliente'])) {
                $sql .= " AND v.id_cliente = :id_cliente";
                $params[':id_cliente'] = $filters['id_cliente'];
            }
            
            if (!empty($filters['estado_venta'])) {
                $sql .= " AND v.estado_venta = :estado_venta";
                $params[':estado_venta'] = $filters['estado_venta'];
            }
            
            if (!empty($filters['id_tipo_comprobante'])) {
                $sql .= " AND v.id_tipo_comprobante = :id_tipo_comprobante";
                $params[':id_tipo_comprobante'] = $filters['id_tipo_comprobante'];
            }
            
            if (!empty($filters['id_metodo_pago'])) {
                $sql .= " AND v.id_metodo_pago = :id_metodo_pago";
                $params[':id_metodo_pago'] = $filters['id_metodo_pago'];
            }
            
            if (!empty($filters['nro_venta'])) {
                $sql .= " AND v.nro_venta = :nro_venta";
                $params[':nro_venta'] = $filters['nro_venta'];
            }
            
            $sql .= " ORDER BY v.fecha_venta DESC, v.id_venta DESC";
            
            if (!empty($filters['limit'])) {
                $sql .= " LIMIT " . (int)$filters['limit'];
            }
            
            return $this->query($sql, $params);
        } catch(PDOException $e) {
            $this->logError('getVentasWithDetails', $e);
            return [];
        }
    }
    
    /**
     * Obtener detalle de una venta específica
     * @param int $ventaId
     * @return array
     */
    public function getVentaCompleta($ventaId) {
        try {
            // Obtener datos de la venta (busca por nro_venta)
            $venta = $this->getVentasWithDetails(['nro_venta' => $ventaId]);
            
            if (empty($venta)) {
                return null;
            }
            
            $venta = $venta[0];
            
            // Obtener detalle de productos (usa id_venta real)
            $venta['detalles'] = $this->getVentaDetalle($venta['id_venta']);
            
            return $venta;
        } catch(Exception $e) {
            $this->logError('getVentaCompleta', $e);
            return null;
        }
    }
    
    /**
     * Obtener detalle de productos de una venta
     * @param int $ventaId
     * @return array
     */
    public function getVentaDetalle($ventaId) {
        try {
            $sql = "SELECT dv.*, 
                    p.codigo as producto_codigo,
                    p.nombre as producto_nombre,
                    p.imagen as producto_imagen,
                    c.nombre_categoria
                    FROM tb_detalle_ventas dv
                    INNER JOIN tb_almacen p ON dv.id_producto = p.id_producto
                    INNER JOIN tb_categorias c ON p.id_categoria = c.id_categoria
                    WHERE dv.id_venta = :venta_id
                    AND dv.estado_registro = 'ACTIVO'
                    ORDER BY dv.id_detalle_venta";
            
            return $this->query($sql, [':venta_id' => $ventaId]);
        } catch(PDOException $e) {
            $this->logError('getVentaDetalle', $e);
            return [];
        }
    }
    
    /**
     * Anular venta
     * @param int $ventaId
     * @param int $userId
     * @param string $motivo
     * @return bool
     */
    public function anularVenta($ventaId, $userId, $motivo = '') {
        try {
            $this->beginTransaction();
            
            // Obtener datos de la venta
            $venta = $this->getById($ventaId);
            
            if (!$venta) {
                throw new Exception('Venta no encontrada');
            }
            
            if ($venta['estado_venta'] === 'anulada') {
                throw new Exception('La venta ya está anulada');
            }
            
            // Cambiar estado de la venta
            $sql = "UPDATE {$this->table} 
                    SET estado_venta = 'anulada', 
                        observaciones = CONCAT(COALESCE(observaciones, ''), ' | ANULADA: ', :motivo)
                    WHERE id_venta = :venta_id";
            
            $this->execute($sql, [
                ':venta_id' => $ventaId,
                ':motivo' => $motivo
            ]);
            
            // Devolver stock de los productos
            $sql = "UPDATE tb_almacen a
                    INNER JOIN tb_detalle_ventas dv ON a.id_producto = dv.id_producto
                    SET a.stock = a.stock + dv.cantidad
                    WHERE dv.id_venta = :venta_id
                    AND dv.estado_registro = 'ACTIVO'";
            
            $this->execute($sql, [':venta_id' => $ventaId]);
            
            // Registrar movimiento de caja negativo (egreso por devolución)
            $sql = "INSERT INTO tb_movimientos_caja 
                    (tipo_movimiento, concepto, descripcion, monto, id_usuario, id_venta, estado_movimiento)
                    VALUES ('egreso', 'Anulación de Venta', :descripcion, :monto, :user_id, :venta_id, 'completado')";
            
            $this->execute($sql, [
                ':descripcion' => "Anulación de venta #" . $venta['nro_venta'] . " - " . $motivo,
                ':monto' => $venta['total'],
                ':user_id' => $userId,
                ':venta_id' => $ventaId
            ]);
            
            // Actualizar datos del cliente (restar total y puntos)
            $puntosPerdidos = floor($venta['total'] / 10);
            
            $sql = "UPDATE tb_clientes 
                    SET total_compras = total_compras - :total,
                        puntos_fidelidad = GREATEST(0, puntos_fidelidad - :puntos)
                    WHERE id_cliente = :cliente_id";
            
            $this->execute($sql, [
                ':total' => $venta['total'],
                ':puntos' => $puntosPerdidos,
                ':cliente_id' => $venta['id_cliente']
            ]);
            
            // Registrar en auditoría
            $this->logAudit($ventaId, 'ANULAR_VENTA', $userId);
            
            $this->commit();
            
            return true;
        } catch(Exception $e) {
            $this->rollback();
            $this->logError('anularVenta', $e);
            return false;
        }
    }
    
    /**
     * Obtener ventas del día
     * @param string $fecha
     * @return array
     */
    public function getVentasDelDia($fecha = null) {
        $fecha = $fecha ?? date('Y-m-d');
        
        return $this->getVentasWithDetails([
            'fecha_inicio' => $fecha,
            'fecha_fin' => $fecha,
            'estado_venta' => 'completada'
        ]);
    }
    
    /**
     * Obtener resumen de ventas del día
     * @param string $fecha
     * @return array
     */
    public function getResumenDelDia($fecha = null) {
        $fecha = $fecha ?? date('Y-m-d');
        
        try {
            $sql = "SELECT 
                    COUNT(*) as total_ventas,
                    SUM(subtotal) as subtotal_total,
                    SUM(igv) as igv_total,
                    SUM(descuento) as descuento_total,
                    SUM(total) as total_ventas_monto,
                    AVG(total) as ticket_promedio,
                    COUNT(DISTINCT id_cliente) as clientes_atendidos
                    FROM {$this->table}
                    WHERE DATE(fecha_venta) = :fecha
                    AND estado_venta = 'completada'
                    AND estado_registro = 'ACTIVO'";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':fecha' => $fecha]);
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            $this->logError('getResumenDelDia', $e);
            return [];
        }
    }
    
    /**
     * Reporte de ventas por período
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return array
     */
    public function reporteVentasPeriodo($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT DATE(fecha_venta) as fecha,
                           COUNT(*) as num_ventas,
                           SUM(total) as total_dia,
                           SUM(subtotal) as subtotal_dia,
                           SUM(igv) as igv_dia
                    FROM {$this->table}
                    WHERE DATE(fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin
                    AND estado_venta = 'completada'
                    AND estado_registro = 'ACTIVO'
                    GROUP BY DATE(fecha_venta)
                    ORDER BY fecha";
            
            return $this->query($sql, [
                ':fecha_inicio' => $fechaInicio,
                ':fecha_fin' => $fechaFin
            ]);
        } catch(PDOException $e) {
            $this->logError('reporteVentasPeriodo', $e);
            return [];
        }
    }
    
    /**
     * Productos más vendidos
     * @param string $fechaInicio
     * @param string $fechaFin
     * @param int $limite
     * @return array
     */
    public function productosMasVendidos($fechaInicio, $fechaFin, $limite = 10) {
        try {
            $sql = "SELECT p.nombre,
                           COALESCE(c.nombre_categoria, 'Sin categoría') as nombre_categoria,
                           SUM(dv.cantidad) as total_vendido,
                           SUM(dv.subtotal) as total_ingresos,
                           AVG(dv.precio_unitario) as precio_promedio
                    FROM tb_detalle_ventas dv
                    INNER JOIN tb_ventas v ON dv.id_venta = v.id_venta
                    INNER JOIN tb_almacen p ON dv.id_producto = p.id_producto
                    LEFT JOIN tb_categorias c ON p.id_categoria = c.id_categoria
                    WHERE DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin
                    AND v.estado_venta = 'completada'
                    AND v.estado_registro = 'ACTIVO'
                    GROUP BY p.id_producto, p.nombre, c.nombre_categoria
                    ORDER BY total_vendido DESC
                    LIMIT :limite";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fechaInicio);
            $stmt->bindParam(':fecha_fin', $fechaFin);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            $this->logError('productosMasVendidos', $e);
            return [];
        }
    }
    
    /**
     * Calcular utilidad por período
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return array
     */
    public function calcularUtilidad($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT DATE(v.fecha_venta) as fecha,
                           SUM(dv.cantidad * p.precio_venta) as ingresos_brutos,
                           SUM(dv.cantidad * p.precio_compra) as costo_total,
                           SUM(dv.cantidad * (p.precio_venta - p.precio_compra)) as utilidad_bruta,
                           ROUND((SUM(dv.cantidad * (p.precio_venta - p.precio_compra)) / 
                                  SUM(dv.cantidad * p.precio_venta)) * 100, 2) as margen_porcentaje
                    FROM tb_detalle_ventas dv
                    INNER JOIN tb_ventas v ON dv.id_venta = v.id_venta
                    INNER JOIN tb_almacen p ON dv.id_producto = p.id_producto
                    WHERE DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin
                    AND v.estado_venta = 'completada'
                    AND v.estado_registro = 'ACTIVO'
                    GROUP BY DATE(v.fecha_venta)
                    ORDER BY fecha";
            
            return $this->query($sql, [
                ':fecha_inicio' => $fechaInicio,
                ':fecha_fin' => $fechaFin
            ]);
        } catch(PDOException $e) {
            $this->logError('calcularUtilidad', $e);
            return [];
        }
    }
    
    /**
     * Ventas por método de pago
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return array
     */
    public function ventasPorMetodoPago($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT 
                    mp.nombre_metodo,
                    COUNT(v.id_venta) as total_ventas,
                    SUM(v.total) as total_monto,
                    ROUND((SUM(v.total) / (SELECT SUM(total) FROM {$this->table} 
                        WHERE DATE(fecha_venta) BETWEEN :fecha_inicio2 AND :fecha_fin2
                        AND estado_venta = 'completada' 
                        AND estado_registro = 'ACTIVO')) * 100, 2) as porcentaje
                    FROM {$this->table} v
                    INNER JOIN tb_metodos_pago mp ON v.id_metodo_pago = mp.id_metodo
                    WHERE DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin
                    AND v.estado_venta = 'completada'
                    AND v.estado_registro = 'ACTIVO'
                    GROUP BY mp.id_metodo, mp.nombre_metodo
                    ORDER BY total_monto DESC";
            
            return $this->query($sql, [
                ':fecha_inicio' => $fechaInicio,
                ':fecha_fin' => $fechaFin,
                ':fecha_inicio2' => $fechaInicio,
                ':fecha_fin2' => $fechaFin
            ]);
        } catch(PDOException $e) {
            $this->logError('ventasPorMetodoPago', $e);
            return [];
        }
    }
    
    /**
     * Ventas por tipo de comprobante
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return array
     */
    public function ventasPorTipoComprobante($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT 
                    tc.nombre_tipo,
                    COUNT(v.id_venta) as total_ventas,
                    SUM(v.total) as total_monto
                    FROM {$this->table} v
                    INNER JOIN tb_tipo_comprobante tc ON v.id_tipo_comprobante = tc.id_tipo_comprobante
                    WHERE DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin
                    AND v.estado_venta = 'completada'
                    AND v.estado_registro = 'ACTIVO'
                    GROUP BY tc.id_tipo_comprobante, tc.nombre_tipo
                    ORDER BY total_monto DESC";
            
            return $this->query($sql, [
                ':fecha_inicio' => $fechaInicio,
                ':fecha_fin' => $fechaFin
            ]);
        } catch(PDOException $e) {
            $this->logError('ventasPorTipoComprobante', $e);
            return [];
        }
    }
    
    /**
     * Top clientes
     * @param string $fechaInicio
     * @param string $fechaFin
     * @param int $limite
     * @return array
     */
    public function topClientes($fechaInicio, $fechaFin, $limite = 10) {
        try {
            $sql = "SELECT 
                    c.id_cliente,
                    c.nombre,
                    c.apellidos,
                    c.telefono,
                    c.email,
                    COUNT(v.id_venta) as total_compras,
                    SUM(v.total) as total_gastado,
                    AVG(v.total) as ticket_promedio
                    FROM {$this->table} v
                    INNER JOIN tb_clientes c ON v.id_cliente = c.id_cliente
                    WHERE DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin
                    AND v.estado_venta = 'completada'
                    AND v.estado_registro = 'ACTIVO'
                    GROUP BY c.id_cliente
                    ORDER BY total_gastado DESC
                    LIMIT :limite";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fechaInicio);
            $stmt->bindParam(':fecha_fin', $fechaFin);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            $this->logError('topClientes', $e);
            return [];
        }
    }
    
    /**
     * Buscar ventas
     * @param string $search
     * @return array
     */
    public function search($search) {
        try {
            $sql = "SELECT v.*, 
                    c.nombre as cliente_nombre,
                    c.numero_documento,
                    CONCAT(v.serie_comprobante, '-', v.numero_comprobante) as comprobante_completo
                    FROM {$this->table} v
                    INNER JOIN tb_clientes c ON v.id_cliente = c.id_cliente
                    WHERE (v.nro_venta LIKE :search 
                        OR v.serie_comprobante LIKE :search
                        OR v.numero_comprobante LIKE :search
                        OR c.nombre LIKE :search
                        OR c.numero_documento LIKE :search)
                    AND v.estado_registro = 'ACTIVO'
                    ORDER BY v.fecha_venta DESC
                    LIMIT 50";
            
            return $this->query($sql, [':search' => "%$search%"]);
        } catch(PDOException $e) {
            $this->logError('search', $e);
            return [];
        }
    }
    
    // ==========================================
    // MÉTODOS PRIVADOS / AUXILIARES
    // ==========================================
    
    /**
     * Obtener siguiente número de venta
     * @return int
     */
    private function getNextNroVenta() {
        try {
            $sql = "SELECT COALESCE(MAX(nro_venta), 0) + 1 as next_number FROM {$this->table}";
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch();
            return (int)$result['next_number'];
        } catch(PDOException $e) {
            $this->logError('getNextNroVenta', $e);
            return 1;
        }
    }
    
    /**
     * Obtener siguiente número de comprobante
     * @param int $tipoComprobanteId
     * @return array
     */
    private function getNextComprobante($tipoComprobanteId) {
        try {
            $sql = "SELECT serie, correlativo_actual + 1 as numero
                    FROM tb_tipo_comprobante
                    WHERE id_tipo_comprobante = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $tipoComprobanteId]);
            $result = $stmt->fetch();
            
            return [
                'serie' => $result['serie'],
                'numero' => str_pad($result['numero'], 8, '0', STR_PAD_LEFT)
            ];
        } catch(PDOException $e) {
            $this->logError('getNextComprobante', $e);
            return ['serie' => 'B001', 'numero' => '00000001'];
        }
    }
    
    /**
     * Insertar detalle de venta
     * @param array $data
     * @return int|false
     */
    private function insertDetalle($data) {
        try {
            $sql = "INSERT INTO tb_detalle_ventas 
                    (id_venta, id_producto, cantidad, precio_unitario, descuento, subtotal)
                    VALUES (:id_venta, :id_producto, :cantidad, :precio_unitario, :descuento, :subtotal)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            
            return $this->pdo->lastInsertId();
        } catch(PDOException $e) {
            $this->logError('insertDetalle', $e);
            return false;
        }
    }
    
    /**
     * Actualizar stock de producto
     * @param int $productoId
     * @param int $cantidad
     * @return bool
     */
    private function updateStock($productoId, $cantidad) {
        try {
            $sql = "UPDATE tb_almacen 
                    SET stock = stock - :cantidad
                    WHERE id_producto = :producto_id";
            
            return $this->execute($sql, [
                ':cantidad' => $cantidad,
                ':producto_id' => $productoId
            ]);
        } catch(PDOException $e) {
            $this->logError('updateStock', $e);
            return false;
        }
    }
    
    /**
     * Actualizar correlativo de comprobante
     * @param int $tipoComprobanteId
     * @return bool
     */
    private function updateCorrelativoComprobante($tipoComprobanteId) {
        try {
            $sql = "UPDATE tb_tipo_comprobante 
                    SET correlativo_actual = correlativo_actual + 1
                    WHERE id_tipo_comprobante = :id";
            
            return $this->execute($sql, [':id' => $tipoComprobanteId]);
        } catch(PDOException $e) {
            $this->logError('updateCorrelativoComprobante', $e);
            return false;
        }
    }
    
    /**
     * Registrar movimiento en caja
     * @param int $ventaId
     * @param float $monto
     * @param int $userId
     * @param int $nroVenta
     * @param array $comprobante
     * @return bool
     */
    private function registrarMovimientoCaja($ventaId, $monto, $userId, $nroVenta, $comprobante) {
        try {
            $sql = "INSERT INTO tb_movimientos_caja 
                    (tipo_movimiento, concepto, descripcion, monto, id_usuario, id_venta, estado_movimiento)
                    VALUES ('ingreso', 'Venta', :descripcion, :monto, :usuario_id, :venta_id, 'completado')";
            
            return $this->execute($sql, [
                ':descripcion' => "Venta #{$nroVenta} - {$comprobante['serie']}-{$comprobante['numero']}",
                ':monto' => $monto,
                ':usuario_id' => $userId,
                ':venta_id' => $ventaId
            ]);
        } catch(PDOException $e) {
            $this->logError('registrarMovimientoCaja', $e);
            return false;
        }
    }
    
    /**
     * Actualizar datos del cliente
     * @param int $clienteId
     * @param float $totalCompra
     * @return bool
     */
    private function actualizarDatosCliente($clienteId, $totalCompra) {
        try {
            $puntos = floor($totalCompra / 10); // 1 punto por cada S/10
            
            $sql = "UPDATE tb_clientes 
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
            $this->logError('actualizarDatosCliente', $e);
            return false;
        }
    }
    
    // ==========================================
    // DASHBOARD REPORTES - KPIs COMPLETOS
    // ==========================================
    
    /**
     * Obtener KPIs completos del dashboard
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return array
     */
    public function getKPIsDashboard($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT 
                    COUNT(DISTINCT v.id_venta) as total_pedidos,
                    COUNT(DISTINCT v.id_cliente) as clientes_atendidos,
                    SUM(v.total) as ventas_totales,
                    AVG(v.total) as ticket_promedio,
                    SUM(v.subtotal) as subtotal_total,
                    SUM(v.igv) as igv_total,
                    SUM(v.descuento) as descuento_total,
                    COUNT(DISTINCT CASE WHEN v.estado_venta = 'completada' THEN v.id_venta END) as pedidos_completados,
                    COUNT(DISTINCT CASE WHEN v.estado_venta = 'anulada' THEN v.id_venta END) as pedidos_anulados,
                    COUNT(DISTINCT CASE WHEN DATE(v.fyh_creacion) = DATE(v.fecha_venta) THEN v.id_cliente END) as clientes_nuevos
                    FROM {$this->table} v
                    WHERE DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin
                    AND v.estado_registro = 'ACTIVO'";
            
            $result = $this->query($sql, [
                ':fecha_inicio' => $fechaInicio,
                ':fecha_fin' => $fechaFin
            ]);
            
            return !empty($result) ? $result[0] : [];
        } catch(PDOException $e) {
            $this->logError('getKPIsDashboard', $e);
            return [];
        }
    }
    
    /**
     * Análisis de ventas por tipo de pedido
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return array
     */
    public function ventasPorTipoPedido($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT 
                    p.tipo_pedido,
                    COUNT(DISTINCT v.id_venta) as total_ventas,
                    SUM(v.total) as total_monto,
                    AVG(v.total) as ticket_promedio
                    FROM {$this->table} v
                    INNER JOIN tb_pedidos p ON v.id_pedido = p.id_pedido
                    WHERE DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin
                    AND v.estado_venta = 'completada'
                    AND v.estado_registro = 'ACTIVO'
                    GROUP BY p.tipo_pedido
                    ORDER BY total_monto DESC";
            
            return $this->query($sql, [
                ':fecha_inicio' => $fechaInicio,
                ':fecha_fin' => $fechaFin
            ]);
        } catch(PDOException $e) {
            $this->logError('ventasPorTipoPedido', $e);
            return [];
        }
    }
    
    /**
     * Análisis de ventas por hora del día
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return array
     */
    public function ventasPorHora($fechaInicio, $fechaFin) {
        try {
            $sql = "SELECT 
                    HOUR(v.fecha_venta) as hora,
                    COUNT(*) as total_ventas,
                    SUM(v.total) as total_monto
                    FROM {$this->table} v
                    WHERE DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin
                    AND v.estado_venta = 'completada'
                    AND v.estado_registro = 'ACTIVO'
                    GROUP BY HOUR(v.fecha_venta)
                    ORDER BY hora";
            
            return $this->query($sql, [
                ':fecha_inicio' => $fechaInicio,
                ':fecha_fin' => $fechaFin
            ]);
        } catch(PDOException $e) {
            $this->logError('ventasPorHora', $e);
            return [];
        }
    }
    
    /**
     * Obtener productos con stock bajo (alertas)
     * @param int $limite
     * @return array
     */
    public function getStockBajo($limite = 20) {
        try {
            $sql = "SELECT 
                    a.id_producto,
                    a.codigo,
                    a.nombre,
                    a.stock,
                    a.stock_minimo,
                    c.nombre_categoria,
                    (a.stock_minimo - a.stock) as unidades_faltantes
                    FROM tb_almacen a
                    LEFT JOIN tb_categorias c ON a.id_categoria = c.id_categoria
                    WHERE a.stock <= a.stock_minimo
                    AND a.estado_registro = 'ACTIVO'
                    ORDER BY (a.stock_minimo - a.stock) DESC, a.stock ASC
                    LIMIT :limite";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            $this->logError('getStockBajo', $e);
            return [];
        }
    }
}


