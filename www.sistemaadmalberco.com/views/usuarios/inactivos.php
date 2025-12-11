<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Obtener usuarios inactivos
try {
    $sql = "SELECT u.*, 
                   r.rol,
                   e.nombres,
                   e.apellidos
            FROM tb_usuarios u
            LEFT JOIN tb_roles r ON u.id_rol = r.id_rol
            LEFT JOIN tb_empleados e ON u.id_empleado = e.id_empleado
            WHERE u.estado_registro = 'INACTIVO'
            ORDER BY u.fyh_actualizacion DESC";
    
    $stmt = $pdo->query($sql);
    $usuarios_inactivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al obtener usuarios inactivos: " . $e->getMessage());
    $usuarios_inactivos = [];
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-user-slash"></i> Usuarios Inactivos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Usuarios</a></li>
                        <li class="breadcrumb-item active">Inactivos</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-danger">
                        <div class="card-header">
                            <h3 class="card-title">Listado de Usuarios Eliminados</h3>
                            <div class="card-tools">
                                <a href="index.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Volver a Usuarios Activos
                                </a>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <?php if (count($usuarios_inactivos) > 0): ?>
                            <div class="table-responsive">
                                <table id="tablaUsuariosInactivos" class="table table-bordered table-striped table-sm">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Email</th>
                                        <th>Nombre Completo</th>
                                        <th>Rol</th>
                                        <th>Fecha Eliminación</th>
                                        <th>Acciones</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($usuarios_inactivos as $usuario): ?>
                                    <tr>
                                        <td><?php echo $usuario['id_usuario']; ?></td>
                                        <td>
                                            <i class="fas fa-user-slash text-danger"></i>
                                            <?php echo htmlspecialchars($usuario['username']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                        <td>
                                            <?php 
                                            if ($usuario['nombres']) {
                                                echo htmlspecialchars($usuario['nombres'] . ' ' . ($usuario['apellidos'] ?? ''));
                                            } else {
                                                echo '<span class="text-muted">Sin empleado asignado</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($usuario['rol']): ?>
                                                <span class="badge badge-secondary">
                                                    <?php echo htmlspecialchars($usuario['rol']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Sin rol</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y H:i', strtotime($usuario['fyh_actualizacion'])); ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button type="button" 
                                                        class="btn btn-success btn-sm" 
                                                        onclick="restaurarUsuario(<?php echo $usuario['id_usuario']; ?>, '<?php echo htmlspecialchars($usuario['username']); ?>')"
                                                        title="Restaurar Usuario">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm" 
                                                        onclick="eliminarPermanente(<?php echo $usuario['id_usuario']; ?>, '<?php echo htmlspecialchars($usuario['username']); ?>')"
                                                        title="Eliminar Permanentemente">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                No hay usuarios inactivos en el sistema.
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

<script>
$(document).ready(function() {
    $('#tablaUsuariosInactivos').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [[5, "desc"]], // Ordenar por fecha de eliminación
        "pageLength": 10
    });
});

function restaurarUsuario(id, username) {
    Swal.fire({
        title: '¿Restaurar usuario?',
        html: `¿Está seguro de restaurar al usuario <strong>${username}</strong>?<br>El usuario volverá a estar activo en el sistema.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, restaurar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Enviar petición para restaurar
            $.ajax({
                url: '../../controllers/usuario/restaurar.php',
                type: 'POST',
                data: { id_usuario: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Restaurado',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al procesar la solicitud'
                    });
                }
            });
        }
    });
}

function eliminarPermanente(id, username) {
    Swal.fire({
        title: '⚠️ ¿Eliminar permanentemente?',
        html: `Esta acción NO se puede deshacer.<br>¿Está seguro de eliminar permanentemente al usuario <strong>${username}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar permanentemente',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'error',
                title: 'Función no implementada',
                text: 'Por seguridad, la eliminación permanente debe ser realizada directamente en la base de datos.'
            });
        }
    });
}
</script>
