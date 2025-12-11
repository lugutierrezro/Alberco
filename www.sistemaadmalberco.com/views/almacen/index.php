<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');
include ('../../controllers/productos/listar.php'); 
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Gestión de Almacén</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item active">Almacén</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <!-- Filtros rápidos -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="btn-group">
                        <a href="index.php" class="btn btn-default">
                            <i class="fas fa-boxes"></i> Todos
                        </a>
                        <a href="index.php?disponibles=1" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Disponibles
                        </a>
                        <a href="stock_bajo.php" class="btn btn-warning">
                            <i class="fas fa-exclamation-triangle"></i> Stock Bajo
                        </a>
                        <a href="index.php?categoria=<?php echo $_GET['categoria'] ?? ''; ?>" class="btn btn-info">
                            <i class="fas fa-filter"></i> Por Categoría
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Productos en Almacén</h3>
                            <div class="card-tools">
                                <a href="create.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Nuevo Producto
                                </a>
                                <?php if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1): ?>
                                <a href="papelera.php" class="btn btn-danger btn-sm" title="Ver productos eliminados">
                                    <i class="fas fa-trash-restore"></i> Papelera
                                </a>
                                <?php endif; ?>
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
                                    <th><center>Código</center></th>
                                    <th><center>Producto</center></th>
                                    <th><center>Categoría</center></th>
                                    <th><center>Stock</center></th>
                                    <th><center>P. Compra</center></th>
                                    <th><center>P. Venta</center></th>
                                    <th><center>Estado</center></th>
                                    <th><center>Acciones</center></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador = 0;
                                foreach ($productos_datos as $producto){
                                    $id_producto = $producto['id_producto'];
                                    $contador++;
                                    
                                    // Determinar estado de stock
                                    $stock_actual = $producto['stock'];
                                    $stock_minimo = $producto['stock_minimo'];
                                    $badge_stock = 'success';
                                    $icono_stock = 'fa-check-circle';
                                    
                                    if ($stock_actual <= 0) {
                                        $badge_stock = 'danger';
                                        $icono_stock = 'fa-times-circle';
                                    } elseif ($stock_actual <= $stock_minimo) {
                                        $badge_stock = 'warning';
                                        $icono_stock = 'fa-exclamation-triangle';
                                    }
                                    
                                    // Badge de disponibilidad
                                    $badge_disponible = $producto['disponible_venta'] ? 'success' : 'secondary';
                                    $texto_disponible = $producto['disponible_venta'] ? 'Disponible' : 'No disponible';
                                    ?>
                                    <tr>
                                        <td><center><?php echo $contador; ?></center></td>
                                        <td><center><?php echo htmlspecialchars($producto['codigo']); ?></center></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($producto['imagen']): ?>
                                                    <img src="<?php echo URL_BASE . '/' . $producto['imagen']; ?>" 
                                                         class="img-thumbnail mr-2" 
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                                    <?php if ($producto['requiere_preparacion']): ?>
                                                        <br><small class="text-muted">
                                                            <i class="fas fa-clock"></i> 
                                                            <?php echo $producto['tiempo_preparacion']; ?> min
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><center>
                                            <span class="badge" style="background-color: <?php echo $producto['categoria_color'] ?? '#007bff'; ?>">
                                                <?php echo htmlspecialchars($producto['nombre_categoria']); ?>
                                            </span>
                                        </center></td>
                                        <td><center>
                                            <span class="badge badge-<?php echo $badge_stock; ?>">
                                                <i class="fas <?php echo $icono_stock; ?>"></i>
                                                <?php echo $stock_actual; ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">Min: <?php echo $stock_minimo; ?></small>
                                        </center></td>
                                        <td>S/ <?php echo number_format($producto['precio_compra'], 2); ?></td>
                                        <td><strong>S/ <?php echo number_format($producto['precio_venta'], 2); ?></strong></td>
                                        <td><center>
                                            <span class="badge badge-<?php echo $badge_disponible; ?>">
                                                <?php echo $texto_disponible; ?>
                                            </span>
                                        </center></td>
                                        <td>
                                            <center>
                                                <div class="btn-group">
                                                    <a href="show.php?id=<?php echo $id_producto; ?>" 
                                                       class="btn btn-info btn-sm" 
                                                       title="Ver">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <a href="update.php?id=<?php echo $id_producto; ?>" 
                                                       class="btn btn-success btn-sm" 
                                                       title="Editar">
                                                        <i class="fa fa-pencil-alt"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-warning btn-sm" 
                                                            onclick="ajustarStock(<?php echo $id_producto; ?>, '<?php echo addslashes($producto['nombre']); ?>', <?php echo $stock_actual; ?>)"
                                                            title="Ajustar Stock">
                                                        <i class="fa fa-boxes"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            onclick="confirmarEliminar(<?php echo $id_producto; ?>)"
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

<!-- Modal Ajustar Stock -->
<div class="modal fade" id="modalAjustarStock" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../../controllers/producto/ajustar_stock.php" method="post">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Ajustar Stock del Producto</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_producto" id="id_producto_stock">
                    
                    <div class="alert alert-info">
                        <strong>Producto:</strong> <span id="nombre_producto_stock"></span><br>
                        <strong>Stock Actual:</strong> <span id="stock_actual_display"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Nuevo Stock <span class="text-danger">*</span></label>
                        <input type="number" 
                               name="stock" 
                               id="stock" 
                               class="form-control"
                               required
                               min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="motivo">Motivo del Ajuste <span class="text-danger">*</span></label>
                        <textarea name="motivo" 
                                  id="motivo" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Ejemplo: Ajuste por inventario físico"
                                  required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Ajustar Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminar -->
<form id="formEliminar" action="../../controllers/productos/eliminar.php" method="post" style="display: none;">
    <input type="hidden" name="id_producto" id="id_producto_eliminar">
</form>

<?php include ('../../contans/layout/parte2.php'); ?>
<?php include ('../../contans/layout/mensajes.php'); ?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 25,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Productos",
                "infoEmpty": "Mostrando 0 a 0 de 0 Productos",
                "infoFiltered": "(Filtrado de _MAX_ total Productos)",
                "lengthMenu": "Mostrar _MENU_ Productos",
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

    function ajustarStock(id, nombre, stockActual) {
        document.getElementById('id_producto_stock').value = id;
        document.getElementById('nombre_producto_stock').textContent = nombre;
        document.getElementById('stock_actual_display').textContent = stockActual;
        document.getElementById('stock').value = stockActual;
        $('#modalAjustarStock').modal('show');
    }

    function confirmarEliminar(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "¿Desea eliminar este producto?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('id_producto_eliminar').value = id;
                document.getElementById('formEliminar').submit();
            }
        });
    }
</script>
