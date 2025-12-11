<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Obtener historial de cajas cerradas
try {
    $sqlHistorial = "SELECT c.*, u.username as usuario_apertura
                     FROM tb_caja c
                     INNER JOIN tb_usuarios u ON c.id_usuario = u.id_usuario
                     WHERE c.estado = 'CERRADA'
                     ORDER BY c.fecha_cierre DESC
                     LIMIT 100";
    
    $stmtHistorial = $pdo->prepare($sqlHistorial);
    $stmtHistorial->execute();
    $historial = $stmtHistorial->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al obtener historial: " . $e->getMessage());
    $historial = [];
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Historial de Cajas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Caja</a></li>
                        <li class="breadcrumb-item active">Historial</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history"></i> Cajas Cerradas</h3>
                            <div class="card-tools">
                                <a href="index.php" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (count($historial) > 0): ?>
                            <table id="tablaHistorial" class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th><center>Nro</center></th>
                                    <th><center>Turno</center></th>
                                    <th><center>Fecha Apertura</center></th>
                                    <th><center>Fecha Cierre</center></th>
                                    <th><center>Usuario</center></th>
                                    <th class="text-right">Monto Inicial</th>
                                    <th class="text-right">Monto Esperado</th>
                                    <th class="text-right">Monto Final</th>
                                    <th class="text-right">Diferencia</th>
                                    <th><center>Acciones</center></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador = 0;
                                foreach ($historial as $caja):
                                    $contador++;
                                    $diferencia = $caja['monto_final'] - $caja['monto_esperado'];
                                    $badge_diferencia = $diferencia >= 0 ? 'success' : 'danger';
                                ?>
                                <tr>
                                    <td><center><?php echo $contador; ?></center></td>
                                    <td><center>
                                        <span class="badge badge-primary">
                                            <?php echo htmlspecialchars($caja['turno']); ?>
                                        </span>
                                    </center></td>
                                    <td><center><?php echo date('d/m/Y H:i', strtotime($caja['fecha_apertura'])); ?></center></td>
                                    <td><center><?php echo date('d/m/Y H:i', strtotime($caja['fecha_cierre'])); ?></center></td>
                                    <td><?php echo htmlspecialchars($caja['usuario_apertura']); ?></td>
                                    <td class="text-right">S/ <?php echo number_format($caja['monto_inicial'], 2); ?></td>
                                    <td class="text-right">S/ <?php echo number_format($caja['monto_esperado'], 2); ?></td>
                                    <td class="text-right"><strong>S/ <?php echo number_format($caja['monto_final'], 2); ?></strong></td>
                                    <td class="text-right">
                                        <span class="badge badge-<?php echo $badge_diferencia; ?>">
                                            S/ <?php echo number_format($diferencia, 2); ?>
                                        </span>
                                    </td>
                                    <td><center>
                                        <a href="detalle_caja.php?id=<?php echo $caja['id_caja']; ?>" 
                                           class="btn btn-info btn-sm"
                                           title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </center></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                No hay cajas cerradas en el historial.
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

<script>
    $(function () {
        $("#tablaHistorial").DataTable({
            "pageLength": 25,
            "order": [[0, "desc"]],
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Cajas",
                "infoEmpty": "Mostrando 0 a 0 de 0 Cajas",
                "infoFiltered": "(Filtrado de _MAX_ total)",
                "lengthMenu": "Mostrar _MENU_ Cajas",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron resultados",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            buttons: [{
                extend: 'collection',
                text: 'Reportes',
                orientation: 'landscape',
                buttons: [{
                    text: 'Copiar',
                    extend: 'copy',
                }, {
                    extend: 'pdf'
                },{
                    extend: 'excel'
                },{
                    text: 'Imprimir',
                    extend: 'print'
                }]
            }],
        }).buttons().container().appendTo('#tablaHistorial_wrapper .col-md-6:eq(0)');
    });
</script>
