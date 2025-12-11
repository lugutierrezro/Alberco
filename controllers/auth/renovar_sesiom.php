<?php
// Renovar tiempo de sesión (AJAX endpoint)

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../services/database/config.php';

if (isset($_SESSION['id_usuario'])) {
    // Regenerar ID de sesión por seguridad
    session_regenerate_id(true);
    
    echo json_encode([
        'success' => true,
        'message' => 'Sesión renovada',
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Sesión no válida'
    ], JSON_UNESCAPED_UNICODE);
}
exit;
