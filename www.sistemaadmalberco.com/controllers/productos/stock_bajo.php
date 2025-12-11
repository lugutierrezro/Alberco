<?php
// Productos con Stock Bajo (sin JSON - preparar datos)

try {
    $sql = "SELECT p.*, c.nombre_categoria, c.color as categoria_color
            FROM tb_almacen p
            INNER JOIN tb_categorias c ON p.id_categoria = c.id_categoria
            WHERE p.estado_registro = 'ACTIVO'
            AND p.stock <= p.stock_minimo
            ORDER BY (p.stock_minimo - p.stock) DESC, p.nombre ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $productos_stock_bajo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular criticidad
    foreach ($productos_stock_bajo as &$producto) {
        $diferencia = $producto['stock_minimo'] - $producto['stock'];
        if ($producto['stock'] <= 0) {
            $producto['criticidad'] = 'CRITICO';
            $producto['criticidad_color'] = 'danger';
        } elseif ($diferencia >= 10) {
            $producto['criticidad'] = 'ALTO';
            $producto['criticidad_color'] = 'danger';
        } elseif ($diferencia >= 5) {
            $producto['criticidad'] = 'MEDIO';
            $producto['criticidad_color'] = 'warning';
        } else {
            $producto['criticidad'] = 'BAJO';
            $producto['criticidad_color'] = 'info';
        }
    }
    
} catch (PDOException $e) {
    error_log("Error al obtener stock bajo: " . $e->getMessage());
    $productos_stock_bajo = [];
}

// NO usar echo, print, jsonResponse()
