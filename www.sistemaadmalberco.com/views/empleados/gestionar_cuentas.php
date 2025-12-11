<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');

// SOLO ADMINISTRADORES
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    $_SESSION['error'] = 'Acceso denegado. Solo administradores pueden acceder.';
    header('Location: ' . URL_BASE . '/views/empleados/');
    exit;
}

require_once '../../models/empleado.php';
include ('../../contans/layout/parte1.php');

$empleadoModel = new Empleado();

// Obtener empleados sin cuenta
$sinCuenta = $empleadoModel->getSinCuenta();

// Obtener empleados con cuenta
$conCuenta = $empleadoModel->getConUsuario();

// Separar los que tienen cuenta de los que no
$conCuentaFiltrado = array_filter($conCuenta, function($emp) {
    return $emp['id_usuario'] !== null;
});

?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-users-cog"></i> Gestionar Cuentas de Empleados
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Empleados</a></li>
                        <li class="breadcrumb-item active">Gestionar Cuentas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <!-- Alert de permisos -->
            <div class="alert alert-info alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5><i class="icon fas fa-info-circle"></i> Vinculación por Email</h5>
                Las cuentas se vinculan automáticamente cuando el email del empleado coincide con el email del usuario.
            </div>
            
            <?php if (isset($_SESSION['credenciales'])): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5><i class="icon fas fa-check"></i> ¡Cuenta Creada!</h5>
                <strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['credenciales']['username']); ?><br>
                <strong>Contraseña:</strong> <?php echo htmlspecialchars($_SESSION['credenciales']['password']); ?><br>
                <small class="text-muted">Guarda estas credenciales. La contraseña es el número de documento del empleado.</small>
            </div>
            <?php 
            unset($_SESSION['credenciales']);
            endif; 
            ?>
            
            <!-- Estadísticas -->
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-user-slash"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Sin Cuenta</span>
                            <span class="info-box-number"><?php echo count($sinCuenta); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Con Cuenta</span>
                            <span class="info-box-number"><?php echo count($conCuentaFiltrado); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Empleados</span>
                            <span class="info-box-number"><?php echo count($sinCuenta) + count($conCuentaFiltrado); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Empleados SIN cuenta -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-exclamation-triangle"></i> Empleados Sin Cuenta de Usuario
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if (count($sinCuenta) > 0): ?>
                            <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">Nro</th>
                                        <th width="10%">Código</th>
                                        <th>Empleado</th>
                                        <th>Email</th>
                                        <th width="10%">Rol</th>
                                        <th width="12%">Documento</th>
                                        <th width="15%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $contador = 1;
                                    foreach ($sinCuenta as $emp): 
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $contador++; ?></td>
                                        <td class="text-center">
                                            <code class="bg-light p-1"><?php echo htmlspecialchars($emp['codigo_empleado']); ?></code>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="mr-2">
                                                    <i class="fas fa-user-circle fa-2x text-secondary"></i>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($emp['telefono']); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <i class="fas fa-envelope text-info"></i>
                                            <?php echo htmlspecialchars($emp['email']); ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info p-2">
                                                <?php echo htmlspecialchars($emp['rol']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <small><?php echo htmlspecialchars($emp['numero_documento']); ?></small>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" 
                                                    class="btn btn-success btn-sm"
                                                    onclick="confirmarCrearCuenta(
                                                        <?php echo $emp['id_empleado']; ?>,
                                                        '<?php echo addslashes($emp['nombres'] . ' ' . $emp['apellidos']); ?>',
                                                        '<?php echo addslashes($emp['codigo_empleado']); ?>',
                                                        '<?php echo addslashes($emp['email']); ?>',
                                                        '<?php echo addslashes($emp['numero_documento']); ?>',
                                                        '<?php echo addslashes($emp['rol']); ?>'
                                                    )"
                                                    title="Crear cuenta de usuario">
                                                <i class="fas fa-user-plus"></i> Crear Cuenta
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <strong>¡Excelente!</strong> Todos los empleados tienen cuenta de usuario.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Empleados CON cuenta -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-check-circle"></i> Empleados Con Cuenta de Usuario
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if (count($conCuentaFiltrado) > 0): ?>
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Empleado</th>
                                        <th>Email</th>
                                        <th>Username</th>
                                        <th>Estado Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($conCuentaFiltrado as $emp): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($emp['codigo_empleado']); ?></code></td>
                                        <td><?php echo htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']); ?></td>
                                        <td><?php echo htmlspecialchars($emp['email']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($emp['username']); ?></strong></td>
                                        <td>
                                            <span class="badge badge-<?php echo $emp['usuario_estado'] == 'ACTIVO' ? 'success' : 'danger'; ?>">
                                                <?php echo $emp['usuario_estado']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No hay empleados con cuenta de usuario aún.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Formulario oculto para crear cuenta -->
<form id="formCrearCuenta" action="../../controllers/empleados/crear_cuenta.php" method="POST" style="display:none;">
    <input type="hidden" name="id_empleado" id="id_empleado_crear">
</form>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
function confirmarCrearCuenta(idEmpleado, nombreCompleto, codigoEmp, email, documento, rol) {
    Swal.fire({
        title: '¿Crear Cuenta de Usuario?',
        html: `
            <div class="text-left">
                <h5 class="mb-3"><i class="fas fa-user-plus text-success"></i> Datos del Empleado</h5>
                <table class="table table-sm table-bordered">
                    <tr>
                        <th width="40%">Nombre Completo:</th>
                        <td><strong>${nombreCompleto}</strong></td>
                    </tr>
                    <tr>
                        <th>Código Empleado:</th>
                        <td><code class="bg-light p-1">${codigoEmp}</code></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><i class="fas fa-envelope text-info"></i> ${email}</td>
                    </tr>
                    <tr>
                        <th>Rol:</th>
                        <td><span class="badge badge-info">${rol}</span></td>
                    </tr>
                    <tr>
                        <th>Documento:</th>
                        <td>${documento}</td>
                    </tr>
                </table>

                <div class="alert alert-info mb-0 mt-3">
                    <h6><i class="fas fa-key"></i> Credenciales que se generarán:</h6>
                    <ul class="mb-0">
                        <li><strong>Username:</strong> <code>emp_${codigoEmp.toLowerCase()}</code></li>
                        <li><strong>Password:</strong> <code>${documento}</code> (número de documento)</li>
                        <li><strong>Email vinculado:</strong> ${email}</li>
                    </ul>
                </div>
            </div>
        `,
        icon: 'question',
        width: '600px',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-check"></i> Sí, Crear Cuenta',
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            document.getElementById('id_empleado_crear').value = idEmpleado;
            document.getElementById('formCrearCuenta').submit();
        }
    });
}
</script>
