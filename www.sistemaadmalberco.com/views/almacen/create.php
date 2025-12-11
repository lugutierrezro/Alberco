<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Cargar categorías
try {
    $sqlCategorias = "SELECT * FROM tb_categorias WHERE estado_registro = 'ACTIVO' ORDER BY nombre_categoria ASC";
    $stmtCategorias = $pdo->prepare($sqlCategorias);
    $stmtCategorias->execute();
    $categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categorias = [];
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Registrar Nuevo Producto</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <form action="../../controllers/productos/crear.php" 
                  method="post" 
                  enctype="multipart/form-data"
                  id="formCrearProducto">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Información del Producto</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="codigo">
                                                Código <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   name="codigo" 
                                                   id="codigo"
                                                   class="form-control"
                                                   placeholder="PROD-001"
                                                   required
                                                   maxlength="50">
                                        </div>
                                    </div>

                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="nombre">
                                                Nombre del Producto <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   name="nombre" 
                                                   id="nombre"
                                                   class="form-control"
                                                   placeholder="Nombre descriptivo del producto"
                                                   required
                                                   maxlength="200">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <textarea name="descripcion" 
                                              id="descripcion"
                                              class="form-control" 
                                              rows="3"
                                              placeholder="Descripción detallada del producto"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="id_categoria">
                                                Categoría <span class="text-danger">*</span>
                                            </label>
                                            <select name="id_categoria" id="id_categoria" class="form-control" required>
                                                <option value="">Seleccione una categoría...</option>
                                                <?php foreach ($categorias as $cat): ?>
                                                    <option value="<?php echo $cat['id_categoria']; ?>">
                                                        <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>


                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="stock">
                                                Stock Inicial <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" 
                                                   name="stock" 
                                                   id="stock"
                                                   class="form-control"
                                                   value="0"
                                                   required
                                                   min="0">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="stock_minimo">Stock Mínimo</label>
                                            <input type="number" 
                                                   name="stock_minimo" 
                                                   id="stock_minimo"
                                                   class="form-control"
                                                   value="5"
                                                   min="0">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="stock_maximo">Stock Máximo</label>
                                            <input type="number" 
                                                   name="stock_maximo" 
                                                   id="stock_maximo"
                                                   class="form-control"
                                                   value="100"
                                                   min="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="precio_compra">
                                                Precio de Compra <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">S/</span>
                                                </div>
                                                <input type="number" 
                                                       name="precio_compra" 
                                                       id="precio_compra"
                                                       class="form-control"
                                                       step="0.01"
                                                       required
                                                       min="0">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="precio_venta">
                                                Precio de Venta <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">S/</span>
                                                </div>
                                                <input type="number" 
                                                       name="precio_venta" 
                                                       id="precio_venta"
                                                       class="form-control"
                                                       step="0.01"
                                                       required
                                                       min="0">
                                            </div>
                                            <small class="text-muted">Margen: <span id="margen">0%</span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-secondary">
                            <div class="card-header">
                                <h3 class="card-title">Configuración Adicional</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="fecha_ingreso">Fecha de Ingreso</label>
                                    <input type="date" 
                                           name="fecha_ingreso" 
                                           id="fecha_ingreso"
                                           class="form-control"
                                           value="<?php echo date('Y-m-d'); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="imagen">Imagen del Producto</label>
                                    <div class="custom-file">
                                        <input type="file" 
                                               name="imagen" 
                                               id="imagen"
                                               class="custom-file-input"
                                               accept="image/*">
                                        <label class="custom-file-label" for="imagen">Seleccionar imagen...</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Máximo 3MB. JPG, PNG o WEBP
                                    </small>
                                </div>

                                <!-- Preview de imagen -->
                                <div class="form-group">
                                    <img id="preview-imagen" 
                                         src="" 
                                         alt="Preview" 
                                         style="max-width: 100%; display: none;"
                                         class="img-thumbnail">
                                </div>

                                <hr>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" 
                                               name="disponible_venta" 
                                               class="custom-control-input" 
                                               id="disponible_venta"
                                               checked>
                                        <label class="custom-control-label" for="disponible_venta">
                                            Disponible para venta
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" 
                                               name="requiere_preparacion" 
                                               class="custom-control-input" 
                                               id="requiere_preparacion"
                                               checked>
                                        <label class="custom-control-label" for="requiere_preparacion">
                                            Requiere preparación
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group" id="campo_tiempo">
                                    <label for="tiempo_preparacion">Tiempo de Preparación (min)</label>
                                    <input type="number" 
                                           name="tiempo_preparacion" 
                                           id="tiempo_preparacion"
                                           class="form-control"
                                           value="15"
                                           min="1">
                                </div>

                                <hr>

                                <a href="index.php" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Guardar Producto
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<?php include ('../../contans/layout/parte2.php'); ?>
<?php include ('../../contans/layout/mensajes.php'); ?>

<script>
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

    // Mostrar/ocultar tiempo de preparación
    document.getElementById('requiere_preparacion').addEventListener('change', function() {
        var campo = document.getElementById('campo_tiempo');
        campo.style.display = this.checked ? 'block' : 'none';
    });

    // Calcular margen
    function calcularMargen() {
        var compra = parseFloat(document.getElementById('precio_compra').value) || 0;
        var venta = parseFloat(document.getElementById('precio_venta').value) || 0;
        
        if (compra > 0) {
            var margen = ((venta - compra) / compra * 100).toFixed(2);
            document.getElementById('margen').textContent = margen + '%';
        }
    }

    document.getElementById('precio_compra').addEventListener('input', calcularMargen);
    document.getElementById('precio_venta').addEventListener('input', calcularMargen);

    // Validación
    document.getElementById('formCrearProducto').addEventListener('submit', function(e) {
        var precioCompra = parseFloat(document.getElementById('precio_compra').value);
        var precioVenta = parseFloat(document.getElementById('precio_venta').value);
        
        if (precioVenta <= precioCompra) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El precio de venta debe ser mayor al precio de compra'
            });
            return false;
        }
    });
</script>
