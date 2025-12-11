<?php 
include('../../services/database/config.php');
include('../../contans/layout/sesion.php');
require_once __DIR__ . '/../../controllers/personalizacion/anuncios.php';
include('../../contans/layout/parte1.php');
?>
<link rel="stylesheet" href="css/anuncios-editor.css">

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-bullhorn"></i> Gestión de Anuncios</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../../dashboard.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Personalización</a></li>
                        <li class="breadcrumb-item active">Anuncios</li>
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
                <?= htmlspecialchars($mensaje) ?>
            </div>
            <?php endif; ?>

            <?php if ($accion === 'listar'): ?>
            
            <!-- Estadísticas -->
            <div class="row">
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $estadisticas['total'] ?? 0 ?></h3>
                            <p>Total de Anuncios</p>
                        </div>
                        <div class="icon"><i class="fas fa-list"></i></div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $estadisticas['activos'] ?? 0 ?></h3>
                            <p>Anuncios Activos</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $estadisticas['expirados'] ?? 0 ?></h3>
                            <p>Anuncios Expirados</p>
                        </div>
                        <div class="icon"><i class="fas fa-calendar-times"></i></div>
                    </div>
                </div>
            </div>

            <!-- Lista de anuncios -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lista de Anuncios</h3>
                    <div class="card-tools">
                        <a href="?accion=crear" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Nuevo Anuncio
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Tipo</th>
                                <th>Posición</th>
                                <th>Vigencia</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($anuncios as $a): ?>
                            <tr>
                                <td><?= $a['id_anuncio'] ?></td>
                                <td><?= htmlspecialchars($a['titulo']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $a['tipo'] === 'alerta' ? 'danger' : ($a['tipo'] === 'promocion' ? 'success' : 'info') ?>">
                                        <?= ucfirst($a['tipo']) ?>
                                    </span>
                                </td>
                                <td><?= ucfirst($a['posicion']) ?></td>
                                <td>
                                    <small>
                                        <?= $a['fecha_inicio'] ? date('d/m/Y', strtotime($a['fecha_inicio'])) : 'Sin fecha' ?> -
                                        <?= $a['fecha_fin'] ? date('d/m/Y', strtotime($a['fecha_fin'])) : 'Indefinido' ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" 
                                               class="custom-control-input toggle-activo" 
                                               id="switch-<?= $a['id_anuncio'] ?>"
                                               data-id="<?= $a['id_anuncio'] ?>"
                                               <?= $a['activo'] ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="switch-<?= $a['id_anuncio'] ?>"></label>
                                    </div>
                                </td>
                                <td><?= $a['prioridad'] ?></td>
                                <td>
                                    <a href="?accion=editar&id=<?= $a['id_anuncio'] ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger btn-eliminar" data-id="<?= $a['id_anuncio'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php elseif ($accion === 'crear' || $accion === 'editar'): ?>
            
            <!-- Formulario de crear/editar -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <?= $accion === 'crear' ? 'Nuevo Anuncio' : 'Editar Anuncio' ?>
                    </h3>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="accion" value="<?= $accion ?>">
                    <?php if ($accion === 'editar'): ?>
                    <input type="hidden" name="id_anuncio" value="<?= $anuncio['id_anuncio'] ?>">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Título <span class="text-danger">*</span></label>
                                    <input type="text" name="titulo" class="form-control" 
                                           value="<?= $anuncio['titulo'] ?? '' ?>" required>
                                </div>

                                <div class="form-group">
                                    <label>Contenido</label>
                                    <textarea name="contenido" class="form-control" rows="3"><?= $anuncio['contenido'] ?? '' ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tipo</label>
                                            <select name="tipo" class="form-control">
                                                <option value="info" <?= ($anuncio['tipo'] ?? '') === 'info' ? 'selected' : '' ?>>Información</option>
                                                <option value="alerta" <?= ($anuncio['tipo'] ?? '') === 'alerta' ? 'selected' : '' ?>>Alerta</option>
                                                <option value="promocion" <?= ($anuncio['tipo'] ?? '') === 'promocion' ? 'selected' : '' ?>>Promoción</option>
                                                <option value="evento" <?= ($anuncio['tipo'] ?? '') === 'evento' ? 'selected' : '' ?>>Evento</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Posición</label>
                                            <select name="posicion" class="form-control">
                                                <option value="top" <?= ($anuncio['posicion'] ?? '') === 'top' ? 'selected' : '' ?>>Superior</option>
                                                <option value="hero" <?= ($anuncio['posicion'] ?? '') === 'hero' ? 'selected' : '' ?>>Hero/Banner Principal</option>
                                                <option value="footer" <?= ($anuncio['posicion'] ?? '') === 'footer' ? 'selected' : '' ?>>Pie de Página</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha Inicio</label>
                                            <input type="datetime-local" name="fecha_inicio" class="form-control"
                                                   value="<?= isset($anuncio['fecha_inicio']) ? date('Y-m-d\TH:i', strtotime($anuncio['fecha_inicio'])) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha Fin</label>
                                            <input type="datetime-local" name="fecha_fin" class="form-control"
                                                   value="<?= isset($anuncio['fecha_fin']) ? date('Y-m-d\TH:i', strtotime($anuncio['fecha_fin'])) : '' ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Prioridad</label>
                                    <input type="number" name="prioridad" class="form-control" 
                                           value="<?= $anuncio['prioridad'] ?? 1 ?>" min="1" max="10">
                                    <small class="text-muted">Mayor número = Mayor prioridad</small>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="activo" name="activo"
                                               <?= ($anuncio['activo'] ?? 1) ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="activo">Activo</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>🎨 Personalización Visual Completa</label>
                                    
                                    <!-- Tabs para organizar controles -->
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-toggle="tab" href="#colores">Colores</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#tipografia">Tipografía</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#bordes">Bordes</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#espaciado">Espaciado</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#efectos">Efectos</a>
                                        </li>
                                    </ul>
                                    
                                    <div class="tab-content border border-top-0 p-3">
                                        <!-- Tab: Colores -->
                                        <div id="colores" class="tab-pane active">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label class="small">🎨 Fondo</label>
                                                    <input type="color" class="form-control css-color" id="bgColor" value="#ffffff">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="small">📝 Texto</label>
                                                    <input type="color" class="form-control css-color" id="textColor" value="#000000">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="small">🖼️ Borde</label>
                                                    <input type="color" class="form-control css-color" id="borderColor" value="#cccccc">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="small">💧 Opacidad (%)</label>
                                                    <input type="range" class="form-control-range css-control" id="opacity" min="0" max="100" value="100">
                                                    <small class="text-muted" id="opacityValue">100%</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Tab: Tipografía -->
                                        <div id="tipografia" class="tab-pane fade">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label class="small">📏 Tamaño</label>
                                                    <select class="form-control form-control-sm css-control" id="fontSize">
                                                        <option value="10px">Mini (10px)</option>
                                                        <option value="12px">Pequeño (12px)</option>
                                                        <option value="14px" selected>Normal (14px)</option>
                                                        <option value="16px">Mediano (16px)</option>
                                                        <option value="18px">Grande (18px)</option>
                                                        <option value="20px">Muy Grande (20px)</option>
                                                        <option value="24px">Extra Grande (24px)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="small">💪 Grosor</label>
                                                    <select class="form-control form-control-sm css-control" id="fontWeight">
                                                        <option value="300">Ligera</option>
                                                        <option value="normal">Normal</option>
                                                        <option value="600">Semi-Negrita</option>
                                                        <option value="bold" selected>Negrita</option>
                                                        <option value="800">Extra-Negrita</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="small">📐 Alineación</label>
                                                    <select class="form-control form-control-sm css-control" id="textAlign">
                                                        <option value="left">Izquierda</option>
                                                        <option value="center" selected>Centro</option>
                                                        <option value="right">Derecha</option>
                                                        <option value="justify">Justificado</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Tab: Bordes -->
                                        <div id="bordes" class="tab-pane fade">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label class="small">📏 Grosor (px)</label>
                                                    <input type="number" class="form-control form-control-sm css-control" 
                                                           id="borderWidth" value="1" min="0" max="10">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="small">🔘 Redondeo (px)</label>
                                                    <input type="number" class="form-control form-control-sm css-control" 
                                                           id="borderRadius" value="0" min="0" max="50">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small">🎨 Estilo</label>
                                                    <select class="form-control form-control-sm css-control" id="borderStyle">
                                                        <option value="solid" selected>Sólido</option>
                                                        <option value="dashed">Discontinuo</option>
                                                        <option value="dotted">Punteado</option>
                                                        <option value="double">Doble</option>
                                                        <option value="none">Sin borde</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Tab: Espaciado -->
                                        <div id="espaciado" class="tab-pane fade">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="small">📦 Padding Interno (px)</label>
                                                    <input type="number" class="form-control form-control-sm css-control" 
                                                           id="padding" value="10" min="0" max="100">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small">🔲 Margin Externo (px)</label>
                                                    <input type="number" class="form-control form-control-sm css-control" 
                                                           id="margin" value="0" min="0" max="50">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Tab: Efectos -->
                                        <div id="efectos" class="tab-pane fade">
                                            <div class="row mb-2">
                                                <div class="col-md-12">
                                                    <label class="small">💫 Sombra</label>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="number" class="form-control form-control-sm css-control" 
                                                           id="shadowX" value="0" min="-20" max="20" placeholder="X">
                                                    <small class="text-muted">Horizontal</small>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="number" class="form-control form-control-sm css-control" 
                                                           id="shadowY" value="2" min="-20" max="20" placeholder="Y">
                                                    <small class="text-muted">Vertical</small>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="number" class="form-control form-control-sm css-control" 
                                                           id="shadowBlur" value="4" min="0" max="50" placeholder="Difuminado">
                                                    <small class="text-muted">Blur</small>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="color" class="form-control css-color" 
                                                           id="shadowColor" value="#000000">
                                                    <small class="text-muted">Color</small>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="small">✨ Animación</label>
                                                    <select class="form-control form-control-sm css-control" id="animation">
                                                        <option value="none" selected>Sin animación</option>
                                                        <option value="pulse">Pulso</option>
                                                        <option value="bounce">Rebote</option>
                                                        <option value="shake">Vibración</option>
                                                        <option value="fade">Desvanecimiento</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small">🔄 Transform</label>
                                                    <select class="form-control form-control-sm css-control" id="transform">
                                                        <option value="none" selected>Normal</option>
                                                        <option value="scale(1.05)">Agrandar 5%</option>
                                                        <option value="scale(1.1)">Agrandar 10%</option>
                                                        <option value="rotate(2deg)">Rotar ligero</option>
                                                        <option value="skew(2deg)">Inclinar</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Vista Previa Realista Mejorada -->
                                    <div class="mt-3 preview-container">
                                        <div class="preview-header">
                                            <h5 class="mb-0">
                                                <i class="fas fa-eye"></i> Vista Previa en Tiempo Real
                                                <span class="position-badge top" id="positionBadge">Top</span>
                                            </h5>
                                        </div>
                                        
                                        <!-- Mockup del sitio web -->
                                        <div class="website-mockup">
                                            <!-- Navbar simulado -->
                                            <div class="mockup-navbar">
                                                <i class="fas fa-utensils mr-2"></i>
                                                ALBERCO - Restaurante
                                            </div>
                                            
                                            <!-- Área de anuncio TOP -->
                                            <div id="previewTop" style="display: none;"></div>
                                            
                                            <!-- Contenido simulado -->
                                            <div class="mockup-content">
                                                <!-- Área de anuncio HERO -->
                                                <div id="previewHero" style="display: none;"></div>
                                                
                                                <div class="text-center text-muted py-4">
                                                    <p><i class="fas fa-pizza-slice fa-3x mb-3"></i></p>
                                                    <h4>Contenido del Sitio Web</h4>
                                                    <p>Menú, productos, promociones, etc.</p>
                                                </div>
                                            </div>
                                            
                                            <!-- Footer simulado -->
                                            <div class="mockup-footer">
                                                <!-- Área de anuncio FOOTER -->
                                                <div id="previewFooter" style="display: none;"></div>
                                                
                                                <div id="footerPlaceholder">
                                                    © 2024 ALBERCO | Todos los derechos reservados
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Información de la posición -->
                                        <div class="alert alert-info mt-3 mb-0">
                                            <i class="fas fa-info-circle"></i>
                                            <strong id="positionInfo">Posición Superior:</strong>
                                            <span id="positionDesc">El anuncio aparecerá en la parte superior del sitio, antes del navbar.</span>
                                        </div>
                                    </div>
                                    
                                    <!-- CSS Generado -->
                                    <label>💻 CSS Generado (Avanzado)</label>
                                    <textarea name="estilo_css" id="cssOutput" class="form-control" rows="5" 
                                              placeholder="El CSS se genera automáticamente..."><?= $anuncio['estilo_css'] ?? '' ?></textarea>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> Puedes editar manualmente el CSS si necesitas ajustes específicos
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                        <a href="anuncios.php" class="btn btn-default">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>

            <?php endif; ?>
        </div>
    </section>
</div>

<script>
// Generar CSS completo y actualizar vista previa
function updateCSS() {
    const bgColor = $('#bgColor').val();
    const textColor = $('#textColor').val();
    const borderColor = $('#borderColor').val();
    const fontSize = $('#fontSize').val();
    const fontWeight = $('#fontWeight').val();
    const textAlign = $('#textAlign').val();
    const borderWidth = $('#borderWidth').val();
    const borderRadius = $('#borderRadius').val();
    const borderStyle = $('#borderStyle').val();
    const padding = $('#padding').val();
    const margin = $('#margin').val();
    const opacity = $('#opacity').val() / 100;
    const shadowX = $('#shadowX').val();
    const shadowY = $('#shadowY').val();
    const shadowBlur = $('#shadowBlur').val();
    const shadowColor = $('#shadowColor').val();
    const animation = $('#animation').val();
    const transform = $('#transform').val();
    
    // Obtener contenido del anuncio
    const titulo = $('input[name="titulo"]').val() || 'Título del Anuncio';
    const contenido = $('textarea[name="contenido"]').val() || 'Contenido del anuncio. Escribe aquí el mensaje que deseas mostrar.';
    const posicion = $('select[name="posicion"]').val() || 'top';
    
    // Actualizar display de opacidad
    $('#opacityValue').text(Math.round(opacity * 100) + '%');
    
    // Construir box-shadow
    const boxShadow = (shadowX != 0 || shadowY != 0 || shadowBlur != 0) 
        ? `box-shadow: ${shadowX}px ${shadowY}px ${shadowBlur}px ${shadowColor};` 
        : '';
    
    // Construir border
    const border = borderStyle !== 'none' && borderWidth > 0
        ? `border: ${borderWidth}px ${borderStyle} ${borderColor};`
        : 'border: none;';
    
    // Generar CSS completo
    let css = `background-color: ${bgColor}; ` +
              `color: ${textColor}; ` +
              `${border} ` +
              `border-radius: ${borderRadius}px; ` +
              `padding: ${padding}px; ` +
              `margin: ${margin}px; ` +
              `font-size: ${fontSize}; ` +
              `font-weight: ${fontWeight}; ` +
              `text-align: ${textAlign}; ` +
              `opacity: ${opacity}; `;
    
    if (boxShadow) css += boxShadow + ' ';
    if (transform !== 'none') css += `transform: ${transform}; `;
    if (animation !== 'none') css += `animation: ${animation} 1.5s infinite; `;
    
    // Actualizar textarea
    $('#cssOutput').val(css.trim());
    
    // Crear contenido del anuncio
    const anuncioHTML = `
        <div class="preview-content" style="${css}">
            <strong>${titulo}</strong><br>
            ${contenido}
        </div>
    `;
    
    // Ocultar todos los previews
    $('#previewTop, #previewHero, #previewFooter').hide().html('');
    $('#footerPlaceholder').show();
    
    // Mostrar en la posición correcta
    switch(posicion) {
        case 'top':
            $('#previewTop').html(anuncioHTML).show();
            updatePositionInfo('top', 'Posición Superior', 'El anuncio aparecerá en la parte superior del sitio, antes del navbar.');
            break;
        case 'hero':
            $('#previewHero').html(anuncioHTML).show();
            updatePositionInfo('hero', 'Hero/Banner Principal', 'El anuncio aparecerá como banner principal en la sección hero del sitio.');
            break;
        case 'footer':
            $('#footerPlaceholder').hide();
            $('#previewFooter').html(anuncioHTML).show();
            updatePositionInfo('footer', 'Pie de Página', 'El anuncio aparecerá en el footer del sitio, al final de la página.');
            break;
    }
    
    // Actualizar animaciones CSS
    let styleTag = $('#animationStyles');
    if (styleTag.length === 0) {
        styleTag = $('<style id="animationStyles"></style>').appendTo('head');
    }
    
    styleTag.html(`
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        @keyframes fade {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    `);
}

// Actualizar información de posición
function updatePositionInfo(position, title, description) {
    $('#positionBadge')
        .removeClass('top hero footer')
        .addClass(position)
        .text(position === 'hero' ? 'Hero' : (position === 'footer' ? 'Footer' : 'Top'));
    
    $('#positionInfo').text(title + ':');
    $('#positionDesc').text(description);
}

// Parsear CSS existente al cargar (mejorado)
$(document).ready(function() {
    const existingCSS = $('#cssOutput').val();
    if (existingCSS) {
        // Parsear valores del CSS
        const bgMatch = existingCSS.match(/background-color:\s*([^;]+)/);
        const colorMatch = existingCSS.match(/(?:^|;)\s*color:\s*([^;]+)/);
        const borderMatch = existingCSS.match(/border:\s*(\d+)px\s+(\w+)\s+([^;]+)/);
        const borderRadiusMatch = existingCSS.match(/border-radius:\s*(\d+)/);
        const fontSizeMatch = existingCSS.match(/font-size:\s*([^;]+)/);
        const fontWeightMatch = existingCSS.match(/font-weight:\s*([^;]+)/);
        const textAlignMatch = existingCSS.match(/text-align:\s*([^;]+)/);
        const paddingMatch = existingCSS.match(/padding:\s*(\d+)/);
        const marginMatch = existingCSS.match(/margin:\s*(\d+)/);
        const opacityMatch = existingCSS.match(/opacity:\s*([\d.]+)/);
        const shadowMatch = existingCSS.match(/box-shadow:\s*(-?\d+)px\s+(-?\d+)px\s+(\d+)px\s+([^;]+)/);
        const transformMatch = existingCSS.match(/transform:\s*([^;]+)/);
        const animationMatch = existingCSS.match(/animation:\s*(\w+)/);
        
        if (bgMatch) $('#bgColor').val(bgMatch[1].trim());
        if (colorMatch) $('#textColor').val(colorMatch[1].trim());
        if (borderMatch) {
            $('#borderWidth').val(borderMatch[1]);
            $('#borderStyle').val(borderMatch[2]);
            $('#borderColor').val(borderMatch[3].trim());
        }
        if (borderRadiusMatch) $('#borderRadius').val(borderRadiusMatch[1]);
        if (fontSizeMatch) $('#fontSize').val(fontSizeMatch[1].trim());
        if (fontWeightMatch) $('#fontWeight').val(fontWeightMatch[1].trim());
        if (textAlignMatch) $('#textAlign').val(textAlignMatch[1].trim());
        if (paddingMatch) $('#padding').val(paddingMatch[1]);
        if (marginMatch) $('#margin').val(marginMatch[1]);
        if (opacityMatch) $('#opacity').val(Math.round(parseFloat(opacityMatch[1]) * 100));
        if (shadowMatch) {
            $('#shadowX').val(shadowMatch[1]);
            $('#shadowY').val(shadowMatch[2]);
            $('#shadowBlur').val(shadowMatch[3]);
            $('#shadowColor').val(shadowMatch[4].trim());
        }
        if (transformMatch) {
            const t = transformMatch[1].trim();
            if ($('#transform option[value="' + t + '"]').length > 0) {
                $('#transform').val(t);
            }
        }
        if (animationMatch) $('#animation').val(animationMatch[1]);
        
        updateCSS();
    } else {
        // Generar CSS inicial
        updateCSS();
    }
});

// Actualizar cuando cambien los controles CSS
$('.css-color, .css-control').on('input change', updateCSS);

// Actualizar cuando cambien título, contenido o posición
$('input[name="titulo"], textarea[name="contenido"], select[name="posicion"]').on('input change', updateCSS);


// Toggle activo/inactivo
$(document).on('change', '.toggle-activo', function() {
    const id = $(this).data('id');
    $.post('anuncios.php', {
        accion: 'toggle',
        id_anuncio: id
    }, function(response) {
        const result = JSON.parse(response);
        if (result.success) {
            toastr.success('Estado actualizado');
        } else {
            toastr.error('Error al actualizar');
        }
    });
});

// Eliminar anuncio
$(document).on('click', '.btn-eliminar', function() {
    const id = $(this).data('id');
    Swal.fire({
        title: '¿Eliminar anuncio?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = $('<form method="POST"></form>');
            form.append('<input type="hidden" name="accion" value="eliminar">');
            form.append('<input type="hidden" name="id_anuncio" value="' + id + '">');
            $('body').append(form);
            form.submit();
        }
    });
});
</script>

<?php include('../../contans/layout/parte2.php'); ?>
