<?php
// Listar Proveedores (sin JSON - solo preparar datos)

try {
    $sql = "SELECT p.*,
            (SELECT COUNT(*) FROM tb_compras c 
             WHERE c.id_proveedor = p.id_proveedor 
             AND c.estado_registro = 'ACTIVO') as total_compras,
            (SELECT COALESCE(SUM(total), 0) FROM tb_compras c 
             WHERE c.id_proveedor = p.id_proveedor 
             AND c.estado_registro = 'ACTIVO') as total_monto,
            (SELECT MAX(fecha_compra) FROM tb_compras c 
             WHERE c.id_proveedor = p.id_proveedor 
             AND c.estado_registro = 'ACTIVO') as ultima_compra
            FROM tb_proveedores p
            WHERE p.estado_registro = 'ACTIVO'
            ORDER BY p.nombre_proveedor ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $proveedores_datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al listar proveedores: " . $e->getMessage());
    $proveedores_datos = [];
}
