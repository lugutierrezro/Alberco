<?php
// Listar Categorías (sin JSON - preparar datos)

try {
    $conProductos = isset($_GET['con_productos']) && $_GET['con_productos'] === '1';
    $ordenadas = isset($_GET['ordenadas']) && $_GET['ordenadas'] === '1';
    
    if ($conProductos) {
        // Con conteo de productos
        $sql = "SELECT c.*, 
                       COUNT(p.id_producto) as total_productos
                FROM tb_categorias c
                LEFT JOIN tb_almacen p ON c.id_categoria = p.id_categoria 
                    AND p.estado_registro = 'ACTIVO'
                WHERE c.estado_registro = 'ACTIVO'
                GROUP BY c.id_categoria";
    } else {
        // Sin conteo
        $sql = "SELECT * FROM tb_categorias 
                WHERE estado_registro = 'ACTIVO'";
    }
    
    if ($ordenadas) {
        $sql .= " ORDER BY orden ASC, nombre_categoria ASC";
    } else {
        $sql .= " ORDER BY nombre_categoria ASC";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $categorias_datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al listar categorías: " . $e->getMessage());
    $categorias_datos = [];
}

// NO usar echo, print, jsonResponse()
