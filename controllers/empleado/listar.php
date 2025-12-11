<?php
// Listar Empleados (sin JSON - preparar datos)

try {
    $estadoLaboral = $_GET['estado_laboral'] ?? null;
    
    $sql = "SELECT e.*, r.rol as nombre_rol, u.username, u.email as email_usuario
            FROM tb_empleados e
            LEFT JOIN tb_usuarios u ON e.id_empleado = u.id_empleado
            LEFT JOIN tb_roles r ON e.id_rol = r.id_rol 
            WHERE e.estado_registro = 'ACTIVO'";
    
    $params = [];
    
    if ($estadoLaboral === 'ACTIVO') {
        $sql .= " AND e.estado_laboral = 'ACTIVO'";
    }
    
    $sql .= " ORDER BY e.nombres, e.apellidos";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $empleados_datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al listar empleados: " . $e->getMessage());
    $empleados_datos = [];
}

// NO usar echo, print, jsonResponse()
