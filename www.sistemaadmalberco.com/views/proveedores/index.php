<?php
include('../../services/database/config.php');
include('../../contans/layout/sesion.php');
include('../../contans/layout/parte1.php');
include('../../controllers/proveedores/listar.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Gestión de Proveedores</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item active">Proveedores</li>
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
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Proveedores Registrados</h3>
                            <div class="card-tools">
                                <a href="create.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Nuevo Proveedor
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
                                        <th>
                                            <center>Nro</center>
                                        </th>
                                        <th>
                                            <center>Código</center>
                                        </th>
                                        <th>
                                            <center>Proveedor</center>
                                        </th>
                                        <th>
                                            <center>RUC/DNI</center>
                                        </th>
                                        <th>
                                            <center>Contacto</center>
                                        </th>
                                        <th>
                                            <center>Email</center>
                                        </th>
                                        <th>
                                            <center>Total Compras</center>
                                        </th>
                                        <th>
                                            <center>Acciones</center>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $contador = 0;
                                    foreach ($proveedores_datos as $proveedor_dato) {
                                        $id_proveedor = $proveedor_dato['id_proveedor'];
                                        $contador++;
                                    ?>
                                        <tr>
                                            <td>
                                                <center><?php echo $contador; ?></center>
                                            </td>
                                            <td>
                                                <center><?php echo htmlspecialchars($proveedor_dato['codigo_proveedor'], ENT_QUOTES, 'UTF-8'); ?></center>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($proveedor_dato['nombre_proveedor'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <?php if (!empty($proveedor_dato['empresa'])): ?>
                                                    <br><small class="text-muted">
                                                        <?php echo htmlspecialchars($proveedor_dato['empresa'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <center><?php echo htmlspecialchars($proveedor_dato['ruc'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></center>
                                            </td>
                                            <td>
                                                <?php if (!empty($proveedor_dato['telefono'])): ?>
                                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($proveedor_dato['telefono'], ENT_QUOTES, 'UTF-8'); ?>
                                                <?php endif; ?>
                                                <?php if (!empty($proveedor_dato['celular'])): ?>
                                                    <br><i class="fas fa-mobile-alt"></i> <?php echo htmlspecialchars($proveedor_dato['celular'], ENT_QUOTES, 'UTF-8'); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($proveedor_dato['email'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <center>
                                                    <span class="badge badge-info">
                                                        <?php echo $proveedor_dato['total_compras'] ?? 0; ?>
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">
                                                        S/ <?php echo number_format($proveedor_dato['total_monto'] ?? 0, 2); ?>
                                                    </small>
                                                </center>
                                            </td>
                                            <td>
                                                <center>
                                                    <div class="btn-group">
                                                        <a href="show.php?id=<?php echo $id_proveedor; ?>"
                                                            class="btn btn-info btn-sm"
                                                            title="Ver">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                        <a href="update.php?id=<?php echo $id_proveedor;  ?>"
                                                            class="btn btn-success btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-pencil-alt"></i>
                                                        </a>
                                                        <button type="button"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="confirmarEliminar(<?php echo $id_proveedor; ?>)"
                                                            title="Eliminar">
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
<form id="formEliminar" action="../../controllers/proveedores/eliminar.php" method="post" style="display: none;">
    <input type="hidden" name="id_proveedor" id="id_proveedor_eliminar">
</form>

<?php include('../../contans/layout/mensajes.php'); ?>
<?php include('../../contans/layout/parte2.php'); ?>

<script>
    $(function() {
        $("#example1").DataTable({
            "pageLength": 10,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Proveedores",
                "infoEmpty": "Mostrando 0 a 0 de 0 Proveedores",
                "infoFiltered": "(Filtrado de _MAX_ total Proveedores)",
                "lengthMenu": "Mostrar _MENU_ Proveedores",
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
                    }, {
                        extend: 'csv'
                    }, {
                        extend: 'excel'
                    }, {
                        text: 'Imprimir',
                        extend: 'print'
                    }]
                },
                {
                    extend: 'colvis',
                    text: 'Visor de columnas',
                    collectionLayout: 'fixed three-column'
                }
            ],
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });

    function confirmarEliminar(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "¿Desea eliminar este proveedor?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('id_proveedor_eliminar').value = id;
                document.getElementById('formEliminar').submit();
            }
        });
    }
</script>