<?php
// Historial de Seguimiento de Pedido (sin JSON - preparar datos)

try {
    $pedidoId = (int)($_GET['id_pedido'] ?? 0);
    
    if ($pedidoId <= 0) {
        $historial_seguimiento = [];
    } else {
        $sql = "SELECT s.*, u.username as usuario_nombre
                FROM tb_pedido_seguimiento s
                INNER JOIN tb_usuarios u ON s.id_usuario = u.id_usuario
                WHERE s.id_pedido = ?
                ORDER BY s.fyh_registro ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$pedidoId]);
        $historial_seguimiento = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    error_log("Error al obtener historial: " . $e->getMessage());
    $historial_seguimiento = [];
}

// NO usar echo, print, jsonResponse()
