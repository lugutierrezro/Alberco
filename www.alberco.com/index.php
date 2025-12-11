<?php
session_start();
$pageTitle = "Inicio - ALB

ERCO Pollería y Chifa Premium";
include 'includes/header.php';

// Include centralized initialization
require_once 'app/init.php';

// Get promotions
$promociones = getPromociones(6);

// Get categories
$categoriaModel = new Categoria();
$categorias = $categoriaModel->getAll();
?>

<style>
/* Hero Section Ultra Modern */
.hero-modern {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--dark) 0%, var(--dark-80) 100%);
    overflow: hidden;
}

.hero-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('Assets/imagenes/fondo3.jpg') center/cover no-repeat;
    opacity: 0.15;
    z-index: 0;
}

.hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: var(--light);
    max-width: 900px;
    padding: 2rem;
}

.hero-title {
    font-family: var(--font-display);
    font-size: clamp(3rem, 8vw, 6rem);
    font-weight: 900;
    line-height: 1.1;
    margin-bottom: var(--space-md);
    background: var(--gradient-rainbow);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-subtitle {
    font-size: clamp(1.25rem, 3vw, 1.75rem);
    color: var(--light-80);
    margin-bottom: var(--space-xl);
    font-weight: 300;
}

.hero-cta-group {
    display: flex;
    gap: var(--space-md);
    justify-content: center;
    flex-wrap: wrap;
}

/* Scroll Indicator */
.scroll-indicator {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 3;
    animation: float 3s ease-in-out infinite;
}

.scroll-indicator i {
    font-size: 2rem;
    color: var(--light-80);
}

/* Products Card Premium */
.product-card-premium {
    background: var(--light);
    border-radius: var(--radius-xl);
    overflow: hidden;
    transition: all var(--transition-base);
    box-shadow: var(--shadow-md);
    position: relative;
}

.product-card-premium:hover {
    transform: translateY(-12px);
    box-shadow: var(--shadow-xl);
}

.product-card-image {
    position: relative;
    overflow: hidden;
    height: 280px;
}

.product-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--transition-slow);
}

.product-card-premium:hover .product-card-image img {
    transform: scale(1.1);
}

.product-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: var(--gradient-primary);
    color: var(--light);
    padding: 0.5rem 1rem;
    border-radius: var(--radius-full);
    font-weight: 700;
    font-size: 0.875rem;
    z-index: 2;
}

.product-card-body {
    padding: var(--space-lg);
}

.product-title {
    font-family: var(--font-display);
    font-size: var(--text-xl);
    font-weight: 700;
    margin-bottom: var(--space-sm);
    color: var(--dark);
}

.product-price {
    font-size: var(--text-2xl);
    font-weight: 800;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: var(--space-md);
}

/* Stats Section */
.stats-section {
    background: var(--gradient-dark);
    padding: var(--space-3xl) 0;
}

.stat-card {
    text-align: center;
    padding: var(--space-lg);
}

.stat-number {
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 900;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1;
    margin-bottom: var(--space-sm);
}

.stat-label {
    color: var(--light-80);
    font-size: var(--text-lg);
    font-weight: 500;
}

/* Category Card */
.category-card {
    position: relative;
    height: 300px;
    border-radius: var(--radius-xl);
    overflow: hidden;
    cursor: pointer;
    transition: all var(--transition-base);
}

.category-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0,0,0,0.4) 0%, rgba(0,0,0,0.7) 100%);
    transition: all var(--transition-base);
    z-index: 1;
}

.category-card:hover::before {
    background: linear-gradient(135deg, rgba(255,61,0,0.6) 0%, rgba(0,0,0,0.8) 100%);
}

.category-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--transition-slow);
}

.category-card:hover img {
    transform: scale(1.15);
}

.category-card-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: var(--space-lg);
    z-index: 2;
    color: var(--light);
}

