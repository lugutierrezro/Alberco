<?php
// Controlador de Logout (sin JSON excepto AJAX)

require_once __DIR__ . '/../../services/database/config.php';
require_once __DIR__ . '/../../helpers/actividad_helper.php';

session_start();

// Registrar logout antes de destruir sesión
if (isset($_SESSION['email'])) {
    $_SESSION['sesion_id_usuario'] = $_SESSION['id_usuario'] ?? null;
    registrarLogout($_SESSION['email']);
}

// Destruir sesión
session_unset();
session_destroy();

// Verificar si es petición AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => 'Sesión cerrada correctamente',
        'data' => [
            'redirect' => URL_BASE . '/views/login/'
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
    
} else {
    // Redirección normal
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}
