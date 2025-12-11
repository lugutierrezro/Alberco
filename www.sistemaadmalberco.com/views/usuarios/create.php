<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');
include ('../../controllers/rol/listar.php');

// Obtener empleados para vincular
$sql_empleados = "SELECT e.*, r.rol
                  FROM tb_empleados e
                  INNER JOIN tb_roles r ON e.id_rol = r.id_rol
                  WHERE e.estado_registro = 'ACTIVO'
                  ORDER BY e.nombres, e.apellidos";
$stmt_empleados = $pdo->prepare($sql_empleados);
$stmt_empleados->execute();
$empleados = $stmt_empleados->fetchAll(PDO::FETCH_ASSOC);

// Obtener usuarios existentes para verificar emails
$sql_usuarios = "SELECT email FROM tb_usuarios WHERE estado_registro = 'ACTIVO'";
$stmt_usuarios = $pdo->prepare($sql_usuarios);
$stmt_usuarios->execute();
$emails_usados = $stmt_usuarios->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-user-plus text-primary"></i> Registro de Nuevo Usuario</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Usuarios</a></li>
                        <li class="breadcrumb-item active">Crear</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Formulario -->
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Datos del Usuario</h3>
                        </div>

                        <div class="card-body">
                            <form action="../../controllers/usuario/crear.php" method="post" id="formCrearUsuario">
                                
                                <!-- Selector de Empleado -->
                                <div class="form-group">
                                    <label for="id_empleado">
                                        <i class="fas fa-user-tie"></i> Vincular con Empleado (Opcional)
                                    </label>
                                    <select name="id_empleado" id="id_empleado" class="form-control">
                                        <option value="">-- Sin vincular a empleado --</option>
                                        <?php foreach ($empleados as $emp): 
                                            $email_usado = in_array($emp['email'], $emails_usados);
                                        ?>
                                            <option value="<?php echo $emp['id_empleado']; ?>"
                                                    data-email="<?php echo htmlspecialchars($emp['email']); ?>"
                                                    data-nombre="<?php echo htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']); ?>"
                                                    data-codigo="<?php echo htmlspecialchars($emp['codigo_empleado']); ?>"
                                                    data-telefono="<?php echo htmlspecialchars($emp['telefono']); ?>"
                                                    data-rol="<?php echo htmlspecialchars($emp['rol']); ?>"
                                                    <?php if ($email_usado) echo 'disabled'; ?>>
                                                <?php echo htmlspecialchars($emp['codigo_empleado'] . ' - ' . $emp['nombres'] . ' ' . $emp['apellidos']); ?>
                                                <?php if ($email_usado) echo ' (Ya tiene cuenta)'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">
                                        Si selecciona un empleado, el email y rol se autocompletarán
                                    </small>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="username">Usuario <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                </div>
                                                <input type="text" 
                                                       name="username" 
                                                       id="username"
                                                       class="form-control"
                                                       placeholder="Ejemplo: jperez"
                                                       required
                                                       maxlength="50">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                </div>
                                                <input type="email" 
                                                       name="email" 
                                                       id="email"
                                                       class="form-control"
                                                       placeholder="ejemplo@correo.com"
                                                       required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="id_rol">Rol del Usuario <span class="text-danger">*</span></label>
                                    <select name="id_rol" id="id_rol" class="form-control" required>
                                        <option value="">Seleccione un rol...</option>
                                        <?php foreach ($roles_datos as $roles_dato): ?>
                                            <option value="<?php echo $roles_dato['id_rol'];?>">
                                                <?php echo $roles_dato['rol'];?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password_user">Contraseña <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                </div>
                                                <input type="password" 
                                                       name="password_user" 
                                                       id="password_user"
                                                       class="form-control"
                                                       required
                                                       minlength="6">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">Mínimo 6 caracteres</small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password_repeat">Repetir Contraseña <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                </div>
                                                <input type="password" 
                                                       name="password_repeat" 
                                                       id="password_repeat"
                                                       class="form-control"
                                                       required
                                                       minlength="6">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                
                                <div class="form-group">
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar Usuario
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Panel Informativo -->
                <div class="col-md-4">
                    <div class="card card-info" id="cardEmpleadoInfo" style="display:none;">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle"></i> Empleado Seleccionado</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-5">Código:</dt>
                                <dd class="col-sm-7"><code id="info_codigo"></code></dd>
                                
                                <dt class="col-sm-5">Nombre:</dt>
                                <dd class="col-sm-7" id="info_nombre"></dd>
                                
                                <dt class="col-sm-5">Email:</dt>
                                <dd class="col-sm-7"><small id="info_email"></small></dd>
                                
                                <dt class="col-sm-5">Teléfono:</dt>
                                <dd class="col-sm-7" id="info_telefono"></dd>
                                
                                <dt class="col-sm-5">Rol:</dt>
                                <dd class="col-sm-7"><span class="badge badge-info" id="info_rol"></span></dd>
                            </dl>
                        </div>
                    </div>

                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Importante</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Vinculación:</strong></p>
                            <ul class="mb-0">
                                <li>Si vincula con un empleado, NO puede usar el mismo email para otro usuario</li>
                                <li>El email del empleado se usará para vincular automáticamente</li>
                                <li>Guarde bien las credenciales creadas</li>
                            </ul>
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
    // Cuando selecciona un empleado
    $('#id_empleado').change(function() {
        var selectedOption = $(this).find('option:selected');
        
        if ($(this).val() == '') {
            $('#cardEmpleadoInfo').hide();
            $('#email').val('').prop('readonly', false);
            $('#id_rol').val('');
            return;
        }
        
        // Autocompletar datos del empleado
        var email = selectedOption.data('email');
        var nombre = selectedOption.data('nombre');
        var codigo = selectedOption.data('codigo');
        var telefono = selectedOption.data('telefono');
        var rol = selectedOption.data('rol');
        
        $('#email').val(email).prop('readonly', true);
        
        // Mostrar info del empleado
        $('#info_codigo').text(codigo);
        $('#info_nombre').text(nombre);
        $('#info_email').text(email);
        $('#info_telefono').text(telefono);
        $('#info_rol').text(rol);
        $('#cardEmpleadoInfo').show();
        
        // Sugerir username basado en código
        var suggestedUsername = 'emp_' + codigo.toLowerCase();
        if ($('#username').val() == '') {
            $('#username').val(suggestedUsername);
        }
    });

    // Toggle password visibility
    $('#togglePassword').click(function() {
        var passwordField = $('#password_user');
        var type = passwordField.attr('type') === 'password' ? 'text' : 'password';
        passwordField.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    // Validación de contraseñas coincidentes
    $('#formCrearUsuario').submit(function(e) {
        var password = $('#password_user').val();
        var passwordRepeat = $('#password_repeat').val();
        
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
    });
});
</script>
