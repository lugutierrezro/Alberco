<?php
// Actualizar Rol (sin JSON)

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
    $rol = strtoupper(sanitize($_POST['rol'] ?? ''));
    $descripcion = sanitize($_POST['descripcion'] ?? '');
    
    if (empty($rol)) {
        $_SESSION['error'] = 'El nombre del rol es obligatorio';
        header('Location: ' . URL_BASE . '/views/roles/update.php?id=' . $rolId);
        exit;
    }
    
    // Verificar que no sea rol del sistema
    $checkSql = "SELECT rol FROM tb_roles WHERE id_rol = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$rolId]);
    $rolActual = $checkStmt->fetch();
    
    if ($rolActual && in_array($rolActual['rol'], ['ADMINISTRADOR', 'CAJERO', 'DELIVERY', 'COCINERO'])) {
        $_SESSION['error'] = 'No se puede modificar un rol del sistema';
        header('Location: ' . URL_BASE . '/views/roles');
        exit;
    }
    
    // Actualizar
    $updateSql = "UPDATE tb_roles 
                  SET rol = :rol, descripcion = :descripcion, fyh_actualizacion = NOW() 
                  WHERE id_rol = :id";
    
    $stmt = $pdo->prepare($updateSql);
    $result = $stmt->execute([
        ':rol' => $rol,
        ':descripcion' => $descripcion,
        ':id' => $rolId
    ]);
    
    if ($result) {
        $_SESSION['success'] = 'Rol actualizado correctamente';
    } else {
        $_SESSION['error'] = 'Error al actualizar el rol';
    }
    
    header('Location: ' . URL_BASE . '/views/roles');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al actualizar rol: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/views/roles');
    exit;
}
