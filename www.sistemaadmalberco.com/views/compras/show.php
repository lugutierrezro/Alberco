<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Obtener ID de la compra
$id_compra_get = $_GET['id'] ?? 0;

if ($id_compra_get <= 0) {
    $_SESSION['error'] = 'ID de compra inválido';
    header('Location: index.php');
    exit;
}

// Obtener datos de la compra
try {
    $sql = "SELECT c.*, 
                   pr.nombre_proveedor, pr.empresa, pr.ruc_dni, pr.telefono, pr.direccion,
                   p.nombre as producto_nombre, p.codigo as producto_codigo, p.unidad_medida,
                   u.username as usuario_nombre
            FROM tb_compras c
            INNER JOIN tb_proveedores pr ON c.id_proveedor = pr.id_proveedor
            INNER JOIN tb_almacen p ON c.id_producto = p.id_producto
            INNER JOIN tb_usuarios u ON c.id_usuario = u.id_usuario
            WHERE c.id_compra = ? AND c.estado_registro = 'ACTIVO'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_compra_get]);
    $compra_dato = $stmt->fetch();
    
    if (!$compra_dato) {
        $_SESSION['error'] = 'Compra no encontrada';
        header('Location: index.php');
        exit;
    }
    
    $total = $compra_dato['cantidad'] * $compra_dato['precio_compra'];
    
} catch (PDOException $e) {
    error_log("Error al obtener compra: " . $e->getMessage());
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
                    <h1 class="m-0">Detalle de Compra</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Compras</a></li>
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
                <!-- Información de la Compra -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h3 class="card-title">
                                <i class="fas fa-shopping-cart"></i> 
                                Compra #<?php echo $id_compra_get; ?>
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-light badge-lg">
                                    <?php echo date('d/m/Y', strtotime($compra_dato['fecha_compra'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Información del Proveedor -->
                            <h5><i class="fas fa-truck"></i> Proveedor</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="200">Nombre:</th>
                                    <td><?php echo htmlspecialchars($compra_dato['nombre_proveedor']); ?></td>
                                </tr>
                                <?php if ($compra_dato['empresa']): ?>
                                <tr>
                                    <th>Empresa:</th>
                                    <td><?php echo htmlspecialchars($compra_dato['empresa']); ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th>RUC/DNI:</th>
                                    <td><?php echo htmlspecialchars($compra_dato['ruc_dni']); ?></td>
                                </tr>
                                <tr>
                                    <th>Teléfono:</th>
                                    <td><?php echo htmlspecialchars($compra_dato['telefono']); ?></td>
                                </tr>
                                <?php if ($compra_dato['direccion']): ?>
                                <tr>
                                    <th>Dirección:</th>
                                    <td><?php echo htmlspecialchars($compra_dato['direccion']); ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>

                            <hr>

                            <!-- Detalle del Producto -->
                            <h5><i class="fas fa-box"></i> Detalle del Producto</h5>
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th><center>Cantidad</center></th>
                                    <th><center>P. Unitario</center></th>
                                    <th><center>Total</center></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><?php echo htmlspecialchars($compra_dato['producto_codigo']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($compra_dato['producto_nombre']); ?></strong>
                                    </td>
                                    <td><center>
                                        <span class="badge badge-info">
                                            <?php echo $compra_dato['cantidad']; ?> <?php echo $compra_dato['unidad_medida']; ?>
                                        </span>
                                    </center></td>
                                    <td><center>S/ <?php echo number_format($compra_dato['precio_compra'], 2); ?></center></td>
                                    <td><center><h4>S/ <?php echo number_format($total, 2); ?></h4></center></td>
                                </tr>
                                </tbody>
                            </table>

                            <?php if ($compra_dato['observaciones']): ?>
                            <div class="alert alert-info mt-3">
                                <strong><i class="fas fa-comment"></i> Observaciones:</strong><br>
                                <?php echo nl2br(htmlspecialchars($compra_dato['observaciones'])); ?>
                            </div>
                            <?php endif; ?>

                            <!-- Acciones -->
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Volver
                                    </a>
                                    
                                    <button type="button" 
                                            class="btn btn-info" 
                                            onclick="window.print()">
                                        <i class="fas fa-print"></i> Imprimir
                                    </button>
                                    
                                    <button type="button" 
                                            class="btn btn-danger" 
                                            onclick="confirmarAnular()">
                                        <i class="fas fa-ban"></i> Anular Compra
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información Adicional -->
                <div class="col-md-4">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-file-invoice"></i> Comprobante</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($compra_dato['comprobante']): ?>
                            <strong>Número:</strong>
                            <p><?php echo htmlspecialchars($compra_dato['comprobante']); ?></p>
                            <?php endif; ?>

                            <?php if ($compra_dato['tipo_comprobante']): ?>
                            <strong>Tipo:</strong>
                            <p>
                                <span class="badge badge-primary">
                                    <?php echo htmlspecialchars($compra_dato['tipo_comprobante']); ?>
                                </span>
                            </p>
                            <?php endif; ?>

                            <?php if ($compra_dato['forma_pago']): ?>
                            <strong>Forma de Pago:</strong>
                            <p>
                                <span class="badge badge-success">
                                    <?php echo htmlspecialchars($compra_dato['forma_pago']); ?>
                                </span>
                            </p>
                            <?php endif; ?>

                            <?php if ($compra_dato['imagen_comprobante']): ?>
                            <strong>Imagen del Comprobante:</strong><br>
                            <a href="<?php echo URL_BASE . '/' . $compra_dato['imagen_comprobante']; ?>" 
                               target="_blank">
                                <img src="<?php echo URL_BASE . '/' . $compra_dato['imagen_comprobante']; ?>" 
                                     class="img-thumbnail mt-2" 
                                     style="max-width: 100%;">
                            </a>
                            <?php endif; ?>

                            <hr>

                            <strong>Registrado por:</strong>
                            <p><?php echo htmlspecialchars($compra_dato['usuario_nombre']); ?></p>

                            <strong>Fecha de Registro:</strong>
                            <p><?php echo date('d/m/Y H:i', strtotime($compra_dato['fyh_creacion'])); ?></p>
                        </div>
                    </div>

                    <!-- Resumen -->
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-calculator"></i> Resumen</h3>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <tr>
                                    <td><strong>Cantidad:</strong></td>
                                    <td class="text-right">
                                        <?php echo $compra_dato['cantidad']; ?> 
                                        <?php echo $compra_dato['unidad_medida']; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Precio Unit.:</strong></td>
                                    <td class="text-right">
                                        S/ <?php echo number_format($compra_dato['precio_compra'], 2); ?>
                                    </td>
                                </tr>
                                <tr class="bg-light">
                                    <td><h5>TOTAL:</h5></td>
                                    <td class="text-right"><h4>S/ <?php echo number_format($total, 2); ?></h4></td>
                                </tr>
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
    <input type="hidden" name="id_compra" value="<?php echo $id_compra_get; ?>">
</form>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
    function confirmarAnular() {
        Swal.fire({
            title: '¿Está seguro?',
            text: "¿Desea anular esta compra? Se descontará el stock del producto.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formAnular').submit();
            }
        });
    }
</script>

<style>
    @media print {
        .btn, .breadcrumb, .card-tools, .sidebar, .main-header, .main-footer {
            display: none !important;
        }
    }
</style>
