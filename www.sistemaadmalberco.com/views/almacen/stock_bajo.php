<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');
include ('../../controllers/productos/stock_bajo.php'); 
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Productos con Stock Bajo</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Almacén</a></li>
                        <li class="breadcrumb-item active">Stock Bajo</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="alert alert-warning">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> Alerta de Inventario</h5>
                        Se encontraron <strong><?php echo count($productos_stock_bajo); ?></strong> producto(s) 
                        con stock por debajo del mínimo establecido.
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Productos que Requieren Reabastecimiento</h3>
                            <div class="card-tools">
                                <a href="index.php" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Volver al Almacén
                                </a>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <?php if (count($productos_stock_bajo) > 0): ?>
                            <table id="example1" class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th><center>Nro</center></th>
                                    <th><center>Criticidad</center></th>
                                    <th><center>Código</center></th>
                                    <th><center>Producto</center></th>
                                    <th><center>Categoría</center></th>
                                    <th><center>Stock Actual</center></th>
                                    <th><center>Stock Mínimo</center></th>
                                    <th><center>Diferencia</center></th>
                                    <th><center>Acciones</center></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador = 0;
                                foreach ($productos_stock_bajo as $producto){
                                    $id_producto = $producto['id_producto'];
                                    $contador++;
                                    
                                    $stock = $producto['stock'];
                                    $stock_min = $producto['stock_minimo'];
                                    $diferencia = $stock_min - $stock;
                                    ?>
                                    <tr>
                                        <td><center><?php echo $contador; ?></center></td>
                                        <td><center>
                                            <span class="badge badge-<?php echo $producto['criticidad_color']; ?>">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <?php echo $producto['criticidad']; ?>
                                            </span>
                                        </center></td>
                                        <td><center><?php echo htmlspecialchars($producto['codigo']); ?></center></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($producto['imagen']): ?>
                                                    <img src="<?php echo URL_BASE . '/' . $producto['imagen']; ?>" 
                                                         class="img-thumbnail mr-2" 
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                <?php endif; ?>
                                                <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                            </div>
                                        </td>
                                        <td><center>
                                            <span class="badge" style="background-color: <?php echo $producto['categoria_color'] ?? '#007bff'; ?>">
                                                <?php echo htmlspecialchars($producto['nombre_categoria']); ?>
                                            </span>
                                        </center></td>
                                        <td><center>
                                            <?php if ($stock <= 0): ?>
                                                <span class="badge badge-danger badge-lg">
                                                    <i class="fas fa-times-circle"></i> AGOTADO
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-warning badge-lg">
                                                    <?php echo $stock; ?>
                                                </span>
                                            <?php endif; ?>
                                        </center></td>
                                        <td><center><?php echo $stock_min; ?></center></td>
                                        <td><center>
                                            <span class="badge badge-danger">
                                                <i class="fas fa-arrow-down"></i> -<?php echo $diferencia; ?>
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
                                                    <button type="button" 
                                                            class="btn btn-primary btn-sm" 
                                                            onclick="generarOrdenCompra(<?php echo $id_producto; ?>, '<?php echo addslashes($producto['nombre']); ?>', <?php echo $diferencia; ?>)"
                                                            title="Generar Orden de Compra">
                                                        <i class="fa fa-shopping-cart"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-warning btn-sm" 
                                                            onclick="ajustarStock(<?php echo $id_producto; ?>, '<?php echo addslashes($producto['nombre']); ?>', <?php echo $stock; ?>)"
                                                            title="Ajustar Stock">
                                                        <i class="fa fa-boxes"></i>
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
                            <?php else: ?>
                            <div class="alert alert-success">
                                <h5><i class="icon fas fa-check"></i> ¡Excelente!</h5>
                                Todos los productos tienen stock suficiente.
                            </div>
                            <?php endif; ?>
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
                                  placeholder="Ejemplo: Reabastecimiento de emergencia"
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

<!-- Modal Generar Orden de Compra -->
<div class="modal fade" id="modalOrdenCompra" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Generar Orden de Compra</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Producto:</strong> <span id="nombre_producto_orden"></span><br>
                    <strong>Cantidad Sugerida:</strong> <span id="cantidad_sugerida"></span>
                </div>
                <p>Será redirigido a la página de compras para registrar la orden.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <a href="#" id="btn_ir_compras" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Ir a Compras
                </a>
            </div>
        </div>
    </div>
</div>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 25,
            "order": [[1, "desc"]],
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
                    extend: 'excel',
                    title: 'Productos Stock Bajo - ' + new Date().toLocaleDateString()
                },{
                    text: 'Imprimir',
                    extend: 'print'
                }]
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

    function generarOrdenCompra(id, nombre, cantidadSugerida) {
        document.getElementById('nombre_producto_orden').textContent = nombre;
        document.getElementById('cantidad_sugerida').textContent = cantidadSugerida + ' unidades';
        document.getElementById('btn_ir_compras').href = '../compras/create.php?id_producto=' + id + '&cantidad=' + cantidadSugerida;
        $('#modalOrdenCompra').modal('show');
    }
</script>
