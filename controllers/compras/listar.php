<?php
// Listar Compras (sin JSON - preparar datos)

try {
    $fechaInicio = $_GET['fecha_inicio'] ?? null;
    $fechaFin = $_GET['fecha_fin'] ?? null;
    $idProveedor = isset($_GET['id_proveedor']) ? (int)$_GET['id_proveedor'] : null;
    
    $sql = "SELECT c.*, 
                   p.nombre as producto_nombre,
                   p.codigo as producto_codigo,
                   prov.nombre_proveedor,
                   prov.empresa as proveedor_empresa,
                   u.username as usuario_registro
            FROM tb_compras c
            INNER JOIN tb_almacen p ON c.id_producto = p.id_producto
            INNER JOIN tb_proveedores prov ON c.id_proveedor = prov.id_proveedor
            INNER JOIN tb_usuarios u ON c.id_usuario = u.id_usuario
            WHERE c.estado_registro = 'ACTIVO'";
    
    $params = [];
    
    if ($fechaInicio) {
        $sql .= " AND DATE(c.fecha_compra) >= :fecha_inicio";
        $params[':fecha_inicio'] = $fechaInicio;
    }
    
    if ($fechaFin) {
        $sql .= " AND DATE(c.fecha_compra) <= :fecha_fin";
        $params[':fecha_fin'] = $fechaFin;
    }
    
    if ($idProveedor) {
        $sql .= " AND c.id_proveedor = :id_proveedor";
        $params[':id_proveedor'] = $idProveedor;
    }
    
    $sql .= " ORDER BY c.fecha_compra DESC LIMIT 100";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $compras_datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al listar compras: " . $e->getMessage());
    $compras_datos = [];
}

// NO usar echo, print, jsonResponse()
