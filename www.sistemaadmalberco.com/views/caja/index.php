<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');
include ('../../controllers/caja/resumen.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Gestión de Caja</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item active">Caja</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <?php if (isset($caja_actual) && $caja_actual): ?>
                <!-- Caja Abierta -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-success">
                            <h4><i class="icon fas fa-check"></i> Caja Abierta</h4>
                            <strong>Fecha Apertura:</strong> <?php echo date('d/m/Y', strtotime($caja_actual['fecha_arqueo'])); ?> - <?php echo $caja_actual['hora_apertura']; ?> | 
                            <strong>Usuario:</strong> <?php echo htmlspecialchars($caja_actual['nombre_usuario'] ?? 'N/A'); ?> |
                            <strong>Saldo Inicial:</strong> S/ <?php echo number_format($caja_actual['saldo_inicial'], 2); ?>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas de la Caja Actual -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>S/ <?php echo number_format($resumen_caja['total_ingresos'] ?? 0, 2); ?></h3>
                                <p>Ingresos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>S/ <?php echo number_format($resumen_caja['total_egresos'] ?? 0, 2); ?></h3>
                                <p>Egresos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>S/ <?php echo number_format($resumen_caja['saldo_actual'] ?? 0, 2); ?></h3>
                                <p>Saldo Actual</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo $resumen_caja['total_transacciones'] ?? 0; ?></h3>
                                <p>Transacciones</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen por Forma de Pago -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary">
                                <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Resumen por Forma de Pago</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Forma de Pago</th>
                                        <th class="text-right">Cantidad</th>
                                        <th class="text-right">Monto Total</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($resumen_por_metodo as $metodo): ?>
                                    <tr>
                                        <td>
                                            <?php
                                            $icono = 'fa-money-bill';
                                            switch ($metodo['forma_pago']) {
                                                case 'EFECTIVO': $icono = 'fa-money-bill-wave'; break;
                                                case 'TARJETA': $icono = 'fa-credit-card'; break;
                                                case 'TRANSFERENCIA': $icono = 'fa-university'; break;
                                                case 'YAPE': 
                                                case 'PLIN': $icono = 'fa-mobile-alt'; break;
                                            }
                                            ?>
                                            <i class="fas <?php echo $icono; ?>"></i>
                                            <?php echo htmlspecialchars($metodo['forma_pago']); ?>
                                        </td>
                                        <td class="text-right">
                                            <span class="badge badge-info"><?php echo $metodo['cantidad']; ?></span>
                                        </td>
                                        <td class="text-right">
                                            <strong>S/ <?php echo number_format($metodo['total'], 2); ?></strong>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success">
                                <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Ventas del Día</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-success"><i class="fas fa-receipt"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Pedidos</span>
                                                <span class="info-box-number"><?php echo $ventas_dia['total_pedidos'] ?? 0; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info"><i class="fas fa-dollar-sign"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Ventas</span>
                                                <span class="info-box-number">S/ <?php echo number_format($ventas_dia['total_ventas'] ?? 0, 2); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-warning"><i class="fas fa-chart-line"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Ticket Promedio</span>
                                                <span class="info-box-number">S/ <?php echo number_format($ventas_dia['ticket_promedio'] ?? 0, 2); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-danger"><i class="fas fa-percent"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Propinas</span>
                                                <span class="info-box-number">S/ <?php echo number_format($ventas_dia['total_propinas'] ?? 0, 2); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Movimientos Recientes -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-list"></i> Últimos Movimientos</h3>
                                <div class="card-tools">
                                    <a href="movimientos.php" class="btn btn-sm btn-info">
                                        Ver Todos
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (count($movimientos_recientes) > 0): ?>
                                <table class="table table-sm table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Hora</th>
                                        <th>Tipo</th>
                                        <th>Concepto</th>
                                        <th>Forma de Pago</th>
                                        <th class="text-right">Monto</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($movimientos_recientes as $mov): ?>
                                    <tr>
                                        <td><?php echo date('H:i', strtotime($mov['fecha_movimiento'])); ?></td>
                                        <td>
                                            <?php if ($mov['tipo_movimiento'] === 'INGRESO'): ?>
                                                <span class="badge badge-success">Ingreso</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Egreso</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($mov['concepto']); ?></td>
                                        <td><?php echo htmlspecialchars($mov['forma_pago']); ?></td>
                                        <td class="text-right">
                                            <?php if ($mov['tipo_movimiento'] === 'INGRESO'): ?>
                                                <strong class="text-success">+S/ <?php echo number_format($mov['monto'], 2); ?></strong>
                                            <?php else: ?>
                                                <strong class="text-danger">-S/ <?php echo number_format($mov['monto'], 2); ?></strong>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <div class="alert alert-info">
                                    No hay movimientos registrados en esta caja.
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <a href="movimientos.php" class="btn btn-info">
                                    <i class="fas fa-plus"></i> Registrar Movimiento
                                </a>
                                <a href="cierre.php" class="btn btn-danger">
                                    <i class="fas fa-lock"></i> Cerrar Caja
                                </a>
                                <a href="historial.php" class="btn btn-secondary">
                                    <i class="fas fa-history"></i> Ver Historial
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Caja Cerrada -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-warning">
                            <h4><i class="icon fas fa-exclamation-triangle"></i> No hay caja abierta</h4>
                            Debe abrir una caja para comenzar a trabajar.
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-unlock"></i> Apertura de Caja</h3>
                            </div>
                            <div class="card-body text-center">
                                <i class="fas fa-cash-register fa-5x text-primary mb-3"></i>
                                <h4>¿Desea abrir una nueva caja?</h4>
                                <p class="text-muted">
                                    Registre el monto inicial con el que comienza el turno
                                </p>
                                <a href="apertura.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-unlock"></i> Abrir Caja
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-secondary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-history"></i> Última Caja Cerrada</h3>
                            </div>
                            <div class="card-body">
                                <?php if (isset($ultima_caja) && $ultima_caja): ?>
                                <dl class="row">
                                    <dt class="col-sm-4">Fecha:</dt>
                                    <dd class="col-sm-8"><?php echo date('d/m/Y', strtotime($ultima_caja['fecha_arqueo'])); ?></dd>

                                    <dt class="col-sm-4">Hora Cierre:</dt>
                                    <dd class="col-sm-8"><?php echo $ultima_caja['hora_cierre']; ?></dd>

                                    <dt class="col-sm-4">Monto Inicial:</dt>
                                    <dd class="col-sm-8">S/ <?php echo number_format($ultima_caja['monto_inicial'], 2); ?></dd>

                                    <dt class="col-sm-4">Monto Final:</dt>
                                    <dd class="col-sm-8"><strong>S/ <?php echo number_format($ultima_caja['monto_final'], 2); ?></strong></dd>

                                    <dt class="col-sm-4">Diferencia:</dt>
                                    <dd class="col-sm-8">
                                        <?php 
                                        $diferencia = $ultima_caja['monto_final'] - $ultima_caja['monto_esperado'];
                                        $color = $diferencia >= 0 ? 'success' : 'danger';
                                        ?>
                                        <span class="badge badge-<?php echo $color; ?>">
                                            S/ <?php echo number_format($diferencia, 2); ?>
                                        </span>
                                    </dd>
                                </dl>
                                <a href="historial.php" class="btn btn-secondary btn-block">
                                    Ver Historial Completo
                                </a>
                                <?php else: ?>
                                <div class="alert alert-info">
                                    No hay historial de cajas cerradas.
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
    // Auto-refresh cada 30 segundos
    setTimeout(function(){
        location.reload();
    }, 30000);
</script>
