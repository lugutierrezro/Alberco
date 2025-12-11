<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Obtener ID del usuario a eliminar
$id_usuario_get = $_GET['id'] ?? 0;

if ($id_usuario_get <= 0) {
    $_SESSION['error'] = 'ID de usuario inválido';
    header('Location: index.php');
    exit;
}

// No permitir eliminar el propio usuario
if ($id_usuario_get == $id_usuario_sesion) {
    $_SESSION['error'] = 'No puede eliminar su propio usuario';
    header('Location: index.php');
    exit;
}

// Obtener datos del usuario
try {
    $sql = "SELECT u.*, r.rol, e.nombres, e.apellidos
            FROM tb_usuarios u
            INNER JOIN tb_roles r ON u.id_rol = r.id_rol
            LEFT JOIN tb_empleados e ON u.id_empleado = e.id_empleado
            WHERE u.id_usuario = ? AND u.estado_registro = 'ACTIVO'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_usuario_get]);
    $usuario_dato = $stmt->fetch();
    
    if (!$usuario_dato) {
        $_SESSION['error'] = 'Usuario no encontrado';
        header('Location: index.php');
        exit;
    }
    
    $username = $usuario_dato['username'];
    $email = $usuario_dato['email'];
    $rol = $usuario_dato['rol'];
    $nombres = $usuario_dato['nombres'] ?? $usuario_dato['username'];
    $apellidos = $usuario_dato['apellidos'] ?? '';
    $nombre_completo = $nombres . ' ' . $apellidos;
    
} catch (PDOException $e) {
    error_log("Error al obtener usuario: " . $e->getMessage());
    $_SESSION['error'] = 'Error al cargar datos del usuario';
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
                <div class="col-sm-12">
                    <h1 class="m-0">Eliminar Usuario</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-danger">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-exclamation-triangle"></i>
                                ¿Está seguro de eliminar este usuario?
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="alert alert-danger">
                                <h5><i class="icon fas fa-ban"></i> ¡Advertencia!</h5>
                                Esta acción no se puede deshacer. El usuario será marcado como inactivo.
                            </div>

                            <form action="../../controllers/usuario/eliminar.php" 
                                  method="post" 
                                  id="formEliminarUsuario">
                                <input type="hidden" name="id_usuario" value="<?php echo $id_usuario_get; ?>">
                                
                                <div class="form-group">
                                    <label for="nombres">Nombre Completo</label>
                                    <input type="text" 
                                           id="nombres"
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($nombre_completo); ?>" 
                                           disabled>
                                </div>

                                <div class="form-group">
                                    <label for="username">Usuario</label>
                                    <input type="text" 
                                           id="username"
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($username); ?>" 
                                           disabled>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" 
                                           id="email"
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($email); ?>" 
                                           disabled>
                                </div>

                                <div class="form-group">
                                    <label for="rol">Rol del Usuario</label>
                                    <input type="text" 
                                           id="rol"
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($rol); ?>" 
                                           disabled>
                                </div>

                                <hr>
                                
                                <div class="form-group">
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Confirmar Eliminación
                                    </button>
                                </div>
                            </form>
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
    // Confirmación adicional antes de eliminar
    document.getElementById('formEliminarUsuario').addEventListener('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: '¿Está completamente seguro?',
            html: 'Está a punto de eliminar al usuario:<br><strong><?php echo htmlspecialchars($nombre_completo); ?></strong>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
</script>
