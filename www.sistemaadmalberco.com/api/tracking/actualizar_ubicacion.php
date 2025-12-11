<?php
/**
 * API: Actualizar Ubicación GPS del Delivery
 * Registra la posición actual del delivery en tiempo real
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../../services/database/config.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $pedido_id = $data['pedido_id'] ?? '';
    $empleado_id = $data['empleado_id'] ?? '';
    $latitud = $data['latitud'] ?? '';
    $longitud = $data['longitud'] ?? '';
    $observaciones = $data['observaciones'] ?? '';
    
    // Validar datos requeridos
    if (empty($pedido_id) || empty($empleado_id) || empty($latitud) || empty($longitud)) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Datos incompletos (pedido_id, empleado_id, latitud, longitud requeridos)'
        ]);
        exit;
    }
    
    // Validar que el pedido existe y está asignado a este delivery
    $stmtCheck = $pdo->prepare("
        SELECT id_pedido, id_empleado_delivery, id_estado 
        FROM tb_pedidos 
        WHERE id_pedido = ?
    ");
    $stmtCheck->execute([$pedido_id]);
    $pedido = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Pedido no encontrado'
        ]);
        exit;
    }
    
    // Verificar que el delivery coincide
    if ($pedido['id_empleado_delivery'] != $empleado_id) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Este pedido no está asignado a este delivery'
        ]);
        exit;
    }
    
    // Determinar estado del tracking
    $estadoTracking = 'EN_CAMINO';
    if ($pedido['id_estado'] == 3) {
        $estadoTracking = 'RECOGIDO';
    } elseif ($pedido['id_estado'] == 4) {
        $estadoTracking = 'EN_CAMINO';
    } elseif ($pedido['id_estado'] == 5) {
        $estadoTracking = 'ENTREGADO';
    }
    
    // Insertar registro de tracking
    $stmt = $pdo->prepare("
        INSERT INTO tb_tracking_delivery 
        (id_pedido, id_empleado, latitud, longitud, estado_tracking, observaciones, fyh_registro)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([
        $pedido_id,
        $empleado_id,
        $latitud,
        $longitud,
        $estadoTracking,
        $observaciones
    ]);
    
    if ($result) {
        $tracking_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'mensaje' => 'Ubicación actualizada correctamente',
            'tracking_id' => (int)$tracking_id,
            'estado_tracking' => $estadoTracking
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al registrar ubicación'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Error en actualizar_ubicacion: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al actualizar ubicación'
    ]);
} catch (Exception $e) {
    error_log("Error en actualizar_ubicacion: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error: ' . $e->getMessage()
    ]);
}
