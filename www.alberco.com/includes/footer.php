<!-- Footer Premium -->
<style>
.footer-modern {
    background: linear-gradient(135deg, var(--dark) 0%, var(--dark-80) 100%);
    color: var(--light);
    position: relative;
    overflow: hidden;
}

.footer-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-rainbow);
}

.footer-section-title {
    font-family: var(--font-display);
    font-size: var(--text-xl);
    font-weight: 700;
    margin-bottom: var(--space-md);
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.footer-link {
    color: var(--light-80);
    text-decoration: none;
    display: inline-block;
    padding: 0.5rem 0;
    transition: all var(--transition-fast);
    position: relative;
}

.footer-link::before {
    content: '→';
    position: absolute;
    left: -20px;
    opacity: 0;
    transition: all var(--transition-fast);
}

.footer-link:hover {
    color: var(--primary);
    transform: translateX(8px);
}

.footer-link:hover::before {
    opacity: 1;
    left: -16px;
}

.newsletter-form {
    display: flex;
    gap: 0.5rem;
    margin-top: var(--space-md);
}

.newsletter-input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 2px solid var(--dark-40);
    border-radius: var(--radius-full);
    background: rgba(255, 255, 255, 0.05);
    color: var(--light);
    font-size: var(--text-base);
    transition: all var(--transition-fast);
}

.newsletter-input:focus {
    outline: none;
    border-color: var(--primary);
    background: rgba(255, 255, 255, 0.1);
}

.newsletter-input::placeholder {
    color: var(--light-80);
}

.newsletter-btn {
    padding: 0.75rem 2rem;
    background: var(--gradient-primary);
    border: none;
    border-radius: var(--radius-full);
    color: var(--light);
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition-base);
    white-space: nowrap;
}

.newsletter-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-glow);
}

.social-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 45px;
    height: 45px;
    border-radius: var(--radius-full);
    background: rgba(255, 255, 255, 0.05);
    color: var(--light);
    font-size: 1.25rem;
    transition: all var(--transition-base);
    text-decoration: none;
}

