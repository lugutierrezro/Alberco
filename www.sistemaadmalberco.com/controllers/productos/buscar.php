<?php
// Buscar Productos (sin JSON - preparar datos)

try {
    $search = sanitize($_GET['q'] ?? '');
    
    if (empty($search)) {
        $productos_busqueda = [];
    } else {
        $sql = "SELECT p.*, c.nombre_categoria, c.color as categoria_color
                FROM tb_almacen p
                INNER JOIN tb_categorias c ON p.id_categoria = c.id_categoria
                WHERE (p.nombre LIKE :search 
                   OR p.codigo LIKE :search 
                   OR p.descripcion LIKE :search)
                AND p.estado_registro = 'ACTIVO'
                ORDER BY p.nombre ASC
                LIMIT 50";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':search' => '%' . $search . '%']);
        $productos_busqueda = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    error_log("Error al buscar productos: " . $e->getMessage());
    $productos_busqueda = [];
}

// NO usar echo, print, jsonResponse()
