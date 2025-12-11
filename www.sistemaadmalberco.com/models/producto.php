<?php
/**
 * Modelo Producto (Almacén)
 * Sistema de Gestión Alberco
 */

require_once 'database.php';

class Producto extends BaseModel {
    protected $table = 'tb_almacen';
    protected $primaryKey = 'id_producto';
    
    /**
     * Obtener productos con su categoría
     * @return array
     */
    public function getAllWithCategory() {
        try {
            $sql = "SELECT a.*, c.nombre_categoria 
                    FROM {$this->table} a
                    INNER JOIN tb_categorias c ON a.id_categoria = c.id_categoria
                    WHERE a.estado_registro = 'ACTIVO'
                    AND c.estado_registro = 'ACTIVO'
                    ORDER BY c.nombre_categoria, a.nombre";
            
            return $this->query($sql);
        } catch(PDOException $e) {
            $this->logError('getAllWithCategory', $e);
            return [];
        }
    }
    
    /**
     * Obtener productos disponibles para venta
     * @return array
     */
    public function getAvailableForSale() {
        try {
            $sql = "SELECT * FROM vw_productos_disponibles";
            
            return $this->query($sql);
        } catch(PDOException $e) {
            $this->logError('getAvailableForSale', $e);
            return [];
        }
    }
    
    /**
     * Obtener productos por categoría
     * @param int $categoryId
     * @return array
     */
    public function getByCategory($categoryId) {
        try {
            $sql = "SELECT a.*, c.nombre_categoria
                    FROM {$this->table} a
                    INNER JOIN tb_categorias c ON a.id_categoria = c.id_categoria
                    WHERE a.id_categoria = :category_id 
                    AND a.estado_registro = 'ACTIVO'
                    ORDER BY a.nombre";
            
            return $this->query($sql, [':category_id' => $categoryId]);
        } catch(PDOException $e) {
            $this->logError('getByCategory', $e);
            return [];
        }
    }
    
    /**
     * Obtener productos con stock bajo
     * @return array
     */
    public function getLowStock() {
        try {
            $sql = "CALL sp_productos_stock_bajo()";
            
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            $this->logError('getLowStock', $e);
            return [];
        }
    }
    
    /**
     * Obtener productos sin stock
     * @return array
     */
    public function getOutOfStock() {
        try {
            $sql = "SELECT a.*, c.nombre_categoria 
                    FROM {$this->table} a
                    INNER JOIN tb_categorias c ON a.id_categoria = c.id_categoria
                    WHERE a.stock = 0
                    AND a.estado_registro = 'ACTIVO'
                    ORDER BY c.nombre_categoria, a.nombre";
            
            return $this->query($sql);
        } catch(PDOException $e) {
            $this->logError('getOutOfStock', $e);
            return [];
        }
    }
    
    /**
     * Actualizar stock
     * @param int $productId
     * @param int $quantity
     * @param string $operation 'add' o 'subtract'
     * @return bool
     */
    public function updateStock($productId, $quantity, $operation = 'subtract') {
        try {
            $operator = ($operation === 'add') ? '+' : '-';
            
            $sql = "UPDATE {$this->table} 
                    SET stock = GREATEST(0, stock $operator :quantity)
                    WHERE id_producto = :product_id";
            
            return $this->execute($sql, [
                ':quantity' => $quantity,
                ':product_id' => $productId
            ]);
        } catch(PDOException $e) {
            $this->logError('updateStock', $e);
            return false;
        }
    }
    
    /**
     * Buscar productos
     * @param string $search
     * @return array
     */
    public function search($search) {
        try {
            $sql = "SELECT a.*, c.nombre_categoria 
                    FROM {$this->table} a
                    INNER JOIN tb_categorias c ON a.id_categoria = c.id_categoria
                    WHERE (a.nombre LIKE :search 
                        OR a.codigo LIKE :search 
                        OR a.descripcion LIKE :search
                        OR c.nombre_categoria LIKE :search)
                    AND a.estado_registro = 'ACTIVO'
                    ORDER BY a.nombre
                    LIMIT 50";
            
            $searchTerm = "%$search%";
            
            return $this->query($sql, [':search' => $searchTerm]);
        } catch(PDOException $e) {
            $this->logError('search', $e);
            return [];
        }
    }
    
    /**
     * Buscar por código
     * @param string $codigo
     * @return array|false
     */
    public function getByCodigo($codigo) {
        $result = $this->findBy('codigo', $codigo);
        return !empty($result) ? $result[0] : false;
    }
    
    /**
     * Verificar si código existe
     * @param string $code
     * @param int $excludeId
     * @return bool
     */
    public function codeExists($code, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                    WHERE codigo = :code";
            
            if ($excludeId) {
                $sql .= " AND id_producto != :exclude_id";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $params = [':code' => $code];
            
            if ($excludeId) {
                $params[':exclude_id'] = $excludeId;
            }
            
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            return $result['total'] > 0;
        } catch(PDOException $e) {
            $this->logError('codeExists', $e);
            return false;
        }
    }
    
