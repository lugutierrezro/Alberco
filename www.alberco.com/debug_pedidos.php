&lt;?php
/**
 * VISUALIZADOR DE LOGS DE PEDIDOS
 * Sistema de debug para monitorear pedidos
 */

// Protección básica - Solo en localhost
if ($_SERVER['SERVER_NAME'] !== 'localhost' && $_SERVER['SERVER_NAME'] !== '127.0.0.1') {
    die('Acceso denegado');
}

$logDir = __DIR__ . '/logs/pedidos';
$fecha = $_GET['fecha'] ?? date('Y-m-d');
$logFile = $logDir . "/pedidos_{$fecha}.log";

// Obtener fechas disponibles
$logFiles = glob($logDir . "/pedidos_*.log");
$fechasDisponibles = [];
foreach ($logFiles as $file) {
    if (preg_match('/pedidos_(\d{4}-\d{2}-\d{2})\.log$/', basename($file), $matches)) {
        $fechasDisponibles[] = $matches[1];
    }
}
rsort($fechasDisponibles); // Más reciente primero

// Leer contenido del log
$logContent = '';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
}

// Estadísticas del día
$totalEntradas = substr_count($logContent, '[INFO]') + substr_count($logContent, '[ERROR]');
$totalErrores = substr_count($logContent, '[ERROR]');
$totalPedidosExitosos = substr_count($logContent, '✅ PEDIDO COMPLETADO EXITOSAMENTE');
$totalPedidosFallidos = substr_count($logContent, '❌ ERROR CRÍTICO');

?>&lt;!DOCTYPE html>
&lt;html lang="es">
&lt;head>
    &lt;meta charset="UTF-8">
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0">
    &lt;title>Debug Pedidos - Alberco&lt;/title>
    &lt;link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    &lt;link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    &lt;style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #6c757d;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 1px;
        }
        .log-container {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 1.5rem;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            max-height: 600px;
            overflow-y: auto;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .log-container pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .log-info {
            color: #4fc3f7;
        }
        .log-error {
            color: #ef5350;
            font-weight: bold;
        }
        .log-success {
            color: #66bb6a;
            font-weight: bold;
        }
        .log-warning {
            color: #ffca28;
        }
        .timestamp {
            color: #9e9e9e;
        }
        .empty-log {
            text-align: center;
            padding: 3rem;
            color: #9e9e9e;
        }
        .refresh-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            cursor: pointer;
            transition: all 0.3s;
        }
        .refresh-btn:hover {
            transform: scale(1.1);
        }
    &lt;/style>
