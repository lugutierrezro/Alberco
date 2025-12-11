<?php
/**
 * DIAGNÓSTICO SIMPLE DE LA API
 * Este archivo verificará si las APIs están funcionando correctamente
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico API</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .test { margin: 10px 0; padding: 10px; background: white; border-left: 4px solid #ccc; }
        .success { border-color: #28a745; }
        .error { border-color: #dc3545; }
        pre { background: #f8f8f8; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 Diagnóstico de APIs</h1>
    
    <?php
    // Test 1: Verificar que el archivo existe
    echo "<div class='test'>";
    echo "<h3>1. Verificar archivos de API</h3>";
    
    $apiFiles = [
        'auth/login_empleado.php',
        'pedidos/listar_por_estado.php',
        'pedidos/por_delivery.php',
        'pedidos/detalle.php',
        'pedidos/cambiar_estado.php',
        'tracking/actualizar_ubicacion.php'
    ];
    
    foreach ($apiFiles as $file) {
        $exists = file_exists($file);
        $class = $exists ? 'success' : 'error';
        $status = $exists ? '✅ Existe' : '❌ No existe';
        echo "<div class='{$class}'>{$status}: {$file}</div>";
    }
    echo "</div>";
    
    // Test 2: Verificar conexión a DB
    echo "<div class='test'>";
    echo "<h3>2. Conexión a Base de Datos</h3>";
    try {
        require_once '../services/database/config.php';
        echo "<div class='success'>✅ Conexión exitosa a BD: " . BD . "</div>";
        echo "<div>Servidor: " . SERVIDOR . "</div>";
        echo "<div>URL_BASE: " . URL_BASE . "</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
    
    // Test 3: Probar API directamente desde servidor usando cURL
    echo "<div class='test'>";
    echo "<h3>3. Test directo de API (servidor via HTTP)</h3>";
    
    try {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        
        // Auto-detectar ruta según entorno
        $isLocal = ($host === 'localhost' || $host === '127.0.0.1' || strpos($host, 'localhost') !== false);
        $basePath = $isLocal ? '/www.sistemaadmalberco.com' : '/www.sistemaadmalberco.com';
        
        $apiUrl = $protocol . $host . $basePath . '/api/pedidos/listar_por_estado.php';
        
        echo "<div>Probando URL: <code>{$apiUrl}</code></div>";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Para desarrollo con SSL autofirmado
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            echo "<div class='error'>❌ Error de cURL: {$curlError}</div>";
        } elseif ($httpCode !== 200) {
            echo "<div class='error'>❌ HTTP {$httpCode}</div>";
            echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "</pre>";
        } else {
            $json = json_decode($output, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "<div class='success'>✅ API retorna JSON válido (HTTP {$httpCode})</div>";
                echo "<pre>" . json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
            } else {
                echo "<div class='error'>❌ La salida no es JSON válido</div>";
                echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "</pre>";
            }
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error al ejecutar API: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
    
    // Test 4: URLs generadas
    echo "<div class='test'>";
    echo "<h3>4. URLs que se deben usar desde JavaScript</h3>";
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    
    // Auto-detectar si es localhost
    $isLocal = ($host === 'localhost' || $host === '127.0.0.1' || strpos($host, 'localhost') !== false);
    $basePath = '/www.sistemaadmalberco.com';
    
    echo "<div>Protocolo detectado: <strong>{$protocol}</strong></div>";
    echo "<div>Host: <strong>{$host}</strong></div>";
    echo "<div>Entorno: <strong>" . ($isLocal ? 'Desarrollo (localhost)' : 'Producción') . "</strong></div>";
    echo "<div>Base Path: <strong>{$basePath}</strong></div>";
    echo "<br>";
    echo "<div>URL completa ejemplo:</div>";
    echo "<pre>{$protocol}{$host}{$basePath}/api/pedidos/listar_por_estado.php</pre>";
    echo "</div>";
    
    ?>
    
    <div class="test">
        <h3>5. Test desde JavaScript</h3>
        <button onclick="testAPI()">🧪 Probar API desde JS</button>
        <div id="result"></div>
    </div>
    
    <script>
        async function testAPI() {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<p>⏳ Probando...</p>';
            
            // Auto-detectar entorno
            const isLocalhost = window.location.hostname === 'localhost' || 
                               window.location.hostname === '127.0.0.1';
            
            // Construir URL correcta
            const protocol = window.location.protocol;
            const host = window.location.host;
            const basePath = '/www.sistemaadmalberco.com';
            const apiUrl = `${protocol}//${host}${basePath}/api/pedidos/listar_por_estado.php`;
            
            console.log('Entorno:', isLocalhost ? 'Localhost' : 'Producción');
            console.log('Intentando llamar a:', apiUrl);
            
            try {
                const response = await fetch(apiUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                const contentType = response.headers.get('content-type');
                console.log('Content-Type:', contentType);
                
                const text = await response.text();
                console.log('Response text:', text);
                
                if (contentType && contentType.includes('application/json')) {
                    const json = JSON.parse(text);
                    resultDiv.innerHTML = `
                        <div class="success">✅ API funciona correctamente desde JavaScript</div>
                        <pre>${JSON.stringify(json, null, 2)}</pre>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="error">❌ API no retorna JSON</div>
                        <div>Content-Type recibido: ${contentType}</div>
                        <pre>${text.substring(0, 500)}</pre>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="error">❌ Error: ${error.message}</div>
                `;
                console.error('Error completo:', error);
            }
        }
    </script>
</body>
</html>
