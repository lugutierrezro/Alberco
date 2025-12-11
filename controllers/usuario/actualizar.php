<?php
/**
 * Actualizar Usuario
 */

require_once '../../services/database/config.php';
require_once '../../models/usuario.php';

// Detectar si es petición AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    if ($isAjax) {
        jsonResponse(false, 'No autenticado');
    } else {
        $_SESSION['error'] = 'Debe iniciar sesión';
        header('Location: ' . URL_BASE . '/views/login/');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        jsonResponse(false, 'Método no permitido');
    } else {
        header('Location: ' . URL_BASE . '/views/usuarios/');
        exit;
    }
}

try {
    $usuarioModel = new Usuario();
    
    $userId = (int)$_POST['id_usuario'];
    
    $data = [
        'username' => sanitize($_POST['username']),
        'email' => sanitize($_POST['email']),
        'id_rol' => (int)$_POST['id_rol']
    ];
    
    // Si viene contraseña, agregarla
    if (!empty($_POST['password_user'])) {
        $password = $_POST['password_user'];
        $passwordRepeat = $_POST['password_repeat'] ?? '';
        
        if ($password !== $passwordRepeat) {
            if ($isAjax) {
                jsonResponse(false, 'Las contraseñas no coinciden');
            } else {
                $_SESSION['error'] = 'Las contraseñas no coinciden';
                header('Location: ' . URL_BASE . '/views/usuarios/update.php?id=' . $userId);
                exit;
            }
        }
        
        if (strlen($password) < 6) {
            if ($isAjax) {
                jsonResponse(false, 'La contraseña debe tener al menos 6 caracteres');
            } else {
                $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
                header('Location: ' . URL_BASE . '/views/usuarios/update.php?id=' . $userId);
                exit;
            }
        }
        
        $data['password'] = password_hash($password, PASSWORD_DEFAULT);
    }
    
    if (!empty($_POST['id_empleado'])) {
        $data['id_empleado'] = (int)$_POST['id_empleado'];
    }
    
    // Validaciones
    if (empty($data['username']) || empty($data['email'])) {
        if ($isAjax) {
            jsonResponse(false, 'Complete todos los campos obligatorios');
        } else {
            $_SESSION['error'] = 'Complete todos los campos obligatorios';
            header('Location: ' . URL_BASE . '/views/usuarios/update.php?id=' . $userId);
            exit;
        }
    }
    
    if (!validarEmail($data['email'])) {
        if ($isAjax) {
            jsonResponse(false, 'Email inválido');
        } else {
            $_SESSION['error'] = 'Email inválido';
            header('Location: ' . URL_BASE . '/views/usuarios/update.php?id=' . $userId);
            exit;
        }
    }
    
    // Verificar si el email ya existe (excluyendo el usuario actual)
    if ($usuarioModel->emailExists($data['email'], $userId)) {
        if ($isAjax) {
            jsonResponse(false, 'El email ya está registrado por otro usuario');
        } else {
            $_SESSION['error'] = 'El email ya está registrado por otro usuario';
            header('Location: ' . URL_BASE . '/views/usuarios/update.php?id=' . $userId);
            exit;
        }
    }
    
    // Actualizar
    if ($usuarioModel->update($userId, $data)) {
        if ($isAjax) {
            jsonResponse(true, 'Usuario actualizado correctamente');
        } else {
            $_SESSION['success'] = 'Usuario actualizado correctamente';
            header('Location: ' . URL_BASE . '/views/usuarios/');
            exit;
        }
    } else {
        if ($isAjax) {
            jsonResponse(false, 'Error al actualizar el usuario');
        } else {
            $_SESSION['error'] = 'Error al actualizar el usuario';
            header('Location: ' . URL_BASE . '/views/usuarios/update.php?id=' . $userId);
            exit;
        }
    }
    
} catch (Exception $e) {
    error_log("Error al actualizar usuario: " . $e->getMessage());
    if ($isAjax) {
        jsonResponse(false, 'Error al procesar la solicitud');
    } else {
        $_SESSION['error'] = 'Error al procesar la solicitud: ' . $e->getMessage();
        header('Location: ' . URL_BASE . '/views/usuarios/update.php?id=' . ($userId ?? 0));
        exit;
    }
}
