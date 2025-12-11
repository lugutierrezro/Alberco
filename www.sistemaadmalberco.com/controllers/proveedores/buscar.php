<?php
// Buscar Proveedores (sin JSON - preparar datos)

try {
    $search = sanitize($_GET['q'] ?? '');
    
    if (empty($search)) {
        $proveedores_busqueda = [];
    } else {
        $sql = "SELECT * FROM tb_proveedores 
                WHERE (nombre_proveedor LIKE :search 
                   OR empresa LIKE :search 
                   OR ruc LIKE :search 
                   OR codigo_proveedor LIKE :search)
                AND estado_registro = 'ACTIVO'
                ORDER BY nombre_proveedor ASC
                LIMIT 50";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':search' => '%' . $search . '%']);
        $proveedores_busqueda = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    error_log("Error al buscar proveedores: " . $e->getMessage());
    $proveedores_busqueda = [];
}

// NO usar echo, print, jsonResponse()
