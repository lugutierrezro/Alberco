<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Registro de Nueva Categoría</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Complete los datos de la categoría</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="../../controllers/categorias/crear.php" 
                                  method="post" 
                                  enctype="multipart/form-data"
                                  id="formCrearCategoria">
                                
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
                                                   placeholder="Ejemplo: Bebidas, Postres, etc."
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
                                                   value="0"
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
                                                   value="#007bff">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <textarea name="descripcion" 
                                              id="descripcion"
                                              class="form-control" 
                                              rows="3"
                                              placeholder="Descripción de la categoría (opcional)"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="icono">Icono (FontAwesome)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i id="preview-icon" class="fas fa-tag"></i>
                                                    </span>
                                                </div>
                                                <input type="text" 
                                                       name="icono" 
                                                       id="icono"
                                                       class="form-control"
                                                       value="fas fa-tag"
                                                       placeholder="fas fa-tag">
                                            </div>
                                            <small class="form-text text-muted">
                                                Ejemplo: fas fa-coffee, fas fa-pizza-slice, fas fa-ice-cream
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="imagen">Imagen (opcional)</label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" 
                                                           name="imagen" 
                                                           id="imagen"
                                                           class="custom-file-input"
                                                           accept="image/*">
                                                    <label class="custom-file-label" for="imagen">Seleccionar imagen...</label>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">
                                                Formatos: JPG, PNG, WEBP. Máximo 2MB
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Preview de imagen -->
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
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar Categoría
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
            // Actualizar label
            var label = this.nextElementSibling;
            label.textContent = file.name;
            
            // Mostrar preview
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
    document.getElementById('formCrearCategoria').addEventListener('submit', function(e) {
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
