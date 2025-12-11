<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Obtener ID de la mesa
$id_mesa_get = $_GET['id'] ?? 0;

if ($id_mesa_get <= 0) {
    $_SESSION['error'] = 'ID de mesa inválido';
    header('Location: index.php');
    exit;
}

// Obtener datos de la mesa
try {
    $sql = "SELECT * FROM tb_mesas WHERE id_mesa = ? AND estado_registro = 'ACTIVO'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_mesa_get]);
    $mesa_dato = $stmt->fetch();
    
    if (!$mesa_dato) {
        $_SESSION['error'] = 'Mesa no encontrada';
        header('Location: index.php');
        exit;
    }
    
    // Obtener pedidos activos de la mesa
    $sqlPedidos = "SELECT p.*, c.nombres, c.apellidos, u.username
                   FROM tb_pedidos p
                   INNER JOIN tb_clientes c ON p.id_cliente = c.id_cliente
                   INNER JOIN tb_usuarios u ON p.id_usuario = u.id_usuario
                   WHERE p.id_mesa = ? 
                   AND p.estado IN ('PENDIENTE', 'EN_PREPARACION', 'LISTO')
                   AND p.estado_registro = 'ACTIVO'
                   ORDER BY p.fecha_pedido DESC";
    
    $stmtPedidos = $pdo->prepare($sqlPedidos);
    $stmtPedidos->execute([$id_mesa_get]);
    $pedidos_activos = $stmtPedidos->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener historial reciente
    $sqlHistorial = "SELECT p.*, c.nombres, c.apellidos
                     FROM tb_pedidos p
                     INNER JOIN tb_clientes c ON p.id_cliente = c.id_cliente
                     WHERE p.id_mesa = ?
                     AND p.estado_registro = 'ACTIVO'
                     ORDER BY p.fecha_pedido DESC
                     LIMIT 10";
    
    $stmtHistorial = $pdo->prepare($sqlHistorial);
    $stmtHistorial->execute([$id_mesa_get]);
    $historial = $stmtHistorial->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al obtener mesa: " . $e->getMessage());
    $_SESSION['error'] = 'Error al cargar datos';
    header('Location: index.php');
    exit;
}

// Determinar color según estado
$color_card = 'success';
switch ($mesa_dato['estado']) {
    case 'OCUPADA': $color_card = 'danger'; break;
    case 'RESERVADA': $color_card = 'warning'; break;
    case 'MANTENIMIENTO': $color_card = 'secondary'; break;
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detalle de Mesa</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Mesas</a></li>
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
                <!-- Información de la Mesa -->
                <div class="col-md-4">
                    <div class="card card-<?php echo $color_card; ?> card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <i class="fas fa-chair fa-5x text-<?php echo $color_card; ?>"></i>
                            </div>

                            <h3 class="profile-username text-center">
                                Mesa <?php echo $mesa_dato['numero_mesa']; ?>
                            </h3>

                            <p class="text-muted text-center">
                                <span class="badge badge-<?php echo $color_card; ?> badge-lg">
                                    <?php echo $mesa_dato['estado']; ?>
                                </span>
                            </p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b><i class="fas fa-users"></i> Capacidad</b> 
                                    <span class="float-right">
                                        <?php echo $mesa_dato['capacidad']; ?> personas
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-map-marker-alt"></i> Zona</b> 
                                    <span class="float-right">
                                        <?php echo htmlspecialchars($mesa_dato['zona'] ?? 'Principal'); ?>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-shopping-cart"></i> Pedidos Activos</b> 
                                    <span class="float-right badge badge-danger">
                                        <?php echo count($pedidos_activos); ?>
                                    </span>
                                </li>
                            </ul>

                            <?php if ($mesa_dato['descripcion']): ?>
                            <div class="alert alert-info">
                                <strong>Descripción:</strong><br>
                                <?php echo nl2br(htmlspecialchars($mesa_dato['descripcion'])); ?>
                            </div>
                            <?php endif; ?>

                            <a href="index.php" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <a href="update.php?id=<?php echo $id_mesa_get; ?>" class="btn btn-success btn-block">
                                <i class="fas fa-edit"></i> Editar Mesa
                            </a>
                            <button type="button" 
                                    class="btn btn-primary btn-block"
                                    onclick="cambiarEstado()">
                                <i class="fas fa-exchange-alt"></i> Cambiar Estado
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pedidos y Historial -->
                <div class="col-md-8">
                    <!-- Pedidos Activos -->
                    <?php if (count($pedidos_activos) > 0): ?>
                    <div class="card card-danger">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-clock"></i> Pedidos Activos</h3>
                        </div>
                        <div class="card-body">
                            <?php foreach ($pedidos_activos as $pedido): ?>
                            <div class="alert alert-warning">
                                <h5>
                                    <i class="icon fas fa-utensils"></i>
                                    Pedido <?php echo htmlspecialchars($pedido['numero_comanda'] ?? 'PED-' . $pedido['id_pedido']); ?>
                                </h5>
                                <p class="mb-1">
                                    <strong>Cliente:</strong> 
                                    <?php echo htmlspecialchars($pedido['nombres'] . ' ' . ($pedido['apellidos'] ?? '')); ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Hora:</strong> 
                                    <?php echo date('H:i', strtotime($pedido['fecha_pedido'])); ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Total:</strong> 
                                    S/ <?php echo number_format($pedido['total'], 2); ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Estado:</strong> 
                                    <span class="badge badge-info"><?php echo $pedido['estado']; ?></span>
                                </p>
                                <a href="../pedidos/show.php?id=<?php echo $pedido['id_pedido']; ?>" 
                                   class="btn btn-info btn-sm mt-2">
                                    <i class="fas fa-eye"></i> Ver Detalle
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Historial -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history"></i> Historial Reciente</h3>
                        </div>
                        <div class="card-body">
                            <?php if (count($historial) > 0): ?>
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($historial as $ped): 
                                    $badge = 'secondary';
                                    switch ($ped['estado']) {
                                        case 'PENDIENTE': $badge = 'warning'; break;
                                        case 'EN_PREPARACION': $badge = 'info'; break;
                                        case 'LISTO': $badge = 'primary'; break;
                                        case 'ENTREGADO': $badge = 'success'; break;
                                        case 'CANCELADO': $badge = 'danger'; break;
                                    }
                                ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($ped['fecha_pedido'])); ?></td>
                                    <td><?php echo htmlspecialchars($ped['nombres'] . ' ' . ($ped['apellidos'] ?? '')); ?></td>
                                    <td>S/ <?php echo number_format($ped['total'], 2); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $badge; ?>">
                                            <?php echo $ped['estado']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../pedidos/show.php?id=<?php echo $ped['id_pedido']; ?>" 
                                           class="btn btn-info btn-xs">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                No hay historial de pedidos para esta mesa.
                            </div>
                            <?php endif; ?>
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
            <form action="../../controllers/mesas/cambiar_estado.php" method="post">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">Cambiar Estado de Mesa</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_mesa" value="<?php echo $id_mesa_get; ?>">
                    
                    <div class="form-group">
                        <label for="estado">Nuevo Estado</label>
                        <select name="estado" id="estado" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="DISPONIBLE">Disponible</option>
                            <option value="OCUPADA">Ocupada</option>
                            <option value="RESERVADA">Reservada</option>
                            <option value="MANTENIMIENTO">Mantenimiento</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
    function cambiarEstado() {
        $('#modalCambiarEstado').modal('show');
    }
</script>
