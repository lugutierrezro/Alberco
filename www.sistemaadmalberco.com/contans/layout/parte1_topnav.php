<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistema Administrativo Pollería Alberco">
    <meta name="author" content="Alberco">
    
    <title>Sistema Alberco - Admin</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo URL_BASE; ?>/assets/public/images/favicon.png">

    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css">
    
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/dist/css/adminlte.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

    <!-- jQuery -->
    <script src="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/plugins/jquery/jquery.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

    <style>
        /* ==============================================
           VARIABLES Y RESET
           ============================================== */
        :root {
            --primary: #FF6B35;
            --secondary: #F7931E;
            --accent: #FFC107;
            --dark: #1a1f36;
            --light: #f8f9fa;
            --success: #1DD1A1;
            --danger: #EE5A6F;
            --warning: #F39C12;
            --info: #3498DB;
            
            --navbar-height: 70px;
            --subnav-height: 50px;
            
            --font-family: 'Inter', sans-serif;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.15);
        }

        * {
            font-family: var(--font-family);
        }

        body {
            background: var(--light);
            padding-top: var(--navbar-height);
            overflow-x: hidden;
        }

        /* Top navbar mejorado con gradientes naranjas */
        .top-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--navbar-height);
            background: linear-gradient(135deg, #FF6B35 0%, #F7931E 50%, #FFC107 100%);
            box-shadow: 0 4px 20px rgba(255, 107, 53, 0.3);
            z-index: 1030;
            display: flex;
            align-items: center;
            padding: 0 1rem;
        }

        @media (min-width: 768px) {
            .top-navbar {
                padding: 0 2rem;
            }
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            margin-right: 1rem;
        }

        @media (min-width: 992px) {
            .navbar-brand {
                gap: 1rem;
                margin-right: 3rem;
            }
        }

        .navbar-logo {
            height: 35px;
            width: auto;
            filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3));
        }

        @media (min-width: 768px) {
            .navbar-logo {
                height: 45px;
            }
        }

        .navbar-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            display: none;
        }

        @media (min-width: 768px) {
            .navbar-title {
                display: block;
                font-size: 1.3rem;
            }
        }

        /* Menu principal - Bootstrap responsive */
        .navbar-menu {
            display: none;
        }

        @media (min-width: 992px) {
            .navbar-menu {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                flex: 1;
            }
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1rem;
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: 8px;
            transition: var(--transition);
            background: rgba(0,0,0,0.1);
        }

        @media (min-width: 1200px) {
            .nav-link {
                padding: 0.75rem 1.25rem;
                font-size: 0.95rem;
            }
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            transform: translateY(-2px);
        }

        .nav-link i {
            font-size: 1.1rem;
        }

        /* Mega Menu - Responsive */
        .mega-menu {
            position: absolute;
            top: calc(100% + 0.5rem);
            left: 0;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            padding: 1rem;
            min-width: 300px;
            display: none;
            z-index: 1000;
        }

        @media (min-width: 768px) {
            .mega-menu {
                padding: 1.5rem;
                min-width: 500px;
            }
        }

        @media (min-width: 992px) {
            .mega-menu {
                min-width: 600px;
            }
        }

        .nav-item:hover .mega-menu {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            animation: fadeInDown 0.3s;
        }

        @media (min-width: 768px) {
            .nav-item:hover .mega-menu {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 992px) {
            .nav-item:hover .mega-menu {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .mega-menu-section {
            padding: 1rem;
        }

        .mega-menu-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            margin-bottom: 0.75rem;
            letter-spacing: 0.5px;
        }

        .mega-menu-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem;
            color: #333;
            text-decoration: none;
            border-radius: 6px;
            transition: var(--transition);
            margin-bottom: 0.25rem;
        }

        .mega-menu-link:hover {
            background: rgba(255,107,53,0.1);
            color: var(--primary);
            transform: translateX(5px);
        }

        .mega-menu-link i {
            width: 20px;
            text-align: center;
            color: var(--primary);
        }

        /* Navbar Right - Actions */
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-left: auto;
        }

        .search-bar {
            display: none;
            position: relative;
        }

        @media (min-width: 768px) {
            .search-bar {
                display: block;
            }
        }

        .search-input {
            width: 300px;
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255,107,53,0.1);
        }

        .search-icon {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
        }

        .action-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: var(--light);
            color: var(--dark);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .action-btn:hover {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
        }

        .action-btn .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: var(--light);
            border-radius: 25px;
            cursor: pointer;
            transition: var(--transition);
        }

        .user-menu:hover {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
            display: none;
        }

        @media (min-width: 768px) {
            .user-name {
                display: block;
            }
        }

        /* Mobile Menu Toggle */
        .mobile-menu-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: none;
            background: var(--light);
            border-radius: 8px;
            cursor: pointer;
            margin-right: 1rem;
        }

        @media (min-width: 992px) {
            .mobile-menu-btn {
                display: none;
            }
        }

        /* ==============================================
           SUB NAVIGATION (TABS)
           ============================================== */
        .sub-navbar {
            position: fixed;
            top: var(--navbar-height);
            left: 0;
            right: 0;
            height: var(--subnav-height);
            background: white;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            padding: 0 2rem;
            gap: 1rem;
            z-index: 1020;
            overflow-x: auto;
        }

        body.has-subnav {
            padding-top: calc(var(--navbar-height) + var(--subnav-height));
        }

        .tab-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            color: #7f8c8d;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            border-bottom: 3px solid transparent;
            transition: var(--transition);
            white-space: nowrap;
        }

        .tab-link:hover {
            color: var(--primary);
        }

        .tab-link.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        /* ==============================================
           MAIN CONTENT AREA
           ============================================== */
        .main-content {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        /* Breadcrumbs */
        .breadcrumbs {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #7f8c8d;
        }

        .breadcrumb-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .breadcrumb-item a {
            color: #7f8c8d;
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: var(--primary);
        }

        .breadcrumb-separator {
            color: #bdc3c7;
        }

        /* ==============================================
           COLLAPSIBLE SECTIONS
           ============================================== */
        .collapsible-section {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: var(--transition);
        }

        .collapsible-section:hover {
            box-shadow: var(--shadow-md);
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem;
            cursor: pointer;
            user-select: none;
            background: linear-gradient(135deg, rgba(255,107,53,0.05), rgba(247,147,30,0.05));
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
        }

        .section-title i {
            color: var(--primary);
        }

        .collapse-icon {
            transition: transform 0.3s;
        }

        .collapsible-section.collapsed .collapse-icon {
            transform: rotate(-90deg);
        }

        .section-content {
            padding: 1.5rem;
            max-height: 1000px;
            overflow: hidden;
            transition: max-height 0.3s ease-out, padding 0.3s;
        }

        .collapsible-section.collapsed .section-content {
            max-height: 0;
            padding-top: 0;
            padding-bottom: 0;
        }

        /* ==============================================
           MOBILE RESPONSIVE
           ============================================== */
        @media (max-width: 991.98px) {
            .top-navbar {
                padding: 0 1rem;
            }

            .navbar-brand {
                margin-right: 1rem;
            }

            .sub-navbar {
                padding: 0 1rem;
            }

            .main-content {
                padding: 0 1rem;
            }

            .mega-menu {
                min-width: 100vw;
                left: 50%;
                transform: translateX(-50%);
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</head>

<body>
    <!-- TOP NAVBAR -->
    <nav class="top-navbar">
        <button class="mobile-menu-btn">
            <i class="fas fa-bars"></i>
        </button>

        <a href="<?php echo URL_BASE; ?>" class="navbar-brand">
            <img src="<?php echo URL_BASE; ?>/assets/public/images/Logo.png" alt="Alberco" class="navbar-logo">
            <span class="navbar-title">ALBERCO</span>
        </a>

        <div class="navbar-menu">
            <div class="nav-item">
                <a href="<?php echo URL_BASE; ?>/" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Inicio</span>
                </a>
            </div>

            <!-- VENTAS -->
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Ventas</span>
                    <i class="fas fa-caret-down"></i>
                </a>
                <div class="mega-menu">
                    <div class="mega-menu-section">
                        <div class="mega-menu-title">Gestión</div>
                        <a href="<?php echo URL_BASE; ?>/views/venta/" class="mega-menu-link">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Ventas</span>
                        </a>
                        <a href="<?php echo URL_BASE; ?>/views/pedidos/" class="mega-menu-link">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Pedidos</span>
                        </a>
                        <a href="<?php echo URL_BASE; ?>/views/pedidos/create.php" class="mega-menu-link">
                            <i class="fas fa-plus-circle"></i>
                            <span>Nuevo Pedido</span>
                        </a>
                    </div>
                    <div class="mega-menu-section">
                        <div class="mega-menu-title">Reportes</div>
                        <a href="<?php echo URL_BASE; ?>/views/reportes/" class="mega-menu-link">
                            <i class="fas fa-chart-pie"></i>
                            <span>Reportes de Ventas</span>
                        </a>
                        <a href="#" class="mega-menu-link">
                            <i class="fas fa-chart-line"></i>
                            <span>Análisis</span>
                        </a>
                    </div>
                    <div class="mega-menu-section">
                        <div class="mega-menu-title">Clientes</div>
                        <a href="<?php echo URL_BASE; ?>/views/clientes/" class="mega-menu-link">
                            <i class="fas fa-users"></i>
                            <span>Ver Clientes</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- RESTAURANTE -->
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-utensils"></i>
                    <span>Restaurante</span>
                    <i class="fas fa-caret-down"></i>
                </a>
                <div class="mega-menu">
                    <div class="mega-menu-section">
                        <div class="mega-menu-title">Mesas</div>
                        <a href="<?php echo URL_BASE; ?>/views/mesas/" class="mega-menu-link">
                            <i class="fas fa-chair"></i>
                            <span>Gestión de Mesas</span>
                        </a>
                        <a href="<?php echo URL_BASE; ?>/views/mesas/create.php" class="mega-menu-link">
                            <i class="fas fa-plus-circle"></i>
                            <span>Nueva Mesa</span>
                        </a>
                    </div>
                    <div class="mega-menu-section">
                        <div class="mega-menu-title">Seguimiento</div>
                        <a href="<?php echo URL_BASE; ?>/views/tracking/" class="mega-menu-link">
                            <i class="fas fa-route"></i>
                            <span>Tracking Pedidos</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- FINANZAS -->
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Finanzas</span>
                    <i class="fas fa-caret-down"></i>
                </a>
                <div class="mega-menu">
                    <div class="mega-menu-section">
                        <div class="mega-menu-title">Caja</div>
                        <a href="<?php echo URL_BASE; ?>/views/caja/" class="mega-menu-link">
                            <i class="fas fa-cash-register"></i>
                            <span>Gestión de Caja</span>
                        </a>
                    </div>
                    <div class="mega-menu-section">
                        <div class="mega-menu-title">Compras</div>
                        <a href="<?php echo URL_BASE; ?>/views/compras/" class="mega-menu-link">
                            <i class="fas fa-cart-plus"></i>
                            <span>Ver Compras</span>
                        </a>
                        <a href="<?php echo URL_BASE; ?>/views/compras/create.php" class="mega-menu-link">
                            <i class="fas fa-plus-circle"></i>
                            <span>Nueva Compra</span>
                        </a>
                        <a href="<?php echo URL_BASE; ?>/views/proveedores/" class="mega-menu-link">
                            <i class="fas fa-truck"></i>
                            <span>Proveedores</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- INVENTARIO -->
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-boxes"></i>
                    <span>Inventario</span>
                    <i class="fas fa-caret-down"></i>
                </a>
                <div class="mega-menu">
                    <div class="mega-menu-section">
                        <div class="mega-menu-title">Almacén</div>
                        <a href="<?php echo URL_BASE; ?>/views/almacen/" class="mega-menu-link">
                            <i class="fas fa-warehouse"></i>
                            <span>Productos</span>
                        </a>
                        <a href="<?php echo URL_BASE; ?>/views/almacen/create.php" class="mega-menu-link">
                            <i class="fas fa-plus-circle"></i>
                            <span>Nuevo Producto</span>
                        </a>
                    </div>
                    <div class="mega-menu-section">
                        <div class="mega-menu-title">Organización</div>
                        <a href="<?php echo URL_BASE; ?>/views/categorias/" class="mega-menu-link">
                            <i class="fas fa-tags"></i>
                            <span>Categorías</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- USUARIOS -->
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-user-shield"></i>
                    <span>Usuarios</span>
                    <i class="fas fa-caret-down"></i>
                </a>
                <div class="mega-menu">
                    <div class="mega-menu-section">
                        <div class="mega-menu-title">Gestión</div>
                        <a href="<?php echo URL_BASE; ?>/views/usuarios/" class="mega-menu-link">
                            <i class="fas fa-users-cog"></i>
                            <span>Usuarios</span>
                        </a>
                        <a href="<?php echo URL_BASE; ?>/views/empleado/" class="mega-menu-link">
                            <i class="fas fa-id-badge"></i>
                            <span>Empleados</span>
                        </a>
                        <a href="<?php echo URL_BASE; ?>/views/roles/" class="mega-menu-link">
                            <i class="fas fa-user-tag"></i>
                            <span>Roles</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- CONFIGURACIÓN -->
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Configuración</span>
                    <i class="fas fa-caret-down"></i>
                </a>
                <div class="mega-menu">
                    <div class="mega-menu-section">
                        <div class="mega-menu-title">Sitio Web</div>
                        <a href="<?php echo URL_BASE; ?>/views/personalizacion/index.php" class="mega-menu-link">
                            <i class="fas fa-palette"></i>
                            <span>Personalización</span>
                        </a>
                        <a href="<?php echo URL_BASE; ?>/views/personalizacion/anuncios.php" class="mega-menu-link">
                            <i class="fas fa-bullhorn"></i>
                            <span>Anuncios</span>
                        </a>
                        <a href="<?php echo URL_BASE; ?>/views/personalizacion/eventos.php" class="mega-menu-link">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Eventos</span>
                        </a>
                    </div>
                    <div class="mega-menu-section">
                        <div class="mega-menu-title">Sistema</div>
                        <a href="<?php echo URL_BASE; ?>/views/logs/historial.php" class="mega-menu-link">
                            <i class="fas fa-history"></i>
                            <span>Historial</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="navbar-actions">
            <div class="search-bar">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Buscar..." id="globalSearch">
            </div>

            <!-- Notificaciones Dropdown -->
            <div class="dropdown">
                <button class="action-btn dropdown-toggle" data-toggle="dropdown" id="notifBtn">
                    <i class="fas fa-bell"></i>
                    <span class="badge" id="notifBadge" style="display: none;">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right notify-dropdown" style="min-width: 320px; max-height: 400px; overflow-y: auto;">
                    <h6 class="dropdown-header">
                        <i class="fas fa-bell mr-2"></i> Notificaciones
                    </h6>
                    <div class="dropdown-divider"></div>
                    <div id="notifContainer">
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-spinner fa-spin"></i> Cargando...
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-footer">Ver todas las notificaciones</a>
                </div>
            </div>

            <!-- User Menu Dropdown -->
            <div class="dropdown">
                <div class="user-menu dropdown-toggle" data-toggle="dropdown" style="cursor: pointer;">
                    <div class="user-avatar">
                        <?php echo isset($nombre_sesion) ? strtoupper(substr($nombre_sesion, 0, 1)) : 'U'; ?>
                    </div>
                    <span class="user-name"><?php echo isset($nombre_sesion) ? htmlspecialchars($nombre_sesion) : 'Usuario'; ?></span>
                    <i class="fas fa-chevron-down ml-2"></i>
                </div>
                <div class="dropdown-menu dropdown-menu-right" style="min-width: 200px;">
                    <h6 class="dropdown-header">
                        <i class="fas fa-user-circle mr-2"></i> <?php echo isset($nombre_sesion) ? htmlspecialchars($nombre_sesion) : 'Usuario'; ?>
                    </h6>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?php echo URL_BASE; ?>/views/usuarios/perfil.php">
                        <i class="fas fa-user mr-2"></i> Mi Perfil
                    </a>
                    <a class="dropdown-item" href="<?php echo URL_BASE; ?>/views/configuracion/">
                        <i class="fas fa-cog mr-2"></i> Configuración
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="<?php echo URL_BASE; ?>/controllers/auth/logout.php" onclick="return confirm('¿Cerrar sesión?');">
                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <style>
        /* Dropdown de notificaciones */
        .notify-dropdown .dropdown-item {
            padding: 0.75rem 1rem;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }

        .notify-dropdown .dropdown-item:hover {
            border-left-color: #FF6B35;
            background: rgba(255,107,53,0.05);
        }

        .notify-dropdown .notify-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FF6B35, #F7931E);
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
        }

        .notify-text {
            flex: 1;
        }

        .notify-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }

        .notify-time {
            font-size: 0.8rem;
            color: #95a5a6;
        }
    </style>

    <!-- Aquí continúa el contenido de cada página -->
