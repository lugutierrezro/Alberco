<?php
require_once __DIR__ . '/../../services/database/config.php';
session_start();

echo "<h1>Verificar Estructura de tb_categorias</h1>";

try {
    $pdo = getDB();
    
    // Obtener estructura
    $stmt = $pdo->query("DESCRIBE tb_categorias");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Columnas actuales:</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    $campos_existentes = [];
    foreach ($columnas as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
        $campos_existentes[] = $col['Field'];
    }
    echo "</table>";
    
    // Verificar campos necesarios
    echo "<h2>Campos necesarios:</h2>";
    $campos_necesarios = ['color', 'icono'];
    
    foreach ($campos_necesarios as $campo) {
        if (in_array($campo, $campos_existentes)) {
            echo "<p style='color:green'>✓ $campo existe</p>";
        } else {
            echo "<p style='color:red'>✗ $campo NO existe - Necesita ser agregado</p>";
            echo "<p>SQL para agregar: <code>ALTER TABLE tb_categorias ADD COLUMN $campo VARCHAR(100) NULL;</code></p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>

<hr>
<a href="../../views/categorias/" style="display:inline-block; padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;">Volver a Categorías</a>
