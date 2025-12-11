<?php 
include('../../services/database/config.php');
include('../../contans/layout/sesion.php');
require_once __DIR__ . '/../../controllers/personalizacion/temas.php';
include('../../contans/layout/parte1.php');
?>

<style>
.tema-card {
    transition: transform 0.3s, box-shadow 0.3s;
    cursor: pointer;
}
.tema-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}
.tema-card.active {
    border: 3px solid #28a745;
    box-shadow: 0 0 20px rgba(40, 167, 69, 0.3);
}
.tema-preview {
    height: 200px;
    background-size: cover;
    background-position: center;
    border-radius: 8px 8px 0 0;
}
.color-circle {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: inline-block;
    margin: 0 5px;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-paint-brush"></i> Galería de Temas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../../dashboard.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Personalización</a></li>
                        <li class="breadcrumb-item active">Temas</li>
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

            <div class="callout callout-info">
                <h5><i class="fas fa-info-circle"></i> Temas Predefinidos</h5>
                <p>Aplica instantáneamente un tema predefinido para ocasiones especiales. El tema actual es: 
                    <strong><?= $temas[$temaActualId]['nombre'] ?? 'Default' ?></strong>
                </p>
            </div>

            <!-- Galería de temas -->
            <div class="row">
                <?php foreach ($temas as $id => $tema): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card tema-card <?= $temaActualId === $id ? 'active' : '' ?>">
                        <?php if ($tema['preview']): ?>
                        <div class="tema-preview" style="background-image: url('<?= $tema['preview'] ?>');"></div>
                        <?php else: ?>
                        <div class="tema-preview bg-gradient-secondary d-flex align-items-center justify-content-center">
                            <h3 class="text-white"><i class="fas fa-home"></i></h3>
                        </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h4 class="card-title">
                                <?= $tema['nombre'] ?>
                                <?php if ($temaActualId === $id): ?>
                                <span class="badge badge-success float-right">Activo</span>
                                <?php endif; ?>
                            </h4>
                            <p class="card-text text-muted">
                                <?= $tema['descripcion'] ?>
                            </p>
                            
                            <!-- Paleta de colores -->
                            <div class="mb-3">
                                <strong>Colores:</strong><br>
                                <span class="color-circle" 
                                      style="background-color: <?= $tema['configuraciones']['color_primario'] ?>;"
                                      title="Color Primario"></span>
                                <span class="color-circle" 
                                      style="background-color: <?= $tema['configuraciones']['color_secundario'] ?>;"
                                      title="Color Secundario"></span>
                                <span class="color-circle" 
                                      style="background-color: <?= $tema['configuraciones']['color_acento'] ?>;"
                                      title="Color Acento"></span>
                            </div>
                            
                            <!-- Detalles -->
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-font"></i> <?= $tema['configuraciones']['fuente_principal'] ?>
                                </small>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-sparkles"></i> 
                                    Efectos: <?= $tema['configuraciones']['efectos_activos'] ? 'Sí' : 'No' ?>
                                </small>
                            </div>

                            <?php if ($temaActualId !== $id): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="accion" value="aplicar_tema">
                                <input type="hidden" name="tema_id" value="<?= $id ?>">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-check"></i> Aplicar Tema
                                </button>
                            </form>
                            <?php else: ?>
                            <button class="btn btn-success btn-block" disabled>
                                <i class="fas fa-check-circle"></i> Tema Activo
                            </button>
                            <?php endif; ?>
                            
                            <button class="btn btn-outline-info btn-sm btn-block mt-2" 
                                    onclick="verDetalles('<?= $id ?>')">
                                <i class="fas fa-eye"></i> Ver Detalles
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Sección de personalización avanzada -->
            <div class="card card-dark">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cogs"></i> Personalización Avanzada</h3>
                </div>
                <div class="card-body">
                    <p>¿Necesitas personalizar aún más tu tema?</p>
                    <a href="index.php" class="btn btn-warning">
                        <i class="fas fa-cog"></i> Ir a Configuración Manual
                    </a>
                    <button class="btn btn-info" onclick="crearTemaPersonalizado()">
                        <i class="fas fa-plus"></i> Crear Tema Personalizado
                    </button>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal de detalles del tema -->
<div class="modal fade" id="detallesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Detalles del Tema</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="detallesContenido">
                <!-- Contenido se cargará con JS -->
            </div>
        </div>
    </div>
</div>

<script>
const temas = <?= json_encode($temas) ?>;

function verDetalles(temaId) {
    const tema = temas[temaId];
    let html = `
        <h4>${tema.nombre}</h4>
        <p class="text-muted">${tema.descripcion}</p>
        <hr>
        <h5>Configuraciones:</h5>
        <table class="table table-sm">
            <thead>
                <tr><th>Propiedad</th><th>Valor</th></tr>
            </thead>
            <tbody>
    `;
    
    for (const [key, value] of Object.entries(tema.configuraciones)) {
        let displayValue = value;
        if (key.includes('color')) {
            displayValue = `<span class="color-circle" style="background-color: ${value}"></span> ${value}`;
        }
        html += `<tr><td>${key.replace(/_/g, ' ')}</td><td>${displayValue}</td></tr>`;
    }
    
    html += `
            </tbody>
        </table>
        <p class="mt-3"><strong>Mensaje de Bienvenida:</strong></p>
        <div class="alert alert-info">${tema.configuraciones.mensaje_bienvenida}</div>
    `;
    
    document.getElementById('detallesContenido').innerHTML = html;
    $('#detallesModal').modal('show');
}

function crearTemaPersonalizado() {
    Swal.fire({
        title: 'Crear Tema Personalizado',
        html: `
            <p>Esta función te permitirá guardar tu configuración actual como un tema personalizado.</p>
            <input id="nombre-tema" class="swal2-input" placeholder="Nombre del tema">
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Crear',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Próximamente', 'Esta función estará disponible en la próxima versión', 'info');
        }
    });
}

// Confirmación antes de aplicar tema
$('form').on('submit', function(e) {
    const temaId = $(this).find('input[name="tema_id"]').val();
    const tema = temas[temaId];
    
    if (!confirm(`¿Aplicar el tema "${tema.nombre}"?\n\nEsto cambiará todos los colores y estilos del sitio web.`)) {
        e.preventDefault();
    }
});
</script>

<?php include('../../contans/layout/parte2.php'); ?>
