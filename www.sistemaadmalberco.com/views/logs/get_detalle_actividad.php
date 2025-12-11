<?php
/**
 * Archivo auxiliar para mostrar detalles de una actividad
 */

include('../../services/database/config.php');
include('../../contans/layout/sesion.php');
require_once __DIR__ . '/../../models/auditoria.php';

// Verificar que el usuario sea administrador
if ($rol_sesion !== 'ADMINISTRADOR') {
    echo '<div class="alert alert-danger">No tienes permisos para ver esta información</div>';
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo '<div class="alert alert-danger">ID no proporcionado</div>';
    exit;
}

$auditoriaModel = new Auditoria();
$actividad = $auditoriaModel->getById($id);

if (!$actividad) {
    echo '<div class="alert alert-danger">Actividad no encontrada</div>';
    exit;
}

// Decodificar datos JSON
$datos_anteriores = json_decode($actividad['datos_anteriores'], true);
$datos_nuevos = json_decode($actividad['datos_nuevos'], true);
?>

<div class="row">
    <div class="col-md-6">
        <h5><i class="fas fa-info"></i> Información General</h5>
        <table class="table table-bordered table-sm">
            <tr>
                <th width="40%">Fecha/Hora:</th>
                <td><?= date('d/m/Y H:i:s', strtotime($actividad['fecha_accion'])) ?></td>
            </tr>
            <tr>
                <th>Usuario:</th>
                <td><?= htmlspecialchars($actividad['username'] ?? 'Sistema') ?></td>
            </tr>
            <tr>
                <th>Módulo:</th>
                <td><span class="badge badge-secondary"><?= htmlspecialchars($actividad['modulo'] ?? 'N/A') ?></span></td>
            </tr>
            <tr>
                <th>Acción:</th>
                <td><strong><?= htmlspecialchars($actividad['accion']) ?></strong></td>
            </tr>
            <tr>
                <th>Nivel:</th>
                <td>
                    <?php
                    $badges = [
                        'info' => 'info',
                        'warning' => 'warning',
                        'error' => 'danger',
                        'critical' => 'danger'
                    ];
                    $badge = $badges[$actividad['nivel']] ?? 'secondary';
                    ?>
                    <span class="badge badge-<?= $badge ?>"><?= strtoupper($actividad['nivel']) ?></span>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="col-md-6">
        <h5><i class="fas fa-network-wired"></i> Información Técnica</h5>
        <table class="table table-bordered table-sm">
            <tr>
                <th width="40%">Tabla Afectada:</th>
                <td><code><?= htmlspecialchars($actividad['tabla_afectada'] ?? 'N/A') ?></code></td>
            </tr>
            <tr>
                <th>ID Registro:</th>
                <td><?= $actividad['id_registro_afectado'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <th>Dirección IP:</th>
                <td><code><?= htmlspecialchars($actividad['ip_address'] ?? 'N/A') ?></code></td>
            </tr>
            <tr>
                <th>User Agent:</th>
                <td><small><?= htmlspecialchars(substr($actividad['user_agent'] ?? 'N/A', 0, 50)) ?></small></td>
            </tr>
        </table>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <h5><i class="fas fa-comment-alt"></i> Descripción</h5>
        <div class="alert alert-light">
            <?php 
            $descripcion = $actividad['descripcion'] ?? '';
            echo $descripcion ? htmlspecialchars($descripcion) : '<em class="text-muted">Sin descripción</em>';
            ?>
        </div>
    </div>
</div>

<?php if ($datos_anteriores || $datos_nuevos): ?>
<div class="row mt-3">
    <?php if ($datos_anteriores): ?>
    <div class="col-md-6">
        <h5><i class="fas fa-history"></i> Datos Anteriores</h5>
        <pre class="bg-light p-3 rounded"><?= json_encode($datos_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
    </div>
    <?php endif; ?>
    
    <?php if ($datos_nuevos): ?>
    <div class="col-md-6">
        <h5><i class="fas fa-file-alt"></i> Datos Nuevos</h5>
        <pre class="bg-light p-3 rounded"><?= json_encode($datos_nuevos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
