<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Cargar proveedores
try {
    $sqlProveedores = "SELECT * FROM tb_proveedores WHERE estado_registro = 'ACTIVO' ORDER BY nombre_proveedor ASC";
    $stmtProveedores = $pdo->prepare($sqlProveedores);
    $stmtProveedores->execute();
    $proveedores = $stmtProveedores->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $proveedores = [];
}

// Cargar productos
try {
    $sqlProductos = "SELECT p.*, c.nombre_categoria 
                     FROM tb_almacen p
                     INNER JOIN tb_categorias c ON p.id_categoria = c.id_categoria
                     WHERE p.estado_registro = 'ACTIVO' 
                     ORDER BY p.nombre ASC";
    $stmtProductos = $pdo->prepare($sqlProductos);
    $stmtProductos->execute();
    $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $productos = [];
}

// Verificar si viene de stock bajo
$id_producto_preseleccionado = $_GET['id_producto'] ?? null;
$cantidad_sugerida = $_GET['cantidad'] ?? null;
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Registrar Nueva Compra</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <form action="../../controllers/compra/crear.php" 
                  method="post" 
                  enctype="multipart/form-data"
                  id="formCrearCompra">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Información de la Compra</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="id_proveedor">
                                                Proveedor <span class="text-danger">*</span>
                                            </label>
                                                <select name="id_proveedor" id="id_proveedor" class="form-control select2" required>
                                                    <option value="">Seleccione un proveedor...</option>

                                                    <?php if (!empty($proveedores) && is_array($proveedores)): ?>
                                                        <?php foreach ($proveedores as $prov): ?>
                                                            <option 
                                                                value="<?= htmlspecialchars($prov['id_proveedor']) ?>"
                                                                data-ruc="<?= htmlspecialchars($prov['ruc_dni'] ?? '') ?>"
                                                                data-telefono="<?= htmlspecialchars($prov['telefono'] ?? '') ?>"
                                                                data-direccion="<?= htmlspecialchars($prov['direccion'] ?? '') ?>"
                                                            >
                                                                <?= htmlspecialchars($prov['nombre_proveedor']) ?>

                                                                <?php if (!empty($prov['empresa'])): ?>
                                                                    - <?= htmlspecialchars($prov['empresa']) ?>
                                                                <?php endif; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>   
                                            <small>
                                                <a href="../proveedores/create.php" target="_blank">+ Nuevo proveedor</a>
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fecha_compra">
                                                Fecha de Compra <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" 
                                                   name="fecha_compra" 
                                                   id="fecha_compra"
                                                   class="form-control"
                                                   value="<?php echo date('Y-m-d'); ?>"
                                                   required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Información del proveedor seleccionado -->
                                <div id="info_proveedor" style="display: none;">
                                    <div class="alert alert-info">
                                        <strong>RUC/DNI:</strong> <span id="prov_ruc">-</span><br>
                                        <strong>Teléfono:</strong> <span id="prov_telefono">-</span><br>
                                        <strong>Dirección:</strong> <span id="prov_direccion">-</span>
                                    </div>
                                </div>

                                <hr>

                                <h5>Productos a Comprar</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="id_producto">
                                                Producto <span class="text-danger">*</span>
                                            </label>
                                            <select name="id_producto" id="id_producto" class="form-control select2" required>
                                                <option value="">Seleccione un producto...</option>
                                                <?php foreach ($productos as $prod): ?>
                                                    <option value="<?php echo $prod['id_producto']; ?>"
                                                            data-codigo="<?php echo $prod['codigo']; ?>"
                                                            data-stock="<?php echo $prod['stock']; ?>"
                                                            data-precio="<?php echo $prod['precio_compra']; ?>"
                                                            <?php echo ($id_producto_preseleccionado == $prod['id_producto']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($prod['nombre']); ?> 
                                                        (<?php echo htmlspecialchars($prod['nombre_categoria']); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Información del Producto</label>
                                            <div class="alert alert-secondary" id="info_producto">
                                                Seleccione un producto para ver su información
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="cantidad">
                                                Cantidad <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" 
                                                   name="cantidad" 
                                                   id="cantidad"
                                                   class="form-control"
                                                   value="<?php echo $cantidad_sugerida ?? 1; ?>"
                                                   required
                                                   min="1"
                                                   step="1">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="precio_compra">
                                                Precio Unitario <span class="text-danger">*</span>
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

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Total</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">S/</span>
                                                </div>
                                                <input type="text" 
                                                       id="total_display"
                                                       class="form-control"
                                                       readonly
                                                       value="0.00">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-secondary">
                            <div class="card-header">
                                <h3 class="card-title">Información Adicional</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="comprobante">Número de Comprobante</label>
                                    <input type="text" 
                                           name="comprobante" 
                                           id="comprobante"
                                           class="form-control"
                                           placeholder="001-00001234">
                                </div>

                                <div class="form-group">
                                    <label for="tipo_comprobante">Tipo de Comprobante</label>
                                    <select name="tipo_comprobante" id="tipo_comprobante" class="form-control">
                                        <option value="FACTURA">Factura</option>
                                        <option value="BOLETA">Boleta</option>
                                        <option value="NOTA_VENTA">Nota de Venta</option>
                                        <option value="TICKET">Ticket</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="forma_pago">Forma de Pago</label>
                                    <select name="forma_pago" id="forma_pago" class="form-control">
                                        <option value="EFECTIVO">Efectivo</option>
                                        <option value="TRANSFERENCIA">Transferencia</option>
                                        <option value="CHEQUE">Cheque</option>
                                        <option value="CREDITO">Crédito</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="imagen_comprobante">Imagen del Comprobante</label>
                                    <div class="custom-file">
                                        <input type="file" 
                                               name="imagen_comprobante" 
                                               id="imagen_comprobante"
                                               class="custom-file-input"
                                               accept="image/*">
                                        <label class="custom-file-label" for="imagen_comprobante">Seleccionar imagen...</label>
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

                                <div class="form-group">
                                    <label for="observaciones">Observaciones</label>
                                    <textarea name="observaciones" 
                                              id="observaciones"
                                              class="form-control" 
                                              rows="3"
                                              placeholder="Notas adicionales sobre la compra..."></textarea>
                                </div>

                                <hr>

                                <div class="alert alert-warning">
                                    <strong>Nota:</strong> Esta compra incrementará automáticamente el stock del producto seleccionado.
                                </div>

                                <a href="index.php" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Registrar Compra
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
    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Mostrar información del proveedor
    document.getElementById('id_proveedor').addEventListener('change', function() {
        var option = this.options[this.selectedIndex];
        if (option.value) {
            document.getElementById('info_proveedor').style.display = 'block';
            document.getElementById('prov_ruc').textContent = option.getAttribute('data-ruc');
            document.getElementById('prov_telefono').textContent = option.getAttribute('data-telefono');
            document.getElementById('prov_direccion').textContent = option.getAttribute('data-direccion') || 'No registrada';
        } else {
            document.getElementById('info_proveedor').style.display = 'none';
        }
    });

    // Mostrar información del producto
    document.getElementById('id_producto').addEventListener('change', function() {
        var option = this.options[this.selectedIndex];
        if (option.value) {
            var codigo = option.getAttribute('data-codigo');
            var stock = option.getAttribute('data-stock');
            var unidad = option.getAttribute('data-unidad');
            var precio = option.getAttribute('data-precio');
            
            document.getElementById('info_producto').innerHTML = 
                '<strong>Código:</strong> ' + codigo + '<br>' +
                '<strong>Stock Actual:</strong> ' + stock + ' ' + unidad + '<br>' +
                '<strong>Precio Compra Anterior:</strong> S/ ' + parseFloat(precio).toFixed(2);
            
            // Prellenar precio
            document.getElementById('precio_compra').value = precio;
            calcularTotal();
        } else {
            document.getElementById('info_producto').innerHTML = 'Seleccione un producto para ver su información';
        }
    });

    // Calcular total
    function calcularTotal() {
        var cantidad = parseFloat(document.getElementById('cantidad').value) || 0;
        var precio = parseFloat(document.getElementById('precio_compra').value) || 0;
        var total = cantidad * precio;
        document.getElementById('total_display').value = total.toFixed(2);
    }

    document.getElementById('cantidad').addEventListener('input', calcularTotal);
    document.getElementById('precio_compra').addEventListener('input', calcularTotal);

    // Preview de imagen
    document.getElementById('imagen_comprobante').addEventListener('change', function(e) {
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

    // Validación
    document.getElementById('formCrearCompra').addEventListener('submit', function(e) {
        var cantidad = parseFloat(document.getElementById('cantidad').value);
        var precio = parseFloat(document.getElementById('precio_compra').value);
        
        if (cantidad <= 0 || precio <= 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La cantidad y el precio deben ser mayores a cero'
            });
            return false;
        }
    });

    // Si viene preseleccionado, disparar evento
    <?php if ($id_producto_preseleccionado): ?>
    document.getElementById('id_producto').dispatchEvent(new Event('change'));
    <?php endif; ?>
</script>
