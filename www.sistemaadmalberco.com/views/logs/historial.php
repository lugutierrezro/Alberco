<?php 
require_once __DIR__ . '/../../controllers/logs/historial.php';
include('../../contans/layout/parte1.php');
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-history"></i> Historial de Actividad</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../../dashboard.php">Inicio</a></li>
                        <li class="breadcrumb-item active">Historial</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            
            <!-- Estadísticas -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= number_format($estadisticas['total']) ?></h3>
                            <p>Total Actividades</p>
                        </div>
                        <div class="icon"><i class="fas fa-list"></i></div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= count($estadisticas['por_usuario']) ?></h3>
                            <p>Usuarios Activos</p>
                        </div>
                        <div class="icon"><i class="fas fa-users"></i></div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <?php
                            $errores = array_filter($estadisticas['por_nivel'], function($n) {
                                return $n['nivel'] === 'error' || $n['nivel'] === 'critical';
                            });
                            $total_errores = array_sum(array_column($errores, 'total'));
                            ?>
                            <h3><?= $total_errores ?></h3>
                            <p>Errores/Críticos</p>
                        </div>
                        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= count($estadisticas['por_modulo']) ?></h3>
                            <p>Módulos Activos</p>
                        </div>
                        <div class="icon"><i class="fas fa-cube"></i></div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card collapsed-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <form method="GET" action="" id="formFiltros">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Usuario</label>
                                    <select name="id_usuario" class="form-control">
                                        <option value="">Todos</option>
                                        <?php foreach ($usuarios as $u): ?>
                                            <option value="<?= $u['id_usuario'] ?>" <?= (isset($_GET['id_usuario']) && $_GET['id_usuario'] == $u['id_usuario']) ? 'selected' : '' ?>>
                                                <?= $u['username'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Módulo</label>
                                    <select name="modulo" class="form-control">
                                        <option value="">Todos</option>
                                        <?php foreach ($modulos as $m): ?>
                                            <option value="<?= $m ?>" <?= (isset($_GET['modulo']) && $_GET['modulo'] == $m) ? 'selected' : '' ?>>
                                                <?= ucfirst($m) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Acción</label>
                                    <select name="accion" class="form-control">
                                        <option value="">Todas</option>
                                        <?php foreach ($acciones as $a): ?>
                                            <option value="<?= $a ?>" <?= (isset($_GET['accion']) && $_GET['accion'] == $a) ? 'selected' : '' ?>>
                                                <?= $a ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Nivel</label>
                                    <select name="nivel" class="form-control">
                                        <option value="">Todos</option>
                                        <option value="info" <?= (isset($_GET['nivel']) && $_GET['nivel'] == 'info') ? 'selected' : '' ?>>Info</option>
                                        <option value="warning" <?= (isset($_GET['nivel']) && $_GET['nivel'] == 'warning') ? 'selected' : '' ?>>Warning</option>
                                        <option value="error" <?= (isset($_GET['nivel']) && $_GET['nivel'] == 'error') ? 'selected' : '' ?>>Error</option>
                                        <option value="critical" <?= (isset($_GET['nivel']) && $_GET['nivel'] == 'critical') ? 'selected' : '' ?>>Critical</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Búsqueda</label>
                                    <input type="text" name="busqueda" class="form-control" 
                                           placeholder="Buscar en descripción..." 
                                           value="<?= $_GET['busqueda'] ?? '' ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha Desde</label>
                                    <input type="date" name="fecha_desde" class="form-control" 
                                           value="<?= $_GET['fecha_desde'] ?? '' ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha Hasta</label>
                                    <input type="date" name="fecha_hasta" class="form-control" 
                                           value="<?= $_GET['fecha_hasta'] ?? '' ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Registros por página</label>
                                    <select name="por_pagina" class="form-control">
                                        <option value="25" <?= (isset($_GET['por_pagina']) && $_GET['por_pagina'] == 25) ? 'selected' : '' ?>>25</option>
                                        <option value="50" <?= (isset($_GET['por_pagina']) && $_GET['por_pagina'] == 50) ? 'selected' : 'selected' ?>>50</option>
                                        <option value="100" <?= (isset($_GET['por_pagina']) && $_GET['por_pagina'] == 100) ? 'selected' : '' ?>>100</option>
                                        <option value="200" <?= (isset($_GET['por_pagina']) && $_GET['por_pagina'] == 200) ? 'selected' : '' ?>>200</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label>&nbsp;</label><br>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="?" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                                <a href="?exportar=csv&<?= http_build_query($_GET) ?>" class="btn btn-success">
                                    <i class="fas fa-file-excel"></i> Exportar CSV
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de actividades -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Actividades Registradas</h3>
                    <div class="card-tools">
                        <span class="badge badge-primary"><?= number_format($total_registros) ?> registros</span>
                    </div>
                </div>
                <div class="card-body table-responsive p-0" style="max-height: 600px;">
                    <table class="table table-hover table-head-fixed text-nowrap">
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Usuario</th>
                                <th>Módulo</th>
                                <th>Acción</th>
                                <th>Descripción</th>
                                <th>Nivel</th>
                                <th>IP</th>
                                <th>Detalles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($actividades)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle fa-3x mb-3 d-block"></i>
                                        No se encontraron registros con los filtros aplicados
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($actividades as $act): ?>
                                    <?php
                                    // Iconos y colores por nivel
                                    $nivel_config = [
                                        'info' => ['icono' => 'info-circle', 'color' => 'info'],
                                        'warning' => ['icono' => 'exclamation-triangle', 'color' => 'warning'],
                                        'error' => ['icono' => 'times-circle', 'color' => 'danger'],
                                        'critical' => ['icono' => 'skull-crossbones', 'color' => 'danger']
                                    ];
                                    $config = $nivel_config[$act['nivel']] ?? $nivel_config['info'];
                                    
                                    // Iconos por acción
                                    $accion_icono = [
                                        'INSERT' => 'plus-circle text-success',
                                        'UPDATE' => 'edit text-info',
                                        'DELETE' => 'trash text-danger',
                                        'LOGIN' => 'sign-in-alt text-success',
                                        'LOGOUT' => 'sign-out-alt text-secondary',
                                        'ERROR' => 'bug text-danger'
                                    ];
                                    $icono_accion = $accion_icono[$act['accion']] ?? 'circle';
                                    ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i:s', strtotime($act['fecha_accion'])) ?></td>
                                        <td>
                                            <i class="fas fa-user"></i>
                                            <?= $act['nombre_completo'] ?? $act['username'] ?? 'Sistema' ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                <?= ucfirst($act['modulo'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fas fa-<?= $icono_accion ?>"></i>
                                            <?= $act['accion'] ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $descripcion = $act['descripcion'] ?? '';
                                            echo $descripcion ? htmlspecialchars($descripcion) : '<span class="text-muted"><em>Sin descripción</em></span>';
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $config['color'] ?>">
                                                <i class="fas fa-<?= $config['icono'] ?>"></i>
                                                <?= strtoupper($act['nivel']) ?>
                                            </span>
                                        </td>
                                        <td><small><?= $act['ip_address'] ?? 'N/A' ?></small></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="verDetalles(<?= $act['id_auditoria'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                    <div class="card-footer clearfix">
                        <ul class="pagination pagination-sm m-0 float-right">
                            <?php if ($pagina > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])) ?>">«</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $pagina - 3); $i <= min($total_paginas, $pagina + 3); $i++): ?>
                                <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagina < $total_paginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])) ?>">»</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                        <div class="float-left mt-2">
                            Mostrando <?= ($offset + 1) ?> a <?= min($offset + $por_pagina, $total_registros) ?> de <?= number_format($total_registros) ?> registros
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<!-- Modal para ver detalles -->
<div class="modal fade" id="modalDetalles">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title"><i class="fas fa-info-circle"></i> Detalles de la Actividad</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="modalDetallesBody">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function verDetalles(id) {
    $('#modalDetalles').modal('show');
    $('#modalDetallesBody').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
    
    $.ajax({
        url: 'get_detalle_actividad.php',
        method: 'GET',
        data: { id: id },
        success: function(response) {
            $('#modalDetallesBody').html(response);
        },
        error: function() {
            $('#modalDetallesBody').html('<div class="alert alert-danger">Error al cargar los detalles</div>');
        }
    });
}
</script>

<?php include('../../contans/layout/parte2.php'); ?>
