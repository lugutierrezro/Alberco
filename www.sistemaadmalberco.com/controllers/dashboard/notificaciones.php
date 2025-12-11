<?php
// Notificaciones Controller - CORREGIDO
require_once(__DIR__ . '/../../services/database/config.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDB();
    $notificaciones = [];
    
    // ========== 1. PEDIDOS PENDIENTES ==========
    // Estados 1=Pendiente, 2=En Preparación, 3=Listo
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM tb_pedidos 
        WHERE id_estado IN (1, 2)
          AND estado_registro = 'ACTIVO'
    ");
    $pendientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($pendientes > 0) {
        $notificaciones[] = [
            'tipo' => 'pedido',
            'titulo' => "$pendientes pedido(s) pendiente(s)",
            'icono' => 'fa-clipboard-list',
            'url' => URL_BASE . '/views/pedidos/',
            'tiempo' => 'Ahora',
            'color' => 'warning'
        ];
    }
    
    // ========== 2. STOCK BAJO ==========
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM tb_almacen 
        WHERE stock <= stock_minimo 
            AND estado_registro = 'ACTIVO'
    ");
    $stock_bajo = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($stock_bajo > 0) {
        $notificaciones[] = [
            'tipo' => 'stock',
            'titulo' => "$stock_bajo producto(s) con stock bajo",
            'icono' => 'fa-exclamation-triangle',
            'url' => URL_BASE . '/views/almacen/',
            'tiempo' => 'Hoy',
            'color' => 'danger'
        ];
    }
    
    // ========== 3. CAJA ABIERTA DESDE DÍA ANTERIOR ==========
    $stmt = $pdo->query("
        SELECT id_arqueo, fecha_arqueo
        FROM tb_arqueo_caja 
        WHERE estado = 'abierto' 
            AND DATE(fecha_arqueo) < CURDATE()
        LIMIT 1
    ");
    $caja_antigua = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($caja_antigua) {
        $notificaciones[] = [
            'tipo' => 'caja',
            'titulo' => 'Caja abierta desde día anterior',
            'icono' => 'fa-cash-register',
            'url' => URL_BASE . '/views/caja/',
            'tiempo' => 'Importante',
            'color' => 'danger'
        ];
    }
    
    // ========== 4. VENTAS DE HOY ==========
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM tb_ventas 
        WHERE DATE(fecha_venta) = CURDATE()
    ");
    $ventas_hoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($ventas_hoy > 0) {
        $notificaciones[] = [
            'tipo' => 'info',
            'titulo' => "$ventas_hoy venta(s) realizadas hoy",
            'icono' => 'fa-shopping-cart',
            'url' => URL_BASE . '/views/venta/',
            'tiempo' => 'Hoy',
            'color' => 'success'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'total' => count($notificaciones),
        'notificaciones' => $notificaciones
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_UNESCAPED_UNICODE);
}
