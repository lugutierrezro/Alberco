<?php
// Listar Clientes (sin JSON - preparar datos)

try {
    $tipoCliente = $_GET['tipo_cliente'] ?? null;

    $sql = "SELECT *
            FROM (
                SELECT 
                    c.*,
                    (SELECT COUNT(*) 
                     FROM tb_pedidos p 
                     WHERE p.id_cliente = c.id_cliente 
                     AND p.estado_registro = 'ACTIVO') AS total_pedidos,
                     
                    (SELECT COALESCE(SUM(total), 0) 
                     FROM tb_pedidos p 
                     WHERE p.id_cliente = c.id_cliente 
                     AND p.estado_registro = 'ACTIVO') AS total_gastado,

                    (SELECT MAX(fecha_pedido) 
                     FROM tb_pedidos p 
                     WHERE p.id_cliente = c.id_cliente 
                     AND p.estado_registro = 'ACTIVO') AS ultimo_pedido

                FROM tb_clientes c
                WHERE c.estado_registro = 'ACTIVO'
            ) AS clientes";

    // Filtro tipo cliente
    if ($tipoCliente === 'FRECUENTE') {
        $sql .= " WHERE total_pedidos >= 5";
    }

    $sql .= " ORDER BY nombre ASC LIMIT 500";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $clientes_datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error al listar clientes: " . $e->getMessage());
    $clientes_datos = [];
}

// NO usar echo, print, jsonResponse()
