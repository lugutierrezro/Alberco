<?php
session_start();
$pageTitle = "Nosotros - ALBERCO Pollería y Chifa Premium";
include __DIR__ . '/../includes/header.php';
?>

<style>
/* About Page Styles */
.about-hero {
    position: relative;
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--dark) 0%, var(--dark-80) 100%);
    overflow: hidden;
}

.about-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('../Assets/imagenes/restaurante.png') center/cover;
    opacity: 0.2;
}

/* Timeline Styles */
.timeline {
    position: relative;
    max-width: 1000px;
    margin: 0 auto;
    padding: var(--space-2xl) 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 4px;
    height: 100%;
    background: var(--gradient-rainbow);
}

.timeline-item {
    position: relative;
    margin-bottom: var(--space-3xl);
}

.timeline-item:nth-child(odd) .timeline-content {
    margin-left: auto;
    text-align: left;
}

.timeline-item:nth-child(even) .timeline-content {
    margin-right: auto;
    text-align: right;
}

.timeline-content {
    width: calc(50% - 40px);
    background: var(--light);
    padding: var(--space-lg);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    position: relative;
}

.timeline-year {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 80px;
    background: var(--gradient-primary);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--light);
    font-weight: 800;
    font-size: var(--text-lg);
    box-shadow: 0 0 0 10px var(--light-95);
    z-index: 2;
}

/*Values Cards */
.value-card {
    background: var(--light);
    border-radius: var(--radius-xl);
    padding: var(--space-xl);
    text-align: center;
    transition: all var(--transition-base);
    box-shadow: var(--shadow-md);
    height: 100%;
}

.value-card:hover {
    transform: translateY(-12px);
    box-shadow: var(--shadow-xl);
}

.value-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto var(--space-md);
    background: var(--gradient-primary);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--light);
    font-size: 2rem;
}

.value-title {
    font-family: var(--font-display);
    font-size: var(--text-xl);
    font-weight: 700;
    margin-bottom: var(--space-sm);
}

/* Gallery Grid */
.gallery-grid-modern {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--space-lg);
}

.gallery-item-modern {
    position: relative;
    overflow: hidden;
    border-radius: var(--radius-xl);
    height: 300px;
    cursor: pointer;
}

.gallery-item-modern img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--transition-slow);
}

.gallery-item-modern:hover img {
    transform: scale(1.15);
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.8) 100%);
    opacity: 0;
    transition: opacity var(--transition-base);
    display: flex;
    align-items: flex-end;
    padding: var(--space-md);
}

.gallery-item-modern:hover .gallery-overlay {
    opacity: 1;
}

@media (max-width: 768px) {
    .timeline::before {
        left: 20px;
    }
    
    .timeline-content {
        width: calc(100% - 80px);
        margin-left: 60px !important;
        text-align: left !important;
    }
    
    .timeline-year {
        left: 20px;
    }
}
</style>

<!-- Hero Section -->
<section class="about-hero">
    <div class="container-modern text-center text-white">
        <div data-aos="fade-up">
            <h1 class="display-2 fw-bold mb-4">
                Nuestra <span class="text-gradient" style="background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Historia</span>
            </h1>
            <p class="lead" style="color: var(--light-80); max-width: 700px; margin: 0 auto;">
                Más de 15 años compartiendo sabor, tradición y pasión por la buena comida
            </p>
        </div>
    </div>
</section>

