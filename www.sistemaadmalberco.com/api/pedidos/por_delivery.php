<?php
/**
 * API: Listar Pedidos Asignados a un Delivery
 * Endpoint para app de delivery
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../../services/database/config.php';

try {
    $empleado_id = $_GET['empleado_id'] ?? '';
    
    if (empty($empleado_id)) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'ID de empleado requerido',
            'pedidos' => []
        ]);
        exit;
    }
    
    // Pedidos asignados al delivery que están LISTO o EN_CAMINO
    $stmt = $pdo->prepare("
        SELECT 
            p.id_pedido,
            p.nro_pedido,
            p.tipo_pedido,
            p.id_estado,
            p.direccion_entrega,
            p.referencia,
            p.latitud,
            p.longitud,
            p.subtotal,
            p.igv,
            p.total,
            p.observaciones,
            p.tiempo_estimado_min,
            p.calificacion,
            p.fyh_creacion,
            p.fyh_actualizacion,
            e.nombre as nombre_estado,
            e.color as color_estado,
            c.nombre as cliente_nombre,
            c.apellidos as cliente_apellidos,
            c.telefono as cliente_telefono,
            emp.nombre as empleado_delivery_nombre,
            emp.telefono as empleado_delivery_telefono
        FROM tb_pedidos p
        INNER JOIN tb_estados e ON p.id_estado = e.id_estado
        LEFT JOIN tb_clientes c ON p.id_cliente = c.id_cliente
        LEFT JOIN tb_empleados emp ON p.id_empleado_delivery = emp.id_empleado
        WHERE p.id_empleado_delivery = ?
        AND p.id_estado IN (3, 4)
        AND p.tipo_pedido = 'DELIVERY'
        ORDER BY p.fyh_creacion ASC
    ");
    $stmt->execute([$empleado_id]);
    
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos y agregar tracking
    foreach ($pedidos as &$pedido) {
        $pedido['id_pedido'] = (int)$pedido['id_pedido'];
        $pedido['id_estado'] = (int)$pedido['id_estado'];
        $pedido['subtotal'] = (float)$pedido['subtotal'];
        $pedido['igv'] = (float)$pedido['igv'];
        $pedido['total'] = (float)$pedido['total'];
        $pedido['latitud'] = $pedido['latitud'] ? (float)$pedido['latitud'] : null;
        $pedido['longitud'] = $pedido['longitud'] ? (float)$pedido['longitud'] : null;
        $pedido['calificacion'] = $pedido['calificacion'] ? (int)$pedido['calificacion'] : null;
        
        // Obtener último tracking
        $stmtTracking = $pdo->prepare("
            SELECT latitud, longitud, fyh_registro
            FROM tb_tracking_delivery
            WHERE id_pedido = ?
            ORDER BY fyh_registro DESC
            LIMIT 1
        ");
        $stmtTracking->execute([$pedido['id_pedido']]);
        $tracking = $stmtTracking->fetch(PDO::FETCH_ASSOC);
        
        $pedido['ultimo_tracking'] = $tracking ? [
            'latitud' => (float)$tracking['latitud'],
            'longitud' => (float)$tracking['longitud'],
            'fyh_registro' => $tracking['fyh_registro']
        ] : null;
    }
    
    echo json_encode([
        'success' => true,
        'total' => count($pedidos),
        'pedidos' => $pedidos
    ]);
    
} catch (PDOException $e) {
    error_log("Error en por_delivery: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al obtener pedidos',
        'pedidos' => []
    ]);
} catch (Exception $e) {
    error_log("Error en por_delivery: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error: ' . $e->getMessage(),
        'pedidos' => []
    ]);
}
