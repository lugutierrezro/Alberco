<?php
// Reordenar Categorías (sin JSON)

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
    // Recibir array de IDs en el nuevo orden
    $ordenArray = json_decode($_POST['orden'] ?? '[]', true);
    
    if (!is_array($ordenArray) || empty($ordenArray)) {
        $_SESSION['error'] = 'Formato de orden inválido';
        header('Location: ' . URL_BASE . '/categorias');
        exit;
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Actualizar orden de cada categoría
    $updateSql = "UPDATE tb_categorias SET orden = :orden WHERE id_categoria = :id";
    $stmt = $pdo->prepare($updateSql);
    
    $orden = 1;
    foreach ($ordenArray as $categoriaId) {
        $stmt->execute([
            ':orden' => $orden,
            ':id' => (int)$categoriaId
        ]);
        $orden++;
    }
    
    $pdo->commit();
    
    $_SESSION['success'] = 'Categorías reordenadas correctamente';
    header('Location: ' . URL_BASE . '/categorias');
    exit;
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error al reordenar categorías: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/categorias');
    exit;
}