&lt;/head>
&lt;body>
    &lt;div class="header">
        &lt;div class="container">
            &lt;h1>&lt;i class="fas fa-bug">&lt;/i> Debug Sistema de Pedidos&lt;/h1>
            &lt;p class="lead mb-0">Monitoreo y análisis de pedidos en tiempo real&lt;/p>
        &lt;/div>
    &lt;/div>

    &lt;div class="container">
        &lt;!-- Selector de fecha -->
        &lt;div class="card mb-4">
            &lt;div class="card-body">
                &lt;form method="get" class="row g-3 align-items-center">
                    &lt;div class="col-auto">
                        &lt;label class="form-label">&lt;strong>Seleccionar Fecha:&lt;/strong>&lt;/label>
                    &lt;/div>
                    &lt;div class="col-md-4">
                        &lt;select name="fecha" class="form-select" onchange="this.form.submit()">
                            &lt;?php foreach ($fechasDisponibles as $f): ?>
                                &lt;option value="&lt;?= $f ?>" &lt;?= $f === $fecha ? 'selected' : '' ?>>
                                    &lt;?= date('d/m/Y', strtotime($f)) ?> 
                                    &lt;?= $f === date('Y-m-d') ? '(HOY)' : '' ?>
                                &lt;/option>
                            &lt;?php endforeach; ?>
                        &lt;/select>
                    &lt;/div>
                    &lt;div class="col-auto">
                        &lt;a href="?" class="btn btn-outline-secondary">
                            &lt;i class="fas fa-redo">&lt;/i> Hoy
                        &lt;/a>
                    &lt;/div>
                &lt;/form>
            &lt;/div>
        &lt;/div>

        &lt;!-- Estadísticas -->
        &lt;div class="row mb-4">
            &lt;div class="col-md-3">
                &lt;div class="stat-card">
                    &lt;div class="stat-number text-primary">&lt;?= $totalEntradas ?>&lt;/div>
                    &lt;div class="stat-label">Total Entradas&lt;/div>
                &lt;/div>
            &lt;/div>
            &lt;div class="col-md-3">
                &lt;div class="stat-card">
                    &lt;div class="stat-number text-success">&lt;?= $totalPedidosExitosos ?>&lt;/div>
                    &lt;div class="stat-label">Pedidos Exitosos&lt;/div>
                &lt;/div>
            &lt;/div>
            &lt;div class="col-md-3">
                &lt;div class="stat-card">
                    &lt;div class="stat-number text-danger">&lt;?= $totalPedidosFallidos ?>&lt;/div>
                    &lt;div class="stat-label">Pedidos Fallidos&lt;/div>
                &lt;/div>
            &lt;/div>
            &lt;div class="col-md-3">
                &lt;div class="stat-card">
                    &lt;div class="stat-number text-warning">&lt;?= $totalErrores ?>&lt;/div>
                    &lt;div class="stat-label">Total Errores&lt;/div>
                &lt;/div>
            &lt;/div>
        &lt;/div>

        &lt;!-- Contenido del log -->
        &lt;div class="card">
            &lt;div class="card-header bg-dark text-white">
                &lt;h5 class="mb-0">
                    &lt;i class="fas fa-file-alt">&lt;/i> 
                    Log del &lt;?= date('d/m/Y', strtotime($fecha)) ?>
                    &lt;small class="float-end">&lt;?= basename($logFile) ?>&lt;/small>
                &lt;/h5>
            &lt;/div>
            &lt;div class="card-body p-0">
                &lt;?php if (!empty($logContent)): ?>
                    &lt;div class="log-container">
                        &lt;pre>&lt;?php
                            // Colorear el log
                            $lines = explode("\n", $logContent);
                            foreach ($lines as $line) {
                                if (empty(trim($line))) {
                                    echo "\n";
                                    continue;
                                }
                                
                                // Colorear según tipo
                                if (strpos($line, '[ERROR]') !== false) {
                                    echo '&lt;span class="log-error">' . htmlspecialchars($line) . '&lt;/span>' . "\n";
                                } elseif (strpos($line, '✅') !== false || strpos($line, '✓') !== false) {
                                    echo '&lt;span class="log-success">' . htmlspecialchars($line) . '&lt;/span>' . "\n";
                                } elseif (strpos($line, '⚠️') !== false || strpos($line, 'WARNING') !== false) {
                                    echo '&lt;span class="log-warning">' . htmlspecialchars($line) . '&lt;/span>' . "\n";
                                } elseif (strpos($line, '❌') !== false) {
                                    echo '&lt;span class="log-error">' . htmlspecialchars($line) . '&lt;/span>' . "\n";
                                } elseif (preg_match('/^\[[\d\-: ]+\]/', $line)) {
                                    // Resaltar timestamp
                                    echo preg_replace(
                                        '/^(\[[\d\-: ]+\])/', 
                                        '&lt;span class="timestamp">$1&lt;/span>', 
                                        htmlspecialchars($line)
                                    ) . "\n";
                                } else {
                                    echo htmlspecialchars($line) . "\n";
                                }
                            }
                        ?>&lt;/pre>
                    &lt;/div>
                &lt;?php else: ?>
                    &lt;div class="empty-log">
                        &lt;i class="fas fa-inbox fa-3x mb-3">&lt;/i>
                        &lt;h4>No hay registros para esta fecha&lt;/h4>
                        &lt;p>No se han procesado pedidos el &lt;?= date('d/m/Y', strtotime($fecha)) ?>&lt;/p>
                    &lt;/div>
                &lt;?php endif; ?>
            &lt;/div>
        &lt;/div>

        &lt;div class="text-center mt-4 mb-5">
            &lt;a href="../index.php" class="btn btn-outline-primary">
                &lt;i class="fas fa-home">&lt;/i> Volver al Inicio
            &lt;/a>
            &lt;a href="Vista/pedido.php" class="btn btn-outline-success">
                &lt;i class="fas fa-shopping-cart">&lt;/i> Hacer Pedido de Prueba
            &lt;/a>
        &lt;/div>
    &lt;/div>

    &lt;button class="refresh-btn" onclick="location.reload()" title="Refrescar">
        &lt;i class="fas fa-sync-alt fa-lg">&lt;/i>
    &lt;/button>

    &lt;script>
        // Auto-refresh cada 30 segundos si es el día actual
        &lt;?php if ($fecha === date('Y-m-d')): ?>
        setTimeout(() => {
            location.reload();
        }, 30000);
        &lt;?php endif; ?>
    &lt;/script>
&lt;/body>
&lt;/html>
