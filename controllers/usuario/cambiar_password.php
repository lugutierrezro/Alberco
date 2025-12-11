<?php
// Cambiar Contraseña (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/usuarios');
    exit;
}

try {
    $userId = (int)($_POST['id_usuario'] ?? 0);
    $passwordActual = $_POST['password_actual'] ?? '';
    $passwordNueva = $_POST['password_nueva'] ?? '';
    $passwordConfirmar = $_POST['password_confirmar'] ?? '';
    
    // Validaciones
    if (empty($passwordNueva) || empty($passwordConfirmar)) {
        $_SESSION['error'] = 'Complete todos los campos';
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? URL_BASE . '/usuarios');
        exit;
    }
    
    if ($passwordNueva !== $passwordConfirmar) {
        $_SESSION['error'] = 'Las contraseñas no coinciden';
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? URL_BASE . '/usuarios');
        exit;
    }
    
    if (strlen($passwordNueva) < 6) {
        $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? URL_BASE . '/usuarios');
        exit;
    }
    
    // Si cambia su propia contraseña, verificar la actual
    if ($userId === $_SESSION['id_usuario'] && !empty($passwordActual)) {
        $sql = "SELECT password_user FROM tb_usuarios WHERE id_usuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($passwordActual, $user['password_user'])) {
            $_SESSION['error'] = 'La contraseña actual es incorrecta';
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? URL_BASE . '/usuarios');
            exit;
        }
    }
    
    // Actualizar contraseña
    $newPasswordHash = password_hash($passwordNueva, PASSWORD_DEFAULT);
    $updateSql = "UPDATE tb_usuarios 
                  SET password_user = :password, fyh_actualizacion = NOW() 
                  WHERE id_usuario = :id";
    
    $stmt = $pdo->prepare($updateSql);
    $result = $stmt->execute([
        ':password' => $newPasswordHash,
        ':id' => $userId
    ]);
    
    if ($result) {
        $_SESSION['success'] = 'Contraseña actualizada correctamente';
    } else {
        $_SESSION['error'] = 'Error al actualizar la contraseña';
    }
    
    header('Location: ' . URL_BASE . '/usuarios');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al cambiar contraseña: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/usuarios');
    exit;
}
