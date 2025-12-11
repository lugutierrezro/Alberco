<?php
// Listar Productos (sin JSON - preparar datos)

try {
    // Sanitizar parámetros GET
    $categoria   = isset($_GET['categoria']) && is_numeric($_GET['categoria']) ? (int)$_GET['categoria'] : null;
    $disponibles = isset($_GET['disponibles']) && $_GET['disponibles'] === '1';
    $stockBajo   = isset($_GET['stock_bajo']) && $_GET['stock_bajo'] === '1';

    $sql = "SELECT 
            p.*, 
            c.nombre_categoria
        FROM tb_almacen p
        INNER JOIN tb_categorias c ON p.id_categoria = c.id_categoria
        WHERE p.estado_registro = 'ACTIVO'";

    $params = [];

    // Filtro por categoría
    if (!is_null($categoria)) {
        $sql .= " AND p.id_categoria = :categoria";
        $params['categoria'] = $categoria;
    }

    // Filtro solo disponibles
    if ($disponibles) {
        $sql .= " AND p.disponible_venta = 1 AND p.stock > 0";
    }

    // Filtro stock bajo
    if ($stockBajo) {
        $sql .= " AND p.stock <= p.stock_minimo";
    }

    $sql .= " ORDER BY p.nombre ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $productos_datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al listar productos: " . $e->getMessage());
    $productos_datos = [];
}
