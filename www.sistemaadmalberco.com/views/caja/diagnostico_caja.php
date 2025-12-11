<?php
/**
 * Diagnóstico de caja
 */

require_once __DIR__ . '/../../services/database/config.php';

echo "<h2>Diagnóstico del Sistema de Caja</h2>";
echo "<hr>";

try {
    $fecha = date('Y-m-d');
    
    echo "<h3>1. Verificar caja abierta hoy</h3>";
    $sql = "SELECT * FROM tb_arqueo_caja WHERE fecha_arqueo = ? AND estado_registro = 'ACTIVO'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fecha]);
    $caja = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($caja) {
        echo "<p style='color: green;'>✅ Se encontró caja para hoy:</p>";
        echo "<pre>";
        print_r($caja);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>❌ NO se encontró caja para hoy</p>";
    }
    
    echo "<h3>2. Todas las cajas en la base de datos</h3>";
    $sqlAll = "SELECT * FROM tb_arqueo_caja ORDER BY fecha_arqueo DESC LIMIT 5";
    $stmtAll = $pdo->query($sqlAll);
    $todasCajas = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Fecha</th><th>Estado</th><th>Saldo Inicial</th><th>Usuario ID</th><th>Estado Registro</th></tr>";
    foreach ($todasCajas as $c) {
        echo "<tr>";
        echo "<td>{$c['id_arqueo']}</td>";
        echo "<td>{$c['fecha_arqueo']}</td>";
        echo "<td>{$c['estado']}</td>";
        echo "<td>S/ {$c['saldo_inicial']}</td>";
        echo "<td>{$c['id_usuario_apertura']}</td>";
        echo "<td>{$c['estado_registro']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>3. Probar consulta del resumen.php</h3>";
    $arqueoSql = "SELECT a.*, 
                         CONCAT(COALESCE(e.nombres, u.username), ' ', COALESCE(e.apellidos, '')) as nombre_usuario 
                  FROM tb_arqueo_caja a
                  INNER JOIN tb_usuarios u ON a.id_usuario_apertura = u.id_usuario
                  LEFT JOIN tb_empleados e ON u.id_empleado = e.id_empleado
                  WHERE a.fecha_arqueo = ? 
                  AND a.estado_registro = 'ACTIVO'
                  ORDER BY a.id_arqueo DESC 
                  LIMIT 1";
    
    $arqueoStmt = $pdo->prepare($arqueoSql);
    $arqueoStmt->execute([$fecha]);
    $caja_actual = $arqueoStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($caja_actual) {
        echo "<p style='color: green;'>✅ Consulta del resumen funciona:</p>";
        echo "<pre>";
        print_r($caja_actual);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>❌ La consulta del resumen NO devuelve datos</p>";
        echo "<p>Posibles causas:</p>";
        echo "<ul>";
        echo "<li>El usuario con id_usuario_apertura no existe en tb_usuarios</li>";
        echo "<li>El JOIN está fallando</li>";
        echo "</ul>";
    }
    
    echo "<h3>4. Verificar si hay errores en resumen.php</h3>";
    ob_start();
    include(__DIR__ . '/../../controllers/caja/resumen.php');
    $output = ob_get_clean();
    
    if (isset($caja_actual)) {
        echo "<p style='color: green;'>✅ resumen.php se ejecutó sin errores</p>";
        echo "<p>\$caja_actual existe: " . ($caja_actual ? "Sí" : "No") . "</p>";
    } else {
        echo "<p style='color: red;'>❌ \$caja_actual no está definido después de ejecutar resumen.php</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
