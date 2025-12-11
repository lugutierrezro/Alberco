<?php
// Eliminar Compra (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';
include('../../contans/layout/sesion.php');

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'ADMINISTRADOR') {
    $_SESSION['error'] = 'No tiene permisos';
    header('Location: ' . URL_BASE . '/compras');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/compras');
    exit;
}

try {
    $compraId = (int)($_POST['id_compra'] ?? 0);
    
    if ($compraId <= 0) {
        $_SESSION['error'] = 'ID de compra inválido';
        header('Location: ' . URL_BASE . '/compras');
        exit;
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Obtener datos de la compra
    $getSql = "SELECT id_producto, cantidad FROM tb_compras WHERE id_compra = ?";
    $getStmt = $pdo->prepare($getSql);
    $getStmt->execute([$compraId]);
    $compra = $getStmt->fetch();
    
    if (!$compra) {
        $_SESSION['error'] = 'Compra no encontrada';
        header('Location: ' . URL_BASE . '/compras');
        exit;
    }
    
    // Restar del stock
    $updateStockSql = "UPDATE tb_almacen 
                       SET stock = stock - :cantidad 
                       WHERE id_producto = :producto";
    
    $updateStmt = $pdo->prepare($updateStockSql);
    $updateStmt->execute([
        ':cantidad' => $compra['cantidad'],
        ':producto' => $compra['id_producto']
    ]);
    
    // Soft delete de la compra
    $deleteSql = "UPDATE tb_compras 
                  SET estado_registro = 'INACTIVO', fyh_actualizacion = NOW() 
                  WHERE id_compra = ?";
    
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->execute([$compraId]);
    
    $pdo->commit();
    
    $_SESSION['success'] = 'Compra eliminada correctamente';
    header('Location: ' . URL_BASE . '/compras');
    exit;
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error al eliminar compra: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/compras');
    exit;
}
