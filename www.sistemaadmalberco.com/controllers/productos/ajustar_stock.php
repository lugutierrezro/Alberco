<?php
// Ajustar Stock de Producto (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'ADMINISTRADOR') {
    $_SESSION['error'] = 'No tiene permisos';
    header('Location: ' . URL_BASE . '/productos');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/productos');
    exit;
}

try {
    $productoId = (int)($_POST['id_producto'] ?? 0);
    $nuevoStock = (int)($_POST['stock'] ?? 0);
    $motivo = sanitize($_POST['motivo'] ?? '');
    
    if ($productoId <= 0) {
        $_SESSION['error'] = 'ID de producto inválido';
        header('Location: ' . URL_BASE . '/views/productos');
        exit;
    }
    
    if ($nuevoStock < 0) {
        $_SESSION['error'] = 'El stock no puede ser negativo';
        header('Location: ' . URL_BASE . '/views/productos/detalle.php?id=' . $productoId);
        exit;
    }
    
    if (empty($motivo)) {
        $_SESSION['error'] = 'Debe especificar un motivo para el ajuste';
        header('Location: ' . URL_BASE . '/views/productos/detalle.php?id=' . $productoId);
        exit;
    }
    
    // Obtener stock actual
    $getSql = "SELECT stock, nombre FROM tb_almacen WHERE id_producto = ?";
    $getStmt = $pdo->prepare($getSql);
    $getStmt->execute([$productoId]);
    $producto = $getStmt->fetch();
    
    if (!$producto) {
        $_SESSION['error'] = 'Producto no encontrado';
        header('Location: ' . URL_BASE . '/views/productos/');
        exit;
    }
    
    $stockAnterior = $producto['stock'];
    $diferencia = $nuevoStock - $stockAnterior;
    
    // Actualizar stock
    $updateSql = "UPDATE tb_almacen SET stock = :stock, fyh_actualizacion = NOW() 
                  WHERE id_producto = :id";
    $stmt = $pdo->prepare($updateSql);
    $result = $stmt->execute([
        ':stock' => $nuevoStock,
        ':id' => $productoId
    ]);
    
    if ($result) {
        // Registrar ajuste en log (si tienes tabla de logs)
        $logSql = "INSERT INTO tb_stock_logs 
                   (id_producto, stock_anterior, stock_nuevo, diferencia, motivo, id_usuario, fyh_registro)
                   VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        try {
            $logStmt = $pdo->prepare($logSql);
            $logStmt->execute([
                $productoId,
                $stockAnterior,
                $nuevoStock,
                $diferencia,
                $motivo,
                $_SESSION['id_usuario']
            ]);
        } catch (PDOException $e) {
            // Si no existe la tabla de logs, continuar sin error
            error_log("Advertencia - No se pudo registrar log de stock: " . $e->getMessage());
        }
        
        $_SESSION['success'] = 'Stock ajustado correctamente. ' . 
                               'Stock anterior: ' . $stockAnterior . ', ' .
                               'Stock nuevo: ' . $nuevoStock . ' ' .
                               '(' . ($diferencia >= 0 ? '+' : '') . $diferencia . ')';
    } else {
        $_SESSION['error'] = 'Error al ajustar el stock';
    }
    
    header('Location: ' . URL_BASE . '/views/productos/detalle.php?id=' . $productoId);
    exit;
    
} catch (PDOException $e) {
    error_log("Error al ajustar stock: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/views/productos/');
    exit;
}
