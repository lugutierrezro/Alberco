<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');

// Obtener datos del usuario con rol
$pdo = getDB();
$id_usuario = $_SESSION['id_usuario'];

$stmt = $pdo->prepare("SELECT u.*, r.rol as rol 
                       FROM tb_usuarios u
                       LEFT JOIN tb_roles r ON u.id_rol = r.id_rol
                       WHERE u.id_usuario = :id");
$stmt->execute([':id' => $id_usuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

include ('../../contans/layout/parte1.php');
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-user-edit mr-2 text-orange"></i>Editar Mi Perfil</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Mi Perfil</a></li>
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <form action="../../controllers/usuario/actualizar_perfil.php" method="POST" id="formEditarPerfil">
                <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                
                <div class="row">
                    <!-- Información Personal -->
                    <div class="col-md-6">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user mr-2"></i>Información Personal
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="nombres">
                                        Nombres Completos <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        </div>
                                        <input type="text" 
                                               name="nombres" 
                                               id="nombres"
                                               class="form-control" 
                                               value="<?php echo htmlspecialchars($usuario['nombres'] ?? ''); ?>"
                                               required
                                               maxlength="100">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="email">
                                        Correo Electrónico <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        </div>
                                        <input type="email" 
                                               name="email" 
                                               id="email"
                                               class="form-control" 
                                               value="<?php echo htmlspecialchars($usuario['email']); ?>"
                                               required>
                                    </div>
                                    <small class="form-text text-muted">
                                        Este email se usa para notificaciones del sistema
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="username">
                                        Nombre de Usuario <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                        </div>
                                        <input type="text" 
                                               name="username" 
                                               id="username"
                                               class="form-control" 
                                               value="<?php echo htmlspecialchars($usuario['username'] ?? ''); ?>"
                                               required
                                               maxlength="50">
                                    </div>
                                    <small class="form-text text-muted">
                                        Se usa para iniciar sesión
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Sistema -->
                    <div class="col-md-6">
                        <div class="card card-info card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-info-circle mr-2"></i>Información del Sistema
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <h5><i class="icon fas fa-info"></i> Información de Solo Lectura</h5>
                                    Estos datos son administrados por el sistema.
                                </div>

                                <dl class="row">
                                    <dt class="col-sm-5">Rol:</dt>
                                    <dd class="col-sm-7">
                                        <span class="badge badge-info">
                                            <?php echo htmlspecialchars($usuario['rol'] ?? 'Sin rol'); ?>
                                        </span>
                                    </dd>

                                    <dt class="col-sm-5">Estado:</dt>
                                    <dd class="col-sm-7">
                                        <span class="badge badge-<?php echo $usuario['estado_registro'] == 'ACTIVO' ? 'success' : 'danger'; ?>">
                                            <?php echo $usuario['estado_registro']; ?>
                                        </span>
                                    </dd>

                                    <dt class="col-sm-5">Miembro desde:</dt>
                                    <dd class="col-sm-7">
                                        <?php 
                                        $fecha = new DateTime($usuario['fyh_creacion']);
                                        echo $fecha->format('d/m/Y');
                                        ?>
                                    </dd>

                                    <dt class="col-sm-5">Última actualización:</dt>
                                    <dd class="col-sm-7">
                                        <?php 
                                        if ($usuario['fyh_actualizacion']) {
                                            $fecha_act = new DateTime($usuario['fyh_actualizacion']);
                                            echo $fecha_act->format('d/m/Y H:i');
                                        } else {
                                            echo 'Nunca';
                                        }
                                        ?>
                                    </dd>
                                </dl>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="card">
                            <div class="card-body">
                                <a href="index.php" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times mr-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
include_once('../../contans/layout/mensajes.php'); 
include_once('../../contans/layout/parte2.php'); 
?>

<script>
    // Validación del formulario
    document.getElementById('formEditarPerfil').addEventListener('submit', function(e) {
        var email = document.getElementById('email').value;
        var username = document.getElementById('username').value;
        var nombres = document.getElementById('nombres').value;
        
        if (nombres.trim().length < 3) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El nombre debe tener al menos 3 caracteres'
            });
            return false;
        }
        
        if (username.trim().length < 3) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El nombre de usuario debe tener al menos 3 caracteres'
            });
            return false;
        }
        
        // Validar formato de email
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor ingrese un email válido'
            });
            return false;
        }
    });
</script>
