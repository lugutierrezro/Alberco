<?php
// Eliminar Cliente (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';
include('../../contans/layout/sesion.php');

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/views/clientes/');
    exit;
}

try {
    $clienteId = (int)($_POST['id_cliente'] ?? 0);
    
    // Verificar si tiene pedidos
    $checkPedidosSql = "SELECT COUNT(*) as total FROM tb_pedidos 
                        WHERE id_cliente = ? AND estado_registro = 'ACTIVO'";
    $checkPedidosStmt = $pdo->prepare($checkPedidosSql);
    $checkPedidosStmt->execute([$clienteId]);
    $count = $checkPedidosStmt->fetch();
    
    if ($count['total'] > 0) {
        $_SESSION['error'] = 'No se puede eliminar el cliente porque tiene pedidos registrados';
        header('Location: ' . URL_BASE . '/views/clientes/');
        exit;
    }
    
    // Soft delete
    $deleteSql = "UPDATE tb_clientes 
                  SET estado_registro = 'INACTIVO', fyh_actualizacion = NOW() 
                  WHERE id_cliente = ?";
    
    $stmt = $pdo->prepare($deleteSql);
    $result = $stmt->execute([$clienteId]);
    
    if ($result) {
        $_SESSION['success'] = 'Cliente eliminado correctamente';
    } else {
        $_SESSION['error'] = 'Error al eliminar el cliente';
    }
    
    header('Location: ' . URL_BASE . '/views/clientes/');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al eliminar cliente: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/views/clientes/');
    exit;
}
