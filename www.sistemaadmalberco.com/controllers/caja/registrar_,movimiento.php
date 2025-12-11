<?php
// Registrar Movimiento de Caja (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/caja');
    exit;
}

try {
    $data = [
        'tipo_movimiento' => strtoupper(sanitize($_POST['tipo_movimiento'] ?? '')),
        'monto' => (float)($_POST['monto'] ?? 0),
        'concepto' => sanitize($_POST['concepto'] ?? ''),
        'referencia' => sanitize($_POST['referencia'] ?? ''),
        'id_usuario' => $_SESSION['user_id']
    ];
    
    // Validaciones
    if (!in_array($data['tipo_movimiento'], ['INGRESO', 'EGRESO'])) {
        $_SESSION['error'] = 'Tipo de movimiento inválido';
        header('Location: ' . URL_BASE . '/caja');
        exit;
    }
    
    if ($data['monto'] <= 0) {
        $_SESSION['error'] = 'El monto debe ser mayor a cero';
        header('Location: ' . URL_BASE . '/caja');
        exit;
    }
    
    if (empty($data['concepto'])) {
        $_SESSION['error'] = 'Debe especificar un concepto';
        header('Location: ' . URL_BASE . '/caja');
        exit;
    }
    
    // Verificar que haya una caja abierta
    $checkCajaSql = "SELECT id_arqueo FROM tb_arqueo_caja 
                     WHERE fecha = CURDATE() AND estado = 'ABIERTA'";
    $checkCajaStmt = $pdo->prepare($checkCajaSql);
    $checkCajaStmt->execute();
    $cajaAbierta = $checkCajaStmt->fetch();
    
    if (!$cajaAbierta) {
        $_SESSION['error'] = 'Debe abrir la caja antes de registrar movimientos';
        header('Location: ' . URL_BASE . '/caja');
        exit;
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Registrar movimiento
    $insertSql = "INSERT INTO tb_movimientos_caja 
                  (id_arqueo, tipo_movimiento, monto, concepto, referencia, 
                   id_usuario, fecha_movimiento, fyh_creacion)
                  VALUES 
                  (:arqueo, :tipo, :monto, :concepto, :referencia,
                   :usuario, NOW(), NOW())";
    
    $stmt = $pdo->prepare($insertSql);
    $stmt->execute([
        ':arqueo' => $cajaAbierta['id_arqueo'],
        ':tipo' => $data['tipo_movimiento'],
        ':monto' => $data['monto'],
        ':concepto' => $data['concepto'],
        ':referencia' => $data['referencia'],
        ':usuario' => $data['id_usuario']
    ]);
    
    // Actualizar saldo en arqueo de caja
    if ($data['tipo_movimiento'] === 'INGRESO') {
        $updateArqueoSql = "UPDATE tb_arqueo_caja 
                           SET saldo_final = saldo_final + :monto 
                           WHERE id_arqueo = :id";
    } else {
        $updateArqueoSql = "UPDATE tb_arqueo_caja 
                           SET saldo_final = saldo_final - :monto 
                           WHERE id_arqueo = :id";
    }
    
    $updateStmt = $pdo->prepare($updateArqueoSql);
    $updateStmt->execute([
        ':monto' => $data['monto'],
        ':id' => $cajaAbierta['id_arqueo']
    ]);
    
    $pdo->commit();
    
    $_SESSION['success'] = 'Movimiento registrado correctamente: ' . $data['tipo_movimiento'] . ' de S/ ' . number_format($data['monto'], 2);
    header('Location: ' . URL_BASE . '/caja');
    exit;
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error al registrar movimiento: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/caja');
    exit;
}
