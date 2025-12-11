<?php
header('Content-Type: application/json');
require_once '/../../app/config.php'; // archivo donde se conecta a la BD
require_once '/../modelo/PedidoModelo.php';
require_once '/../modelo/SeguimientoModel.php';
require_once '/../controller/Producto/PedisdoController.php';

// Conexión a BD
$db = Database::connect();

// Instanciar modelos y controlador
$pedidoModel = new PedidoModel($db);
$seguimientoModel = new SeguimientoModel($db);
$pedidoController = new PedidoController($pedidoModel, $seguimientoModel);

// Leer datos JSON del POST
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['direccion']) || empty($data['productos']) || !is_array($data['productos'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Parámetros a pasar (en un caso real se obtiene el id_usuario de sesión o token)
$clienteId = 1; // Simulación cliente fijo o recuperación real
$usuarioId = 1; // Usuario administrador o repartidor asignado fijo aquí para ejemplo
$direccion = $data['direccion'];
$total = $data['total'];
$productos = $data['productos'];

// Crear pedido y responder
try {
    $pedidoId = $pedidoController->crearPedido($clienteId, $usuarioId, $direccion, $total, $productos);
    echo json_encode(['success' => true, 'pedidoId' => $pedidoId]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
}
