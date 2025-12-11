<?php
/**
 * Anular Compra
 * Anula una compra registrada y descuenta el stock que se había agregado
 */

require_once __DIR__ . '/../../services/database/config.php';
include('../../contans/layout/sesion.php');

// Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/views/compras');
    exit;
}

try {
    // Obtener datos
    $id_compra = (int)($_POST['id_compra'] ?? 0);
    $id_usuario = $_SESSION['id_usuario'];
    
    // Validar ID
    if ($id_compra <= 0) {
        $_SESSION['error'] = 'ID de compra inválido';
        header('Location: ' . URL_BASE . '/views/compras');
        exit;
    }
    
    // Verificar que la compra existe y está activa
    $sqlVerificar = "SELECT c.*, p.stock, p.nombre as producto_nombre
                     FROM tb_compras c
                     INNER JOIN tb_almacen p ON c.id_producto = p.id_producto
                     WHERE c.id_compra = ? AND c.estado_registro = 'ACTIVO'";
    
    $stmtVerificar = $pdo->prepare($sqlVerificar);
    $stmtVerificar->execute([$id_compra]);
    $compra = $stmtVerificar->fetch();
    
    if (!$compra) {
        $_SESSION['error'] = 'La compra no existe o ya fue anulada';
        header('Location: ' . URL_BASE . '/views/compras');
        exit;
    }
    
    // Verificar que hay suficiente stock para descontar
    if ($compra['stock'] < $compra['cantidad']) {
        $_SESSION['warning'] = 'Advertencia: El stock actual (' . $compra['stock'] . ') es menor a la cantidad comprada (' . $compra['cantidad'] . '). Se ajustará el stock a 0.';
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // 1. Descontar el stock del producto
    $nuevoStock = max(0, $compra['stock'] - $compra['cantidad']); // No permitir stock negativo
    
    $sqlActualizarStock = "UPDATE tb_almacen 
                          SET stock = ?,
                              fyh_actualizacion = NOW()
                          WHERE id_producto = ?";
    
    $stmtStock = $pdo->prepare($sqlActualizarStock);
    $stmtStock->execute([$nuevoStock, $compra['id_producto']]);
    
    // 2. Marcar la compra como anulada
    $sqlAnular = "UPDATE tb_compras 
                  SET estado_registro = 'ANULADO',
                      fyh_actualizacion = NOW()
                  WHERE id_compra = ?";
    
    $stmtAnular = $pdo->prepare($sqlAnular);
    $stmtAnular->execute([$id_compra]);
    
    // 3. Registrar en el historial de movimientos (opcional, pero recomendado)
    $sqlHistorial = "INSERT INTO tb_movimientos_stock 
                    (id_producto, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, 
                     motivo, id_usuario, fyh_creacion)
                    VALUES (?, 'SALIDA', ?, ?, ?, ?, ?, NOW())";
    
    try {
        $stmtHistorial = $pdo->prepare($sqlHistorial);
        $stmtHistorial->execute([
            $compra['id_producto'],
            $compra['cantidad'],
            $compra['stock'],
            $nuevoStock,
            'Anulación de compra #' . $id_compra,
            $id_usuario
        ]);
    } catch (PDOException $e) {
        // Si la tabla no existe, continuar sin registrar el historial
        error_log("No se pudo registrar en historial de movimientos: " . $e->getMessage());
    }
    
    // Confirmar transacción
    $pdo->commit();
    
    // Mensaje de éxito
    $_SESSION['success'] = 'Compra anulada correctamente. Stock actualizado de ' . $compra['stock'] . ' a ' . $nuevoStock . ' para el producto: ' . $compra['producto_nombre'];
    
    header('Location: ' . URL_BASE . '/views/compras');
    exit;
    
} catch (PDOException $e) {
    // Revertir transacción en caso de error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error al anular compra: " . $e->getMessage());
    $_SESSION['error'] = 'Error al anular la compra: ' . $e->getMessage();
    header('Location: ' . URL_BASE . '/views/compras');
    exit;
}
