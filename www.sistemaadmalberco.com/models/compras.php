<?php
/**
 * Modelo Compra
 */

require_once 'database.php';

class Compra extends BaseModel {
    protected $table = 'tb_compras';
    protected $primaryKey = 'id_compra';
    
    /**
     * Registrar compra completa
     */
    public function registrarCompra($compraData) {
        try {
            $this->beginTransaction();
            
            // Obtener siguiente número de compra
            $nroCompra = $this->getNextNroCompra();
            $compraData['nro_compra'] = $nroCompra;
            
            // Calcular subtotal y total
            $subtotal = $compraData['precio_compra'] * $compraData['cantidad'];
            $compraData['total'] = $subtotal + ($compraData['igv'] ?? 0);
            
            // Crear compra
            $compraId = $this->create($compraData);
            
            if (!$compraId) {
                throw new Exception('Error al crear la compra');
            }
            
            // Actualizar stock del producto
            $sql = "UPDATE tb_almacen 
                    SET stock = stock + :cantidad,
                        precio_compra = :precio_compra
                    WHERE id_producto = :producto_id";
            
            $this->execute($sql, [
                ':cantidad' => $compraData['cantidad'],
                ':precio_compra' => $compraData['precio_compra'],
                ':producto_id' => $compraData['id_producto']
            ]);
            
            // Registrar movimiento de caja (egreso)
            $sql = "INSERT INTO tb_movimientos_caja 
                    (tipo_movimiento, concepto, descripcion, monto, id_usuario, id_compra, estado_movimiento)
                    VALUES ('egreso', 'Compra', :descripcion, :monto, :usuario_id, :compra_id, 'completado')";
            
            $this->execute($sql, [
                ':descripcion' => "Compra #{$nroCompra} - {$compraData['comprobante']}",
                ':monto' => $compraData['total'],
                ':usuario_id' => $compraData['id_usuario'],
                ':compra_id' => $compraId
            ]);
            
            $this->commit();
            
            return [
                'success' => true,
                'id_compra' => $compraId,
                'nro_compra' => $nroCompra,
                'mensaje' => 'Compra registrada correctamente'
            ];
            
        } catch(Exception $e) {
            $this->rollback();
            $this->logError('registrarCompra', $e);
            
            return [
                'success' => false,
                'mensaje' => 'Error al registrar la compra: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener compras con detalles
     */
    public function getComprasWithDetails($filters = []) {
        try {
            $sql = "SELECT c.*, 
                    p.nombre as producto_nombre,
                    p.codigo as producto_codigo,
                    pr.nombre_proveedor,
                    pr.empresa as proveedor_empresa,
                    u.username as usuario
                    FROM {$this->table} c
                    INNER JOIN tb_almacen p ON c.id_producto = p.id_producto
                    INNER JOIN tb_proveedores pr ON c.id_proveedor = pr.id_proveedor
                    INNER JOIN tb_usuarios u ON c.id_usuario = u.id_usuario
                    WHERE c.estado_registro = 'ACTIVO'";
            
            $params = [];
            
            if (!empty($filters['fecha_inicio'])) {
                $sql .= " AND DATE(c.fecha_compra) >= :fecha_inicio";
                $params[':fecha_inicio'] = $filters['fecha_inicio'];
            }
            
            if (!empty($filters['fecha_fin'])) {
                $sql .= " AND DATE(c.fecha_compra) <= :fecha_fin";
                $params[':fecha_fin'] = $filters['fecha_fin'];
            }
            
            if (!empty($filters['id_proveedor'])) {
                $sql .= " AND c.id_proveedor = :id_proveedor";
                $params[':id_proveedor'] = $filters['id_proveedor'];
            }
            
            $sql .= " ORDER BY c.fecha_compra DESC";
            
            return $this->query($sql, $params);
        } catch(PDOException $e) {
            $this->logError('getComprasWithDetails', $e);
            return [];
        }
    }
    
    /**
     * Obtener siguiente número de compra
     */
    private function getNextNroCompra() {
        try {
            $sql = "SELECT COALESCE(MAX(nro_compra), 0) + 1 as next_number FROM {$this->table}";
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch();
            return (int)$result['next_number'];
        } catch(PDOException $e) {
            $this->logError('getNextNroCompra', $e);
            return 1;
        }
    }
    
    /**
     * Anular compra
     */
    public function anularCompra($compraId, $userId, $motivo = '') {
        try {
            $this->beginTransaction();
            
            // Obtener datos de la compra
            $compra = $this->getById($compraId);
            
            if (!$compra) {
                throw new Exception('Compra no encontrada');
            }
            
            // Descontar stock del producto
            $sql = "UPDATE tb_almacen 
                    SET stock = stock - :cantidad
                    WHERE id_producto = :producto_id";
            
            $this->execute($sql, [
                ':cantidad' => $compra['cantidad'],
                ':producto_id' => $compra['id_producto']
            ]);
            
            // Marcar compra como inactiva
            $this->delete($compraId, $userId);
            
            // Registrar movimiento de caja (ingreso por devolución)
            $sql = "INSERT INTO tb_movimientos_caja 
                    (tipo_movimiento, concepto, descripcion, monto, id_usuario, estado_movimiento)
                    VALUES ('ingreso', 'Anulación de Compra', :descripcion, :monto, :usuario_id, 'completado')";
            
            $this->execute($sql, [
                ':descripcion' => "Anulación de compra #{$compra['nro_compra']} - {$motivo}",
                ':monto' => $compra['total'],
                ':usuario_id' => $userId
            ]);
            
            $this->commit();
            return true;
            
        } catch(Exception $e) {
            $this->rollback();
            $this->logError('anularCompra', $e);
            return false;
        }
    }
}
