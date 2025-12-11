<?php
// Obtener Detalle de Categoría (sin JSON - preparar datos)

try {
    $categoriaId = (int)($_GET['id_categoria'] ?? $_GET['id'] ?? 0);
    
    if ($categoriaId <= 0) {
        $_SESSION['error'] = 'ID de categoría inválido';
        header('Location: ' . URL_BASE . '/categorias');
        exit;
    }
    
    // Obtener datos de la categoría
    $sql = "SELECT * FROM tb_categorias 
            WHERE id_categoria = ? AND estado_registro = 'ACTIVO'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$categoriaId]);
    $categoria_dato = $stmt->fetch();
    
    if (!$categoria_dato) {
        $_SESSION['error'] = 'Categoría no encontrada';
        header('Location: ' . URL_BASE . '/categorias');
        exit;
    }
    
    // Obtener productos de esta categoría
    $productosSql = "SELECT p.*, 
                            CASE WHEN p.stock > 0 THEN 'disponible' ELSE 'agotado' END as estado_stock
                     FROM tb_almacen p
                     WHERE p.id_categoria = ? AND p.estado_registro = 'ACTIVO'
                     ORDER BY p.nombre";
    
    $productosStmt = $pdo->prepare($productosSql);
    $productosStmt->execute([$categoriaId]);
    $productos_categoria = $productosStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular estadísticas
    $statsSql = "SELECT 
                    COUNT(*) as total_productos,
                    COALESCE(SUM(stock), 0) as stock_total,
                    SUM(CASE WHEN disponible_venta = 1 THEN 1 ELSE 0 END) as productos_disponibles,
                    COALESCE(AVG(precio_venta), 0) as precio_promedio
                 FROM tb_almacen
                 WHERE id_categoria = ? AND estado_registro = 'ACTIVO'";
    
    $statsStmt = $pdo->prepare($statsSql);
    $statsStmt->execute([$categoriaId]);
    $estadisticas_categoria = $statsStmt->fetch();
    
} catch (PDOException $e) {
    error_log("Error al obtener categoría: " . $e->getMessage());
    $_SESSION['error'] = 'Error al obtener detalle';
    header('Location: ' . URL_BASE . '/categorias');
    exit;
}

// NO usar echo, print, jsonResponse()
