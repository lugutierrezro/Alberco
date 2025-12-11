<?php
// Obtener Detalle de Rol (sin JSON - preparar datos)

try {
    $rolId = (int)($_GET['id_rol'] ?? 0);
    
    if ($rolId <= 0) {
        $_SESSION['error'] = 'ID de rol inválido';
        header('Location: ' . URL_BASE . '/views/roles');
        exit;
    }
    
    // Obtener rol
    $sql = "SELECT * FROM tb_roles WHERE id_rol = ? AND estado_registro = 'ACTIVO'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$rolId]);
    $rol_dato = $stmt->fetch();
    
    if (!$rol_dato) {
        $_SESSION['error'] = 'Rol no encontrado';
        header('Location: ' . URL_BASE . '/views/roles');
        exit;
    }
    
    // Obtener usuarios con este rol
    $sqlUsuarios = "SELECT u.id_usuario, u.username, u.email, e.nombres, e.apellidos
                    FROM tb_usuarios u
                    LEFT JOIN tb_empleados e ON u.id_empleado = e.id_empleado
                    WHERE u.id_rol = ? AND u.estado_registro = 'ACTIVO'
                    ORDER BY e.nombres";
    
    $stmtUsuarios = $pdo->prepare($sqlUsuarios);
    $stmtUsuarios->execute([$rolId]);
    $usuarios_rol = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al obtener rol: " . $e->getMessage());
    $_SESSION['error'] = 'Error al obtener detalle';
    header('Location: ' . URL_BASE . '/views/roles');
    exit;
}

// NO usar echo, print, jsonResponse()
