<?php
/**
 * TEST AUTOMATIZADO DE APIs MÓVILES
 * Ejecuta pruebas completas de todos los endpoints
 */

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración base
// Auto-detectar entorno
$isLocal = (isset($_SERVER['SERVER_NAME']) && 
           ($_SERVER['SERVER_NAME'] === 'localhost' || 
            $_SERVER['SERVER_NAME'] === '127.0.0.1'));

if ($isLocal) {
    define('API_BASE', 'http://localhost/www.sistemaadmalberco.com/api');
} else {
    define('API_BASE', 'https://allwiya.pe/www.sistemaadmalberco.com/api');
}
define('TIMEOUT', 30);

// Colores para consola (si se ejecuta desde CLI)
$isCliMode = php_sapi_name() === 'cli';

// Contador de pruebas
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;
$testResults = [];

/**
 * Función para hacer peticiones HTTP
 */
function makeRequest($method, $url, $data = null, $headers = []) {
    $ch = curl_init();
    
    if ($method === 'GET' && $data) {
        $url .= '?' . http_build_query($data);
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, TIMEOUT);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($jsonData);
        }
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'body' => $response,
        'error' => $error,
        'data' => json_decode($response, true)
    ];
}

/**
 * Ejecuta una prueba individual
 */
function runTest($name, $description, $method, $endpoint, $data = null, $expectedSuccess = true) {
    global $totalTests, $passedTests, $failedTests, $testResults;
    
    $totalTests++;
    $startTime = microtime(true);
    
    $url = API_BASE . $endpoint;
    $result = makeRequest($method, $url, $data);
    
    $duration = round((microtime(true) - $startTime) * 1000, 2);
    
    $passed = false;
    $message = '';
    
    // Validar resultado
    if ($result['error']) {
        $message = "Error de conexión: {$result['error']}";
    } elseif ($result['http_code'] !== 200) {
        $message = "HTTP {$result['http_code']} - Esperado 200";
    } elseif (!$result['data']) {
        $message = "Respuesta no es JSON válido";
    } elseif (!isset($result['data']['success'])) {
        $message = "Respuesta no tiene campo 'success'";
    } elseif ($result['data']['success'] !== $expectedSuccess) {
        $expected = $expectedSuccess ? 'exitoso' : 'fallido';
        $message = "Se esperaba resultado {$expected}";
    } else {
        $passed = true;
        $message = "✓ Correcto";
    }
    
    if ($passed) {
        $passedTests++;
    } else {
        $failedTests++;
    }
    
    $testResults[] = [
        'test' => $name,
        'description' => $description,
        'passed' => $passed,
        'duration_ms' => $duration,
        'http_code' => $result['http_code'],
        'message' => $message,
        'response' => $result['data']
    ];
    
    return $passed;
}

echo "🧪 INICIANDO PRUEBAS DE API MÓVIL - ALBERCO\n";
echo "=========================================\n\n";

// ============================================
// PRUEBAS DE AUTENTICACIÓN
// ============================================
echo "📋 MÓDULO: AUTENTICACIÓN\n";
echo "------------------------\n";

runTest(
    'AUTH-001',
    'Login con credenciales inválidas (debe fallar)',
    'POST',
    '/auth/login_empleado.php',
    ['email' => 'invalido@test.com', 'password' => 'wrongpass'],
    false
);

runTest(
    'AUTH-002',
    'Login sin email (debe fallar)',
    'POST',
    '/auth/login_empleado.php',
    ['password' => 'test123'],
    false
);

runTest(
    'AUTH-003',
    'Login sin password (debe fallar)',
    'POST',
    '/auth/login_empleado.php',
    ['email' => 'test@test.com'],
    false
);

// ============================================
// PRUEBAS DE LISTADO DE PEDIDOS
// ============================================
echo "\n📋 MÓDULO: LISTADO DE PEDIDOS\n";
echo "----------------------------\n";

