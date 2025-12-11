<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');
include ('../../controllers/empleado/listar.php'); 
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Gestión de Empleados</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Empleados Registrados</h3>
                            <div class="card-tools">
                                <a href="create.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-user-plus"></i> Nuevo Empleado
                                </a>
                                <a href="reporte_planilla.php" target="_blank" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-file-invoice-dollar"></i> Reporte Planilla
                                </a>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th><center>Nro</center></th>
                                    <th><center>Código</center></th>
                                    <th><center>Empleado</center></th>
                                    <th><center>Documento</center></th>
                                    <th><center>Rol</center></th>
                                    <th><center>Teléfono</center></th>
                                    <th><center>Email</center></th>
                                    <th><center>Estado</center></th>
                                    <th><center>Acciones</center></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador = 0;
                                foreach ($empleados_datos as $empleado){
                                    $id_empleado = $empleado['id_empleado'];
                                    $contador++;
                                    
                                    $nombre_completo = trim($empleado['nombres'] . ' ' . $empleado['apellidos']);
                                    
                                    // Badge de estado laboral
                                    $estado_color = match($empleado['estado_laboral']) {
                                        'ACTIVO' => 'success',
                                        'VACACIONES' => 'info',
                                        'LICENCIA' => 'warning',
                                        'SUSPENDIDO' => 'danger',
                                        'RETIRADO' => 'secondary',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <tr>
                                        <td><center><?php echo $contador; ?></center></td>
                                        <td><center><?php echo htmlspecialchars($empleado['codigo_empleado']); ?></center></td>
                                        <td>
                                            <?php echo htmlspecialchars($nombre_completo); ?>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($empleado['turno'] ?? 'N/A'); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <small>
                                                <strong><?php echo htmlspecialchars($empleado['tipo_documento']); ?>:</strong>
                                                <?php echo htmlspecialchars($empleado['numero_documento']); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <center>
                                                <span class="badge badge-primary">
                                                    <?php echo htmlspecialchars($empleado['nombre_rol'] ?? 'Sin rol'); ?>
                                                </span>
                                            </center>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($empleado['telefono']); ?>
                                            <?php if (!empty($empleado['celular'])): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($empleado['celular']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars($empleado['email']); ?></small>
                                            <?php if (!empty($empleado['username'])): ?>
                                                <br>
                                                <small class="badge badge-info">
                                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($empleado['username']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <center>
                                                <span class="badge badge-<?php echo $estado_color; ?>">
                                                    <?php echo htmlspecialchars($empleado['estado_laboral']); ?>
                                                </span>
                                            </center>
                                        </td>
                                        <td>
                                            <center>
                                                <div class="btn-group">
                                                    <a href="show.php?id=<?php echo $id_empleado; ?>" 
                                                       class="btn btn-info btn-sm" 
                                                       title="Ver">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <a href="update.php?id=<?php echo $id_empleado; ?>" 
                                                       class="btn btn-success btn-sm" 
                                                       title="Editar">
                                                        <i class="fa fa-pencil-alt"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            onclick="confirmarEliminar(<?php echo $id_empleado; ?>)"
                                                            title="Eliminar"
                                                            <?php echo !empty($empleado['username']) ? 'disabled' : ''; ?>>
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </center>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminar -->
<form id="formEliminar" action="../../controllers/empleado/eliminar.php" method="post" style="display: none;">
    <input type="hidden" name="id_empleado" id="id_empleado_eliminar">
</form>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 10,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Empleados",
                "infoEmpty": "Mostrando 0 a 0 de 0 Empleados",
                "infoFiltered": "(Filtrado de _MAX_ total Empleados)",
                "lengthMenu": "Mostrar _MENU_ Empleados",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscador:",
                "zeroRecords": "Sin resultados encontrados",
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
                    extend: 'csv'
                },{
                    extend: 'excel'
                },{
                    text: 'Imprimir',
                    extend: 'print'
                }]
            },
            {
                extend: 'colvis',
                text: 'Visor de columnas',
                collectionLayout: 'fixed three-column'
            }],
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });

    function confirmarEliminar(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "¿Desea eliminar este empleado? Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('id_empleado_eliminar').value = id;
                document.getElementById('formEliminar').submit();
            }
        });
    }
</script>
