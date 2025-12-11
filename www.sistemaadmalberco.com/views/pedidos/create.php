<?php
include('../../services/database/config.php');
include('../../contans/layout/sesion.php');
include('../../contans/layout/parte1.php');

// Activar modo de errores PDO
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Cargar clientes para el selector
try {
    $sqlClientes = "SELECT id_cliente, codigo_cliente, nombre, apellidos, telefono 
                    FROM tb_clientes 
                    WHERE estado_registro = 'ACTIVO' 
                    ORDER BY nombre ASC";
    $stmtClientes = $pdo->prepare($sqlClientes);
    $stmtClientes->execute();
    $clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al cargar clientes: " . $e->getMessage() . "</div>";
    $clientes = [];
}

// Cargar mesas disponibles
try {
    $sqlMesas = "SELECT id_mesa, numero_mesa, capacidad 
                FROM tb_mesas 
                WHERE estado = 'disponible' AND estado_registro = 'ACTIVO' 
                ORDER BY numero_mesa ASC";
    $stmtMesas = $pdo->prepare($sqlMesas);
    $stmtMesas->execute();
    $mesas = $stmtMesas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al cargar mesas: " . $e->getMessage() . "</div>";
    $mesas = [];
}

// Definir colores por categoría
$coloresCategorias = [
    'Pollos' => '#dc3545',
    'Chifa' => '#ffc107',
    'Sopas y Caldos' => '#17a2b8',
    'Bebidas' => '#28a745',
    'Acompañamientos' => '#6c757d',
    'Promociones' => '#e83e8c',
    'Postres' => '#fd7e14'
];

