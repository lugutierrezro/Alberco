<?php
// 1. INCLUDES Y CONFIGURACIÓN
include_once('services/database/config.php');
include_once('contans/layout/sesion.php');
include_once('contans/layout/parte1.php');

// 2. OBTENER DATOS REALES DE LA BD
try {
    $pdo = getDB();
    
    // KPIs - Contadores
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_usuarios WHERE estado_registro = 'ACTIVO'");
    $total_usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_roles WHERE estado_registro = 'ACTIVO'");
    $total_roles = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_categorias WHERE estado_registro = 'ACTIVO'");
    $total_categorias = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_almacen WHERE estado_registro = 'ACTIVO'");
    $total_productos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_proveedores WHERE estado_registro = 'ACTIVO'");
    $total_proveedores = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_mesas WHERE estado_registro = 'ACTIVO'");
    $total_mesas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Ventas del día
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_ventas WHERE DATE(fecha_venta) = CURDATE()");
    $ventas_hoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) as total FROM tb_ventas WHERE DATE(fecha_venta) = CURDATE() AND estado_venta = 'completada'");
    $ingresos_hoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Pedidos activos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_pedidos WHERE id_estado IN (1,2,3) AND estado_registro = 'ACTIVO'");
    $pedidos_activos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Mesas ocupadas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_mesas WHERE estado = 'ocupada'");
    $mesas_ocupadas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Actividad reciente - Últimas 10 acciones
    $stmt = $pdo->query("
        SELECT 'venta' as tipo, CONCAT('Venta #', nro_venta, ' - S/ ', FORMAT(total, 2)) as descripcion, 
               fecha_venta as fecha, 
               TIMESTAMPDIFF(MINUTE, fecha_venta, NOW()) as minutos
        FROM tb_ventas 
        WHERE estado_venta = 'completada'
        ORDER BY fecha_venta DESC 
        LIMIT 5
    ");
    $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Productos con stock bajo
    $stmt = $pdo->query("
        SELECT nombre, stock, stock_minimo 
        FROM tb_almacen 
        WHERE stock <= stock_minimo 
          AND estado_registro = 'ACTIVO'
        ORDER BY stock ASC 
        LIMIT 5
    ");
    $productos_stock_bajo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    // Si hay error, usar valores por defecto
    $total_usuarios = 0;
    $total_roles = 0;
    $total_categorias = 0;
    $total_productos = 0;
    $total_proveedores = 0;
    $total_mesas = 0;
    $ventas_hoy = 0;
    $ingresos_hoy = 0;
    $pedidos_activos = 0;
    $mesas_ocupadas = 0;
    $actividades = [];
    $productos_stock_bajo = [];
    
    $_SESSION['error'] = "Error al cargar datos: " . $e->getMessage();
}

// Función para formatear tiempo
function tiempoTranscurrido($minutos) {
    if ($minutos < 1) return 'Justo ahora';
    if ($minutos < 60) return "Hace $minutos " . ($minutos == 1 ? 'minuto' : 'minutos');
    $horas = floor($minutos / 60);
    if ($horas < 24) return "Hace $horas " . ($horas == 1 ? 'hora' : 'horas');
    $dias = floor($horas / 24);
    return "Hace $dias " . ($dias == 1 ? 'día' : 'días');
}
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-fire mr-2" style="color: #FF6B35;"></i>Dashboard Principal</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>/">Inicio</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">

            <!-- KPIs Row -->
            <div class="row">
                <!-- Usuarios -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <div class="small-box bg-gradient-primary">
                        <div class="inner">
                            <h3><?php echo $total_usuarios; ?></h3>
                            <p>Usuarios</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="<?php echo URL_BASE; ?>/views/usuarios" class="small-box-footer">
                            Ver más <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Roles -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <div class="small-box bg-gradient-danger">
                        <div class="inner">
                            <h3><?php echo $total_roles; ?></h3>
                            <p>Roles</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <a href="<?php echo URL_BASE; ?>/views/roles" class="small-box-footer">
                            Ver más <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Categorías -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <div class="small-box bg-gradient-warning">
                        <div class="inner">
                            <h3><?php echo $total_categorias; ?></h3>
                            <p>Categorías</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <a href="<?php echo URL_BASE; ?>/views/categorias" class="small-box-footer">
                            Ver más <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Productos -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <div class="small-box bg-gradient-info">
                        <div class="inner">
                            <h3><?php echo $total_productos; ?></h3>
                            <p>Productos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <a href="<?php echo URL_BASE; ?>/views/almacen" class="small-box-footer">
                            Ver más <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Proveedores -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <div class="small-box bg-gradient-success">
                        <div class="inner">
                            <h3><?php echo $total_proveedores; ?></h3>
                            <p>Proveedores</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-truck-loading"></i>
                        </div>
                        <a href="<?php echo URL_BASE; ?>/views/proveedores" class="small-box-footer">
                            Ver más <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Mesas -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <div class="small-box bg-gradient-secondary">
                        <div class="inner">
                            <h3><?php echo $total_mesas; ?></h3>
                            <p>Mesas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chair"></i>
                        </div>
                        <a href="<?php echo URL_BASE; ?>/views/mesas" class="small-box-footer">
                            Ver más <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Segunda Fila - Estadísticas del Día -->
            <div class="row">
                <!-- Ventas Hoy -->
                <div class="col-lg-3 col-md-6">
                    <div class="info-box bg-gradient-orange">
                        <span class="info-box-icon"><i class="fas fa-shopping-cart"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Ventas Hoy</span>
                            <span class="info-box-number"><?php echo $ventas_hoy; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Ingresos Hoy -->
                <div class="col-lg-3 col-md-6">
                    <div class="info-box bg-gradient-success">
                        <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Ingresos Hoy</span>
                            <span class="info-box-number">S/ <?php echo number_format($ingresos_hoy, 2); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Pedidos Activos -->
                <div class="col-lg-3 col-md-6">
                    <div class="info-box bg-gradient-info">
                        <span class="info-box-icon"><i class="fas fa-clipboard-list"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pedidos Activos</span>
                            <span class="info-box-number"><?php echo $pedidos_activos; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Mesas Ocupadas -->
                <div class="col-lg-3 col-md-6">
                    <div class="info-box bg-gradient-warning">
                        <span class="info-box-icon"><i class="fas fa-utensils"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Mesas Ocupadas</span>
                            <span class="info-box-number"><?php echo $mesas_ocupadas; ?> / <?php echo $total_mesas; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tercera Fila - Actividad y Alertas -->
            <div class="row">
                <!-- Actividad Reciente -->
                <div class="col-lg-6">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history mr-2"></i>Actividad Reciente</h3>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <?php if (empty($actividades)): ?>
                                <p class="text-muted text-center">No hay actividad reciente</p>
                            <?php else: ?>
                                <?php foreach ($actividades as $act): ?>
                                    <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                        <div class="mr-3">
                                            <span class="badge badge-success p-2">
                                                <i class="fas fa-dollar-sign"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <strong><?php echo htmlspecialchars($act['descripcion']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo tiempoTranscurrido($act['minutos']); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Stock Bajo -->
                <div class="col-lg-6">
                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>Productos con Stock Bajo</h3>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <?php if (empty($productos_stock_bajo)): ?>
                                <p class="text-success text-center"><i class="fas fa-check-circle"></i> Todo bien - No hay productos con stock bajo</p>
                            <?php else: ?>
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th class="text-center">Stock Actual</th>
                                            <th class="text-center">Stock Mínimo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($productos_stock_bajo as $prod): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                                                <td class="text-center">
                                                    <span class="badge badge-danger"><?php echo $prod['stock']; ?></span>
                                                </td>
                                                <td class="text-center"><?php echo $prod['stock_minimo']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accesos Rápidos -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-bolt mr-2"></i>Accesos Rápidos</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
                                    <a href="<?php echo URL_BASE; ?>/views/pedidos/create.php" class="btn btn-app btn-block">
                                        <i class="fas fa-shopping-cart"></i> Nuevo Pedido
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
                                    <a href="<?php echo URL_BASE; ?>/views/venta" class="btn btn-app btn-block">
                                        <i class="fas fa-cash-register"></i> Ventas
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
                                    <a href="<?php echo URL_BASE; ?>/views/caja" class="btn btn-app btn-block">
                                        <i class="fas fa-money-bill-wave"></i> Caja
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
                                    <a href="<?php echo URL_BASE; ?>/views/reportes" class="btn btn-app btn-block">
                                        <i class="fas fa-chart-bar"></i> Reportes
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
                                    <a href="<?php echo URL_BASE; ?>/views/almacen/create.php" class="btn btn-app btn-block">
                                        <i class="fas fa-plus-circle"></i> Nuevo Producto
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
                                    <a href="<?php echo URL_BASE; ?>/views/mesas" class="btn btn-app btn-block">
                                        <i class="fas fa-utensils"></i> Mesas
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /.container-fluid -->
    </div><!-- /.content -->
</div><!-- /.content-wrapper -->

<style>
.bg-gradient-orange {
    background: linear-gradient(87deg, #FF6B35 0, #F7931E 100%) !important;
    color: white;
}
.btn-app {
    height: 80px;
    background: white;
    border: 1px solid #ddd;
    transition: all 0.3s;
}
.btn-app:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
</style>

<?php 
include_once('contans/layout/mensajes.php'); 
include_once('contans/layout/parte2.php'); 
?>
