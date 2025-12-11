<?php
/**
 * Verificador de Logs - Sistema de Pedidos
 * Este archivo muestra los últimos logs para diagnosticar problemas en producción
 */

// Solo permitir en desarrollo o con password
$password_correcto = '159023..qQq'; // Mismo que la BD
$password_ingresado = $_GET['pass'] ?? '';

if ($password_ingresado !== $password_correcto) {
    die('Acceso denegado. Usa: ?pass=TU_PASSWORD');
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificador de Logs - Pedidos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #4ec9b0;
            margin-bottom: 10px;
        }
        .info {
            background: #2d2d30;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007acc;
        }
        .log-section {
            background: #252526;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .log-section h2 {
            color: #569cd6;
            margin-bottom: 15px;
            font-size: 18px;
        }
        pre {
            background: #1e1e1e;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            border: 1px solid #3e3e42;
            line-height: 1.5;
        }
        .error {
            color: #f48771;
        }
        .success {
            color: #4ec9b0;
        }
        .warning {
            color: #dcdcaa;
        }
        .button {
            background: #007acc;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
        }
        .button:hover {
            background: #005a9e;
        }
        .timestamp {
            color: #608b4e;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificador de Logs del Sistema</h1>
        
        <div class="info">
            <strong>Servidor:</strong> <?= $_SERVER['SERVER_NAME'] ?><br>
            <strong>Hora actual:</strong> <?= date('Y-m-d H:i:s') ?><br>
            <strong>Environment:</strong> <?= defined('ENVIRONMENT') ? ENVIRONMENT : 'No definido' ?>
        </div>

        <div style="margin-bottom: 20px;">
            <button class="button" onclick="location.reload()">🔄 Recargar</button>
            <button class="button" onclick="window.location='?pass=<?= $password_ingresado ?>&clear=1'">🗑️ Limpiar Logs</button>
        </div>

        <?php
        // Limpiar logs si se solicita
        if (isset($_GET['clear'])) {
            $phpErrorLog = ini_get('error_log');
            if ($phpErrorLog && file_exists($phpErrorLog)) {
                file_put_contents($phpErrorLog, '');
                echo '<div class="info success">✅ Logs limpiados</div>';
            }
        }

        // Mostrar logs de PHP
        echo '<div class="log-section">';
        echo '<h2>📄 Últimas 100 líneas del Error Log de PHP</h2>';
        
        $phpErrorLog = ini_get('error_log');
        if (!$phpErrorLog || $phpErrorLog === 'syslog') {
            // Intentar rutas comunes
            $possibleLogs = [
                '/var/log/php_errors.log',
                '/var/log/php/error.log',
                __DIR__ . '/../logs/php-errors.log',
                __DIR__ . '/../../www.sistemaadmalberco.com/logs/php-errors.log'
            ];
            
            foreach ($possibleLogs as $logPath) {
                if (file_exists($logPath)) {
                    $phpErrorLog = $logPath;
                    break;
                }
            }
        }
        
        if ($phpErrorLog && file_exists($phpErrorLog)) {
            echo '<p style="color: #608b4e;">Ubicación: ' . $phpErrorLog . '</p>';
            
            $lines = file($phpErrorLog);
            $recentLines = array_slice($lines, -100); // Últimas 100 líneas
            
            echo '<pre>';
            foreach ($recentLines as $line) {
                // Colorear según el tipo de mensaje
                $line = htmlspecialchars($line);
                
                if (stripos($line, 'error') !== false || stripos($line, 'fatal') !== false) {
                    echo '<span class="error">' . $line . '</span>';
                } elseif (stripos($line, 'warning') !== false) {
                    echo '<span class="warning">' . $line . '</span>';
                } elseif (stripos($line, 'success') !== false || stripos($line, 'OK') !== false) {
                    echo '<span class="success">' . $line . '</span>';
                } else {
                    echo $line;
                }
            }
            echo '</pre>';
        } else {
            echo '<pre class="error">⚠️ No se pudo encontrar el archivo de log de PHP</pre>';
            echo '<pre>Ubicaciones buscadas:<br>';
            echo 'ini_get(error_log): ' . ($phpErrorLog ?: 'no configurado') . '<br>';
            foreach ($possibleLogs ?? [] as $path) {
                echo $path . ' - ' . (file_exists($path) ? '✅ Existe' : '❌ No existe') . '<br>';
            }
            echo '</pre>';
        }
        echo '</div>';

        // Información de configuración
        echo '<div class="log-section">';
        echo '<h2>⚙️ Configuración del Sistema</h2>';
        echo '<pre>';
        
        require_once __DIR__ . '/../app/init.php';
        
        echo "PHP Version: " . PHP_VERSION . "\n";
        echo "Display Errors: " . ini_get('display_errors') . "\n";
        echo "Log Errors: " . ini_get('log_errors') . "\n";
        echo "Error Log: " . ini_get('error_log') . "\n\n";
        
        echo "=== Constantes del Sistema ===\n";
        echo "ENVIRONMENT: " . (defined('ENVIRONMENT') ? ENVIRONMENT : 'No definido') . "\n";
        echo "SALES_URL_BASE: " . (defined('SALES_URL_BASE') ? SALES_URL_BASE : 'No definido') . "\n";
        echo "URL_BASE (Admin): " . (defined('URL_BASE') ? URL_BASE : 'No definido') . "\n";
        echo "BD: " . (defined('BD') ? BD : 'No definido') . "\n";
        echo "SERVIDOR: " . (defined('SERVIDOR') ? SERVIDOR : 'No definido') . "\n\n";
        
        echo "=== Rutas ===\n";
        echo "__DIR__: " . __DIR__ . "\n";
        echo "ADMIN_PATH: " . (defined('ADMIN_PATH') ? ADMIN_PATH : 'No definido') . "\n";
        echo "SALES_APP_PATH: " . (defined('SALES_APP_PATH') ? SALES_APP_PATH : 'No definido') . "\n\n";
        
        // Test de conexión a BD
        echo "=== Test de Conexión a BD ===\n";
        try {
            $pdo = getDB();
            if ($pdo) {
                echo "✅ Conexión a BD: OK\n";
                $stmt = $pdo->query("SELECT DATABASE()");
                echo "Base de datos actual: " . $stmt->fetchColumn() . "\n";
            } else {
                echo "❌ Conexión a BD: FALLO\n";
            }
        } catch (Exception $e) {
            echo "❌ Error de conexión: " . $e->getMessage() . "\n";
        }
        
        echo '</pre>';
        echo '</div>';
        
        // Test de archivos importantes
        echo '<div class="log-section">';
        echo '<h2>📂 Verificación de Archivos Importantes</h2>';
        echo '<pre>';
        
        $archivosImportantes = [
            __DIR__ . '/../app/init.php',
            __DIR__ . '/procesar_pedido.php',
            __DIR__ . '/config.js.php',
            __DIR__ . '/js/pedido_mejorado.js',
            __DIR__ . '/js/carrito.js',
            __DIR__ . '/../../www.sistemaadmalberco.com/services/database/config.php',
            __DIR__ . '/../../www.sistemaadmalberco.com/models/pedido.php',
            __DIR__ . '/../../www.sistemaadmalberco.com/models/cliente.php'
        ];
        
        foreach ($archivosImportantes as $archivo) {
            $existe = file_exists($archivo);
            $permisos = $existe ? substr(sprintf('%o', fileperms($archivo)), -4) : 'N/A';
            $icono = $existe ? '✅' : '❌';
            
            echo "$icono " . basename($archivo);
            echo " (" . $permisos . ")";
            if (!$existe) {
                echo " - FALTA";
            }
            echo "\n";
        }
        
        echo '</pre>';
        echo '</div>';
        ?>
    </div>
</body>
</html>
