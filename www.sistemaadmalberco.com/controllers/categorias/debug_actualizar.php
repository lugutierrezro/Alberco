<?php
// Debug para actualizar categoría
session_start();

echo "<h1>Debug - Actualizar Categoría</h1>";
echo "<h2>1. Datos POST Recibidos:</h2>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h2>2. Archivos recibidos:</h2>";
echo "<pre>";
print_r($_FILES);
echo "</pre>";

echo "<h2>3. Sesión:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Simular el proceso
require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['id_usuario'])) {
    echo "<p style='color:red'>ERROR: No hay sesión de usuario</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<p style='color:red'>ERROR: No es método POST</p>";
    exit;
}

try {
    $categoriaId = (int)($_POST['id_categoria'] ?? 0);
    
    echo "<h2>4. ID de categoría: $categoriaId</h2>";
    
    $data = [
        'nombre_categoria' => sanitize($_POST['nombre_categoria'] ?? ''),
        'descripcion' => sanitize($_POST['descripcion'] ?? ''),
        'orden' => (int)($_POST['orden'] ?? 0),
        'color' => sanitize($_POST['color'] ?? '#007bff'),
        'icono' => sanitize($_POST['icono'] ?? 'fas fa-tag')
    ];
    
    echo "<h2>5. Data sanitizada:</h2>";
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    
    // Verificar nombre duplicado (excluyendo la actual)
    $checkSql = "SELECT id_categoria FROM tb_categorias 
                 WHERE nombre_categoria = ? 
                 AND id_categoria != ? 
                 AND estado_registro = 'ACTIVO'";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$data['nombre_categoria'], $categoriaId]);
    
    if ($checkStmt->fetch()) {
        echo "<p style='color:red'>ERROR: Ya existe otra categoría con ese nombre</p>";
    } else {
        echo "<p style='color:green'>OK: Nombre disponible</p>";
    }
    
    // Obtener datos actuales
    $getSql = "SELECT * FROM tb_categorias WHERE id_categoria = ?";
    $getStmt = $pdo->prepare($getSql);
    $getStmt->execute([$categoriaId]);
    $categoriaActual = $getStmt->fetch();
    
    if (!$categoriaActual) {
        echo "<p style='color:red'>ERROR: Categoría no encontrada</p>";
        exit;
    }
    
    echo "<h2>6. Categoría actual:</h2>";
    echo "<pre>";
    print_r($categoriaActual);
    echo "</pre>";
    
    // Manejar imagen nueva
    $imagenPath = $categoriaActual['imagen'];
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        echo "<p style='color:blue'>INFO: Nueva imagen detectada</p>";
        echo "<pre>";
        print_r($_FILES['imagen']);
        echo "</pre>";
        
        $uploadDir = dirname(__DIR__, 2) . '/uploads/categorias/';
        echo "<p>Upload dir: $uploadDir</p>";
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
            echo "<p style='color:green'>Directorio creado</p>";
        }
        
        // Validar tipo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $fileType = $_FILES['imagen']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            echo "<p style='color:red'>ERROR: Tipo de archivo no permitido: $fileType</p>";
        } else {
            echo "<p style='color:green'>OK: Tipo de archivo válido</p>";
        }
        
        // Validar tamaño
        $fileSize = $_FILES['imagen']['size'];
        if ($fileSize > 2 * 1024 * 1024) {
            echo "<p style='color:red'>ERROR: Archivo muy grande: " . round($fileSize/1024/1024, 2) . "MB</p>";
        } else {
            echo "<p style='color:green'>OK: Tamaño válido: " . round($fileSize/1024, 2) . "KB</p>";
        }
    } else {
        echo "<p style='color:blue'>INFO: No se subió nueva imagen</p>";
        if (isset($_FILES['imagen'])) {
            echo "<p>Error code: " . $_FILES['imagen']['error'] . "</p>";
        }
    }
    
    echo "<h2>7. SQL que se ejecutaría:</h2>";
    $updateSql = "UPDATE tb_categorias SET
                  nombre_categoria = :nombre,
                  descripcion = :descripcion,
                  orden = :orden,
                  color = :color,
                  icono = :icono,
                  imagen = :imagen,
                  fyh_actualizacion = NOW()
                  WHERE id_categoria = :id";
    echo "<pre>$updateSql</pre>";
    
    echo "<h2>8. Parámetros:</h2>";
    echo "<pre>";
    print_r([
        ':nombre' => $data['nombre_categoria'],
        ':descripcion' => $data['descripcion'],
        ':orden' => $data['orden'],
        ':color' => $data['color'],
        ':icono' => $data['icono'],
        ':imagen' => $imagenPath,
        ':id' => $categoriaId
    ]);
    echo "</pre>";
    
    echo "<hr>";
    echo "<p><strong>Todo OK!</strong> El proceso debería funcionar correctamente.</p>";
    echo "<a href='../../views/categorias/update.php?id=$categoriaId' style='display:inline-block; padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>Volver a Editar</a>";
    
} catch (PDOException $e) {
    echo "<h2 style='color:red'>ERROR PDO:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
