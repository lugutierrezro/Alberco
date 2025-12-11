<?php
// Test simple - ver qué devuelve el servidor
$url = 'http://localhost/www.alberco.com/Vista/procesar_pedido_directo.php';

$pedidoTest = [
    'tipo_pedido' => 'delivery',
    'cliente' => [
        'nombre' => 'Test',
        'telefono' => '999999999',
        'direccion' => 'Test 123',
        'mesa' => null,
        'email' => null
    ],
    'productos' => [
        [
            'id' => 1,
            'nombre' => 'Test',
            'precio' => 10.00,
            'cantidad' => 1,
            'observaciones' => null
        ]
    ],
    'metodo_pago' => 'efectivo',
    'observaciones' => '',
    'subtotal' => 10.00,
    'descuento' => 0,
    'costo_delivery' => 5.00,
    'total' => 15.00
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pedidoTest));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h1>Prueba de procesar_pedido_directo.php</h1>";
echo "<h2>HTTP Code: $httpCode</h2>";
echo "<h2>Respuesta RAW:</h2>";
echo "<pre style='background:#f4f4f4;padding:10px;border:1px solid #ddd;'>";
echo htmlspecialchars($response);
echo "</pre>";

echo "<h2>Primeros 500 caracteres:</h2>";
echo "<pre>";
echo htmlspecialchars(substr($response, 0, 500));
echo "</pre>";

// Intentar parsear
echo "<h2>Intentando parsear JSON:</h2>";
$data = json_decode($response, true);
if ($data) {
    echo "<pre style='background:#d4edda;padding:10px;'>";
    print_r($data);
    echo "</pre>";
} else {
    echo "<p style='color:red'>ERROR: " . json_last_error_msg() . "</p>";
    echo "<p>La respuesta no es JSON válido. Probablemente hay un error de PHP.</p>";
}
?>
