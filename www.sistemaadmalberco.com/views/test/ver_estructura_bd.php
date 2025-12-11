<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/../../services/database/config.php');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Estructura BD</title>
<style>
body { font-family: Arial; padding: 20px; background: #f5f5f5; }
.section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; }
h2 { color: #FF6B35; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
th { background: #FF6B35; color: white; }
pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; }
</style>
</head><body>";

try {
    $pdo = getDB();
    
    // ============================================
    // 1. ESTRUCTURA tb_ventas
    // ============================================
    echo "<div class='section'><h2>1. Estructura de tb_ventas</h2>";
    $stmt = $pdo->query("DESCRIBE tb_ventas");
    $columnas_ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columnas_ventas as $col) {
        echo "<tr>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table></div>";
    
    // ============================================
    // 2. BUSCAR TABLA DE DETALLES
    // ============================================
    echo "<div class='section'><h2>2. Tablas relacionadas con Ventas/Detalles</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE '%detalle%' OR SHOW TABLES LIKE '%venta%'");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tablas)) {
        // Buscar todas las tablas
        $stmt = $pdo->query("SHOW TABLES");
        $todas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p><strong>Tablas que contienen 'venta' o 'detalle':</strong></p><ul>";
        foreach ($todas as $tabla) {
            if (stripos($tabla, 'venta') !== false || stripos($tabla, 'detalle') !== false) {
                echo "<li>$tabla</li>";
            }
        }
        echo "</ul>";
        
        echo "<p><strong>TODAS las tablas disponibles:</strong></p><ul>";
        foreach ($todas as $tabla) {
            echo "<li>$tabla</li>";
        }
        echo "</ul>";
    } else {
        echo "<ul>";
        foreach ($tablas as $tabla) {
            echo "<li><strong>$tabla</strong></li>";
        }
        echo "</ul>";
    }
    echo "</div>";
    
    // ============================================
    // 3. ESTRUCTURA tb_pedidos (alternativa)
    // ============================================
    echo "<div class='section'><h2>3. Estructura de tb_pedidos</h2>";
    $stmt = $pdo->query("DESCRIBE tb_pedidos");
    $columnas_pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table><tr><th>Campo</th><th>Tipo</th></tr>";
    foreach ($columnas_pedidos as $col) {
        echo "<tr><td><strong>" . $col['Field'] . "</strong></td><td>" . $col['Type'] . "</td></tr>";
    }
    echo "</table></div>";
    
    // ============================================
    // 4. SAMPLE DATA tb_ventas
    // ============================================
    echo "<div class='section'><h2>4. Datos de Ejemplo - tb_ventas</h2>";
    $stmt = $pdo->query("SELECT * FROM tb_ventas LIMIT 2");
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($samples)) {
        echo "<pre>" . print_r($samples, true) . "</pre>";
    } else {
        echo "<p>No hay datos en tb_ventas</p>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section'><p style='color: red;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p></div>";
}

echo "</body></html>";
?>
