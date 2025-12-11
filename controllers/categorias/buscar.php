<?php
// Buscar Categorías (sin JSON - preparar datos)

try {
    $search = sanitize($_GET['q'] ?? '');
    
    if (empty($search)) {
        $categorias_busqueda = [];
    } else {
        $sql = "SELECT c.*, 
                       COUNT(p.id_producto) as total_productos
                FROM tb_categorias c
                LEFT JOIN tb_almacen p ON c.id_categoria = p.id_categoria 
                    AND p.estado_registro = 'ACTIVO'
                WHERE (c.nombre_categoria LIKE :search 
                    OR c.descripcion LIKE :search)
                AND c.estado_registro = 'ACTIVO'
                GROUP BY c.id_categoria
                ORDER BY c.nombre_categoria
                LIMIT 20";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':search' => '%' . $search . '%']);
        $categorias_busqueda = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    error_log("Error al buscar categorías: " . $e->getMessage());
    $categorias_busqueda = [];
}

// NO usar echo, print, jsonResponse()
