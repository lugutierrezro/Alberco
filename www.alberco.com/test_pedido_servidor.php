&lt;?php
/**
 * TEST DIRECTO - Verificar que procesar_pedido_directo.php funciona
 */

echo "&lt;h1>Test de Pedido - Alberco&lt;/h1>";

// Datos de prueba
$pedidoPrueba = [
    'tipo_pedido' => 'delivery',
    'cliente' => [
        'nombre' => 'Cliente de Prueba',
        'telefono' => '999888777',
        'direccion' => 'Av. Test 123',
        'mesa' => null,
        'email' => null
    ],
    'productos' => [
        [
            'id' => 1,  // Asegúrate de que este ID exista en tu BD
            'nombre' => 'Producto Test',
            'precio' => 25.00,
            'cantidad' => 2,
            'observaciones' => null
        ]
    ],
    'metodo_pago' => 'efectivo',
    'observaciones' => 'Pedido de prueba desde test',
    'subtotal' => 50.00,
    'descuento' => 0,
    'costo_delivery' => 5.00,
    'total' => 55.00
];

echo "&lt;h2>Datos a enviar:&lt;/h2>";
echo "&lt;pre>" . json_encode($pedidoPrueba, JSON_PRETTY_PRINT) . "&lt;/pre>";

// Hacer request
$url = 'http://localhost/www.alberco.com/Vista/procesar_pedido_directo.php';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pedidoPrueba));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "&lt;h2>Respuesta del servidor:&lt;/h2>";
echo "&lt;p>&lt;strong>HTTP Code:&lt;/strong> " . $httpCode . "&lt;/p>";

if ($error) {
    echo "&lt;p style='color: red;'>&lt;strong>Error CURL:&lt;/strong> " . $error . "&lt;/p>";
}

echo "&lt;h3>Respuesta RAW:&lt;/h3>";
echo "&lt;pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ddd;'>";
echo htmlspecialchars($response);
echo "&lt;/pre>";

echo "&lt;h3>Respuesta JSON Parseada:&lt;/h3>";
$data = json_decode($response, true);
if ($data) {
    echo "&lt;pre style='background: " . ($data['success'] ? '#d4edda' : '#f8d7da') . "; padding: 10px; border: 1px solid #ddd;'>";
    print_r($data);
    echo "&lt;/pre>";
    
    if ($data['success']) {
        echo "&lt;h2 style='color: green;'>✅ ÉXITO!&lt;/h2>";
        echo "&lt;p>Pedido creado: &lt;strong>" . ($data['nro_pedido'] ?? 'N/A') . "&lt;/strong>&lt;/p>";
    } else {
        echo "&lt;h2 style='color: red;'>❌ ERROR&lt;/h2>";
        echo "&lt;p>" . ($data['error'] ?? $data['mensaje'] ?? 'Error desconocido') . "&lt;/p>";
    }
} else {
    echo "&lt;p style='color: red;'>&lt;strong>No se pudo parsear JSON:&lt;/strong> " . json_last_error_msg() . "&lt;/p>";
}

echo "&lt;hr>";
echo "&lt;h2>Verificar Logs:&lt;/h2>";
echo "&lt;p>&lt;a href='debug_pedidos.php' target='_blank'>Ver logs en debug_pedidos.php&lt;/a>&lt;/p>";

echo "&lt;hr>";
echo "&lt;h2>Verificar en Base de Datos:&lt;/h2>";
echo "&lt;pre>SELECT * FROM tb_pedidos ORDER BY id_pedido DESC LIMIT 1;&lt;/pre>";
?>
