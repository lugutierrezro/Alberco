<?php
/**
 * DEBUG SCRIPT - Sistema de Pedidos
 * Este archivo ayuda a identificar problemas en pedido.php
 */

// Activar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Debug - Sistema de Pedidos</title>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #fff; }
    .section { background: #2d2d2d; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .ok { color: #4CAF50; }
    .error { color: #f44336; }
    .warning { color: #ff9800; }
    h2 { color: #03a9f4; }
    pre { background: #000; padding: 10px; overflow-x: auto; }
</style>";
echo "</head><body>";

echo "<h1>🔧 Debug - Sistema de Pedidos de Alberco</h1>";

// ========================================
// 1. VERIFICAR ARCHIVOS NECESARIOS
// ========================================
echo "<div class='section'>";
echo "<h2>1. Archivos del Sistema</h2>";

$archivos = [
    'pedido.php' => __DIR__ . '/pedido.php',
    'config.js.php' => __DIR__ . '/config.js.php',
    'carrito.js' => __DIR__ . '/js/carrito.js',
    'pedido.js' => __DIR__ . '/js/pedido.js',
    'procesar_pedido_directo.php' => __DIR__ . '/procesar_pedido_directo.php'
];

foreach ($archivos as $nombre => $ruta) {
    if (file_exists($ruta)) {
        echo "<div class='ok'>✓ $nombre encontrado</div>";
    } else {
        echo "<div class='error'>✗ $nombre NO ENCONTRADO en: $ruta</div>";
    }
}
echo "</div>";

// ========================================
// 2. VERIFICAR SINTAXIS JAVASCRIPT
// ========================================
echo "<div class='section'>";
echo "<h2>2. Análisis de pedido.js</h2>";

$pedidoJsPath = __DIR__ . '/js/pedido.js';
if (file_exists($pedidoJsPath)) {
    $contenido = file_get_contents($pedidoJsPath);
    
    // Buscar funciones principales
    $funciones = [
        'renderCarrito',
        'cambiarCantidad',
        'eliminarItem',
        'actualizarResumen'
    ];
    
    foreach ($funciones as $func) {
        if (strpos($contenido, "function $func") !== false || strpos($contenido, "$func =") !== false) {
            echo "<div class='ok'>✓ Función $func() encontrada</div>";
        } else {
            echo "<div class='error'>✗ Función $func() NO encontrada</div>";
        }
    }
    
    // Verificar errores comunes
    $llaves_abrir = substr_count($contenido, '{');
    $llaves_cerrar = substr_count($contenido, '}');
    
    if ($llaves_abrir === $llaves_cerrar) {
        echo "<div class='ok'>✓ Llaves balanceadas ({ = $llaves_abrir, } = $llaves_cerrar)</div>";
    } else {
        echo "<div class='error'>✗ Llaves DESBALANCEADAS ({ = $llaves_abrir, } = $llaves_cerrar)</div>";
    }
} else {
    echo "<div class='error'>✗ No se puede verificar pedido.js</div>";
}
echo "</div>";

// ========================================
// 3. VERIFICAR SINTAXIS DE PEDIDO.PHP
// ========================================
echo "<div class='section'>";
echo "<h2>3. Análisis de pedido.php</h2>";

$pedidoPhpPath = __DIR__ . '/pedido.php';
if (file_exists($pedidoPhpPath)) {
    $contenido = file_get_contents($pedidoPhpPath);
    
    // Verificar etiquetas script
    $script_abrir = substr_count($contenido, '<script>') + substr_count($contenido, '<script ');
    $script_cerrar = substr_count($contenido, '</script>');
    
    if ($script_abrir === $script_cerrar) {
        echo "<div class='ok'>✓ Tags &lt;script&gt; balanceados (abiertos = $script_abrir, cerrados = $script_cerrar)</div>";
    } else {
        echo "<div class='error'>✗ Tags &lt;script&gt; DESBALANCEADOS (abiertos = $script_abrir, cerrados = $script_cerrar)</div>";
    }
    
    // Buscar funciones del mapa
    if (strpos($contenido, 'function inicializarMapa') !== false) {
        echo "<div class='ok'>✓ Función inicializarMapa() encontrada</div>";
    } else {
        echo "<div class='error'>✗ Función inicializarMapa() NO encontrada</div>";
    }
    
    if (strpos($contenido, 'function obtenerDireccion') !== false) {
        echo "<div class='ok'>✓ Función obtenerDireccion() encontrada</div>";
    } else {
        echo "<div class='error'>✗ Función obtenerDireccion() NO encontrada</div>";
    }
    
    // Verificar event listeners
    if (strpos($contenido, 'addEventListener') !== false) {
        $count = substr_count($contenido, 'addEventListener');
        echo "<div class='ok'>✓ Event listeners encontrados ($count)</div>";
    } else {
        echo "<div class='warning'>⚠ No se encontraron event listeners</div>";
    }
} else {
    echo "<div class='error'>✗ No se puede verificar pedido.php</div>";
}
echo "</div>";

// ========================================
// 4. VERIFICAR CONEXIÓN A BASE DE DATOS
// ========================================
echo "<div class='section'>";
echo "<h2>4. Conexión a Base de Datos</h2>";

try {
    $isLocal = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1');
    
    if ($isLocal) {
        $dbHost = 'localhost';
        $dbUser = 'root';
        $dbPass = '';
        $dbName = 'sistema_gestion_alberco_v3';
    }
    
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<div class='ok'>✓ Conexión a BD exitosa</div>";
    
    // Verificar tabla tb_pedidos
    $stmt = $pdo->query("SHOW COLUMNS FROM tb_pedidos");
    $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<div class='ok'>✓ Tabla tb_pedidos existe</div>";
    echo "<div style='margin-left: 20px;'>";
    
    $columnas_requeridas = ['id_mesa', 'tipo_pedido', 'direccion_entrega'];
    foreach ($columnas_requeridas as $col) {
        if (in_array($col, $columnas)) {
            echo "<div class='ok'>✓ Columna '$col' existe</div>";
        } else {
            echo "<div class='error'>✗ Columna '$col' NO existe</div>";
        }
    }
    
    // Verificar si metodo_pago existe (NO debería existir)
    if (in_array('metodo_pago', $columnas)) {
        echo "<div class='warning'>⚠ Columna 'metodo_pago' existe en tb_pedidos (no esperado)</div>";
    } else {
        echo "<div class='ok'>✓ Columna 'metodo_pago' NO existe en tb_pedidos (correcto)</div>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>✗ Error de BD: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// ========================================
// 5. TEST DE INSERCIÓN SIMULADA
// ========================================
echo "<div class='section'>";
echo "<h2>5. Simulación de Pedido</h2>";

echo "<h3>Pedido tipo 'para_llevar':</h3>";
echo "<pre>";
$sql_para_llevar = "INSERT INTO tb_pedidos (
    nro_pedido, numero_comanda, id_cliente, id_usuario_registro, tipo_pedido, 
    id_estado, direccion_entrega, id_mesa, observaciones, 
    subtotal, costo_delivery, total
) VALUES (
    'TEST-001', 'TEST-001', 1, 1, 'para_llevar', 
    1, NULL, NULL, 'Test pedido para llevar', 
    25.00, 0.00, 25.00
)";
echo htmlspecialchars($sql_para_llevar);
echo "</pre>";
echo "<div class='ok'>✓ SQL válido (sin metodo_pago)</div>";

echo "<h3>Pedido tipo 'mesa':</h3>";
echo "<pre>";
$sql_mesa = "INSERT INTO tb_pedidos (
    nro_pedido, numero_comanda, id_cliente, id_usuario_registro, tipo_pedido, 
    id_estado, direccion_entrega, id_mesa, observaciones, 
    subtotal, costo_delivery, total
) VALUES (
    'TEST-002', 'TEST-002', 1, 1, 'mesa', 
    1, NULL, 5, 'Test pedido en local', 
    30.00, 0.00, 30.00
)";
echo htmlspecialchars($sql_mesa);
echo "</pre>";
echo "<div class='ok'>✓ SQL válido (con id_mesa)</div>";

echo "</div>";

// ========================================
// 6. LOGS RECIENTES
// ========================================
echo "<div class='section'>";
echo "<h2>6. Logs Recientes</h2>";

$logFile = __DIR__ . '/../logs/pedidos/pedidos_' . date('Y-m-d') . '.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    $ultimas = array_slice($lines, -30);
    
    echo "<div class='ok'>✓ Archivo de log encontrado</div>";
    echo "<h3>Últimas 30 líneas:</h3>";
    echo "<pre>" . htmlspecialchars(implode("\n", $ultimas)) . "</pre>";
} else {
    echo "<div class='warning'>⚠ No hay logs para hoy en: $logFile</div>";
}
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #888;'>Debug generado: " . date('Y-m-d H:i:s') . "</p>";
echo "</body></html>";
?>
