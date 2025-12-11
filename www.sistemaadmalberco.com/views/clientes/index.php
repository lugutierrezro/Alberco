<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');
include ('../../controllers/clientes/listar.php'); 
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Gestión de Clientes</h1>
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
                            <h3 class="card-title">Clientes Registrados</h3>
                            <div class="card-tools">
                                <a href="create.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-user-plus"></i> Nuevo Cliente
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
                                    <th><center>Cliente</center></th>
                                    <th><center>Teléfono</center></th>
                                    <th><center>Email</center></th>
                                    <th><center>Dirección</center></th>
                                    <th><center>Total Pedidos</center></th>
                                    <th><center>Acciones</center></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador = 0;
                                foreach ($clientes_datos as $cliente){
                                    $id_cliente = $cliente['id_cliente'];
                                    $contador++;
                                    
                                    $nombre_completo = trim($cliente['nombre'] . ' ' . ($cliente['apellidos'] ?? ''));
                                    $tipo_badge = $cliente['tipo_cliente'] === 'FRECUENTE' ? 'success' : 'secondary';
                                    ?>
                                    <tr>
                                        <td><center><?php echo $contador; ?></center></td>
                                        <td><center><?php echo htmlspecialchars($cliente['codigo_cliente']); ?></center></td>
                                        <td>
                                            <?php echo htmlspecialchars($nombre_completo); ?>
                                            <br>
                                            <small class="badge badge-<?php echo $tipo_badge; ?>">
                                                <?php echo $cliente['tipo_cliente'] ?? 'NUEVO'; ?>
                                            </small>
                                        </td>
                                        <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['email'] ?? 'N/A'); ?></td>
                                        <td>
                                            <small>
                                                <?php echo htmlspecialchars(substr($cliente['direccion'] ?? '', 0, 40)); ?>
                                                <?php if (strlen($cliente['direccion'] ?? '') > 40) echo '...'; ?>
                                            </small>
                                        </td>
                                        <td><center>
                                            <span class="badge badge-info">
                                                <?php echo $cliente['total_pedidos'] ?? 0; ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                S/ <?php echo number_format($cliente['total_gastado'] ?? 0, 2); ?>
                                            </small>
                                        </center></td>
                                        <td>
                                            <center>
                                                <div class="btn-group">
                                                    <a href="show.php?id=<?php echo $id_cliente; ?>" 
                                                       class="btn btn-info btn-sm" 
                                                       title="Ver">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <a href="update.php?id=<?php echo $id_cliente; ?>" 
                                                       class="btn btn-success btn-sm" 
                                                       title="Editar">
                                                        <i class="fa fa-pencil-alt"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            onclick="confirmarEliminar(<?php echo $id_cliente; ?>)"
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
<form id="formEliminar" action="../../controllers/clientes/eliminar.php" method="post" style="display: none;">
    <input type="hidden" name="id_cliente" id="id_cliente_eliminar">
</form>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 10,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Clientes",
                "infoEmpty": "Mostrando 0 a 0 de 0 Clientes",
                "infoFiltered": "(Filtrado de _MAX_ total Clientes)",
                "lengthMenu": "Mostrar _MENU_ Clientes",
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
            text: "¿Desea eliminar este cliente?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('id_cliente_eliminar').value = id;
                document.getElementById('formEliminar').submit();
            }
        });
    }
</script>
