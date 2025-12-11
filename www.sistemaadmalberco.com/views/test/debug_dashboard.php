<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Debug Dashboard - Sistema Alberco</title>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        h2 { color: #FF6B35; border-bottom: 2px solid #FF6B35; padding-bottom: 10px; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #FF6B35; color: white; }
        .btn { background: #FF6B35; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #F7931E; }
    </style>
</head>
<body>
    <h1>🔧 Diagnóstico del Dashboard - Sistema Alberco</h1>";

// ============================================
// 1. VERIFICAR ARCHIVOS PRINCIPALES
// ============================================
echo "<div class='section'>";
echo "<h2>1. Verificación de Archivos</h2>";

$archivos_criticos = [
    'index.php' => __DIR__ . '/../../index.php',
    'parte1.php' => __DIR__ . '/../../contans/layout/parte1.php',
    'parte2.php' => __DIR__ . '/../../contans/layout/parte2.php',
    'config.php' => __DIR__ . '/../../services/database/config.php',
    'estadisticas.php' => __DIR__ . '/../../controllers/dashboard/estadisticas.php',
    'actividad.php' => __DIR__ . '/../../controllers/dashboard/actividad.php',
    'notificaciones.php' => __DIR__ . '/../../controllers/dashboard/notificaciones.php'
];

echo "<table>";
echo "<tr><th>Archivo</th><th>Estado</th><th>Ruta</th></tr>";
foreach ($archivos_criticos as $nombre => $ruta) {
    $existe = file_exists($ruta);
    $clase = $existe ? 'success' : 'error';
    $estado = $existe ? '✅ Existe' : '❌ No encontrado';
    echo "<tr><td>$nombre</td><td class='$clase'>$estado</td><td style='font-size: 0.8em;'>$ruta</td></tr>";
}
echo "</table>";
echo "</div>";

// ============================================
// 2. VERIFICAR CONEXIÓN A BD
// ============================================
echo "<div class='section'>";
echo "<h2>2. Conexión a Base de Datos</h2>";

try {
    require_once(__DIR__ . '/../../services/database/config.php');
    $pdo = getDB();
    echo "<p class='success'>✅ Conexión exitosa a la base de datos</p>";
    
    // Listar tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Tablas disponibles:</strong> " . count($tablas) . "</p>";
    
    // Verificar tablas críticas
    $tablas_necesarias = ['tb_ventas', 'tb_detalle_venta', 'tb_pedidos', 'tb_clientes', 'tb_almacen', 'tb_arqueo_caja', 'tb_mesas'];
    echo "<table>";
    echo "<tr><th>Tabla Necesaria</th><th>Estado</th></tr>";
    foreach ($tablas_necesarias as $tabla) {
        $existe = in_array($tabla, $tablas);
        $clase = $existe ? 'success' : 'error';
        $estado = $existe ? '✅ Existe' : '❌ Falta';
        echo "<tr><td>$tabla</td><td class='$clase'>$estado</td></tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error de conexión: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// ============================================
// 3. PROBAR CONTROLADORES
// ============================================
echo "<div class='section'>";
echo "<h2>3. Test de Controladores Dashboard</h2>";

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$sistema_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(dirname(__DIR__)));

$endpoints = [
    'Estadísticas' => $base_url . $sistema_path . '/controllers/dashboard/estadisticas.php',
    'Actividad' => $base_url . $sistema_path . '/controllers/dashboard/actividad.php',
    'Notificaciones' => $base_url . $sistema_path . '/controllers/dashboard/notificaciones.php'
];

echo "<table>";
echo "<tr><th>Endpoint</th><th>Respuesta</th><th>Acción</th></tr>";
foreach ($endpoints as $nombre => $url) {
    echo "<tr>";
    echo "<td>$nombre</td>";
    echo "<td id='resp-" . strtolower($nombre) . "'>⏳ Cargando...</td>";
    echo "<td><a href='$url' target='_blank' class='btn'>Abrir</a></td>";
    echo "</tr>";
    
    // Test AJAX
    echo "<script>
        fetch('$url')
            .then(r => r.json())
            .then(data => {
                const elem = document.getElementById('resp-" . strtolower($nombre) . "');
                if (data.success) {
                    elem.innerHTML = '<span class=\"success\">✅ OK</span>';
                    console.log('$nombre:', data);
                } else {
                    elem.innerHTML = '<span class=\"error\">❌ Error: ' + (data.error || 'Desconocido') + '</span>';
                }
            })
            .catch(err => {
                document.getElementById('resp-" . strtolower($nombre) . "').innerHTML = '<span class=\"error\">❌ ' + err.message + '</span>';
            });
    </script>";
}
echo "</table>";
echo "</div>";

// ============================================
// 4. VERIFICAR DATOS DE VENTAS
// ============================================
echo "<div class='section'>";
echo "<h2>4. Datos de Ventas (para gráficos)</h2>";

try {
    // Últimas ventas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_ventas WHERE estado_venta = 'completada'");
    $total_ventas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<p><strong>Ventas completadas:</strong> <span class='success'>$total_ventas</span></p>";
    
    if ($total_ventas > 0) {
        // Últimas 5 ventas - USANDO 'total' no 'monto_total'
        $stmt = $pdo->query("
            SELECT 
                v.id_venta,
                v.fecha_venta,
                v.total,
                v.estado_venta
            FROM tb_ventas v
            ORDER BY v.fecha_venta DESC
            LIMIT 5
        ");
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Fecha</th><th>Monto</th><th>Estado</th></tr>";
        foreach ($ventas as $venta) {
            echo "<tr>";
            echo "<td>#{$venta['id_venta']}</td>";
            echo "<td>{$venta['fecha_venta']}</td>";
            echo "<td>S/ " . number_format($venta['total'], 2) . "</td>";
            echo "<td>{$venta['estado_venta']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ No hay ventas registradas. Los gráficos mostrarán datos en 0.</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error al consultar ventas: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// ============================================
// 5. VERIFICAR ERRORES PHP
// ============================================
echo "<div class='section'>";
echo "<h2>5. Registro de Errores PHP</h2>";

$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    echo "<p><strong>Archivo de errores:</strong> $error_log</p>";
    $ultimas_lineas = array_slice(file($error_log), -20);
    echo "<pre>" . htmlspecialchars(implode('', $ultimas_lineas)) . "</pre>";
} else {
    echo "<p class='warning'>⚠️ No se encontró archivo de log de errores</p>";
    echo "<p><strong>error_log actual:</strong> " . ($error_log ? $error_log : 'No configurado') . "</p>";
}
echo "</div>";

// ============================================
// 6. TEST DE INDEX.PHP
// ============================================
echo "<div class='section'>";
echo "<h2>6. Verificar index.php</h2>";

$index_path = __DIR__ . '/../../index.php';
if (file_exists($index_path)) {
    $index_content = file_get_contents($index_path);
    
    // Verificar includes
    $checks = [
        'parte1.php' => strpos($index_content, "include_once('contans/layout/parte1.php')") !== false,
        'parte2.php' => strpos($index_content, "include_once('contans/layout/parte2.php')") !== false,
        'cargarEstadisticas()' => strpos($index_content, 'cargarEstadisticas()') !== false,
        'content-wrapper' => strpos($index_content, 'content-wrapper') !== false
    ];
    
    // Verificar Chart.js en parte1.php
    $parte1_path = __DIR__ . '/../../contans/layout/parte1.php';
    if (file_exists($parte1_path)) {
        $parte1_content = file_get_contents($parte1_path);
        $checks['Chart.js en parte1.php'] = (
            strpos($parte1_content, 'chart.js') !== false || 
            strpos($parte1_content, 'Chart.js') !== false ||
            strpos($parte1_content, 'chartjs') !== false
        );
    } else {
        $checks['Chart.js en parte1.php'] = false;
    }
    
    echo "<table>";
    echo "<tr><th>Verificación</th><th>Estado</th></tr>";
    foreach ($checks as $nombre => $resultado) {
        $clase = $resultado ? 'success' : 'error';
        $estado = $resultado ? '✅ OK' : '❌ Falta';
        echo "<tr><td>$nombre</td><td class='$clase'>$estado</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ No se pudo leer index.php</p>";
}
echo "</div>";

// ============================================
// 7. ACCIONES RÁPIDAS
// ============================================
echo "<div class='section'>";
echo "<h2>7. Acciones Rápidas</h2>";
echo "<a href='../../index.php' class='btn'>🏠 Ir al Dashboard</a>";
echo "<a href='../../' class='btn'>📊 Ver Sistema</a>";
echo "<a href='javascript:location.reload()' class='btn'>🔄 Recargar Debug</a>";
echo "<br><br>";
echo "<p><strong>🕐 Generado:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

echo "</body></html>";
?>
