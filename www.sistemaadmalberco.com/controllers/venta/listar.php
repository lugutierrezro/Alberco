<?php
/**
 * Listar Ventas
 */

require_once '../../services/database/config.php';
require_once MODELS_PATH . 'venta/venta.php';
require_once MODELS_PATH . 'usuario/usuario.php';

if (!Usuario::isAuthenticated()) {
    jsonResponse(false, 'No autenticado');
}

try {
    $ventaModel = new Venta();
    
    // Filtros opcionales
    $filters = [];
    
    if (!empty($_GET['fecha_inicio'])) {
        $filters['fecha_inicio'] = $_GET['fecha_inicio'];
    }
    
    if (!empty($_GET['fecha_fin'])) {
        $filters['fecha_fin'] = $_GET['fecha_fin'];
    }
    
    if (!empty($_GET['id_cliente'])) {
        $filters['id_cliente'] = (int)$_GET['id_cliente'];
    }
    
    if (!empty($_GET['estado_venta'])) {
        $filters['estado_venta'] = $_GET['estado_venta'];
    }
    
    if (!empty($_GET['limit'])) {
        $filters['limit'] = (int)$_GET['limit'];
    }
    
    $ventas = $ventaModel->getVentasWithDetails($filters);
    
    jsonResponse(true, 'Ventas obtenidas correctamente', [
        'ventas' => $ventas,
        'total' => count($ventas)
    ]);
    
} catch (Exception $e) {
    error_log("Error al listar ventas: " . $e->getMessage());
    jsonResponse(false, 'Error al obtener ventas');
}
