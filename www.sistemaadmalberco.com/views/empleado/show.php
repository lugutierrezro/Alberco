<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Obtener ID del empleado
$id_empleado = (int)($_GET['id'] ?? 0);

if ($id_empleado <= 0) {
    header('Location: index.php');
    exit;
}

// Obtener datos del empleado
$sql = "SELECT e.*, r.rol as nombre_rol, u.username, u.email as email_usuario
        FROM tb_empleados e
        LEFT JOIN tb_roles r ON e.id_rol = r.id_rol
        LEFT JOIN tb_usuarios u ON e.id_empleado = u.id_empleado
        WHERE e.id_empleado = :id AND e.estado_registro = 'ACTIVO'";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_empleado]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empleado) {
    $_SESSION['error'] = 'Empleado no encontrado';
    header('Location: index.php');
    exit;
}

// Badge de estado laboral
$estado_color = match($empleado['estado_laboral']) {
    'ACTIVO' => 'success',
    'VACACIONES' => 'info',
    'LICENCIA' => 'warning',
    'SUSPENDIDO' => 'danger',
    'RETIRADO' => 'secondary',
    default => 'secondary'
};
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detalle del Empleado</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Empleados</a></li>
                        <li class="breadcrumb-item active">Ver</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Información Personal -->
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <?php if (!empty($empleado['foto'])): ?>
                                    <img class="profile-user-img img-fluid img-circle"
                                         src="<?php echo URL_BASE . '/' . $empleado['foto']; ?>"
                                         alt="Foto del empleado">
                                <?php else: ?>
                                    <img class="profile-user-img img-fluid img-circle"
                                         src="<?php echo URL_BASE; ?>/assets/img/default-user.png"
                                         alt="Sin foto">
                                <?php endif; ?>
                            </div>

                            <h3 class="profile-username text-center">
                                <?php echo htmlspecialchars($empleado['nombres'] . ' ' . $empleado['apellidos']); ?>
                            </h3>

                            <p class="text-muted text-center">
                                <span class="badge badge-primary">
                                    <?php echo htmlspecialchars($empleado['nombre_rol']); ?>
                                </span>
                            </p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Código</b> 
                                    <a class="float-right"><?php echo htmlspecialchars($empleado['codigo_empleado']); ?></a>
                                </li>
                                <li class="list-group-item">
                                    <b>Estado</b>
                                    <span class="float-right badge badge-<?php echo $estado_color; ?>">
                                        <?php echo htmlspecialchars($empleado['estado_laboral']); ?>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Turno</b> 
                                    <a class="float-right"><?php echo htmlspecialchars($empleado['turno']); ?></a>
                                </li>
                                <?php if (!empty($empleado['salario'])): ?>
                                <li class="list-group-item">
                                    <b>Salario</b> 
                                    <a class="float-right">S/ <?php echo number_format($empleado['salario'], 2); ?></a>
                                </li>
                                <?php endif; ?>
                            </ul>

                            <a href="update.php?id=<?php echo $id_empleado; ?>" class="btn btn-primary btn-block">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="index.php" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Información Detallada -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#personal" data-toggle="tab">Datos Personales</a></li>
                                <li class="nav-item"><a class="nav-link" href="#contacto" data-toggle="tab">Contacto</a></li>
                                <li class="nav-item"><a class="nav-link" href="#laboral" data-toggle="tab">Información Laboral</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- Datos Personales -->
                                <div class="active tab-pane" id="personal">
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <th style="width: 200px;">Nombres:</th>
                                                <td><?php echo htmlspecialchars($empleado['nombres']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Apellidos:</th>
                                                <td><?php echo htmlspecialchars($empleado['apellidos']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tipo de Documento:</th>
                                                <td><?php echo htmlspecialchars($empleado['tipo_documento']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>N° de Documento:</th>
                                                <td><?php echo htmlspecialchars($empleado['numero_documento']); ?></td>
                                            </tr>
                                            <?php if (!empty($empleado['fecha_nacimiento'])): ?>
                                            <tr>
                                                <th>Fecha de Nacimiento:</th>
                                                <td>
                                                    <?php 
                                                    $fecha_nac = new DateTime($empleado['fecha_nacimiento']);
                                                    echo $fecha_nac->format('d/m/Y');
                                                    
                                                    // Calcular edad
                                                    $hoy = new DateTime();
                                                    $edad = $hoy->diff($fecha_nac)->y;
                                                    echo " <small class='text-muted'>($edad años)</small>";
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if (!empty($empleado['direccion'])): ?>
                                            <tr>
                                                <th>Dirección:</th>
                                                <td><?php echo htmlspecialchars($empleado['direccion']); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Contacto -->
                                <div class="tab-pane" id="contacto">
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <th style="width: 200px;">Email:</th>
                                                <td>
                                                    <a href="mailto:<?php echo $empleado['email']; ?>">
                                                        <?php echo htmlspecialchars($empleado['email']); ?>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Teléfono:</th>
                                                <td>
                                                    <a href="tel:<?php echo $empleado['telefono']; ?>">
                                                        <?php echo htmlspecialchars($empleado['telefono']); ?>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php if (!empty($empleado['celular'])): ?>
                                            <tr>
                                                <th>Celular:</th>
                                                <td>
                                                    <a href="tel:<?php echo $empleado['celular']; ?>">
                                                        <?php echo htmlspecialchars($empleado['celular']); ?>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if (!empty($empleado['username'])): ?>
                                            <tr>
                                                <th>Usuario del Sistema:</th>
                                                <td>
                                                    <span class="badge badge-info">
                                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($empleado['username']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Información Laboral -->
                                <div class="tab-pane" id="laboral">
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <th style="width: 200px;">Código de Empleado:</th>
                                                <td><?php echo htmlspecialchars($empleado['codigo_empleado']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Rol:</th>
                                                <td>
                                                    <span class="badge badge-primary">
                                                        <?php echo htmlspecialchars($empleado['nombre_rol']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Fecha de Contratación:</th>
                                                <td>
                                                    <?php 
                                                    $fecha_cont = new DateTime($empleado['fecha_contratacion']);
                                                    echo $fecha_cont->format('d/m/Y');
                                                    
                                                    // Calcular antigüedad
                                                    $hoy = new DateTime();
                                                    $antiguedad = $hoy->diff($fecha_cont);
                                                    $anos = $antiguedad->y;
                                                    $meses = $antiguedad->m;
                                                    
                                                    echo " <small class='text-muted'>(";
                                                    if ($anos > 0) echo "$anos año" . ($anos > 1 ? 's' : '');
                                                    if ($meses > 0) echo ($anos > 0 ? ' y ' : '') . "$meses mes" . ($meses > 1 ? 'es' : '');
                                                    echo ")</small>";
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Estado Laboral:</th>
                                                <td>
                                                    <span class="badge badge-<?php echo $estado_color; ?>">
                                                        <?php echo htmlspecialchars($empleado['estado_laboral']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Turno:</th>
                                                <td><?php echo htmlspecialchars($empleado['turno']); ?></td>
                                            </tr>
                                            <?php if (!empty($empleado['salario'])): ?>
                                            <tr>
                                                <th>Salario:</th>
                                                <td><strong>S/ <?php echo number_format($empleado['salario'], 2); ?></strong></td>
                                            </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <th>Fecha de Registro:</th>
                                                <td>
                                                    <?php 
                                                    $fecha_reg = new DateTime($empleado['fyh_creacion']);
                                                    echo $fecha_reg->format('d/m/Y H:i:s');
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Última Actualización:</th>
                                                <td>
                                                    <?php 
                                                    $fecha_act = new DateTime($empleado['fyh_actualizacion']);
                                                    echo $fecha_act->format('d/m/Y H:i:s');
                                                    ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>