runTest(
    'PEDIDOS-001',
    'Listar todos los pedidos activos',
    'GET',
    '/pedidos/listar_por_estado.php',
    [],
    true
);

runTest(
    'PEDIDOS-002',
    'Listar pedidos de cocina',
    'GET',
    '/pedidos/listar_por_estado.php',
    ['tipo' => 'cocina'],
    true
);

runTest(
    'PEDIDOS-003',
    'Listar pedidos por estado específico (id=1)',
    'GET',
    '/pedidos/listar_por_estado.php',
    ['estado_id' => 1],
    true
);

// ============================================
// PRUEBAS DE PEDIDOS POR DELIVERY
// ============================================
echo "\n📋 MÓDULO: PEDIDOS POR DELIVERY\n";
echo "------------------------------\n";

runTest(
    'DELIVERY-001',
    'Listar pedidos sin ID de empleado (debe fallar)',
    'GET',
    '/pedidos/por_delivery.php',
    [],
    false
);

runTest(
    'DELIVERY-002',
    'Listar pedidos con ID de empleado válido',
    'GET',
    '/pedidos/por_delivery.php',
    ['empleado_id' => 1],
    true
);

runTest(
    'DELIVERY-003',
    'Listar pedidos con ID de empleado inexistente',
    'GET',
    '/pedidos/por_delivery.php',
    ['empleado_id' => 99999],
    true // Debe ser exitoso pero con array vacío
);

// ============================================
// PRUEBAS DE DETALLE DE PEDIDO
// ============================================
echo "\n📋 MÓDULO: DETALLE DE PEDIDO\n";
echo "---------------------------\n";

runTest(
    'DETALLE-001',
    'Obtener detalle sin ID de pedido (debe fallar)',
    'GET',
    '/pedidos/detalle.php',
    [],
    false
);

runTest(
    'DETALLE-002',
    'Obtener detalle de pedido inexistente (debe fallar)',
    'GET',
    '/pedidos/detalle.php',
    ['pedido_id' => 99999],
    false
);

// ============================================
// PRUEBAS DE CAMBIO DE ESTADO
// ============================================
echo "\n📋 MÓDULO: CAMBIO DE ESTADO\n";
echo "--------------------------\n";

runTest(
    'ESTADO-001',
    'Cambiar estado sin datos (debe fallar)',
    'POST',
    '/pedidos/cambiar_estado.php',
    [],
    false
);

runTest(
    'ESTADO-002',
    'Cambiar estado sin estado_id (debe fallar)',
    'POST',
    '/pedidos/cambiar_estado.php',
    ['pedido_id' => 1],
    false
);

runTest(
    'ESTADO-003',
    'Cambiar estado de pedido inexistente (debe fallar)',
    'POST',
    '/pedidos/cambiar_estado.php',
    ['pedido_id' => 99999, 'estado_id' => 2],
    false
);

// ============================================
// PRUEBAS DE TRACKING GPS
// ============================================
echo "\n📋 MÓDULO: TRACKING GPS\n";
echo "----------------------\n";

runTest(
    'TRACKING-001',
    'Actualizar ubicación sin datos (debe fallar)',
    'POST',
    '/tracking/actualizar_ubicacion.php',
    [],
    false
);

runTest(
    'TRACKING-002',
    'Actualizar ubicación sin coordenadas (debe fallar)',
    'POST',
    '/tracking/actualizar_ubicacion.php',
    ['pedido_id' => 1, 'empleado_id' => 1],
    false
);

runTest(
    'TRACKING-003',
    'Actualizar ubicación de pedido inexistente (debe fallar)',
    'POST',
    '/tracking/actualizar_ubicacion.php',
    [
        'pedido_id' => 99999,
        'empleado_id' => 1,
        'latitud' => -12.046374,
        'longitud' => -77.042793
    ],
    false
);

