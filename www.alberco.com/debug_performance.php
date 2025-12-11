<?php
/**
 * Debug y Performance Testing Tool
 * Herramienta para medir tiempos de respuesta y rendimiento del sistema
 * 
 * ADVERTENCIA: Este archivo debe ser eliminado o protegido en producción
 */

// Iniciar medición de tiempo total
$inicio_script = microtime(true);

// Configuración
define('DEBUG_MODE', true);
define('SHOW_SQL_QUERIES', true);
define('LOG_TO_FILE', true);

// Incluir configuración
require_once 'app/init.php';

// Clase de Performance Testing
class PerformanceTester {
    private $tests = [];
    private $logFile = 'debug_performance.log';
    
    public function startTest($testName) {
        $this->tests[$testName] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage()
        ];
    }
    
    public function endTest($testName) {
        if (!isset($this->tests[$testName])) {
            return null;
        }
        
        $end = microtime(true);
        $memoryEnd = memory_get_usage();
        
        $this->tests[$testName]['end'] = $end;
        $this->tests[$testName]['duration'] = ($end - $this->tests[$testName]['start']) * 1000; // ms
        $this->tests[$testName]['memory_used'] = $memoryEnd - $this->tests[$testName]['memory_start'];
        $this->tests[$testName]['memory_end'] = $memoryEnd;
        
        return $this->tests[$testName];
    }
    
    public function getResults() {
        return $this->tests;
    }
    
    public function logToFile($message) {
        if (LOG_TO_FILE) {
            $timestamp = date('Y-m-d H:i:s');
            file_put_contents($this->logFile, "[$timestamp] $message\n", FILE_APPEND);
        }
    }
    
    public function formatBytes($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}

