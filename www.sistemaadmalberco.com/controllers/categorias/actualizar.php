<?php
// Actualizar Categoría (sin JSON)

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
    
    $data = [
        'nombre_categoria' => sanitize($_POST['nombre_categoria'] ?? ''),
        'descripcion' => sanitize($_POST['descripcion'] ?? ''),
        'orden' => (int)($_POST['orden'] ?? 0),
        'color' => sanitize($_POST['color'] ?? '#007bff'),
        'icono' => sanitize($_POST['icono'] ?? 'fas fa-tag')
    ];
    
    // Validaciones
    if (empty($data['nombre_categoria'])) {
        $_SESSION['error'] = 'El nombre de la categoría es obligatorio';
        header('Location: ' . URL_BASE . '/views/categorias/update.php?id=' . $categoriaId);
        exit;
    }
    
    // Verificar nombre duplicado (excluyendo la actual)
    $checkSql = "SELECT id_categoria FROM tb_categorias 
                 WHERE nombre_categoria = ? 
                 AND id_categoria != ? 
                 AND estado_registro = 'ACTIVO'";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$data['nombre_categoria'], $categoriaId]);
    
    if ($checkStmt->fetch()) {
        $_SESSION['error'] = 'Ya existe otra categoría con ese nombre';
        header('Location: ' . URL_BASE . '/views/categorias/update.php?id=' . $categoriaId);
        exit;
    }
    
    // Obtener datos actuales
    $getSql = "SELECT imagen FROM tb_categorias WHERE id_categoria = ?";
    $getStmt = $pdo->prepare($getSql);
    $getStmt->execute([$categoriaId]);
    $categoriaActual = $getStmt->fetch();
    
    if (!$categoriaActual) {
        $_SESSION['error'] = 'Categoría no encontrada';
        header('Location: ' . URL_BASE . '/views/categorias/');
        exit;
    }
    
    // Manejar imagen nueva
    $imagenPath = $categoriaActual['imagen'];
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__, 2) . '/uploads/categorias/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Validar tipo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $fileType = $_FILES['imagen']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $_SESSION['error'] = 'Solo se permiten imágenes JPG, PNG o WEBP';
            header('Location: ' . URL_BASE . '/views/categorias/update.php?id=' . $categoriaId);
            exit;
        }
        
        // Validar tamaño
        if ($_FILES['imagen']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error'] = 'La imagen no debe superar los 2MB';
            header('Location: ' . URL_BASE . '/views/categorias/update.php?id=' . $categoriaId);
            exit;
        }
        
        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $filename = 'CAT_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $filepath)) {
            // Eliminar imagen anterior
            if (!empty($categoriaActual['imagen'])) {
                $oldFile = dirname(__DIR__, 2) . '/' . $categoriaActual['imagen'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            $imagenPath = 'uploads/categorias/' . $filename;
        }
    }
    
    // Actualizar
    $updateSql = "UPDATE tb_categorias SET
                  nombre_categoria = :nombre,
                  descripcion = :descripcion,
                  orden = :orden,
                  color = :color,
                  icono = :icono,
                  imagen = :imagen,
                  fyh_actualizacion = NOW()
                  WHERE id_categoria = :id";
    
    $stmt = $pdo->prepare($updateSql);
    $result = $stmt->execute([
        ':nombre' => $data['nombre_categoria'],
        ':descripcion' => $data['descripcion'],
        ':orden' => $data['orden'],
        ':color' => $data['color'],
        ':icono' => $data['icono'],
        ':imagen' => $imagenPath,
        ':id' => $categoriaId
    ]);
    
    if ($result) {
        $_SESSION['success'] = 'Categoría actualizada correctamente';
    } else {
        $_SESSION['error'] = 'Error al actualizar la categoría';
    }
    
    header('Location: ' . URL_BASE . '/views/categorias/');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al actualizar categoría: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/views/categorias/');
    exit;
}
