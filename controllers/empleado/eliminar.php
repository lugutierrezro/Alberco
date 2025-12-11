<?php
// Eliminar Empleado (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/views/empleado/');
    exit;
}

try {
    $empleadoId = (int)($_POST['id_empleado'] ?? 0);
    
    if ($empleadoId <= 0) {
        $_SESSION['error'] = 'ID de empleado inválido';
        header('Location: ' . URL_BASE . '/views/empleado/');
        exit;
    }
    
    // Verificar si tiene usuario asociado
    $checkUsuarioSql = "SELECT id_usuario FROM tb_usuarios WHERE id_empleado = ? AND estado_registro = 'ACTIVO'";
    $checkUsuarioStmt = $pdo->prepare($checkUsuarioSql);
    $checkUsuarioStmt->execute([$empleadoId]);
    
    if ($checkUsuarioStmt->fetch()) {
        $_SESSION['error'] = 'No se puede eliminar el empleado porque tiene un usuario asociado';
        header('Location: ' . URL_BASE . '/views/empleado/');
        exit;
    }
    
    // Soft delete
    $deleteSql = "UPDATE tb_empleados 
                  SET estado_registro = 'INACTIVO', 
                      estado_laboral = 'INACTIVO',
                      fyh_actualizacion = NOW() 
                  WHERE id_empleado = ?";
    
    $stmt = $pdo->prepare($deleteSql);
    $result = $stmt->execute([$empleadoId]);
    
    if ($result) {
        $_SESSION['success'] = 'Empleado eliminado correctamente';
    } else {
        $_SESSION['error'] = 'Error al eliminar el empleado';
    }
    
    header('Location: ' . URL_BASE . '/views/empleado/');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al eliminar empleado: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/views/empleado/');
    exit;
}
