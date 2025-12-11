<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Obtener roles activos
$sql_roles = "SELECT id_rol, rol FROM tb_roles WHERE estado_registro = 'ACTIVO' ORDER BY rol";
$stmt_roles = $pdo->prepare($sql_roles);
$stmt_roles->execute();
$roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

// Generar siguiente código de empleado automáticamente
$sql_ultimo = "SELECT codigo_empleado FROM tb_empleados ORDER BY id_empleado DESC LIMIT 1";
$stmt_ultimo = $pdo->prepare($sql_ultimo);
$stmt_ultimo->execute();
$ultimo = $stmt_ultimo->fetch(PDO::FETCH_ASSOC);

if ($ultimo) {
    // Extraer el número del último código (ej: EMP-001 -> 001)
    preg_match('/EMP-(\d+)/', $ultimo['codigo_empleado'], $matches);
    $siguiente_numero = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
} else {
    $siguiente_numero = 1;
}

// Formato: EMP-001, EMP-002, etc
$codigo_generado = 'EMP-' . str_pad($siguiente_numero, 3, '0', STR_PAD_LEFT);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Nuevo Empleado</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Empleados</a></li>
                        <li class="breadcrumb-item active">Nuevo</li>
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
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Datos del Empleado</h3>
                        </div>

                        <form action="../../controllers/empleado/crear.php" method="post" enctype="multipart/form-data">
                            <div class="card-body">
                                <div class="row">
                                    <!-- Columna Izquierda -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="codigo_empleado">
                                                Código 
                                                <span class="badge badge-success">
                                                    <i class="fas fa-magic"></i> Automático
                                                </span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-success">
                                                        <i class="fas fa-barcode text-white"></i>
                                                    </span>
                                                </div>
                                                <input type="text" 
                                                       class="form-control bg-light" 
                                                       id="codigo_empleado" 
                                                       name="codigo_empleado" 
                                                       value="<?php echo htmlspecialchars($codigo_generado); ?>"
                                                       readonly
                                                       required>
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i> 
                                                Este código se genera automáticamente
                                            </small>
                                        </div>

                                        <div class="form-group">
                                            <label for="nombres">Nombres <span class="text-danger">*</span></label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="nombres" 
                                                   name="nombres" 
                                                   required>
                                        </div>

                                        <div class="form-group">
                                            <label for="apellidos">Apellidos <span class="text-danger">*</span></label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="apellidos" 
                                                   name="apellidos" 
                                                   required>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="tipo_documento">Tipo Doc. <span class="text-danger">*</span></label>
                                                    <select class="form-control" id="tipo_documento" name="tipo_documento" required>
                                                        <option value="DNI" selected>DNI</option>
                                                        <option value="CE">CE</option>
                                                        <option value="PASAPORTE">PASAPORTE</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label for="numero_documento">N° Documento <span class="text-danger">*</span></label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="numero_documento" 
                                                           name="numero_documento" 
                                                           maxlength="20"
                                                           required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="email">Email <span class="text-danger">*</span></label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   id="email" 
                                                   name="email" 
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
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="celular">Celular</label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="celular" 
                                                           name="celular">
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
                                                   name="direccion">
                                        </div>

                                        <div class="form-group">
                                            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="fecha_nacimiento" 
                                                   name="fecha_nacimiento">
                                        </div>

                                        <div class="form-group">
                                            <label for="fecha_contratacion">Fecha de Contratación <span class="text-danger">*</span></label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="fecha_contratacion" 
                                                   name="fecha_contratacion" 
                                                   value="<?php echo date('Y-m-d'); ?>"
                                                   required>
                                        </div>

                                        <div class="form-group">
                                            <label for="id_rol">Rol <span class="text-danger">*</span></label>
                                            <select class="form-control" id="id_rol" name="id_rol" required>
                                                <option value="">Seleccione un rol</option>
                                                <?php foreach ($roles as $rol): ?>
                                                    <option value="<?php echo $rol['id_rol']; ?>">
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
                                                   min="0">
                                        </div>

                                        <div class="form-group">
                                            <label for="turno">Turno</label>
                                            <select class="form-control" id="turno" name="turno">
                                                <option value="ROTATIVO" selected>ROTATIVO</option>
                                                <option value="MAÑANA">MAÑANA</option>
                                                <option value="TARDE">TARDE</option>
                                                <option value="NOCHE">NOCHE</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="foto">Foto del Empleado</label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" 
                                                           class="custom-file-input" 
                                                           id="foto" 
                                                           name="foto"
                                                           accept="image/jpeg,image/png,image/jpg,image/gif">
                                                    <label class="custom-file-label" for="foto">Seleccionar imagen</label>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">
                                                Formatos permitidos: JPG, JPEG, PNG, GIF. Tamaño máximo: 2MB
                                            </small>
                                        </div>

                                        <div class="form-group text-center" id="preview-container" style="display: none;">
                                            <img id="preview-image" src="" alt="Vista previa" class="img-thumbnail" style="max-width: 200px;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar
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

    // Validar DNI (8 dígitos)
    document.getElementById('tipo_documento').addEventListener('change', function() {
        const numDoc = document.getElementById('numero_documento');
        if (this.value === 'DNI') {
            numDoc.maxLength = 8;
            numDoc.pattern = '[0-9]{8}';
        } else if (this.value === 'CE') {
            numDoc.maxLength = 12;
            numDoc.pattern = '[0-9A-Z]{9,12}';
        } else {
            numDoc.maxLength = 20;
            numDoc.pattern = '';
        }
    });
</script>
