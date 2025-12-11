<?php 
include('../../services/database/config.php');
include('../../contans/layout/sesion.php');
require_once __DIR__ . '/../../controllers/personalizacion/configuracion.php';
include('../../contans/layout/parte1.php');
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-palette"></i> Personalización del Sitio Web</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../../">Inicio</a></li>
                        <li class="breadcrumb-item active">Personalización</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            
            <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipo_mensaje === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-<?= $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($mensaje) ?>
            </div>
            <?php endif; ?>

            <!-- Tarjetas de acceso rápido -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><i class="fas fa-cog"></i></h3>
                            <p>Configuración General</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <a href="#config-general" class="small-box-footer" data-toggle="collapse">
                            Ver Opciones <i class="fas fa-arrow-circle-down"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><i class="fas fa-bullhorn"></i></h3>
                            <p>Anuncios</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <a href="anuncios.php" class="small-box-footer">
                            Gestionar <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><i class="fas fa-calendar-alt"></i></h3>
                            <p>Eventos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <a href="eventos.php" class="small-box-footer">
                            Gestionar <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><i class="fas fa-paint-brush"></i></h3>
                            <p>Temas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-paint-brush"></i>
                        </div>
                        <a href="temas.php" class="small-box-footer">
                            Ver Galería <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Panel de Configuración General -->
            <div id="config-general" class="collapse">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-cog"></i> Configuración General</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="accion" value="guardar_configuraciones">
                        
                        <div class="card-body">
                            <ul class="nav nav-tabs" id="config-tabs" role="tablist">
                                <?php foreach ($categorias as $index => $cat): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= $index === 0 ? 'active' : '' ?>" 
                                       data-toggle="tab" 
                                       href="#tab-<?= $cat ?>" 
                                       role="tab">
                                        <?= ucfirst(str_replace('_', ' ', $cat)) ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="tab-content mt-3">
                                <?php foreach ($categorias as $index => $cat): ?>
                                <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" 
                                     id="tab-<?= $cat ?>" 
                                     role="tabpanel">
                                    
                                    <?php if (isset($configsPorCategoria[$cat])): ?>
                                        <?php foreach ($configsPorCategoria[$cat] as $config): ?>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">
                                                <?= htmlspecialchars($config['descripcion'] ?: $config['clave']) ?>
                                            </label>
                                            <div class="col-sm-9">
                                                <?php
                                                $inputType = 'text';
                                                $inputValue = htmlspecialchars($config['valor']);
                                                
                                                if ($config['tipo_dato'] === 'booleano') {
                                                    ?>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" 
                                                               class="custom-control-input" 
                                                               id="<?= $config['clave'] ?>"
                                                               name="configs[<?= $config['clave'] ?>]"
                                                               value="1"
                                                               <?= $config['valor'] ? 'checked' : '' ?>>
                                                        <label class="custom-control-label" for="<?= $config['clave'] ?>"></label>
                                                    </div>
                                                    <?php
                                                } elseif ($config['tipo_dato'] === 'numero') {
                                                    ?>
                                                    <input type="number" 
                                                           class="form-control" 
                                                           name="configs[<?= $config['clave'] ?>]"
                                                           value="<?= $inputValue ?>">
                                                    <?php
                                                } elseif ($config['clave'] === 'color_primario' || 
                                                          $config['clave'] === 'color_secundario' || 
                                                          $config['clave'] === 'color_acento') {
                                                    ?>
                                                    <input type="color" 
                                                           class="form-control" 
                                                           name="configs[<?= $config['clave'] ?>]"
                                                           value="<?= $inputValue ?>">
                                                    <?php
                                                } else {
                                                    ?>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           name="configs[<?= $config['clave'] ?>]"
                                                           value="<?= $inputValue ?>">
                                                    <?php
                                                }
                                                ?>
                                                
                                                <input type="hidden" name="tipos[<?= $config['clave'] ?>]" value="<?= $config['tipo_dato'] ?>">
                                                <input type="hidden" name="categorias[<?= $config['clave'] ?>]" value="<?= $cat ?>">
                                                <input type="hidden" name="descripciones[<?= $config['clave'] ?>]" value="<?= htmlspecialchars($config['descripcion']) ?>">
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted">No hay configuraciones en esta categoría</p>
                                    <?php endif; ?>
                                    
                                    <!-- Botón para agregar nueva configuración -->
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="agregarConfiguracion('<?= $cat ?>')">
                                        <i class="fas fa-plus"></i> Agregar configuración
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                            <a href="<?= URL_BASE ?>/" class="btn btn-default">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Vista previa del sitio -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-eye"></i> Vista Previa del Sitio</h3>
                </div>
                <div class="card-body">
                    <div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-responsive-item" 
                                src="https://allwiya.pe/www.alberco.com/" 
                                id="preview-frame"></iframe>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-info" onclick="recargarPreview()">
                        <i class="fas fa-sync-alt"></i> Recargar Vista Previa
                    </button>
                    <a href="//localhost/www.alberco.com/index.php" 
                       target="_blank" 
                       class="btn btn-outline-info">
                        <i class="fas fa-external-link-alt"></i> Abrir en Nueva Ventana
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function recargarPreview() {
    document.getElementById('preview-frame').src = document.getElementById('preview-frame').src;
}

function agregarConfiguracion(categoria) {
    Swal.fire({
        title: 'Nueva Configuración',
        html: `
            <input id="nueva-clave" class="swal2-input" placeholder="Clave (ej: color_texto)">
            <input id="nueva-valor" class="swal2-input" placeholder="Valor">
            <select id="nueva-tipo" class="swal2-input">
                <option value="texto">Texto</option>
                <option value="numero">Número</option>
                <option value="booleano">Verdadero/Falso</option>
                <option value="json">JSON</option>
            </select>
            <input id="nueva-desc" class="swal2-input" placeholder="Descripción">
        `,
        confirmButtonText: 'Agregar',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            return {
                clave: document.getElementById('nueva-clave').value,
                valor: document.getElementById('nueva-valor').value,
                tipo: document.getElementById('nueva-tipo').value,
                descripcion: document.getElementById('nueva-desc').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Aquí podrías hacer una llamada AJAX para guardar directamente
            // O agregar dinámicamente al formulario
            Swal.fire('Agregado', 'Recarga la página para ver los cambios', 'success');
        }
    });
}
</script>

<?php include('../../contans/layout/parte2.php'); ?>
