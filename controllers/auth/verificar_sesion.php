<?php
// Verificar si la sesión está activa (AJAX endpoint - mantiene JSON)

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../services/database/config.php';

$isAuthenticated = isset($_SESSION['id_usuario']) && !empty($_SESSION['id_usuario']);

if ($isAuthenticated) {
    echo json_encode([
        'success' => true,
        'message' => 'Sesión activa',
        'data' => [
            'user' => [
                'id' => $_SESSION['id_usuario'],
                'nombres' => $_SESSION['nombres'] ?? '',
                'email' => $_SESSION['email'] ?? '',
                'rol' => $_SESSION['rol'] ?? ''
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Sesión inactiva'
    ], JSON_UNESCAPED_UNICODE);
}
exit;
