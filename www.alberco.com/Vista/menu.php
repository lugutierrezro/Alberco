<?php
$pageTitle = "Menú - ALBERCO Pollería y Chifa";
require_once __DIR__ . '/../app/init.php';

// Get categories and products
$categorias_datos = getCategorias(true);
$productos_datos = getProductos(['disponibles' => true]);

include '../includes/header.php';
?>

<style>
/* Menu Page Styles */
.menu-hero {
    background: linear-gradient(135deg, var(--dark-80) 0%, var(--dark) 100%);
    padding: var(--space-3xl) 0 var(--space-2xl);
    position: relative;
    overflow: hidden;
}

.menu-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('../Assets/imagenes/fondo3.jpg') center/cover;
    opacity: 0.1;
}

.search-box-modern {
    position: relative;
    max-width: 600px;
    margin: 0 auto;
}

.search-input-modern {
    width: 100%;
    padding: 1rem 3rem 1rem 1.5rem;
    border: 2px solid var(--dark-40);
    border-radius: var(--radius-full);
    background: rgba(255,255,255,0.95);
    font-size: var(--text-lg);
    transition: all var(--transition-fast);
    box-shadow: var(--shadow-lg);
}

.search-input-modern:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(255, 61, 0, 0.1);
}

.search-icon {
    position: absolute;
    right: 1.5rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--dark-40);
    font-size: 1.25rem;
}

/* Category Filters Modern - Estático y Armonioso */
.filter-section {
    background: var(--light);
    padding: var(--space-xl) 0;
    box-shadow: var(--shadow-sm);
    border-bottom: 1px solid var(--light-90);
}

.category-filter-btn {
    padding: 0.75rem 1.5rem;
    border: 2px solid var(--light-90);
    border-radius: var(--radius-full);
    background: var(--light);
    color: var(--dark);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.category-filter-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: var(--light-95);
}

.category-filter-btn.active {
    background: var(--gradient-primary);
    border-color: var(--primary);
    color: var(--light);
    box-shadow: 0 4px 12px rgba(255, 61, 0, 0.25);
}

/* Product Grid Modern */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--space-lg);
    margin-top: var(--space-2xl);
}

/* Product Card - Hover Suave */
.product-card-menu {
    background: var(--light);
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
}

.product-card-menu:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
}

.product-image-wrapper {
    position: relative;
    overflow: hidden;
    height: 240px;
    background: var(--light-95);
}

.product-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.product-card-menu:hover .product-image-wrapper img {
    transform: scale(1.08);
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.7) 100%);
    opacity: 0;
    transition: opacity var(--transition-base);
    display: flex;
    align-items: flex-end;
    padding: var(--space-md);
}

.product-card-menu:hover .product-overlay {
    opacity: 1;
}

.quick-view-btn {
    background: var(--light);
    color: var(--dark);
    padding: 0.5rem 1rem;
    border-radius: var(--radius-full);
    font-weight: 600;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.quick-view-btn:hover {
    background: var(--primary);
    color: var(--light);
}

.product-badge-corner {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background: var(--accent);
    color: var(--light);
    padding: 0.5rem 1rem;
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: 700;
    z-index: 2;
    text-transform: uppercase;
}

.product-badge-corner.stock-low {
    background: var(--secondary);
    color: var(--dark);
}

.product-info {
    padding: var(--space-lg);
}

.product-category-tag {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: var(--light-95);
    color: var(--dark-60);
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: var(--space-sm);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.product-name {
    font-family: var(--font-display);
    font-size: var(--text-lg);
    font-weight: 700;
    color: var(--dark);
    margin-bottom: var(--space-xs);
    line-height: 1.3;
}

.product-description {
    font-size: var(--text-sm);
    color: var(--dark-60);
    margin-bottom: var(--space-md);
    line-height: 1.5;
    height: 3em;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.product-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-sm);
}

.product-price-modern {
    font-size: var(--text-xl);
    font-weight: 800;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.add-to-cart-btn-modern {
    flex: 1;
    padding: 0.75rem;
    background: var(--gradient-primary);
    color: var(--light);
    border: none;
    border-radius: var(--radius-full);
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition-base);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.add-to-cart-btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-glow);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: var(--space-3xl) var(--space-lg);
}

.empty-state i {
    font-size: 4rem;
    color: var(--light-80);
    margin-bottom: var(--space-lg);
}

/* Responsive */
@media (max-width: 768px) {
    .filter-section {
        top: 60px;
    }
    
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: var(--space-md);
    }
}
</style>

<!-- Hero Section -->
<section class="menu-hero">
    <div class="container-modern">
        <div class="text-center text-white" data-aos="fade-up">
            <h1 class="display-3 fw-bold mb-3">
                Nuestra <span class="text-gradient" style="background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Carta</span>
            </h1>
            <p class="lead mb-5" style="color: var(--light-80); max-width: 600px; margin: 0 auto;">
                Descubre nuestros deliciosos platos preparados con ingredientes frescos y de calidad
            </p>
            
            <!-- Search Box -->
            <div class="search-box-modern" data-aos="fade-up" data-aos-delay="100">
                <input type="text" 
                       id="searchInput" 
                       class="search-input-modern" 
                       placeholder="Buscar productos...">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>
    </div>
</section>

