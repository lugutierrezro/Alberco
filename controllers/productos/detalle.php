<?php
// Obtener Detalle de Producto (sin JSON - preparar datos)

try {
    $productoId = (int)($_GET['id_producto'] ?? $_GET['id'] ?? 0);
    
    if ($productoId <= 0) {
        $_SESSION['error'] = 'ID de producto inválido';
        header('Location: ' . URL_BASE . '/views/almacen/');
        exit;
    }
    
    // Obtener datos del producto (incluyendo eliminados)
    $sql = "SELECT p.*, c.nombre_categoria, c.color as categoria_color
            FROM tb_almacen p
            INNER JOIN tb_categorias c ON p.id_categoria = c.id_categoria
            WHERE p.id_producto = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$productoId]);
    $producto_dato = $stmt->fetch();
    
    if (!$producto_dato) {
        $_SESSION['error'] = 'Producto no encontrado';
        header('Location: ' . URL_BASE . '/views/almacen/');
        exit;
    }
    
    // Historial de compras
    $comprasSql = "SELECT c.*, prov.nombre_proveedor, prov.empresa
                   FROM tb_compras c
                   INNER JOIN tb_proveedores prov ON c.id_proveedor = prov.id_proveedor
                   WHERE c.id_producto = ? AND c.estado_registro = 'ACTIVO'
                   ORDER BY c.fecha_compra DESC
                   LIMIT 10";
    
    $comprasStmt = $pdo->prepare($comprasSql);
    $comprasStmt->execute([$productoId]);
    $historial_compras = $comprasStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Estadísticas
    $statsSql = "SELECT 
                    COUNT(*) as total_compras,
                    SUM(cantidad) as cantidad_total_comprada,
                    COALESCE(AVG(precio_compra), 0) as precio_compra_promedio,
                    MAX(fecha_compra) as ultima_compra
                 FROM tb_compras
                 WHERE id_producto = ? AND estado_registro = 'ACTIVO'";
    
    $statsStmt = $pdo->prepare($statsSql);
    $statsStmt->execute([$productoId]);
    $estadisticas_producto = $statsStmt->fetch();
    
} catch (PDOException $e) {
    error_log("Error al obtener producto: " . $e->getMessage());
    $_SESSION['error'] = 'Error al obtener detalle';
    header('Location: ' . URL_BASE . '/views/almacen/');
    exit;
}

// NO usar echo, print, jsonResponse()
