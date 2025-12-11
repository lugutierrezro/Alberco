<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../services/database/config.php';
include('../../contans/layout/sesion.php');

// Validar sesión de usuario
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Método no permitido';
    header('Location: ' . URL_BASE . '/views/almacen/');
    exit;
}

// Función para sanitizar texto
function sanitize($str)
{
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

try {
    // Recoger datos
    $data = [
        'codigo' => sanitize($_POST['codigo'] ?? ''),
        'nombre' => sanitize($_POST['nombre'] ?? ''),
        'descripcion' => sanitize($_POST['descripcion'] ?? null),
        'stock' => (int)($_POST['stock'] ?? 0),
        'stock_minimo' => (int)($_POST['stock_minimo'] ?? 5),
        'stock_maximo' => (int)($_POST['stock_maximo'] ?? 100),
        'precio_compra' => (float)($_POST['precio_compra'] ?? 0),
        'precio_venta' => (float)($_POST['precio_venta'] ?? 0),
        'fecha_ingreso' => $_POST['fecha_ingreso'] ?? date('Y-m-d'),
        'disponible_venta' => isset($_POST['disponible_venta']) ? 1 : 0,
        'requiere_preparacion' => isset($_POST['requiere_preparacion']) ? 1 : 0,
        'tiempo_preparacion' => (int)($_POST['tiempo_preparacion'] ?? 15),
        'id_usuario' => $_SESSION['id_usuario'],
        'id_categoria' => (int)($_POST['id_categoria'] ?? 0)
    ];

    // Validaciones obligatorias
    if (empty($data['codigo']) || empty($data['nombre']) || $data['id_categoria'] <= 0) {
        $_SESSION['error'] = 'Complete todos los campos obligatorios (código, nombre, categoría)';
        header('Location: ' . URL_BASE . '/views/almacen/create.php');
        exit;
    }

    if ($data['precio_compra'] <= 0 || $data['precio_venta'] <= 0) {
        $_SESSION['error'] = 'Los precios deben ser mayores a cero';
        header('Location: ' . URL_BASE . '/views/almacen/create.php');
        exit;
    }

    if ($data['precio_venta'] <= $data['precio_compra']) {
        $_SESSION['error'] = 'El precio de venta debe ser mayor al precio de compra';
        header('Location: ' . URL_BASE . '/views/almacen/create.php');
        exit;
    }

    // Verificar código duplicado
    $checkSql = "SELECT id_producto FROM tb_almacen WHERE codigo = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$data['codigo']]);
    if ($checkStmt->fetch()) {
        $_SESSION['error'] = 'El código de producto ya existe';
        header('Location: ' . URL_BASE . '/views/almacen/create.php');
        exit;
    }

    // Manejar imagen
    $imagenPath = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__, 2) . '/uploads/almacen/';

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $fileType = $_FILES['imagen']['type'];

        if (!in_array($fileType, $allowedTypes)) {
            $_SESSION['error'] = 'Solo se permiten imágenes JPG, PNG o WEBP';
            header('Location: ' . URL_BASE . '/views/almacen/create.php');
            exit;
        }

        if ($_FILES['imagen']['size'] > 3 * 1024 * 1024) {
            $_SESSION['error'] = 'La imagen no debe superar los 3MB';
            header('Location: ' . URL_BASE . '/views/almacen/create.php');
            exit;
        }

        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $filename = 'PROD_' . $data['codigo'] . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $filepath)) {
            $imagenPath = 'uploads/almacen/' . $filename;
        }
    }

    // Insertar producto
    $insertSql = "INSERT INTO tb_almacen
              (codigo, nombre, descripcion, stock, stock_minimo, stock_maximo,
               precio_compra, precio_venta, fecha_ingreso, disponible_venta,
               requiere_preparacion, tiempo_preparacion,
               id_usuario, id_categoria, imagen, fyh_creacion)
              VALUES
              (:codigo, :nombre, :descripcion, :stock, :stock_minimo, :stock_maximo,
               :precio_compra, :precio_venta, :fecha_ingreso, :disponible_venta,
               :requiere_preparacion, :tiempo_preparacion,
               :id_usuario, :id_categoria, :imagen, NOW())";

    $stmt = $pdo->prepare($insertSql);

    // Ejecutar y capturar errores detalladamente
    if (!$stmt->execute([
        ':codigo' => $data['codigo'],
        ':nombre' => $data['nombre'],
        ':descripcion' => $data['descripcion'] ?: null,
        ':stock' => $data['stock'],
        ':stock_minimo' => $data['stock_minimo'],
        ':stock_maximo' => $data['stock_maximo'],
        ':precio_compra' => $data['precio_compra'],
        ':precio_venta' => $data['precio_venta'],
        ':fecha_ingreso' => $data['fecha_ingreso'],
        ':disponible_venta' => $data['disponible_venta'],
        ':requiere_preparacion' => $data['requiere_preparacion'],
        ':tiempo_preparacion' => $data['tiempo_preparacion'],
        ':id_usuario' => $data['id_usuario'],
        ':id_categoria' => $data['id_categoria'],
        ':imagen' => $imagenPath
    ])) {
        $errorInfo = $stmt->errorInfo();
        $_SESSION['error'] = "Error al insertar producto: [{$errorInfo[0]}] {$errorInfo[2]}";
        header('Location: ' . URL_BASE . '/views/almacen/create.php');
        exit;
    }

    $_SESSION['success'] = 'Producto creado correctamente - ' . $data['codigo'];
    header('Location: ' . URL_BASE . '/views/almacen/');
    exit;
} catch (PDOException $e) {
    error_log("Error al crear producto: " . $e->getMessage());
    $_SESSION['error'] = 'Error inesperado: ' . $e->getMessage();
    header('Location: ' . URL_BASE . '/views/almacen/create.php');
    exit;
}
