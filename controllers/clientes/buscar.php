<?php
// Buscar Clientes (sin JSON - preparar datos)

try {
    $search = sanitize($_GET['q'] ?? '');
    
    if (empty($search)) {
        $clientes_busqueda = [];
    } else {
        $sql = "SELECT * FROM tb_clientes 
                WHERE (nombres LIKE :search 
                   OR apellidos LIKE :search 
                   OR telefono LIKE :search 
                   OR codigo_cliente LIKE :search 
                   OR numero_documento LIKE :search)
                AND estado_registro = 'ACTIVO'
                ORDER BY nombres ASC
                LIMIT 50";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':search' => '%' . $search . '%']);
        $clientes_busqueda = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    error_log("Error al buscar clientes: " . $e->getMessage());
    $clientes_busqueda = [];
}

// NO usar echo, print, jsonResponse()
