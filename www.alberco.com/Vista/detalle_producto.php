<?php
$pageTitle = "Detalle del Producto - ALBERCO";
require_once __DIR__ . '/../app/init.php';

// Validate ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: menu.php");
    exit;
}

$id = intval($_GET['id']);

// Get product
$prod = getProductoById($id);

// Validate product existence
if (!$prod) {
    include '../includes/header.php';
    echo '<div class="container-modern section-spacing text-center">
            <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
            <h3 class="text-muted">Producto no encontrado</h3>
            <a href="menu.php" class="btn-modern btn-primary mt-3">
                <i class="fas fa-arrow-left"></i> Volver al menú
            </a>
          </div>';
    include '../includes/footer.php';
    exit;
}

// Get category
$cat = getCategoriaById($prod['id_categoria']);
$prod['nombre_categoria'] = $cat ? $cat['nombre_categoria'] : 'Sin Categoría';

// Get image path
$imgPath = getImagePath($prod['imagen']);

// Get related products
$relacionados = getProductosByCategoria($prod['id_categoria']);
$relacionados = array_filter($relacionados, function($p) use ($id) {
    return $p['id_producto'] != $id;
});
$relacionados = array_slice($relacionados, 0, 4);

include '../includes/header.php';
?>

<style>
/* Product Detail Styles */
.product-detail-hero {
    background: linear-gradient(135deg, var(--light-95) 0%, var(--light) 100%);
    padding: var(--space-lg) 0;
}

.breadcrumb-modern {
    display: flex;
    gap: 0.5rem;
    list-style: none;
    padding: 0;
    margin: 0;
    font-size: var(--text-sm);
}

.breadcrumb-modern li {
    color: var(--dark-60);
}

.breadcrumb-modern a {
    color: var(--dark-60);
    text-decoration: none;
    transition: color var(--transition-fast);
}

.breadcrumb-modern a:hover {
    color: var(--primary);
}

.breadcrumb-modern li:not(:last-child)::after {
    content: '›';
    margin-left: 0.5rem;
    color: var(--dark-40);
}

.product-image-container {
    position: relative;
    border-radius: var(--radius-xl);
    overflow: hidden;
    background: var(--light);
    padding: var(--space-xl);
    box-shadow: var(--shadow-xl);
}

.product-image-main {
    width: 100%;
    height: auto;
    max-height: 500px;
    object-fit: contain;
    border-radius: var(--radius-lg);
}

.product-badge-detail {
    position: absolute;
    top: 1.5rem;
    right: 1.5rem;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-full);
    font-weight: 700;
    font-size: var(--text-sm);
    text-transform: uppercase;
}

.product-info-card {
    background: var(--light);
    border-radius: var(--radius-xl);
    padding: var(--space-2xl);
    box-shadow: var(--shadow-lg);
}

.product-category-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: var(--light-95);
    color: var(--dark-60);
    border-radius: var(--radius-full);
    font-size: var(--text-sm);
    font-weight: 600;
    margin-bottom: var(--space-md);
}

.product-name-detail {
    font-family: var(--font-display);
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 900;
    color: var(--dark);
    margin-bottom: var(--space-md);
    line-height: 1.2;
}

.product-price-detail {
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 800;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: var(--space-lg);
}

.product-description {
    font-size: var(--text-lg);
    line-height: 1.8;
    color: var(--dark-80);
    margin-bottom: var(--space-xl);
}

.stock-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius-full);
    font-weight: 600;
    margin-bottom: var(--space-lg);
}

.stock-badge.in-stock {
    background: rgba(0, 200, 83, 0.1);
    color: #008000;
}

.stock-badge.low-stock {
    background: rgba(255, 193, 7, 0.1);
    color: #FF8F00;
}

.stock-badge.out-stock {
    background: rgba(255, 61, 0, 0.1);
    color: #FF3D00;
}

.delivery-info-card {
    background: var(--light-95);
    border-radius: var(--radius-lg);
    padding: var(--space-lg);
    margin-top: var(--space-xl);
}

.delivery-info-item {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    padding: var(--space-sm) 0;
    color: var(--dark-80);
}

.delivery-info-item i {
    width: 24px;
    text-align: center;
}

.related-title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 700;
    margin-bottom: var(--space-xl);
    text-align: center;
}

.related-product-card {
    background: var(--light);
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: all var(--transition-base);
    height: 100%;
}

.related-product-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
}

.related-product-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform var(--transition-slow);
}

.related-product-card:hover .related-product-image {
    transform: scale(1.1);
}

.related-product-body {
    padding: var(--space-lg);
}

.related-product-name {
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--dark);
}

.related-product-price {
    font-weight: 800;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: var(--space-md);
}
</style>

<!-- Breadcrumb -->
<section class="product-detail-hero">
    <div class="container-modern">
        <ul class="breadcrumb-modern" data-aos="fade-right">
            <li><a href="../index.php">Inicio</a></li>
            <li><a href="menu.php">Menú</a></li>
            <li><?= htmlspecialchars($prod['nombre']) ?></li>
        </ul>
    </div>
</section>

