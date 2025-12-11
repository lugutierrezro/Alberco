<?php
// Controlador de autenticación (CON REDIRECCIÓN)

require_once __DIR__ . '/../../services/database/config.php';
require_once __DIR__ . '/../../models/usuario.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

try {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validaciones
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Complete todos los campos';
        header('Location: ' . URL_BASE . '/views/login/');
        exit;
    }
    
    if (!validarEmail($email)) {
        $_SESSION['error'] = 'Email inválido';
        header('Location: ' . URL_BASE . '/views/login/');
        exit;
    }
    
    // Buscar usuario
    $sql = "SELECT u.*, r.rol, e.nombres, e.apellidos 
            FROM tb_usuarios u
            INNER JOIN tb_roles r ON u.id_rol = r.id_rol
            LEFT JOIN tb_empleados e ON u.id_empleado = e.id_empleado
            WHERE u.email = :email 
            AND u.estado_registro = 'ACTIVO'
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    
    // Verificar contraseña
    if ($user && password_verify($password, $user['password_user'])) {
        // Crear sesión
        $_SESSION['id_usuario'] = $user['id_usuario'];
        $_SESSION['nombres'] = $user['nombres'] ?? $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['rol'] = $user['rol'];
        
        // Actualizar último acceso
        $updateSql = "UPDATE tb_usuarios SET ultimo_acceso = NOW() WHERE id_usuario = :id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([':id' => $user['id_usuario']]);
        
        header('Location: ' . URL_BASE . '/');
        exit;
        
    } else {
        $_SESSION['error'] = 'Credenciales incorrectas';
        header('Location: ' . URL_BASE . '/views/login/');
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Error en login: " . $e->getMessage());
    $_SESSION['error'] = 'Error interno del servidor';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}
