<?php
require_once '../../database';

header('Content-Type: application/json');

$API_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6ImpvcnkxMDAtemlvQGhvdG1haWwuY29tIn0.bDF-KMUzEFMFgb6YX6ew9YF44JsAQQUKOWYQXljud4I';

$tipo_documento = $_POST['tipo_documento'] ?? '';
$numero_documento = $_POST['numero_documento'] ?? '';

if (empty($tipo_documento) || empty($numero_documento)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    if ($tipo_documento === 'DNI') {
        
        $url = "https://dniruc.apisperu.com/api/v1/dni/" . $numero_documento . "?token=" . $API_TOKEN;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($http_code == 200) {
            $data = json_decode($response, true);
            
            // Verificar si la respuesta es exitosa
            if (isset($data['nombres']) || isset($data['success'])) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'nombres' => $data['nombres'] ?? '',
                        'apellido_paterno' => $data['apellidoPaterno'] ?? '',
                        'apellido_materno' => $data['apellidoMaterno'] ?? '',
                        'dni' => $data['dni'] ?? $numero_documento
                    ],
                    'message' => 'Datos obtenidos de RENIEC exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'DNI no encontrado en RENIEC',
                    'debug' => $data
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al consultar DNI. Código HTTP: ' . $http_code,
                'error' => $curl_error
            ]);
        }
        
    } elseif ($tipo_documento === 'RUC') {
        // Consulta RUC con APIsPeru
        $url = "https://dniruc.apisperu.com/api/v1/ruc/" . $numero_documento . "?token=" . $API_TOKEN;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($http_code == 200) {
            $data = json_decode($response, true);
            
            // Verificar si la respuesta es exitosa
            if (isset($data['razonSocial']) || isset($data['nombre'])) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'razon_social' => $data['razonSocial'] ?? $data['nombre'] ?? '',
                        'direccion' => $data['direccion'] ?? '',
                        'estado' => $data['estado'] ?? '',
                        'condicion' => $data['condicion'] ?? '',
                        'ruc' => $data['ruc'] ?? $numero_documento,
                        'ubigeo' => $data['ubigeo'] ?? '',
                        'departamento' => $data['departamento'] ?? '',
                        'provincia' => $data['provincia'] ?? '',
                        'distrito' => $data['distrito'] ?? ''
                    ],
                    'message' => 'Datos obtenidos de SUNAT exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'RUC no encontrado en SUNAT',
                    'debug' => $data
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al consultar RUC. Código HTTP: ' . $http_code,
                'error' => $curl_error
            ]);
        }
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Tipo de documento no soportado para consulta API'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}
?>
