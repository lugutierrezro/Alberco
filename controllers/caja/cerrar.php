<?php
// Cerrar Caja (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

// Solo cajeros y administradores
if (!in_array($_SESSION['user_role'], ['ADMINISTRADOR', 'CAJERO'])) {
    $_SESSION['error'] = 'No tiene permisos para cerrar caja';
    header('Location: ' . URL_BASE . '/caja');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/caja');
    exit;
}

try {
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $saldoReal = (float)($_POST['saldo_real'] ?? 0);
    $observaciones = sanitize($_POST['observaciones'] ?? '');
    
    // Verificar que la caja esté abierta
    $checkSql = "SELECT id_arqueo, saldo_inicial, saldo_esperado 
                 FROM tb_arqueo_caja 
                 WHERE fecha_arqueo = ? AND estado = 'abierto'
                 AND estado_registro = 'ACTIVO'";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$fecha]);
    $arqueo = $checkStmt->fetch();
    
    if (!$arqueo) {
        $_SESSION['error'] = 'No hay una caja abierta para cerrar en esta fecha';
        header('Location: ' . URL_BASE . '/views/caja/index.php');
        exit;
    }
    
    // Obtener saldo esperado actualizado (puede haber cambiado por movimientos recientes)
    require_once 'resumen.php'; // Usa la lógica de resumen para obtener totales
    // $totalIngresos y $totalEgresos deberían estar disponibles si incluimos resumen, 
    // pero resumen.php usa $_GET['fecha']. Mejor recalculamos aquí.
    
    $movSql = "SELECT 
                SUM(CASE WHEN tipo_movimiento = 'INGRESO' THEN monto ELSE 0 END) as ingresos,
                SUM(CASE WHEN tipo_movimiento = 'EGRESO' THEN monto ELSE 0 END) as egresos
               FROM tb_movimientos_caja
               WHERE DATE(fecha_movimiento) = ? AND estado_registro = 'ACTIVO'";
    $movStmt = $pdo->prepare($movSql);
    $movStmt->execute([$fecha]);
    $movs = $movStmt->fetch();
    
    $saldoEsperadoCalculado = $arqueo['saldo_inicial'] + ($movs['ingresos'] ?? 0) - ($movs['egresos'] ?? 0);

    // Calcular diferencia
    $diferencia = $saldoReal - $saldoEsperadoCalculado;
    
    // Cerrar caja
    $updateSql = "UPDATE tb_arqueo_caja SET
                  saldo_real = :saldo_real,
                  saldo_esperado = :saldo_esperado,
                  total_ingresos = :ingresos,
                  total_egresos = :egresos,
                  diferencia = :diferencia,
                  estado = 'cerrado',
                  id_usuario_cierre = :usuario,
                  hora_cierre = CURTIME(),
                  observaciones = CONCAT(COALESCE(observaciones, ''), ' | Cierre: ', :observaciones),
                  fyh_actualizacion = NOW()
                  WHERE id_arqueo = :id";
    
    $stmt = $pdo->prepare($updateSql);
    $result = $stmt->execute([
        ':saldo_real' => $saldoReal,
        ':saldo_esperado' => $saldoEsperadoCalculado,
        ':ingresos' => $movs['ingresos'] ?? 0,
        ':egresos' => $movs['egresos'] ?? 0,
        ':diferencia' => $diferencia,
        ':usuario' => $_SESSION['user_id'],
        ':observaciones' => $observaciones,
        ':id' => $arqueo['id_arqueo']
    ]);
    
    if ($result) {
        $mensaje = 'Caja cerrada correctamente. ';
        $mensaje .= 'Saldo esperado: S/ ' . number_format($arqueo['saldo_final'], 2) . ', ';
        $mensaje .= 'Saldo real: S/ ' . number_format($saldoReal, 2);
        
        if ($diferencia != 0) {
            $mensaje .= ', Diferencia: S/ ' . number_format(abs($diferencia), 2);
            if ($diferencia > 0) {
                $mensaje .= ' (Sobrante)';
            } else {
                $mensaje .= ' (Faltante)';
            }
        }
        
        $_SESSION['success'] = $mensaje;
        unset($_SESSION['id_arqueo_actual']);
    } else {
        $_SESSION['error'] = 'Error al cerrar la caja';
    }
    
    header('Location: ' . URL_BASE . '/caja');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al cerrar caja: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/caja');
    exit;
}
