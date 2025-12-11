<?php
// Eliminar Producto (Soft Delete)

require_once __DIR__ . '/../../services/database/config.php';
include('../../contans/layout/sesion.php');

// ============================================
// LOGGING DE DEBUG
// ============================================
error_log("========== INICIO ELIMINAR PRODUCTO ==========");
error_log("POST Data: " . print_r($_POST, true));
error_log("Usuario ID: " . ($_SESSION['id_usuario'] ?? 'NO DEFINIDO'));

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("ERROR: Método no POST");
    header('Location: ' . URL_BASE . '/views/almacen/');
    exit;
}

try {
    $productoId = (int)($_POST['id_producto'] ?? 0);
    
    error_log("Producto ID a eliminar: $productoId");
    
    if ($productoId <= 0) {
        error_log("ERROR: ID inválido");
        $_SESSION['error'] = 'ID de producto inválido';
        header('Location: ' . URL_BASE . '/views/almacen/');
        exit;
    }
    
    // Verificar si el producto existe
    $checkSql = "SELECT id_producto, nombre, codigo FROM tb_almacen WHERE id_producto = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$productoId]);
    $producto = $checkStmt->fetch();
    
    if (!$producto) {
        error_log("ERROR: Producto no encontrado");
        $_SESSION['error'] = 'Producto no encontrado';
        header('Location: ' . URL_BASE . '/views/almacen/');
        exit;
    }
    
    error_log("Producto encontrado: " . $producto['nombre'] . " (Código: " . $producto['codigo'] . ")");
    
    // Verificar si tiene movimientos en ventas
    $checkVentasSql = "SELECT COUNT(*) as total FROM tb_detalle_ventas 
                       WHERE id_producto = ?";
    $checkVentasStmt = $pdo->prepare($checkVentasSql);
    $checkVentasStmt->execute([$productoId]);
    $ventas = $checkVentasStmt->fetch();
    
    error_log("Ventas relacionadas: " . $ventas['total']);
    
    // Verificar si tiene movimientos en pedidos
    $checkPedidosSql = "SELECT COUNT(*) as total FROM tb_detalle_pedidos 
                        WHERE id_producto = ?";
    $checkPedidosStmt = $pdo->prepare($checkPedidosSql);
    $checkPedidosStmt->execute([$productoId]);
    $pedidos = $checkPedidosStmt->fetch();
    
    error_log("Pedidos relacionados: " . $pedidos['total']);
    
    // Si tiene movimientos, solo desactivar pero informar al usuario
    if ($ventas['total'] > 0 || $pedidos['total'] > 0) {
        error_log("ADVERTENCIA: Producto tiene movimientos, solo se desactivará");
    }
    
    // Soft delete - cambiar estado a INACTIVO
    $deleteSql = "UPDATE tb_almacen 
                  SET estado_registro = 'INACTIVO', 
                      disponible_venta = 0,
                      fyh_actualizacion = NOW() 
                  WHERE id_producto = ?";
    
    $stmt = $pdo->prepare($deleteSql);
    $result = $stmt->execute([$productoId]);
    
    if ($result && $stmt->rowCount() > 0) {
        error_log("✅ Producto eliminado (soft delete) correctamente. Rows affected: " . $stmt->rowCount());
        
        if ($ventas['total'] > 0 || $pedidos['total'] > 0) {
            $_SESSION['success'] = 'Producto desactivado correctamente (tiene ' . ($ventas['total'] + $pedidos['total']) . ' movimientos asociados)';
        } else {
            $_SESSION['success'] = 'Producto eliminado correctamente';
        }
    } else {
        error_log("❌ Error: No se pudo eliminar. Rows affected: " . $stmt->rowCount());
        $_SESSION['error'] = 'No se pudo eliminar el producto';
    }
    
    error_log("========== FIN ELIMINAR PRODUCTO ==========");
    header('Location: ' . URL_BASE . '/views/almacen/');
    exit;
    
} catch (PDOException $e) {
    error_log("❌ EXCEPCIÓN PDO: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $_SESSION['error'] = 'Error al procesar la solicitud: ' . $e->getMessage();
    header('Location: ' . URL_BASE . '/views/almacen/');
    exit;
}
