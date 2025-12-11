<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');
include ('../../controllers/clientes/detalle.php');

// Función helper para obtener valor seguro del array
function getValor($array, $key, $default = '') {
    return isset($array[$key]) && $array[$key] !== null ? $array[$key] : $default;
}

// Función para determinar el badge del estado
function getBadgeEstado($estado) {
    $badges = [
        'pendiente'       => 'warning',
        'en preparación'  => 'info',
        'en preparacion'  => 'info',
        'listo'           => 'primary',
        'en camino'       => 'secondary',
        'entregado'       => 'success',
        'cancelado'       => 'danger',
    ];
    return $badges[strtolower($estado)] ?? 'secondary';
}
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Detalle del Cliente</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Información del Cliente -->
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <i class="fas fa-user-circle fa-5x text-primary"></i>
                            </div>

                            <h3 class="profile-username text-center">
                                <?php echo htmlspecialchars(getValor($cliente_dato, 'nombre') . ' ' . getValor($cliente_dato, 'apellidos')); ?>
                            </h3>

                            <p class="text-muted text-center"><?php echo htmlspecialchars(getValor($cliente_dato, 'codigo_cliente', 'Sin código')); ?></p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Tipo</b> 
                                    <span class="float-right badge badge-success">
                                        <?php echo htmlspecialchars(getValor($cliente_dato, 'tipo_cliente', 'NUEVO')); ?>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Teléfono</b> 
                                    <span class="float-right">
                                        <?php echo htmlspecialchars(getValor($cliente_dato, 'telefono', 'N/A')); ?>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Email</b> 
                                    <span class="float-right">
                                        <?php echo htmlspecialchars(getValor($cliente_dato, 'email', 'N/A')); ?>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Total Pedidos</b> 
                                    <span class="float-right badge badge-info">
                                        <?php echo getValor($estadisticas_cliente, 'total_pedidos', 0); ?>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Total Gastado</b> 
                                    <span class="float-right badge badge-success">
                                        S/ <?php echo number_format(getValor($estadisticas_cliente, 'total_gastado', 0), 2); ?>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Ticket Promedio</b> 
                                    <span class="float-right">
                                        S/ <?php echo number_format(getValor($estadisticas_cliente, 'ticket_promedio', 0), 2); ?>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Último Pedido</b> 
                                    <span class="float-right">
                                        <?php 
                                        $ultimo_pedido = getValor($estadisticas_cliente, 'ultimo_pedido');
                                        echo $ultimo_pedido ? date('d/m/Y', strtotime($ultimo_pedido)) : 'Nunca';
                                        ?>
                                    </span>
                                </li>
                            </ul>

                            <a href="index.php" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Volver al Listado
                            </a>
                            <a href="update.php?id=<?php echo getValor($cliente_dato, 'id_cliente'); ?>" class="btn btn-success btn-block">
                                <i class="fas fa-edit"></i> Editar Cliente
                            </a>
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Dirección</h3>
                        </div>
                        <div class="card-body">
                            <strong><i class="fas fa-home mr-1"></i> Dirección Principal</strong>
                            <p class="text-muted">
                                <?php echo htmlspecialchars(getValor($cliente_dato, 'direccion', 'No registrada')); ?>
                            </p>

                            <strong><i class="fas fa-map-pin mr-1"></i> Distrito</strong>
                            <p class="text-muted"><?php echo htmlspecialchars(getValor($cliente_dato, 'distrito', 'N/A')); ?></p>

                            <strong><i class="fas fa-city mr-1"></i> Ciudad</strong>
                            <p class="text-muted"><?php echo htmlspecialchars(getValor($cliente_dato, 'ciudad', 'N/A')); ?></p>

                            <?php $referencia = getValor($cliente_dato, 'referencia_direccion'); ?>
                            <?php if ($referencia): ?>
                            <strong><i class="fas fa-info-circle mr-1"></i> Referencia</strong>
                            <p class="text-muted"><?php echo htmlspecialchars($referencia); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Historial de Pedidos -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Historial de Pedidos</h3>
                        </div>
                        <div class="card-body">
                            <?php if (isset($historial_pedidos) && count($historial_pedidos) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Código</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Total</th>
                                        <th>Acciones</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($historial_pedidos as $pedido): 
                                        // Obtener el nombre del estado (viene del JOIN con tb_estados)
                                        $nombre_estado = getValor($pedido, 'nombre_estado') 
                                                      ?: getValor($pedido, 'estado')
                                                      ?: 'Sin estado';
                                        
                                        $badge_estado = getBadgeEstado($nombre_estado);
                                        
                                        // Obtener código del pedido
                                        $codigo_pedido = getValor($pedido, 'numero_comanda') 
                                                      ?: getValor($pedido, 'nro_pedido')
                                                      ?: 'N/A';
                                        
                                        // Obtener fecha
                                        $fecha_pedido = getValor($pedido, 'fecha_pedido') 
                                                     ?: getValor($pedido, 'fyh_creacion');
                                    ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                echo $fecha_pedido 
                                                    ? date('d/m/Y H:i', strtotime($fecha_pedido)) 
                                                    : 'N/A'; 
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($codigo_pedido); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo getValor($pedido, 'tipo_pedido') == 'delivery' ? 'info' : 'primary'; ?>">
                                                    <?php echo htmlspecialchars(ucfirst(getValor($pedido, 'tipo_pedido', 'N/A'))); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $badge_estado; ?>">
                                                    <?php echo htmlspecialchars(ucfirst($nombre_estado)); ?>
                                                </span>
                                            </td>
                                            <td>S/ <?php echo number_format(getValor($pedido, 'total', 0), 2); ?></td>
                                            <td>
                                                <a href="../pedidos/show.php?id=<?php echo getValor($pedido, 'id_pedido'); ?>" 
                                                   class="btn btn-info btn-sm"
                                                   title="Ver pedido">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Este cliente aún no ha realizado pedidos.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>
