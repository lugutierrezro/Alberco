<?php
/**
 * Servicio de Verificación de Stock
 * Sistema Alberco
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../app/init.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['productos'])) {
        throw new Exception('Datos inválidos');
    }
    
    $productoModel = new Producto();
    $resultados = [];
    $todosDisponibles = true;
    
    foreach ($input['productos'] as $item) {
        $idProducto = $item['id'];
        $cantidadSolicitada = $item['cantidad'];
        
        // Obtener producto
        $producto = $productoModel->getById($idProducto);
        
        if (!$producto) {
            $resultados[] = [
                'id' => $idProducto,
                'disponible' => false,
                'mensaje' => 'Producto no encontrado',
                'stock_actual' => 0
            ];
            $todosDisponibles = false;
            continue;
        }
        
        // Verificar stock
        $stockActual = (int)$producto['stock'];
        $disponible = $stockActual >= $cantidadSolicitada;
        
        if (!$disponible) {
            $todosDisponibles = false;
        }
        
        $resultados[] = [
            'id' => $idProducto,
            'nombre' => $producto['nombre'],
            'disponible' => $disponible,
            'stock_actual' => $stockActual,
            'cantidad_solicitada' => $cantidadSolicitada,
            'mensaje' => $disponible 
                ? 'Disponible' 
                : "Solo hay $stockActual unidades disponibles"
        ];
    }
    
    echo json_encode([
        'success' => true,
        'todos_disponibles' => $todosDisponibles,
        'productos' => $resultados
    ]);
    
} catch (Exception $e) {
    error_log("Error en verificar_stock.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
