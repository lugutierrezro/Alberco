<?php
// Contar Notificaciones No Leídas (sin JSON - preparar datos)

if (!isset($_SESSION['id_usuario'])) {
    $total_notificaciones_no_leidas = 0;
} else {
    try {
        $sql = "SELECT COUNT(*) as total 
                FROM tb_notificaciones 
                WHERE id_usuario = ? AND leida = 0";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['id_usuario']]);
        $result = $stmt->fetch();
        
        $total_notificaciones_no_leidas = $result['total'] ?? 0;
        
    } catch (PDOException $e) {
        error_log("Error al contar notificaciones: " . $e->getMessage());
        $total_notificaciones_no_leidas = 0;
    }
}

// NO usar echo, print, jsonResponse()
