<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');
include ('../../controllers/rol/listar.php');

// Obtener ID del usuario a editar
$id_usuario_get = $_GET['id'] ?? 0;

if ($id_usuario_get <= 0) {
    $_SESSION['error'] = 'ID de usuario inválido';
    header('Location: index.php');
    exit;
}

// Obtener datos del usuario
try {
    $sql = "SELECT u.*, r.rol, r.id_rol
            FROM tb_usuarios u
            INNER JOIN tb_roles r ON u.id_rol = r.id_rol
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
    $id_rol_actual = $usuario_dato['id_rol'];
    
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
                    <h1 class="m-0">Actualizar Usuario</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Actualice los datos con cuidado</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="../../controllers/usuario/actualizar.php" method="post" id="formActualizarUsuario">
                                <input type="hidden" name="id_usuario" value="<?php echo $id_usuario_get; ?>">
                                
                                <div class="form-group">
                                    <label for="username">Nombre de Usuario</label>
                                    <input type="text" 
                                           name="username" 
                                           id="username"
                                           class="form-control"
                                           value="<?php echo htmlspecialchars($username); ?>"
                                           required
                                           maxlength="50">
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" 
                                           name="email" 
                                           id="email"
                                           class="form-control"
                                           value="<?php echo htmlspecialchars($email); ?>"
                                           required>
                                </div>

                                <div class="form-group">
                                    <label for="id_rol">Rol del Usuario</label>
                                    <select name="id_rol" id="id_rol" class="form-control" required>
                                        <?php
                                        foreach ($roles_datos as $roles_dato){
                                            $selected = ($roles_dato['id_rol'] == $id_rol_actual) ? 'selected' : '';
                                            ?>
                                            <option value="<?php echo $roles_dato['id_rol']; ?>" <?php echo $selected; ?>>
                                                <?php echo $roles_dato['rol']; ?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Nota:</strong> Deje los campos de contraseña vacíos si no desea cambiarla
                                </div>

                                <div class="form-group">
                                    <label for="password_user">Nueva Contraseña (opcional)</label>
                                    <input type="password" 
                                           name="password_user" 
                                           id="password_user"
                                           class="form-control"
                                           minlength="6"
                                           placeholder="Dejar vacío para no cambiar">
                                    <small class="form-text text-muted">Mínimo 6 caracteres si desea cambiarla</small>
                                </div>

                                <div class="form-group">
                                    <label for="password_repeat">Repetir Nueva Contraseña</label>
                                    <input type="password" 
                                           name="password_repeat" 
                                           id="password_repeat"
                                           class="form-control"
                                           minlength="6"
                                           placeholder="Dejar vacío para no cambiar">
                                </div>

                                <hr>
                                
                                <div class="form-group">
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-sync-alt"></i> Actualizar
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
    // Validación de contraseñas coincidentes
    document.getElementById('formActualizarUsuario').addEventListener('submit', function(e) {
        var password = document.getElementById('password_user').value;
        var passwordRepeat = document.getElementById('password_repeat').value;
        
        // Si se ingresó una contraseña, validar
        if (password !== '' || passwordRepeat !== '') {
            if (password !== passwordRepeat) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Las contraseñas no coinciden'
                });
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'La contraseña debe tener al menos 6 caracteres'
                });
                return false;
            }
        }
    });
</script>
