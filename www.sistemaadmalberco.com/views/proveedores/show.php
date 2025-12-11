<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Obtener ID del proveedor
$id_proveedor_get = $_GET['id'] ?? 0;

if ($id_proveedor_get <= 0) {
    $_SESSION['error'] = 'ID de proveedor inválido';
    header('Location: index.php');
    exit;
}

// Obtener datos del proveedor
try {
    $sql = "SELECT * FROM tb_proveedores WHERE id_proveedor = ? AND estado_registro = 'ACTIVO'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_proveedor_get]);
    $proveedor_dato = $stmt->fetch();
    
    if (!$proveedor_dato) {
        $_SESSION['error'] = 'Proveedor no encontrado';
        header('Location: index.php');
        exit;
    }
    
    // Obtener historial de compras
    $sqlCompras = "SELECT c.*, p.nombre as producto_nombre, p.codigo as producto_codigo
                   FROM tb_compras c
                   INNER JOIN tb_almacen p ON c.id_producto = p.id_producto
                   WHERE c.id_proveedor = ? AND c.estado_registro = 'ACTIVO'
                   ORDER BY c.fecha_compra DESC
                   LIMIT 20";
    
    $stmtCompras = $pdo->prepare($sqlCompras);
    $stmtCompras->execute([$id_proveedor_get]);
    $historial_compras = $stmtCompras->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener estadísticas
    $sqlStats = "SELECT 
                    COUNT(*) as total_compras,
                    SUM(cantidad) as cantidad_total,
                    SUM(cantidad * precio_compra) as monto_total,
                    AVG(precio_compra) as precio_promedio,
                    MAX(fecha_compra) as ultima_compra
                 FROM tb_compras
                 WHERE id_proveedor = ? AND estado_registro = 'ACTIVO'";
    
    $stmtStats = $pdo->prepare($sqlStats);
    $stmtStats->execute([$id_proveedor_get]);
    $estadisticas = $stmtStats->fetch();
    
    // Productos más comprados
    $sqlTopProductos = "SELECT 
                           p.nombre as producto_nombre,
                           p.codigo as producto_codigo,
                           COUNT(*) as veces_comprado,
                           SUM(c.cantidad) as cantidad_total,
                           SUM(c.cantidad * c.precio_compra) as monto_total
                        FROM tb_compras c
                        INNER JOIN tb_almacen p ON c.id_producto = p.id_producto
                        WHERE c.id_proveedor = ? AND c.estado_registro = 'ACTIVO'
                        GROUP BY c.id_producto
                        ORDER BY cantidad_total DESC
                        LIMIT 5";
    
    $stmtTopProductos = $pdo->prepare($sqlTopProductos);
    $stmtTopProductos->execute([$id_proveedor_get]);
    $top_productos = $stmtTopProductos->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al obtener proveedor: " . $e->getMessage());
    $_SESSION['error'] = 'Error al cargar datos';
    header('Location: index.php');
    exit;
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detalle del Proveedor</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Proveedores</a></li>
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
                <!-- Información del Proveedor -->
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <i class="fas fa-truck fa-5x text-primary mb-3"></i>
                            </div>

                            <h3 class="profile-username text-center">
                                <?php echo htmlspecialchars($proveedor_dato['nombre_proveedor']); ?>
                            </h3>

                            <?php if ($proveedor_dato['empresa']): ?>
                            <p class="text-muted text-center">
                                <?php echo htmlspecialchars($proveedor_dato['empresa']); ?>
                            </p>
                            <?php endif; ?>

                            <p class="text-muted text-center">
                                <?php echo htmlspecialchars($proveedor_dato['codigo_proveedor']); ?>
                            </p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b><i class="fas fa-id-card"></i> RUC/DNI</b> 
                                    <span class="float-right">
                                        <?php echo htmlspecialchars($proveedor_dato['ruc']); ?>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-phone"></i> Teléfono</b> 
                                    <span class="float-right">
                                        <?php echo htmlspecialchars($proveedor_dato['telefono']); ?>
                                    </span>
                                </li>
                                <?php if ($proveedor_dato['celular']): ?>
                                <li class="list-group-item">
                                    <b><i class="fas fa-mobile-alt"></i> Celular</b> 
                                    <span class="float-right">
                                        <?php echo htmlspecialchars($proveedor_dato['celular']); ?>
                                    </span>
                                </li>
                                <?php endif; ?>
                                <?php if ($proveedor_dato['email']): ?>
                                <li class="list-group-item">
                                    <b><i class="fas fa-envelope"></i> Email</b> 
                                    <span class="float-right">
                                        <?php echo htmlspecialchars($proveedor_dato['email']); ?>
                                    </span>
                                </li>
                                <?php endif; ?>
                                <?php if ($proveedor_dato['direccion']): ?>
                                <li class="list-group-item">
                                    <b><i class="fas fa-globe"></i> Sitio Web</b> 
                                    <span class="float-right">
                                        <a href="<?php echo htmlspecialchars($proveedor_dato['direccion']); ?>" 
                                           target="_blank">
                                            Ver sitio
                                        </a>
                                    </span>
                                </li>
                                <?php endif; ?>
                                <li class="list-group-item">
                                    <b><i class="fas fa-shopping-cart"></i> Total Compras</b> 
                                    <span class="float-right">
                                        <span class="badge badge-primary">
                                            <?php echo $estadisticas['total_compras'] ?? 0; ?>
                                        </span>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-dollar-sign"></i> Monto Total</b> 
                                    <span class="float-right">
                                        <strong class="text-success">
                                            S/ <?php echo number_format($estadisticas['monto_total'] ?? 0, 2); ?>
                                        </strong>
                                    </span>
                                </li>
                                <?php if ($estadisticas['ultima_compra']): ?>
                                <li class="list-group-item">
                                    <b><i class="fas fa-calendar"></i> Última Compra</b> 
                                    <span class="float-right">
                                        <?php echo date('d/m/Y', strtotime($estadisticas['ultima_compra'])); ?>
                                    </span>
                                </li>
                                <?php endif; ?>
                            </ul>

                            <a href="index.php" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <a href="update.php?id=<?php echo $id_proveedor_get; ?>" class="btn btn-success btn-block">
                                <i class="fas fa-edit"></i> Editar Proveedor
                            </a>
                            <a href="../compras/create.php?id_proveedor=<?php echo $id_proveedor_get; ?>" class="btn btn-primary btn-block">
                                <i class="fas fa-shopping-cart"></i> Nueva Compra
                            </a>
                        </div>
                    </div>

                    <!-- Información Adicional -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle"></i> Información Adicional</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($proveedor_dato['direccion']): ?>
                            <strong><i class="fas fa-map-marker-alt mr-1"></i> Dirección</strong>
                            <p class="text-muted">
                                <?php echo htmlspecialchars($proveedor_dato['direccion']); ?>
                            </p>
                            <?php endif; ?>

                            <?php if ($proveedor_dato['contacto_nombre']): ?>
                            <strong><i class="fas fa-user mr-1"></i> Contacto</strong>
                            <p class="text-muted">
                                <?php echo htmlspecialchars($proveedor_dato['telefono']); ?>
                                <?php if ($proveedor_dato['telefono']): ?>
                                    <br><i class="fas fa-phone"></i> <?php echo htmlspecialchars($proveedor_dato['telefono']); ?>
                                <?php endif; ?>
                            </p>
                            <?php endif; ?>

                            <strong><i class="fas fa-credit-card mr-1"></i> Condición de Pago</strong>
                            <p class="text-muted">
                                <?php echo str_replace('_', ' ', $proveedor_dato['condicion_pago'] ?? 'CONTADO'); ?>
                            </p>

                            <strong><i class="fas fa-money-bill-wave mr-1"></i> Método de Pago Preferido</strong>
                            <p class="text-muted">
                                <?php echo str_replace('_', ' ', $proveedor_dato['metodo_pago_preferido'] ?? 'EFECTIVO'); ?>
                            </p>

        
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
                                    <span class="info-box-number"><?php echo $estadisticas['total_compras'] ?? 0; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-boxes"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cantidad Total</span>
                                    <span class="info-box-number"><?php echo number_format($estadisticas['cantidad_total'] ?? 0); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Precio Promedio</span>
                                    <span class="info-box-number">S/ <?php echo number_format($estadisticas['precio_promedio'] ?? 0, 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Productos Más Comprados -->
                    <?php if (count($top_productos) > 0): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-star"></i> Productos Más Comprados</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-sm">
                                <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th><center>Veces Comprado</center></th>
                                    <th><center>Cantidad Total</center></th>
                                    <th><center>Monto Total</center></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($top_productos as $prod): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($prod['producto_nombre']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($prod['producto_codigo']); ?></small>
                                    </td>
                                    <td><center>
                                        <span class="badge badge-primary"><?php echo $prod['veces_comprado']; ?></span>
                                    </center></td>
                                    <td><center><?php echo number_format($prod['cantidad_total']); ?></center></td>
                                    <td><center><strong>S/ <?php echo number_format($prod['monto_total'], 2); ?></strong></center></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

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
                                    <th>Producto</th>
                                    <th><center>Cantidad</center></th>
                                    <th><center>Precio Unit.</center></th>
                                    <th><center>Total</center></th>
                                    <th><center>Comprobante</center></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($historial_compras as $compra): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($compra['fecha_compra'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($compra['producto_nombre']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($compra['producto_codigo']); ?></small>
                                    </td>
                                    <td><center><?php echo $compra['cantidad']; ?></center></td>
                                    <td><center>S/ <?php echo number_format($compra['precio_compra'], 2); ?></center></td>
                                    <td><center><strong>S/ <?php echo number_format($compra['cantidad'] * $compra['precio_compra'], 2); ?></strong></center></td>
                                    <td><center>
                                        <?php if ($compra['comprobante']): ?>
                                            <span class="badge badge-success">
                                                <?php echo htmlspecialchars($compra['comprobante']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Sin comprobante</span>
                                        <?php endif; ?>
                                    </center></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                No hay historial de compras para este proveedor.
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
