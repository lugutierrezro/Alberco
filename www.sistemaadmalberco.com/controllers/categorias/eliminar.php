<?php
// Eliminar Categoría (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/views/categorias/');
    exit;
}

try {
    $categoriaId = (int)($_POST['id_categoria'] ?? 0);
    
    if ($categoriaId <= 0) {
        $_SESSION['error'] = 'ID de categoría inválido';
        header('Location: ' . URL_BASE . '/views/categorias/');
        exit;
    }
    
    // Verificar productos asociados
    $checkSql = "SELECT COUNT(*) as total FROM tb_almacen 
                 WHERE id_categoria = ? AND estado_registro = 'ACTIVO'";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$categoriaId]);
    $count = $checkStmt->fetch();
    
    if ($count['total'] > 0) {
        $_SESSION['error'] = 'No se puede eliminar la categoría porque tiene ' . $count['total'] . ' producto(s) asociado(s)';
        header('Location: ' . URL_BASE . '/views/categorias/');
        exit;
    }
    
    // Soft delete
    $deleteSql = "UPDATE tb_categorias 
                  SET estado_registro = 'INACTIVO', fyh_actualizacion = NOW() 
                  WHERE id_categoria = ?";
    
    $stmt = $pdo->prepare($deleteSql);
    $result = $stmt->execute([$categoriaId]);
    
    if ($result) {
        $_SESSION['success'] = 'Categoría eliminada correctamente';
    } else {
        $_SESSION['error'] = 'Error al eliminar la categoría';
    }
    
    header('Location: ' . URL_BASE . '/views/categorias/');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al eliminar categoría: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/views/categorias/');
    exit;
}