.social-link:hover {
    background: var(--primary);
    color: var(--light);
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.footer-bottom {
    margin-top: var(--space-2xl);
    padding-top: var(--space-lg);
    border-top: 1px solid var(--dark-40);
    text-align: center;
    font-size: var(--text-sm);
    color: var(--light-80);
}

@media (max-width: 768px) {
    .newsletter-form {
        flex-direction: column;
    }
    
    .newsletter-btn {
        width: 100%;
    }
}
</style>

<?php
// Cargar servicio de configuración si no está cargado
if (!isset($configService)) {
    require_once __DIR__ . '/../Services/configuracion_service.php';
    $configService = getConfiguracionService();
}

// Detectar ruta para links
$currentPath = $_SERVER['PHP_SELF'];
$footerPagePath = (strpos($currentPath, 'Vista/') !== false) ? './' : 'Vista/';
$footerHomePath = (strpos($currentPath, 'Vista/') !== false) ? '../index.php' : 'index.php';
?>

<?php
// Anuncios Footer
$mostrarAnuncios = isset($siteConfig['mostrar_anuncios']) && $siteConfig['mostrar_anuncios'];

if ($mostrarAnuncios) {
    try {
        $anunciosFooter = $configService->getAnuncios('footer');
        
        if (!empty($anunciosFooter)) {
            foreach ($anunciosFooter as $anuncio):
                $tipoClase = [
                    'alerta' => 'danger',
                    'info' => 'info',
                    'promocion' => 'success',
                    'evento' => 'warning'
                ][$anuncio['tipo']] ?? 'info';
?>
<div class="alert alert-<?= $tipoClase ?> mb-0 text-center" 
     data-aos="fade-up" 
     data-aos-duration="800"
     style="border-radius: 0; <?= $anuncio['estilo_css'] ?? '' ?>; z-index: 100; position: relative;">
    <strong><?= htmlspecialchars($anuncio['titulo']) ?></strong>
    <?php if (!empty($anuncio['contenido'])): ?>
    - <?= htmlspecialchars($anuncio['contenido']) ?>
    <?php endif; ?>
</div>
<?php
            endforeach;
        }
    } catch (Exception $e) {
        error_log("Error al obtener anuncios footer: " . $e->getMessage());
    }
}
?>

<footer class="footer-modern">
    <div class="container-modern section-spacing">
        <div class="row g-5">
            <!-- Columna 1: Sobre Alberco -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="0">
                <h3 class="footer-section-title">ALBERCO</h3>
                <p class="mb-4" style="color: var(--light-80); line-height: 1.8;">
                    Somos un restaurante especializado en pollos a la brasa y chifa fusión, 
                    ofreciendo los mejores sabores con ingredientes de primera calidad 
                    desde hace más de 15 años.
                </p>
                
                <!-- Social Media -->
                <div class="d-flex gap-3 mb-4">
                    <a href="#" class="social-link" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="TikTok">
                        <i class="fab fa-tiktok"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>

            <!-- Columna 2: Enlaces Rápidos -->
            <div class="col-lg-2 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <h3 class="footer-section-title">Enlaces</h3>
                <ul class="list-unstyled">
                    <li><a href="<?= $footerHomePath ?>" class="footer-link">Inicio</a></li>
                    <li><a href="<?= $footerPagePath ?>menu.php" class="footer-link">Carta</a></li>
                    <li><a href="<?= $footerPagePath ?>promociones.php" class="footer-link">Promociones</a></li>
                    <li><a href="<?= $footerPagePath ?>nosotros.php" class="footer-link">Nosotros</a></li>
                    <li><a href="<?= $footerPagePath ?>contacto.php" class="footer-link">Contacto</a></li>
                </ul>
            </div>

            <!-- Columna 3: Mi Cuenta -->
            <div class="col-lg-2 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <h3 class="footer-section-title">Mi Cuenta</h3>
                <ul class="list-unstyled">
                    <li><a href="<?= $footerPagePath ?>perfil_cliente.php" class="footer-link">Mi Perfil</a></li>
                    <li><a href="<?= $footerPagePath ?>mis_pedidos.php" class="footer-link">Mis Pedidos</a></li>
                    <li><a href="<?= $footerPagePath ?>mis_puntos.php" class="footer-link">Mis Puntos</a></li>
                    <li><a href="<?= $footerPagePath ?>mis_direcciones.php" class="footer-link">Direcciones</a></li>
                    <li><a href="<?= $footerPagePath ?>seguimiento_pedido.php" class="footer-link">Seguimiento</a></li>
                </ul>
            </div>

            <!-- Columna 4: Newsletter & Contacto -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <h3 class="footer-section-title">Newsletter</h3>
                <p style="color: var(--light-80); margin-bottom: var(--space-sm);">
                    Suscríbete y recibe ofertas exclusivas
                </p>
                <form class="newsletter-form" onsubmit="return false;">
                    <input type="email" class="newsletter-input" placeholder="tu@email.com" required>
                    <button type="submit" class="newsletter-btn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>

                <!-- Contacto Info -->
                <div class="mt-4">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <i class="fas fa-map-marker-alt" style="color: var(--primary); font-size: 1.25rem;"></i>
                        <span style="color: var(--light-80); font-size: 0.9rem;">
                            Av. 5 de Agosto Mz. A1 Lt.13<br>Anexo 8, Jicamarca
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="fas fa-phone" style="color: var(--primary); font-size: 1.25rem;"></i>
                        <a href="tel:012345678" class="footer-link" style="padding: 0;">(01) 234-5678</a>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <i class="fas fa-clock" style="color: var(--primary); font-size: 1.25rem;"></i>
                        <span style="color: var(--light-80); font-size: 0.9rem;">Lun-Dom: 11AM - 11PM</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legal Links -->
        <div class="footer-bottom">
            <div class="mb-3">
                <a href="#" class="footer-link mx-3 small">Términos y Condiciones</a>
                <a href="#" class="footer-link mx-3 small">Política de Privacidad</a>
                <a href="#" class="footer-link mx-3 small">Libro de Reclamaciones</a>
            </div>
            <p class="mb-0">
                &copy; <?php echo date('Y'); ?> <strong>D3spiadado</strong>. Todos los derechos reservados.
                <br>
                <small>Hecho con <i class="fas fa-heart" style="color: var(--primary);"></i> en Perú</small>
            </p>
        </div>
    </div>
</footer>

<!-- Scroll to Top Button -->
<button id="scrollToTop" style="
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 50px;
    height: 50px;
    border-radius: var(--radius-full);
    background: var(--gradient-primary);
    border: none;
    color: var(--light);
    font-size: 1.25rem;
    cursor: pointer;
    box-shadow: var(--shadow-lg);
    opacity: 0;
    visibility: hidden;
    transition: all var(--transition-base);
    z-index: 999;
">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
// Scroll to Top Button
const scrollToTopBtn = document.getElementById('scrollToTop');

window.addEventListener('scroll', function() {
    if (window.scrollY > 300) {
        scrollToTopBtn.style.opacity = '1';
        scrollToTopBtn.style.visibility = 'visible';
    } else {
        scrollToTopBtn.style.opacity = '0';
        scrollToTopBtn.style.visibility = 'hidden';
    }
});

scrollToTopBtn.addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
</script>

<!-- Advanced Effects JS -->
<script src="<?= $basePath ?? './' ?>Constans/js/advanced-effects.js"></script>

<!-- Tema Efectos JS (Navidad, Halloween, etc.) -->
<script src="<?= $basePath ?? './' ?>Constans/js/tema-efectos.js"></script>

</body>
</html>
