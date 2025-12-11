<?php
// Log de inicio para debug
error_log("=== INICIO procesar_pedido.php ===");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'no definido'));
error_log("__DIR__: " . __DIR__);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include centralized init
$initPath = __DIR__ . '/../app/init.php';
error_log("Intentando cargar init.php desde: " . $initPath);

if (!file_exists($initPath)) {
    error_log("ERROR: init.php NO EXISTE en la ruta: " . $initPath);
    echo json_encode([
        'success' => false,
        'error' => 'Error de configuración del servidor',
        'mensaje' => 'No se pudo cargar la configuración (init.php no encontrado)',
        'debug_path' => $initPath
    ]);
    exit;
}

require_once $initPath;
error_log("init.php cargado correctamente");

// Verificar conexión a base de datos
try {
    $pdo = getDB();
    if (!$pdo) {
        throw new Exception("No se pudo obtener conexión a BD");
    }
    error_log("Conexión a BD verificada: OK");
} catch (Exception $e) {
    error_log("ERROR DE CONEXIÓN A BD: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error de conexión a base de datos',
        'mensaje' => 'No se pudo conectar a la base de datos',
        'detalle' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Contacte al administrador'
    ]);
    exit;
}

try {
    // Get JSON input
    $rawInput = file_get_contents('php://input');
    error_log("Raw input length: " . strlen($rawInput));
    
    $input = json_decode($rawInput, true);
    
    if (!$input) {
        $jsonError = json_last_error_msg();
        error_log("ERROR: JSON inválido - " . $jsonError);
        error_log("Raw input: " . substr($rawInput, 0, 500)); // Primeros 500 caracteres
        throw new Exception('Datos inválidos o JSON mal formado: ' . $jsonError);
    }
    
    error_log("JSON decodificado correctamente");
    error_log("Campos recibidos: " . implode(', ', array_keys($input)));
    
    // Validate required fields
    if (!isset($input['cliente']) || !isset($input['productos']) || !isset($input['tipo_pedido'])) {
        error_log("ERROR: Faltan campos requeridos");
        error_log("Cliente: " . (isset($input['cliente']) ? 'OK' : 'FALTA'));
        error_log("Productos: " . (isset($input['productos']) ? 'OK' : 'FALTA'));
        error_log("Tipo pedido: " . (isset($input['tipo_pedido']) ? 'OK' : 'FALTA'));
        throw new Exception('Faltan campos requeridos');
    }

    // Verificar que haya productos
    if (empty($input['productos'])) {
        error_log("ERROR: Array de productos vacío");
        throw new Exception('El pedido debe tener al menos un producto');
    }
    
    error_log("Pedido con " . count($input['productos']) . " producto(s)");
    error_log("Tipo de pedido: " . $input['tipo_pedido']);

    $pedidoModel = new Pedido();
    $clienteModel = new Cliente();
    $productoModel = new Producto();
    
    // Client data
    $clienteData = $input['cliente'];
    $nombre = $clienteData['nombre'];
    $telefono = $clienteData['telefono'];
    $direccion = $clienteData['direccion'];

    // Validar teléfono
    if (empty($telefono) || !preg_match('/^[0-9]{9}$/', $telefono)) {
        throw new Exception('Teléfono inválido');
    }

    // PASO 1: Verificar stock de todos los productos
    foreach ($input['productos'] as $prod) {
        $producto = $productoModel->getById($prod['id']);
        
        if (!$producto) {
            throw new Exception("Producto no encontrado: " . $prod['nombre']);
        }
        
        if ($producto['stock'] < $prod['cantidad']) {
            throw new Exception("Stock insuficiente para: " . $producto['nombre'] . 
                              " (Disponible: " . $producto['stock'] . ", Solicitado: " . $prod['cantidad'] . ")");
        }
    }

    // PASO 2: Find or create client
    $cliente = $clienteModel->getByTelefono($telefono);
    if ($cliente) {
        $clienteId = $cliente['id_cliente'];
        
        // Actualizar dirección si se proporciona y es diferente
        if (!empty($direccion) && $direccion !== $cliente['direccion']) {
            $clienteModel->update($clienteId, ['direccion' => $direccion]);
        }
    } else {
        $clienteId = $clienteModel->create([
            'nombre' => $nombre,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'tipo_cliente' => 'NUEVO',
            'estado_registro' => 'ACTIVO',
            'fyh_creacion' => date('Y-m-d H:i:s')
        ]);
        
        if (!$clienteId) {
            throw new Exception('Error al registrar cliente');
        }
    }

    // Get a user (Default Admin ID 1)
    $usuarioId = 1;

    // PASO 3: Prepare details
    $detalles = [];
    foreach ($input['productos'] as $prod) {
        $detalles[] = [
            'id_producto' => $prod['id'],
            'cantidad' => $prod['cantidad'],
            'precio_unitario' => $prod['precio'],
            'observaciones' => $prod['observaciones'] ?? ''
        ];
    }

    // PASO 4: Create order
    $pedidoData = [
        'tipo_pedido' => $input['tipo_pedido'],
        'id_mesa' => ($input['tipo_pedido'] == 'mesa') ? $input['cliente']['mesa'] : null,
        'id_cliente' => $clienteId,
        'id_usuario' => $usuarioId,
        'direccion_entrega' => $direccion,
        'latitud' => null,
        'longitud' => null,
        'observaciones' => $input['observaciones']
    ];
    
    error_log("=== CREANDO PEDIDO ===");
    error_log("Datos del pedido: " . json_encode($pedidoData));
    error_log("Detalles (productos): " . json_encode($detalles));

    $resultado = $pedidoModel->crearPedido($pedidoData, $detalles);
    
    error_log("Resultado de crearPedido: " . json_encode($resultado));

    if ($resultado['success']) {
        // PASO 5: Actualizar datos de compra del cliente y puntos
        $totalCompra = $input['total'];
        $puntosGanados = floor($totalCompra / 10); // 1 punto por cada S/10
        
        error_log("Actualizando datos del cliente ID: " . $clienteId);
        
        // Actualizar total de compras y puntos
        $clienteModel->actualizarDatosCompra($clienteId, $totalCompra);
        
        // Actualizar tipo de cliente según total acumulado
        $clienteModel->actualizarTipoCliente($clienteId);
        
        // Obtener datos actualizados del cliente
        $clienteActualizado = $clienteModel->getById($clienteId);
        
        $respuesta = [
            'success' => true,
            'nro_pedido' => $resultado['numero_comanda'],
            'numero_comanda' => $resultado['numero_comanda'],
            'id_pedido' => $resultado['id_pedido'] ?? null,
            'total' => $input['total'],
            'mensaje' => $resultado['mensaje'],
            'puntos_ganados' => $puntosGanados,
            'puntos_totales' => $clienteActualizado['puntos_fidelidad'] ?? 0,
            'tipo_cliente' => $clienteActualizado['tipo_cliente'] ?? 'NUEVO'
        ];
        
        error_log("=== PEDIDO CREADO EXITOSAMENTE ===");
        error_log("Número de pedido: " . $respuesta['nro_pedido']);
        error_log("ID pedido: " . $respuesta['id_pedido']);
        
        echo json_encode($respuesta);
    } else {
        error_log("ERROR: crearPedido retornó success=false");
        error_log("Mensaje de error: " . ($resultado['mensaje'] ?? 'sin mensaje'));
        throw new Exception($resultado['mensaje']);
    }

} catch (Exception $e) {
    error_log("=== ERROR EN CATCH PRINCIPAL ===");
    error_log("Error message: " . $e->getMessage());
    error_log("Error file: " . $e->getFile());
    error_log("Error line: " . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());
    error_log("Input recibido: " . json_encode($input ?? []));
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'mensaje' => $e->getMessage(),
        'error_detalle' => 'Error en línea ' . $e->getLine() . ' de ' . basename($e->getFile()),
        'debug_info' => ENVIRONMENT === 'development' ? [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ] : null
    ]);
}
error_log("=== FIN procesar_pedido.php ===");

