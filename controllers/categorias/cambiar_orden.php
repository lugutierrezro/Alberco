<?php
// Cambiar Orden de una Categoría Individual (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/categorias');
    exit;
}

try {
    $categoriaId = (int)($_POST['id_categoria'] ?? 0);
    $nuevoOrden = (int)($_POST['orden'] ?? 0);
    
    if ($categoriaId <= 0) {
        $_SESSION['error'] = 'ID de categoría inválido';
        header('Location: ' . URL_BASE . '/categorias');
        exit;
    }
    
    // Actualizar orden
    $updateSql = "UPDATE tb_categorias SET orden = :orden WHERE id_categoria = :id";
    $stmt = $pdo->prepare($updateSql);
    $result = $stmt->execute([
        ':orden' => $nuevoOrden,
        ':id' => $categoriaId
    ]);
    
    if ($result) {
        $_SESSION['success'] = 'Orden actualizado correctamente';
    } else {
        $_SESSION['error'] = 'Error al actualizar el orden';
    }
    
    header('Location: ' . URL_BASE . '/categorias');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al cambiar orden: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/categorias');
    exit;
}
