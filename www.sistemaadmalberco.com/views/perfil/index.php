<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');

// Obtener datos del usuario con rol y empleado vinculado (por email)
$pdo = getDB();
$id_usuario = $_SESSION['id_usuario'];

$stmt = $pdo->prepare("SELECT u.*, 
                              r.rol as nombre_rol,
                              e.id_empleado,
                              e.codigo_empleado,
                              e.nombres as emp_nombres,
                              e.apellidos as emp_apellidos,
                              e.numero_documento as emp_documento,
                              e.telefono as emp_telefono,
                              e.direccion as emp_direccion,
                              e.fecha_contratacion,
                              e.salario,
                              e.turno,
                              e.estado_laboral,
                              e.foto as emp_foto
                       FROM tb_usuarios u
                       LEFT JOIN tb_roles r ON u.id_rol = r.id_rol
                       LEFT JOIN tb_empleados e ON u.email = e.email
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
                    <h1 class="m-0"><i class="fas fa-user-circle mr-2 text-orange"></i>Mi Perfil</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item active">Mi Perfil</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Tarjeta de Perfil -->
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <div class="profile-avatar mb-3">
                                    <?php if (!empty($usuario['emp_foto'])): ?>
                                        <img src="<?php echo URL_BASE . '/' . $usuario['emp_foto']; ?>" 
                                             class="img-circle elevation-2" 
                                             style="width: 100px; height: 100px; object-fit: cover;" 
                                             alt="Foto de perfil">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle fa-5x text-orange"></i>
                                    <?php endif; ?>
                                </div>
                                <h3 class="profile-username text-center">
                                    <?php 
                                    if (!empty($usuario['emp_nombres'])) {
                                        echo htmlspecialchars($usuario['emp_nombres'] . ' ' . $usuario['emp_apellidos']);
                                    } else {
                                        echo htmlspecialchars($usuario['username']);
                                    }
                                    ?>
                                </h3>
                                <p class="text-muted text-center">
                                    <span class="badge badge-info">
                                        <?php echo htmlspecialchars($usuario['nombre_rol'] ?? 'Sin rol'); ?>
                                    </span>
                                </p>
                                
                                <?php if (!empty($usuario['id_empleado'])): ?>
                                <div class="alert alert-success mt-2 py-1">
                                    <small>
                                        <i class="fas fa-link"></i> 
                                        Vinculado como empleado
                                        <br>
                                        <strong><?php echo htmlspecialchars($usuario['codigo_empleado']); ?></strong>
                                    </small>
                                </div>
                                <?php endif; ?>
                            </div>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b><i class="fas fa-envelope mr-2"></i>Email</b>
                                    <span class="float-right"><?php echo htmlspecialchars($usuario['email']); ?></span>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-user-tag mr-2"></i>Usuario</b>
                                    <span class="float-right"><?php echo htmlspecialchars($usuario['username']); ?></span>
                                </li>
                                
                                <?php if (!empty($usuario['id_empleado'])): ?>
                                <li class="list-group-item">
                                    <b><i class="fas fa-id-card mr-2"></i>Código Empleado</b>
                                    <span class="float-right">
                                        <code><?php echo htmlspecialchars($usuario['codigo_empleado']); ?></code>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-briefcase mr-2"></i>Estado Laboral</b>
                                    <span class="float-right">
                                        <span class="badge badge-<?php echo $usuario['estado_laboral'] == 'ACTIVO' ? 'success' : 'warning'; ?>">
                                            <?php echo $usuario['estado_laboral']; ?>
                                        </span>
                                    </span>
                                </li>
                                <?php endif; ?>
                                
                                <li class="list-group-item">
                                    <b><i class="fas fa-calendar mr-2"></i>Miembro desde</b>
                                    <span class="float-right">
                                        <?php 
                                        $fecha = new DateTime($usuario['fyh_creacion']);
                                        echo $fecha->format('d/m/Y');
                                        ?>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-toggle-on mr-2"></i>Estado</b>
                                    <span class="float-right">
                                        <span class="badge badge-<?php echo $usuario['estado_registro'] == 'ACTIVO' ? 'success' : 'danger'; ?>">
                                            <?php echo $usuario['estado_registro']; ?>
                                        </span>
                                    </span>
                                </li>
                            </ul>

                            <a href="editar.php" class="btn btn-primary btn-block">
                                <i class="fas fa-edit mr-2"></i>Editar Perfil
                            </a>
                        </div>
                    </div>

                    <?php if (!empty($usuario['id_empleado'])): ?>
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-id-badge mr-2"></i>Información Laboral</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-6">Contratación:</dt>
                                <dd class="col-sm-6">
                                    <?php echo date('d/m/Y', strtotime($usuario['fecha_contratacion'])); ?>
                                </dd>
                                
                                <?php if ($usuario['salario']): ?>
                                <dt class="col-sm-6">Salario:</dt>
                                <dd class="col-sm-6">S/ <?php echo number_format($usuario['salario'], 2); ?></dd>
                                <?php endif; ?>
                                
                                <dt class="col-sm-6">Turno:</dt>
                                <dd class="col-sm-6">
                                    <span class="badge badge-info"><?php echo $usuario['turno']; ?></span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>Sin Vinculación</h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">
                                <small>Esta cuenta no está vinculada a ningún empleado del sistema.</small>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Información Detallada -->
                <div class="col-md-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#info" data-toggle="tab">
                                        <i class="fas fa-info-circle mr-2"></i>Información
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#seguridad" data-toggle="tab">
                                        <i class="fas fa-shield-alt mr-2"></i>Seguridad
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- Información -->
                                <div class="active tab-pane" id="info">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><i class="fas fa-user mr-2"></i>Nombres Completos</label>
                                                <p class="form-control-static">
                                                    <?php echo htmlspecialchars($usuario['nombres'] ?? 'No especificado'); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><i class="fas fa-at mr-2"></i>Correo Electrónico</label>
                                                <p class="form-control-static">
                                                    <?php echo htmlspecialchars($usuario['email']); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><i class="fas fa-user-tag mr-2"></i>Rol</label>
                                                <p class="form-control-static">
                                                    <span class="badge badge-info">
                                                        <?php echo htmlspecialchars($usuario['rol'] ?? 'Sin rol'); ?>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><i class="fas fa-clock mr-2"></i>Última Actualización</label>
                                                <p class="form-control-static">
                                                    <?php 
                                                    $fecha_act = new DateTime($usuario['fyh_actualizacion']);
                                                    echo $fecha_act->format('d/m/Y H:i');
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Seguridad -->
                                <div class="tab-pane" id="seguridad">
                                    <form method="POST" action="../../controllers/usuario/cambiar_password.php">
                                        <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                                        
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Para cambiar tu contraseña, completa los siguientes campos.
                                        </div>

                                        <div class="form-group">
                                            <label>Contraseña Actual</label>
                                            <input type="password" name="password_actual" class="form-control" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Nueva Contraseña</label>
                                            <input type="password" name="password_nueva" class="form-control" required minlength="6">
                                            <small class="form-text text-muted">
                                                Mínimo 6 caracteres
                                            </small>
                                        </div>

                                        <div class="form-group">
                                            <label>Confirmar Nueva Contraseña</label>
                                            <input type="password" name="password_confirmar" class="form-control" required>
                                        </div>

                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-key mr-2"></i>Cambiar Contraseña
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-avatar {
    transition: transform 0.3s ease;
}
.profile-avatar:hover {
    transform: scale(1.1);
}
.form-control-static {
    padding: 0.375rem 0.75rem;
    background: #f8f9fa;
    border-radius: 0.25rem;
    margin-bottom: 0;
}
</style>

<?php 
include_once('../../contans/layout/mensajes.php'); 
include_once('../../contans/layout/parte2.php'); 
?>
