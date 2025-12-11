<?php
// Historial de Compras de un Proveedor (sin JSON - preparar datos)

try {
    $proveedorId = (int)($_GET['id_proveedor'] ?? 0);
    $fechaInicio = $_GET['fecha_inicio'] ?? null;
    $fechaFin = $_GET['fecha_fin'] ?? null;
    
    if ($proveedorId <= 0) {
        $historial_compras = [];
    } else {
        $sql = "SELECT c.*, 
                       p.codigo as producto_codigo,
                       p.nombre as producto_nombre,
                       u.username as usuario
                FROM tb_compras c
                INNER JOIN tb_almacen p ON c.id_producto = p.id_producto
                INNER JOIN tb_usuarios u ON c.id_usuario = u.id_usuario
                WHERE c.id_proveedor = :proveedor_id
                AND c.estado_registro = 'ACTIVO'";
        
        $params = [':proveedor_id' => $proveedorId];
        
        if ($fechaInicio) {
            $sql .= " AND DATE(c.fecha_compra) >= :fecha_inicio";
            $params[':fecha_inicio'] = $fechaInicio;
        }
        
        if ($fechaFin) {
            $sql .= " AND DATE(c.fecha_compra) <= :fecha_fin";
            $params[':fecha_fin'] = $fechaFin;
        }
        
        $sql .= " ORDER BY c.fecha_compra DESC LIMIT 100";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $historial_compras = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    error_log("Error al obtener historial: " . $e->getMessage());
    $historial_compras = [];
}

// NO usar echo, print, jsonResponse()
