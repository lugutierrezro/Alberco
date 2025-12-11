<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');
include ('../../controllers/productos/detalle.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detalle del Producto</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Almacén</a></li>
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
                <!-- Información del Producto -->
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <?php if ($producto_dato['imagen']): ?>
                                    <img src="<?php echo URL_BASE . '/' . $producto_dato['imagen']; ?>" 
                                         class="img-fluid rounded mb-3" 
                                         style="max-height: 200px;">
                                <?php else: ?>
                                    <i class="fas fa-box fa-5x text-primary mb-3"></i>
                                <?php endif; ?>
                            </div>

                            <h3 class="profile-username text-center">
                                <?php echo htmlspecialchars($producto_dato['nombre']); ?>
                                <?php if ($producto_dato['estado_registro'] == 'INACTIVO'): ?>
                                    <br><span class="badge badge-danger mt-2">
                                        <i class="fas fa-trash"></i> ELIMINADO
                                    </span>
                                <?php endif; ?>
                            </h3>

                            <p class="text-muted text-center">
                                Código: <?php echo htmlspecialchars($producto_dato['codigo']); ?>
                            </p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b><i class="fas fa-tag"></i> Categoría</b> 
                                    <span class="float-right">
                                        <span class="badge" style="background-color: <?php echo $producto_dato['categoria_color'] ?? '#007bff'; ?>">
                                            <?php echo htmlspecialchars($producto_dato['nombre_categoria']); ?>
                                        </span>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-boxes"></i> Stock Actual</b> 
                                    <span class="float-right">
                                        <?php
                                        $stock = $producto_dato['stock'];
                                        $stock_min = $producto_dato['stock_minimo'];
                                        $badge = 'success';
                                        if ($stock <= 0) $badge = 'danger';
                                        elseif ($stock <= $stock_min) $badge = 'warning';
                                        ?>
                                        <span class="badge badge-<?php echo $badge; ?> badge-lg">
                                            <?php echo $stock; ?>
                                        </span>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-arrow-down"></i> Stock Mínimo</b> 
                                    <span class="float-right"><?php echo $producto_dato['stock_minimo']; ?></span>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-arrow-up"></i> Stock Máximo</b> 
                                    <span class="float-right"><?php echo $producto_dato['stock_maximo']; ?></span>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-shopping-cart"></i> Precio Compra</b> 
                                    <span class="float-right">S/ <?php echo number_format($producto_dato['precio_compra'], 2); ?></span>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-dollar-sign"></i> Precio Venta</b> 
                                    <span class="float-right">
                                        <strong class="text-success">S/ <?php echo number_format($producto_dato['precio_venta'], 2); ?></strong>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-chart-line"></i> Margen</b> 
                                    <span class="float-right">
                                        <?php
                                        $margen = (($producto_dato['precio_venta'] - $producto_dato['precio_compra']) / $producto_dato['precio_compra']) * 100;
                                        ?>
                                        <span class="badge badge-info"><?php echo number_format($margen, 2); ?>%</span>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-toggle-on"></i> Estado</b> 
                                    <span class="float-right">
                                        <?php if ($producto_dato['disponible_venta']): ?>
                                            <span class="badge badge-success">Disponible</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">No disponible</span>
                                        <?php endif; ?>
                                    </span>
                                </li>
                                <?php if ($producto_dato['requiere_preparacion']): ?>
                                <li class="list-group-item">
                                    <b><i class="fas fa-clock"></i> Tiempo Preparación</b> 
                                    <span class="float-right"><?php echo $producto_dato['tiempo_preparacion']; ?> min</span>
                                </li>
                                <?php endif; ?>
                            </ul>

                            <?php if ($producto_dato['descripcion']): ?>
                            <div class="alert alert-info">
                                <strong>Descripción:</strong><br>
                                <?php echo nl2br(htmlspecialchars($producto_dato['descripcion'])); ?>
                            </div>
                            <?php endif; ?>

                            <a href="index.php" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <a href="update.php?id=<?php echo $producto_dato['id_producto']; ?>" class="btn btn-success btn-block">
                                <i class="fas fa-edit"></i> Editar Producto
                            </a>
                            <button type="button" 
                                    class="btn btn-warning btn-block"
                                    onclick="ajustarStock()">
                                <i class="fas fa-boxes"></i> Ajustar Stock
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Historial y Estadísticas -->
                <div class="col-md-8">
                    <!-- Estadísticas -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-shopping-bag"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Compras</span>
                                    <span class="info-box-number"><?php echo $estadisticas_producto['total_compras'] ?? 0; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-boxes"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cantidad Comprada</span>
                                    <span class="info-box-number"><?php echo $estadisticas_producto['cantidad_total_comprada'] ?? 0; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Precio Prom. Compra</span>
                                    <span class="info-box-number">S/ <?php echo number_format($estadisticas_producto['precio_compra_promedio'] ?? 0, 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historial de Compras -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history"></i> Historial de Compras</h3>
                        </div>
                        <div class="card-body">
                            <?php if (count($historial_compras) > 0): ?>
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($historial_compras as $compra): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($compra['fecha_compra'])); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($compra['nombre_proveedor']); ?>
                                        <?php if ($compra['empresa']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($compra['empresa']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $compra['cantidad']; ?></td>
                                    <td>S/ <?php echo number_format($compra['precio_compra'], 2); ?></td>
                                    <td><strong>S/ <?php echo number_format($compra['cantidad'] * $compra['precio_compra'], 2); ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                No hay historial de compras para este producto.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Información Adicional -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle"></i> Información Adicional</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Fecha de Ingreso:</dt>
                                <dd class="col-sm-8"><?php echo date('d/m/Y', strtotime($producto_dato['fecha_ingreso'])); ?></dd>

                                <dt class="col-sm-4">Última Actualización:</dt>
                                <dd class="col-sm-8">
                                    <?php 
                                    if ($producto_dato['fyh_actualizacion']) {
                                        echo date('d/m/Y H:i', strtotime($producto_dato['fyh_actualizacion']));
                                    } else {
                                        echo 'Sin actualización';
                                    }
                                    ?>
                                </dd>

                                <?php if ($estadisticas_producto['ultima_compra']): ?>
                                <dt class="col-sm-4">Última Compra:</dt>
                                <dd class="col-sm-8">
                                    <?php echo date('d/m/Y', strtotime($estadisticas_producto['ultima_compra'])); ?>
                                </dd>
                                <?php endif; ?>
                            </dl>
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
                    <input type="hidden" name="id_producto" value="<?php echo $producto_dato['id_producto']; ?>">
                    
                    <div class="alert alert-info">
                        <strong>Stock Actual:</strong> <?php echo $producto_dato['stock']; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Nuevo Stock <span class="text-danger">*</span></label>
                        <input type="number" 
                               name="stock" 
                               id="stock" 
                               class="form-control"
                               value="<?php echo $producto_dato['stock']; ?>"
                               required
                               min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="motivo">Motivo del Ajuste <span class="text-danger">*</span></label>
                        <textarea name="motivo" 
                                  id="motivo" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Ejemplo: Ajuste por inventario físico, merma, etc."
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

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
    function ajustarStock() {
        $('#modalAjustarStock').modal('show');
    }
</script>
