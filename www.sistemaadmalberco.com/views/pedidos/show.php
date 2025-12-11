<?php
include('../../services/database/config.php');
include('../../contans/layout/sesion.php');
include('../../contans/layout/parte1.php');

// Obtener ID del pedido
$id_pedido_get = $_GET['id'] ?? 0;

if ($id_pedido_get <= 0) {
    $_SESSION['error'] = 'ID de pedido inválido';
    header('Location: index.php');
    exit;
}

// Obtener datos del pedido
try {
    $sql = "SELECT p.*, 
                   c.nombre as cliente_nombre, 
                   c.apellidos as cliente_apellidos,
                   c.telefono as cliente_telefono,
                   c.email as cliente_email,
                   m.numero_mesa,
                   u.username as usuario_registro,
                   e.nombre_estado as estado,
                   e.color as estado_color,
                   emp.nombres as delivery_nombre,
                   emp.apellidos as delivery_apellidos
            FROM tb_pedidos p
            INNER JOIN tb_clientes c ON p.id_cliente = c.id_cliente
            LEFT JOIN tb_mesas m ON p.id_mesa = m.id_mesa
            INNER JOIN tb_usuarios u ON p.id_usuario_registro = u.id_usuario
            LEFT JOIN tb_estados e ON p.id_estado = e.id_estado
            LEFT JOIN tb_empleados emp ON p.id_empleado_delivery = emp.id_empleado
            WHERE p.id_pedido = ? AND p.estado_registro = 'ACTIVO'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_pedido_get]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        $_SESSION['error'] = 'Pedido no encontrado';
        header('Location: index.php');
        exit;
    }
    
    // Obtener detalles del pedido
    $sqlDetalles = "SELECT pd.*, 
                           pr.nombre as producto_nombre, 
                           pr.codigo as producto_codigo
                    FROM tb_detalle_pedidos pd
                    INNER JOIN tb_almacen pr ON pd.id_producto = pr.id_producto
                    WHERE pd.id_pedido = ?
                    ORDER BY pd.id_detalle ASC";
    
    $stmtDetalles = $pdo->prepare($sqlDetalles);
    $stmtDetalles->execute([$id_pedido_get]);
    $detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener historial de estados
    $sqlHistorial = "SELECT s.*, 
                            e.nombre_estado, 
                            e.color as estado_color,
                            u.username as usuario_cambio
                     FROM tb_seguimiento_pedidos s
                     INNER JOIN tb_estados e ON s.id_estado = e.id_estado
                     LEFT JOIN tb_usuarios u ON s.id_usuario = u.id_usuario
                     WHERE s.id_pedido = ?
                     ORDER BY s.fecha_cambio DESC";
    
    $stmtHistorial = $pdo->prepare($sqlHistorial);
    $stmtHistorial->execute([$id_pedido_get]);
    $historial = $stmtHistorial->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al obtener pedido: " . $e->getMessage());
    $_SESSION['error'] = 'Error al cargar pedido: ' . $e->getMessage();
    header('Location: index.php');
    exit;
}

// Determinar badge de estado
$badge_estado = 'secondary';
$estado_normalizado = strtolower(str_replace(' ', '_', $pedido['estado']));

switch ($estado_normalizado) {
    case 'pendiente': $badge_estado = 'warning'; break;
    case 'en_preparación':
    case 'en_preparacion': $badge_estado = 'info'; break;
    case 'listo': $badge_estado = 'primary'; break;
    case 'en_camino': $badge_estado = 'secondary'; break;
    case 'entregado': $badge_estado = 'success'; break;
    case 'cancelado': $badge_estado = 'danger'; break;
}

