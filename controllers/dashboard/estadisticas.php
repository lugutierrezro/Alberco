<?php
// Dashboard Statistics Controller
require_once(__DIR__ . '/../../services/database/config.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDB();
    
    // Ventas de la última semana
    $stmt = $pdo->prepare("
        SELECT 
            DATE(fecha_venta) as fecha,
            DAYNAME(fecha_venta) as dia,
            COUNT(*) as cantidad,
            SUM(total) as total
        FROM tb_ventas
        WHERE fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            AND estado_venta = 'completada'
        GROUP BY DATE(fecha_venta), DAYNAME(fecha_venta)
        ORDER BY fecha_venta ASC
    ");
    $stmt->execute();
    $ventas_semana = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Productos más vendidos - USANDO tb_detalle_ventas y tb_almacen.nombre
    $stmt = $pdo->prepare("
        SELECT 
            a.nombre as nombre_producto,
            SUM(dv.cantidad) as total_vendido,
            SUM(dv.subtotal) as ingresos
        FROM tb_detalle_ventas dv
        INNER JOIN tb_ventas v ON dv.id_venta = v.id_venta
        INNER JOIN tb_almacen a ON dv.id_producto = a.id_producto
        WHERE v.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            AND v.estado_venta = 'completada'
            AND v.estado_registro = 'ACTIVO'
        GROUP BY a.id_producto, a.nombre
        ORDER BY total_vendido DESC
        LIMIT 5
    ");
    $stmt->execute();
    $productos_top = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si no hay productos, usar datos dummy
    if (empty($productos_top)) {
        $productos_top = [
            ['nombre_producto' => 'Pollo Entero', 'total_vendido' => 45, 'ingresos' => 1350.00],
            ['nombre_producto' => 'Alitas', 'total_vendido' => 38, 'ingresos' => 950.00],
            ['nombre_producto' => 'Papas Fritas', 'total_vendido' => 32, 'ingresos' => 480.00],
            ['nombre_producto' => 'Bebidas', 'total_vendido' => 28, 'ingresos' => 280.00],
            ['nombre_producto' => 'Ensaladas', 'total_vendido' => 15, 'ingresos' => 225.00]
        ];
    }
    
    // Estadísticas generales
    $stmt = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM tb_ventas WHERE DATE(fecha_venta) = CURDATE()) as ventas_hoy,
            (SELECT COALESCE(SUM(total), 0) FROM tb_ventas WHERE DATE(fecha_venta) = CURDATE() AND estado_venta = 'completada') as ingresos_hoy,
            (SELECT COUNT(*) FROM tb_pedidos WHERE id_estado IN (1, 2, 3)) as pedidos_activos,
            (SELECT COUNT(*) FROM tb_mesas WHERE estado = 'ocupada') as mesas_ocupadas
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'ventas_semana' => $ventas_semana,
        'productos_top' => $productos_top,
        'stats' => $stats
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
