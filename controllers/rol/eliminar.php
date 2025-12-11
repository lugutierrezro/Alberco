<?php
// Eliminar Rol (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'ADMINISTRADOR') {
    $_SESSION['error'] = 'No tiene permisos';
    header('Location: ' . URL_BASE . '/views/roles');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/views/roles');
    exit;
}

try {
    $rolId = (int)($_POST['id_rol'] ?? 0);
    
    // Verificar que no sea rol del sistema
    $checkSql = "SELECT rol FROM tb_roles WHERE id_rol = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$rolId]);
    $rolData = $checkStmt->fetch();
    
    if (!$rolData) {
        $_SESSION['error'] = 'Rol no encontrado';
        header('Location: ' . URL_BASE . '/views/roles');
        exit;
    }
    
    if (in_array($rolData['rol'], ['ADMINISTRADOR', 'CAJERO', 'DELIVERY', 'COCINERO'])) {
        $_SESSION['error'] = 'No se puede eliminar un rol del sistema';
        header('Location: ' . URL_BASE . '/views/roles');
        exit;
    }
    
    // Verificar usuarios asignados
    $countSql = "SELECT COUNT(*) as total FROM tb_usuarios 
                 WHERE id_rol = ? AND estado_registro = 'ACTIVO'";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$rolId]);
    $count = $countStmt->fetch();
    
    if ($count['total'] > 0) {
        $_SESSION['error'] = 'No se puede eliminar el rol porque tiene usuarios asignados';
        header('Location: ' . URL_BASE . '/views/roles');
        exit;
    }
    
    // Soft delete
    $deleteSql = "UPDATE tb_roles 
                  SET estado_registro = 'INACTIVO', fyh_actualizacion = NOW() 
                  WHERE id_rol = ?";
    
    $stmt = $pdo->prepare($deleteSql);
    $result = $stmt->execute([$rolId]);
    
    if ($result) {
        $_SESSION['success'] = 'Rol eliminado correctamente';
    } else {
        $_SESSION['error'] = 'Error al eliminar el rol';
    }
    
    header('Location: ' . URL_BASE . '/views/roles');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al eliminar rol: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/views/roles');
    exit;
}
