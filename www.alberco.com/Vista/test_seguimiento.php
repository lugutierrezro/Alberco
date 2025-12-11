<?php
/**
 * Test de seguimiento API
 * Accede a: http://localhost/www.alberco.com/Vista/test_seguimiento.php?nroPedido=PED-2025-000007
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test de Seguimiento API</h2>";

// Incluir init
require_once __DIR__ . '/../app/init.php';

$nroPedido = $_GET['nroPedido'] ?? 'PED-2025-000007';

echo "<p><strong>Buscando pedido:</strong> $nroPedido</p>";

try {
    // Verificar conexión
    echo "<p>✓ Conexión PDO establecida</p>";
    echo "<p>Tipo de conexión: " . get_class($pdo) . "</p>";
    
    // Buscar pedido
    $sql = "SELECT p.*, 
                   CONCAT(c.nombre, ' ', COALESCE(c.apellidos, '')) as cliente_nombre,
                   c.telefono as cliente_telefono,
                   c.direccion as cliente_direccion,
                   es.nombre_estado,
                   es.color as estado_color,
                   CONCAT(e.nombres, ' ', e.apellidos) as delivery_nombres
            FROM tb_pedidos p
            INNER JOIN tb_clientes c ON p.id_cliente = c.id_cliente
            INNER JOIN tb_estados es ON p.id_estado = es.id_estado
            LEFT JOIN tb_empleados e ON p.id_empleado_delivery = e.id_empleado
            WHERE (p.nro_pedido = ? OR p.numero_comanda = ?)
            AND p.estado_registro = 'ACTIVO'
            LIMIT 1";
    
    echo "<p>✓ SQL preparado</p>";
    
    $stmt = $pdo->prepare($sql);
    $nroPedidoUpper = strtoupper($nroPedido);
    $stmt->execute([$nroPedidoUpper, $nroPedidoUpper]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        echo "<p style='color: red;'>✗ Pedido no encontrado</p>";
        
        // Listar pedidos disponibles
        $listSql = "SELECT nro_pedido, numero_comanda, tipo_pedido FROM tb_pedidos WHERE estado_registro = 'ACTIVO' ORDER BY id_pedido DESC LIMIT 5";
        $listStmt = $pdo->prepare($listSql);
        $listStmt->execute();
        $pedidos = $listStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Pedidos disponibles:</strong></p>";
        echo "<ul>";
        foreach ($pedidos as $p) {
            echo "<li><a href='?nroPedido={$p['nro_pedido']}'>{$p['nro_pedido']}</a> / {$p['numero_comanda']} ({$p['tipo_pedido']})</li>";
        }
        echo "</ul>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ Pedido encontrado</p>";
    echo "<pre>";
    print_r($pedido);
    echo "</pre>";
    
    // Obtener historial
    $historialSql = "SELECT sp.*, es.nombre_estado
                    FROM tb_seguimiento_pedidos sp
                    INNER JOIN tb_estados es ON sp.id_estado = es.id_estado
                    WHERE sp.id_pedido = ?
                    AND sp.estado_registro = 'ACTIVO'
                    ORDER BY sp.fecha_cambio ASC";
    
    $historialStmt = $pdo->prepare($historialSql);
    $historialStmt->execute([$pedido['id_pedido']]);
    $historial = $historialStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p style='color: green;'>✓ Historial obtenido (" . count($historial) . " registros)</p>";
    echo "<pre>";
    print_r($historial);
    echo "</pre>";
    
    // Probar JSON
    $response = [
        'success' => true,
        'pedido' => [
            'nro_pedido' => $pedido['nro_pedido'],
            'numero_comanda' => $pedido['numero_comanda'],
            'estado' => $pedido['nombre_estado'],
            'direccion_entrega' => $pedido['direccion_entrega'] ?? $pedido['cliente_direccion'],
            'fecha_pedido' => date('d/m/Y h:i A', strtotime($pedido['fecha_pedido'])),
            'total' => 'S/ ' . number_format($pedido['total'], 2),
            'repartidor' => !empty($pedido['delivery_nombres']) ? trim($pedido['delivery_nombres']) : 'Por asignar'
        ],
        'seguimiento' => []
    ];
    
    foreach ($historial as $h) {
        $response['seguimiento'][] = [
            'fecha_estado' => $h['fecha_cambio'] ?? date('Y-m-d H:i:s'),
            'estado' => $h['nombre_estado'] ?? 'Actualización',
            'ubicacion_actual' => null,
            'descripcion' => $h['observaciones'] ?? ''
        ];
    }
    
    echo "<h3>JSON Response:</h3>";
    echo "<pre>";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "</pre>";
    
    echo "<p style='color: green; font-weight: bold;'>✓ TODO FUNCIONA CORRECTAMENTE</p>";
    echo "<p><a href='seguimiento_api.php?nroPedido=$nroPedido' target='_blank'>Probar API real</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error de base de datos:</p>";
    echo "<pre>";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error:</p>";
    echo "<pre>";
    echo $e->getMessage();
    echo "</pre>";
}
