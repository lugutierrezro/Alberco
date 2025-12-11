<?php
/**
 * API: Login de Empleados (para App Móvil)
 * Autenticación con email del usuario asignado y password
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../../services/database/config.php';

try {
    // Obtener datos del request
    $data = json_decode(file_get_contents('php://input'), true);
    
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    // Validar datos requeridos
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'mensaje' => 'Email y contraseña son requeridos'
        ]);
        exit;
    }
    
    // Buscar usuario por email
    $stmt = $pdo->prepare("
        SELECT 
            u.id_usuario,
            u.username,
            u.email,
            u.password_user,
            u.id_rol,
            u.id_empleado,
            e.codigo_empleado,
            e.nombres as empleado_nombre,
            e.apellidos,
            e.telefono,
            e.celular,
            e.estado_laboral,
            r.rol as rol_nombre
        FROM tb_usuarios u
        LEFT JOIN tb_empleados e ON u.id_empleado = e.id_empleado
        LEFT JOIN tb_roles r ON u.id_rol = r.id_rol
        WHERE u.email = ?
        AND u.estado_registro = 'ACTIVO'
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verificar si existe el usuario
    if (!$usuario) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'mensaje' => 'Credenciales inválidas'
        ]);
        exit;
    }
    
    // Verificar contraseña
    if (!password_verify($password, $usuario['password_user'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'mensaje' => 'Credenciales inválidas'
        ]);
        exit;
    }
    
    // Verificar que tenga empleado asociado
    if (empty($usuario['id_empleado'])) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'mensaje' => 'Este usuario no tiene un empleado asociado'
        ]);
        exit;
    }
    
    // Verificar que el empleado esté activo
    if ($usuario['estado_laboral'] !== 'ACTIVO') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'mensaje' => 'El empleado no está activo'
        ]);
        exit;
    }
    
    // Actualizar último acceso
    $stmtUpdate = $pdo->prepare("
        UPDATE tb_usuarios 
        SET ultimo_acceso = NOW() 
        WHERE id_usuario = ?
    ");
    $stmtUpdate->execute([$usuario['id_usuario']]);
    
    // Login exitoso - generar token
    $token = bin2hex(random_bytes(32));
    
    // Remover password de la respuesta
    unset($usuario['password_user']);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'mensaje' => 'Login exitoso',
        'token' => $token,
        'empleado' => [
            'id_empleado' => (int)$usuario['id_empleado'],
            'id_usuario' => (int)$usuario['id_usuario'],
            'codigo_empleado' => $usuario['codigo_empleado'],
            'nombre' => $usuario['empleado_nombre'],
            'apellidos' => $usuario['apellidos'],
            'nombre_completo' => trim($usuario['empleado_nombre'] . ' ' . $usuario['apellidos']),
            'telefono' => $usuario['telefono'],
            'celular' => $usuario['celular'],
            'email' => $usuario['email'],
            'estado_laboral' => $usuario['estado_laboral'],
            'rol' => $usuario['rol_nombre']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Error en login_empleado: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error del servidor: ' . $e->getMessage() // MOSTRAR EL ERROR
    ]);
} catch (Exception $e) {
    error_log("Error en login_empleado: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error inesperado. Por favor, contacte al administrador.'
    ]);
}
