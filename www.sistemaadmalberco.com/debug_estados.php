<?php
/**
 * DEBUG - Cambio de Estado de Pedidos
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../../services/database/config.php');

echo "<!DOCTYPE html>";
echo "<html><head><title>Debug - Estados</title>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #fff; }
    .panel { background: #2d2d2d; padding: 15px; margin: 10px 0; border-radius: 5px; }
    h2 { color: #03a9f4; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 8px; border: 1px solid #555; }
    th { background: #333; }
    .ok { color: #4CAF50; }
    .error { color: #f44336; }
</style></head><body>";

echo "<h1>🔍 Debug - Estados de Pedidos</h1>";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sistema_gestion_alberco_v3;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Ver estados disponibles
    echo "<div class='panel'>";
    echo "<h2>Estados Disponibles</h2>";
    $stmt = $pdo->query("SELECT * FROM tb_estados WHERE estado_registro = 'ACTIVO' ORDER BY id_estado");
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table><tr><th>ID</th><th>Nombre</th><th>Descripción</th></tr>";
    foreach ($estados as $estado) {
        $highlight = (strtolower($estado['nombre_estado']) === 'entregado') ? "style='background: #4CAF50;'" : "";
        echo "<tr $highlight>";
        echo "<td><strong>{$estado['id_estado']}</strong></td>";
        echo "<td>{$estado['nombre_estado']}</td>";
        echo "<td>{$estado['descripcion_estado']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Ver pedidos con sus ventas
    echo "<div class='panel'>";
    echo "<h2>Pedidos con Ventas Vinculadas</h2>";
    $stmt = $pdo->query("
        SELECT 
            p.id_pedido,
            p.nro_pedido,
            p.id_estado,
            e.nombre_estado as estado_pedido,
            p.id_venta,
            v.estado_venta as estado_venta,
            p.total
        FROM tb_pedidos p
        LEFT JOIN tb_estados e ON p.id_estado = e.id_estado
        LEFT JOIN tb_ventas v ON p.id_venta = v.id_venta
        ORDER BY p.id_pedido DESC
        LIMIT 10
    ");
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>ID Pedido</th><th>Nro</th><th>Estado Pedido</th><th>ID Venta</th><th>Estado Venta</th><th>Total</th></tr>";
    foreach ($pedidos as $pedido) {
        $mismatch = ($pedido['estado_pedido'] === 'Entregado' && $pedido['estado_venta'] !== 'completada');
        $rowClass = $mismatch ? "style='background: #f44336;'" : "";
        
        echo "<tr $rowClass>";
        echo "<td>{$pedido['id_pedido']}</td>";
        echo "<td>{$pedido['nro_pedido']}</td>";
        echo "<td><strong>{$pedido['estado_pedido']}</strong> (ID: {$pedido['id_estado']})</td>";
        echo "<td>" . ($pedido['id_venta'] ?? 'NULL') . "</td>";
        echo "<td>" . ($pedido['estado_venta'] ?? 'NULL') . "</td>";
        echo "<td>S/ " . number_format($pedido['total'], 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($mismatch) {
        echo "<p class='error'>⚠️ HAY PEDIDOS ENTREGADOS CON VENTAS PENDIENTES</p>";
    }
    echo "</div>";
    
    // Script de corrección
    echo "<div class='panel'>";
    echo "<h2>🔧 Script de Corrección</h2>";
    echo "<p>Para arreglar pedidos entregados con ventas pendientes:</p>";
    echo "<pre>";
    echo "UPDATE tb_ventas v\n";
    echo "INNER JOIN tb_pedidos p ON v.id_pedido = p.id_pedido\n";
    echo "INNER JOIN tb_estados e ON p.id_estado = e.id_estado\n";
    echo "SET v.estado_venta = 'completada'\n";
    echo "WHERE e.nombre_estado = 'Entregado'\n";
    echo "AND v.estado_venta = 'pendiente';";
    echo "</pre>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
}

echo "</body></html>";
?>
