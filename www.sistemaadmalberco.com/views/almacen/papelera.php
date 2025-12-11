<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');

// SOLO ADMINISTRADORES
if (!isset($_SESSION['user_role_id']) || $_SESSION['user_role_id'] != 1) {
    $_SESSION['error'] = 'Acceso denegado. Solo administradores pueden acceder a esta sección.';
    header('Location: ' . URL_BASE . '/views/almacen/');
    exit;
}

include ('../../contans/layout/parte1.php');

// Obtener productos eliminados (INACTIVOS)
try {
    $sql = "SELECT 
                a.*,
                c.nombre_categoria,
                c.icono as categoria_icono,
                c.color as categoria_color,
                u.username as nombres_usuario
            FROM tb_almacen a
            LEFT JOIN tb_categorias c ON a.id_categoria = c.id_categoria
            LEFT JOIN tb_usuarios u ON a.id_usuario = u.id_usuario
            WHERE a.estado_registro = 'INACTIVO'
            ORDER BY a.fyh_actualizacion DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $productos_eliminados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al obtener productos eliminados: " . $e->getMessage());
    $productos_eliminados = [];
}
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-trash-restore"></i> Papelera de Productos
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Almacén</a></li>
                        <li class="breadcrumb-item active">Papelera</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            
            <!-- Alert de permisos -->
            <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5><i class="icon fas fa-exclamation-triangle"></i> Sección Exclusiva de Administradores</h5>
                Solo los administradores pueden ver y restaurar productos eliminados.
            </div>
            
            <!-- Estadísticas -->
            <div class="row">
                <div class="col-md-3">
                    <div class="info-box bg-danger">
                        <span class="info-box-icon"><i class="fas fa-trash"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Productos Eliminados</span>
                            <span class="info-box-number"><?php echo count($productos_eliminados); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-undo"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Acciones Disponibles</span>
                            <span class="info-box-number">Restaurar</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de productos eliminados -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-danger card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list"></i> Productos Eliminados
                            </h3>
                            <div class="card-tools">
                                <a href="index.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Volver al Almacén
                                </a>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <?php if (count($productos_eliminados) > 0): ?>
                            <table id="tablaEliminados" class="table table-bordered table-striped table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Nro</th>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th>Categoría</th>
                                        <th>Stock</th>
                                        <th>Precio Venta</th>
                                        <th>Eliminado por</th>
                                        <th>Fecha Eliminación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $contador = 0;
                                    foreach ($productos_eliminados as $producto): 
                                        $contador++;
                                    ?>
                                    <tr>
                                        <td><center><?php echo $contador; ?></center></td>
                                        <td><center><code><?php echo htmlspecialchars($producto['codigo']); ?></code></center></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($producto['imagen']): ?>
                                                    <img src="<?php echo URL_BASE . '/' . $producto['imagen']; ?>" 
                                                         class="img-thumbnail mr-2" 
                                                         style="width: 40px; height: 40px; object-fit: cover; opacity: 0.6;">
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                                    <?php if ($producto['descripcion']): ?>
                                                        <br><small class="text-muted"><?php echo substr(htmlspecialchars($producto['descripcion']), 0, 50); ?>...</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><center>
                                            <span class="badge" style="background-color: <?php echo $producto['categoria_color'] ?? '#6c757d'; ?>">
                                                <?php echo htmlspecialchars($producto['nombre_categoria']); ?>
                                            </span>
                                        </center></td>
                                        <td><center>
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-ban"></i> <?php echo $producto['stock']; ?>
                                            </span>
                                        </center></td>
                                        <td>S/ <?php echo number_format($producto['precio_venta'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($producto['nombres_usuario'] ?? 'Sistema'); ?></td>
                                        <td>
                                            <small>
                                                <?php 
                                                if ($producto['fyh_actualizacion']) {
                                                    echo date('d/m/Y H:i', strtotime($producto['fyh_actualizacion']));
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </small>
                                        </td>
                                        <td>
                                            <center>
                                                <button type="button" 
                                                        class="btn btn-success btn-sm" 
                                                        onclick="confirmarRestaurar(<?php echo $producto['id_producto']; ?>, '<?php echo addslashes($producto['nombre']); ?>')"
                                                        title="Restaurar producto">
                                                    <i class="fas fa-undo"></i> Restaurar
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-info btn-sm" 
                                                        onclick="verDetalle(<?php echo $producto['id_producto']; ?>)"
                                                        title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </center>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <div class="alert alert-success">
                                <h5><i class="icon fas fa-check"></i> ¡Papelera vacía!</h5>
                                No hay productos eliminados. Todos los productos están activos.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Formulario oculto para restaurar -->
<form id="formRestaurar" action="../../controllers/productos/restaurar.php" method="post" style="display: none;">
    <input type="hidden" name="id_producto" id="id_producto_restaurar">
</form>

<?php include ('../../contans/layout/parte2.php'); ?>
<?php include ('../../contans/layout/mensajes.php'); ?>

<script>
    $(function () {
        $("#tablaEliminados").DataTable({
            "pageLength": 25,
            "language": {
                "emptyTable": "No hay productos eliminados",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ productos",
                "infoEmpty": "Mostrando 0 a 0 de 0 productos",
                "infoFiltered": "(Filtrado de _MAX_ total)",
                "lengthMenu": "Mostrar _MENU_ registros",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron resultados",
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
            "order": [[7, 'desc']], // Ordenar por fecha de eliminación
            buttons: [{
                extend: 'collection',
                text: 'Exportar',
                buttons: ['copy', 'excel', 'csv', 'pdf', 'print']
            }],
        }).buttons().container().appendTo('#tablaEliminados_wrapper .col-md-6:eq(0)');
    });

    function confirmarRestaurar(id, nombre) {
        Swal.fire({
            title: '¿Restaurar producto?',
            html: `¿Desea restaurar el producto <strong>${nombre}</strong>?<br><br>
                   <small class="text-muted">El producto volverá a estar disponible en el almacén.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-undo"></i> Sí, restaurar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('id_producto_restaurar').value = id;
                document.getElementById('formRestaurar').submit();
            }
        });
    }
    
    function verDetalle(id) {
        window.open('show.php?id=' + id, '_blank');
    }
</script>

<style>
    .info-box {
        min-height: 90px;
    }
    
    /* Efecto de opacidad para productos eliminados */
    #tablaEliminados tbody tr {
        opacity: 0.8;
    }
    
    #tablaEliminados tbody tr:hover {
        opacity: 1;
        background-color: #f8f9fa;
    }
</style>
