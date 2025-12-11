<?php
require_once __DIR__ . '/../app/init.php';

// Cargar servicio de configuración
require_once __DIR__ . '/../Services/configuracion_service.php';
$configService = getConfiguracionService();
$siteConfig = $configService->getConfiguraciones();

// Detectar ruta
$currentPath = $_SERVER['PHP_SELF'];
if (strpos($currentPath, 'Vista/') !== false) {
    $basePath = '../';
    $pagePath = './';
    $homePath = '../index.php';
} else {
    $basePath = './';
    $pagePath = 'Vista/';
    $homePath = 'index.php';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#FF3D00">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'ALBERCO - Pollería y Chifa Premium'; ?></title>
    
    <!-- Preconnect para performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Fuentes Modernas -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;700;900&display=swap" rel="stylesheet">
    
    <!-- CSS Frameworks -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- AOS - Animate On Scroll -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Modern Theme CSS -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>Constans/css/modern-theme.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>Constans/css/advanced-effects.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>Constans/css/style3.css">
    
    <!-- CSS Dinámico -->
    <style id="dynamic-theme-css">
    <?php echo $configService->generarCSSDinamico(); ?>
    </style>
    
    <!-- Header Specific Styles -->
    <style>
    /* Top Bar Modern */
    .top-bar-modern {
        background: linear-gradient(90deg, var(--dark-80) 0%, var(--dark) 100%);
        padding: 0.5rem 0;
        font-size: 0.875rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .top-bar-modern a {
        color: var(--light-80);
        transition: color var(--transition-fast);
    }
    
    .top-bar-modern a:hover {
        color: var(--primary);
    }
    
    /* Navbar Premium */
    .navbar-modern {
        background: var(--light);
        padding: 1rem 0;
        transition: all var(--transition-base);
        box-shadow: var(--shadow-sm);
        position: sticky;
        top: 0;
        z-index: 1000;
    }
    
    .navbar-modern.scrolled {
        padding: 0.5rem 0;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: var(--shadow-lg);
    }
    
    .navbar-brand-modern {
        font-family: var(--font-display);
        font-size: 2rem;
        font-weight: 900;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        transition: transform var(--transition-base);
    }
    
    .navbar-brand-modern:hover {
        transform: scale(1.05);
    }
    
    .navbar-brand-modern img {
        height: 50px;
        transition: all var(--transition-base);
    }
    
    .navbar-modern.scrolled .navbar-brand-modern img {
        height: 40px;
    }
    
    .nav-link-modern {
        position: relative;
        color: var(--dark) !important;
        font-weight: 600;
        padding: 0.75rem 1.25rem !important;
        transition: color var(--transition-fast);
    }
    
    .nav-link-modern::after {
        content: '';
        position: absolute;
        bottom: 0.5rem;
        left: 50%;
        transform: translateX(-50%) scaleX(0);
        width: 80%;
        height: 3px;
        background: var(--gradient-primary);
        border-radius: var(--radius-full);
        transition: transform var(--transition-base);
    }
    
    .nav-link-modern:hover::after,
    .nav-link-modern.active::after {
        transform: translateX(-50%) scaleX(1);
    }
    
    .nav-link-modern:hover {
        color: var(--primary) !important;
    }
    
    /* Cart Badge Modern */
    .cart-icon-modern {
        position: relative;
        padding: 0.75rem 1rem;
        border-radius: var(--radius-full);
        background: var(--gradient-primary);
        color: var(--light);
        transition: all var(--transition-base);
        text-decoration: none;
    }
    
    .cart-icon-modern:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-glow);
        color: var(--light);
    }
    
    .cart-badge-modern {
        position: absolute;
        top: -8px;
        right: -8px;
        background: var(--accent);
        color: var(--light);
        border-radius: var(--radius-full);
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 700;
        min-width: 24px;
        text-align: center;
        animation: pulse 2s ease-in-out infinite;
    }
    
    /* User Dropdown Modern */
    .user-dropdown-modern {
        padding: 0.5rem 1rem;
        border-radius: var(--radius-full);
        background: var(--light-95);
        transition: all var(--transition-fast);
        cursor: pointer;
        color: var(--dark);
    }
    
    .user-dropdown-modern:hover {
        background: var(--light-90);
        transform: translateY(-2px);
    }
    
    .dropdown-menu-modern {
        border: none;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xl);
        padding: 0.5rem;
        margin-top: 0.5rem !important;
    }
    
    .dropdown-item-modern {
        padding: 0.75rem 1rem;
        border-radius: var(--radius-md);
        transition: all var(--transition-fast);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .dropdown-item-modern:hover {
        background: var(--light-95);
        transform: translateX(4px);
    }
    
    /* Mobile Menu */
    .navbar-toggler-modern {
        border: none;
        padding: 0.5rem;
    }
    
    .navbar-toggler-modern:focus {
        box-shadow: none;
    }
    
    .navbar-toggler-icon-modern {
        width: 30px;
        height: 2px;
        background: var(--dark);
        display: block;
        transition: all var(--transition-base);
        position: relative;
    }
    
    .navbar-toggler-icon-modern::before,
    .navbar-toggler-icon-modern::after {
        content: '';
        width: 30px;
        height: 2px;
        background: var(--dark);
        position: absolute;
        left: 0;
        transition: all var(--transition-base);
    }
    
    .navbar-toggler-icon-modern::before {
        top: -8px;
    }
    
    .navbar-toggler-icon-modern::after {
        top: 8px;
    }
    
    @media (max-width: 991px) {
        .navbar-collapse {
            margin-top: 1rem;
            padding: 1rem;
            background: var(--light-95);
            border-radius: var(--radius-lg);
        }
        
        .nav-link-modern::after {
            display: none;
        }
    }
    </style>
</head>

<body data-tema="<?= $siteConfig['tema_activo'] ?? 'modern' ?>">

    <?php
    // Top Alerts (anuncios top)
    $mostrarAnuncios = isset($siteConfig['mostrar_anuncios']) && $siteConfig['mostrar_anuncios'];
    
    if ($mostrarAnuncios) {
        try {
            $anuncios = $configService->getAnuncios('top');
            
            // Debug: Log para verificar
            error_log("Anuncios obtenidos: " . count($anuncios));
            
            if (!empty($anuncios)) {
                foreach ($anuncios as $anuncio):
                    $tipoClase = [
                        'alerta' => 'danger',
                        'info' => 'info',
                        'promocion' => 'success',
                        'evento' => 'warning'
                    ][$anuncio['tipo']] ?? 'info';
    ?>
    <div class="alert alert-<?= $tipoClase ?> mb-0 text-center" 
         data-aos="fade-down" 
         data-aos-duration="800"
         data-aos-once="false"
         style="border-radius: 0; <?= $anuncio['estilo_css'] ?? '' ?>; z-index: 9998; position: relative;">
        <strong><?= htmlspecialchars($anuncio['titulo']) ?></strong>
        <?php if (!empty($anuncio['contenido'])): ?>
        - <?= htmlspecialchars($anuncio['contenido']) ?>
        <?php endif; ?>
    </div>
    <?php
                endforeach;
            } else {
                error_log("No hay anuncios disponibles");
            }
        } catch (Exception $e) {
            error_log("Error al obtener anuncios: " . $e->getMessage());
        }
    }
    ?>

    <?php
    // Countdown de Eventos
    include __DIR__ . '/countdown.php';
    ?>

    <!-- Top Bar Modern -->
    <div class="top-bar-modern d-none d-lg-block">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex gap-4">
                    <a href="tel:012345678" class="text-decoration-none">
                        <i class="fas fa-phone"></i> (01) 234-5678
                    </a>
                    <a href="mailto:contacto@alberco.pe" class="text-decoration-none">
                        <i class="fas fa-envelope"></i> contacto@alberco.pe
                    </a>
                    <span class="text-light-80">
                        <i class="fas fa-clock"></i> Lun-Dom: 11:00 AM - 11:00 PM
                    </span>
                </div>
                <div class="d-flex gap-3">
                    <a href="#" class="text-decoration-none"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="text-decoration-none"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-decoration-none"><i class="fab fa-tiktok fa-lg"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar Premium -->
    <nav class="navbar navbar-expand-lg navbar-modern" id="mainNavbar">
        <div class="container">
            <!-- Logo -->
            <a href="<?php echo $homePath; ?>" class="navbar-brand navbar-brand-modern">
                <img src="<?php echo $basePath; ?>Assets/imagenes/AbercoLogo.png" 
                     alt="Alberco" class="d-inline-block align-top">
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler navbar-toggler-modern" type="button" data-bs-toggle="collapse" 
                    data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false">
                <span class="navbar-toggler-icon-modern"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <!-- Nav Links -->
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" 
                           href="<?php echo $homePath; ?>">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo (basename($_SERVER['PHP_SELF']) == 'menu.php') ? 'active' : ''; ?>" 
                           href="<?php echo $pagePath; ?>menu.php">Carta</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo (basename($_SERVER['PHP_SELF']) == 'promociones.php') ? 'active' : ''; ?>" 
                           href="<?php echo $pagePath; ?>promociones.php">Promociones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo (basename($_SERVER['PHP_SELF']) == 'nosotros.php') ? 'active' : ''; ?>" 
                           href="<?php echo $pagePath; ?>nosotros.php">Nosotros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern <?php echo (basename($_SERVER['PHP_SELF']) == 'contacto.php') ? 'active' : ''; ?>" 
                           href="<?php echo $pagePath; ?>contacto.php">Contacto</a>
                    </li>
                </ul>

                <!-- Right Side Icons -->
                <div class="d-flex align-items-center gap-3">
                    <!-- Cart -->
                    <a href="<?php echo $pagePath; ?>pedido.php" class="cart-icon-modern">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="cartBadge" class="cart-badge-modern">0</span>
                    </a>

                    <!-- User Menu -->
                    <?php
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    
                    $clienteLogueado = isset($_SESSION['cliente_logueado']) && $_SESSION['cliente_logueado'] === true;
                    
                    if ($clienteLogueado):
                        $nombreCliente = $_SESSION['cliente_nombre'] ?? 'Cliente';
                        $puntosCliente = $_SESSION['cliente_puntos'] ?? 0;
                    ?>
                        <!-- Usuario Logueado -->
                        <div class="dropdown">
                            <a class="user-dropdown-modern d-flex align-items-center gap-2 text-decoration-none" 
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle fa-2x"></i>
                                <div class="d-none d-lg-block">
                                    <div class="fw-bold" style="font-size: 0.9rem;"><?= htmlspecialchars($nombreCliente) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--primary);">
                                        <i class="fas fa-coins"></i> <?= number_format($puntosCliente) ?> pts
                                    </div>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-modern">
                                <li>
                                    <a class="dropdown-item dropdown-item-modern" href="<?php echo $pagePath; ?>perfil_cliente.php">
                                        <i class="fas fa-user" style="color: var(--primary);"></i>
                                        Mi Perfil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-modern" href="<?php echo $pagePath; ?>mis_pedidos.php">
                                        <i class="fas fa-history" style="color: var(--secondary);"></i>
                                        Mis Pedidos
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-modern" href="<?php echo $pagePath; ?>mis_puntos.php">
                                        <i class="fas fa-coins" style="color: var(--accent);"></i>
                                        Mis Puntos
                                        <span class="badge" style="background: var(--accent); margin-left: auto;">
                                            <?= $puntosCliente ?>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-modern" href="<?php echo $pagePath; ?>mis_direcciones.php">
                                        <i class="fas fa-map-marker-alt" style="color: var(--primary);"></i>
                                        Mis Direcciones
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item dropdown-item-modern text-danger" href="<?php echo $pagePath; ?>logout_cliente.php">
                                        <i class="fas fa-sign-out-alt"></i>
                                        Cerrar Sesión
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Botón Login -->
                        <a href="<?php echo $pagePath; ?>login_cliente.php" class="btn-modern btn-outline">
                            <i class="fas fa-user"></i>
                            <span class="d-none d-lg-inline">Ingresar</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Navbar Scroll Scripts -->
    <script>
    // Navbar Sticky Effect
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('mainNavbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Init -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    </script>
</body>
</html>
