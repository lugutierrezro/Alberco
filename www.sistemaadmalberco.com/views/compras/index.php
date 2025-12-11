<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');
include ('../../controllers/compras/listar.php'); 
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Gestión de Compras</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item active">Compras</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <!-- Estadísticas -->
            <div class="row mb-3">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $estadisticas_compras['total_compras'] ?? 0; ?></h3>
                            <p>Total Compras</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>S/ <?php echo number_format($estadisticas_compras['monto_total'] ?? 0, 2); ?></h3>
                            <p>Monto Total</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $estadisticas_compras['mes_actual'] ?? 0; ?></h3>
                            <p>Compras Este Mes</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo $estadisticas_compras['proveedores_activos'] ?? 0; ?></h3>
                            <p>Proveedores Activos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-truck"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Compras Registradas</h3>
                            <div class="card-tools">
                                <a href="create.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Nueva Compra
                                </a>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped table-hover table-sm">
                                <thead>
                                <tr>
                                    <th><center>Nro</center></th>
                                    <th><center>Fecha</center></th>
                                    <th><center>Proveedor</center></th>
                                    <th><center>Producto</center></th>
                                    <th><center>Cantidad</center></th>
                                    <th><center>P. Unitario</center></th>
                                    <th><center>Total</center></th>
                                    <th><center>Comprobante</center></th>
                                    <th><center>Usuario</center></th>
                                    <th><center>Acciones</center></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador = 0;
                                foreach ($compras_datos as $compra){
                                    $id_compra = $compra['id_compra'];
                                    $contador++;
                                    $total = $compra['cantidad'] * $compra['precio_compra'];
                                    ?>
                                    <tr>
                                        <td><center><?php echo $contador; ?></center></td>
                                        <td><center>
                                            <?php echo date('d/m/Y', strtotime($compra['fecha_compra'])); ?>
                                            <br><small class="text-muted"><?php echo date('H:i', strtotime($compra['fecha_compra'])); ?></small>
                                        </center></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($compra['nombre_proveedor']); ?></strong>
                                            <?php if ($compra['empresa']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($compra['empresa']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($compra['producto_nombre']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($compra['producto_codigo']); ?></small>
                                        </td>
                                        <td><center>
                                            <span class="badge badge-info">
                                                <?php echo $compra['cantidad']; ?>
                                            </span>
                                        </center></td>
                                        <td>S/ <?php echo number_format($compra['precio_compra'], 2); ?></td>
                                        <td><strong>S/ <?php echo number_format($total, 2); ?></strong></td>
                                        <td><center>
                                            <?php if ($compra['comprobante']): ?>
                                                <span class="badge badge-success">
                                                    <?php echo htmlspecialchars($compra['comprobante']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Sin comprobante</span>
                                            <?php endif; ?>
                                        </center></td>
                                        <td><?php echo htmlspecialchars($compra['usuario_nombre']); ?></td>
                                        <td>
                                            enterer>
                                                <div class="btn-group">
                                                    <a href="show.php?id=<?php echo $id_compra; ?>" 
                                                       class="btn btn-info btn-sm" 
                                                       title="Ver">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            onclick="confirmarAnular(<?php echo $id_compra; ?>)"
                                                            title="Anular">
                                                        <i class="fa fa-ban"></i>
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

<!-- Formulario oculto para anular -->
<form id="formAnular" action="../../controllers/compras/anular.php" method="post" style="display: none;">
    <input type="hidden" name="id_compra" id="id_compra_anular">
</form>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 25,
            "order": [[0, "desc"]],
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Compras",
                "infoEmpty": "Mostrando 0 a 0 de 0 Compras",
                "infoFiltered": "(Filtrado de _MAX_ total Compras)",
                "lengthMenu": "Mostrar _MENU_ Compras",
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

    function confirmarAnular(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "¿Desea anular esta compra? Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('id_compra_anular').value = id;
                document.getElementById('formAnular').submit();
            }
        });
    }
</script>
