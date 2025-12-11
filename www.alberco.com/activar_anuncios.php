<?php
/**
 * Activar todos los anuncios INACTIVOS
 */

require_once __DIR__ . '/../www.sistemaadmalberco.com/services/database/config.php';

try {
    // Activar todos los anuncios
    $sql = "UPDATE tb_anuncios_sitio 
            SET estado_registro = 'ACTIVO' 
            WHERE estado_registro = 'INACTIVO'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $affected = $stmt->rowCount();
    
    echo "<h1>✅ Anuncios Activados</h1>";
    echo "<p>Se activaron <strong>$affected</strong> anuncios.</p>";
    echo "<hr>";
    
    // Mostrar anuncios activos ahora
    $sql2 = "SELECT id_anuncio, titulo, posicion, activo, estado_registro, fecha_inicio, fecha_fin 
             FROM tb_anuncios_sitio 
             WHERE estado_registro = 'ACTIVO'
             ORDER BY posicion, prioridad DESC";
    
    $stmt2 = $pdo->query($sql2);
    $anuncios = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Anuncios Activos Ahora:</h2>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #4CAF50; color: white;'>
            <th>ID</th>
            <th>Título</th>
            <th>Posición</th>
            <th>Activo</th>
            <th>Estado</th>
            <th>Fecha Inicio</th>
            <th>Fecha Fin</th>
          </tr>";
    
    foreach ($anuncios as $anuncio) {
        $bgColor = $anuncio['activo'] == 1 ? '#e8f5e9' : '#ffebee';
        echo "<tr style='background: $bgColor;'>";
        echo "<td>{$anuncio['id_anuncio']}</td>";
        echo "<td>{$anuncio['titulo']}</td>";
        echo "<td><strong>{$anuncio['posicion']}</strong></td>";
        echo "<td>" . ($anuncio['activo'] ? '✅ SÍ' : '❌ NO') . "</td>";
        echo "<td><strong>{$anuncio['estado_registro']}</strong></td>";
        echo "<td>{$anuncio['fecha_inicio']}</td>";
        echo "<td>{$anuncio['fecha_fin']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<p><a href='test_anuncios.php' style='padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px;'>🔍 Ver Test Completo</a></p>";
    echo "<p><a href='index.php' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>🏠 Ir al Inicio</a></p>";
    
} catch (PDOException $e) {
    echo "<h1 style='color: red;'>❌ Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        background: #f5f5f5;
    }
    table {
        background: white;
        width: 100%;
        margin: 20px 0;
    }
    th, td {
        text-align: left;
        padding: 12px;
    }
</style>
