<?php 
include('../../services/database/config.php');
include('../../contans/layout/sesion.php');
require_once __DIR__ . '/../../controllers/personalizacion/eventos.php';
include('../../contans/layout/parte1.php');
?>
<link rel="stylesheet" href="css/eventos-editor.css">

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-calendar-alt"></i> Gestión de Eventos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../../dashboard.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Personalización</a></li>
                        <li class="breadcrumb-item active">Eventos</li>
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
                            <p>Total de Eventos</p>
                        </div>
                        <div class="icon"><i class="fas fa-list"></i></div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $estadisticas['proximos'] ?? 0 ?></h3>
                            <p>Eventos Próximos</p>
                        </div>
                        <div class="icon"><i class="fas fa-clock"></i></div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3><?= $estadisticas['pasados'] ?? 0 ?></h3>
                            <p>Eventos Pasados</p>
                        </div>
                        <div class="icon"><i class="fas fa-history"></i></div>
                    </div>
                </div>
            </div>

            <!-- Lista de eventos -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-stopwatch"></i> Lista de Eventos con Temporizador</h3>
                    <div class="card-tools">
                        <a href="?accion=crear" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Nuevo Evento
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre del Evento</th>
                                <th>Fecha del Evento</th>
                                <th>Tiempo Restante</th>
                                <th>Contador</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($eventos as $e): 
                                $fechaEvento = strtotime($e['fecha_evento']);
                                $ahora = time();
                                $diferencia = $fechaEvento - $ahora;
                                $dias = floor($diferencia / 86400);
                                $horas = floor(($diferencia % 86400) / 3600);
                            ?>
                            <tr>
                                <td><?= $e['id_evento'] ?></td>
                                <td><strong><?= htmlspecialchars($e['nombre_evento']) ?></strong></td>
                                <td><?= date('d/m/Y H:i', $fechaEvento) ?></td>
                                <td>
                                    <?php if ($diferencia > 0): ?>
                                        <span class="badge badge-warning">
                                            <?= $dias ?> días, <?= $horas ?> horas
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Evento pasado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($e['mostrar_contador']): ?>
                                        <i class="fas fa-check-circle text-success"></i> Sí
                                    <?php else: ?>
                                        <i class="fas fa-times-circle text-muted"></i> No
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($e['activo']): ?>
                                        <span class="badge badge-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?accion=editar&id=<?= $e['id_evento'] ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger btn-eliminar" data-id="<?= $e['id_evento'] ?>">
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
            
            <!-- Formulario -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <?= $accion === 'crear' ? 'Nuevo Evento' : 'Editar Evento' ?>
                    </h3>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="accion" value="<?= $accion ?>">
                    <?php if ($accion === 'editar'): ?>
                    <input type="hidden" name="id_evento" value="<?= $evento['id_evento'] ?>">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nombre del Evento <span class="text-danger">*</span></label>
                                    <input type="text" name="nombre_evento" class="form-control" 
                                           value="<?= $evento['nombre_evento'] ?? '' ?>" required
                                           placeholder="Ej: Gran Apertura 2025">
                                </div>

                                <div class="form-group">
                                    <label>Descripción</label>
                                    <textarea name="descripcion" class="form-control" rows="3"><?= $evento['descripcion'] ?? '' ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Fecha y Hora del Evento <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="fecha_evento" class="form-control" required
                                           value="<?= isset($evento['fecha_evento']) ? date('Y-m-d\TH:i', strtotime($evento['fecha_evento'])) : '' ?>">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" 
                                                       id="mostrar_contador" name="mostrar_contador"
                                                       <?= ($evento['mostrar_contador'] ?? 1) ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="mostrar_contador">Mostrar Contador</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" 
                                                       id="activo" name="activo"
                                                       <?= ($evento['activo'] ?? 1) ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="activo">Activo</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mensaje Antes del Evento</label>
                                    <input type="text" name="mensaje_antes" class="form-control"
                                           value="<?= $evento['mensaje_antes'] ?? 'Próximamente' ?>"
                                           placeholder="Ej: ¡Faltan solo X días!">
                                </div>

                                <div class="form-group">
                                    <label>Mensaje Durante el Evento</label>
                                    <input type="text" name="mensaje_durante" class="form-control"
                                           value="<?= $evento['mensaje_durante'] ?? '¡El evento está en curso!' ?>"
                                           placeholder="Ej: ¡Evento en vivo ahora!">
                                </div>

                                <div class="form-group">
                                    <label>Mensaje Después del Evento</label>
                                    <input type="text" name="mensaje_despues" class="form-control"
                                           value="<?= $evento['mensaje_despues'] ?? 'Evento finalizado' ?>"
                                           placeholder="Ej: Gracias por participar">
                                </div>

                                <div class="form-group">
                                    <label>🎨 Personalización Visual del Countdown</label>
                                    <small class="text-muted d-block mb-2">Usa los controles abajo para personalizar el aspecto visual</small>
                                    <textarea name="estilo_json" id="estiloJSON" class="form-control" rows="4"
                                              placeholder='Generado automáticamente...'><?= $evento['estilo_json'] ?? '' ?></textarea>
                                    <small class="text-muted">El JSON se genera automáticamente desde los controles visuales</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Editor Visual del Countdown con Color Pickers -->
                        <div class="row mt-4">
                            <?php include __DIR__ . '/includes/countdown_editor.php'; ?>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Guardar Evento
                        </button>
                        <a href="eventos.php" class="btn btn-default">
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
// Actualizar countdown preview en tiempo real
function updateCountdownPreview() {
    const config = {
        countdownBg: $('#countdownBg').val(),
        numberColor: $('#countdownNumberColor').val(),
        labelColor: $('#countdownLabelColor').val(),
        boxBg: $('#countdownBoxBg').val(),
        numberSize: $('#numberSize').val() + 'px',
        labelSize: $('#labelSize').val() + 'px',
        numberWeight: $('#numberWeight').val(),
        fontFamily: $('#fontFamily').val(),
        borderRadius: $('#boxBorderRadius').val() + 'px',
        padding: $('#boxPadding').val() + 'px',
        gap: $('#boxGap').val() + 'px',
        shadowX: $('#shadowX').val() + 'px',
        shadowY: $('#shadowY').val() + 'px',
        shadowBlur: $('#shadowBlur').val() + 'px',
        shadowColor: $('#shadowColor').val(),
        animation: $('#countdownAnimation').val()
    };
    
    // Actualizar displays de valores
    $('#numberSizeValue').text(config.numberSize);
    $('#labelSizeValue').text(config.labelSize);
    
    // Aplicar estilos al preview
    $('#countdownPreview').css({
        'background-color': config.countdownBg,
        'gap': config.gap,
        'font-family': config.fontFamily
    });
    
    $('.countdown-box').css({
        'background-color': config.boxBg,
        'border-radius': config.borderRadius,
        'padding': config.padding,
        'box-shadow': `${config.shadowX} ${config.shadowY} ${config.shadowBlur} ${config.shadowColor}`
    });
    
    $('.countdown-number').css({
        'color': config.numberColor,
        'font-size': config.numberSize,
        'font-weight': config.numberWeight
    });
    
    $('.countdown-label').css({
        'color': config.labelColor,
        'font-size': config.labelSize
    });
    
    // Aplicar animación
    $('.countdown-box').removeClass('pulse flip');
    if (config.animation !== 'none') {
        $('.countdown-box').addClass(config.animation);
    }
    
    // Actualizar mensaje del evento
    const nombreEvento = $('input[name="nombre_evento"]').val() || 'Nombre del Evento';
    $('#eventoMensaje').html(`<strong>${nombreEvento}</strong>`);
    
    // Generar JSON
    const configJSON = {
        backgroundColor: config.countdownBg,
        numberColor: config.numberColor,
        labelColor: config.labelColor,
        boxBackgroundColor: config.boxBg,
        numberSize: config.numberSize,
        labelSize: config.labelSize,
        numberWeight: config.numberWeight,
        fontFamily: config.fontFamily,
        borderRadius: config.borderRadius,
        boxPadding: config.padding,
        boxGap: config.gap,
        boxShadow: `${config.shadowX} ${config.shadowY} ${config.shadowBlur} ${config.shadowColor}`,
        animation: config.animation
    };
    
    $('#estiloJSON').val(JSON.stringify(configJSON, null, 2));
}

