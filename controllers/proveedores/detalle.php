<?php
// Obtener Detalle de Proveedor (sin JSON - preparar datos)

try {
    $proveedorId = (int)($_GET['id_proveedor'] ?? 0);
    
    if ($proveedorId <= 0) {
        $_SESSION['error'] = 'ID de proveedor inválido';
        header('Location: ' . URL_BASE . '/proveedores');
        exit;
    }
    
    // Obtener datos del proveedor
    $sql = "SELECT * FROM tb_proveedores WHERE id_proveedor = ? AND estado_registro = 'ACTIVO'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$proveedorId]);
    $proveedor_dato = $stmt->fetch();
    
    if (!$proveedor_dato) {
        $_SESSION['error'] = 'Proveedor no encontrado';
        header('Location: ' . URL_BASE . '/proveedores');
        exit;
    }
    
    // Estadísticas de compras
    $statsSql = "SELECT 
                    COUNT(*) as total_compras,
                    COALESCE(SUM(total), 0) as total_gastado,
                    COALESCE(AVG(total), 0) as compra_promedio,
                    MAX(fecha_compra) as ultima_compra,
                    MIN(fecha_compra) as primera_compra
                 FROM tb_compras
                 WHERE id_proveedor = ? AND estado_registro = 'ACTIVO'";
    
    $statsStmt = $pdo->prepare($statsSql);
    $statsStmt->execute([$proveedorId]);
    $estadisticas = $statsStmt->fetch();
    
    // Historial de compras
    $comprasSql = "SELECT c.*, p.codigo as producto_codigo, p.nombre as producto_nombre
                   FROM tb_compras c
                   INNER JOIN tb_almacen p ON c.id_producto = p.id_producto
                   WHERE c.id_proveedor = ? AND c.estado_registro = 'ACTIVO'
                   ORDER BY c.fecha_compra DESC
                   LIMIT 20";
    
    $comprasStmt = $pdo->prepare($comprasSql);
    $comprasStmt->execute([$proveedorId]);
    $historial_compras = $comprasStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Productos más comprados
    $productosSql = "SELECT 
                        p.id_producto,
                        p.codigo,
                        p.nombre,
                        COUNT(c.id_compra) as veces_comprado,
                        SUM(c.cantidad) as cantidad_total,
                        SUM(c.total) as monto_total
                     FROM tb_compras c
                     INNER JOIN tb_almacen p ON c.id_producto = p.id_producto
                     WHERE c.id_proveedor = ? AND c.estado_registro = 'ACTIVO'
                     GROUP BY p.id_producto
                     ORDER BY veces_comprado DESC
                     LIMIT 10";
    
    $productosStmt = $pdo->prepare($productosSql);
    $productosStmt->execute([$proveedorId]);
    $productos_mas_comprados = $productosStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al obtener proveedor: " . $e->getMessage());
    $_SESSION['error'] = 'Error al obtener detalle';
    header('Location: ' . URL_BASE . '/proveedores');
    exit;
}

// NO usar echo, print, jsonResponse()
