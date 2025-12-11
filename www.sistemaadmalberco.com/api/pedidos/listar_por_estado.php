<?php
/**
 * API: Listar Pedidos por Estado
 * Endpoint para app de cocina y gestión de pedidos
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
    $tipo = $_GET['tipo'] ?? '';
    $estado_id = $_GET['estado_id'] ?? '';
    
    if ($tipo === 'cocina') {
        // Pedidos para cocina: PENDIENTE, EN_PREPARACION, LISTO
        $stmt = $pdo->prepare("
            SELECT 
                p.id_pedido,
                p.nro_pedido,
                p.tipo_pedido,
                p.id_estado,
                p.direccion_entrega,
                p.referencia,
                p.subtotal,
                p.igv,
                p.total,
                p.observaciones,
                p.tiempo_estimado_min,
                p.fyh_creacion,
                p.fyh_actualizacion,
                e.nombre as nombre_estado,
                e.color as color_estado,
                c.nombre as cliente_nombre,
                c.telefono as cliente_telefono,
                m.numero_mesa as mesa_numero,
                m.capacidad as mesa_capacidad
            FROM tb_pedidos p
            INNER JOIN tb_estados e ON p.id_estado = e.id_estado
            LEFT JOIN tb_clientes c ON p.id_cliente = c.id_cliente
            LEFT JOIN tb_mesas m ON p.id_mesa = m.id_mesa
            WHERE p.id_estado IN (1, 2, 3)
            AND p.tipo_pedido IN ('DELIVERY', 'RECOJO', 'MESA')
            ORDER BY 
                FIELD(p.id_estado, 1, 2, 3),
                p.fyh_creacion ASC
        ");
        $stmt->execute();
        
    } elseif (!empty($estado_id)) {
        // Filtrar por estado específico
        $stmt = $pdo->prepare("
            SELECT 
                p.id_pedido,
                p.nro_pedido,
                p.tipo_pedido,
                p.id_estado,
                p.direccion_entrega,
                p.referencia,
                p.subtotal,
                p.igv,
                p.total,
                p.observaciones,
                p.tiempo_estimado_min,
                p.fyh_creacion,
                p.fyh_actualizacion,
                e.nombre as nombre_estado,
                e.color as color_estado,
                c.nombre as cliente_nombre,
                c.telefono as cliente_telefono,
                m.numero_mesa as mesa_numero
            FROM tb_pedidos p
            INNER JOIN tb_estados e ON p.id_estado = e.id_estado
            LEFT JOIN tb_clientes c ON p.id_cliente = c.id_cliente
            LEFT JOIN tb_mesas m ON p.id_mesa = m.id_mesa
            WHERE p.id_estado = ?
            ORDER BY p.fyh_creacion ASC
        ");
        $stmt->execute([$estado_id]);
        
    } else {
        // Todos los pedidos activos (no entregados ni cancelados)
        $stmt = $pdo->prepare("
            SELECT 
                p.id_pedido,
                p.nro_pedido,
                p.tipo_pedido,
                p.id_estado,
                p.direccion_entrega,
                p.referencia,
                p.subtotal,
                p.igv,
                p.total,
                p.observaciones,
                p.tiempo_estimado_min,
                p.fyh_creacion,
                p.fyh_actualizacion,
                e.nombre as nombre_estado,
                e.color as color_estado,
                c.nombre as cliente_nombre,
                c.telefono as cliente_telefono,
                m.numero_mesa as mesa_numero
            FROM tb_pedidos p
            INNER JOIN tb_estados e ON p.id_estado = e.id_estado
            LEFT JOIN tb_clientes c ON p.id_cliente = c.id_cliente
            LEFT JOIN tb_mesas m ON p.id_mesa = m.id_mesa
            WHERE p.id_estado NOT IN (5, 6)
            ORDER BY p.fyh_creacion ASC
        ");
        $stmt->execute();
    }
    
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos
    foreach ($pedidos as &$pedido) {
        $pedido['id_pedido'] = (int)$pedido['id_pedido'];
        $pedido['id_estado'] = (int)$pedido['id_estado'];
        $pedido['subtotal'] = (float)$pedido['subtotal'];
        $pedido['igv'] = (float)$pedido['igv'];
        $pedido['total'] = (float)$pedido['total'];
        $pedido['tiempo_estimado_min'] = $pedido['tiempo_estimado_min'] ? (int)$pedido['tiempo_estimado_min'] : null;
    }
    
    echo json_encode([
        'success' => true,
        'total' => count($pedidos),
        'pedidos' => $pedidos
    ]);
    
} catch (PDOException $e) {
    error_log("Error en listar_por_estado: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al obtener pedidos',
        'pedidos' => []
    ]);
} catch (Exception $e) {
    error_log("Error en listar_por_estado: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error: ' . $e->getMessage(),
        'pedidos' => []
    ]);
}