// Parsear JSON existente al cargar
$(document).ready(function() {
    const existingJSON = $('#estiloJSON').val();
    
    if (existingJSON) {
        try {
            const config = JSON.parse(existingJSON);
            
            if (config.backgroundColor) $('#countdownBg').val(config.backgroundColor);
            if (config.numberColor) $('#countdownNumberColor').val(config.numberColor);
            if (config.labelColor) $('#countdownLabelColor').val(config.labelColor);
            if (config.boxBackgroundColor) $('#countdownBoxBg').val(config.boxBackgroundColor);
            if (config.numberSize) $('#numberSize').val(parseInt(config.numberSize));
            if (config.labelSize) $('#labelSize').val(parseInt(config.labelSize));
            if (config.numberWeight) $('#numberWeight').val(config.numberWeight);
            if (config.fontFamily) $('#fontFamily').val(config.fontFamily);
            if (config.borderRadius) $('#boxBorderRadius').val(parseInt(config.borderRadius));
            if (config.boxPadding) $('#boxPadding').val(parseInt(config.boxPadding));
            if (config.boxGap) $('#boxGap').val(parseInt(config.boxGap));
            if (config.animation) $('#countdownAnimation').val(config.animation);
            
            // Parsear box-shadow
            const shadowMatch = (config.boxShadow || '').match(/(-?\d+)px\s+(-?\d+)px\s+(\d+)px\s+(.+)/);
            if (shadowMatch) {
                $('#shadowX').val(parseInt(shadowMatch[1]));
                $('#shadowY').val(parseInt(shadowMatch[2]));
                $('#shadowBlur').val(parseInt(shadowMatch[3]));
                $('#shadowColor').val(shadowMatch[4].trim());
            }
            
            updateCountdownPreview();
        } catch(e) {
            console.error('Error parsing JSON:', e);
        }
    } else {
        // Inicializar con valores por defecto
        updateCountdownPreview();
    }
});

// Actualizar cuando cambien los controles
$('.countdown-control').on('input change', updateCountdownPreview);

// Actualizar cuando cambie el nombre del evento
$('input[name="nombre_evento"]').on('input', updateCountdownPreview);

// Eliminar evento
$(document).on('click', '.btn-eliminar', function() {
    const id = $(this).data('id');
    Swal.fire({
        title: '¿Eliminar evento?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = $('<form method="POST"></form>');
            form.append('<input type="hidden" name="accion" value="eliminar">');
            form.append('<input type="hidden" name="id_evento" value="' + id + '">');
            $('body').append(form);
            form.submit();
        }
    });
});
</script>

<?php include('../../contans/layout/parte2.php'); ?>
