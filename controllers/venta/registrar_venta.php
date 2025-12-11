<?php
/**
 * Ejemplo: registrar_venta.php
 */

require_once '../../services/database/config.php';
require_once MODELS_PATH . 'venta/registrar_venta.php';
require_once MODELS_PATH . 'usuario/usuarios.php';

// Verificar autenticación
if (!Usuario::isAuthenticated()) {
    jsonResponse(false, 'No autenticado');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $ventaModel = new Venta();
    
    $ventaData = [
        'id_cliente' => (int)$_POST['id_cliente'],
        'id_usuario' => $_SESSION['user_id'],
        'id_tipo_comprobante' => (int)$_POST['id_tipo_comprobante'],
        'id_metodo_pago' => (int)$_POST['id_metodo_pago'],
        'referencia_pago' => sanitize($_POST['referencia_pago'] ?? null),
        'subtotal' => (float)$_POST['subtotal'],
        'igv' => (float)($_POST['igv'] ?? 0),
        'descuento' => (float)($_POST['descuento'] ?? 0),
        'total' => (float)$_POST['total'],
        'monto_recibido' => (float)$_POST['monto_recibido']
    ];
    
    $detalles = json_decode($_POST['detalles'], true);
    
    if (empty($detalles)) {
        jsonResponse(false, 'Debe agregar productos a la venta');
    }
    
    // Registrar venta
    $result = $ventaModel->registrarVenta($ventaData, $detalles);
    
    if ($result['success']) {
        jsonResponse(true, $result['mensaje'], [
            'id_venta' => $result['id_venta'],
            'nro_venta' => $result['nro_venta']
        ]);
    } else {
        jsonResponse(false, $result['mensaje']);
    }
}
