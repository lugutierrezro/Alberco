<?php
require_once __DIR__ . '/../app/init.php';

// Get promotional products using helper function
// We ask for a generous limit to show all active promotions
$promociones_datos = getPromociones(50);

$pageTitle = 'Promociones - Pollería Chifa Alberco';
include '../includes/header.php';
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promociones - Pollería Chifa Alberco</title>
</head>

<body>
    <div class="container-fluid">
        <!-- Contenido Principal -->
        <div class="container py-4">
            <!-- Título -->
            <div class="text-center mb-5">
                <i class="fas fa-fire fa-3x text-danger mb-3 animate__animated animate__pulse animate__infinite"></i>
                <h1 class="display-5 fw-bold text-dark">Promociones de la Semana</h1>
                <p class="text-muted lead">¡Aprovecha nuestras ofertas exclusivas y combos deliciosos!</p>
            </div>

            <!-- Barra de búsqueda -->
            <div class="row mb-5">
                <div class="col-md-8 mx-auto">
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Buscar en promociones...">
                    </div>
                </div>
            </div>

            <!-- Grid de Productos -->
            <div class="row g-4" id="productosGrid">
                <?php if (!empty($promociones_datos)): ?>
                    <?php foreach ($promociones_datos as $prod): ?>
                        <?php
                        // Use helper function for image path
                        $rutaImagen = getImagePath($prod['imagen'], true);
                        
                        // Calcular descuento (simulado si no existe campo descuento)
                        // Si hubiera precio regular, lo mostraríamos tachado
                        ?>

                        <div class="col-md-4 col-lg-3 product-item">
                            <div class="card product-card h-100 shadow-sm border-0 position-relative">
                                <!-- Badge de Oferta -->
                                <div class="position-absolute top-0 end-0 m-3 z-3">
                                    <span class="badge bg-danger rounded-pill px-3 py-2 shadow-sm">
                                        <i class="fas fa-tag"></i> OFERTA
                                    </span>
                                </div>

                                <a href="detalle_producto.php?id=<?= $prod['id_producto'] ?>" class="overflow-hidden bg-light rounded-top">
                                    <img src="<?= $rutaImagen ?>"
                                        class="product-img w-100"
                                        alt="<?= htmlspecialchars($prod['nombre']) ?>"
                                        style="height: 250px; object-fit: cover; transition: transform 0.3s;">
                                </a>
                                
                                <div class="card-body d-flex flex-column pt-4">
                                    <!-- Categoría -->
                                    <div class="mb-2">
                                        <span class="badge bg-light text-secondary border">
                                            <?= htmlspecialchars($prod['nombre_categoria']) ?>
                                        </span>
                                    </div>

                                    <!-- Nombre -->
                                    <h5 class="card-title fw-bold mb-2">
                                        <a href="detalle_producto.php?id=<?= $prod['id_producto'] ?>" class="text-dark text-decoration-none">
                                            <?= htmlspecialchars($prod['nombre']) ?>
                                        </a>
                                    </h5>

                                    <!-- Descripción -->
                                    <p class="card-text text-muted small flex-grow-1 mb-3">
                                        <?= htmlspecialchars(substr($prod['descripcion'], 0, 100)) . (strlen($prod['descripcion']) > 100 ? '...' : '') ?>
                                    </p>

                                    <?php if ($prod['stock'] <= 5): ?>
                                        <div class="mb-2">
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-exclamation-triangle"></i> ¡Últimas unidades!
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <hr class="my-3 opacity-25">

                                    <div class="d-flex align-items-center justify-content-between mt-auto">
                                        <!-- Precio -->
                                        <div>
                                            <small class="text-secondary text-decoration-line-through d-block" style="font-size: 0.9em;">
                                                S/ <?= number_format($prod['precio_venta'] * 1.2, 2) // Precio ficticio anterior ?>
                                            </small>
                                            <h4 class="text-danger fw-bold mb-0">
                                                S/ <?= number_format($prod['precio_venta'], 2) ?>
                                            </h4>
                                        </div>

                                        <!-- Botón agregar -->
                                        <button class="btn btn-danger btn-lg rounded-circle shadow-sm p-0 d-flex align-items-center justify-content-center"
                                            style="width: 50px; height: 50px;"
                                            onclick="agregarAlCarrito(
                                                <?= $prod['id_producto'] ?>,
                                                '<?= htmlspecialchars($prod['nombre']) ?>',
                                                <?= $prod['precio_venta'] ?>,
                                                '<?= $rutaImagen ?>'
                                            )"
                                            title="Agregar al carrito">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="py-5 bg-light rounded-3">
                            <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
                            <h3 class="text-muted">No hay promociones activas por el momento</h3>
                            <p class="lead">Vuelve pronto para ver nuestras ofertas especiales.</p>
                            <a href="menu.php" class="btn btn-primary btn-lg mt-3">
                                <i class="fas fa-utensils"></i> Ver Menú Completo
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Cart functionality -->
    <script src="./js/carrito.js"></script>
    <script src="./js/menu2.js"></script>
    
    <script>
    // Wrapper function for cart buttons to ensure compatibility with carrito.js logic
    function agregarAlCarrito(id, nombre, precio, imagen) {
        // Create product object
        const producto = {
            id: id,
            nombre: nombre,
            precio: precio,
            imagen: imagen,
            categoria: 'Promoción' // Mark as promotion in cart
        };
        
        // Add animation effect
        const btn = event.currentTarget;
        const icon = btn.querySelector('i');
        
        // Call global addToCart from carrito.js
        addToCart(producto, 1);
        
        // Optional: Visual feedback button animation
        if(icon) {
            icon.classList.remove('fa-cart-plus');
            icon.classList.add('fa-check');
            setTimeout(() => {
                icon.classList.remove('fa-check');
                icon.classList.add('fa-cart-plus');
            }, 1000);
        }
    }
    
    // Add hover effects via JS for extra polish
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.classList.add('translate-up');
        });
        card.addEventListener('mouseleave', () => {
            card.classList.remove('translate-up');
        });
    });
    </script>
    
    <style>
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
        }
        
        .translate-up {
            transform: translateY(-10px);
        }
    </style>
</body>
</html>
<?php include '../includes/footer.php'; ?>
