<?php
// Restaurar Producto (Soft Restore)

require_once __DIR__ . '/../../services/database/config.php';
include('../../contans/layout/sesion.php');

// ============================================
// SOLO ADMINISTRADORES
// ============================================
if (!isset($_SESSION['user_role_id']) || $_SESSION['user_role_id'] != 1) {
    $_SESSION['error'] = 'Acceso denegado. Solo administradores pueden restaurar productos.';
    header('Location: ' . URL_BASE . '/views/almacen/');
    exit;
}

// ============================================
// LOGGING DE DEBUG
// ============================================
error_log("========== INICIO RESTAURAR PRODUCTO ==========");
error_log("POST Data: " . print_r($_POST, true));
error_log("Usuario ID: " . ($_SESSION['user_id'] ?? 'NO DEFINIDO'));

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("ERROR: Método no POST");
    header('Location: ' . URL_BASE . '/views/almacen/papelera.php');
    exit;
}

try {
    $productoId = (int)($_POST['id_producto'] ?? 0);
    
    error_log("Producto ID a restaurar: $productoId");
    
    if ($productoId <= 0) {
        error_log("ERROR: ID inválido");
        $_SESSION['error'] = 'ID de producto inválido';
        header('Location: ' . URL_BASE . '/views/almacen/papelera.php');
        exit;
    }
    
    // Verificar que el producto existe y está INACTIVO
    $checkSql = "SELECT id_producto, nombre, codigo, estado_registro 
                 FROM tb_almacen 
                 WHERE id_producto = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$productoId]);
    $producto = $checkStmt->fetch();
    
    if (!$producto) {
        error_log("ERROR: Producto no encontrado");
        $_SESSION['error'] = 'Producto no encontrado';
        header('Location: ' . URL_BASE . '/views/almacen/papelera.php');
        exit;
    }
    
    if ($producto['estado_registro'] !== 'INACTIVO') {
        error_log("ADVERTENCIA: Producto ya está activo");
        $_SESSION['warning'] = 'El producto ya está activo';
        header('Location: ' . URL_BASE . '/views/almacen/papelera.php');
        exit;
    }
    
    error_log("Producto encontrado: " . $producto['nombre'] . " (Código: " . $producto['codigo'] . ")");
    
    // Restaurar producto - cambiar estado a ACTIVO
    $restaurarSql = "UPDATE tb_almacen 
                     SET estado_registro = 'ACTIVO', 
                         disponible_venta = 1,
                         fyh_actualizacion = NOW() 
                     WHERE id_producto = ?";
    
    $stmt = $pdo->prepare($restaurarSql);
    $result = $stmt->execute([$productoId]);
    
    if ($result && $stmt->rowCount() > 0) {
        error_log("✅ Producto restaurado correctamente. Rows affected: " . $stmt->rowCount());
        
        // Registrar en auditoría (opcional)
        $auditSql = "INSERT INTO tb_auditoria 
                     (tabla_afectada, accion, id_registro, usuario_id, datos_nuevos, fyh_creacion)
                     VALUES 
                     ('tb_almacen', 'RESTAURAR', ?, ?, ?, NOW())";
        try {
            $auditStmt = $pdo->prepare($auditSql);
            $auditStmt->execute([
                $productoId,
                $_SESSION['user_id'],
                json_encode(['producto' => $producto['nombre'], 'codigo' => $producto['codigo']])
            ]);
        } catch (Exception $e) {
            error_log("Advertencia: No se pudo registrar auditoría: " . $e->getMessage());
        }
        
        $_SESSION['success'] = 'Producto "' . $producto['nombre'] . '" restaurado correctamente y disponible para venta';
    } else {
        error_log("❌ Error: No se pudo restaurar. Rows affected: " . $stmt->rowCount());
        $_SESSION['error'] = 'No se pudo restaurar el producto';
    }
    
    error_log("========== FIN RESTAURAR PRODUCTO ==========");
    header('Location: ' . URL_BASE . '/views/almacen/papelera.php');
    exit;
    
} catch (PDOException $e) {
    error_log("❌ EXCEPCIÓN PDO: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $_SESSION['error'] = 'Error al procesar la solicitud: ' . $e->getMessage();
    header('Location: ' . URL_BASE . '/views/almacen/papelera.php');
    exit;
}