<!-- Category Filters -->
<section class="filter-section">
    <div class="container-modern">
        <div class="d-flex gap-2 justify-content-center flex-wrap" data-aos="fade-up">
            <button class="category-filter-btn active" onclick="filtrarCategoria(0, event)">
                <i class="fas fa-th-large me-2"></i>
                Todos
            </button>
            <?php foreach ($categorias_datos as $cat): ?>
            <button class="category-filter-btn" onclick="filtrarCategoria(<?= $cat['id_categoria'] ?>, event)">
                <?= htmlspecialchars($cat['nombre_categoria']) ?>
                <span class="badge bg-light text-dark ms-1" style="font-size: 0.75rem;">
                    <?= $cat['total_productos'] ?? 0 ?>
                </span>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Products Grid -->
<section class="section-spacing" style="background: var(--light-95);">
    <div class="container-modern">
        <div class="product-grid" id="productosGrid">
            <?php if (!empty($productos_datos)): ?>
                <?php foreach ($productos_datos as $index => $prod): 
                    $rutaImagen = getImagePath($prod['imagen']);
                    $rutaDetalle = "detalle_producto.php?id=" . $prod['id_producto'];
                ?>
                <div class="product-card-menu product-item" 
                     data-category="<?= $prod['id_categoria'] ?>"
                     data-aos="fade-up" 
                     data-aos-delay="<?= $index * 50 ?>">
                    
                    <!-- Badge -->
                    <?php if (isset($prod['promocion']) && $prod['promocion']): ?>
                        <div class="product-badge-corner">Oferta</div>
                    <?php elseif ($prod['stock'] <= 5): ?>
                        <div class="product-badge-corner stock-low">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Stock Bajo
                        </div>
                    <?php endif; ?>
                    
                    <!-- Image -->
                    <div class="product-image-wrapper">
                        <img src="<?= $rutaImagen ?>" 
                             alt="<?= htmlspecialchars($prod['nombre']) ?>"
                             loading="lazy">
                        <div class="product-overlay">
                            <button class="quick-view-btn" onclick="window.location.href='<?= $rutaDetalle ?>'">
                                <i class="fas fa-eye me-2"></i>
                                Ver Detalles
                            </button>
                        </div>
                    </div>
                    
                    <!-- Info -->
                    <div class="product-info">
                        <span class="product-category-tag">
                            <?= htmlspecialchars($prod['nombre_categoria'] ?? 'Sin categoría') ?>
                        </span>
                        <h3 class="product-name"><?= htmlspecialchars($prod['nombre']) ?></h3>
                        <p class="product-description">
                            <?= htmlspecialchars($prod['descripcion'] ?? 'Delicioso producto de alta calidad') ?>
                        </p>
                        
                        <div class="product-footer">
                            <div class="product-price-modern">
                                S/ <?= number_format($prod['precio_venta'], 2) ?>
                            </div>
                            <button class="add-to-cart-btn-modern" 
                                    onclick="agregarAlCarrito(
                                        <?= $prod['id_producto'] ?>,
                                        '<?= htmlspecialchars($prod['nombre']) ?>',
                                        <?= $prod['precio_venta'] ?>,
                                        '<?= $rutaImagen ?>'
                                    )">
                                <i class="fas fa-cart-plus"></i>
                                Agregar
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h3 class="text-muted">No se encontraron productos</h3>
                        <p class="text-muted">Intenta con otro filtro o búsqueda</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- No Results Message (hidden by default) -->
        <div id="noResultsMessage" class="empty-state" style="display: none;">
            <i class="fas fa-search"></i>
            <h3 class="text-muted">No se encontraron resultados</h3>
            <p class="text-muted">Intenta con otros términos de búsqueda</p>
        </div>
    </div>
</section>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="./js/carrito.js"></script>

<script>
// Cart functionality
function agregarAlCarrito(id, nombre, precio, imagen) {
    const producto = {
        id: id,
        nombre: nombre,
        precio: precio,
        imagen: imagen,
        categoria: 'Producto'
    };
    addToCart(producto, 1);
}

// Category Filter
function filtrarCategoria(categoriaId, event) {
    // Update active button
    document.querySelectorAll('.category-filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Filter products
    const productos = document.querySelectorAll('.product-item');
    let visibleCount = 0;
    
    productos.forEach(producto => {
        if (categoriaId === 0 || producto.dataset.category == categoriaId) {
            producto.style.display = 'block';
            visibleCount++;
        } else {
            producto.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    document.getElementById('noResultsMessage').style.display = 
        visibleCount === 0 ? 'block' : 'none';
    document.getElementById('productosGrid').style.display = 
        visibleCount === 0 ? 'none' : 'grid';
}

// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const productos = document.querySelectorAll('.product-item');
    let visibleCount = 0;
    
    productos.forEach(producto => {
        const nombre = producto.querySelector('.product-name').textContent.toLowerCase();
        const descripcion = producto.querySelector('.product-description').textContent.toLowerCase();
        
        if (nombre.includes(searchTerm) || descripcion.includes(searchTerm)) {
            producto.style.display = 'block';
            visibleCount++;
        } else {
            producto.style.display = 'none';
        }
    });
    
    // Show/hide no results
    document.getElementById('noResultsMessage').style.display = 
        visibleCount === 0 ? 'block' : 'none';
    document.getElementById('productosGrid').style.display = 
        visibleCount === 0 ? 'none' : 'grid';
});

// Update cart badge
const cartBadge = document.getElementById('cartBadge');
if (cartBadge) {
    const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
    cartBadge.textContent = cart.length;
}
</script>

<?php include '../includes/footer.php'; ?>
