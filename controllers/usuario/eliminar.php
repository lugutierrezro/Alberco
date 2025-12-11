<?php
/**
 * Controlador: Eliminar Usuario (Soft Delete)
 * Marca un usuario como INACTIVO en lugar de eliminarlo físicamente
 */

// Iniciar sesión (config.php no la inicia para peticiones JSON)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../services/database/config.php';
header('Content-Type: application/json; charset=utf-8');

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar permisos
if ($_SESSION['user_role'] !== 'ADMINISTRADOR') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tiene permisos'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $userId = (int)($_POST['id_usuario'] ?? 0);
    
    // Validar ID
    if ($userId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de usuario inválido'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // No permitir eliminar el propio usuario
    if ($userId === $_SESSION['user_id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No puede eliminar su propio usuario'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Soft delete
    $sql = "UPDATE tb_usuarios 
            SET estado_registro = 'INACTIVO', 
                fyh_actualizacion = NOW()
            WHERE id_usuario = :id";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([':id' => $userId]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Usuario eliminado correctamente'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado'], JSON_UNESCAPED_UNICODE);
    }
    
} catch (PDOException $e) {
    error_log("Error PDO al eliminar usuario: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log("Error general al eliminar usuario: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
