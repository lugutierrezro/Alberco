<?php
// Controlador para preparar datos de usuarios (SIN salida JSON)
try {
    $sql = "SELECT u.*, r.rol, e.nombres, e.apellidos, e.foto
            FROM tb_usuarios u
            INNER JOIN tb_roles r ON u.id_rol = r.id_rol
            LEFT JOIN tb_empleados e ON u.id_empleado = e.id_empleado
            WHERE u.estado_registro = 'ACTIVO'
            ORDER BY u.fyh_creacion DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios_datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al listar usuarios: " . $e->getMessage());
    $usuarios_datos = [];
}

// NO usar echo, print, jsonResponse(), var_dump()
