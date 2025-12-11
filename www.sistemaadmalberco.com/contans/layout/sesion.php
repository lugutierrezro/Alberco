<?php
/**
 * Verificación de Sesión
 * Middleware de autenticación
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar configuración
require_once(__DIR__ . '/../../services/database/config.php');

// Verificar si hay sesión activa
if (!isset($_SESSION['sesion']) || $_SESSION['sesion'] !== 'ok') {
    // Solo redirigir si no se han enviado headers
    if (!headers_sent()) {
        header('Location: ' . URL_BASE . '/views/login/');
        exit;
    }
}

// Verificar que las variables de sesión necesarias existan
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    // Sesión corrupta, limpiar y redirigir
    if (!headers_sent()) {
        session_unset();
        session_destroy();
        header('Location: ' . URL_BASE . '/views/login/');
        exit;
    }
}

// Opcional: Revalidar usuario en base de datos (cada cierto tiempo)
// Solo si quieres verificar que el usuario sigue activo
$revalidar = false; // Cambiar a true si deseas revalidación constante

if ($revalidar) {
    try {
        $sql = "SELECT u.id_usuario, u.email, u.username, 
                       r.rol, r.id_rol,
                       e.nombres, e.apellidos
                FROM tb_usuarios u
                INNER JOIN tb_roles r ON u.id_rol = r.id_rol
                LEFT JOIN tb_empleados e ON u.id_empleado = e.id_empleado
                WHERE u.id_usuario = :user_id 
                AND u.email = :email
                AND u.estado_registro = 'ACTIVO'
                AND u.bloqueado = 0";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':email' => $_SESSION['user_email']
        ]);
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            // Usuario no existe o fue desactivado
            if (!headers_sent()) {
                session_unset();
                session_destroy();
                header('Location: ' . URL_BASE . '/views/login/');
                exit;
            }
        }
        
        // Actualizar datos de sesión por si cambiaron
        $_SESSION['user_names'] = $usuario['nombres'] . ' ' . ($usuario['apellidos'] ?? '');
        $_SESSION['user_role'] = $usuario['rol'];
        $_SESSION['user_role_id'] = $usuario['id_rol'];
        $_SESSION['user_name'] = $usuario['nombres'];
        
    } catch (PDOException $e) {
        error_log("Error al revalidar sesión: " . $e->getMessage());
        // No cerrar sesión en caso de error de BD
    }
}

// Variables globales para uso en las vistas (opcional)
$id_usuario_sesion = $_SESSION['user_id'] ?? 0;
$nombres_sesion = $_SESSION['user_names'] ?? '';
$nombre_sesion = $_SESSION['user_name'] ?? '';
$email_sesion = $_SESSION['user_email'] ?? '';
$rol_sesion = $_SESSION['user_role'] ?? '';
$rol_id_sesion = $_SESSION['user_role_id'] ?? 0;

// Verificar tiempo de inactividad (opcional - 30 minutos)
$tiempo_inactividad = 1800; // 30 minutos en segundos

if (isset($_SESSION['ultimo_acceso'])) {
    $tiempo_transcurrido = time() - $_SESSION['ultimo_acceso'];
    
    if ($tiempo_transcurrido > $tiempo_inactividad) {
        // Sesión expirada por inactividad
        
        // Si es petición AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            
            if (!headers_sent()) {
                session_unset();
                session_destroy();
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Sesión expirada por inactividad',
                    'session_expired' => true
                ]);
                exit;
            }
        }
        
        // Petición normal
        if (!headers_sent()) {
            session_unset();
            session_destroy();
            header('Location: ' . URL_BASE . '/views/login/?timeout=1');
            exit;
        }
    }
}

// Actualizar último acceso
$_SESSION['ultimo_acceso'] = time();
