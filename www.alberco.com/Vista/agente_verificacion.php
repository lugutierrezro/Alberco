<?php
/**
 * AGENTE DE VERIFICACIÓN AUTOMÁTICA
 * Sistema de Seguimiento de Pedidos - Alberco
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/init.php';

$results = [];
$overallStatus = 'success';

// ============================================
// TEST 1: Conexión a Base de Datos
// ============================================
$results['database'] = [
    'name' => 'Conexión a Base de Datos',
    'status' => 'pending',
    'message' => '',
    'details' => []
];

try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_pedidos WHERE estado_registro = 'ACTIVO'");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $results['database']['status'] = 'success';
        $results['database']['message'] = "Conexión exitosa. {$count['total']} pedidos activos encontrados.";
        $results['database']['details'][] = "PDO Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    } else {
        throw new Exception('Variable $pdo no está definida');
    }
} catch (Exception $e) {
    $results['database']['status'] = 'error';
    $results['database']['message'] = $e->getMessage();
    $overallStatus = 'error';
}

// ============================================
// TEST 2: Verificar Pedidos con Coordenadas
// ============================================
$results['coordinates'] = [
    'name' => 'Pedidos con Coordenadas GPS',
    'status' => 'pending',
    'message' => '',
    'details' => []
];

try {
    $sql = "SELECT 
                nro_pedido, 
                numero_comanda,
                latitud_entrega, 
                longitud_entrega,
                direccion_entrega,
                fecha_pedido
            FROM tb_pedidos 
            WHERE estado_registro = 'ACTIVO' 
            AND latitud_entrega IS NOT NULL 
            AND longitud_entrega IS NOT NULL
            ORDER BY fecha_pedido DESC 
            LIMIT 5";
    
    $stmt = $pdo->query($sql);
    $pedidosConCoords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($pedidosConCoords) > 0) {
        $results['coordinates']['status'] = 'success';
        $results['coordinates']['message'] = count($pedidosConCoords) . " pedidos con coordenadas encontrados";
        foreach ($pedidosConCoords as $p) {
            $results['coordinates']['details'][] = sprintf(
                "%s - Lat: %s, Lng: %s",
                $p['nro_pedido'] ?? $p['numero_comanda'],
                $p['latitud_entrega'],
                $p['longitud_entrega']
            );
        }
    } else {
        $results['coordinates']['status'] = 'warning';
        $results['coordinates']['message'] = "No hay pedidos con coordenadas GPS guardadas";
        $results['coordinates']['details'][] = "Esto es normal si no se han registrado pedidos con ubicación";
    }
} catch (Exception $e) {
    $results['coordinates']['status'] = 'error';
    $results['coordinates']['message'] = $e->getMessage();
    $overallStatus = 'error';
}

// ============================================
// TEST 3: API de Seguimiento
// ============================================
$results['api'] = [
    'name' => 'API de Seguimiento (seguimiento_api.php)',
    'status' => 'pending',
    'message' => '',
    'details' => []
];

try {
    // Obtener un pedido de ejemplo
    $stmt = $pdo->query("SELECT nro_pedido, numero_comanda FROM tb_pedidos WHERE estado_registro = 'ACTIVO' ORDER BY fecha_pedido DESC LIMIT 1");
    $pedidoEjemplo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pedidoEjemplo) {
        $nroPedido = $pedidoEjemplo['nro_pedido'] ?? $pedidoEjemplo['numero_comanda'];
        
        // Simular llamada a la API
        $apiUrl = "http://localhost/www.alberco.com/Vista/seguimiento_api.php?nroPedido=" . urlencode($nroPedido);
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if ($data && isset($data['success'])) {
                $results['api']['status'] = 'success';
                $results['api']['message'] = "API responde correctamente";
                $results['api']['details'][] = "Pedido de prueba: $nroPedido";
                $results['api']['details'][] = "Success: " . ($data['success'] ? 'true' : 'false');
                if (isset($data['pedido'])) {
                    $results['api']['details'][] = "Datos del pedido: OK";
                }
            } else {
                $results['api']['status'] = 'warning';
                $results['api']['message'] = "API responde pero formato JSON incorrecto";
            }
        } else {
            $results['api']['status'] = 'error';
            $results['api']['message'] = "HTTP Code: $httpCode";
            $overallStatus = 'error';
        }
    } else {
        $results['api']['status'] = 'warning';
        $results['api']['message'] = "No hay pedidos para probar la API";
    }
} catch (Exception $e) {
    $results['api']['status'] = 'error';
    $results['api']['message'] = $e->getMessage();
    $overallStatus = 'error';
}

// ============================================
// TEST 4: Archivos del Sistema
// ============================================
$results['files'] = [
    'name' => 'Archivos del Sistema',
    'status' => 'pending',
    'message' => '',
    'details' => []
];

$requiredFiles = [
    'seguimiento_pedido.php' => __DIR__ . '/seguimiento_pedido.php',
    'seguimiento_api.php' => __DIR__ . '/seguimiento_api.php',
    'test_mapa_simple.html' => __DIR__ . '/test_mapa_simple.html'
];

$missingFiles = [];
foreach ($requiredFiles as $name => $path) {
    if (file_exists($path)) {
        $results['files']['details'][] = "✓ $name (" . number_format(filesize($path)) . " bytes)";
    } else {
        $missingFiles[] = $name;
        $results['files']['details'][] = "✗ $name - NO ENCONTRADO";
    }
}

if (empty($missingFiles)) {
    $results['files']['status'] = 'success';
    $results['files']['message'] = "Todos los archivos necesarios están presentes";
} else {
    $results['files']['status'] = 'error';
    $results['files']['message'] = count($missingFiles) . " archivo(s) faltante(s)";
    $overallStatus = 'error';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agente de Verificación - Sistema de Seguimiento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; padding: 20px; }
        .test-card { margin-bottom: 20px; }
        .status-success { color: #28a745; }
        .status-warning { color: #ffc107; }
        .status-error { color: #dc3545; }
        .status-pending { color: #6c757d; }
        .detail-item { padding: 5px 0; border-bottom: 1px solid #eee; }
        .detail-item:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-lg mb-4">
                    <div class="card-header bg-dark text-white">
                        <h2 class="mb-0">
                            <i class="fas fa-robot"></i> Agente de Verificación Automática
                        </h2>
                        <small>Sistema de Seguimiento de Pedidos - Alberco</small>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-<?= $overallStatus === 'success' ? 'success' : 'danger' ?>">
                            <h4>
                                <?php if ($overallStatus === 'success'): ?>
                                    <i class="fas fa-check-circle"></i> Sistema Operativo
                                <?php else: ?>
                                    <i class="fas fa-exclamation-triangle"></i> Problemas Detectados
                                <?php endif; ?>
                            </h4>
                            <p class="mb-0">Fecha de verificación: <?= date('d/m/Y H:i:s') ?></p>
                        </div>
                    </div>
                </div>

                <?php foreach ($results as $key => $test): ?>
                <div class="card test-card shadow">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <?php
                            $icon = 'fa-circle';
                            if ($test['status'] === 'success') $icon = 'fa-check-circle';
                            if ($test['status'] === 'error') $icon = 'fa-times-circle';
                            if ($test['status'] === 'warning') $icon = 'fa-exclamation-triangle';
                            ?>
                            <i class="fas <?= $icon ?> status-<?= $test['status'] ?>"></i>
                            <?= $test['name'] ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Estado:</strong> 
                            <span class="status-<?= $test['status'] ?>">
                                <?= strtoupper($test['status']) ?>
                            </span>
                        </p>
                        <p class="mb-3"><strong>Mensaje:</strong> <?= htmlspecialchars($test['message']) ?></p>
                        
                        <?php if (!empty($test['details'])): ?>
                        <div class="mt-3">
                            <strong>Detalles:</strong>
                            <div class="mt-2 p-3 bg-light rounded">
                                <?php foreach ($test['details'] as $detail): ?>
                                <div class="detail-item">
                                    <small><code><?= htmlspecialchars($detail) ?></code></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-tools"></i> Acciones Recomendadas</h5>
                    </div>
                    <div class="card-body">
                        <ol>
                            <li class="mb-2">
                                <strong>Probar el mapa simple:</strong><br>
                                <a href="test_mapa_simple.html" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-external-link-alt"></i> Abrir Test de Mapa
                                </a>
                            </li>
                            <li class="mb-2">
                                <strong>Probar seguimiento de pedido:</strong><br>
                                <a href="seguimiento_pedido.php" target="_blank" class="btn btn-sm btn-success">
                                    <i class="fas fa-map-marked-alt"></i> Abrir Seguimiento
                                </a>
                            </li>
                            <li class="mb-2">
                                <strong>Ver consola del navegador:</strong><br>
                                Presiona <kbd>F12</kbd> en el navegador y busca errores en la pestaña "Console"
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
