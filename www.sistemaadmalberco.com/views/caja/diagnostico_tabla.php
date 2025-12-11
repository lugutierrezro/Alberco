<?php
/**
 * Script de diagnóstico para la tabla tb_arqueo_caja
 */

require_once __DIR__ . '/../../services/database/config.php';

echo "<h2>Diagnóstico de tabla tb_arqueo_caja</h2>";

try {
    // 1. Verificar que la tabla existe
    $checkTable = $pdo->query("SHOW TABLES LIKE 'tb_arqueo_caja'");
    if ($checkTable->rowCount() == 0) {
        echo "<p style='color: red;'>❌ La tabla tb_arqueo_caja NO existe</p>";
        exit;
    }
    echo "<p style='color: green;'>✅ La tabla tb_arqueo_caja existe</p>";
    
    // 2. Mostrar estructura de la tabla
    echo "<h3>Estructura de la tabla:</h3>";
    $structure = $pdo->query("DESCRIBE tb_arqueo_caja");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Key</th><th>Default</th></tr>";
    while ($row = $structure->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 3. Verificar campos necesarios
    echo "<h3>Verificación de campos necesarios:</h3>";
    $camposNecesarios = [
        'id_arqueo',
        'fecha_arqueo',
        'hora_apertura', 
        'saldo_inicial',
        'saldo_esperado',
        'estado',
        'id_usuario_apertura',
        'observaciones',
        'fyh_creacion'
    ];
    
    $columns = $pdo->query("SHOW COLUMNS FROM tb_arqueo_caja")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($camposNecesarios as $campo) {
        if (in_array($campo, $columns)) {
            echo "<p style='color: green;'>✅ Campo '$campo' existe</p>";
        } else {
            echo "<p style='color: red;'>❌ Campo '$campo' NO existe</p>";
        }
    }
    
    // 4. Probar INSERT simulado
    echo "<h3>Prueba de INSERT:</h3>";
    echo "<pre>";
    $testSql = "INSERT INTO tb_arqueo_caja 
                (fecha_arqueo, hora_apertura, saldo_inicial, saldo_esperado, 
                 estado, id_usuario_apertura, observaciones, fyh_creacion)
                VALUES 
                (CURDATE(), CURTIME(), 100.00, 100.00, 
                 'abierto', 1, 'Prueba', NOW())";
    echo htmlspecialchars($testSql);
    echo "</pre>";
    
    echo "<p><strong>Nota:</strong> Esta es solo la consulta que se intentaría ejecutar. No se ejecutó realmente.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Código de error: " . $e->getCode() . "</p>";
}
?>
