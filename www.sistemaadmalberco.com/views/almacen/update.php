<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Obtener ID del producto
$id_producto_get = $_GET['id'] ?? 0;

if ($id_producto_get <= 0) {
    $_SESSION['error'] = 'ID de producto inválido';
    header('Location: index.php');
    exit;
}

// Obtener datos del producto
try {
    $sql = "SELECT * FROM tb_almacen WHERE id_producto = ? AND estado_registro = 'ACTIVO'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_producto_get]);
    $producto_dato = $stmt->fetch();
    
    if (!$producto_dato) {
        $_SESSION['error'] = 'Producto no encontrado';
        header('Location: index.php');
        exit;
    }
    
    // Cargar categorías
    $sqlCategorias = "SELECT * FROM tb_categorias WHERE estado_registro = 'ACTIVO' ORDER BY nombre_categoria ASC";
    $stmtCategorias = $pdo->prepare($sqlCategorias);
    $stmtCategorias->execute();
    $categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al obtener producto: " . $e->getMessage());
    $_SESSION['error'] = 'Error al cargar datos';
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
                    <h1 class="m-0">Editar Producto</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <form action="../../controllers/productos/actualizar.php" 
                  method="post" 
                  enctype="multipart/form-data"
                  id="formActualizarProducto">
                
                <input type="hidden" name="id_producto" value="<?php echo $id_producto_get; ?>">
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Información del Producto</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="codigo">Código</label>
                                            <input type="text" 
                                                   id="codigo"
                                                   class="form-control"
                                                   value="<?php echo htmlspecialchars($producto_dato['codigo']); ?>"
                                                   disabled>
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
                                                   value="<?php echo htmlspecialchars($producto_dato['nombre']); ?>"
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
                                              rows="3"><?php echo htmlspecialchars($producto_dato['descripcion'] ?? ''); ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="id_categoria">
                                                Categoría <span class="text-danger">*</span>
                                            </label>
                                            <select name="id_categoria" id="id_categoria" class="form-control" required>
                                                <?php foreach ($categorias as $cat): ?>
                                                    <option value="<?php echo $cat['id_categoria']; ?>"
                                                            <?php echo $cat['id_categoria'] == $producto_dato['id_categoria'] ? 'selected' : ''; ?>>
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
                                                Stock Actual <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" 
                                                   name="stock" 
                                                   id="stock"
                                                   class="form-control"
                                                   value="<?php echo $producto_dato['stock']; ?>"
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
                                                   value="<?php echo $producto_dato['stock_minimo']; ?>"
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
                                                   value="<?php echo $producto_dato['stock_maximo']; ?>"
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
                                                       value="<?php echo $producto_dato['precio_compra']; ?>"
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
                                                       value="<?php echo $producto_dato['precio_venta']; ?>"
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
                                    <label>Imagen Actual:</label><br>
                                    <?php if ($producto_dato['imagen']): ?>
                                        <img src="<?php echo URL_BASE . '/' . $producto_dato['imagen']; ?>" 
                                             class="img-thumbnail mb-2" 
                                             style="max-width: 100%;">
                                    <?php else: ?>
                                        <p class="text-muted">Sin imagen</p>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="imagen">Nueva Imagen (opcional)</label>
                                    <div class="custom-file">
                                        <input type="file" 
                                               name="imagen" 
                                               id="imagen"
                                               class="custom-file-input"
                                               accept="image/*">
                                        <label class="custom-file-label" for="imagen">Seleccionar nueva imagen...</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Dejar vacío para mantener la imagen actual
                                    </small>
                                </div>

                                <!-- Preview de nueva imagen -->
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
                                               <?php echo $producto_dato['disponible_venta'] ? 'checked' : ''; ?>>
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
                                               <?php echo $producto_dato['requiere_preparacion'] ? 'checked' : ''; ?>>
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
                                           value="<?php echo $producto_dato['tiempo_preparacion']; ?>"
                                           min="1">
                                </div>

                                <hr>

                                <a href="index.php" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-sync-alt"></i> Actualizar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

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

    // Inicializar visibilidad del campo tiempo
    document.getElementById('campo_tiempo').style.display = 
        document.getElementById('requiere_preparacion').checked ? 'block' : 'none';

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
    
    // Calcular margen inicial
    calcularMargen();

    // Validación
    document.getElementById('formActualizarProducto').addEventListener('submit', function(e) {
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
