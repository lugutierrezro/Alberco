<?php
// Actualizar Perfil de Usuario

require_once __DIR__ . '/../../services/database/config.php';
include('../../contans/layout/sesion.php');

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/views/perfil/');
    exit;
}

try {
    $id_usuario = (int)($_POST['id_usuario'] ?? 0);
    
    // Verificar que el usuario solo puede editar su propio perfil
    if ($id_usuario !== $_SESSION['id_usuario']) {
        $_SESSION['error'] = 'No tiene permiso para editar este perfil';
        header('Location: ' . URL_BASE . '/views/perfil/');
        exit;
    }
    
    $nombres = sanitize($_POST['nombres'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    
    // Validaciones
    if (empty($nombres) || empty($email) || empty($username)) {
        $_SESSION['error'] = 'Complete todos los campos obligatorios';
        header('Location: ' . URL_BASE . '/views/perfil/editar.php');
        exit;
    }
    
    if (strlen($nombres) < 3) {
        $_SESSION['error'] = 'El nombre debe tener al menos 3 caracteres';
        header('Location: ' . URL_BASE . '/views/perfil/editar.php');
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'El email no es válido';
        header('Location: ' . URL_BASE . '/views/perfil/editar.php');
        exit;
    }
    
    // Verificar que el email no esté en uso por otro usuario
    $checkEmailSql = "SELECT id_usuario FROM tb_usuarios WHERE email = ? AND id_usuario != ?";
    $checkEmailStmt = $pdo->prepare($checkEmailSql);
    $checkEmailStmt->execute([$email, $id_usuario]);
    
    if ($checkEmailStmt->fetch()) {
        $_SESSION['error'] = 'El email ya está en uso por otro usuario';
        header('Location: ' . URL_BASE . '/views/perfil/editar.php');
        exit;
    }
    
    // Verificar que el username no esté en uso por otro usuario
    $checkUserSql = "SELECT id_usuario FROM tb_usuarios WHERE username = ? AND id_usuario != ?";
    $checkUserStmt = $pdo->prepare($checkUserSql);
    $checkUserStmt->execute([$username, $id_usuario]);
    
    if ($checkUserStmt->fetch()) {
        $_SESSION['error'] = 'El nombre de usuario ya está en uso';
        header('Location: ' . URL_BASE . '/views/perfil/editar.php');
        exit;
    }
    
    // Actualizar usuario
    $updateSql = "UPDATE tb_usuarios 
                  SET nombres = :nombres,
                      email = :email,
                      username = :username,
                      fyh_actualizacion = NOW()
                  WHERE id_usuario = :id";
    
    $stmt = $pdo->prepare($updateSql);
    $result = $stmt->execute([
        ':nombres' => $nombres,
        ':email' => $email,
        ':username' => $username,
        ':id' => $id_usuario
    ]);
    
    if ($result) {
        // Actualizar datos de sesión
        $_SESSION['nombres_usuario'] = $nombres;
        
        $_SESSION['success'] = 'Perfil actualizado correctamente';
    } else {
        $_SESSION['error'] = 'Error al actualizar el perfil';
    }
    
    header('Location: ' . URL_BASE . '/views/perfil/');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al actualizar perfil: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/views/perfil/editar.php');
    exit;
}