<!-- Timeline Section -->
<section class="section-spacing">
    <div class="container-modern">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 fw-bold mb-3">
                Nuestro <span class="text-gradient">Camino</span>
            </h2>
            <p class="text-muted" style="max-width: 600px; margin: 0 auto;">
                Un viaje de dedicación, calidad y amor por la cocina peruana
            </p>
        </div>

        <div class="timeline">
            <div class="timeline-item" data-aos="fade-up">
                <div class="timeline-year">2008</div>
                <div class="timeline-content">
                    <h3 class="fw-bold mb-3">Los Inicios</h3>
                    <p class="text-muted">
                        ALBERCO abre sus puertas en Jicamarca con un pequeño local y grandes sueños. 
                        Nuestro objetivo: ofrecer el mejor pollo a la brasa de la zona.
                    </p>
                </div>
            </div>

            <div class="timeline-item" data-aos="fade-up" data-aos-delay="100">
                <div class="timeline-year">2012</div>
                <div class="timeline-content">
                    <h3 class="fw-bold mb-3">Expansión del Menú</h3>
                    <p class="text-muted">
                        Incorporamos la tradición chifa peruana, fusionando dos de las cocinas más 
                        queridas del Perú en un solo lugar.
                    </p>
                </div>
            </div>

            <div class="timeline-item" data-aos="fade-up" data-aos-delay="200">
                <div class="timeline-year">2018</div>
                <div class="timeline-content">
                    <h3 class="fw-bold mb-3">Reconocimiento</h3>
                    <p class="text-muted">
                        Alcanzamos los 10,000 clientes satisfechos y nos convertimos en referente 
                        de calidad en la zona este de Lima.
                    </p>
                </div>
            </div>

            <div class="timeline-item" data-aos="fade-up" data-aos-delay="300">
                <div class="timeline-year">2024</div>
                <div class="timeline-content">
                    <h3 class="fw-bold mb-3">Transformación Digital</h3>
                    <p class="text-muted">
                        Lanzamos nuestra plataforma de pedidos online, acercándonos aún más a 
                        nuestros clientes con delivery y para llevar.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="section-spacing" style="background: var(--light-95);">
    <div class="container-modern">
        <div class="row g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3 class="value-title">Nuestra Misión</h3>
                    <p class="text-muted">
                        Brindar a nuestros clientes una experiencia gastronómica excepcional, 
                        ofreciendo platillos de alta calidad elaborados con ingredientes frescos 
                        y recetas auténticas, manteniendo viva la tradición culinaria peruana 
                        mientras superamos las expectativas en servicio y sabor.
                    </p>
                </div>
            </div>

            <div class="col-lg-6" data-aos="fade-left">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3 class="value-title">Nuestra Visión</h3>
                    <p class="text-muted">
                        Ser reconocidos como la pollería y chifa líder en Lima, expandiendo 
                        nuestra presencia a nivel nacional, siendo referente de calidad, 
                        innovación y servicio excepcional, preservando las tradiciones 
                        culinarias peruanas para las futuras generaciones.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="section-spacing">
    <div class="container-modern">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 fw-bold mb-3">
                Nuestros <span class="text-gradient">Valores</span>
            </h2>
        </div>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="0">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h4 class="value-title">Pasión</h4>
                    <p class="text-muted small">
                        Amamos lo que hacemos y se refleja en cada plato
                    </p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="100">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h4 class="value-title">Calidad</h4>
                    <p class="text-muted small">
                        Solo ingredientes frescos y de primera
                    </p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="200">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="value-title">Familia</h4>
                    <p class="text-muted small">
                        Tratamos a cada cliente como parte de nuestra familia
                    </p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="300">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h4 class="value-title">Tradición</h4>
                    <p class="text-muted small">
                        Recetas auténticas transmitidas por generaciones
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section class="section-spacing" style="background: var(--dark); color: var(--light);">
    <div class="container-modern">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 fw-bold mb-3">
                Galería
            </h2>
            <p style="color: var(--light-80);">
                Momentos que capturan nuestra esencia
            </p>
        </div>

        <div class="gallery-grid-modern">
            <div class="gallery-item-modern" data-aos="fade-up" data-aos-delay="0">
                <img src="https://images.unsplash.com/photo-1598103442097-8b74394b95c6?w=600&h=400&fit=crop" 
                     alt="Pollo a la brasa" loading="lazy">
                <div class="gallery-overlay">
                    <p class="text-white fw-bold">Pollo a la Brasa</p>
                </div>
            </div>

            <div class="gallery-item-modern" data-aos="fade-up" data-aos-delay="100">
                <img src="https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=600&h=400&fit=crop" 
                     alt="Arroz Chaufa" loading="lazy">
                <div class="gallery-overlay">
                    <p class="text-white fw-bold">Arroz Chaufa</p>
                </div>
            </div>

            <div class="gallery-item-modern" data-aos="fade-up" data-aos-delay="200">
                <img src="https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?w=600&h=400&fit=crop" 
                     alt="Platos variados" loading="lazy">
                <div class="gallery-overlay">
                    <p class="text-white fw-bold">Variedad de Platos</p>
                </div>
            </div>

            <div class="gallery-item-modern" data-aos="fade-up" data-aos-delay="300">
                <img src="https://images.unsplash.com/photo-1552611052-33e04de081de?w=600&h=400&fit=crop" 
                     alt="Tallarín saltado" loading="lazy">
                <div class="gallery-overlay">
                    <p class="text-white fw-bold">Tallarín Saltado</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Location Section -->
<section class="section-spacing" style="background: var(--light-95);">
    <div class="container-modern">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 fw-bold mb-3">
                Encuéntranos
            </h2>
        </div>

        <div class="row g-5 align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="card-modern" style="padding: var(--space-xl);">
                    <h3 class="fw-bold mb-4">Visítanos</h3>
                    <div class="mb-4">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="value-icon" style="width: 50px; height: 50px; font-size: 1.25rem;">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <strong class="d-block mb-1">Dirección</strong>
                                <p class="text-muted mb-0">Av. 5 de Agosto Mz. A1 Lt.13 - Anexo 8, Jicamarca</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="value-icon" style="width: 50px; height: 50px; font-size: 1.25rem;">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <strong class="d-block mb-1">Teléfono</strong>
                                <p class="text-muted mb-0">(01) 234-5678</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-3">
                            <div class="value-icon" style="width: 50px; height: 50px; font-size: 1.25rem;">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <strong class="d-block mb-1">Horario</strong>
                                <p class="text-muted mb-0">Lunes a Domingo<br>11:00 AM - 11:00 PM</p>
                            </div>
                        </div>
                    </div>

                    <a href="../Vista/contacto.php" class="btn-modern btn-primary w-100">
                        <i class="fas fa-envelope me-2"></i>
                        Contáctanos
                    </a>
                </div>
            </div>

            <div class="col-lg-6" data-aos="fade-left">
                <div style="border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-xl);">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3900.9340315447693!2d-76.91252352487541!3d-12.01847228817698!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9105cef5a7e6fca3%3A0x9e3f14b4adbc2b16!2s2376%2BXQ8%2C%20Lurigancho-Chosica%2015461!5e0!3m2!1ses!2spe!4v1730347000000!5m2!1ses!2spe" 
                        width="100%" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
