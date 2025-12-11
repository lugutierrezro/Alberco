<?php
// Obtener Detalle de Cliente

try {
    $clienteId = (int)($_GET['id'] ?? 0);
    
    if ($clienteId <= 0) {
        $_SESSION['error'] = 'ID de cliente inválido';
        header('Location: ' . URL_BASE . '/views/clientes/');
        exit;
    }
    
    // Obtener datos del cliente
    $sql = "SELECT * FROM tb_clientes WHERE id_cliente = :id_cliente AND estado_registro = 'ACTIVO'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_cliente' => $clienteId]);
    $cliente_dato = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente_dato) {
        $_SESSION['error'] = 'Cliente no encontrado';
        header('Location: ' . URL_BASE . '/views/clientes/');
        exit;
    }
    
    // Historial de pedidos CON JOIN A TB_ESTADOS
    $pedidosSql = "SELECT 
                        p.id_pedido,
                        p.numero_comanda,
                        p.nro_pedido,
                        p.tipo_pedido,
                        p.fecha_pedido,
                        p.subtotal,
                        p.costo_delivery,
                        p.descuento,
                        p.total,
                        p.observaciones,
                        p.direccion_entrega,
                        p.id_estado,
                        e.nombre_estado,
                        e.color AS color_estado,
                        e.icono AS icono_estado
                   FROM tb_pedidos p
                   INNER JOIN tb_estados e ON p.id_estado = e.id_estado
                   WHERE p.id_cliente = :id_cliente 
                   AND p.estado_registro = 'ACTIVO'
                   ORDER BY p.fecha_pedido DESC
                   LIMIT 20";
    
    $pedidosStmt = $pdo->prepare($pedidosSql);
    $pedidosStmt->execute([':id_cliente' => $clienteId]);
    $historial_pedidos = $pedidosStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Estadísticas del cliente
    $statsSql = "SELECT 
                    COUNT(*) AS total_pedidos,
                    COALESCE(SUM(total), 0) AS total_gastado,
                    COALESCE(AVG(total), 0) AS ticket_promedio,
                    MAX(fecha_pedido) AS ultimo_pedido
                 FROM tb_pedidos
                 WHERE id_cliente = :id_cliente 
                 AND estado_registro = 'ACTIVO'";
    
    $statsStmt = $pdo->prepare($statsSql);
    $statsStmt->execute([':id_cliente' => $clienteId]);
    $estadisticas_cliente = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al obtener cliente: " . $e->getMessage());
    $_SESSION['error'] = 'Error al obtener detalle del cliente';
    header('Location: ' . URL_BASE . '/views/clientes/');
    exit;
}
