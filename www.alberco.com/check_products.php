<?php
require 'c:/xampp/htdocs/www.sistemaadmalberco.com/app/config.php';

echo "=== PRODUCTOS EN BASE DE DATOS ===\n\n";

// Count products in Promociones
$stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_almacen WHERE id_categoria = 6");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total products in Promociones category: " . $row['total'] . "\n\n";

// Show sample products
echo "Sample products from database:\n";
$stmt2 = $pdo->query("SELECT id_producto, nombre, id_categoria, disponible_venta, stock FROM tb_almacen LIMIT 10");
while($p = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo "  ID: {$p['id_producto']}, Name: {$p['nombre']}, Category: {$p['id_categoria']}, Disponible: {$p['disponible_venta']}, Stock: {$p['stock']}\n";
}

echo "\n=== END ===\n";
?>
