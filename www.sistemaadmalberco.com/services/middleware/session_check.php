<?php
/**
 * Middleware de Verificación de Sesión
 * Sistema Alberco
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si hay sesión activa
if (!isset($_SESSION['sesion']) || $_SESSION['sesion'] !== 'ok') {
    // Redirigir al login si no está autenticado
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

// Verificar que las variables de sesión necesarias existan
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    // Sesión corrupta, limpiar y redirigir
    session_unset();
    session_destroy();
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

// Variables globales para uso en las vistas
$id_usuario_sesion = $_SESSION['user_id'];
$nombre_sesion = $_SESSION['user_name'] ?? '';
$email_sesion = $_SESSION['user_email'];
$rol_sesion = $_SESSION['user_role'] ?? '';
$rol_id_sesion = $_SESSION['user_role_id'] ?? 0;

// Verificar tiempo de inactividad (30 minutos)
$tiempo_inactividad = 1800; // 30 minutos en segundos

if (isset($_SESSION['ultimo_acceso'])) {
    $tiempo_transcurrido = time() - $_SESSION['ultimo_acceso'];
    
    if ($tiempo_transcurrido > $tiempo_inactividad) {
        // Sesión expirada por inactividad
        session_unset();
        session_destroy();
        
        // Si es petición AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Sesión expirada por inactividad',
                'session_expired' => true
            ]);
            exit;
        }
        
        // Petición normal
        header('Location: ' . URL_BASE . '/views/login/?timeout=1');
        exit;
    }
}

// Actualizar último acceso
$_SESSION['ultimo_acceso'] = time();
?>
