<?php
/**
 * API: Cambiar Estado de Pedido
 * Actualiza el estado del pedido y registra en historial
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
    $estado_id = $data['estado_id'] ?? '';
    $empleado_id = $data['empleado_id'] ?? null;
    $observaciones = $data['observaciones'] ?? '';
    
    if (empty($pedido_id) || empty($estado_id)) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Pedido ID y Estado ID son requeridos'
        ]);
        exit;
    }
    
    // Validar que el pedido existe
    $stmtCheck = $pdo->prepare("SELECT id_pedido, id_estado FROM tb_pedidos WHERE id_pedido = ?");
    $stmtCheck->execute([$pedido_id]);
    $pedidoActual = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedidoActual) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Pedido no encontrado'
        ]);
        exit;
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Actualizar estado del pedido
    $stmt = $pdo->prepare("
        UPDATE tb_pedidos 
        SET id_estado = ?,
            fyh_actualizacion = NOW()
        WHERE id_pedido = ?
    ");
    
    $result = $stmt->execute([$estado_id, $pedido_id]);
    
    if (!$result) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al actualizar el estado'
        ]);
        exit;
    }
    
    // Registrar en auditoría si existe la tabla
    try {
        $stmtAudit = $pdo->prepare("
            INSERT INTO tb_auditoria 
            (tabla_afectada, id_registro_afectado, accion, id_usuario, detalles)
            VALUES ('tb_pedidos', ?, 'CAMBIO_ESTADO', ?, ?)
        ");
        $detalles = "Estado cambiado de {$pedidoActual['id_estado']} a {$estado_id}. " . $observaciones;
        $stmtAudit->execute([$pedido_id, $empleado_id ?? 0, $detalles]);
    } catch (PDOException $e) {
        // Si la tabla de auditoría no existe, continuar
        error_log("No se pudo registrar auditoría: " . $e->getMessage());
    }
    
    // Commit de la transacción
    $pdo->commit();
    
    // Obtener nombre del nuevo estado
    $stmtEstado = $pdo->prepare("SELECT nombre FROM tb_estados WHERE id_estado = ?");
    $stmtEstado->execute([$estado_id]);
    $estado = $stmtEstado->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Estado actualizado correctamente',
        'nuevo_estado' => [
            'id' => (int)$estado_id,
            'nombre' => $estado['nombre'] ?? 'Desconocido'
        ]
    ]);
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error en cambiar_estado: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al actualizar el estado'
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error en cambiar_estado: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error: ' . $e->getMessage()
    ]);
}
