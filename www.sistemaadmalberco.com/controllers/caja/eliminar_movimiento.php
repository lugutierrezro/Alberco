<?php
/**
 * Eliminar Movimiento de Caja (Con Auditoría)
 */

require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/views/caja/movimientos.php');
    exit;
}

try {
    $id_movimiento = (int)($_POST['id_movimiento'] ?? 0);
    $id_usuario = $_SESSION['user_id'];
    $motivo_eliminacion = $_POST['motivo'] ?? 'Sin motivo especificado';
    
    if ($id_movimiento <= 0) {
        $_SESSION['error'] = 'ID de movimiento inválido';
        header('Location: ' . URL_BASE . '/views/caja/movimientos.php');
        exit;
    }
    
    // Obtener datos del movimiento
    $sqlMovimiento = "SELECT m.*, c.estado as caja_estado, c.id_usuario as caja_usuario
                      FROM tb_movimientos_caja m
                      INNER JOIN tb_caja c ON m.id_caja = c.id_caja
                      WHERE m.id_movimiento = ?";
    
    $stmtMovimiento = $pdo->prepare($sqlMovimiento);
    $stmtMovimiento->execute([$id_movimiento]);
    $movimiento = $stmtMovimiento->fetch();
    
    if (!$movimiento) {
        $_SESSION['error'] = 'El movimiento no existe';
        header('Location: ' . URL_BASE . '/views/caja/movimientos.php');
        exit;
    }
    
    // Validaciones
    if ($movimiento['caja_estado'] !== 'ABIERTA') {
        $_SESSION['error'] = 'No se pueden eliminar movimientos de una caja cerrada';
        header('Location: ' . URL_BASE . '/views/caja/movimientos.php');
        exit;
    }
    
    if ($movimiento['caja_usuario'] != $id_usuario) {
        $_SESSION['error'] = 'No tiene permisos para eliminar este movimiento';
        header('Location: ' . URL_BASE . '/views/caja/movimientos.php');
        exit;
    }
    
    if ((isset($movimiento['es_venta']) && $movimiento['es_venta'] == 1) || 
        (isset($movimiento['id_pedido']) && $movimiento['id_pedido'] > 0)) {
        $_SESSION['error'] = 'No se pueden eliminar movimientos generados automáticamente';
        header('Location: ' . URL_BASE . '/views/caja/movimientos.php');
        exit;
    }
    
    $pdo->beginTransaction();
    
    // 1. Guardar en tabla de auditoría (opcional)
    $sqlAuditoria = "INSERT INTO tb_auditoria_movimientos_caja 
                    (id_movimiento_original, id_caja, tipo_movimiento, concepto, monto, 
                     forma_pago, fecha_movimiento_original, id_usuario_elimino, 
                     motivo_eliminacion, fyh_eliminacion)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    try {
        $stmtAuditoria = $pdo->prepare($sqlAuditoria);
        $stmtAuditoria->execute([
            $id_movimiento,
            $movimiento['id_caja'],
            $movimiento['tipo_movimiento'],
            $movimiento['concepto'],
            $movimiento['monto'],
            $movimiento['forma_pago'],
            $movimiento['fecha_movimiento'],
            $id_usuario,
            $motivo_eliminacion
        ]);
    } catch (PDOException $e) {
        // Si la tabla de auditoría no existe, continuar sin guardar
        error_log("No se pudo guardar en auditoría: " . $e->getMessage());
    }
    
    // 2. Eliminar el movimiento
    $sqlEliminar = "DELETE FROM tb_movimientos_caja WHERE id_movimiento = ?";
    $stmtEliminar = $pdo->prepare($sqlEliminar);
    $stmtEliminar->execute([$id_movimiento]);
    
    $pdo->commit();
    
    $_SESSION['success'] = 'Movimiento eliminado correctamente';
    header('Location: ' . URL_BASE . '/views/caja/movimientos.php');
    exit;
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error al eliminar movimiento: " . $e->getMessage());
    $_SESSION['error'] = 'Error al eliminar el movimiento';
    header('Location: ' . URL_BASE . '/views/caja/movimientos.php');
    exit;
}
