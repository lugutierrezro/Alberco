<?php
/**
 * Crear Venta
 */

require_once '../../services/database/config.php';
require_once MODELS_PATH . 'venta/venta.php';
require_once MODELS_PATH . 'usuario/usuario.php';

if (!Usuario::isAuthenticated()) {
    jsonResponse(false, 'No autenticado');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método no permitido');
}

try {
    $ventaModel = new Venta();
    
    // Datos de la venta
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
        'monto_recibido' => (float)$_POST['monto_recibido'],
        'observaciones' => sanitize($_POST['observaciones'] ?? null)
    ];
    
    // Detalles de productos (JSON string)
    $detalles = json_decode($_POST['detalles'], true);
    
    // Validaciones
    if (empty($detalles)) {
        jsonResponse(false, 'Debe agregar productos a la venta');
    }
    
    if ($ventaData['total'] <= 0) {
        jsonResponse(false, 'El total de la venta debe ser mayor a cero');
    }
    
    if ($ventaData['monto_recibido'] < $ventaData['total']) {
        jsonResponse(false, 'El monto recibido es menor al total de la venta');
    }
    
    // Registrar venta
    $result = $ventaModel->registrarVenta($ventaData, $detalles);
    
    if ($result['success']) {
        jsonResponse(true, $result['mensaje'], [
            'id_venta' => $result['id_venta'],
            'nro_venta' => $result['nro_venta'],
            'vuelto' => $ventaData['monto_recibido'] - $ventaData['total']
        ]);
    } else {
        jsonResponse(false, $result['mensaje']);
    }
    
} catch (Exception $e) {
    error_log("Error al crear venta: " . $e->getMessage());
    jsonResponse(false, 'Error al procesar la venta');
}
