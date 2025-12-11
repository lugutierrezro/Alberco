<?php
/**
 * Initialization file for Alberco Sales Website
 * This file sets up paths, includes necessary files from admin system,
 * and provides helper functions
 */

error_log("=== INICIO init.php ===");

// Set SERVER_NAME if not set (for CLI execution)
if (!isset($_SERVER['SERVER_NAME'])) {
    $_SERVER['SERVER_NAME'] = 'localhost';
}
error_log("SERVER_NAME: " . $_SERVER['SERVER_NAME']);

// Define Admin System Path
if (!defined('ADMIN_PATH')) {
    define('ADMIN_PATH', __DIR__ . '/../../www.sistemaadmalberco.com/');
    error_log("ADMIN_PATH definido: " . ADMIN_PATH);
}

// Verificar que ADMIN_PATH existe
if (!is_dir(ADMIN_PATH)) {
    error_log("ERROR CRÍTICO: ADMIN_PATH no existe: " . ADMIN_PATH);
    die(json_encode([
        'success' => false,
        'error' => 'Ruta de sistema admin no encontrada',
        'debug' => 'ADMIN_PATH: ' . ADMIN_PATH
    ]));
}

// Include Admin Configuration and Database
$configPath = ADMIN_PATH . 'services/database/config.php';
error_log("Intentando cargar config.php: " . $configPath);

if (!file_exists($configPath)) {
    error_log("ERROR: config.php NO EXISTE: " . $configPath);
    die(json_encode([
        'success' => false,
        'error' => 'Archivo de configuración no encontrado'
    ]));
}

try {
    // Suprimir errores de salida para evitar que interrumpan JSON
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    
    require_once $configPath;
    error_log("config.php cargado OK");
} catch (Throwable $e) {
    error_log("ERROR FATAL al cargar config.php: " . $e->getMessage());
    error_log("Archivo: " . $e->getFile() . " Línea: " . $e->getLine());
    error_log("Trace: " . $e->getTraceAsString());
    die(json_encode([
        'success' => false,
        'error' => 'Error al cargar configuración',
        'detalle' => $e->getMessage()
    ]));
}

$databasePath = ADMIN_PATH . 'models/database.php';
error_log("Intentando cargar database.php: " . $databasePath);

if (!file_exists($databasePath)) {
    error_log("ERROR: database.php NO EXISTE: " . $databasePath);
} else {
    require_once $databasePath;
    error_log("database.php cargado OK");
}

// Include Models (Controllers are NOT included - they require authentication)
// The sales site only needs models to read data, not controllers
$modelos = [
    'producto' => ADMIN_PATH . 'models/producto.php',
    'categorias' => ADMIN_PATH . 'models/categorias.php',
    'pedido' => ADMIN_PATH . 'models/pedido.php',
    'cliente' => ADMIN_PATH . 'models/cliente.php',
    'mesas' => ADMIN_PATH . 'models/mesas.php'
];

error_log("=== Cargando Modelos ===");
foreach ($modelos as $nombre => $ruta) {
    error_log("Cargando modelo: $nombre desde $ruta");
    if (!file_exists($ruta)) {
        error_log("ERROR: Modelo $nombre NO EXISTE en $ruta");
        // No detener la ejecución, solo registrar
        continue;
    }
    
    try {
        require_once $ruta;
        error_log("✓ Modelo $nombre cargado OK");
    } catch (Exception $e) {
        error_log("ERROR al cargar modelo $nombre: " . $e->getMessage());
    }
}

// Define URL Base for Admin System (for images and assets) - already defined in admin config
// define('URL_BASE', "http://localhost/www.sistemaadmalberco.com");

// Define Sales Site Paths (avoid conflicts with admin constants)
if (!defined('SALES_APP_PATH')) {
    define('SALES_APP_PATH', dirname(__FILE__));
}
if (!defined('SALES_ROOT')) {
    define('SALES_ROOT', dirname(SALES_APP_PATH));
}
if (!defined('SALES_VIEWS_PATH')) {
    define('SALES_VIEWS_PATH', SALES_ROOT . '/View/');
}
if (!defined('SALES_ASSETS_PATH')) {
    define('SALES_ASSETS_PATH', SALES_ROOT . '/Assets/');
}

// Auto-detect Sales Site URL
if (!defined('SALES_URL_BASE')) {
    if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
        define('SALES_URL_BASE', "http://localhost/www.alberco.com");
    } else {
        define('SALES_URL_BASE', "https://allwiya.pe/www.alberco.com");
    }
}

// Include Helper Functions
$helpersPath = SALES_APP_PATH . '/helpers.php';
error_log("Intentando cargar helpers.php: " . $helpersPath);

if (!file_exists($helpersPath)) {
    error_log("ERROR: helpers.php NO EXISTE: " . $helpersPath);
} else {
    require_once $helpersPath;
    error_log("helpers.php cargado OK");
}

error_log("=== FIN init.php - Carga completa ===");


/**
 * Get image path from admin system
 * @param string $imageFile Image file path relative to admin system
 * @return string Full URL or default image path
 */
function getAdminImagePath($imageFile) {
    if (empty($imageFile)) {
        return 'Assets/no-image.jpg';
    }
    
    $physicalPath = ADMIN_PATH . $imageFile;
    if (file_exists($physicalPath)) {
        return URL_BASE . '/' . $imageFile;
    }
    
    return 'Assets/no-image.jpg';
}
