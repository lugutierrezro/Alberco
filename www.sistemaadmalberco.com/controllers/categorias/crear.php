<?php
// Crear Categoría (sin JSON)

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
    $data = [
        'nombre_categoria' => sanitize($_POST['nombre_categoria'] ?? ''),
        'descripcion' => sanitize($_POST['descripcion'] ?? ''),
        'orden' => !empty($_POST['orden']) ? (int)$_POST['orden'] : 0,
        'color' => sanitize($_POST['color'] ?? '#007bff'),
        'icono' => sanitize($_POST['icono'] ?? 'fas fa-tag')
    ];
    
    // Validaciones
    if (empty($data['nombre_categoria'])) {
        $_SESSION['error'] = 'El nombre de la categoría es obligatorio';
        header('Location: ' . URL_BASE . '/views/categorias/create.php');
        exit;
    }
    
    // Verificar duplicados
    $checkSql = "SELECT id_categoria FROM tb_categorias 
                 WHERE nombre_categoria = ? AND estado_registro = 'ACTIVO'";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$data['nombre_categoria']]);
    
    if ($checkStmt->fetch()) {
        $_SESSION['error'] = 'Ya existe una categoría con ese nombre';
        header('Location: ' . URL_BASE . '/views/categorias/create.php');
        exit;
    }
    
    // Manejar imagen
    $imagenPath = null;
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
            header('Location: ' . URL_BASE . '/views/categorias/create.php');
            exit;
        }
        
        // Validar tamaño (máximo 2MB)
        if ($_FILES['imagen']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error'] = 'La imagen no debe superar los 2MB';
            header('Location: ' . URL_BASE . '/views/categorias/create.php');
            exit;
        }
        
        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $filename = 'CAT_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $filepath)) {
            $imagenPath = 'uploads/categorias/' . $filename;
        }
    }
    
    // Insertar categoría
    $insertSql = "INSERT INTO tb_categorias 
                  (nombre_categoria, descripcion, orden, color, icono, imagen, fyh_creacion)
                  VALUES (:nombre, :descripcion, :orden, :color, :icono, :imagen, NOW())";
    
    $stmt = $pdo->prepare($insertSql);
    $result = $stmt->execute([
        ':nombre' => $data['nombre_categoria'],
        ':descripcion' => $data['descripcion'],
        ':orden' => $data['orden'],
        ':color' => $data['color'],
        ':icono' => $data['icono'],
        ':imagen' => $imagenPath
    ]);
    
    if ($result) {
        $_SESSION['success'] = 'Categoría creada correctamente';
    } else {
        $_SESSION['error'] = 'Error al crear la categoría';
    }
    
    header('Location: ' . URL_BASE . '/views/categorias/');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al crear categoría: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/views/categorias/create.php');
    exit;
}
