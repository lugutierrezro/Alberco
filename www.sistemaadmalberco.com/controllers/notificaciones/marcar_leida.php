<?php
// Marcar Notificación como Leída (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/notificaciones');
    exit;
}

try {
    if (isset($_POST['id_notificacion'])) {
        // Marcar una notificación específica
        $notificacionId = (int)$_POST['id_notificacion'];
        
        $updateSql = "UPDATE tb_notificaciones 
                      SET leida = 1, fecha_lectura = NOW() 
                      WHERE id_notificacion = ? AND id_usuario = ?";
        
        $stmt = $pdo->prepare($updateSql);
        $result = $stmt->execute([$notificacionId, $_SESSION['id_usuario']]);
        
        if ($result) {
            $_SESSION['success'] = 'Notificación marcada como leída';
        } else {
            $_SESSION['error'] = 'Error al marcar notificación';
        }
        
    } elseif (isset($_POST['todas']) && $_POST['todas'] === '1') {
        // Marcar todas como leídas
        $updateAllSql = "UPDATE tb_notificaciones 
                         SET leida = 1, fecha_lectura = NOW() 
                         WHERE id_usuario = ? AND leida = 0";
        
        $stmt = $pdo->prepare($updateAllSql);
        $result = $stmt->execute([$_SESSION['id_usuario']]);
        
        if ($result) {
            $_SESSION['success'] = 'Todas las notificaciones marcadas como leídas';
        } else {
            $_SESSION['error'] = 'Error al marcar notificaciones';
        }
        
    } else {
        $_SESSION['error'] = 'Parámetros inválidos';
    }
    
    header('Location: ' . URL_BASE . '/notificaciones');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al marcar notificación: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/notificaciones');
    exit;
}
