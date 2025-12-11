<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Obtener ID del usuario
$id_usuario_get = $_GET['id'] ?? 0;

if ($id_usuario_get <= 0) {
    $_SESSION['error'] = 'ID de usuario inválido';
    header('Location: index.php');
    exit;
}

// Obtener datos del usuario
try {
    $sql = "SELECT u.*, r.rol, e.nombres, e.apellidos, e.foto, e.telefono
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
    $nombres = $usuario_dato['nombres'] ?? 'N/A';
    $apellidos = $usuario_dato['apellidos'] ?? '';
    $telefono = $usuario_dato['telefono'] ?? 'N/A';
    $foto = $usuario_dato['foto'] ?? 'assets/img/default-user.png';
    $fecha_creacion = $usuario_dato['fyh_creacion'] ?? '';
    $ultimo_acceso = $usuario_dato['ultimo_acceso'] ?? 'Nunca';
    
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
                    <h1 class="m-0">Datos del Usuario</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="profile-user-img img-fluid img-circle"
                                     src="<?php echo URL_BASE . '/' . $foto; ?>"
                                     alt="Foto de usuario"
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            </div>

                            <h3 class="profile-username text-center">
                                <?php echo htmlspecialchars($nombres . ' ' . $apellidos); ?>
                            </h3>

                            <p class="text-muted text-center">@<?php echo htmlspecialchars($username); ?></p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Rol</b> 
                                    <span class="float-right badge badge-primary">
                                        <?php echo htmlspecialchars($rol); ?>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Email</b> 
                                    <span class="float-right">
                                        <?php echo htmlspecialchars($email); ?>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Teléfono</b> 
                                    <span class="float-right">
                                        <?php echo htmlspecialchars($telefono); ?>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Registrado</b> 
                                    <span class="float-right">
                                        <?php echo date('d/m/Y', strtotime($fecha_creacion)); ?>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Último Acceso</b> 
                                    <span class="float-right">
                                        <?php 
                                        if ($ultimo_acceso !== 'Nunca') {
                                            echo date('d/m/Y H:i', strtotime($ultimo_acceso));
                                        } else {
                                            echo $ultimo_acceso;
                                        }
                                        ?>
                                    </span>
                                </li>
                            </ul>

                            <a href="index.php" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Volver al Listado
                            </a>
                            <a href="update.php?id=<?php echo $id_usuario_get; ?>" class="btn btn-success btn-block">
                                <i class="fas fa-edit"></i> Editar Usuario
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>
