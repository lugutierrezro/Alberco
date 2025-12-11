<?php
echo "<h2>Test de Include de resumen.php</h2>";

// Simular el mismo orden de includes que index.php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
// No incluir parte1 porque tiene HTML

echo "<p>Incluyendo resumen.php...</p>";
include ('../../controllers/caja/resumen.php');

echo "<hr>";
echo "<h3>Resultado:</h3>";

if (isset($caja_actual) && $caja_actual) {
    echo "<p style='color: green;'>✅ \$caja_actual EXISTE y tiene datos</p>";
    echo "<pre>";
    print_r($caja_actual);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ \$caja_actual NO existe o está vacío</p>";
    echo "<p>isset(\$caja_actual): " . (isset($caja_actual) ? "true" : "false") . "</p>";
    if (isset($caja_actual)) {
        echo "<p>\$caja_actual value: ";
        var_dump($caja_actual);
        echo "</p>";
    }
}

echo "<h3>Otras variables:</h3>";
echo "<p>isset(\$resumen_caja): " . (isset($resumen_caja) ? "true" : "false") . "</p>";
echo "<p>isset(\$ventas_dia): " . (isset($ventas_dia) ? "true" : "false") . "</p>";
echo "<p>isset(\$movimientos_recientes): " . (isset($movimientos_recientes) ? "true" : "false") . "</p>";
?>
