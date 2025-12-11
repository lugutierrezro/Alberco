<?php
/**
 * DEBUG - Ventas con Métodos de Pago
 * Verifica que se estén mostrando correctamente los métodos de pago
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../../services/database/config.php');
include('../../models/venta.php');

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Debug - Métodos de Pago en Ventas</title>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #fff; }
    .panel { background: #2d2d2d; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .ok { color: #4CAF50; }
    .error { color: #f44336; }
    h2 { color: #03a9f4; }
    pre { background: #000; padding: 10px; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 8px; text-align: left; border: 1px solid #555; }
    th { background: #333; }
</style>";
echo "</head><body>";

echo "<h1>🔍 Debug - Métodos de Pago en Ventas</h1>";

// ========================================
// 1. VERIFICAR TABLA tb_metodos_pago
// ========================================
echo "<div class='panel'>";
echo "<h2>1. Métodos de Pago Disponibles</h2>";

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $stmt = $pdo->query("SELECT * FROM tb_metodos_pago WHERE estado_registro = 'ACTIVO'");
    $metodos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='ok'>✓ " . count($metodos) . " métodos de pago encontrados</div>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Requiere Ref</th></tr>";
    foreach ($metodos as $metodo) {
        echo "<tr>";
        echo "<td>{$metodo['id_metodo']}</td>";
        echo "<td>{$metodo['nombre_metodo']}</td>";
        echo "<td>" . ($metodo['requiere_referencia'] ? 'Sí' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: " . $e->getMessage() . "</div>";
}
echo "</div>";

// ========================================
// 2.  VERIFICAR ÚLTIMAS 5 VENTAS
// ========================================
echo "<div class='panel'>";
echo "<h2>2. Últimas 5 Ventas (Datos Crudos)</h2>";

try {
    $sql = "SELECT 
        v.id_venta,
        v.nro_venta,
        v.id_metodo_pago,
        mp.id_metodo,
        mp.nombre_metodo,
        v.estado_venta,
        v.total
    FROM tb_ventas v
    LEFT JOIN tb_metodos_pago mp ON v.id_metodo_pago = mp.id_metodo
    ORDER BY v.id_venta DESC
    LIMIT 5";
    
    $stmt = $pdo->query($sql);
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='ok'>✓ " . count($ventas) . " ventas encontradas</div>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Nro</th><th>id_metodo_pago (FK)</th><th>id_metodo (PK)</th><th>Nombre Método</th><th>Estado</th><th>Total</th></tr>";
    foreach ($ventas as $venta) {
        $matched = ($venta['id_metodo_pago'] == $venta['id_metodo']) ? '✓' : '✗';
        $color = ($venta['id_metodo_pago'] == $venta['id_metodo']) ? 'ok' : 'error';
        
        echo "<tr>";
        echo "<td>{$venta['id_venta']}</td>";
        echo "<td>{$venta['nro_venta']}</td>";
        echo "<td class='$color'>{$venta['id_metodo_pago']}</td>";
        echo "<td class='$color'>{$venta['id_metodo']}</td>";
        echo "<td><strong>" . ($venta['nombre_metodo'] ?? 'NULL') . "</strong></td>";
        echo "<td>{$venta['estado_venta']}</td>";
        echo "<td>S/ " . number_format($venta['total'], 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: " . $e->getMessage() . "</div>";
}
echo "</div>";

// ========================================
// 3. PROBAR getVentasWithDetails()
// ========================================
echo "<div class='panel'>";
echo "<h2>3. Prueba del Modelo Venta (getVentasWithDetails)</h2>";

try {
    $ventaModel = new Venta();
    $ventas = $ventaModel->getVentasWithDetails([
        'limit' => 5
    ]);
    
    echo "<div class='ok'>✓ Modelo retorna " . count($ventas) . " ventas</div>";
    
    if (!empty($ventas)) {
        echo "<table>";
        echo "<tr><th>Nro Venta</th><th>Cliente</th><th>metodo_pago (resultado)</th><th>Total</th></tr>";
        foreach ($ventas as $venta) {
            echo "<tr>";
            echo "<td>{$venta['nro_venta']}</td>";
            echo "<td>{$venta['cliente_nombre']}</td>";
            echo "<td><strong>" . ($venta['metodo_pago'] ?? 'NULL') . "</strong></td>";
            echo "<td>S/ " . number_format($venta['total'], 2) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>Estructura completa de primera venta:</h3>";
        echo "<pre>" . print_r($ventas[0], true) . "</pre>";
    } else {
        echo "<div class='error'>✗ No hay ventas</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: " . $e->getMessage() . "</div>";
}
echo "</div>";

// ========================================
// 4. VERIFICAR SI HAY VENTAS HUÉRFANAS
// ========================================
echo "<div class='panel'>";
echo "<h2>4. Ventas Sin Método de Pago Valid (Huérfanas)</h2>";

try {
    $sql = "SELECT v.id_venta, v.nro_venta, v.id_metodo_pago
    FROM tb_ventas v
    LEFT JOIN tb_metodos_pago mp ON v.id_metodo_pago = mp.id_metodo
    WHERE mp.id_metodo IS NULL
    AND v.estado_registro = 'ACTIVO'
    LIMIT 10";
    
    $stmt = $pdo->query($sql);
    $huerfanas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($huerfanas) > 0) {
        echo "<div class='error'>✗ " . count($huerfanas) . " ventas sin método de pago válido</div>";
        echo "<table>";
        echo "<tr><th>ID Venta</th><th>Nro Venta</th><th>id_metodo_pago (inválido)</th></tr>";
        foreach ($huerfanas as $venta) {
            echo "<tr>";
            echo "<td>{$venta['id_venta']}</td>";
            echo "<td>{$venta['nro_venta']}</td>";
            echo "<td class='error'>{$venta['id_metodo_pago']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><strong>Solución:</strong> Actualizar estas ventas con un id_metodo_pago válido (1-6)</p>";
    } else {
        echo "<div class='ok'>✓ Todas las ventas tienen métodos de pago válidos</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: " . $e->getMessage() . "</div>";
}
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #888;'>Debug generado: " . date('Y-m-d H:i:s') . "</p>";
echo "</body></html>";
?>