// Calcular IGV y subtotales
$subtotal_calculado = 0;
foreach ($detalles as $detalle) {
    $subtotal_calculado += ($detalle['precio_unitario'] * $detalle['cantidad']);
}
$igv_calculado = $subtotal_calculado * 0.18;
$total_calculado = $subtotal_calculado + $igv_calculado;
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detalle del Pedido</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Pedidos</a></li>
                        <li class="breadcrumb-item active">Detalle</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Información del Pedido -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h3 class="card-title">
                                <i class="fas fa-shopping-cart"></i> 
                                Pedido <?php echo htmlspecialchars($pedido['numero_comanda'] ?? $pedido['nro_pedido'] ?? 'PED-' . $id_pedido_get); ?>
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-<?php echo $badge_estado; ?> badge-lg">
                                    <?php echo htmlspecialchars($pedido['estado']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Detalle de productos -->
                            <h5><i class="fas fa-utensils"></i> Productos del Pedido</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="bg-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th width="100"><center>Cantidad</center></th>
                                        <th width="120"><center>P. Unit.</center></th>
                                        <th width="120"><center>Subtotal</center></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($detalles)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                <i class="fas fa-info-circle"></i> No hay productos en este pedido
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($detalles as $detalle): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($detalle['producto_nombre']); ?></strong>
                                                <?php if (!empty($detalle['observaciones'])): ?>
                                                    <br><small class="text-muted">
                                                        <i class="fas fa-comment"></i> 
                                                        <?php echo htmlspecialchars($detalle['observaciones']); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td><center><?php echo $detalle['cantidad']; ?></center></td>
                                            <td class="text-right">S/ <?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                                            <td class="text-right">
                                                <strong>S/ <?php echo number_format($detalle['precio_unitario'] * $detalle['cantidad'], 2); ?></strong>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                                        <td class="text-right">
                                            <strong>S/ <?php echo number_format($pedido['subtotal'] ?? $subtotal_calculado, 2); ?></strong>
                                        </td>
                                    </tr>
                                    <?php if (!empty($pedido['costo_delivery']) && $pedido['costo_delivery'] > 0): ?>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Costo de Delivery:</strong></td>
                                        <td class="text-right">
                                            <strong>S/ <?php echo number_format($pedido['costo_delivery'], 2); ?></strong>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($pedido['descuento']) && $pedido['descuento'] > 0): ?>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Descuento:</strong></td>
                                        <td class="text-right text-danger">
                                            <strong>- S/ <?php echo number_format($pedido['descuento'], 2); ?></strong>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr class="bg-success text-white">
                                        <td colspan="3" class="text-right"><h5 class="mb-0">TOTAL:</h5></td>
                                        <td class="text-right"><h5 class="mb-0">S/ <?php echo number_format($pedido['total'], 2); ?></h5></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <?php if (!empty($pedido['observaciones'])): ?>
                            <div class="alert alert-info mt-3">
                                <strong><i class="fas fa-comment-dots"></i> Observaciones del Pedido:</strong><br>
                                <?php echo nl2br(htmlspecialchars($pedido['observaciones'])); ?>
                            </div>
                            <?php endif; ?>

                            <!-- Historial de Estados -->
                            <?php if (!empty($historial)): ?>
                            <hr>
                            <h5><i class="fas fa-history"></i> Historial de Estados</h5>
                            <div class="timeline">
                                <?php foreach ($historial as $cambio): ?>
                                <div>
                                    <i class="fas fa-circle bg-<?php 
                                        $color = 'secondary';
                                        $estado_h = strtolower(str_replace(' ', '_', $cambio['nombre_estado']));
                                        switch ($estado_h) {
                                            case 'pendiente': $color = 'warning'; break;
                                            case 'en_preparación':
                                            case 'en_preparacion': $color = 'info'; break;
                                            case 'listo': $color = 'primary'; break;
                                            case 'en_camino': $color = 'secondary'; break;
                                            case 'entregado': $color = 'success'; break;
                                            case 'cancelado': $color = 'danger'; break;
                                        }
                                        echo $color;
                                    ?>"></i>
                                    <div class="timeline-item">
                                        <span class="time">
                                            <i class="fas fa-clock"></i> 
                                            <?php echo date('d/m/Y H:i', strtotime($cambio['fecha_cambio'])); ?>
                                        </span>
                                        <h3 class="timeline-header">
                                            <span class="badge badge-<?php echo $color; ?>">
                                                <?php echo htmlspecialchars($cambio['nombre_estado']); ?>
                                            </span>
                                        </h3>
                                        <?php if (!empty($cambio['observaciones']) || !empty($cambio['usuario_cambio'])): ?>
                                        <div class="timeline-body">
                                            <?php if (!empty($cambio['usuario_cambio'])): ?>
                                                <small><i class="fas fa-user"></i> Por: <?php echo htmlspecialchars($cambio['usuario_cambio']); ?></small><br>
                                            <?php endif; ?>
                                            <?php if (!empty($cambio['observaciones'])): ?>
                                                <small><?php echo htmlspecialchars($cambio['observaciones']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Acciones -->
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Volver al Listado
                                    </a>
                                    
                                    <?php if ($estado_normalizado !== 'entregado' && $estado_normalizado !== 'cancelado'): ?>
                                    <button type="button" 
                                            class="btn btn-primary" 
                                            onclick="cambiarEstado()">
                                        <i class="fas fa-exchange-alt"></i> Cambiar Estado
                                    </button>
                                    <?php endif; ?>
                                    
                                    <button type="button" 
                                            class="btn btn-info" 
                                            onclick="window.print()">
                                        <i class="fas fa-print"></i> Imprimir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Cliente y Pedido -->
                <div class="col-md-4">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user"></i> Información del Cliente</h3>
                        </div>
                        <div class="card-body">
                            <strong><i class="fas fa-user"></i> Cliente:</strong>
                            <p class="text-muted">
                                <?php echo htmlspecialchars($pedido['cliente_nombre'] . ' ' . ($pedido['cliente_apellidos'] ?? '')); ?>
                            </p>

                            <strong><i class="fas fa-phone"></i> Teléfono:</strong>
                            <p class="text-muted">
                                <a href="tel:<?php echo htmlspecialchars($pedido['cliente_telefono']); ?>">
                                    <?php echo htmlspecialchars($pedido['cliente_telefono']); ?>
                                </a>
                            </p>

                            <?php if (!empty($pedido['cliente_email'])): ?>
                            <strong><i class="fas fa-envelope"></i> Email:</strong>
                            <p class="text-muted">
                                <a href="mailto:<?php echo htmlspecialchars($pedido['cliente_email']); ?>">
                                    <?php echo htmlspecialchars($pedido['cliente_email']); ?>
                                </a>
                            </p>
                            <?php endif; ?>

                            <hr>

                            <strong><i class="fas fa-tag"></i> Tipo de Pedido:</strong>
                            <p>
                                <span class="badge badge-info">
                                    <?php echo ucwords(str_replace('_', ' ', $pedido['tipo_pedido'])); ?>
                                </span>
                            </p>

                            <?php if (strtolower($pedido['tipo_pedido']) === 'mesa' && !empty($pedido['numero_mesa'])): ?>
                            <strong><i class="fas fa-chair"></i> Mesa:</strong>
                            <p>
                                <span class="badge badge-dark">Mesa <?php echo htmlspecialchars($pedido['numero_mesa']); ?></span>
                            </p>
                            <?php endif; ?>

                            <?php if (strtolower($pedido['tipo_pedido']) === 'delivery'): ?>
                                <?php if (!empty($pedido['direccion_entrega'])): ?>
                                <strong><i class="fas fa-map-marker-alt"></i> Dirección de Entrega:</strong>
                                <p class="text-muted"><?php echo htmlspecialchars($pedido['direccion_entrega']); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($pedido['delivery_nombre'])): ?>
                                <strong><i class="fas fa-motorcycle"></i> Repartidor:</strong>
                                <p class="text-muted">
                                    <?php echo htmlspecialchars($pedido['delivery_nombre'] . ' ' . ($pedido['delivery_apellidos'] ?? '')); ?>
                                </p>
                                <?php endif; ?>
                            <?php endif; ?>

                            <hr>

                            <strong><i class="fas fa-calendar"></i> Fecha de Pedido:</strong>
                            <p class="text-muted">
                                <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?>
                            </p>

                            <?php if (!empty($pedido['fecha_entrega_real'])): ?>
                            <strong><i class="fas fa-check-circle"></i> Fecha de Entrega:</strong>
                            <p class="text-muted">
                                <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_entrega_real'])); ?>
                            </p>
                            <?php endif; ?>

                            <strong><i class="fas fa-user-tie"></i> Registrado por:</strong>
                            <p class="text-muted"><?php echo htmlspecialchars($pedido['usuario_registro']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambiar Estado -->
<div class="modal fade" id="modalCambiarEstado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../../controllers/pedidos/actualizar_estado.php" method="post">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">Cambiar Estado del Pedido</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_pedido" value="<?php echo $id_pedido_get; ?>">
                    
                    <div class="form-group">
                        <label for="id_estado">Nuevo Estado <span class="text-danger">*</span></label>
                        <select name="id_estado" id="id_estado" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <?php
                            try {
                                $sqlEstados = "SELECT id_estado, nombre_estado, color 
                                              FROM tb_estados 
                                              WHERE estado_registro = 'ACTIVO' 
                                              ORDER BY orden ASC";
                                $stmtEstados = $pdo->prepare($sqlEstados);
                                $stmtEstados->execute();
                                $estados = $stmtEstados->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($estados as $estado) {
                                    $selected = ($estado['nombre_estado'] === $pedido['estado']) ? 'selected' : '';
                                    echo '<option value="' . $estado['id_estado'] . '" ' . $selected . '>' . 
                                         htmlspecialchars($estado['nombre_estado']) . '</option>';
                                }
                            } catch (PDOException $e) {
                                echo '<option value="">Error al cargar estados</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="observaciones">Observaciones</label>
                        <textarea name="observaciones" 
                                  id="observaciones" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Ingrese observaciones sobre el cambio de estado..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- TICKET DE IMPRESIÓN (Visible solo al imprimir) -->
<div id="impresion_ticket">
    <div class="ticket-header">
        <h3><?php echo EMPRESA_NOMBRE; ?></h3>
        <p><?php echo EMPRESA_DIRECCION; ?></p>
        <p>Tel: <?php echo EMPRESA_TELEFONO; ?></p>
        <p class="ticket-divider">--------------------------------</p>
        <h4>PEDIDO: <?php echo htmlspecialchars($pedido['numero_comanda'] ?? $pedido['nro_pedido'] ?? $id_pedido_get); ?></h4>
        <p>Fecha: <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></p>
        <p>Tipo: <?php echo strtoupper($pedido['tipo_pedido']); ?></p>
        <?php if (!empty($pedido['numero_mesa'])): ?>
            <p><strong>MESA: <?php echo $pedido['numero_mesa']; ?></strong></p>
        <?php endif; ?>
    </div>

    <div class="ticket-body">
        <p class="ticket-divider">--------------------------------</p>
        <p><strong>CLIENTE:</strong></p>
        <p><?php echo htmlspecialchars($pedido['cliente_nombre'] . ' ' . $pedido['cliente_apellidos']); ?></p>
        <p><?php echo htmlspecialchars($pedido['cliente_telefono']); ?></p>
        
        <?php if (strtolower($pedido['tipo_pedido']) === 'delivery' && !empty($pedido['direccion_entrega'])): ?>
            <p><strong>DIRECCIÓN:</strong></p>
            <p><?php echo htmlspecialchars($pedido['direccion_entrega']); ?></p>
        <?php endif; ?>
        
        <p class="ticket-divider">--------------------------------</p>
        
        <table class="ticket-table">
            <thead>
                <tr>
                    <th class="cant">CANT</th>
                    <th class="item">PRODUCTO</th>
                    <th class="price">IMPORTE</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $detalle): ?>
                <tr>
                    <td class="cant"><?php echo $detalle['cantidad']; ?></td>
                    <td class="item">
                        <?php echo htmlspecialchars($detalle['producto_nombre']); ?>
                        <?php if (!empty($detalle['observaciones'])): ?>
                            <br><small>(<?php echo htmlspecialchars($detalle['observaciones']); ?>)</small>
                        <?php endif; ?>
                    </td>
                    <td class="price"><?php echo number_format($detalle['precio_unitario'] * $detalle['cantidad'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p class="ticket-divider">--------------------------------</p>
        
        <div class="ticket-totals">
            <p>SUBTOTAL: <span class="float-right">S/ <?php echo number_format($pedido['subtotal'] ?? $subtotal_calculado, 2); ?></span></p>
            
            <?php if (!empty($pedido['costo_delivery']) && $pedido['costo_delivery'] > 0): ?>
            <p>DELIVERY: <span class="float-right">S/ <?php echo number_format($pedido['costo_delivery'], 2); ?></span></p>
            <?php endif; ?>
            
            <?php if (!empty($pedido['descuento']) && $pedido['descuento'] > 0): ?>
            <p>DESCUENTO: <span class="float-right">- S/ <?php echo number_format($pedido['descuento'], 2); ?></span></p>
            <?php endif; ?>
            
            <p class="ticket-total-big">TOTAL: <span class="float-right">S/ <?php echo number_format($pedido['total'], 2); ?></span></p>
        </div>
        
        <?php if (!empty($pedido['observaciones'])): ?>
        <p class="ticket-divider">--------------------------------</p>
        <p><strong>OBSERVACIONES:</strong></p>
        <p><?php echo htmlspecialchars($pedido['observaciones']); ?></p>
        <?php endif; ?>
        
        <p class="ticket-divider">--------------------------------</p>
        <center>
            <p>¡GRACIAS POR SU PREFERENCIA!</p>
            <p>www.alberco.com</p>
        </center>
    </div>
</div>

<?php include('../../contans/layout/mensajes.php'); ?>
<?php include('../../contans/layout/parte2.php'); ?>

<script>
    function cambiarEstado() {
        $('#modalCambiarEstado').modal('show');
    }

    // Mover el ticket al body para evitar que se oculte con el wrapper al imprimir
    document.addEventListener("DOMContentLoaded", function() {
        const ticket = document.getElementById("impresion_ticket");
        if (ticket) {
            document.body.appendChild(ticket);
        }
    });
</script>

<style>
    @media screen {
        #impresion_ticket {
            display: none;
        }
    }

    @media print {
        /* Ocultar TODO lo demás */
        body > *:not(#impresion_ticket) {
            display: none !important;
        }

        /* Asegurar que el ticket sea visible */
        #impresion_ticket {
            display: block !important;
            visibility: visible !important;
            position: absolute;
            left: 0;
            top: 0;
            width: 80mm; /* Ancho estándar de ticket */
            padding: 0;
            margin: 0;
            font-family: 'Courier New', Courier, monospace; /* Fuente tipo ticket */
            font-size: 12px;
            color: black;
            background: white;
            z-index: 9999;
        }
        
        body {
            background-color: white !important;
            margin: 0 !important;
            padding: 0 !important;
            height: auto !important;
            overflow: visible !important;
        }
    }
    
    /* Estilos del Ticket */
    .ticket-header {
        text-align: center;
        margin-bottom: 10px;
    }
    .ticket-header h3 {
        font-size: 16px;
        font-weight: bold;
        margin: 0;
    }
    .ticket-header p {
        margin: 2px 0;
    }
    .ticket-divider {
        margin: 5px 0;
        overflow: hidden;
        white-space: nowrap;
    }
    .ticket-table {
        width: 100%;
        border-collapse: collapse;
    }
    .ticket-table th {
        text-align: left;
        border-bottom: 1px dashed black;
        font-size: 11px;
    }
    .ticket-table td {
        vertical-align: top;
        padding: 2px 0;
    }
    .cant { width: 15%; text-align: center; }
    .item { width: 60%; }
    .price { width: 25%; text-align: right; }
    
    .ticket-totals p {
        margin: 2px 0;
        text-align: right;
    }
    .float-right {
        float: right;
    }
    .ticket-total-big {
        font-weight: bold;
        font-size: 14px;
        border-top: 1px dashed black;
        padding-top: 5px;
        margin-top: 5px !important;
    }
    
    .timeline {
        position: relative;
        padding: 20px 0;
    }
    
    .timeline > div {
        margin-bottom: 15px;
        position: relative;
        padding-left: 30px;
    }
    
    .timeline > div > .fas {
        position: absolute;
        left: 0;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        text-align: center;
        line-height: 20px;
        font-size: 10px;
        color: white;
    }
    
    .timeline-item {
        padding: 10px;
        background: #f4f4f4;
        border-radius: 5px;
    }
    
    .timeline-header {
        margin: 0 0 5px 0;
        font-size: 14px;
    }
</style>
