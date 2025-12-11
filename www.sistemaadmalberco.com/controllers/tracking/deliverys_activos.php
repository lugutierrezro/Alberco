<?php
// Obtener Deliverys Activos (sin JSON - preparar datos)

try {
    $sql = "SELECT DISTINCT 
                e.id_empleado,
                e.nombres,
                e.apellidos,
                e.foto,
                t.latitud,
                t.longitud,
                t.velocidad,
                t.bateria,
                t.fyh_registro,
                p.codigo_pedido,
                p.id_pedido,
                p.estado
            FROM tb_empleados e
            INNER JOIN tb_usuarios u ON e.id_empleado = u.id_empleado
            INNER JOIN tb_roles r ON u.id_rol = r.id_rol
            INNER JOIN tb_pedidos p ON e.id_empleado = p.id_delivery
            LEFT JOIN (
                SELECT id_pedido, id_empleado, latitud, longitud, velocidad, bateria, fyh_registro,
                       ROW_NUMBER() OVER (PARTITION BY id_pedido ORDER BY fyh_registro DESC) as rn
                FROM tb_tracking_delivery
            ) t ON p.id_pedido = t.id_pedido AND t.rn = 1
            WHERE r.rol = 'DELIVERY'
            AND p.estado IN ('EN_CAMINO', 'PREPARANDO')
            AND p.tipo_pedido = 'DELIVERY'
            ORDER BY e.nombres";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $deliverys_activos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al obtener deliverys activos: " . $e->getMessage());
    $deliverys_activos = [];
}

// NO usar echo, print, jsonResponse()
