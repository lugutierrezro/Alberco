<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');
include ('../../controllers/categorias/listar.php'); 
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Gestión de Categorías</h1>
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
                            <h3 class="card-title">Categorías Registradas</h3>
                            <div class="card-tools">
                                <a href="create.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Nueva Categoría
                                </a>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info" onclick="verTarjetas()">
                                            <i class="fas fa-th"></i> Vista Tarjetas
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="verTabla()">
                                            <i class="fas fa-list"></i> Vista Tabla
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Vista de Tarjetas -->
                            <div id="vistaTarjetas" class="row">
                                <?php
                                foreach ($categorias_datos as $categoria){
                                    $id_categoria = $categoria['id_categoria'];
                                    $nombre = $categoria['nombre_categoria'];
                                    $descripcion = $categoria['descripcion'] ?? '';
                                    $color = $categoria['color'] ?? '#007bff';
                                    $icono = $categoria['icono'] ?? 'fas fa-tag';
                                    $imagen = $categoria['imagen'] ?? 'assets/img/default-category.png';
                                    $total_productos = $categoria['total_productos'] ?? 0;
                                    ?>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="card" style="border-top: 3px solid <?php echo $color; ?>">
                                            <div class="card-body text-center">
                                                <?php if ($imagen): ?>
                                                    <img src="<?php echo URL_BASE . '/' . $imagen; ?>" 
                                                         alt="<?php echo htmlspecialchars($nombre); ?>"
                                                         class="img-fluid mb-2"
                                                         style="max-height: 100px; object-fit: cover;">
                                                <?php else: ?>
                                                    <i class="<?php echo $icono; ?> fa-3x mb-2" 
                                                       style="color: <?php echo $color; ?>"></i>
                                                <?php endif; ?>
                                                
                                                <h5><?php echo htmlspecialchars($nombre); ?></h5>
                                                <p class="text-muted small"><?php echo htmlspecialchars($descripcion); ?></p>
                                                <span class="badge badge-secondary">
                                                    <?php echo $total_productos; ?> producto(s)
                                                </span>
                                                
                                                <div class="mt-2">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="update.php?id=<?php echo $id_categoria; ?>" 
                                                           class="btn btn-success" 
                                                           title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-danger" 
                                                                onclick="confirmarEliminar(<?php echo $id_categoria; ?>)"
                                                                title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>

                            <!-- Vista de Tabla -->
                            <div id="vistaTabla" style="display: none;">
                                <table id="example1" class="table table-bordered table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th><center>Nro</center></th>
                                        <th><center>Categoría</center></th>
                                        <th><center>Descripción</center></th>
                                        <th><center>Productos</center></th>
                                        <th><center>Orden</center></th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $contador = 0;
                                    foreach ($categorias_datos as $categoria){
                                        $id_categoria = $categoria['id_categoria'];
                                        $contador++;
                                        ?>
                                        <tr>
                                            <td><center><?php echo $contador; ?></center></td>
                                            <td>
                                                <i class="<?php echo $categoria['icono'] ?? 'fas fa-tag'; ?>" 
                                                   style="color: <?php echo $categoria['color'] ?? '#007bff'; ?>"></i>
                                                <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($categoria['descripcion'] ?? ''); ?></td>
                                            <td><center>
                                                <span class="badge badge-info">
                                                    <?php echo $categoria['total_productos'] ?? 0; ?>
                                                </span>
                                            </center></td>
                                            <td><center><?php echo $categoria['orden'] ?? 0; ?></center></td>
                                            <td>
                                                    <div class="btn-group">
                                                        <a href="update.php?id=<?php echo $id_categoria; ?>" 
                                                           class="btn btn-success btn-sm" 
                                                           title="Editar">
                                                            <i class="fa fa-pencil-alt"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-danger btn-sm" 
                                                                onclick="confirmarEliminar(<?php echo $id_categoria; ?>)"
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
</div>

<!-- Formulario oculto para eliminar -->
<form id="formEliminar" action="../../controllers/categorias/eliminar.php" method="post" style="display: none;">
    <input type="hidden" name="id_categoria" id="id_categoria_eliminar">
</form>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 10,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Categorías",
                "infoEmpty": "Mostrando 0 a 0 de 0 Categorías",
                "infoFiltered": "(Filtrado de _MAX_ total Categorías)",
                "lengthMenu": "Mostrar _MENU_ Categorías",
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

    function verTarjetas() {
        document.getElementById('vistaTarjetas').style.display = 'block';
        document.getElementById('vistaTabla').style.display = 'none';
    }

    function verTabla() {
        document.getElementById('vistaTarjetas').style.display = 'none';
        document.getElementById('vistaTabla').style.display = 'block';
    }

    function confirmarEliminar(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "¿Desea eliminar esta categoría?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('id_categoria_eliminar').value = id;
                document.getElementById('formEliminar').submit();
            }
        });
    }
</script>