// Cargar productos disponibles con categorías
try {
    $sqlProductos = "SELECT p.*, c.nombre_categoria
                    FROM tb_almacen p
                    INNER JOIN tb_categorias c ON p.id_categoria = c.id_categoria
                    WHERE p.disponible_venta = 1 
                    AND p.stock > 0 
                    AND p.estado_registro = 'ACTIVO'
                    AND c.estado_registro = 'ACTIVO'
                    ORDER BY c.orden ASC, p.nombre ASC";
    $stmtProductos = $pdo->prepare($sqlProductos);
    $stmtProductos->execute();
    $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar por categoría
    $productosPorCategoria = [];
    foreach ($productos as $producto) {
        $categoria = $producto['nombre_categoria'];
        if (!isset($productosPorCategoria[$categoria])) {
            $productosPorCategoria[$categoria] = [
                'color' => $coloresCategorias[$categoria] ?? '#007bff',
                'productos' => []
            ];
        }
        // Agregar el color al producto para usarlo en el HTML
        $producto['categoria_color'] = $coloresCategorias[$categoria] ?? '#007bff';
        $productosPorCategoria[$categoria]['productos'][] = $producto;
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al cargar productos: " . $e->getMessage() . "</div>";
    $productosPorCategoria = [];
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Nuevo Pedido</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Pedidos</a></li>
                        <li class="breadcrumb-item active">Nuevo</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <form action="../../controllers/pedidos/crear.php" method="post" id="formCrearPedido">
                <div class="row">
                    <!-- Información del Pedido -->
                    <div class="col-md-8">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Productos del Pedido</h3>
                            </div>
                            <div class="card-body">
                                <!-- Selector de productos por categoría -->
                                <div class="row">
                                    <?php if (empty($productosPorCategoria)): ?>
                                        <div class="col-12">
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                No hay productos disponibles en este momento.
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($productosPorCategoria as $categoria => $data): ?>
                                            <div class="col-md-12 mb-3">
                                                <h5 style="border-bottom: 2px solid <?php echo htmlspecialchars($data['color']); ?>; padding-bottom: 5px;">
                                                    <i class="fas fa-utensils"></i> <?php echo htmlspecialchars($categoria); ?>
                                                </h5>
                                                <div class="row">
                                                    <?php foreach ($data['productos'] as $producto): ?>
                                                        <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-2">
                                                            <div class="card producto-card"
                                                                style="cursor: pointer; border-top: 3px solid <?php echo htmlspecialchars($producto['categoria_color']); ?>; height: 100%;"
                                                                data-producto='<?php echo json_encode([
                                                                    'id' => $producto['id_producto'],
                                                                    'nombre' => $producto['nombre'],
                                                                    'precio' => $producto['precio_venta']
                                                                ], JSON_HEX_APOS | JSON_HEX_QUOT); ?>'
                                                                onclick="agregarProductoClick(this)">
                                                                <div class="card-body text-center p-2">
                                                                    <?php if (!empty($producto['imagen'])): ?>
                                                                        <img src="<?php echo URL_BASE . '/' . htmlspecialchars($producto['imagen']); ?>"
                                                                            class="img-fluid mb-1"
                                                                            style="max-height: 60px; object-fit: contain;"
                                                                            alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                                                    <?php else: ?>
                                                                        <i class="fas fa-utensils fa-2x mb-1"
                                                                            style="color: <?php echo htmlspecialchars($producto['categoria_color']); ?>"></i>
                                                                    <?php endif; ?>
                                                                    <h6 class="mb-1" style="font-size: 0.8rem; line-height: 1.2;">
                                                                        <?php echo htmlspecialchars($producto['nombre']); ?>
                                                                    </h6>
                                                                    <small class="text-muted d-block">Stock: <?php echo $producto['stock']; ?></small>
                                                                    <strong class="text-success">S/ <?php echo number_format($producto['precio_venta'], 2); ?></strong>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <!-- Tabla de productos seleccionados -->
                                <hr>
                                <h5><i class="fas fa-list"></i> Productos Seleccionados</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="tablaProductos">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Producto</th>
                                                <th width="120">Cantidad</th>
                                                <th width="100">Precio</th>
                                                <th width="100">Subtotal</th>
                                                <th width="70" class="text-center">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="productosSeleccionados">
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">
                                                    <i class="fas fa-info-circle"></i> No hay productos seleccionados
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datos del Pedido -->
                    <div class="col-md-4">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-file-invoice"></i> Información del Pedido</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="tipo_pedido">
                                        <i class="fas fa-tag"></i> Tipo de Pedido
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="tipo_pedido" id="tipo_pedido" class="form-control" required>
                                        <option value="">Seleccione...</option>
                                        <option value="mesa">🪑 Mesa</option>
                                        <option value="delivery">🏍️ Delivery</option>
                                        <option value="para_llevar">📦 Para Llevar</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="id_cliente">
                                        <i class="fas fa-user"></i> Cliente
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="id_cliente" id="id_cliente" class="form-control select2" required>
                                        <option value="">Seleccione un cliente...</option>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <option value="<?php echo $cliente['id_cliente']; ?>">
                                                <?php echo htmlspecialchars($cliente['nombre'] . ' ' . ($cliente['apellidos'] ?? '')); ?>
                                                <?php if (!empty($cliente['telefono'])): ?>
                                                    - <?php echo htmlspecialchars($cliente['telefono']); ?>
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">
                                        <a href="../clientes/create.php" target="_blank">
                                            <i class="fas fa-plus-circle"></i> Nuevo cliente
                                        </a>
                                    </small>
                                </div>

                                <!-- Campo Mesa (solo si tipo = mesa) -->
                                <div class="form-group" id="campo_mesa" style="display: none;">
                                    <label for="id_mesa">
                                        <i class="fas fa-chair"></i> Mesa
                                    </label>
                                    <select name="id_mesa" id="id_mesa" class="form-control">
                                        <option value="">Seleccione una mesa...</option>
                                        <?php foreach ($mesas as $mesa): ?>
                                            <option value="<?php echo $mesa['id_mesa']; ?>">
                                                Mesa <?php echo htmlspecialchars($mesa['numero_mesa']); ?>
                                                (<?php echo $mesa['capacidad']; ?> personas)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Campos Delivery (solo si tipo = delivery) -->
                                <div id="campos_delivery" style="display: none;">
                                    <div class="form-group">
                                        <label for="direccion_entrega">
                                            <i class="fas fa-map-marker-alt"></i> Dirección de Entrega
                                        </label>
                                        <textarea name="direccion_entrega"
                                            id="direccion_entrega"
                                            class="form-control"
                                            rows="2"
                                            placeholder="Ingrese la dirección completa de entrega"></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="latitud_entrega">
                                                    <i class="fas fa-compass"></i> Latitud
                                                </label>
                                                <input type="text"
                                                    name="latitud_entrega"
                                                    id="latitud_entrega"
                                                    class="form-control"
                                                    placeholder="-12.046374">
                                                <small class="form-text text-muted">Opcional</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="longitud_entrega">
                                                    <i class="fas fa-compass"></i> Longitud
                                                </label>
                                                <input type="text"
                                                    name="longitud_entrega"
                                                    id="longitud_entrega"
                                                    class="form-control"
                                                    placeholder="-77.042793">
                                                <small class="form-text text-muted">Opcional</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="observaciones">
                                        <i class="fas fa-comment-dots"></i> Observaciones
                                    </label>
                                    <textarea name="observaciones"
                                        id="observaciones"
                                        class="form-control"
                                        rows="2"
                                        placeholder="Notas especiales del pedido (ej: sin cebolla, picante aparte)"></textarea>
                                </div>

                                <!-- Resumen de totales -->
                                <hr>
                                <div class="card bg-light">
                                    <div class="card-body p-2">
                                        <table class="table table-sm mb-0">
                                            <tr>
                                                <td><strong>Subtotal:</strong></td>
                                                <td class="text-right" id="subtotal">S/ 0.00</td>
                                            </tr>
                                            <tr>
                                                <td><strong>IGV (18%):</strong></td>
                                                <td class="text-right" id="igv">S/ 0.00</td>
                                            </tr>
                                            <tr class="bg-success text-white">
                                                <td><strong>TOTAL:</strong></td>
                                                <td class="text-right"><strong id="total">S/ 0.00</strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <input type="hidden" name="detalles" id="detalles">

                                <hr>
                                <div class="row">
                                    <div class="col-6">
                                        <a href="index.php" class="btn btn-secondary btn-block">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-save"></i> Registrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('../../contans/layout/mensajes.php'); ?>
