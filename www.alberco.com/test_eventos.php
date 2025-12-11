<?php
/**
 * Test y Activación de Eventos
 */

require_once __DIR__ . '/../www.sistemaadmalberco.com/services/database/config.php';
require_once __DIR__ . '/../www.sistemaadmalberco.com/models/evento.php';

echo "<h1>Debug y Activación de Eventos</h1>";
echo "<hr>";

$eventoModel = new Evento();

// 1. Mostrar todos los eventos
echo "<h2>1. Todos los Eventos en BD</h2>";
$todosEventos = $eventoModel->getAll(true);
echo "<p>Total: " . count($todosEventos) . "</p>";

if (count($todosEventos) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #2196F3; color: white;'>
            <th>ID</th>
            <th>Nombre</th>
            <th>Fecha Evento</th>
            <th>Activo</th>
            <th>Mostrar Contador</th>
            <th>Estado Registro</th>
          </tr>";
    
    foreach ($todosEventos as $evento) {
        $bgColor = $evento['estado_registro'] == 'ACTIVO' ? '#e8f5e9' : '#ffebee';
        echo "<tr style='background: $bgColor;'>";
        echo "<td>{$evento['id_evento']}</td>";
        echo "<td><strong>{$evento['nombre_evento']}</strong></td>";
        echo "<td>{$evento['fecha_evento']}</td>";
        echo "<td>" . ($evento['activo'] ? '✅' : '❌') . "</td>";
        echo "<td>" . ($evento['mostrar_contador'] ? '✅' : '❌') . "</td>";
        echo "<td><strong>{$evento['estado_registro']}</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay eventos en la base de datos.</p>";
}

// 2. Activar eventos INACTIVOS
echo "<h2>2. Activar Eventos INACTIVOS</h2>";
try {
    $sql = "UPDATE tb_eventos_temporizador 
            SET estado_registro = 'ACTIVO' 
            WHERE estado_registro = 'INACTIVO'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $affected = $stmt->rowCount();
    
    if ($affected > 0) {
        echo "<p style='color: green;'>✅ Se activaron <strong>$affected</strong> eventos.</p>";
    } else {
        echo "<p>No había eventos INACTIVOS para activar.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// 3. Eventos activos ahora
echo "<h2>3. Eventos Activos</h2>";
$eventosActivos = $eventoModel->getActivos();
echo "<p>Total activos: " . count($eventosActivos) . "</p>";

if (count($eventosActivos) > 0) {
    echo "<pre>";
    print_r($eventosActivos);
    echo "</pre>";
} else {
    echo "<p>No hay eventos activos.</p>";
}

// 4. Próximo evento
echo "<h2>4. Próximo Evento</h2>";
$proximoEvento = $eventoModel->getProximo();

if ($proximoEvento) {
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 8px; border-left: 4px solid #2196F3;'>";
    echo "<h3>{$proximoEvento['nombre_evento']}</h3>";
    echo "<p><strong>Descripción:</strong> {$proximoEvento['descripcion']}</p>";
    echo "<p><strong>Fecha:</strong> {$proximoEvento['fecha_evento']}</p>";
    echo "<p><strong>Mensaje Antes:</strong> {$proximoEvento['mensaje_antes']}</p>";
    echo "<p><strong>Mostrar Contador:</strong> " . ($proximoEvento['mostrar_contador'] ? 'SÍ' : 'NO') . "</p>";
    echo "</div>";
    
    // Mostrar countdown de prueba
    echo "<div id='countdown' style='font-size: 2rem; font-weight: bold; margin-top: 20px; text-align: center; color: #2196F3;'></div>";
    echo "<script>
    const eventDate = new Date('{$proximoEvento['fecha_evento']}').getTime();
    
    function updateCountdown() {
        const now = new Date().getTime();
        const distance = eventDate - now;
        
        if (distance > 0) {
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('countdown').innerHTML = 
                days + 'd ' + hours + 'h ' + minutes + 'm ' + seconds + 's';
        } else {
            document.getElementById('countdown').innerHTML = '¡Evento en curso!';
        }
    }
    
    updateCountdown();
    setInterval(updateCountdown, 1000);
    </script>";
} else {
    echo "<p>No hay próximos eventos.</p>";
}

echo "<hr>";
echo "<p><a href='index.php' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>🏠 Ir al Inicio</a></p>";
?>

<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        background: #f5f5f5;
    }
    h1 {
        color: #d32f2f;
    }
    h2 {
        color: #1976d2;
        margin-top: 30px;
    }
    table {
        background: white;
        margin: 20px 0;
    }
    th, td {
        text-align: left;
        padding: 12px;
    }
</style>
