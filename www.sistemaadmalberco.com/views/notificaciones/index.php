<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');
include ('../../controllers/notificaciones/listar.php'); 
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Centro de Notificaciones</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item active">Notificaciones</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <!-- Resumen de Notificaciones -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo $resumen_notificaciones['stock_bajo'] ?? 0; ?></h3>
                            <p>Stock Bajo</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <a href="../almacen/stock_bajo.php" class="small-box-footer">
                            Ver Productos <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $resumen_notificaciones['pedidos_pendientes'] ?? 0; ?></h3>
                            <p>Pedidos Pendientes</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <a href="../pedidos/index.php?estado=PENDIENTE" class="small-box-footer">
                            Ver Pedidos <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $resumen_notificaciones['mesas_ocupadas'] ?? 0; ?></h3>
                            <p>Mesas Ocupadas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chair"></i>
                        </div>
                        <a href="../mesas/index.php" class="small-box-footer">
                            Ver Mesas <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $resumen_notificaciones['no_leidas'] ?? 0; ?></h3>
                            <p>Sin Leer</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <a href="#notificaciones" class="small-box-footer">
                            Ver Todas <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="btn-group">
                        <a href="index.php" class="btn btn-default">
                            <i class="fas fa-list"></i> Todas
                        </a>
                        <a href="index.php?tipo=STOCK" class="btn btn-danger">
                            <i class="fas fa-boxes"></i> Stock
                        </a>
                        <a href="index.php?tipo=PEDIDO" class="btn btn-warning">
                            <i class="fas fa-shopping-cart"></i> Pedidos
                        </a>
                        <a href="index.php?tipo=SISTEMA" class="btn btn-info">
                            <i class="fas fa-cog"></i> Sistema
                        </a>
                        <a href="index.php?leidas=0" class="btn btn-success">
                            <i class="fas fa-envelope"></i> No Leídas
                        </a>
                    </div>
                    <div class="btn-group float-right">
                        <button type="button" 
                                class="btn btn-primary" 
                                onclick="marcarTodasLeidas()">
                            <i class="fas fa-check-double"></i> Marcar Todas como Leídas
                        </button>
                    </div>
                </div>
            </div>

            <!-- Lista de Notificaciones -->
            <div class="row" id="notificaciones">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-bell"></i> Notificaciones</h3>
                        </div>
                        <div class="card-body p-0">
                            <?php if (count($notificaciones_datos) > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($notificaciones_datos as $notif): 
                                    $id_notificacion = $notif['id_notificacion'];
                                     $leido = $notif['leido'];
                                    
                                    // Determinar icono y color según tipo
                                    $icono = 'fa-bell';
                                    $color = 'primary';
                                    switch ($notif['tipo']) {
                                        case 'STOCK':
                                            $icono = 'fa-exclamation-triangle';
                                            $color = 'danger';
                                            break;
                                        case 'PEDIDO':
                                            $icono = 'fa-shopping-cart';
                                            $color = 'warning';
                                            break;
                                        case 'SISTEMA':
                                            $icono = 'fa-cog';
                                            $color = 'info';
                                            break;
                                        case 'VENTA':
                                            $icono = 'fa-dollar-sign';
                                            $color = 'success';
                                            break;
                                    }
                                ?>
                                <div class="list-group-item list-group-item-action <?php echo $leido ? '' : 'list-group-item-light'; ?>" 
                                     onclick="marcarLeida(<?php echo $id_notificacion; ?>)"
                                     style="cursor: pointer;">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="avatar avatar-md rounded-circle bg-<?php echo $color; ?>">
                                                <i class="fas <?php echo $icono; ?> text-white"></i>
                                            </div>
                                        </div>
                                        <div class="col ml-n2">
                                            <h5 class="mb-1">
                                                <?php echo htmlspecialchars($notif['titulo']); ?>
                                                <?php if (!$leido): ?>
                                                    <span class="badge badge-primary">Nuevo</span>
                                                <?php endif; ?>
                                            </h5>
                                            <p class="mb-1 text-muted">
                                                <?php echo htmlspecialchars($notif['mensaje']); ?>
                                            </p>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> 
                                                <?php 
                                                $fecha = new DateTime($notif['fecha_notificacion']);
                                                $ahora = new DateTime();
                                                $diff = $ahora->diff($fecha);
                                                
                                                if ($diff->days > 0) {
                                                    echo $diff->days . ' día(s) atrás';
                                                } elseif ($diff->h > 0) {
                                                    echo $diff->h . ' hora(s) atrás';
                                                } elseif ($diff->i > 0) {
                                                    echo $diff->i . ' minuto(s) atrás';
                                                } else {
                                                    echo 'Hace un momento';
                                                }
                                                ?>
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <?php if ($notif['enlace']): ?>
                                                <a href="<?php echo htmlspecialchars($notif['enlace']); ?>" 
                                                   class="btn btn-sm btn-outline-<?php echo $color; ?>"
                                                   onclick="event.stopPropagation();">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>
                                            <?php endif; ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="event.stopPropagation(); eliminarNotificacion(<?php echo $id_notificacion; ?>)"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="p-4 text-center">
                                <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No hay notificaciones</h5>
                                <p class="text-muted">Cuando haya nuevas notificaciones, aparecerán aquí.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formularios ocultos -->
<form id="formMarcarLeida" action="../../controllers/notificaciones/marcar_leida.php" method="post" style="display: none;">
    <input type="hidden" name="id_notificacion" id="id_notificacion_leida">
</form>

<form id="formMarcarTodas" action="../../controllers/notificaciones/marcar_todas_leidas.php" method="post" style="display: none;">
</form>

<form id="formEliminar" action="../../controllers/notificaciones/eliminar.php" method="post" style="display: none;">
    <input type="hidden" name="id_notificacion" id="id_notificacion_eliminar">
</form>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
    function marcarLeida(id) {
        document.getElementById('id_notificacion_leida').value = id;
        document.getElementById('formMarcarLeida').submit();
    }

    function marcarTodasLeidas() {
        Swal.fire({
            title: '¿Marcar todas como leídas?',
            text: "Todas las notificaciones se marcarán como leídas",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, marcar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formMarcarTodas').submit();
            }
        });
    }

    function eliminarNotificacion(id) {
        Swal.fire({
            title: '¿Eliminar notificación?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('id_notificacion_eliminar').value = id;
                document.getElementById('formEliminar').submit();
            }
        });
    }

    // Auto-refresh cada 30 segundos
    setTimeout(function(){
        location.reload();
    }, 30000);
</script>

<style>
    .avatar {
        width: 3rem;
        height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .list-group-item-light {
        background-color: #f8f9fa !important;
        border-left: 4px solid #007bff;
    }
    
    .list-group-item:hover {
        background-color: #f1f3f5;
    }
</style>
