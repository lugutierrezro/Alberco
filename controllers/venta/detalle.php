<?php
/**
 * Detalle de Venta
 */

require_once '../../services/database/config.php';
require_once MODELS_PATH . 'venta/venta.php';
require_once MODELS_PATH . 'usuario/usuario.php';

if (!Usuario::isAuthenticated()) {
    jsonResponse(false, 'No autenticado');
}

try {
    $ventaModel = new Venta();
    
    $ventaId = (int)$_GET['id_venta'];
    
    if (empty($ventaId)) {
        jsonResponse(false, 'ID de venta requerido');
    }
    
    $venta = $ventaModel->getVentaCompleta($ventaId);
    
    if ($venta) {
        jsonResponse(true, 'Venta obtenida correctamente', [
            'venta' => $venta
        ]);
    } else {
        jsonResponse(false, 'Venta no encontrada');
    }
    
} catch (Exception $e) {
    error_log("Error al obtener detalle de venta: " . $e->getMessage());
    jsonResponse(false, 'Error al obtener detalle');
}
