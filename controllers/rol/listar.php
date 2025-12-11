<?php
// Listar Roles (sin JSON - solo preparar datos)

try {
    $sql = "SELECT r.*, 
            (SELECT COUNT(*) FROM tb_usuarios u 
             WHERE u.id_rol = r.id_rol AND u.estado_registro = 'ACTIVO') as total_usuarios
            FROM tb_roles r 
            WHERE r.estado_registro = 'ACTIVO'
            ORDER BY r.id_rol ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $roles_datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al listar roles: " . $e->getMessage());
    $roles_datos = [];
}

// NO usar echo, print, jsonResponse()
