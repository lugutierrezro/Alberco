<?php
// Actualizar Posición GPS (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'DELIVERY') {
    $_SESSION['error'] = 'No tiene permisos';
    header('Location: ' . URL_BASE . '/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/');
    exit;
}

try {
    // Obtener ID empleado del usuario
    $sqlUser = "SELECT id_empleado FROM tb_usuarios WHERE id_usuario = ?";
    $stmtUser = $pdo->prepare($sqlUser);
    $stmtUser->execute([$_SESSION['id_usuario']]);
    $user = $stmtUser->fetch();
    
    if (!$user || !$user['id_empleado']) {
        $_SESSION['error'] = 'Usuario sin empleado asignado';
        header('Location: ' . URL_BASE . '/');
        exit;
    }
    
    $trackingData = [
        'id_pedido' => (int)($_POST['id_pedido'] ?? 0),
        'id_empleado' => $user['id_empleado'],
        'latitud' => (float)($_POST['latitud'] ?? 0),
        'longitud' => (float)($_POST['longitud'] ?? 0),
        'velocidad' => !empty($_POST['velocidad']) ? (float)$_POST['velocidad'] : null,
        'rumbo' => !empty($_POST['rumbo']) ? (float)$_POST['rumbo'] : null,
        'bateria' => !empty($_POST['bateria']) ? (int)$_POST['bateria'] : null,
        'distancia_restante' => !empty($_POST['distancia_restante']) ? (float)$_POST['distancia_restante'] : null,
        'tiempo_estimado' => !empty($_POST['tiempo_estimado']) ? (int)$_POST['tiempo_estimado'] : null
    ];
    
    // Insertar posición
    $insertSql = "INSERT INTO tb_tracking_delivery 
                  (id_pedido, id_empleado, latitud, longitud, velocidad, rumbo, 
                   bateria, distancia_restante, tiempo_estimado, fyh_registro)
                  VALUES (:id_pedido, :id_empleado, :latitud, :longitud, :velocidad, 
                          :rumbo, :bateria, :distancia_restante, :tiempo_estimado, NOW())";
    
    $stmt = $pdo->prepare($insertSql);
    $result = $stmt->execute($trackingData);
    
    if ($result) {
        $_SESSION['success'] = 'Posición actualizada';
    } else {
        $_SESSION['error'] = 'Error al actualizar posición';
    }
    
    // Redirigir al dashboard de delivery
    header('Location: ' . URL_BASE . '/delivery/dashboard.php');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al actualizar posición: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/delivery/dashboard.php');
    exit;
}
