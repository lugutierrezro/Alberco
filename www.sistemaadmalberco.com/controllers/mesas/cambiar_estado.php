<?php
// Cambiar Estado de Mesa (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/mesas');
    exit;
}

try {
    $mesaId = (int)($_POST['id_mesa'] ?? 0);
    $nuevoEstado = strtoupper(sanitize($_POST['estado'] ?? ''));
    
    // Validar estado
    $estadosValidos = ['DISPONIBLE', 'OCUPADA', 'RESERVADA', 'MANTENIMIENTO'];
    
    if (!in_array($nuevoEstado, $estadosValidos)) {
        $_SESSION['error'] = 'Estado no válido';
        header('Location: ' . URL_BASE . '/mesas');
        exit;
    }
    
    // Actualizar estado
    $updateSql = "UPDATE tb_mesas 
                  SET estado = :estado, fyh_actualizacion = NOW() 
                  WHERE id_mesa = :id";
    
    $stmt = $pdo->prepare($updateSql);
    $result = $stmt->execute([
        ':estado' => $nuevoEstado,
        ':id' => $mesaId
    ]);
    
    if ($result) {
        $_SESSION['success'] = 'Estado de mesa actualizado correctamente a: ' . $nuevoEstado;
    } else {
        $_SESSION['error'] = 'Error al actualizar estado';
    }
    
    header('Location: ' . URL_BASE . '/mesas');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al cambiar estado de mesa: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/mesas');
    exit;
}
