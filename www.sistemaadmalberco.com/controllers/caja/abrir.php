<?php
// Abrir Caja (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

// Solo cajeros y administradores
if (!in_array($_SESSION['user_role'], ['ADMINISTRADOR', 'CAJERO'])) {
    $_SESSION['error'] = 'No tiene permisos para abrir caja';
    header('Location: ' . URL_BASE . '/views/caja/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/views/caja/');
    exit;
}

try {
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $saldoInicial = (float)($_POST['monto_inicial'] ?? $_POST['saldo_inicial'] ?? 0);
    $observaciones = $_POST['observaciones'] ?? '';
    
    // Verificar si ya hay una caja abierta para hoy
    $checkSql = "SELECT id_arqueo FROM tb_arqueo_caja 
                 WHERE fecha_arqueo = ? AND estado_registro = 'ACTIVO'";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$fecha]);
    
    if ($checkStmt->fetch()) {
        $_SESSION['error'] = 'Ya existe una caja registrada para el día de hoy (' . date('d/m/Y', strtotime($fecha)) . ')';
        header('Location: ' . URL_BASE . '/views/caja/');
        exit;
    }
    
    // Verificar si hay alguna caja abierta de otro día (opcional, pero recomendable cerrar antes de abrir)
    $checkOpenSql = "SELECT id_arqueo, fecha_arqueo FROM tb_arqueo_caja 
                     WHERE estado = 'abierto' AND estado_registro = 'ACTIVO'";
    $checkOpenStmt = $pdo->query($checkOpenSql);
    if ($prevOpen = $checkOpenStmt->fetch()) {
        $_SESSION['error'] = 'Existe una caja abierta del día ' . date('d/m/Y', strtotime($prevOpen['fecha_arqueo'])) . '. Debe cerrarla primero.';
        header('Location: ' . URL_BASE . '/views/caja/');
        exit;
    }
    
    // Verificar que el usuario ID existe y es válido
    $userId = $_SESSION['user_id'] ?? null;
    
    if (!$userId) {
        error_log("ERROR: user_id no encontrado en sesión");
        $_SESSION['error'] = 'No se pudo identificar el usuario. Por favor, inicie sesión nuevamente.';
        header('Location: ' . URL_BASE . '/views/login/');
        exit;
    }
    
    // Log de depuración
    error_log("=== APERTURA DE CAJA ===");
    error_log("Fecha: " . $fecha);
    error_log("Saldo Inicial: " . $saldoInicial);
    error_log("Usuario ID: " . $userId);
    error_log("Observaciones: " . $observaciones);
    
    // Abrir caja
    $insertSql = "INSERT INTO tb_arqueo_caja 
                  (fecha_arqueo, hora_apertura, saldo_inicial, saldo_esperado, 
                   estado, id_usuario_apertura, observaciones, fyh_creacion)
                  VALUES 
                  (:fecha, CURTIME(), :saldo_inicial, :saldo_esperado, 
                   'abierto', :usuario, :observaciones, NOW())";
    
    $stmt = $pdo->prepare($insertSql);
    $result = $stmt->execute([
        ':fecha' => $fecha,
        ':saldo_inicial' => $saldoInicial,
        ':saldo_esperado' => $saldoInicial,
        ':usuario' => $userId,
        ':observaciones' => $observaciones
    ]);
    
    if ($result) {
        $arqueoId = $pdo->lastInsertId();
        $_SESSION['success'] = 'Caja abierta correctamente. Saldo inicial: S/ ' . number_format($saldoInicial, 2);
    } else {
        $_SESSION['error'] = 'Error al abrir la caja';
    }
    
    header('Location: ' . URL_BASE . '/views/caja/');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al abrir caja: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    
    // En desarrollo, mostrar el error completo
    if (ENVIRONMENT === 'development') {
        $_SESSION['error'] = 'Error al procesar la solicitud: ' . $e->getMessage();
    } else {
        $_SESSION['error'] = 'Error al procesar la solicitud';
    }
    
    header('Location: ' . URL_BASE . '/views/caja/');
    exit;
}
