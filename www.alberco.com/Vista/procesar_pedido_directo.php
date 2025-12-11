<?php
/**
 * PROCESAR PEDIDO - VERSIÓN DIRECTA
 * Esta versión evita init.php y carga solo lo necesario
 * para evitar conflictos de sesión
 * 
 * MEJORADO: Sistema de logging a archivo incluido
 */

// ==========================================
// SISTEMA DE LOGGING
// ==========================================
function logPedido($mensaje, $datos = null, $esError = false) {
    $logDir = __DIR__ . '/../logs/pedidos';
    
    // Crear directorio si no existe
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $fecha = date('Y-m-d');
    $timestamp = date('Y-m-d H:i:s');
    $logFile = $logDir . "/pedidos_{$fecha}.log";
    
    $tipo = $esError ? 'ERROR' : 'INFO';
    $logEntry = "[{$timestamp}] [{$tipo}] {$mensaje}";
    
    if ($datos !== null) {
        $logEntry .= "\n" . str_repeat(' ', 22) . "Datos: " . json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    $logEntry .= "\n" . str_repeat('-', 80) . "\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    error_log($mensaje); // También al error_log de PHP
}

logPedido("=== INICIO procesar_pedido_directo.php ===");
logPedido("Método HTTP: " . $_SERVER['REQUEST_METHOD']);
logPedido("IP Cliente: " . ($_SERVER['REMOTE_ADDR'] ?? 'desconocida'));
logPedido("User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'desconocido'));

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    logPedido("Preflight request (OPTIONS) - respondiendo OK");
    exit;
}

try {
    // ==========================================
    // CONEXIÓN DIRECTA A BASE DE DATOS
    // ==========================================
    
    // Detectar entorno
    $isLocal = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1');
    
    if ($isLocal) {
        // LOCALHOST
        $dbHost = 'localhost';
        $dbUser = 'root';
        $dbPass = '';
        $dbName = 'sistema_gestion_alberco_v3';
    } else {
        // PRODUCCIÓN
        $dbHost = '127.0.0.1';
        $dbUser = 'allwiya_Gustavo';
        $dbPass = '159023..qQq';
        $dbName = 'allwiya_allwiya_gustavo';
    }
    
    logPedido("Conectando a BD: $dbName en $dbHost");
    
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    logPedido("✓ Conexión a BD exitosa");
    
    // ==========================================
    // VALIDAR TABLA DE ESTADOS
    // ==========================================
    logPedido("Validando tabla tb_estados...");
    
    // Verificar si existe la tabla
    $stmt = $pdo->query("SHOW TABLES LIKE 'tb_estados'");
    $tablaExiste = $stmt->rowCount() > 0;
    
    if (!$tablaExiste) {
        logPedido("⚠️ Tabla tb_estados no existe, creándola...", null, true);
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tb_estados (
                id_estado INT PRIMARY KEY AUTO_INCREMENT,
                nombre_estado VARCHAR(50) NOT NULL,
                descripcion TEXT,
                color VARCHAR(20),
                icono VARCHAR(50),
                estado_registro ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
                fyh_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        logPedido("✓ Tabla tb_estados creada");
    }
    
    // Verificar si existe el estado ID=1 (PENDIENTE)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_estados WHERE id_estado = 1");
    $existeEstado1 = $stmt->fetch()['total'] > 0;
    
    if (!$existeEstado1) {
        logPedido("⚠️ Estado PENDIENTE (ID=1) no existe, insertando estados por defecto...", null, true);
        $pdo->exec("
            INSERT INTO tb_estados (id_estado, nombre_estado, descripcion, color, icono) VALUES
            (1, 'PENDIENTE', 'Pedido recibido, pendiente de confirmación', '#FFC107', 'fa-clock'),
            (2, 'EN PREPARACIÓN', 'Pedido en preparación en cocina', '#2196F3', 'fa-fire'),
            (3, 'LISTO', 'Pedido listo para entrega', '#4CAF50', 'fa-check'),
            (4, 'EN CAMINO', 'Pedido en camino (delivery)', '#FF9800', 'fa-motorcycle'),
            (5, 'ENTREGADO', 'Pedido entregado al cliente', '#00C853', 'fa-check-circle'),
            (6, 'CANCELADO', 'Pedido cancelado', '#F44336', 'fa-times-circle')
            ON DUPLICATE KEY UPDATE nombre_estado=VALUES(nombre_estado)
        ");
        logPedido("✓ Estados insertados correctamente");
    } else {
        logPedido("✓ Tabla tb_estados OK - Estado PENDIENTE existe");
    }
    
    // ==========================================
    // RECIBIR Y VALIDAR DATOS
    // ==========================================
    
    $rawInput = file_get_contents('php://input');
    logPedido("Raw input recibido (primeros 500 chars)", substr($rawInput, 0, 500));
    
    $input = json_decode($rawInput, true);
    
    if (!$input) {
        $error = json_last_error_msg();
        logPedido("ERROR: JSON inválido - " . $error, ['raw' => substr($rawInput, 0, 200)], true);
        throw new Exception('JSON inválido: ' . $error);
    }
    
    logPedido("✓ JSON decodificado correctamente", array_keys($input));
    
    // Validar campos requeridos
    if (!isset($input['cliente']) || !isset($input['productos']) || !isset($input['tipo_pedido'])) {
        throw new Exception('Faltan campos requeridos');
    }
    
    if (empty($input['productos'])) {
        throw new Exception('El carrito está vacío');
    }
    
    $clienteData = $input['cliente'];
    $nombre = $clienteData['nombre'];
    $telefono = $clienteData['telefono'];
    $direccion = $clienteData['direccion'] ?? '';
    
    logPedido("Cliente: $nombre, Tel: $telefono, Dir: $direccion");
    logPedido("Tipo pedido: " . $input['tipo_pedido']);
    logPedido("Productos: " . count($input['productos']), $input['productos']);
    
    // ==========================================
    // BUSCAR O CREAR CLIENTE
    // ==========================================
    
    $stmt = $pdo->prepare("SELECT * FROM tb_clientes WHERE telefono = ?");
    $stmt->execute([$telefono]);
    $cliente = $stmt->fetch();
    
    if ($cliente) {
        $clienteId = $cliente['id_cliente'];
        logPedido("✓ Cliente existente encontrado", ['id' => $clienteId, 'nombre' => $cliente['nombre']]);
    } else {
        // Crear nuevo cliente
        logPedido("Creando nuevo cliente: $nombre");
        $stmt = $pdo->prepare("
            INSERT INTO tb_clientes (nombre, telefono, direccion, tipo_cliente, estado_registro, fyh_creacion)
            VALUES (?, ?, ?, 'NUEVO', 'ACTIVO', NOW())
        ");
        $stmt->execute([$nombre, $telefono, $direccion]);
        $clienteId = $pdo->lastInsertId();
        logPedido("✓ Nuevo cliente creado", ['id' => $clienteId]);
    }
    
    // ==========================================
    // CREAR PEDIDO
    // ==========================================
    
    logPedido("=== INICIANDO TRANSACCIÓN ===");
    $pdo->beginTransaction();
    
    // Generar número de comanda
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(nro_pedido, 10) AS UNSIGNED)) as ultimo FROM tb_pedidos WHERE nro_pedido LIKE 'PED-" . date('Y') . "-%'");
    $ultimo = $stmt->fetch();
    $siguiente = ($ultimo['ultimo'] ?? 0) + 1;
    $nroPedido = 'PED-' . date('Y') . '-' . str_pad($siguiente, 6, '0', STR_PAD_LEFT);
    
    logPedido("✓ Número de pedido generado: $nroPedido");
    
    // Calcular total
    $subtotal = 0;
    foreach ($input['productos'] as $prod) {
        $subtotal += $prod['precio'] * $prod['cantidad'];
    }
    $total = $subtotal + ($input['costo_delivery'] ?? 0);
    
    // Obtener método de pago del frontend
    $metodoPago = $input['metodo_pago'] ?? 'efectivo';
    logPedido("✓ Método de pago seleccionado: $metodoPago");
    
    // Observaciones del cliente (sin agregar método de pago)
    $observaciones = $input['observaciones'] ?? '';
    
    // Validar dirección para tipos que no sean delivery
    $direccionFinal = ($input['tipo_pedido'] === 'delivery') ? $direccion : null;
    $mesaFinal = ($input['tipo_pedido'] === 'mesa') ? ($clienteData['mesa'] ?? null) : null;
    
    logPedido("✓ Validaciones finales", [
        'tipo_pedido' => $input['tipo_pedido'],
        'direccion_final' => $direccionFinal,
        'mesa_final' => $mesaFinal,
        'metodo_pago' => $metodoPago
    ]);
    
    // Insertar pedido (SIN columna metodo_pago, guardado en observaciones)
    $stmt = $pdo->prepare("
        INSERT INTO tb_pedidos (
            nro_pedido, numero_comanda, id_cliente, id_usuario_registro, tipo_pedido, 
            id_estado, direccion_entrega, id_mesa, observaciones,
            subtotal, costo_delivery, total
        ) VALUES (?, ?, ?, 1, ?, 1, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $nroPedido,
        $nroPedido,
        $clienteId,
        $input['tipo_pedido'],
        $direccionFinal,
        $mesaFinal,
        $observaciones,  // ← Solo observaciones del cliente
        $subtotal,
        $input['costo_delivery'] ?? 0,
        $total
    ]);
    
    $pedidoId = $pdo->lastInsertId();
    logPedido("✓ Pedido creado en BD", ['id' => $pedidoId, 'nro' => $nroPedido]);
    
    // =====================================================
    // CREAR VENTA VINCULADA AL PEDIDO (estado pendiente)
    // =====================================================
    
    // Mapear método de pago texto → ID de tb_metodos_pago
    $metodoPagoMap = [
        'efectivo' => 1,
        'tarjeta' => 2,
        'yape' => 3,
        'plin' => 4,
        'transferencia' => 5,
        'qr' => 6
    ];
    
    $idMetodoPago = $metodoPagoMap[strtolower($metodoPago)] ?? 1; // Default: efectivo
    logPedido("✓ Método de pago mapeado", [
        'metodo_texto' => $metodoPago,
        'id_metodo_pago' => $idMetodoPago
    ]);
    
    // Generar número de venta
    $stmtVenta = $pdo->query("SELECT COALESCE(MAX(nro_venta), 0) + 1 AS next_nro FROM tb_ventas");
    $nroVenta = $stmtVenta->fetch(PDO::FETCH_ASSOC)['next_nro'];
    
    // Obtener serie y correlativo del tipo de comprobante (por defecto Boleta = 1)
    $idTipoComprobante = 1; // Boleta
    $stmtComprobante = $pdo->prepare("
        SELECT serie, correlativo_actual + 1 AS next_correlativo 
        FROM tb_tipo_comprobante 
        WHERE id_tipo_comprobante = ?
    ");
    $stmtComprobante->execute([$idTipoComprobante]);
    $comprobante = $stmtComprobante->fetch(PDO::FETCH_ASSOC);
    $serie = $comprobante['serie'];
    $numeroComprobante = str_pad($comprobante['next_correlativo'], 8, '0', STR_PAD_LEFT);
    
    // Crear venta en estado PENDIENTE
    $stmtInsertVenta = $pdo->prepare("
        INSERT INTO tb_ventas (
            nro_venta, serie_comprobante, numero_comprobante,
            id_cliente, id_usuario, id_tipo_comprobante, id_metodo_pago,
            id_pedido, subtotal, igv, total,
            monto_recibido, vuelto, estado_venta
        ) VALUES (?, ?, ?, ?, 1, ?, ?, ?, ?, 0, ?, 0, 0, 'pendiente')
    ");
    
    $stmtInsertVenta->execute([
        $nroVenta,
        $serie,
        $numeroComprobante,
        $clienteId,
        $idTipoComprobante,
        $idMetodoPago,  // ← ID del método de pago
        $pedidoId,      // ← Vínculo con pedido
        $subtotal,
        $total
    ]);
    
    $ventaId = $pdo->lastInsertId();
    logPedido("✓ Venta creada (pendiente)", [
        'id_venta' => $ventaId,
        'nro_venta' => $nroVenta,
        'id_metodo_pago' => $idMetodoPago,
        'estado' => 'pendiente'
    ]);
    
    // Actualizar correlativo del comprobante
    $pdo->prepare("
        UPDATE tb_tipo_comprobante 
        SET correlativo_actual = correlativo_actual + 1 
        WHERE id_tipo_comprobante = ?
    ")->execute([$idTipoComprobante]);
    
    // Insertar detalles de la venta
    $stmtDetalleVenta = $pdo->prepare("
        INSERT INTO tb_detalle_ventas (id_venta, id_producto, cantidad, precio_unitario, subtotal)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    foreach ($input['productos'] as $prod) {
        $subtotalProducto = $prod['precio'] * $prod['cantidad'];
        $stmtDetalleVenta->execute([
            $ventaId,
            $prod['id'],
            $prod['cantidad'],
            $prod['precio'],
            $subtotalProducto
        ]);
    }
    
    logPedido("✓ Detalles de venta insertados");
    
    // Actualizar pedido con id_venta
    $pdo->prepare("UPDATE tb_pedidos SET id_venta = ? WHERE id_pedido = ?")->execute([$ventaId, $pedidoId]);
    logPedido("✓ Pedido vinculado con venta", ['id_venta' => $ventaId]);
    
    // =====================================================
    // INSERTAR DETALLES DEL PEDIDO
    // =====================================================
    
    // Insertar detalles del pedido
    $stmt = $pdo->prepare("
        INSERT INTO tb_detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario)
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($input['productos'] as $prod) {
        $stmt->execute([
            $pedidoId,
            $prod['id'],
            $prod['cantidad'],
            $prod['precio']
        ]);
        
        // Actualizar stock
        $pdo->prepare("UPDATE tb_almacen SET stock = stock - ? WHERE id_producto = ?")->execute([
            $prod['cantidad'],
            $prod['id']
        ]);
    }
    
    logPedido("✓ Detalles del pedido insertados y stock actualizado", ['items' => count($input['productos'])]);
    
    // Insertar estado inicial en seguimiento
    $stmt = $pdo->prepare("
        INSERT INTO tb_seguimiento_pedidos (id_pedido, id_estado, id_usuario, observaciones)
        VALUES (?, 1, 1, 'Pedido creado desde sitio web')
    ");
    $stmt->execute([$pedidoId]);
    
    $pdo->commit();
    
    logPedido("=== ✅ PEDIDO COMPLETADO EXITOSAMENTE ===", [
        'id_pedido' => $pedidoId,
        'nro_pedido' => $nroPedido,
        'cliente_id' => $clienteId,
        'total' => $total,
        'productos' => count($input['productos'])
    ]);
    
    // ==========================================
    // RESPUESTA EXITOSA
    // ==========================================
    
    echo json_encode([
        'success' => true,
        'nro_pedido' => $nroPedido,
        'id_pedido' => $pedidoId,
        'total' => $total,
        'mensaje' => 'Pedido registrado correctamente'
    ]);
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
        logPedido("⚠️ Transacción revertida", null, true);
    }
    
    logPedido("❌ ERROR CRÍTICO", [
        'mensaje' => $e->getMessage(),
        'archivo' => $e->getFile(),
        'linea' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], true);
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'mensaje' => $e->getMessage()
    ]);
}

logPedido("=== FIN procesar_pedido_directo.php ===");
