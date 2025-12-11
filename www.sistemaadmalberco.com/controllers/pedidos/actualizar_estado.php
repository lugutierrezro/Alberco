<?php
// Actualizar Estado de Pedido
session_start();
require_once __DIR__ . '/../../services/database/config.php';

// Función para sanitizar datos
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/views/pedidos/');
    exit;
}

try {
    $pedidoId = (int)($_POST['id_pedido'] ?? 0);
    $nuevoEstadoId = (int)($_POST['id_estado'] ?? 0);
    $observaciones = sanitize($_POST['observaciones'] ?? '');
    
    if ($pedidoId <= 0 || $nuevoEstadoId <= 0) {
        $_SESSION['error'] = 'Datos incompletos';
        header('Location: ' . URL_BASE . '/views/pedidos/');
        exit;
    }
    
    // Verificar que el estado existe
    $verificarEstadoSql = "SELECT id_estado, nombre_estado FROM tb_estados WHERE id_estado = ? AND estado_registro = 'ACTIVO'";
    $stmtVerificar = $pdo->prepare($verificarEstadoSql);
    $stmtVerificar->execute([$nuevoEstadoId]);
    $estadoValido = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
    
    if (!$estadoValido) {
        $_SESSION['error'] = 'Estado inválido';
        header('Location: ' . URL_BASE . '/views/pedidos/show.php?id=' . $pedidoId);
        exit;
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Actualizar estado del pedido
    $updateSql = "UPDATE tb_pedidos 
                  SET id_estado = :id_estado, 
                      fecha_entrega_real = CASE 
                          WHEN :id_estado_check = (SELECT id_estado FROM tb_estados WHERE nombre_estado = 'Entregado' LIMIT 1) 
                          THEN NOW() 
                          ELSE fecha_entrega_real 
                      END,
                      fyh_actualizacion = NOW() 
                  WHERE id_pedido = :id";
    
    $stmt = $pdo->prepare($updateSql);
    $stmt->execute([
        ':id_estado' => $nuevoEstadoId,
        ':id_estado_check' => $nuevoEstadoId,
        ':id' => $pedidoId
    ]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('No se pudo actualizar el pedido. Verifique que exista.');
    }
    
    // Registrar en historial de seguimiento
    $historySql = "INSERT INTO tb_seguimiento_pedidos 
                   (id_pedido, id_estado, observaciones, id_usuario, fecha_cambio, fyh_creacion)
                   VALUES (:pedido, :estado, :observaciones, :usuario, NOW(), NOW())";
    
    $historyStmt = $pdo->prepare($historySql);
    $historyStmt->execute([
        ':pedido' => $pedidoId,
        ':estado' => $nuevoEstadoId,
        ':observaciones' => $observaciones,
        ':usuario' => $_SESSION['id_usuario']
    ]);
    
    // Si se entrega o cancela, liberar mesa si existe
    // Estados finales: 5 = Entregado, 6 = Cancelado
    if (in_array($nuevoEstadoId, [5, 6])) {
        $getPedidoSql = "SELECT id_mesa, tipo_pedido FROM tb_pedidos WHERE id_pedido = ?";
        $getPedidoStmt = $pdo->prepare($getPedidoSql);
        $getPedidoStmt->execute([$pedidoId]);
        $pedido = $getPedidoStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($pedido && !empty($pedido['id_mesa']) && strtolower($pedido['tipo_pedido']) === 'mesa') {
            $updateMesaSql = "UPDATE tb_mesas 
                             SET estado = 'disponible', 
                                 fyh_actualizacion = NOW() 
                             WHERE id_mesa = ?";
            $updateMesaStmt = $pdo->prepare($updateMesaSql);
            $updateMesaStmt->execute([$pedido['id_mesa']]);
        }
    }
    
    // **NUEVO: Generar Venta cuando el pedido se marca como "Entregado" (id_estado = 5)**
    if ($nuevoEstadoId == 5) {
        try {
            // Verificar si ya existe una venta para este pedido
            $checkVentaSql = "SELECT id_venta FROM tb_ventas WHERE id_pedido = ?";
            $checkVentaStmt = $pdo->prepare($checkVentaSql);
            $checkVentaStmt->execute([$pedidoId]);
            $ventaExistente = $checkVentaStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$ventaExistente) {
                // Obtener datos completos del pedido
                $getPedidoCompletoSql = "SELECT p.*, 
                                               c.id_cliente,
                                               p.id_usuario_registro as id_usuario
                                        FROM tb_pedidos p
                                        LEFT JOIN tb_clientes c ON p.id_cliente = c.id_cliente
                                        WHERE p.id_pedido = ?";
                $getPedidoCompletoStmt = $pdo->prepare($getPedidoCompletoSql);
                $getPedidoCompletoStmt->execute([$pedidoId]);
                $pedidoCompleto = $getPedidoCompletoStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($pedidoCompleto) {
                    // Obtener siguiente número de venta
                    $getNroVentaSql = "SELECT COALESCE(MAX(nro_venta), 0) + 1 as siguiente FROM tb_ventas";
                    $getNroVentaStmt = $pdo->prepare($getNroVentaSql);
                    $getNroVentaStmt->execute();
                    $nroVenta = $getNroVentaStmt->fetch(PDO::FETCH_ASSOC)['siguiente'];
                    
                    // Obtener tipo de comprobante por defecto (Boleta)
                    $getTipoComprobanteSql = "SELECT id_tipo_comprobante FROM tb_tipo_comprobante 
                                             WHERE nombre_tipo = 'Boleta' AND estado_registro = 'ACTIVO' LIMIT 1";
                    $getTipoComprobanteStmt = $pdo->prepare($getTipoComprobanteSql);
                    $getTipoComprobanteStmt->execute();
                    $tipoComprobante = $getTipoComprobanteStmt->fetch(PDO::FETCH_ASSOC);
                    $idTipoComprobante = $tipoComprobante ? $tipoComprobante['id_tipo_comprobante'] : 1;
                    
                    // Obtener método de pago por defecto (Efectivo)
                    $getMetodoPagoSql = "SELECT id_metodo FROM tb_metodos_pago 
                                        WHERE nombre_metodo = 'Efectivo' AND estado_registro = 'ACTIVO' LIMIT 1";
                    $getMetodoPagoStmt = $pdo->prepare($getMetodoPagoSql);
                    $getMetodoPagoStmt->execute();
                    $metodoPago = $getMetodoPagoStmt->fetch(PDO::FETCH_ASSOC);
                    $idMetodoPago = $metodoPago ? $metodoPago['id_metodo'] : 1;
                    
                    // Calcular IGV (18%)
                    $subtotal = $pedidoCompleto['subtotal'];
                    $igv = $subtotal * 0.18;
                    $total = $pedidoCompleto['total'];
                    
                    // Insertar venta
                    $insertVentaSql = "INSERT INTO tb_ventas (
                        nro_venta, serie_comprobante, numero_comprobante,
                        id_cliente, id_usuario, id_tipo_comprobante, id_metodo_pago,
                        id_pedido, subtotal, igv, descuento, total,
                        monto_recibido, vuelto, estado_venta, fecha_venta, fyh_creacion
                    ) VALUES (
                        :nro_venta, 'B001', :numero_comprobante,
                        :id_cliente, :id_usuario, :id_tipo_comprobante, :id_metodo_pago,
                        :id_pedido, :subtotal, :igv, :descuento, :total,
                        :monto_recibido, :vuelto, 'completada', NOW(), NOW()
                    )";
                    
                    $insertVentaStmt = $pdo->prepare($insertVentaSql);
                    $insertVentaStmt->execute([
                        ':nro_venta' => $nroVenta,
                        ':numero_comprobante' => str_pad($nroVenta, 8, '0', STR_PAD_LEFT),
                        ':id_cliente' => $pedidoCompleto['id_cliente'],
                        ':id_usuario' => $pedidoCompleto['id_usuario'],
                        ':id_tipo_comprobante' => $idTipoComprobante,
                        ':id_metodo_pago' => $idMetodoPago,
                        ':id_pedido' => $pedidoId,
                        ':subtotal' => $subtotal,
                        ':igv' => $igv,
                        ':descuento' => $pedidoCompleto['descuento'],
                        ':total' => $total,
                        ':monto_recibido' => ceil($total),
                        ':vuelto' => ceil($total) - $total
                    ]);
                    
                    $idVenta = $pdo->lastInsertId();
                    
                    // Copiar detalles del pedido a la venta
                    $getDetallesPedidoSql = "SELECT id_producto, cantidad, precio_unitario 
                                            FROM tb_detalle_pedidos 
                                            WHERE id_pedido = ?";
                    $getDetallesPedidoStmt = $pdo->prepare($getDetallesPedidoSql);
                    $getDetallesPedidoStmt->execute([$pedidoId]);
                    $detallesPedido = $getDetallesPedidoStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $insertDetalleVentaSql = "INSERT INTO tb_detalle_ventas (
                        id_venta, id_producto, cantidad, precio_unitario, subtotal, fyh_creacion
                    ) VALUES (
                        :id_venta, :id_producto, :cantidad, :precio_unitario, :subtotal, NOW()
                    )";
                    
                    $insertDetalleVentaStmt = $pdo->prepare($insertDetalleVentaSql);
                    
                    foreach ($detallesPedido as $detalle) {
                        $subtotalDetalle = $detalle['cantidad'] * $detalle['precio_unitario'];
                        $insertDetalleVentaStmt->execute([
                            ':id_venta' => $idVenta,
                            ':id_producto' => $detalle['id_producto'],
                            ':cantidad' => $detalle['cantidad'],
                            ':precio_unitario' => $detalle['precio_unitario'],
                            ':subtotal' => $subtotalDetalle
                        ]);
                        
                        // Actualizar stock
                        $updateStockSql = "UPDATE tb_almacen 
                                          SET stock = stock - :cantidad,
                                              fyh_actualizacion = NOW()
                                          WHERE id_producto = :id_producto";
                        $updateStockStmt = $pdo->prepare($updateStockSql);
                        $updateStockStmt->execute([
                            ':cantidad' => $detalle['cantidad'],
                            ':id_producto' => $detalle['id_producto']
                        ]);
                    }
                    
                    error_log("✅ Venta generada automáticamente: ID $idVenta para Pedido $pedidoId");
                }
            } else {
                // **NUEVO: Si la venta ya existe, simplemente actualizar su estado a 'completada'**
                $updateVentaSql = "UPDATE tb_ventas 
                                  SET estado_venta = 'completada',
                                      fyh_actualizacion = NOW()
                                  WHERE id_pedido = ? AND estado_venta = 'pendiente'";
                $updateVentaStmt = $pdo->prepare($updateVentaSql);
                $updateVentaStmt->execute([$pedidoId]);
                
                if ($updateVentaStmt->rowCount() > 0) {
                    error_log("✅ Venta existente actualizada a 'completada' para Pedido $pedidoId");
                }
            }
        } catch (PDOException $e) {
            error_log("❌ Error al generar venta automática: " . $e->getMessage());
            // No detener la transacción principal si falla la venta
        }
    }
    
    // Crear notificación si el sistema lo requiere
    try {
        $notificacionSql = "INSERT INTO tb_notificaciones 
                           (id_pedido, id_usuario_destino, tipo, titulo, mensaje, fecha_notificacion, fyh_creacion)
                           VALUES 
                           (:pedido, :usuario, 'cambio_estado', :titulo, :mensaje, NOW(), NOW())";
        
        $notifStmt = $pdo->prepare($notificacionSql);
        $notifStmt->execute([
            ':pedido' => $pedidoId,
            ':usuario' => $_SESSION['id_usuario'],
            ':titulo' => 'Estado de Pedido Actualizado',
            ':mensaje' => "El pedido #{$pedidoId} cambió a estado: {$estadoValido['nombre_estado']}"
        ]);
    } catch (PDOException $e) {
        // Si falla la notificación, no afecta la transacción principal
        error_log("Error al crear notificación: " . $e->getMessage());
    }
    
    $pdo->commit();
    
    $_SESSION['success'] = 'Estado actualizado correctamente a: ' . $estadoValido['nombre_estado'];
    
    // Redirigir según de donde viene
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'show';
    if ($redirect === 'index') {
        header('Location: ' . URL_BASE . '/views/pedidos/index.php');
    } else {
        header('Location: ' . URL_BASE . '/views/pedidos/show.php?id=' . $pedidoId);
    }
    exit;
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error al actualizar estado: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud: ' . $e->getMessage();
    
    $pedidoIdRedirect = $pedidoId ?? 0;
    if ($pedidoIdRedirect > 0) {
        header('Location: ' . URL_BASE . '/views/pedidos/show.php?id=' . $pedidoIdRedirect);
    } else {
        header('Location: ' . URL_BASE . '/views/pedidos/index.php');
    }
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error de base de datos al actualizar estado: " . $e->getMessage());
    $_SESSION['error'] = 'Error de base de datos. Por favor intente nuevamente.';
    
    $pedidoIdRedirect = $pedidoId ?? 0;
    if ($pedidoIdRedirect > 0) {
        header('Location: ' . URL_BASE . '/views/pedidos/show.php?id=' . $pedidoIdRedirect);
    } else {
        header('Location: ' . URL_BASE . '/views/pedidos/index.php');
    }
    exit;
}
