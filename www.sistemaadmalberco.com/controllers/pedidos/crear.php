<?php
require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/pedidos');
    exit;
}

// Función para sanitizar inputs
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

try {
    $pedidoData = [
        'tipo_pedido' => strtolower(sanitize($_POST['tipo_pedido'] ?? '')),
        'id_mesa' => !empty($_POST['id_mesa']) ? (int)$_POST['id_mesa'] : null,
        'id_cliente' => (int)($_POST['id_cliente'] ?? 0),
        'id_usuario_registro' => $_SESSION['id_usuario'],
        'direccion_entrega' => sanitize($_POST['direccion_entrega'] ?? ''),
        'latitud_entrega' => !empty($_POST['latitud']) ? (float)$_POST['latitud'] : null,
        'longitud_entrega' => !empty($_POST['longitud']) ? (float)$_POST['longitud'] : null,
        'observaciones' => sanitize($_POST['observaciones'] ?? '')
    ];

    // Decodificar detalles de productos
    $detalles = json_decode($_POST['detalles'] ?? '[]', true);

    // Validaciones
    if (empty($detalles) || !is_array($detalles)) {
        $_SESSION['error'] = 'Debe agregar productos al pedido';
        header('Location: ' . URL_BASE . '/pedidos/create.php');
        exit;
    }

    if ($pedidoData['tipo_pedido'] === 'mesa' && empty($pedidoData['id_mesa'])) {
        $_SESSION['error'] = 'Debe seleccionar una mesa';
        header('Location: ' . URL_BASE . '/pedidos/create.php');
        exit;
    }

    if ($pedidoData['tipo_pedido'] === 'delivery' && empty($pedidoData['direccion_entrega'])) {
        $_SESSION['error'] = 'Debe ingresar la dirección de entrega';
        header('Location: ' . URL_BASE . '/pedidos/create.php');
        exit;
    }

    if ($pedidoData['id_cliente'] <= 0) {
        $_SESSION['error'] = 'Debe seleccionar un cliente';
        header('Location: ' . URL_BASE . '/pedidos/create.php');
        exit;
    }

    // Iniciar transacción
    $pdo->beginTransaction();

    // Generar número de comanda y nro_pedido (ambos distintos)
    $numeroComanda = 'COM-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    $nroPedido = 'PED-' . date('Y') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);

    // Calcular subtotal
    $subtotal = 0;
    foreach ($detalles as $detalle) {
        $subtotal += ($detalle['precio'] * $detalle['cantidad']);
    }
    
    // Calcular costo de delivery según tipo de pedido
    $costoDelivery = 0.00;
    $idEmpleadoDelivery = null;
    
    if ($pedidoData['tipo_pedido'] === 'delivery') {
        // Costo base de delivery (puedes ajustar según distancia si lo deseas)
        $costoDelivery = 5.00;
        
        // Asignar automáticamente un empleado delivery disponible
        $getDeliverySql = "SELECT e.id_empleado, e.nombres, e.apellidos
                          FROM tb_empleados e
                          INNER JOIN tb_usuarios u ON e.id_empleado = u.id_empleado
                          INNER JOIN tb_roles r ON u.id_rol = r.id_rol
                          WHERE r.rol = 'DELIVERY'
                          AND e.estado_laboral = 'ACTIVO'
                          AND e.estado_registro = 'ACTIVO'
                          ORDER BY RAND()
                          LIMIT 1";
        
        $stmtDelivery = $pdo->prepare($getDeliverySql);
        $stmtDelivery->execute();
        $empleadoDelivery = $stmtDelivery->fetch(PDO::FETCH_ASSOC);
        
        if ($empleadoDelivery) {
            $idEmpleadoDelivery = $empleadoDelivery['id_empleado'];
        }
        // Si no hay empleados delivery disponibles, continúa sin asignar (NULL)
    }
    
    // Descuento (por ahora 0, puedes implementar lógica de descuentos)
    $descuento = 0.00;
    
    // Calcular total
    $total = $subtotal + $costoDelivery - $descuento;

    // Estado inicial: Pendiente (id_estado = 1)
    $idEstadoPendiente = 1;


    // Insertar pedido
    $insertPedidoSql = "INSERT INTO tb_pedidos 
        (numero_comanda, nro_pedido, tipo_pedido, id_mesa, id_cliente, id_usuario_registro, 
        id_empleado_delivery, direccion_entrega, latitud_entrega, longitud_entrega, subtotal, 
        costo_delivery, descuento, total, id_estado, observaciones, fecha_pedido, fyh_creacion) 
        VALUES 
        (:numero_comanda, :nro_pedido, :tipo_pedido, :id_mesa, :id_cliente, :id_usuario_registro, 
         :id_empleado_delivery, :direccion_entrega, :latitud_entrega, :longitud_entrega, :subtotal, 
         :costo_delivery, :descuento, :total, :id_estado, :observaciones, NOW(), NOW())";

    $stmtPedido = $pdo->prepare($insertPedidoSql);
    $stmtPedido->execute([
        ':numero_comanda' => $numeroComanda,
        ':nro_pedido' => $nroPedido,
        ':tipo_pedido' => $pedidoData['tipo_pedido'],
        ':id_mesa' => $pedidoData['id_mesa'],
        ':id_cliente' => $pedidoData['id_cliente'],
        ':id_usuario_registro' => $pedidoData['id_usuario_registro'],
        ':id_empleado_delivery' => $idEmpleadoDelivery,
        ':direccion_entrega' => $pedidoData['direccion_entrega'],
        ':latitud_entrega' => $pedidoData['latitud_entrega'],
        ':longitud_entrega' => $pedidoData['longitud_entrega'],
        ':subtotal' => $subtotal,
        ':costo_delivery' => $costoDelivery,
        ':descuento' => $descuento,
        ':total' => $total,
        ':id_estado' => $idEstadoPendiente,
        ':observaciones' => $pedidoData['observaciones']
    ]);

    $pedidoId = $pdo->lastInsertId();

    // Insertar detalles
    $insertDetalleSql = "INSERT INTO tb_detalle_pedidos 
        (id_pedido, id_producto, cantidad, precio_unitario, observaciones, fyh_creacion) 
        VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario, :observaciones, NOW())";

    $stmtDetalle = $pdo->prepare($insertDetalleSql);

    foreach ($detalles as $detalle) {
        $stmtDetalle->execute([
            ':id_pedido' => $pedidoId,
            ':id_producto' => $detalle['id_producto'],
            ':cantidad' => $detalle['cantidad'],
            ':precio_unitario' => $detalle['precio'],
            ':observaciones' => $detalle['observaciones'] ?? null
        ]);
    }

    // Registrar en seguimiento de pedidos
    $insertSeguimientoSql = "INSERT INTO tb_seguimiento_pedidos 
        (id_pedido, id_estado, fecha_cambio, id_usuario, observaciones, fyh_creacion) 
        VALUES (:id_pedido, :id_estado, NOW(), :id_usuario, :observaciones, NOW())";
    
    $stmtSeguimiento = $pdo->prepare($insertSeguimientoSql);
    $stmtSeguimiento->execute([
        ':id_pedido' => $pedidoId,
        ':id_estado' => $idEstadoPendiente,
        ':id_usuario' => $pedidoData['id_usuario_registro'],
        ':observaciones' => 'Pedido creado'
    ]);
    
    // Si se asignó delivery automáticamente, registrar en seguimiento
    if ($idEmpleadoDelivery) {
        $insertSeguimientoDeliverySql = "INSERT INTO tb_seguimiento_pedidos 
            (id_pedido, id_estado, fecha_cambio, id_usuario, observaciones, fyh_creacion) 
            VALUES (:id_pedido, :id_estado, NOW(), :id_usuario, :observaciones, NOW())";
        
        $stmtSeguimientoDelivery = $pdo->prepare($insertSeguimientoDeliverySql);
        $stmtSeguimientoDelivery->execute([
            ':id_pedido' => $pedidoId,
            ':id_estado' => $idEstadoPendiente,
            ':id_usuario' => $pedidoData['id_usuario_registro'],
            ':observaciones' => 'Delivery asignado automáticamente: ' . $empleadoDelivery['nombres'] . ' ' . $empleadoDelivery['apellidos']
        ]);
        
        // Crear notificación para el delivery
        try {
            $notifSql = "INSERT INTO tb_notificaciones 
                        (id_pedido, id_usuario_destino, tipo, titulo, mensaje, fecha_notificacion, fyh_creacion)
                        SELECT ?, u.id_usuario, 'delivery_asignado', 'Nuevo pedido asignado', 
                               CONCAT('Se te ha asignado el pedido #', ?), NOW(), NOW()
                        FROM tb_usuarios u
                        WHERE u.id_empleado = ?";
            $pdo->prepare($notifSql)->execute([$pedidoId, $nroPedido, $idEmpleadoDelivery]);
        } catch (PDOException $e) {
            // Si falla la notificación, no afecta la transacción principal
            error_log("Error al crear notificación: " . $e->getMessage());
        }
    }

    // Si es mesa, actualizar estado a 'ocupada' (minúsculas según ENUM)
    if ($pedidoData['id_mesa']) {
        $updateMesaSql = "UPDATE tb_mesas SET estado = 'ocupada', fyh_actualizacion = NOW() WHERE id_mesa = ?";
        $pdo->prepare($updateMesaSql)->execute([$pedidoData['id_mesa']]);
    }

    $pdo->commit();

    $_SESSION['success'] = 'Pedido creado correctamente - ' . $numeroComanda;
    header('Location: ' . URL_BASE . '/views/pedidos/show.php?id=' . $pedidoId);
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error al crear pedido: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar el pedido';
    header('Location: ' . URL_BASE . 'views/pedidos/create.php');
    exit;
}
