<?php
// Crear Usuario (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/views/usuarios');
    exit;
}

try {
    $data = [
        'username' => sanitize($_POST['username'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'password_user' => $_POST['password_user'] ?? '',
        'id_rol' => (int)($_POST['id_rol'] ?? 0)
    ];
    
    // Validaciones
    if (empty($data['username']) || empty($data['email']) || empty($data['password_user']) || $data['id_rol'] <= 0) {
        $_SESSION['error'] = 'Todos los campos son obligatorios';
        header('Location: ' . URL_BASE . '/views/usuarios/create.php');
        exit;
    }
    
    if (!validarEmail($data['email'])) {
        $_SESSION['error'] = 'Email inválido';
        header('Location: ' . URL_BASE . '/views/usuarios/create.php');
        exit;
    }
    
    if (strlen($data['password_user']) < 6) {
        $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
        header('Location: ' . URL_BASE . '/views/usuarios/create.php');
        exit;
    }
    
    // Verificar email duplicado
    $checkEmailSql = "SELECT id_usuario FROM tb_usuarios WHERE email = ?";
    $checkEmailStmt = $pdo->prepare($checkEmailSql);
    $checkEmailStmt->execute([$data['email']]);
    
    if ($checkEmailStmt->fetch()) {
        $_SESSION['error'] = 'El email ya está registrado';
        header('Location: ' . URL_BASE . '/views/usuarios/create.php');
        exit;
    }
    
    // Hashear contraseña
    $passwordHash = password_hash($data['password_user'], PASSWORD_DEFAULT);
    $token = generarToken();
    
    // Insertar usuario (tb_usuarios no tiene campo nombres_usuario)
    $insertSql = "INSERT INTO tb_usuarios 
                  (username, email, password_user, id_rol, token, fyh_creacion)
                  VALUES (:username, :email, :password, :rol, :token, NOW())";
    
    $stmt = $pdo->prepare($insertSql);
    $result = $stmt->execute([
        ':username' => $data['username'],
        ':email' => $data['email'],
        ':password' => $passwordHash,
        ':rol' => $data['id_rol'],
        ':token' => $token
    ]);
    
    if ($result) {
        $_SESSION['success'] = 'Usuario creado correctamente';
    } else {
        $_SESSION['error'] = 'Error al crear el usuario';
    }
    
    header('Location: ' . URL_BASE . '/views/usuarios');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al crear usuario: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/views/usuarios/create.php');
    exit;
}
