<?php
/**
 * Test simple - Solo verificar que config.php se puede cargar
 * Accede a: https://allwiya.pe/www.alberco.com/Vista/test_config.php
 */

header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== TEST DE CARGA DE CONFIG.PHP ===\n\n";

$configPath = __DIR__ . '/../app/../../www.sistemaadmalberco.com/services/database/config.php';
echo "Ruta de config.php: $configPath\n";
echo "¿Existe?: " . (file_exists($configPath) ? 'SÍ' : 'NO') . "\n\n";

if (!file_exists($configPath)) {
    die("ERROR: config.php no encontrado\n");
}

echo "Intentando cargar config.php...\n";

try {
    require_once $configPath;
    echo "✓ config.php cargado exitosamente\n\n";
    
    echo "=== CONSTANTES DEFINIDAS ===\n";
    echo "ENVIRONMENT: " . (defined('ENVIRONMENT') ? ENVIRONMENT : 'No definido') . "\n";
    echo "SERVIDOR: " . (defined('SERVIDOR') ? SERVIDOR : 'No definido') . "\n";
    echo "BD: " . (defined('BD') ? BD : 'No definido') . "\n";
    echo "URL_BASE: " . (defined('URL_BASE') ? URL_BASE : 'No definido') . "\n\n";
    
    echo "=== TEST DE CONEXIÓN PDO ===\n";
    if (isset($pdo) && $pdo instanceof PDO) {
        echo "✓ Conexión PDO disponible\n";
        $stmt = $pdo->query("SELECT DATABASE()");
        echo "Base de datos conectada: " . $stmt->fetchColumn() . "\n";
    } else {
        echo "✗ PDO no disponible\n";
    }
    
} catch (Throwable $e) {
    echo "\n✗ ERROR CAPTURADO:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DEL TEST ===\n";
