<?php
// Actualizar Producto (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';
include('../../contans/layout/sesion.php');

// ============================================
// LOGGING DE DEBUG
// ============================================
error_log("========== INICIO ACTUALIZAR PRODUCTO ==========");
error_log("POST Data: " . print_r($_POST, true));
error_log("FILES Data: " . print_r($_FILES, true));
error_log("Usuario ID: " . ($_SESSION['id_usuario'] ?? 'NO DEFINIDO'));
error_log("===================================================");

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/views/almacen/');
    exit;
}

try {
    $productoId = (int)($_POST['id_producto'] ?? 0);
    
    error_log("Producto ID a actualizar: $productoId");
    
    $data = [
        'nombre' => sanitize($_POST['nombre'] ?? ''),
        'descripcion' => sanitize($_POST['descripcion'] ?? ''),
        'stock' => (int)($_POST['stock'] ?? 0),
        'stock_minimo' => (int)($_POST['stock_minimo'] ?? 5),
        'stock_maximo' => (int)($_POST['stock_maximo'] ?? 100),
        'precio_compra' => (float)($_POST['precio_compra'] ?? 0),
        'precio_venta' => (float)($_POST['precio_venta'] ?? 0),
        'disponible_venta' => isset($_POST['disponible_venta']) ? 1 : 0,
        'requiere_preparacion' => isset($_POST['requiere_preparacion']) ? 1 : 0,
        'tiempo_preparacion' => (int)($_POST['tiempo_preparacion'] ?? 15),
        'id_categoria' => (int)($_POST['id_categoria'] ?? 0)
    ];
    
    error_log("Data procesada: " . print_r($data, true));
    
    // Validaciones
    if (empty($data['nombre']) || $data['id_categoria'] <= 0) {
        error_log("ERROR: Campos obligatorios vacíos");
        $_SESSION['error'] = 'Complete todos los campos obligatorios';
        header('Location: ' . URL_BASE . '/views/almacen/update.php?id=' . $productoId);
        exit;
    }
    
    if ($data['precio_venta'] <= 0 || $data['precio_compra'] <= 0) {
        error_log("ERROR: Precios <= 0");
        $_SESSION['error'] = 'Los precios deben ser mayores a cero';
        header('Location: ' . URL_BASE . '/views/almacen/update.php?id=' . $productoId);
        exit;
    }
    
    if ($data['precio_venta'] <= $data['precio_compra']) {
        error_log("ERROR: Precio venta <= precio compra");
        $_SESSION['error'] = 'El precio de venta debe ser mayor al precio de compra';
        header('Location: ' . URL_BASE . '/views/almacen/update.php?id=' . $productoId);
        exit;
    }
    
    // Obtener datos actuales
    $getSql = "SELECT codigo, imagen FROM tb_almacen WHERE id_producto = ?";
    $getStmt = $pdo->prepare($getSql);
    $getStmt->execute([$productoId]);
    $productoActual = $getStmt->fetch();
    
    if (!$productoActual) {
        error_log("ERROR: Producto no encontrado");
        $_SESSION['error'] = 'Producto no encontrado';
        header('Location: ' . URL_BASE . '/views/almacen/');
        exit;
    }
    
    error_log("Producto actual encontrado: " . $productoActual['codigo']);
    
    // Manejar imagen nueva
    $imagenPath = $productoActual['imagen'];
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        error_log("Nueva imagen detectada: " . $_FILES['imagen']['name']);
        
        $uploadDir = dirname(__DIR__, 2) . '/uploads/almacen/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
            error_log("Carpeta uploads/almacen/ creada");
        }
        
        // Validar tipo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $fileType = $_FILES['imagen']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            error_log("ERROR: Tipo de archivo no permitido: $fileType");
            $_SESSION['error'] = 'Solo se permiten imágenes JPG, PNG o WEBP';
            header('Location: ' . URL_BASE . '/views/almacen/update.php?id=' . $productoId);
            exit;
        }
        
        // Validar tamaño
        if ($_FILES['imagen']['size'] > 3 * 1024 * 1024) {
            error_log("ERROR: Archivo muy grande: " . $_FILES['imagen']['size']);
            $_SESSION['error'] = 'La imagen no debe superar los 3MB';
            header('Location: ' . URL_BASE . '/views/almacen/update.php?id=' . $productoId);
            exit;
        }
        
        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $filename = 'PROD_' . $productoActual['codigo'] . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $filepath)) {
            error_log("Imagen subida correctamente: $filepath");
            
            // Eliminar imagen anterior
            if (!empty($productoActual['imagen'])) {
                $oldFile = dirname(__DIR__, 2) . '/' . $productoActual['imagen'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                    error_log("Imagen anterior eliminada: $oldFile");
                }
            }
            $imagenPath = 'uploads/almacen/' . $filename;
        } else {
            error_log("ERROR: No se pudo mover el archivo");
        }
    }
    
    error_log("Imagen final: $imagenPath");
    
    // Actualizar
    $updateSql = "UPDATE tb_almacen SET
                  nombre = :nombre,
                  descripcion = :descripcion,
                  stock = :stock,
                  stock_minimo = :stock_min,
                  stock_maximo = :stock_max,
                  precio_compra = :precio_compra,
                  precio_venta = :precio_venta,
                  disponible_venta = :disponible,
                  requiere_preparacion = :requiere_prep,
                  tiempo_preparacion = :tiempo_prep,
                  id_categoria = :categoria,
                  imagen = :imagen,
                  fyh_actualizacion = NOW()
                  WHERE id_producto = :id";
    
    $stmt = $pdo->prepare($updateSql);
    $result = $stmt->execute([
        ':nombre' => $data['nombre'],
        ':descripcion' => $data['descripcion'],
        ':stock' => $data['stock'],
        ':stock_min' => $data['stock_minimo'],
        ':stock_max' => $data['stock_maximo'],
        ':precio_compra' => $data['precio_compra'],
        ':precio_venta' => $data['precio_venta'],
        ':disponible' => $data['disponible_venta'],
        ':requiere_prep' => $data['requiere_preparacion'],
        ':tiempo_prep' => $data['tiempo_preparacion'],
        ':categoria' => $data['id_categoria'],
        ':imagen' => $imagenPath,
        ':id' => $productoId
    ]);
    
    if ($result) {
        error_log("✅ Producto actualizado correctamente. Rows affected: " . $stmt->rowCount());
        $_SESSION['success'] = 'Producto actualizado correctamente';
    } else {
        error_log("❌ Error al actualizar - Sin rows affected");
        $errorInfo = $stmt->errorInfo();
        error_log("Error Info: " . print_r($errorInfo, true));
        $_SESSION['error'] = 'Error al actualizar el producto';
    }
    
    error_log("========== FIN ACTUALIZAR PRODUCTO ==========");
    header('Location: ' . URL_BASE . '/views/almacen/');
    exit;
    
} catch (PDOException $e) {
    error_log("❌ EXCEPCIÓN PDO: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $_SESSION['error'] = 'Error al procesar la solicitud: ' . $e->getMessage();
    header('Location: ' . URL_BASE . '/views/almacen/');
    exit;
}