<?php include('../../contans/layout/parte2.php'); ?>

<script>
    // Array para guardar productos seleccionados
    var productosSeleccionados = [];

    // Inicializar Select2
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
            language: 'es'
        });
    });

    // Cambiar campos según tipo de pedido
    document.getElementById('tipo_pedido').addEventListener('change', function() {
        var tipo = this.value;

        if (tipo === 'mesa') {
            document.getElementById('campo_mesa').style.display = 'block';
            document.getElementById('campos_delivery').style.display = 'none';
            document.getElementById('id_mesa').required = true;
            document.getElementById('direccion_entrega').required = false;
        } else if (tipo === 'delivery') {
            document.getElementById('campo_mesa').style.display = 'none';
            document.getElementById('campos_delivery').style.display = 'block';
            document.getElementById('id_mesa').required = false;
            document.getElementById('direccion_entrega').required = true;
        } else if (tipo === 'para_llevar') {
            document.getElementById('campo_mesa').style.display = 'none';
            document.getElementById('campos_delivery').style.display = 'none';
            document.getElementById('id_mesa').required = false;
            document.getElementById('direccion_entrega').required = false;
        }
    });

    // NUEVA FUNCIÓN: Agregar producto desde data-attribute
    function agregarProductoClick(elemento) {
        try {
            var datosProducto = JSON.parse(elemento.getAttribute('data-producto'));
            agregarProducto(datosProducto.id, datosProducto.nombre, datosProducto.precio);
        } catch (e) {
            console.error('Error al parsear datos del producto:', e);
            alert('Error al agregar el producto. Por favor, intente nuevamente.');
        }
    }

    // Agregar producto
    function agregarProducto(id, nombre, precio) {
        // Verificar si ya existe
        var existe = productosSeleccionados.find(p => p.id_producto === id);

        if (existe) {
            existe.cantidad++;
        } else {
            productosSeleccionados.push({
                id_producto: id,
                nombre: nombre,
                precio: parseFloat(precio),
                cantidad: 1
            });
        }

        actualizarTabla();
    }

    // Actualizar tabla de productos
    function actualizarTabla() {
        var tbody = document.getElementById('productosSeleccionados');
        tbody.innerHTML = '';

        if (productosSeleccionados.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted"><i class="fas fa-info-circle"></i> No hay productos seleccionados</td></tr>';
            actualizarTotales();
            return;
        }

        productosSeleccionados.forEach(function(producto, index) {
            var subtotal = producto.precio * producto.cantidad;

            var tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${producto.nombre}</td>
                <td>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <button type="button" class="btn btn-outline-secondary" onclick="cambiarCantidad(${index}, ${producto.cantidad - 1})">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                        <input type="number" 
                            class="form-control text-center" 
                            value="${producto.cantidad}" 
                            min="1" 
                            onchange="cambiarCantidad(${index}, this.value)">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" onclick="cambiarCantidad(${index}, ${producto.cantidad + 1})">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </td>
                <td>S/ ${producto.precio.toFixed(2)}</td>
                <td><strong>S/ ${subtotal.toFixed(2)}</strong></td>
                <td class="text-center">
                    <button type="button" 
                            class="btn btn-danger btn-sm" 
                            onclick="eliminarProducto(${index})"
                            title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        actualizarTotales();
    }

    // Cambiar cantidad
    function cambiarCantidad(index, cantidad) {
        cantidad = parseInt(cantidad);
        if (cantidad < 1) {
            eliminarProducto(index);
            return;
        }
        productosSeleccionados[index].cantidad = cantidad;
        actualizarTabla();
    }

    // Eliminar producto
    function eliminarProducto(index) {
        Swal.fire({
            title: '¿Eliminar producto?',
            text: '¿Está seguro de eliminar este producto del pedido?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                productosSeleccionados.splice(index, 1);
                actualizarTabla();
            }
        });
    }

    // Actualizar totales
    function actualizarTotales() {
        var subtotal = 0;

        productosSeleccionados.forEach(function(producto) {
            subtotal += producto.precio * producto.cantidad;
        });

        var igv = subtotal * 0.18;
        var total = subtotal + igv;

        document.getElementById('subtotal').textContent = 'S/ ' + subtotal.toFixed(2);
        document.getElementById('igv').textContent = 'S/ ' + igv.toFixed(2);
        document.getElementById('total').textContent = 'S/ ' + total.toFixed(2);
    }

    // Validar antes de enviar
    document.getElementById('formCrearPedido').addEventListener('submit', function(e) {
        e.preventDefault();

        if (productosSeleccionados.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe agregar al menos un producto al pedido'
            });
            return false;
        }

        // Validar tipo de pedido
        var tipoPedido = document.getElementById('tipo_pedido').value;
        if (!tipoPedido) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe seleccionar el tipo de pedido'
            });
            return false;
        }

        // Validar cliente
        var idCliente = document.getElementById('id_cliente').value;
        if (!idCliente) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe seleccionar un cliente'
            });
            return false;
        }

        // Validar mesa si es tipo mesa
        if (tipoPedido === 'mesa') {
            var idMesa = document.getElementById('id_mesa').value;
            if (!idMesa) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debe seleccionar una mesa'
                });
                return false;
            }
        }

        // Validar dirección si es delivery
        if (tipoPedido === 'delivery') {
            var direccion = document.getElementById('direccion_entrega').value.trim();
            if (!direccion) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debe ingresar la dirección de entrega'
                });
                return false;
            }
        }

        // Confirmar envío
        Swal.fire({
            title: '¿Registrar pedido?',
            html: `Se registrará el pedido con un total de <strong>S/ ${(productosSeleccionados.reduce((sum, p) => sum + (p.precio * p.cantidad), 0) * 1.18).toFixed(2)}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, registrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Pasar productos como JSON
                document.getElementById('detalles').value = JSON.stringify(productosSeleccionados);

                // Enviar formulario
                e.target.submit();
            }
        });
    });
</script>

<style>
    .producto-card {
        transition: all 0.2s ease-in-out;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .producto-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .producto-card:active {
        transform: translateY(-2px);
    }

    #tablaProductos tbody tr:hover {
        background-color: #f8f9fa;
    }

    .card-body {
        max-height: 70vh;
        overflow-y: auto;
    }

    /* Scrollbar personalizado */
    .card-body::-webkit-scrollbar {
        width: 8px;
    }

    .card-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .card-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .card-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
