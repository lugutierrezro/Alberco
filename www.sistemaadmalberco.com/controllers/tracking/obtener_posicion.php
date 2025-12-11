<?php
// Obtener Posición del Delivery (sin JSON - preparar datos)

try {
    $pedidoId = (int)($_GET['id_pedido'] ?? 0);
    
    if ($pedidoId <= 0) {
        $posicion_tracking = null;
    } else {
        $sql = "SELECT t.*, e.nombres, e.apellidos, p.codigo_pedido
                FROM tb_tracking_delivery t
                INNER JOIN tb_empleados e ON t.id_empleado = e.id_empleado
                INNER JOIN tb_pedidos p ON t.id_pedido = p.id_pedido
                WHERE t.id_pedido = ?
                ORDER BY t.fyh_registro DESC
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$pedidoId]);
        $posicion_tracking = $stmt->fetch();
    }
    
} catch (PDOException $e) {
    error_log("Error al obtener posición: " . $e->getMessage());
    $posicion_tracking = null;
}

// NO usar echo, print, jsonResponse()
