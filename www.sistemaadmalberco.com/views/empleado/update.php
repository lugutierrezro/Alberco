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
$sql = "SELECT e.*, r.rol as nombre_rol
        FROM tb_empleados e
        LEFT JOIN tb_roles r ON e.id_rol = r.id_rol
        WHERE e.id_empleado = :id AND e.estado_registro = 'ACTIVO'";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_empleado]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empleado) {
    $_SESSION['error'] = 'Empleado no encontrado';
    header('Location: index.php');
    exit;
}

// Obtener roles activos
$sql_roles = "SELECT id_rol, rol FROM tb_roles WHERE estado_registro = 'ACTIVO' ORDER BY rol";
$stmt_roles = $pdo->prepare($sql_roles);
$stmt_roles->execute();
$roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Editar Empleado</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Empleados</a></li>
                        <li class="breadcrumb-item active">Editar</li>
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
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Modificar Datos del Empleado</h3>
                        </div>

                        <form action="../../controllers/empleado/actualizar.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="id_empleado" value="<?php echo $id_empleado; ?>">
                            
                            <div class="card-body">
                                <div class="row">
                                    <!-- Columna Izquierda -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="codigo_empleado">Código</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="codigo_empleado" 
                                                   value="<?php echo htmlspecialchars($empleado['codigo_empleado']); ?>"
                                                   disabled>
                                            <small class="form-text text-muted">El código no se puede modificar</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="nombres">Nombres <span class="text-danger">*</span></label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="nombres" 
                                                   name="nombres" 
                                                   value="<?php echo htmlspecialchars($empleado['nombres']); ?>"
                                                   required>
                                        </div>

                                        <div class="form-group">
                                            <label for="apellidos">Apellidos <span class="text-danger">*</span></label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="apellidos" 
                                                   name="apellidos" 
                                                   value="<?php echo htmlspecialchars($empleado['apellidos']); ?>"
                                                   required>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="tipo_documento">Tipo Doc.</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           value="<?php echo htmlspecialchars($empleado['tipo_documento']); ?>"
                                                           disabled>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label for="numero_documento">N° Documento</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           value="<?php echo htmlspecialchars($empleado['numero_documento']); ?>"
                                                           disabled>
                                                    <small class="form-text text-muted">El documento no se puede modificar</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="email">Email <span class="text-danger">*</span></label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   id="email" 
                                                   name="email" 
                                                   value="<?php echo htmlspecialchars($empleado['email']); ?>"
                                                   required>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="telefono">Teléfono <span class="text-danger">*</span></label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="telefono" 
                                                           name="telefono" 
                                                           value="<?php echo htmlspecialchars($empleado['telefono']); ?>"
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="celular">Celular</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="celular" 
                                                           name="celular"
                                                           value="<?php echo htmlspecialchars($empleado['celular'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Columna Derecha -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="direccion">Dirección</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="direccion" 
                                                   name="direccion"
                                                   value="<?php echo htmlspecialchars($empleado['direccion'] ?? ''); ?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="id_rol">Rol <span class="text-danger">*</span></label>
                                            <select class="form-control" id="id_rol" name="id_rol" required>
                                                <option value="">Seleccione un rol</option>
                                                <?php foreach ($roles as $rol): ?>
                                                    <option value="<?php echo $rol['id_rol']; ?>"
                                                            <?php echo ($rol['id_rol'] == $empleado['id_rol']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($rol['rol']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="salario">Salario (S/)</label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="salario" 
                                                   name="salario" 
                                                   step="0.01"
                                                   min="0"
                                                   value="<?php echo $empleado['salario']; ?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="turno">Turno</label>
                                            <select class="form-control" id="turno" name="turno">
                                                <option value="ROTATIVO" <?php echo ($empleado['turno'] == 'ROTATIVO') ? 'selected' : ''; ?>>ROTATIVO</option>
                                                <option value="MAÑANA" <?php echo ($empleado['turno'] == 'MAÑANA') ? 'selected' : ''; ?>>MAÑANA</option>
                                                <option value="TARDE" <?php echo ($empleado['turno'] == 'TARDE') ? 'selected' : ''; ?>>TARDE</option>
                                                <option value="NOCHE" <?php echo ($empleado['turno'] == 'NOCHE') ? 'selected' : ''; ?>>NOCHE</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="estado_laboral">Estado Laboral</label>
                                            <select class="form-control" id="estado_laboral" name="estado_laboral">
                                                <option value="ACTIVO" <?php echo ($empleado['estado_laboral'] == 'ACTIVO') ? 'selected' : ''; ?>>ACTIVO</option>
                                                <option value="VACACIONES" <?php echo ($empleado['estado_laboral'] == 'VACACIONES') ? 'selected' : ''; ?>>VACACIONES</option>
                                                <option value="LICENCIA" <?php echo ($empleado['estado_laboral'] == 'LICENCIA') ? 'selected' : ''; ?>>LICENCIA</option>
                                                <option value="SUSPENDIDO" <?php echo ($empleado['estado_laboral'] == 'SUSPENDIDO') ? 'selected' : ''; ?>>SUSPENDIDO</option>
                                                <option value="RETIRADO" <?php echo ($empleado['estado_laboral'] == 'RETIRADO') ? 'selected' : ''; ?>>RETIRADO</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="foto">Cambiar Foto</label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" 
                                                           class="custom-file-input" 
                                                           id="foto" 
                                                           name="foto"
                                                           accept="image/jpeg,image/png,image/jpg,image/gif">
                                                    <label class="custom-file-label" for="foto">Seleccionar nueva imagen</label>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">
                                                Deja en blanco si no deseas cambiar la foto
                                            </small>
                                        </div>

                                        <div class="form-group text-center">
                                            <?php if (!empty($empleado['foto'])): ?>
                                                <label>Foto Actual:</label><br>
                                                <img src="<?php echo URL_BASE . '/' . $empleado['foto']; ?>" 
                                                     alt="Foto actual" 
                                                     class="img-thumbnail" 
                                                     style="max-width: 200px;"
                                                     id="current-image">
                                            <?php endif; ?>
                                            <div id="preview-container" style="display: none; margin-top: 10px;">
                                                <label>Nueva Foto:</label><br>
                                                <img id="preview-image" src="" alt="Vista previa" class="img-thumbnail" style="max-width: 200px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Actualizar
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
    // Preview de imagen
    document.getElementById('foto').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-image').src = e.target.result;
                document.getElementById('preview-container').style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });

    // Actualizar label del input file
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
</script>