.category-title {
    font-family: var(--font-display);
    font-size: var(--text-2xl);
    font-weight: 700;
    margin-bottom: var(--space-xs);
}

.category-count {
    font-size: var(--text-sm);
    color: var(--light-90);
}
</style>

<!-- Hero Section Ultra Modern -->
<section class="hero-modern" data-aos="fade">
    <div class="hero-content">
        <h1 class="hero-title" data-aos="fade-up" data-aos-delay="100">
            Sabor Premium<br>en Cada Bocado
        </h1>
        <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="200">
            Pollos a la brasa y chifa fusión preparados con ingredientes de primera calidad
        </p>
        <div class="hero-cta-group" data-aos="fade-up" data-aos-delay="300">
            <a href="Vista/menu.php" class="btn-modern btn-primary" style="font-size: 1.125rem; padding: 1rem 2.5rem;">
                <i class="fas fa-utensils me-2"></i>
                Ver Menú
            </a>
            <a href="Vista/promociones.php" class="btn-modern btn-glass" style="font-size: 1.125rem; padding: 1rem 2.5rem;">
                <i class="fas fa-tags me-2"></i>
                Promociones
            </a>
        </div>
        
        <?php
        // Anuncios Hero
        if (!empty($siteConfig['mostrar_anuncios']) && $siteConfig['mostrar_anuncios']) {
            try {
                $anunciosHero = $configService->getAnuncios('hero');
                
                if (!empty($anunciosHero)) {
                    echo '<div class="mt-4" data-aos="fade-up" data-aos-delay="400">';
                    foreach ($anunciosHero as $anuncio):
                        $tipoClase = [
                            'alerta' => 'danger',
                            'info' => 'info',
                            'promocion' => 'success',
                            'evento' => 'warning'
                        ][$anuncio['tipo']] ?? 'info';
        ?>
        <div class="alert alert-<?= $tipoClase ?> text-center" 
             style="<?= $anuncio['estilo_css'] ?? '' ?>; backdrop-filter: blur(10px); background: rgba(255,255,255,0.95) !important;">
            <strong><?= htmlspecialchars($anuncio['titulo']) ?></strong>
            <?php if (!empty($anuncio['contenido'])): ?>
            - <?= htmlspecialchars($anuncio['contenido']) ?>
            <?php endif; ?>
        </div>
        <?php
                    endforeach;
                    echo '</div>';
                }
            } catch (Exception $e) {
                error_log("Error al obtener anuncios hero: " . $e->getMessage());
            }
        }
        ?>
    </div>
    
    <div class="scroll-indicator">
        <i class="fas fa-chevron-down"></i>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container-modern">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="0">
                <div class="stat-card">
                    <div class="stat-number" data-counter="15">0</div>
                    <div class="stat-label">Años de Experiencia</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-card">
                    <div class="stat-number" data-counter="10000">0</div>
                    <div class="stat-label">Clientes Satisfechos</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-card">
                    <div class="stat-number" data-counter="150">0</div>
                    <div class="stat-label">Platos en Nuestra Carta</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-card">
                    <div class="stat-number" data-counter="4.9">0</div>
                    <div class="stat-label">Rating Promedio</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Promociones Premium Section -->
