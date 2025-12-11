 <?php
header('Content-Type: application/json');
require_once '/../../app/config.php'; // archivo donde se conecta a la BD
require_once '/../modelo/PedidoModelo.php';
require_once '/../modelo/SeguimientoModel.php';
require_once '/../controller/Producto/PedisdoController.php';

$db = Database::connect();

$pedidoModel = new PedidoModel($db);
$seguimientoModel = new SeguimientoModel($db);
$pedidoController = new PedidoController($pedidoModel, $seguimientoModel);

if (empty($_GET['pedidoId'])) {
    echo json_encode(['success' => false, 'message' => 'ID de pedido no proporcionado']);
    exit;
}

$idPedido = intval($_GET['pedidoId']);

$pedido = $pedidoModel->obtenerPedido($idPedido);
if (!$pedido) {
    echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
    exit;
}

$seguimiento = $seguimientoModel->obtenerSeguimiento($idPedido);

echo json_encode([
    'success' => true,
    'pedido' => $pedido,
    'seguimiento' => $seguimiento
]);
