<?php
/**
 * API: Detalle Completo de un Pedido
 * Incluye productos y tracking
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
    $pedido_id = $_GET['pedido_id'] ?? '';
    
    if (empty($pedido_id)) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'ID de pedido requerido'
        ]);
        exit;
    }
    
    // Obtener pedido
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            e.nombre as nombre_estado,
            e.color as color_estado,
            c.nombre as cliente_nombre,
            c.apellidos as cliente_apellidos,
            c.telefono as cliente_telefono,
            c.email as cliente_email,
            m.numero_mesa as mesa_numero,
            m.capacidad as mesa_capacidad,
            emp.nombre as empleado_delivery_nombre,
            emp.telefono as empleado_delivery_telefono
        FROM tb_pedidos p
        INNER JOIN tb_estados e ON p.id_estado = e.id_estado
        LEFT JOIN tb_clientes c ON p.id_cliente = c.id_cliente
        LEFT JOIN tb_mesas m ON p.id_mesa = m.id_mesa
        LEFT JOIN tb_empleados emp ON p.id_empleado_delivery = emp.id_empleado
        WHERE p.id_pedido = ?
    ");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Pedido no encontrado'
        ]);
        exit;
    }
    
    // Formatear datos básicos
    $pedido['id_pedido'] = (int)$pedido['id_pedido'];
    $pedido['id_estado'] = (int)$pedido['id_estado'];
    $pedido['subtotal'] = (float)$pedido['subtotal'];
    $pedido['igv'] = (float)$pedido['igv'];
    $pedido['total'] = (float)$pedido['total'];
    $pedido['latitud'] = $pedido['latitud'] ? (float)$pedido['latitud'] : null;
    $pedido['longitud'] = $pedido['longitud'] ? (float)$pedido['longitud'] : null;
    
    // Obtener detalles del pedido (productos)
    $stmt = $pdo->prepare("
        SELECT 
            d.id_detalle_pedido,
            d.id_producto,
            d.cantidad,
            d.precio_unitario,
            d.subtotal,
            d.especificaciones,
            pr.nombre as producto_nombre,
            pr.descripcion as producto_descripcion,
            pr.imagen as producto_imagen
        FROM tb_detalle_pedido d
        INNER JOIN tb_productos pr ON d.id_producto = pr.id_producto
        WHERE d.id_pedido = ?
    ");
    $stmt->execute([$pedido_id]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear detalles
    foreach ($detalles as &$detalle) {
        $detalle['id_detalle_pedido'] = (int)$detalle['id_detalle_pedido'];
        $detalle['id_producto'] = (int)$detalle['id_producto'];
        $detalle['cantidad'] = (int)$detalle['cantidad'];
        $detalle['precio_unitario'] = (float)$detalle['precio_unitario'];
        $detalle['subtotal'] = (float)$detalle['subtotal'];
        
        // URL completa de imagen
        if ($detalle['producto_imagen']) {
            $detalle['producto_imagen_url'] = URL_BASE . '/' . $detalle['producto_imagen'];
        } else {
            $detalle['producto_imagen_url'] = null;
        }
    }
    
    $pedido['detalles'] = $detalles;
    
    // Obtener historial de tracking (si es delivery)
    if ($pedido['tipo_pedido'] === 'DELIVERY') {
        $stmt = $pdo->prepare("
            SELECT 
                id_tracking,
                latitud,
                longitud,
                estado_tracking,
                observaciones,
                fyh_registro
            FROM tb_tracking_delivery
            WHERE id_pedido = ?
            ORDER BY fyh_registro DESC
        ");
        $stmt->execute([$pedido_id]);
        $tracking = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear tracking
        foreach ($tracking as &$t) {
            $t['id_tracking'] = (int)$t['id_tracking'];
            $t['latitud'] = (float)$t['latitud'];
            $t['longitud'] = (float)$t['longitud'];
        }
        
        $pedido['tracking'] = $tracking;
    } else {
        $pedido['tracking'] = [];
    }
    
    echo json_encode([
        'success' => true,
        'pedido' => $pedido
    ]);
    
} catch (PDOException $e) {
    error_log("Error en detalle pedido: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al obtener detalle del pedido'
    ]);
} catch (Exception $e) {
    error_log("Error en detalle pedido: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error: ' . $e->getMessage()
    ]);
}