// ============================================
// RESUMEN FINAL
// ============================================
echo "\n\n";
echo "========================================\n";
echo "📊 RESUMEN DE PRUEBAS\n";
echo "========================================\n";
echo "Total de pruebas: {$totalTests}\n";
echo "✅ Exitosas: {$passedTests}\n";
echo "❌ Fallidas: {$failedTests}\n";
$successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0;
echo "📈 Tasa de éxito: {$successRate}%\n";
echo "========================================\n\n";

// ============================================
// REPORTE DETALLADO
// ============================================
echo "📄 REPORTE DETALLADO:\n\n";

$detailedReport = [];
foreach ($testResults as $test) {
    $status = $test['passed'] ? '✅ PASS' : '❌ FAIL';
    $category = explode('-', $test['test'])[0];
    
    if (!isset($detailedReport[$category])) {
        $detailedReport[$category] = [];
    }
    
    $detailedReport[$category][] = [
        'id' => $test['test'],
        'description' => $test['description'],
        'status' => $status,
        'duration' => $test['duration_ms'] . 'ms',
        'http_code' => $test['http_code'],
        'message' => $test['message'],
        'response_preview' => $test['response'] ? (
            isset($test['response']['mensaje']) ? $test['response']['mensaje'] : 
            (isset($test['response']['total']) ? "{$test['response']['total']} registros" : 'OK')
        ) : 'No response'
    ];
}

foreach ($detailedReport as $category => $tests) {
    echo "\n🔹 {$category}\n";
    foreach ($tests as $test) {
        echo "  [{$test['id']}] {$test['status']} - {$test['description']}\n";
        echo "      ⏱️  {$test['duration']} | HTTP {$test['http_code']}\n";
        echo "      💬 {$test['message']}\n";
        if (!str_contains($test['status'], 'PASS')) {
            echo "      📝 {$test['response_preview']}\n";
        }
    }
}

// ============================================
// RECOMENDACIONES
// ============================================
echo "\n\n📌 RECOMENDACIONES:\n";
echo "==================\n";

$recommendations = [];

if ($failedTests > $passedTests) {
    $recommendations[] = "⚠️  Alta tasa de fallos detectada. Revisar configuración de base de datos.";
}

if ($totalTests > 0) {
    $avgDuration = 0;
    foreach ($testResults as $test) {
        $avgDuration += $test['duration_ms'];
    }
    $avgDuration = round($avgDuration / $totalTests, 2);
    
    if ($avgDuration > 1000) {
        $recommendations[] = "⚠️  Tiempo de respuesta promedio alto ({$avgDuration}ms). Considerar optimización.";
    } else {
        $recommendations[] = "✅ Tiempo de respuesta promedio aceptable ({$avgDuration}ms).";
    }
}

// Verificar si hay errores de conexión
$connectionErrors = 0;
foreach ($testResults as $test) {
    if (str_contains($test['message'], 'Error de conexión')) {
        $connectionErrors++;
    }
}

if ($connectionErrors > 0) {
    $recommendations[] = "❌ {$connectionErrors} errores de conexión detectados. Verificar que XAMPP esté corriendo.";
}

if (empty($recommendations)) {
    $recommendations[] = "✅ No hay recomendaciones adicionales. Las APIs están funcionando correctamente.";
}

foreach ($recommendations as $rec) {
    echo "  {$rec}\n";
}

// ============================================
// GUARDAR RESULTADOS EN JSON
// ============================================
$reportData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'summary' => [
        'total_tests' => $totalTests,
        'passed' => $passedTests,
        'failed' => $failedTests,
        'success_rate' => $successRate
    ],
    'tests' => $testResults,
    'recommendations' => $recommendations
];

file_put_contents(
    __DIR__ . '/test_results_' . date('Y-m-d_His') . '.json',
    json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo "\n\n💾 Resultados guardados en: test_results_" . date('Y-m-d_His') . ".json\n";
echo "\n✅ PRUEBAS COMPLETADAS\n\n";

// Si no es CLI, enviar también como JSON
if (!$isCliMode) {
    header('Content-Type: application/json');
    echo json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
