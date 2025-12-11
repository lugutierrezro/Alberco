<?php
/**
 * PROXY GEOCODING - Alberco
 * Soluciona el problema de CORS con Nominatim API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Validar parámetros
$lat = $_GET['lat'] ?? '';
$lon = $_GET['lon'] ?? '';

if (!$lat || !$lon) {
    echo json_encode([
        'error' => 'Parámetros faltantes',
        'message' => 'Se requieren lat y lon'
    ]);
    exit;
}

// Validar que sean números
if (!is_numeric($lat) || !is_numeric($lon)) {
    echo json_encode([
        'error' => 'Parámetros inválidos',
        'message' => 'lat y lon deben ser números'
    ]);
    exit;
}

// Construir URL de Nominatim
$url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=$lat&lon=$lon&accept-language=es";

// Inicializar cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'AlbercoDeliverySystem/1.0 (contacto@alberco.com)');
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Ejecutar petición
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Verificar errores
if ($error) {
    echo json_encode([
        'error' => 'Error de conexión',
        'message' => $error
    ]);
    exit;
}

if ($httpCode !== 200) {
    echo json_encode([
        'error' => 'Error del servidor',
        'message' => "HTTP $httpCode",
        'response' => $response
    ]);
    exit;
}

// Retornar respuesta
echo $response;
?>