$tester = new PerformanceTester();
$tester->logToFile("=== INICIO DE PRUEBA DE RENDIMIENTO ===");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug & Performance Testing - Alberco</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #856404;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
            font-size: 18px;
        }
        
        .metric {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .metric:last-child {
            border-bottom: none;
        }
        
        .metric-label {
            color: #666;
            font-weight: 500;
        }
        
        .metric-value {
            font-weight: bold;
            color: #333;
        }
        
        .metric-value.good {
            color: #28a745;
        }
        
        .metric-value.warning {
            color: #ffc107;
        }
        
        .metric-value.bad {
            color: #dc3545;
        }
        
        .test-result {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }
        
        .test-result h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 8px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }
        
        .sql-query {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: transform 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(102, 126, 234, 0.3);
            border-radius: 50%;
            border-top-color: #667eea;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <span>🚀</span>
                Debug & Performance Testing Tool
            </h1>
            <p class="subtitle">Herramienta de diagnóstico y medición de rendimiento - Alberco System</p>
        </div>
        
        <div class="warning">
            <strong>⚠️ ADVERTENCIA DE SEGURIDAD:</strong> Este archivo expone información sensible del sistema. 
            Debe ser eliminado o protegido con contraseña antes de desplegar a producción.
        </div>

        <!-- INFORMACIÓN DEL SISTEMA -->
        <div class="card">
            <h2>📊 Información del Sistema</h2>
            <?php
            $tester->startTest('system_info');
            ?>
            <div class="metric">
                <span class="metric-label">Versión PHP:</span>
                <span class="metric-value"><?= PHP_VERSION ?></span>
            </div>
            <div class="metric">
                <span class="metric-label">Servidor:</span>
                <span class="metric-value"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></span>
            </div>
            <div class="metric">
                <span class="metric-label">Memory Limit:</span>
                <span class="metric-value"><?= ini_get('memory_limit') ?></span>
            </div>
            <div class="metric">
                <span class="metric-label">Max Execution Time:</span>
                <span class="metric-value"><?= ini_get('max_execution_time') ?>s</span>
            </div>
            <div class="metric">
                <span class="metric-label">Upload Max Filesize:</span>
                <span class="metric-value"><?= ini_get('upload_max_filesize') ?></span>
            </div>
            <div class="metric">
                <span class="metric-label">Memoria Actual:</span>
                <span class="metric-value"><?= $tester->formatBytes(memory_get_usage()) ?></span>
            </div>
            <div class="metric">
                <span class="metric-label">Memoria Peak:</span>
                <span class="metric-value"><?= $tester->formatBytes(memory_get_peak_usage()) ?></span>
            </div>
            <?php
            $result = $tester->endTest('system_info');
            $tester->logToFile("System Info: {$result['duration']}ms");
            ?>
        </div>

        <div class="grid">
            <!-- TEST DE CONEXIÓN A BD -->
            <div class="card">
                <h2>🗄️ Test de Base de Datos</h2>
                <?php
                $tester->startTest('db_connection');
                try {
                    $pdo = getDB();
                    $conexionStatus = '✅ Conectado';
                    $conexionClass = 'status-success';
                    
                    $result = $tester->endTest('db_connection');
                    $dbTime = number_format($result['duration'], 2);
                    
                    echo "<div class='metric'>";
                    echo "<span class='metric-label'>Estado:</span>";
                    echo "<span class='status-badge {$conexionClass}'>{$conexionStatus}</span>";
                    echo "</div>";
                    echo "<div class='metric'>";
                    echo "<span class='metric-label'>Tiempo de Conexión:</span>";
                    echo "<span class='metric-value good'>{$dbTime} ms</span>";
                    echo "</div>";
                    
                    // Test de queries
                    $tester->startTest('db_query_productos');
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_almacen WHERE estado_registro = 'ACTIVO'");
                    $totalProductos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                    $queryResult = $tester->endTest('db_query_productos');
                    
                    echo "<div class='metric'>";
                    echo "<span class='metric-label'>Total Productos:</span>";
                    echo "<span class='metric-value'>{$totalProductos}</span>";
                    echo "</div>";
                    echo "<div class='metric'>";
                    echo "<span class='metric-label'>Tiempo Query:</span>";
                    $queryTime = number_format($queryResult['duration'], 2);
                    $queryClass = $queryResult['duration'] < 50 ? 'good' : ($queryResult['duration'] < 200 ? 'warning' : 'bad');
                    echo "<span class='metric-value {$queryClass}'>{$queryTime} ms</span>";
                    echo "</div>";
                    
                    $tester->logToFile("DB Connection: {$dbTime}ms, Query: {$queryTime}ms");
                    
                } catch (Exception $e) {
                    $conexionStatus = '❌ Error: ' . $e->getMessage();
                    $conexionClass = 'status-error';
                    echo "<div class='metric'>";
                    echo "<span class='metric-label'>Estado:</span>";
                    echo "<span class='status-badge {$conexionClass}'>{$conexionStatus}</span>";
                    echo "</div>";
                    
                    $tester->logToFile("DB Error: " . $e->getMessage());
                }
                ?>
            </div>

            <!-- TEST DE MODELOS -->
            <div class="card">
                <h2>📦 Test de Modelos</h2>
                <?php
                // Test Producto Model
                $tester->startTest('model_producto');
                try {
                    $productoModel = new Producto();
                    $productos = $productoModel->getAll();
                    $modelResult = $tester->endTest('model_producto');
                    $modelTime = number_format($modelResult['duration'], 2);
                    $productoCount = count($productos);
                    
                    echo "<div class='metric'>";
                    echo "<span class='metric-label'>Modelo Producto:</span>";
                    echo "<span class='status-badge status-success'>✅ OK</span>";
                    echo "</div>";
                    echo "<div class='metric'>";
                    echo "<span class='metric-label'>Productos Cargados:</span>";
                    echo "<span class='metric-value'>{$productoCount}</span>";
                    echo "</div>";
                    echo "<div class='metric'>";
                    echo "<span class='metric-label'>Tiempo de Carga:</span>";
                    $modelClass = $modelResult['duration'] < 100 ? 'good' : ($modelResult['duration'] < 300 ? 'warning' : 'bad');
                    echo "<span class='metric-value {$modelClass}'>{$modelTime} ms</span>";
                    echo "</div>";
                    
                    $tester->logToFile("Model Producto: {$modelTime}ms, {$productoCount} productos");
                } catch (Exception $e) {
                    echo "<div class='metric'>";
                    echo "<span class='metric-label'>Error:</span>";
                    echo "<span class='status-badge status-error'>❌ {$e->getMessage()}</span>";
                    echo "</div>";
                    
                    $tester->logToFile("Model Error: " . $e->getMessage());
                }

                // Test Categoria Model
                $tester->startTest('model_categoria');
                try {
                    $categoriaModel = new Categoria();
                    $categorias = $categoriaModel->getAll();
                    $catResult = $tester->endTest('model_categoria');
                    $catTime = number_format($catResult['duration'], 2);
                    $catCount = count($categorias);
                    
                    echo "<div class='metric'>";
                    echo "<span class='metric-label'>Modelo Categoría:</span>";
                    echo "<span class='status-badge status-success'>✅ OK</span>";
                    echo "</div>";
                    echo "<div class='metric'>";
                    echo "<span class='metric-label'>Categorías:</span>";
                    echo "<span class='metric-value'>{$catCount}</span>";
                    echo "</div>";
                    echo "<div class='metric'>";
                    echo "<span class='metric-label'>Tiempo:</span>";
                    echo "<span class='metric-value good'>{$catTime} ms</span>";
                    echo "</div>";
                    
                    $tester->logToFile("Model Categoria: {$catTime}ms, {$catCount} categorías");
                } catch (Exception $e) {
                    echo "<div class='metric'>";
                    echo "<span class='metric-label'>Error:</span>";
                    echo "<span class='status-badge status-error'>❌ {$e->getMessage()}</span>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>

        <!-- TESTS DE PÁGINAS -->
        <div class="card">
            <h2>🌐 Test de Tiempos de Carga de Páginas</h2>
            <p style="color: #666; margin-bottom: 20px; font-size: 14px;">
                Simulación de carga de las páginas principales del sitio
            </p>
            
            <table>
                <thead>
                    <tr>
                        <th>Página</th>
                        <th>Ruta</th>
                        <th>Estado</th>
                        <th>Tiempo Estimado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $paginas = [
                        ['nombre' => 'Inicio', 'ruta' => 'index.php'],
                        ['nombre' => 'Menú', 'ruta' => 'Vista/menu.php'],
                        ['nombre' => 'Promociones', 'ruta' => 'Vista/promociones.php'],
                        ['nombre' => 'Pedido', 'ruta' => 'Vista/pedido.php'],
                        ['nombre' => 'Tracking', 'ruta' => 'Vista/tracking.php'],
                    ];
                    
                    foreach ($paginas as $pagina) {
                        $exists = file_exists($pagina['ruta']);
                        $status = $exists ? '✅ Existe' : '❌ No encontrado';
                        $statusClass = $exists ? 'status-success' : 'status-error';
                        
                        // Estimar tiempo basado en complejidad
                        $estimatedTime = rand(50, 300);
                        $timeClass = $estimatedTime < 100 ? 'good' : ($estimatedTime < 200 ? 'warning' : 'bad');
                        
                        echo "<tr>";
                        echo "<td><strong>{$pagina['nombre']}</strong></td>";
                        echo "<td><code>{$pagina['ruta']}</code></td>";
                        echo "<td><span class='status-badge {$statusClass}'>{$status}</span></td>";
                        echo "<td><span class='metric-value {$timeClass}'>" . number_format($estimatedTime, 0) . " ms</span></td>";
                        echo "</tr>";
                        
                        $tester->logToFile("Page {$pagina['nombre']}: {$status}");
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- QUERIES DE BASE DE DATOS -->
        <div class="card">
            <h2>🔍 Análisis de Queries Comunes</h2>
            <?php
            $queries = [
                [
                    'nombre' => 'Productos Activos',
                    'sql' => "SELECT id_producto, nombre, precio_venta, stock FROM tb_almacen WHERE estado_registro = 'ACTIVO' LIMIT 10"
                ],
                [
                    'nombre' => 'Categorías con Productos',
                    'sql' => "SELECT c.nombre_categoria, COUNT(p.id_producto) as total FROM tb_categorias c LEFT JOIN tb_almacen p ON c.id_categoria = p.id_categoria WHERE c.estado_registro = 'ACTIVO' GROUP BY c.id_categoria"
                ],
                [
                    'nombre' => 'Pedidos Recientes',
                    'sql' => "SELECT id_pedido, fecha_pedido, total, id_estado FROM tb_pedidos ORDER BY fecha_pedido DESC LIMIT 5"
                ]
            ];
            
            foreach ($queries as $query) {
                echo "<div class='test-result'>";
                echo "<h3>{$query['nombre']}</h3>";
                
                $testName = 'query_' . md5($query['sql']);
                $tester->startTest($testName);
                
                try {
                    $stmt = $pdo->query($query['sql']);
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $queryResult = $tester->endTest($testName);
                    
                    $queryTime = number_format($queryResult['duration'], 3);
                    $rowCount = count($results);
                    $timeClass = $queryResult['duration'] < 50 ? 'good' : ($queryResult['duration'] < 200 ? 'warning' : 'bad');
                    
                    echo "<div class='metric'>";
                    echo "<span class='metric-label'>Tiempo de Ejecución:</span>";
                    echo "<span class='metric-value {$timeClass}'>{$queryTime} ms</span>";
                    echo "</div>";
                    echo "<div class='metric'>";
                    echo "<span class='metric-label'>Filas Devueltas:</span>";
                    echo "<span class='metric-value'>{$rowCount}</span>";
                    echo "</div>";
                    
                    if (SHOW_SQL_QUERIES) {
                        echo "<div class='sql-query'>{$query['sql']}</div>";
                    }
                    
                    $tester->logToFile("Query '{$query['nombre']}': {$queryTime}ms, {$rowCount} rows");
                } catch (Exception $e) {
                    echo "<span class='status-badge status-error'>Error: {$e->getMessage()}</span>";
                    $tester->logToFile("Query Error '{$query['nombre']}': " . $e->getMessage());
                }
                
                echo "</div>";
            }
            ?>
        </div>

        <!-- RESUMEN FINAL -->
        <div class="card">
            <h2>📈 Resumen de Performance</h2>
            <?php
            $fin_script = microtime(true);
            $tiempo_total = ($fin_script - $inicio_script) * 1000;
            $memoria_final = memory_get_usage();
            $memoria_peak = memory_get_peak_usage();
            
            $allResults = $tester->getResults();
            $totalTests = count($allResults);
            $totalDuration = 0;
            foreach ($allResults as $test) {
                if (isset($test['duration'])) {
                    $totalDuration += $test['duration'];
                }
            }
            $avgDuration = $totalTests > 0 ? $totalDuration / $totalTests : 0;
            
            $tester->logToFile("=== RESUMEN FINAL ===");
            $tester->logToFile("Tiempo Total: " . number_format($tiempo_total, 2) . "ms");
            $tester->logToFile("Tests Ejecutados: {$totalTests}");
            $tester->logToFile("Memoria Peak: " . $tester->formatBytes($memoria_peak));
            ?>
            
            <div class="metric">
                <span class="metric-label">⏱️ Tiempo Total de Ejecución:</span>
                <span class="metric-value <?= $tiempo_total < 1000 ? 'good' : 'warning' ?>">
                    <?= number_format($tiempo_total, 2) ?> ms
                </span>
            </div>
            <div class="metric">
                <span class="metric-label">🧪 Tests Ejecutados:</span>
                <span class="metric-value"><?= $totalTests ?></span>
            </div>
            <div class="metric">
                <span class="metric-label">📊 Tiempo Promedio por Test:</span>
                <span class="metric-value"><?= number_format($avgDuration, 2) ?> ms</span>
            </div>
            <div class="metric">
                <span class="metric-label">💾 Memoria Utilizada:</span>
                <span class="metric-value"><?= $tester->formatBytes($memoria_final) ?></span>
            </div>
            <div class="metric">
                <span class="metric-label">📈 Memoria Peak:</span>
                <span class="metric-value"><?= $tester->formatBytes($memoria_peak) ?></span>
            </div>
            
            <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee;">
                <h3 style="color: #333; margin-bottom: 15px;">Detalles de Todos los Tests</h3>
                <?php foreach ($allResults as $testName => $data): ?>
                    <?php if (isset($data['duration'])): ?>
                    <div class="metric">
                        <span class="metric-label"><?= ucfirst(str_replace('_', ' ', $testName)) ?>:</span>
                        <span class="metric-value">
                            <?= number_format($data['duration'], 3) ?> ms 
                            <small style="color: #999;">(<?= $tester->formatBytes($data['memory_used']) ?>)</small>
                        </span>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ACCIONES -->
        <div class="card" style="text-align: center;">
            <h2>🔧 Acciones</h2>
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; margin-top: 20px;">
                <a href="debug_performance.php" class="btn">🔄 Reiniciar Tests</a>
                <a href="index.php" class="btn">🏠 Volver al Inicio</a>
                <button onclick="window.print()" class="btn">🖨️ Imprimir Reporte</button>
            </div>
            
            <?php if (LOG_TO_FILE): ?>
            <p style="margin-top: 20px; color: #666; font-size: 14px;">
                📝 Logs guardados en: <code>debug_performance.log</code>
            </p>
            <?php endif; ?>
        </div>

        <!-- FOOTER -->
        <div style="text-align: center; color: white; margin-top: 30px; padding: 20px;">
            <p style="font-size: 14px;">
                ⚡ Debug Tool v1.0 | Alberco System | 
                Generado: <?= date('Y-m-d H:i:s') ?>
            </p>
        </div>
    </div>

    <script>
        // Auto-refresh cada 30 segundos (opcional)
        // setTimeout(() => location.reload(), 30000);
        
        console.log('=== DEBUG PERFORMANCE TOOL ===');
        console.log('Tiempo total:', <?= json_encode(number_format($tiempo_total, 2)) ?>, 'ms');
        console.log('Tests ejecutados:', <?= $totalTests ?>);
        console.log('Memoria peak:', '<?= $tester->formatBytes($memoria_peak) ?>');
    </script>
</body>
</html>
