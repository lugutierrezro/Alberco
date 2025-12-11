<?php
// Registrar Compra (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';
include('../../contans/layout/sesion.php');

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/compras');
    exit;
}

try {
    $compraData = [
        'id_producto' => (int)($_POST['id_producto'] ?? 0),
        'fecha_compra' => $_POST['fecha_compra'] ?? date('Y-m-d'),
        'id_proveedor' => (int)($_POST['id_proveedor'] ?? 0),
        'comprobante' => sanitize($_POST['comprobante'] ?? ''),
        'id_usuario' => $_SESSION['id_usuario'],
        'precio_compra' => (float)($_POST['precio_compra'] ?? 0),
        'cantidad' => (int)($_POST['cantidad'] ?? 0),
        'observaciones' => sanitize($_POST['observaciones'] ?? '')
    ];
    
    // Validaciones
    if ($compraData['cantidad'] <= 0) {
        $_SESSION['error'] = 'La cantidad debe ser mayor a cero';
        header('Location: ' . URL_BASE . '/compras/create.php');
        exit;
    }
    
    if ($compraData['precio_compra'] <= 0) {
        $_SESSION['error'] = 'El precio debe ser mayor a cero';
        header('Location: ' . URL_BASE . '/compras/create.php');
        exit;
    }
    
    if ($compraData['id_producto'] <= 0 || $compraData['id_proveedor'] <= 0) {
        $_SESSION['error'] = 'Debe seleccionar producto y proveedor';
        header('Location: ' . URL_BASE . '/compras/create.php');
        exit;
    }
    
    // Calcular totales
    $subtotal = $compraData['precio_compra'] * $compraData['cantidad'];
    $igv = $subtotal * 0.18;
    $total = $subtotal + $igv;
    
    // Generar número de compra
    $nroCompra = 'COMP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Insertar compra
    $insertSql = "INSERT INTO tb_compras 
                  (nro_compra, id_producto, fecha_compra, id_proveedor, comprobante,
                   id_usuario, precio_compra, cantidad, subtotal, igv, total, 
                   observaciones, fyh_creacion)
                  VALUES 
                  (:nro_compra, :producto, :fecha, :proveedor, :comprobante,
                   :usuario, :precio, :cantidad, :subtotal, :igv, :total,
                   :observaciones, NOW())";
    
    $stmt = $pdo->prepare($insertSql);
    $stmt->execute([
        ':nro_compra' => $nroCompra,
        ':producto' => $compraData['id_producto'],
        ':fecha' => $compraData['fecha_compra'],
        ':proveedor' => $compraData['id_proveedor'],
        ':comprobante' => $compraData['comprobante'],
        ':usuario' => $compraData['id_usuario'],
        ':precio' => $compraData['precio_compra'],
        ':cantidad' => $compraData['cantidad'],
        ':subtotal' => $subtotal,
        ':igv' => $igv,
        ':total' => $total,
        ':observaciones' => $compraData['observaciones']
    ]);
    
    $compraId = $pdo->lastInsertId();
    
    // Actualizar stock del producto
    $updateStockSql = "UPDATE tb_almacen 
                       SET stock = stock + :cantidad,
                           fyh_actualizacion = NOW()
                       WHERE id_producto = :producto";
    
    $updateStmt = $pdo->prepare($updateStockSql);
    $updateStmt->execute([
        ':cantidad' => $compraData['cantidad'],
        ':producto' => $compraData['id_producto']
    ]);
    
    $pdo->commit();
    
    $_SESSION['success'] = 'Compra registrada correctamente - ' . $nroCompra;
    header('Location: ' . URL_BASE . '/compras/detalle.php?id=' . $compraId);
    exit;
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error al registrar compra: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la compra';
    header('Location: ' . URL_BASE . '/compras/create.php');
    exit;
}
