<?php
// Obtener Detalle de Compra (sin JSON - preparar datos)

try {
    $compraId = (int)($_GET['id'] ?? 0);
    
    if ($compraId <= 0) {
        $_SESSION['error'] = 'ID de compra inválido';
        header('Location: ' . URL_BASE . '/compras');
        exit;
    }
    
    $sql = "SELECT c.*, 
                   p.nombre as producto_nombre,
                   p.codigo as producto_codigo,
                   p.unidad_medida,
                   prov.nombre_proveedor,
                   prov.empresa as proveedor_empresa,
                   prov.ruc as proveedor_ruc,
                   prov.telefono as proveedor_telefono,
                   u.username as usuario_registro
            FROM tb_compras c
            INNER JOIN tb_almacen p ON c.id_producto = p.id_producto
            INNER JOIN tb_proveedores prov ON c.id_proveedor = prov.id_proveedor
            INNER JOIN tb_usuarios u ON c.id_usuario = u.id_usuario
            WHERE c.id_compra = ? AND c.estado_registro = 'ACTIVO'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$compraId]);
    $compra_dato = $stmt->fetch();
    
    if (!$compra_dato) {
        $_SESSION['error'] = 'Compra no encontrada';
        header('Location: ' . URL_BASE . '/compras');
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Error al obtener compra: " . $e->getMessage());
    $_SESSION['error'] = 'Error al obtener detalle';
    header('Location: ' . URL_BASE . '/compras');
    exit;
}

// NO usar echo, print, jsonResponse()