<section class="section-spacing" style="background: var(--light-95);">
    <div class="container-modern">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 fw-bold mb-3">
                Promociones <span class="text-gradient">de la Semana</span>
            </h2>
            <p class="text-muted" style="max-width: 600px; margin: 0 auto;">
                Aprovecha nuestras ofertas especiales y disfruta del mejor sabor al mejor precio
            </p>
        </div>

        <?php if (!empty($promociones)): ?>
        <div class="row g-4">
            <?php foreach(array_slice($promociones, 0, 6) as $index => $promo): 
                $rutaImagen = getImagePath($promo['imagen']);
                $rutaDetalle = "Vista/detalle_producto.php?id=" . $promo['id_producto'];
            ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                <div class="product-card-premium">
                    <div class="product-badge">OFERTA</div>
                    <div class="product-card-image">
                        <img src="<?= $rutaImagen ?>" alt="<?= htmlspecialchars($promo['nombre']) ?>" loading="lazy">
                    </div>
                    <div class="product-card-body">
                        <h3 class="product-title"><?= htmlspecialchars($promo['nombre']) ?></h3>
                        <p class="text-muted small mb-3"><?= htmlspecialchars($promo['descripcion']) ?></p>
                        <div class="product-price">S/ <?= number_format($promo['precio_venta'], 2) ?></div>
                        <a href="<?= $rutaDetalle ?>" class="btn-modern btn-primary w-100">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Agregar al Carrito
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5" data-aos="fade-up">
            <a href="Vista/promociones.php" class="btn-modern btn-outline">
                Ver Todas las Promociones
                <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        <?php else: ?>
        <div class="alert alert-info text-center" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            No hay promociones disponibles en este momento
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Categories Section -->
<?php if (!empty($categorias)): ?>
<section class="section-spacing">
    <div class="container-modern">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 fw-bold mb-3">
                Explora Nuestras <span class="text-gradient">Categorías</span>
            </h2>
            <p class="text-muted" style="max-width: 600px; margin: 0 auto;">
                De pollos a la brasa hasta exquisita comida china, tenemos todo lo que necesitas
            </p>
        </div>

        <div class="row g-4">
            <?php foreach(array_slice($categorias, 0, 4) as $index => $cat): 
                // Usar imagen por defecto si no tiene
                $imgCategoria = !empty($cat['imagen']) ? getImagePath($cat['imagen']) : 'Assets/imagenes/fondo3.jpg';
            ?>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                <a href="Vista/menu.php?categoria=<?= $cat['id_categoria'] ?>" class="text-decoration-none">
                    <div class="category-card">
                        <img src="<?= $imgCategoria ?>" alt="<?= htmlspecialchars($cat['nombre_categoria']) ?>">
                        <div class="category-card-content">
                            <h3 class="category-title"><?= htmlspecialchars($cat['nombre_categoria']) ?></h3>
                            <p class="category-count">Ver productos</p>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="section-spacing" style="background: var(--dark); color: var(--light);">
    <div class="container-modern text-center">
        <div data-aos="fade-up">
            <h2 class="display-4 fw-bold mb-4">
                ¿Listo para ordenar?
            </h2>
            <p class="lead mb-5" style="color: var(--light-80); max-width: 700px; margin: 0 auto;">
                Haz tu pedido ahora y recibe en delivery o recoge en nuestro local
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="Vista/pedido.php" class="btn-modern btn-primary" style="font-size: 1.125rem; padding: 1rem 2.5rem;">
                    <i class="fas fa-motorcycle me-2"></i>
                    Pedir Delivery
                </a>
                <a href="Vista/contacto.php" class="btn-modern btn-glass" style="font-size: 1.125rem; padding: 1rem 2.5rem;">
                    <i class="fas fa-phone me-2"></i>
                    Contactanos
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Counter Animation Script -->
<script>
// Counter Animation
function animateCounter(element) {
    const target = parseFloat(element.getAttribute('data-counter'));
    const duration = 2000;
    const steps = 60;
    const increment = target / steps;
    let current = 0;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, duration / steps);
}

// Trigger counter animation when section is visible
const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const counters = entry.target.querySelectorAll('[data-counter]');
            counters.forEach(counter => {
                if (!counter.classList.contains('animated')) {
                    animateCounter(counter);
                    counter.classList.add('animated');
                }
            });
        }
    });
}, { threshold: 0.5 });

document.querySelector('.stats-section') && statsObserver.observe(document.querySelector('.stats-section'));

// Cart functionality
const cartBadge = document.getElementById('cartBadge');
if (cartBadge) {
    const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
    cartBadge.textContent = cart.length;
}
</script>

<?php include 'includes/footer.php'; ?>
