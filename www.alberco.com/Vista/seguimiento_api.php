<?php
header('Content-Type: application/json');

// Incluir configuración de base de datos
require_once __DIR__ . '/../app/init.php';

try {
    // Aceptar tanto pedidoId (legacy) como nroPedido
    $nroPedido = $_GET['nroPedido'] ?? $_GET['pedidoId'] ?? null;

    if (!$nroPedido) {
        throw new Exception('Número de pedido requerido');
    }

    // Obtener conexión PDO (ya está disponible como $pdo desde config.php via init.php)
    if (!isset($pdo)) {
        throw new Exception('Error de conexión a la base de datos');
    }

    // Buscar pedido por nro_pedido o numero_comanda
    $sql = "SELECT p.*, 
                   CONCAT(c.nombre, ' ', COALESCE(c.apellidos, '')) as cliente_nombre,
                   c.telefono as cliente_telefono,
                   c.direccion as cliente_direccion,
                   es.nombre_estado,
                   es.color as estado_color,
                   CONCAT(e.nombres, ' ', e.apellidos) as delivery_nombres,
                   e.apellidos as delivery_apellidos
            FROM tb_pedidos p
            INNER JOIN tb_clientes c ON p.id_cliente = c.id_cliente
            INNER JOIN tb_estados es ON p.id_estado = es.id_estado
            LEFT JOIN tb_empleados e ON p.id_empleado_delivery = e.id_empleado
            WHERE (p.nro_pedido = ? OR p.numero_comanda = ?)
            AND p.estado_registro = 'ACTIVO'
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $nroPedidoUpper = strtoupper($nroPedido);
    $stmt->execute([$nroPedidoUpper, $nroPedidoUpper]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        throw new Exception('Pedido no encontrado. Verifique el número de pedido.');
    }
    
    $pedidoId = $pedido['id_pedido'];
    
    // Obtener historial de seguimiento
    $historialSql = "SELECT sp.*, es.nombre_estado
                    FROM tb_seguimiento_pedidos sp
                    INNER JOIN tb_estados es ON sp.id_estado = es.id_estado
                    WHERE sp.id_pedido = ?
                    AND sp.estado_registro = 'ACTIVO'
                    ORDER BY sp.fecha_cambio ASC";
    
    $historialStmt = $pdo->prepare($historialSql);
    $historialStmt->execute([$pedidoId]);
    $historial = $historialStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener última posición GPS del delivery
    $ultimaPosicion = null;
    if ($pedido['id_empleado_delivery']) {
        $trackingSql = "SELECT latitud, longitud, velocidad, distancia_restante, 
                              tiempo_estimado_llegada, fecha_registro
                       FROM tb_tracking_delivery
                       WHERE id_pedido = ?
                       ORDER BY fecha_registro DESC
                       LIMIT 1";
        $trackingStmt = $pdo->prepare($trackingSql);
        $trackingStmt->execute([$pedidoId]);
        $ultimaPosicion = $trackingStmt->fetch(PDO::FETCH_ASSOC);
    }

    // Format response
    $response = [
        'success' => true,
        'pedido' => [
            'nro_pedido' => $pedido['nro_pedido'],
            'numero_comanda' => $pedido['numero_comanda'],
            'estado' => $pedido['nombre_estado'],
            'direccion_entrega' => $pedido['direccion_entrega'] ?? $pedido['cliente_direccion'],
            'latitud_entrega' => $pedido['latitud_entrega'],
            'longitud_entrega' => $pedido['longitud_entrega'],
            'fecha_pedido' => date('d/m/Y h:i A', strtotime($pedido['fecha_pedido'])),
            'total' => 'S/ ' . number_format($pedido['total'], 2),
            'repartidor' => !empty($pedido['delivery_nombres']) ? trim($pedido['delivery_nombres']) : 'Por asignar'
        ],
        'seguimiento' => []
    ];
    
    // Agregar última posición GPS si existe
    if ($ultimaPosicion) {
        $response['tracking'] = [
            'latitud' => $ultimaPosicion['latitud'],
            'longitud' => $ultimaPosicion['longitud'],
            'velocidad' => $ultimaPosicion['velocidad'],
            'distancia_restante_km' => $ultimaPosicion['distancia_restante'],
            'tiempo_estimado_minutos' => $ultimaPosicion['tiempo_estimado_llegada'],
            'ultima_actualizacion' => $ultimaPosicion['fecha_registro']
        ];
        
        // Agregar al historial como ubicación actual
        $response['seguimiento'][] = [
            'fecha_estado' => $ultimaPosicion['fecha_registro'],
            'estado' => 'Posición Actual',
            'ubicacion_actual' => $ultimaPosicion['latitud'] . ',' . $ultimaPosicion['longitud'],
            'descripcion' => 'Última posición registrada del delivery'
        ];
    }

    // Agregar historial de estados
    foreach ($historial as $h) {
        $response['seguimiento'][] = [
            'fecha_estado' => $h['fecha_cambio'] ?? date('Y-m-d H:i:s'),
            'estado' => $h['nombre_estado'] ?? 'Actualización',
            'ubicacion_actual' => null,
            'descripcion' => $h['observaciones'] ?? ''
        ];
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
