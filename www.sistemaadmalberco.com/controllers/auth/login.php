<?php
/**
 * autor : D3spiadado 
 */

require_once __DIR__ . '/../../services/database/config.php';
require_once __DIR__ . '/../../models/usuario.php';
require_once __DIR__ . '/../../helpers/actividad_helper.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/views/login/');
    exit();
}

try {
    // Obtener datos
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validación básica
    if (empty($email) || empty($password)) {
        $_SESSION['mensaje'] = "Complete todos los campos";
        header("Location: " . URL_BASE . "/views/login/");
        exit();
    }

    // Instancia del modelo
    $usuarioModel = new Usuario();
    $user = $usuarioModel->authenticate($email, $password);

    
    // Usuario encontrado
    if ($user) {

        // Crear sesión
        $_SESSION['id_usuario'] = $user['id_usuario'];
        $_SESSION['nombres'] = $user['nombres'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['rol'] = $user['rol'];
        
        // Registrar login exitoso
        $_SESSION['sesion_id_usuario'] = $user['id_usuario']; // Para helper
        registrarLogin($email, true);

        header("Location: " . URL_BASE . "/");
        exit();

    } else {
        // Error de credenciales - Registrar intento fallido
        registrarLogin($email, false);
        
        $_SESSION['mensaje'] = "Credenciales incorrectas";
        header("Location: " . URL_BASE . "/views/login/");
        exit();
    }

} catch (Exception $e) {

    error_log("Error en login: " . $e->getMessage());
    $_SESSION['mensaje'] = "Error interno del servidor";
    header("Location: " . URL_BASE . "/contans/layout/mensajes.php");
    exit();
}
