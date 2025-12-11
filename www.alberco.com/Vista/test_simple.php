<?php
/**
 * TEST SIMPLE - Ver si el archivo responde
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

echo json_encode([
    'success' => true,
    'mensaje' => 'El archivo PHP está funcionando correctamente',
    'timestamp' => date('Y-m-d H:i:s'),
    'metodo' => $_SERVER['REQUEST_METHOD']
]);
