<?php
// Crear Rol (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';

// Verificar autenticación y permisos
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
    $rol = strtoupper(sanitize($_POST['rol'] ?? ''));
    $descripcion = sanitize($_POST['descripcion'] ?? '');
    
    // Validaciones
    if (empty($rol)) {
        $_SESSION['error'] = 'El nombre del rol es obligatorio';
        header('Location: ' . URL_BASE . '/views/roles/create.php');
        exit;
    }
    
    // Verificar si existe
    $checkSql = "SELECT id_rol FROM tb_roles WHERE rol = ? LIMIT 1";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$rol]);
    
    if ($checkStmt->fetch()) {
        $_SESSION['error'] = 'Ya existe un rol con ese nombre';
        header('Location: ' . URL_BASE . '/views/roles/create.php');
        exit;
    }
    
    // Insertar
    $insertSql = "INSERT INTO tb_roles (rol, descripcion, fyh_creacion) 
                  VALUES (:rol, :descripcion, NOW())";
    
    $stmt = $pdo->prepare($insertSql);
    $result = $stmt->execute([
        ':rol' => $rol,
        ':descripcion' => $descripcion
    ]);
    
    if ($result) {
        $_SESSION['success'] = 'Rol creado correctamente';
    } else {
        $_SESSION['error'] = 'Error al crear el rol';
    }
    
    header('Location: ' . URL_BASE . '/views/roles');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al crear rol: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/views/roles/create.php');
    exit;
}