<!-- Product Detail -->
<section class="section-spacing">
    <div class="container-modern">
        <div class="row g-5">
            <!-- Image Column -->
            <div class="col-lg-6" data-aos="fade-right">
                <div class="product-image-container">
                    <?php if ($prod['stock'] > 0 && $prod['stock'] <= 5): ?>
                        <div class="product-badge-detail stock-badge low-stock">
                            <i class="fas fa-exclamation-triangle"></i>
                            Stock Bajo
                        </div>
                    <?php elseif ($prod['stock'] <= 0): ?>
                        <div class="product-badge-detail stock-badge out-stock">
                            <i class="fas fa-times-circle"></i>
                            Agotado
                        </div>
                    <?php endif; ?>
                    
                    <img src="<?= htmlspecialchars($imgPath) ?>"
                         class="product-image-main"
                         alt="<?= htmlspecialchars($prod['nombre']) ?>"
                         onerror="this.src='../Assets/no-image.jpg'">
                </div>
            </div>

            <!-- Info Column -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="product-info-card">
                    <!-- Category -->
                    <span class="product-category-badge">
                        <i class="fas fa-tag me-2"></i>
                        <?= htmlspecialchars($prod['nombre_categoria']) ?>
                    </span>

                    <!-- Product Name -->
                    <h1 class="product-name-detail"><?= htmlspecialchars($prod['nombre']) ?></h1>

                    <?php if (!empty($prod['codigo'])): ?>
                        <p class="text-muted mb-3">
                            <small><strong>Código:</strong> <?= htmlspecialchars($prod['codigo']) ?></small>
                        </p>
                    <?php endif; ?>

                    <!-- Description -->
                    <div class="product-description">
                        <?= nl2br(htmlspecialchars($prod['descripcion'])) ?>
                    </div>

                    <!-- Price -->
                    <div class="product-price-detail">
                        S/ <?= number_format($prod['precio_venta'], 2) ?>
                    </div>

                    <!-- Stock Status -->
                    <?php if ($prod['stock'] > 10): ?>
                        <div class="stock-badge in-stock">
                            <i class="fas fa-check-circle"></i>
                            En stock (<?= $prod['stock'] ?> disponibles)
                        </div>
                    <?php elseif ($prod['stock'] > 0): ?>
                        <div class="stock-badge low-stock">
                            <i class="fas fa-exclamation-triangle"></i>
                            Stock bajo (<?= $prod['stock'] ?> disponibles)
                        </div>
                    <?php else: ?>
                        <div class="stock-badge out-stock">
                            <i class="fas fa-times-circle"></i>
                            Producto agotado
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-3 flex-wrap">
                        <?php if ($prod['stock'] > 0): ?>
                            <button class="btn-modern btn-primary flex-1" 
                                    style="min-width: 200px;"
                                    onclick="agregarAlCarrito(
                                        <?= $prod['id_producto'] ?>,
                                        '<?= addslashes($prod['nombre']) ?>',
                                        <?= $prod['precio_venta'] ?>,
                                        '<?= htmlspecialchars($imgPath) ?>'
                                    )">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Agregar al Carrito
                            </button>
                        <?php else: ?>
                            <button class="btn-modern btn-outline flex-1" disabled style="min-width: 200px;">
                                <i class="fas fa-ban me-2"></i>
                                Producto Agotado
                            </button>
                        <?php endif; ?>
                        
                        <a href="menu.php" class="btn-modern btn-outline">
                            <i class="fas fa-arrow-left me-2"></i>
                            Volver
                        </a>
                    </div>

                    <!-- Delivery Info -->
                    <div class="delivery-info-card">
                        <h4 class="fw-bold mb-3">
                            <i class="fas fa-shipping-fast me-2" style="color: var(--primary);"></i>
                            Información de Entrega
                        </h4>
                        <div class="delivery-info-item">
                            <i class="fas fa-check" style="color: #00C853;"></i>
                            <span>Delivery disponible en Lima</span>
                        </div>
                        <div class="delivery-info-item">
                            <i class="fas fa-clock" style="color: var(--primary);"></i>
                            <span>Tiempo estimado: 30-45 minutos</span>
                        </div>
                        <div class="delivery-info-item">
                            <i class="fas fa-credit-card" style="color: var(--secondary);"></i>
                            <span>Efectivo, tarjeta, Yape o Plin</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<?php if (!empty($relacionados)): ?>
<section class="section-spacing" style="background: var(--light-95);">
    <div class="container-modern">
        <h2 class="related-title" data-aos="fade-up">
            Productos <span class="text-gradient">Relacionados</span>
        </h2>

        <div class="row g-4">
            <?php foreach ($relacionados as $index => $rel): 
                $relImgPath = getImagePath($rel['imagen']);
            ?>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                <div class="related-product-card">
                    <div style="overflow: hidden;">
                        <img src="<?= htmlspecialchars($relImgPath) ?>"
                             class="related-product-image"
                             alt="<?= htmlspecialchars($rel['nombre']) ?>"
                             onerror="this.src='../Assets/no-image.jpg'">
                    </div>
                    <div class="related-product-body">
                        <h5 class="related-product-name"><?= htmlspecialchars($rel['nombre']) ?></h5>
                        <div class="related-product-price">
                            S/ <?= number_format($rel['precio_venta'], 2) ?>
                        </div>
                        <a href="detalle_producto.php?id=<?= $rel['id_producto'] ?>"
                           class="btn-modern btn-primary w-100">
                            <i class="fas fa-eye me-2"></i>
                            Ver Detalles
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="./js/carrito.js"></script>

<script>
function agregarAlCarrito(id, nombre, precio, imagen) {
    const producto = {
        id,
        nombre,
        precio,
        imagen,
        categoria: 'Producto'
    };
    addToCart(producto, 1);
    
    // Usar el efecto de animación si está disponible
    if (window.AlbercoEffects && window.AlbercoEffects.animateAddToCart) {
        window.AlbercoEffects.animateAddToCart(event.target);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
