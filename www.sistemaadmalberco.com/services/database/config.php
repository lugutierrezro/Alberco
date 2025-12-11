<?php
/**
 * Archivo de Configuración - Sistema Alberco
 * Solo conexión y rutas
 */

// ============================================
// DETECTAR SI ES LLAMADA DE API
// ============================================
$isApiCall = (
    isset($_SERVER['CONTENT_TYPE']) && 
    stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
) || (
    isset($_SERVER['HTTP_ACCEPT']) && 
    stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
);

// ============================================
// OUTPUT BUFFERING (solo si NO es API)
// ============================================
if (!$isApiCall && !headers_sent()) {
    ob_start();
}

// ============================================
// INICIO DE SESIÓN (solo si NO es API)
// ============================================
if (!$isApiCall && session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_httponly' => true,
        'cookie_secure' => $secure,
        'use_strict_mode' => true,
        'sid_length' => 48
    ]);
}

// ============================================
// CONFIGURACIÓN DE BASE DE DATOS
// ============================================
// Auto-detectar entorno: localhost o producción
$isLocal = ($_SERVER['SERVER_NAME'] === 'localhost' || 
            $_SERVER['SERVER_NAME'] === '127.0.0.1' || 
            strpos($_SERVER['SERVER_NAME'], 'localhost') !== false);

if ($isLocal) {
    // CONFIGURACIÓN LOCAL (XAMPP)
    define('SERVIDOR', 'localhost');
    define('USUARIO', 'root');
    define('PASSWORD', '');
    define('BD', 'sistema_gestion_alberco_v3');
} else {
    // CONFIGURACIÓN PRODUCCIÓN (HOSTING)
    define('SERVIDOR', '127.0.0.1');
    define('USUARIO', 'allwiya_Gustavo');
    define('PASSWORD', '159023..qQq');
    define('BD', 'allwiya_allwiya_gustavo');
}



// ============================================
// CONFIGURACIÓN DE URL BASE
// ============================================
// Auto-detectar URL base según el servidor
if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    // Desarrollo local
    define('URL_BASE', "http://localhost/www.sistemaadmalberco.com");
} else {
    // Producción - usando HTTPS
    define('URL_BASE', "https://allwiya.pe/www.sistemaadmalberco.com");
}

// Rutas de directorios
define('APP_PATH', dirname(__FILE__));
define('CONTROLLERS_PATH', APP_PATH . '/controllers/');
define('MODELS_PATH', APP_PATH . '/models/');
define('VIEWS_PATH', APP_PATH . '/views/');
define('UPLOADS_PATH', APP_PATH . '/uploads/');

// ============================================
// CONFIGURACIÓN DE ERRORES
// ============================================
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    define('ENVIRONMENT', 'development');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', APP_PATH . '/logs/php-errors.log');
    define('ENVIRONMENT', 'production');
}

// ============================================
// CONEXIÓN PDO
// ============================================
$servidor = "mysql:host=" . SERVIDOR . ";dbname=" . BD . ";charset=utf8mb4";

try {
    $pdo = new PDO($servidor, USUARIO, PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);

    $GLOBALS['pdo'] = $pdo;

} catch (PDOException $e) {
    error_log("Error de conexión DB [" . date('Y-m-d H:i:s') . "]: " . $e->getMessage());

    if (ENVIRONMENT === 'development') {
        // Mostrar información de debug en desarrollo
        echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 8px; font-family: monospace;'>";
        echo "<h3>❌ Error de Conexión a Base de Datos</h3>";
        echo "<p><strong>Entorno detectado:</strong> " . ENVIRONMENT . "</p>";
        echo "<p><strong>Servidor web:</strong> " . $_SERVER['SERVER_NAME'] . "</p>";
        echo "<p><strong>Host BD intentado:</strong> " . SERVIDOR . "</p>";
        echo "<p><strong>Usuario BD:</strong> " . USUARIO . "</p>";
        echo "<p><strong>Base de datos:</strong> " . BD . "</p>";
        echo "<p><strong>Error MySQL:</strong> " . $e->getMessage() . "</p>";
        echo "<hr>";
        echo "<h4>🔧 Soluciones posibles:</h4>";
        echo "<ol>";
        echo "<li>Verifica que MySQL esté corriendo en XAMPP</li>";
        echo "<li>Verifica que exista la base de datos '<strong>" . BD . "</strong>'</li>";
        echo "<li>Verifica las credenciales en config.php</li>";
        echo "</ol>";
        echo "</div>";
        die();
    } else {
        die("Error de conexión al sistema. Contacte al administrador.");
    }
}

// ============================================
// FUNCIONES AUXILIARES
// ============================================

if (!function_exists('getDB')) {
    function getDB() {
        return $GLOBALS['pdo'];
    }
}

if (!function_exists('getFechaHora')) {
    function getFechaHora($format = 'Y-m-d H:i:s') {
        return date($format);
    }
}

if (!function_exists('sanitize')) {
    function sanitize($data) {
        if (is_array($data)) {
            return array_map('sanitize', $data);
        }
        return htmlspecialchars(strip_tags(trim($data ?? '')), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('validarEmail')) {
    function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('generarToken')) {
    function generarToken($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
}

// ============================================
// CONSTANTES DE LA APLICACIÓN
// ============================================
define('EMPRESA_NOMBRE', 'Pollería-Chifa Alberco');
define('EMPRESA_RUC', '20123456789');
define('EMPRESA_DIRECCION', 'Av. Principal 123, Lima - Perú');
define('EMPRESA_TELEFONO', '(01) 234-5678');
define('EMPRESA_EMAIL', 'info@alberco.com');

define('IGV_PORCENTAJE', 18);
define('IGV_DECIMAL', 0.18);
define('REGISTROS_POR_PAGINA', 20);
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

define('ESTADO_PENDIENTE', 1);
define('ESTADO_EN_PREPARACION', 2);
define('ESTADO_LISTO', 3);
define('ESTADO_EN_CAMINO', 4);
define('ESTADO_ENTREGADO', 5);
define('ESTADO_CANCELADO', 6);

// ============================================
// AUTOLOAD
// ============================================
spl_autoload_register(function ($class_name) {
    $paths = [
        MODELS_PATH . $class_name . '.php',
        CONTROLLERS_PATH . $class_name . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// ============================================
// FECHA Y HORA ACTUAL
// ============================================
$fechaHora = getFechaHora();

// ============================================
// FUNCIONES DE DEPURACIÓN (SOLO DEVELOPMENT)
// ============================================
if (ENVIRONMENT === 'development') {
    if (!function_exists('dd')) {
        function dd($data) {
            echo '<pre>';
            var_dump($data);
            echo '</pre>';
            die();
        }
    }

    if (!function_exists('dp')) {
        function dp($data) {
            echo '<pre>';
            print_r($data);
            echo '</pre>';
        }
    }
}

if (!function_exists('jsonResponse')) {
    function jsonResponse($success, $message, $data = []) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
}
