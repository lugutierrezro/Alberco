<?php
// Listar Pedidos (sin JSON - preparar datos)

try {
    $tipo = $_GET['tipo'] ?? null;
    $estado = $_GET['estado'] ?? null;
    $activos = isset($_GET['activos']) && $_GET['activos'] === '1';
    
    $sql = "SELECT p.*, 
                   c.nombres as cliente_nombre,
                   c.apellidos as cliente_apellidos,
                   m.numero_mesa,
                   m.capacidad as mesa_capacidad,
                   e.nombres as delivery_nombre,
                   u.username as usuario_registro
            FROM tb_pedidos p
            INNER JOIN tb_clientes c ON p.id_cliente = c.id_cliente
            LEFT JOIN tb_mesas m ON p.id_mesa = m.id_mesa
            LEFT JOIN tb_empleados e ON p.id_delivery = e.id_empleado
            INNER JOIN tb_usuarios u ON p.id_usuario = u.id_usuario
            WHERE p.estado_registro = 'ACTIVO'";
    
    $params = [];
    
    if ($estado) {
        $sql .= " AND p.estado = :estado";
        $params[':estado'] = strtoupper($estado);
    }
    
    if ($activos) {
        $sql .= " AND p.estado IN ('PENDIENTE', 'EN_PREPARACION', 'LISTO', 'EN_CAMINO')";
    }
    
    if ($tipo) {
        $sql .= " AND p.tipo_pedido = :tipo";
        $params[':tipo'] = strtoupper($tipo);
    }
    
    $sql .= " ORDER BY p.fecha_pedido DESC LIMIT 100";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pedidos_datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al listar pedidos: " . $e->getMessage());
    $pedidos_datos = [];
}

// NO usar echo, print, jsonResponse()
