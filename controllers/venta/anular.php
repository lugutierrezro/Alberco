<?php
/**
 * Anular Venta
 */
require_once '../../services/database/config.php';
require_once MODELS_PATH . 'venta/venta.php';
require_once MODELS_PATH . 'usuario/usuario.php';

if (!Usuario::isAuthenticated()) {
    jsonResponse(false, 'No autenticado');
}

// Solo administradores y cajeros pueden anular ventas
if (!in_array($_SESSION['user_role'], ['ADMINISTRADOR', 'CAJERO'])) {
    jsonResponse(false, 'No tiene permisos para anular ventas');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método no permitido');
}

try {
    $ventaModel = new Venta();
    
    $ventaId = (int)$_POST['id_venta'];
    $motivo = sanitize($_POST['motivo']);
    
    if (empty($motivo)) {
        jsonResponse(false, 'Debe indicar el motivo de anulación');
    }
    
    if ($ventaModel->anularVenta($ventaId, $_SESSION['user_id'], $motivo)) {
        jsonResponse(true, 'Venta anulada correctamente');
    } else {
        jsonResponse(false, 'Error al anular la venta');
    }
    
} catch (Exception $e) {
    error_log("Error al anular venta: " . $e->getMessage());
    jsonResponse(false, 'Error al procesar la solicitud');
}
