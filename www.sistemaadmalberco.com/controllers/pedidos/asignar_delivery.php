<?php
// Asignar Delivery a Pedido
require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/pedidos');
    exit;
}

try {
    $pedidoId = (int)($_POST['id_pedido'] ?? 0);
    $empleadoId = (int)($_POST['id_empleado_delivery'] ?? 0);
    
    if ($pedidoId <= 0 || $empleadoId <= 0) {
        $_SESSION['error'] = 'Datos incompletos';
        header('Location: ' . URL_BASE . '/views/pedidos/show.php?id=' . $pedidoId);
        exit;
    }
    
    // Verificar que el empleado sea delivery y esté activo
    $checkSql = "SELECT e.id_empleado, e.nombres, e.apellidos
                 FROM tb_empleados e
                 INNER JOIN tb_usuarios u ON e.id_empleado = u.id_empleado
                 INNER JOIN tb_roles r ON u.id_rol = r.id_rol
                 WHERE e.id_empleado = ? 
                 AND r.rol = 'DELIVERY' 
                 AND e.estado_laboral = 'ACTIVO'
                 AND e.estado_registro = 'ACTIVO'";
    
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$empleadoId]);
    $empleado = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$empleado) {
        $_SESSION['error'] = 'El empleado seleccionado no es delivery o no está disponible';
        header('Location: ' . URL_BASE . '/views/pedidos/show.php?id=' . $pedidoId);
        exit;
    }
    
    // Verificar que el pedido sea tipo delivery
    $checkPedidoSql = "SELECT tipo_pedido FROM tb_pedidos WHERE id_pedido = ? AND estado_registro = 'ACTIVO'";
    $checkPedidoStmt = $pdo->prepare($checkPedidoSql);
    $checkPedidoStmt->execute([$pedidoId]);
    $pedido = $checkPedidoStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido || $pedido['tipo_pedido'] !== 'delivery') {
        $_SESSION['error'] = 'El pedido no es de tipo delivery';
        header('Location: ' . URL_BASE . '/views/pedidos/show.php?id=' . $pedidoId);
        exit;
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Asignar delivery y cambiar estado a "En Camino" (id_estado = 4)
    $updateSql = "UPDATE tb_pedidos 
                  SET id_empleado_delivery = :empleado, 
                      id_estado = 4,
                      fyh_actualizacion = NOW() 
                  WHERE id_pedido = :pedido";
    
    $stmt = $pdo->prepare($updateSql);
    $result = $stmt->execute([
        ':empleado' => $empleadoId,
        ':pedido' => $pedidoId
    ]);
    
    if ($result && $stmt->rowCount() > 0) {
        // Registrar en seguimiento
        $historySql = "INSERT INTO tb_seguimiento_pedidos 
                       (id_pedido, id_estado, fecha_cambio, observaciones, id_usuario, fyh_creacion)
                       VALUES (?, 4, NOW(), ?, ?, NOW())";
        $pdo->prepare($historySql)->execute([
            $pedidoId, 
            'Delivery asignado: ' . $empleado['nombres'] . ' ' . $empleado['apellidos'], 
            $_SESSION['id_usuario']
        ]);
        
        // Crear notificación para el delivery
        try {
            $notifSql = "INSERT INTO tb_notificaciones 
                        (id_pedido, id_usuario_destino, tipo, titulo, mensaje, fecha_notificacion, fyh_creacion)
                        SELECT ?, u.id_usuario, 'delivery_asignado', 'Nuevo pedido asignado', 
                               CONCAT('Se te ha asignado el pedido #', p.nro_pedido), NOW(), NOW()
                        FROM tb_pedidos p
                        INNER JOIN tb_usuarios u ON u.id_empleado = ?
                        WHERE p.id_pedido = ?";
            $pdo->prepare($notifSql)->execute([$pedidoId, $empleadoId, $pedidoId]);
        } catch (PDOException $e) {
            // Si falla la notificación, no afecta la transacción principal
            error_log("Error al crear notificación: " . $e->getMessage());
        }
        
        $pdo->commit();
        $_SESSION['success'] = 'Delivery asignado correctamente';
    } else {
        $pdo->rollBack();
        $_SESSION['error'] = 'No se pudo asignar el delivery';
    }
    
    header('Location: ' . URL_BASE . '/views/pedidos/show.php?id=' . $pedidoId);
    exit;
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error al asignar delivery: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/views/pedidos/');
    exit;
}
