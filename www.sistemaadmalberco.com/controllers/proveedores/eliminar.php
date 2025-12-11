<?php
// Eliminar Proveedor (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/proveedores');
    exit;
}

try {
    $proveedorId = (int)($_POST['id_proveedor'] ?? 0);
    
    // Verificar compras asociadas
    $checkSql = "SELECT COUNT(*) as total FROM tb_compras 
                 WHERE id_proveedor = ? AND estado_registro = 'ACTIVO'";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$proveedorId]);
    $count = $checkStmt->fetch();
    
    if ($count['total'] > 0) {
        $_SESSION['error'] = 'No se puede eliminar el proveedor porque tiene compras registradas';
        header('Location: ' . URL_BASE . '/proveedores');
        exit;
    }
    
    // Soft delete
    $deleteSql = "UPDATE tb_proveedores 
                  SET estado_registro = 'INACTIVO', fyh_actualizacion = NOW() 
                  WHERE id_proveedor = ?";
    
    $stmt = $pdo->prepare($deleteSql);
    $result = $stmt->execute([$proveedorId]);
    
    if ($result) {
        $_SESSION['success'] = 'Proveedor eliminado correctamente';
    } else {
        $_SESSION['error'] = 'Error al eliminar el proveedor';
    }
    
    header('Location: ' . URL_BASE . '/proveedores');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al eliminar proveedor: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/proveedores');
    exit;
}
