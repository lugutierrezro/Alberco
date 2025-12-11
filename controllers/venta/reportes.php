<?php
/**
 * Reportes de Ventas
 */

require_once '../../services/database/config.php';
require_once __DIR__ . '/../../models/venta.php';

// Verificar sesión
if (!isset($_SESSION['sesion']) || $_SESSION['sesion'] !== 'ok') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

try {
    $ventaModel = new Venta();
    
    $tipoReporte = $_GET['tipo'] ?? 'resumen_dia';
    $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
    $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
    
    $data = [];
    
    switch ($tipoReporte) {
        case 'resumen_dia':
            $data = $ventaModel->getResumenDelDia($fechaInicio);
            break;
            
        case 'ventas_periodo':
            $data = $ventaModel->reporteVentasPeriodo($fechaInicio, $fechaFin);
            break;
            
        case 'productos_mas_vendidos':
            $limite = (int)($_GET['limite'] ?? 10);
            $data = $ventaModel->productosMasVendidos($fechaInicio, $fechaFin, $limite);
            break;
            
        case 'utilidad':
            $data = $ventaModel->calcularUtilidad($fechaInicio, $fechaFin);
            break;
            
        case 'metodos_pago':
            $data = $ventaModel->ventasPorMetodoPago($fechaInicio, $fechaFin);
            break;
            
        case 'tipo_comprobante':
            $data = $ventaModel->ventasPorTipoComprobante($fechaInicio, $fechaFin);
            break;
            
        case 'top_clientes':
            $limite = (int)($_GET['limite'] ?? 10);
            $data = $ventaModel->topClientes($fechaInicio, $fechaFin, $limite);
            break;
        
        case 'kpis_dashboard':
            $data = $ventaModel->getKPIsDashboard($fechaInicio, $fechaFin);
            break;
        
        case 'tipo_pedido':
            $data = $ventaModel->ventasPorTipoPedido($fechaInicio, $fechaFin);
            break;
        
        case 'ventas_hora':
            $data = $ventaModel->ventasPorHora($fechaInicio, $fechaFin);
            break;
        
        case 'stock_bajo':
            $limite = (int)($_GET['limite'] ?? 20);
            $data = $ventaModel->getStockBajo($limite);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tipo de reporte no válido']);
            exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Reporte generado correctamente',
        'data' => $data
    ]);
    
} catch (Exception $e) {
    error_log("Error al generar reporte: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al generar reporte']);
}
