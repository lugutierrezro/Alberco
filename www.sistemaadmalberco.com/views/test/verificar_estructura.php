<?php
/**
 * Verificar estructura de tb_clientes
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../services/database/config.php';
$pdo = getDB();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificar Estructura</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1>Verificar Estructura de Tablas</h1>
    
    <?php
    // Verificar tb_clientes
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-info text-white'><h4>Estructura de tb_clientes</h4></div>";
    echo "<div class='card-body'>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM tb_clientes");
    $columns = $stmt->fetchAll();
    
    echo "<table class='table table-sm'>";
    echo "<tr><th>Campo</th><th>Tipo</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td><strong>{$col['Field']}</strong></td><td>{$col['Type']}</td></tr>";
    }
    echo "</table>";
    
    echo "</div></div>";
    
    // Verificar tb_usuarios
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-info text-white'><h4>Estructura de tb_usuarios</h4></div>";
    echo "<div class='card-body'>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM tb_usuarios");
    $columns = $stmt->fetchAll();
    
    echo "<table class='table table-sm'>";
    echo "<tr><th>Campo</th><th>Tipo</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td><strong>{$col['Field']}</strong></td><td>{$col['Type']}</td></tr>";
    }
    echo "</table>";
    
    echo "</div></div>";
    
    // Verificar tb_almacen
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-info text-white'><h4>Estructura de tb_almacen</h4></div>";
    echo "<div class='card-body'>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM tb_almacen");
    $columns = $stmt->fetchAll();
    
    echo "<table class='table table-sm'>";
    echo "<tr><th>Campo</th><th>Tipo</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td><strong>{$col['Field']}</strong></td><td>{$col['Type']}</td></tr>";
    }
    echo "</table>";
    
    echo "</div></div>";
    ?>
    
    <a href="debug_reportes.php" class="btn btn-primary">Volver a Debug Reportes</a>
</div>
</body>
</html>
