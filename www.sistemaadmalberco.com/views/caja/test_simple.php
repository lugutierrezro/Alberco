<?php
require_once __DIR__ . '/../../services/database/config.php';

echo "<h2>Test Simple de Caja</h2>";

try {
    // Test 1: Obtener caja abierta
    $sql = "SELECT a.*, 
                   CONCAT(COALESCE(e.nombres, u.username), ' ', COALESCE(e.apellidos, '')) as nombre_usuario 
            FROM tb_arqueo_caja a
            INNER JOIN tb_usuarios u ON a.id_usuario_apertura = u.id_usuario
            LEFT JOIN tb_empleados e ON u.id_empleado = e.id_empleado
            WHERE a.estado = 'abierto'
            AND a.estado_registro = 'ACTIVO'
            ORDER BY a.id_arqueo DESC 
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $caja_actual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($caja_actual) {
        echo "<p style='color: green;'>✅ CAJA ABIERTA ENCONTRADA:</p>";
        echo "<ul>";
        echo "<li>ID: {$caja_actual['id_arqueo']}</li>";
        echo "<li>Fecha: {$caja_actual['fecha_arqueo']}</li>";
        echo "<li>Estado: {$caja_actual['estado']}</li>";
        echo "<li>Saldo Inicial: S/ {$caja_actual['saldo_inicial']}</li>";
        echo "<li>Usuario: {$caja_actual['nombre_usuario']}</li>";
        echo "</ul>";
        
        // Ahora vamos a simular lo que hace index.php
        echo "<h3>¿Cómo lo evaluaría index.php?</h3>";
        echo "<code>isset(\$caja_actual): " . (isset($caja_actual) ? "true" : "false") . "</code><br>";
        echo "<code>\$caja_actual (bool): " . ($caja_actual ? "true" : "false") . "</code><br>";
        echo "<code>isset(\$caja_actual) && \$caja_actual: " . ((isset($caja_actual) && $caja_actual) ? "✅ MOSTRARÍA CAJA ABIERTA" : "❌ MOSTRARÍA NO HAY CAJA") . "</code>";
        
    } else {
        echo "<p style='color: red;'>❌ NO SE ENCONTRÓ CAJA ABIERTA</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}
?>
