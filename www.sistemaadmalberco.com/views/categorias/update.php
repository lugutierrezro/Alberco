<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Obtener ID de la categoría a editar
$id_categoria_get = $_GET['id'] ?? 0;

if ($id_categoria_get <= 0) {
    $_SESSION['error'] = 'ID de categoría inválido';
    header('Location: index.php');
    exit;
}

// Obtener datos de la categoría
try {
    $sql = "SELECT * FROM tb_categorias WHERE id_categoria = ? AND estado_registro = 'ACTIVO'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_categoria_get]);
    $categoria_dato = $stmt->fetch();
    
    if (!$categoria_dato) {
        $_SESSION['error'] = 'Categoría no encontrada';
        header('Location: index.php');
        exit;
    }
    
    $nombre_categoria = $categoria_dato['nombre_categoria'];
    $descripcion = $categoria_dato['descripcion'] ?? '';
    $orden = $categoria_dato['orden'] ?? 0;
    $color = $categoria_dato['color'] ?? '#007bff';
    $icono = $categoria_dato['icono'] ?? 'fas fa-tag';
    $imagen_actual = $categoria_dato['imagen'] ?? '';
    
} catch (PDOException $e) {
    error_log("Error al obtener categoría: " . $e->getMessage());
    $_SESSION['error'] = 'Error al cargar datos de la categoría';
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
                    <h1 class="m-0">Editar Categoría</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Actualice los datos de la categoría</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="../../controllers/categorias/actualizar.php" 
                                  method="post" 
                                  enctype="multipart/form-data"
                                  id="formActualizarCategoria">
                                
                                <input type="hidden" name="id_categoria" value="<?php echo $id_categoria_get; ?>">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nombre_categoria">
                                                Nombre de la Categoría <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   name="nombre_categoria" 
                                                   id="nombre_categoria"
                                                   class="form-control"
                                                   value="<?php echo htmlspecialchars($nombre_categoria); ?>"
                                                   required
                                                   maxlength="100">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="orden">Orden de Visualización</label>
                                            <input type="number" 
                                                   name="orden" 
                                                   id="orden"
                                                   class="form-control"
                                                   value="<?php echo $orden; ?>"
                                                   min="0">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="color">Color</label>
                                            <input type="color" 
                                                   name="color" 
                                                   id="color"
                                                   class="form-control"
                                                   value="<?php echo $color; ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <textarea name="descripcion" 
                                              id="descripcion"
                                              class="form-control" 
                                              rows="3"><?php echo htmlspecialchars($descripcion); ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="icono">Icono (FontAwesome)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i id="preview-icon" class="<?php echo $icono; ?>"></i>
                                                    </span>
                                                </div>
                                                <input type="text" 
                                                       name="icono" 
                                                       id="icono"
                                                       class="form-control"
                                                       value="<?php echo htmlspecialchars($icono); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="imagen">Nueva Imagen (opcional)</label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" 
                                                           name="imagen" 
                                                           id="imagen"
                                                           class="custom-file-input"
                                                           accept="image/*">
                                                    <label class="custom-file-label" for="imagen">Seleccionar nueva imagen...</label>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">
                                                Dejar vacío para mantener la imagen actual
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Imagen actual -->
                                <?php if ($imagen_actual): ?>
                                <div class="form-group">
                                    <label>Imagen Actual:</label><br>
                                    <img src="<?php echo URL_BASE . '/' . $imagen_actual; ?>" 
                                         alt="Imagen actual" 
                                         class="img-thumbnail"
                                         style="max-width: 200px;">
                                </div>
                                <?php endif; ?>

                                <!-- Preview de nueva imagen -->
                                <div class="form-group">
                                    <img id="preview-imagen" 
                                         src="" 
                                         alt="Preview" 
                                         style="max-width: 200px; display: none;"
                                         class="img-thumbnail">
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
    // Preview de icono
    document.getElementById('icono').addEventListener('input', function() {
        var iconClass = this.value.trim();
        var previewIcon = document.getElementById('preview-icon');
        previewIcon.className = iconClass || 'fas fa-tag';
    });

    // Preview de imagen
    document.getElementById('imagen').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var label = this.nextElementSibling;
            label.textContent = file.name;
            
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById('preview-imagen');
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });

    // Validación del formulario
    document.getElementById('formActualizarCategoria').addEventListener('submit', function(e) {
        var nombre = document.getElementById('nombre_categoria').value.trim();
        
        if (nombre === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El nombre de la categoría es obligatorio'
            });
            return false;
        }
    });
</script>
