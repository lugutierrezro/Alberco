<?php
// Listar Mesas (sin JSON - preparar datos)

try {
    $estado = $_GET['estado'] ?? null;
    $zona = $_GET['zona'] ?? null;
    
    $sql = "SELECT m.*, 
            COALESCE((SELECT COUNT(*) 
                      FROM tb_pedidos p 
                      INNER JOIN tb_estados e ON p.id_estado = e.id_estado
                      WHERE p.id_mesa = m.id_mesa 
                      AND e.nombre_estado IN ('Pendiente', 'En Preparación', 'Listo', 'En Camino')
                      AND p.estado_registro = 'ACTIVO'), 0) as pedidos_activos
            FROM tb_mesas m
            WHERE m.estado_registro = 'ACTIVO'";
    
    $params = [];
    
    if ($estado) {
        $sql .= " AND m.estado = :estado";
        $params[':estado'] = strtolower($estado);
    }
    
    if ($zona) {
        $sql .= " AND m.zona = :zona";
        $params[':zona'] = $zona;
    }
    
    $sql .= " ORDER BY m.numero_mesa ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $mesas_datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al listar mesas: " . $e->getMessage());
    $mesas_datos = [];
}

// NO usar echo, print, jsonResponse()