    /**
     * Cambiar disponibilidad de venta
     * @param int $productId
     * @param bool $disponible
     * @return bool
     */
    public function cambiarDisponibilidad($productId, $disponible) {
        return $this->update($productId, [
            'disponible_venta' => $disponible ? 1 : 0
        ]);
    }
    
    /**
     * Actualizar precios
     * @param int $productId
     * @param float $precioCompra
     * @param float $precioVenta
     * @return bool
     */
    public function actualizarPrecios($productId, $precioCompra, $precioVenta) {
        if ($precioVenta <= $precioCompra) {
            return false;
        }
        
        return $this->update($productId, [
            'precio_compra' => $precioCompra,
            'precio_venta' => $precioVenta
        ]);
    }
    
    /**
     * Calcular margen de ganancia
     * @param int $productId
     * @return array
     */
    public function calcularMargen($productId) {
        $producto = $this->getById($productId);
        
        if (!$producto) {
            return null;
        }
        
        $margen = $producto['precio_venta'] - $producto['precio_compra'];
        $porcentaje = ($margen / $producto['precio_compra']) * 100;
        
        return [
            'precio_compra' => $producto['precio_compra'],
            'precio_venta' => $producto['precio_venta'],
            'margen_ganancia' => $margen,
            'porcentaje_margen' => round($porcentaje, 2)
        ];
    }
    
    /**
     * Obtener productos más vendidos
     * @param string $fechaInicio
     * @param string $fechaFin
     * @param int $limit
     * @return array
     */
    public function getMasVendidos($fechaInicio, $fechaFin, $limit = 10) {
        try {
            $sql = "SELECT 
                    p.id_producto,
                    p.codigo,
                    p.nombre,
                    p.imagen,
                    c.nombre_categoria,
                    SUM(dv.cantidad) as total_vendido,
                    SUM(dv.subtotal) as total_ingresos
                    FROM {$this->table} p
                    INNER JOIN tb_categorias c ON p.id_categoria = c.id_categoria
                    INNER JOIN tb_detalle_ventas dv ON p.id_producto = dv.id_producto
                    INNER JOIN tb_ventas v ON dv.id_venta = v.id_venta
                    WHERE DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin
                    AND v.estado_venta = 'completada'
                    AND v.estado_registro = 'ACTIVO'
                    AND p.estado_registro = 'ACTIVO'
                    GROUP BY p.id_producto
                    ORDER BY total_vendido DESC
                    LIMIT :limit";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fechaInicio);
            $stmt->bindParam(':fecha_fin', $fechaFin);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            $this->logError('getMasVendidos', $e);
            return [];
        }
    }
    
    /**
     * Obtener historial de movimientos de stock
     * @param int $productId
     * @param int $limit
     * @return array
     */
    public function getHistorialStock($productId, $limit = 50) {
        try {
            $sql = "(SELECT 'COMPRA' as tipo,
                    c.fecha_compra as fecha,
                    c.cantidad,
                    'INGRESO' as movimiento,
                    pr.nombre_proveedor as detalle,
                    u.username as usuario
                    FROM tb_compras c
                    INNER JOIN tb_proveedores pr ON c.id_proveedor = pr.id_proveedor
                    INNER JOIN tb_usuarios u ON c.id_usuario = u.id_usuario
                    WHERE c.id_producto = :product_id1
                    AND c.estado_registro = 'ACTIVO')
                    UNION ALL
                    (SELECT 'VENTA' as tipo,
                    v.fecha_venta as fecha,
                    dv.cantidad,
                    'SALIDA' as movimiento,
                    CONCAT('Venta #', v.nro_venta) as detalle,
                    u.username as usuario
                    FROM tb_detalle_ventas dv
                    INNER JOIN tb_ventas v ON dv.id_venta = v.id_venta
                    INNER JOIN tb_usuarios u ON v.id_usuario = u.id_usuario
                    WHERE dv.id_producto = :product_id2
                    AND v.estado_venta = 'completada'
                    AND v.estado_registro = 'ACTIVO')
                    ORDER BY fecha DESC
                    LIMIT :limit";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':product_id1', $productId, PDO::PARAM_INT);
            $stmt->bindValue(':product_id2', $productId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            $this->logError('getHistorialStock', $e);
            return [];
        }
    }
    
    /**
     * Verificar disponibilidad de stock
     * @param int $productId
     * @param int $cantidad
     * @return bool
     */
    public function verificarStock($productId, $cantidad) {
        $producto = $this->getById($productId);
        return $producto && $producto['stock'] >= $cantidad;
    }
    
    /**
     * Ajustar stock (corrección de inventario)
     * @param int $productId
     * @param int $nuevoStock
     * @param int $userId
     * @param string $motivo
     * @return bool
     */
    public function ajustarStock($productId, $nuevoStock, $userId, $motivo = '') {
        try {
            $this->beginTransaction();
            
            // Actualizar stock
            $result = $this->update($productId, ['stock' => $nuevoStock]);
            
            if ($result) {
                // Registrar en auditoría
                $this->logAudit($productId, 'AJUSTE_STOCK', $userId);
            }
            
            $this->commit();
            return $result;
            
        } catch(Exception $e) {
            $this->rollback();
            $this->logError('ajustarStock', $e);
            return false;
        }
    }
}
