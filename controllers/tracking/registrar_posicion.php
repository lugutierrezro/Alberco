<?php
/**
 * API para registrar posición GPS del delivery
 * Endpoint: POST /controllers/tracking/registrar_posicion.php
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../services/database/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Obtener datos JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos requeridos
    if (!isset($input['id_pedido']) || !isset($input['latitud']) || !isset($input['longitud'])) {
        throw new Exception('Faltan datos requeridos: id_pedido, latitud, longitud');
    }
    
    $idPedido = (int)$input['id_pedido'];
    $latitud = (float)$input['latitud'];
    $longitud = (float)$input['longitud'];
    
    // Datos opcionales
    $altitud = isset($input['altitud']) ? (float)$input['altitud'] : null;
    $precision = isset($input['precision']) ? (float)$input['precision'] : null;
    $velocidad = isset($input['velocidad']) ? (float)$input['velocidad'] : null;
    $rumbo = isset($input['rumbo']) ? (float)$input['rumbo'] : null;
    $bateria = isset($input['bateria']) ? (int)$input['bateria'] : null;
    $modoTransporte = isset($input['modo_transporte']) ? $input['modo_transporte'] : 'moto';
    
    // Validar que el pedido existe y es delivery
    $checkSql = "SELECT p.id_pedido, p.id_empleado_delivery, p.latitud_entrega, p.longitud_entrega
                FROM tb_pedidos p
                WHERE p.id_pedido = ?
                AND p.tipo_pedido = 'delivery'
                AND p.estado_registro = 'ACTIVO'";
    
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$idPedido]);
    $pedido = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        throw new Exception('Pedido no encontrado o no es de tipo delivery');
    }
    
    $idEmpleadoDelivery = $pedido['id_empleado_delivery'];
    
    if (!$idEmpleadoDelivery) {
        throw new Exception('El pedido no tiene un delivery asignado');
    }
    
    // Calcular distancia restante al destino
    $distanciaRestante = null;
    $tiempoEstimado = null;
    
    if ($pedido['latitud_entrega'] && $pedido['longitud_entrega']) {
        $distanciaRestante = calcularDistancia(
            $latitud, 
            $longitud, 
            $pedido['latitud_entrega'], 
            $pedido['longitud_entrega']
        );
        
        // Estimar tiempo (asumiendo velocidad promedio de 30 km/h si no se proporciona)
        $velocidadPromedio = $velocidad ?? 30;
        $tiempoEstimado = ($distanciaRestante / $velocidadPromedio) * 60; // en minutos
    }
    
    // Insertar registro de tracking
    $insertSql = "INSERT INTO tb_tracking_delivery 
                 (id_pedido, id_empleado_delivery, latitud, longitud, altitud, 
                  precision, velocidad, rumbo, bateria_porcentaje, modo_transporte,
                  distancia_restante, tiempo_estimado_llegada, fecha_registro)
                 VALUES 
                 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $insertStmt = $pdo->prepare($insertSql);
    $insertStmt->execute([
        $idPedido,
        $idEmpleadoDelivery,
        $latitud,
        $longitud,
        $altitud,
        $precision,
        $velocidad,
        $rumbo,
        $bateria,
        $modoTransporte,
        $distanciaRestante,
        $tiempoEstimado
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Posición registrada correctamente',
        'data' => [
            'id_tracking' => $pdo->lastInsertId(),
            'distancia_restante_km' => $distanciaRestante ? round($distanciaRestante, 2) : null,
            'tiempo_estimado_minutos' => $tiempoEstimado ? ceil($tiempoEstimado) : null
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Calcular distancia entre dos puntos usando fórmula de Haversine
 * @return float Distancia en kilómetros
 */
function calcularDistancia($lat1, $lon1, $lat2, $lon2) {
    $radioTierra = 6371; // Radio de la Tierra en km
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distancia = $radioTierra * $c;
    
    return $distancia;
}
