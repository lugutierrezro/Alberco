<?php
header('Content-Type: application/json');

try {
    $isLocal = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1');
    
    if ($isLocal) {
        $dbHost = 'localhost';
        $dbUser = 'root';
        $dbPass = '';
        $dbName = 'sistema_gestion_alberco_v3';
    }
    
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Obtener columnas de tb_pedidos
    $stmt = $pdo->query("SHOW COLUMNS FROM tb_pedidos");
    $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Obtener último pedido
    $stmt = $pdo->query("SELECT * FROM tb_pedidos ORDER BY id_pedido DESC LIMIT 1");
    $ultimoPedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'conexion' => true,
        'columnas' => $columnas,
        'ultimo_pedido' => $ultimoPedido
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'conexion' => false,
        'error' => $e->getMessage()
    ]);
}
?>
