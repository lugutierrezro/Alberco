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

    <!-- Google Font: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
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

    <!-- Custom Components CSS -->
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/assets/public/css/components.css">

    <style>
        /* ==============================================
           VARIABLES CSS PROFESIONALES
           ============================================== */
        :root {
            /* Colores principales */
            --primary-orange: #FF6B35;
            --secondary-orange: #F7931E;
            --accent-yellow: #FFC107;
            --danger-red: #EE5A6F;
            --success-green: #1DD1A1;
            --info-blue: #3498DB;
            --warning-amber: #F39C12;
            
            /* Colores de fondo */
            --sidebar-dark: #1a2332;
            --sidebar-light: #212d40;
            --content-bg: #f4f6f9;
            --card-bg: #ffffff;
            
            /* Sombras */
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.15);
            --shadow-xl: 0 12px 40px rgba(0,0,0,0.2);
            --shadow-glow: 0 0 20px rgba(255, 107, 53, 0.4);
            
            /* Tipografía */
            --font-family: 'Poppins', sans-serif;
            --font-weight-light: 300;
            --font-weight-normal: 400;
            --font-weight-medium: 500;
            --font-weight-semibold: 600;
            --font-weight-bold: 700;
            
            /* Transiciones */
            --transition-fast: 0.15s;
            --transition-normal: 0.3s;
            --transition-slow: 0.5s;
            --ease-smooth: cubic-bezier(0.4, 0, 0.2, 1);
            
            /* Border radius */
            --radius-sm: 6px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --radius-full: 9999px;
        }

        * {
            font-family: var(--font-family);
        }

        body {
            background: var(--content-bg);
        }

        /* ==============================================
           NAVBAR SUPERIOR - GLASSMORPHISM
           ============================================== */
        .main-header.navbar {
            background: rgba(255, 107, 53, 0.95) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 2px solid rgba(255, 107, 53, 0.3);
            box-shadow: 0 4px 20px rgba(255, 107, 53, 0.2);
            min-height: 60px;
            z-index: 1030;
        }

        .main-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: linear-gradient(135deg, 
                rgba(255, 107, 53, 0.1) 0%, 
                rgba(247, 147, 30, 0.1) 50%, 
                rgba(255, 193, 7, 0.1) 100%);
            pointer-events: none;
        }

        .main-header .nav-link {
            color: #ffffff !important;
            font-weight: var(--font-weight-semibold);
            transition: all var(--transition-normal) var(--ease-smooth);
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            position: relative;
            overflow: hidden;
        }

        .main-header .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.2);
            transition: left 0.3s;
        }

        .main-header .nav-link:hover::before {
            left: 100%;
        }

        .main-header .nav-link:hover {
            transform: translateY(-2px);
            background: rgba(255,255,255,0.15);
        }

        .navbar-badge {
            background: linear-gradient(135deg, #EE5A6F 0%, #ff4444 100%) !important;
            color: #fff !important;
            font-weight: var(--font-weight-bold);
            box-shadow: 0 2px 8px rgba(238, 90, 111, 0.5);
            animation: pulse-badge 2s infinite;
            border-radius: var(--radius-full);
        }

        @keyframes pulse-badge {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* ==============================================
           DROPDOWN MENUS MEJORADOS
           ============================================== */
        .dropdown-menu {
            border-radius: var(--radius-md);
            border: none;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.98);
        }

        .dropdown-item {
            transition: all var(--transition-fast) var(--ease-smooth);
            padding: 0.7rem 1.2rem;
            font-weight: var(--font-weight-medium);
        }

        .dropdown-item:hover {
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%);
            color: #fff !important;
            transform: translateX(5px);
        }

        .dropdown-header {
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%);
            color: #ffffff;
            font-weight: var(--font-weight-bold);
            padding: 1rem 1.2rem;
            border-bottom: 2px solid rgba(255,255,255,0.2);
        }

        .dropdown-footer {
            background: #f8f9fa;
            font-weight: var(--font-weight-semibold);
            text-align: center;
            color: var(--primary-orange) !important;
            transition: all var(--transition-normal);
        }

        .dropdown-footer:hover {
            background: var(--primary-orange) !important;
            color: #fff !important;
        }

        /* ==============================================
           SIDEBAR - REDISEÑO PROFESIONAL
           ============================================== */
        .main-sidebar {
            background: linear-gradient(180deg, var(--sidebar-dark) 0%, var(--sidebar-light) 100%) !important;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
        }

        /* Logo con efecto */
        .brand-link {
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%) !important;
            border-bottom: 3px solid rgba(255,255,255,0.2);
            transition: all var(--transition-normal) var(--ease-smooth);
            padding: 1rem;
            min-height: 70px;
            position: relative;
            overflow: hidden;
        }

        .brand-link::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .brand-link:hover::after {
            left: 100%;
        }

        .brand-link:hover {
            transform: scale(1.02);
        }

        .brand-logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .brand-logo {
            max-width: 100%;
            max-height: 50px;
            height: auto;
            filter: drop-shadow(2px 4px 6px rgba(0,0,0,0.3));
            transition: transform var(--transition-normal);
        }

        .brand-link:hover .brand-logo {
            transform: scale(1.05) rotate(2deg);
        }

        /* Panel de usuario mejorado */
        .user-panel {
            border-bottom: 2px solid rgba(255, 107, 53, 0.2);
            padding: 1.2rem 1rem !important;
            background: rgba(0,0,0,0.1);
        }

        .user-panel .info a {
            color: #ffffff !important;
            font-weight: var(--font-weight-semibold);
            transition: all var(--transition-normal);
            font-size: 1rem;
        }

        .user-panel .info a:hover {
            color: var(--accent-yellow) !important;
            transform: translateX(3px);
        }

        .user-panel small {
            color: #95a5a6;
            font-weight: var(--font-weight-normal);
        }

        /* ==============================================
           MENÚ DE NAVEGACIÓN - ULTRA MODERNO
           ============================================== */
        .nav-sidebar .nav-item > .nav-link {
            color: #ecf0f1;
            transition: all var(--transition-normal) var(--ease-smooth);
            border-radius: var(--radius-md);
            margin: 0.3rem 0.7rem;
            font-weight: var(--font-weight-medium);
            padding: 0.85rem 1.2rem;
            font-size: 0.95rem;
            position: relative;
            overflow: hidden;
        }

        .nav-sidebar .nav-item > .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: var(--accent-yellow);
            transform: scaleY(0);
            transition: transform var(--transition-normal);
        }

        .nav-sidebar .nav-item > .nav-link:hover {
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%) !important;
            color: #ffffff !important;
            transform: translateX(8px);
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4);
        }

        .nav-sidebar .nav-item > .nav-link:hover::before,
        .nav-sidebar .nav-item > .nav-link.active::before {
            transform: scaleY(1);
        }

        .nav-sidebar .nav-item > .nav-link.active {
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.5);
            font-weight: var(--font-weight-bold);
        }

        .nav-sidebar .nav-item > .nav-link i.nav-icon {
            color: var(--accent-yellow);
            transition: all var(--transition-normal);
            width: 1.8rem;
            text-align: center;
            font-size: 1.1rem;
        }

        .nav-sidebar .nav-item > .nav-link:hover i.nav-icon,
        .nav-sidebar .nav-item > .nav-link.active i.nav-icon {
            color: #ffffff;
            transform: scale(1.15) rotate(5deg);
        }

        /* Submenús con efecto de cristal */
        .nav-treeview {
            background: rgba(0, 0, 0, 0.2);
            border-radius: var(--radius-md);
            margin: 0.2rem 0.7rem;
            padding: 0.5rem 0;
            backdrop-filter: blur(8px);
        }

        .nav-treeview > .nav-item > .nav-link {
            color: #bdc3c7;
            padding-left: 3rem;
            font-size: 0.9rem;
        }

        .nav-treeview > .nav-item > .nav-link:hover {
            background: linear-gradient(135deg, var(--secondary-orange) 0%, var(--accent-yellow) 100%) !important;
            color: #ffffff !important;
        }

        .nav-treeview .nav-icon {
            color: var(--accent-yellow) !important;
            font-size: 0.6rem;
        }

        /* Badges mejorados */
        .badge-success {
            background: linear-gradient(135deg, var(--success-green) 0%, #10AC84 100%) !important;
            box-shadow: 0 2px 8px rgba(29, 209, 161, 0.3);
        }

        .badge-warning {
            background: linear-gradient(135deg, var(--accent-yellow) 0%, var(--warning-amber) 100%) !important;
            color: #333 !important;
            box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
        }

        /* Botón de Cerrar Sesión destacado */
        .nav-item-logout > .nav-link {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%) !important;
            color: #ffffff !important;
            font-weight: var(--font-weight-bold);
            margin-top: 2rem !important;
            border: 2px solid rgba(255,255,255,0.1);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }

        .nav-item-logout > .nav-link:hover {
            background: linear-gradient(135deg, #c0392b 0%, #a93226 100%) !important;
            transform: scale(1.03) !important;
            box-shadow: 0 8px 20px rgba(231, 76, 60, 0.6) !important;
        }

        /* ==============================================
           ANIMACIONES AVANZADAS
           ============================================== */
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .nav-sidebar > .nav-item {
            animation: slideInLeft 0.5s var(--ease-smooth) forwards;
            opacity: 0;
        }

        .nav-sidebar > .nav-item:nth-child(1) { animation-delay: 0.05s; }
        .nav-sidebar > .nav-item:nth-child(2) { animation-delay: 0.1s; }
        .nav-sidebar > .nav-item:nth-child(3) { animation-delay: 0.15s; }
        .nav-sidebar > .nav-item:nth-child(4) { animation-delay: 0.2s; }
        .nav-sidebar > .nav-item:nth-child(5) { animation-delay: 0.25s; }
        .nav-sidebar > .nav-item:nth-child(6) { animation-delay: 0.3s; }
        .nav-sidebar > .nav-item:nth-child(7) { animation-delay: 0.35s; }
        .nav-sidebar > .nav-item:nth-child(8) { animation-delay: 0.4s; }
        .nav-sidebar > .nav-item:nth-child(9) { animation-delay: 0.45s; }
        .nav-sidebar > .nav-item:nth-child(10) { animation-delay: 0.5s; }
        .nav-sidebar > .nav-item:nth-child(11) { animation-delay: 0.55s; }
        .nav-sidebar > .nav-item:nth-child(12) { animation-delay: 0.6s; }
        .nav-sidebar > .nav-item:nth-child(13) { animation-delay: 0.65s; }
        .nav-sidebar > .nav-item:nth-child(14) { animation-delay: 0.7s; }

        /* ==============================================
           CARDS MODERNOS PARA CONTENT
           ============================================== */
        .card {
            border-radius: var(--radius-lg);
            border: none;
            box-shadow: var(--shadow-md);
            transition: all var(--transition-normal) var(--ease-smooth);
            overflow: hidden;
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }

        .card-header {
            background: linear-gradient(135deg, rgba(255,107,53,0.1) 0%, rgba(247,147,30,0.1) 100%);
            border-bottom: 2px solid rgba(255,107,53,0.2);
            font-weight: var(--font-weight-semibold);
        }

        /* Small boxes mejoradas */
        .small-box {
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
        }

        .small-box::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            transition: transform 0.6s;
        }

        .small-box:hover::before {
            transform: translate(-25%, -25%);
        }

        .small-box:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .small-box .icon {
            transition: all var(--transition-normal);
        }

        .small-box:hover .icon {
            transform: scale(1.1) rotate(5deg);
        }

        /* Botones mejorados */
        .btn {
            border-radius: var(--radius-md);
            font-weight: var(--font-weight-semibold);
            transition: all var(--transition-normal) var(--ease-smooth);
            border: none;
            box-shadow: var(--shadow-sm);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-green) 0%, #10AC84 100%);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }

        /* ==============================================
           SCROLLBAR PERSONALIZADO
           ============================================== */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.15);
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: var(--primary-orange);
            border-radius: 10px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-orange);
        }

        /* ==============================================
           UTILIDADES DE COLOR
           ============================================== */
        .text-orange { color: var(--primary-orange) !important; }
        .bg-orange { background-color: var(--primary-orange) !important; }
        .text-gradient {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: var(--font-weight-bold);
        }

        /* ==============================================
           RESPONSIVE
           ============================================== */
        @media (max-width: 991.98px) {
            .main-header .nav-link strong {
                display: none;
            }
        }

        @media (max-width: 767.98px) {
            .brand-link {
                padding: 0.6rem;
                min-height: 55px;
            }
            .brand-logo {
                max-height: 40px;
            }
            .nav-sidebar .nav-item > .nav-link {
                padding: 0.7rem 1rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .brand-logo {
                max-height: 35px;
            }
            .nav-sidebar .nav-item > .nav-link {
                padding: 0.6rem 0.8rem;
                font-size: 0.85rem;
            }
        }

        /* Sidebar colapsado */
        .sidebar-mini.sidebar-collapse .brand-link {
            padding: 0.5rem;
        }

        .sidebar-mini.sidebar-collapse .brand-logo {
            max-height: 40px;
            max-width: 45px;
        }

        /* Touch improvements */
        @media (hover: none) and (pointer: coarse) {
            .nav-sidebar .nav-item > .nav-link {
                padding: 1rem 1.2rem;
            }
        }

        /* ==============================================
           MENÚ DE USUARIO INTERACTIVO - RESPONSIVE
           ============================================== */
        .user-menu .nav-link {
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .user-menu .nav-link:hover {
            background-color: rgba(255, 107, 53, 0.1);
            transform: scale(1.1);
        }
        
        .user-menu .nav-link i {
            transition: transform 0.3s ease;
        }
        
        .user-menu:hover .nav-link i {
            transform: rotate(15deg);
        }
        
        /* Dropdown del usuario */
        .user-dropdown {
            min-width: 300px;
            max-width: 95vw;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            border: none;
            margin-top: 0.5rem;
            overflow: hidden;
        }
        
        .user-header {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            color: white;
            padding: 1.5rem 1rem;
            text-align: center;
            position: relative;
        }
        
        .user-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="10" cy="10" r="1" fill="white" opacity="0.1"/></svg>');
            background-size: 30px 30px;
        }
        
        .user-avatar-large {
            position: relative;
            z-index: 1;
        }
        
        .user-details {
            position: relative;
            z-index: 1;
        }
        
        .user-details h5 {
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }
        
        .user-details .text-sm {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.8rem;
            word-break: break-word;
        }
        
        /* Stats rápidos */
        .user-stats {
            background: #f8f9fa;
            padding: 0.75rem !important;
        }
        
        .stat-item {
            padding: 0.5rem;
        }
        
        .stat-item i {
            font-size: 1.3rem;
            margin-bottom: 0.25rem;
        }
        
        .stat-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.65rem;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 0.25rem;
        }
        
        /* Items del menú */
        .user-dropdown .dropdown-item {
            padding: 0.75rem 1.25rem;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-size: 0.9rem;
        }
        
        .user-dropdown .dropdown-item:hover {
            background: linear-gradient(90deg, rgba(255, 107, 53, 0.1), transparent);
            border-left-color: var(--primary-orange);
            transform: translateX(5px);
        }
        
        .user-dropdown .dropdown-item i {
            width: 20px;
            text-align: center;
            font-size: 0.95rem;
        }
        
        .user-dropdown .dropdown-footer {
            background: #fff3f0;
            font-weight: 600;
            border-top: 2px solid #ffe5e0;
        }
        
        .user-dropdown .dropdown-footer:hover {
            background: #ffe5e0;
        }
        
        /* Responsive - Mobile */
        @media (max-width: 576px) {
            .user-dropdown {
                min-width: 280px;
                max-width: 90vw;
                right: -10px !important;
                left: auto !important;
            }
            
            .user-header {
                padding: 1.25rem 0.75rem;
            }
            
            .user-details h5 {
                font-size: 1rem;
            }
            
            .user-details .text-sm {
                font-size: 0.75rem;
            }
            
            .stat-value {
                font-size: 1.1rem;
            }
            
            .stat-label {
                font-size: 0.6rem;
            }
            
            .user-dropdown .dropdown-item {
                padding: 0.65rem 1rem;
                font-size: 0.85rem;
            }
            
            .user-stats {
                padding: 0.5rem !important;
            }
        }
        
        /* Tablet */
        @media (min-width: 577px) and (max-width: 991px) {
            .user-dropdown {
                min-width: 300px;
            }
        }
        
        /* Desktop */
        @media (min-width: 992px) {
            .user-dropdown {
                min-width: 320px;
            }
            
            .user-header {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        
        <!-- NAVBAR SUPERIOR -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Toggle navigation">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="<?php echo URL_BASE; ?>" class="nav-link">
                        <i class="fas fa-drumstick-bite mr-2"></i>
                        <strong>POLLERÍA ALBERCO</strong>
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                
                <!-- NOTIFICACIONES -->
                <?php
                // Inicializar $pdo para notificaciones
                if (!isset($pdo)) {
                    try {
                        $pdo = getDB();
                    } catch (Exception $e) {
                        $pdo = null;
                    }
                }
                
                // Stats para el menú de usuario
                $ventas_hoy = 0;
                $pedidos_activos = 0;
                
                if ($pdo !== null) {
                    try {
                        // Ventas de hoy
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_ventas WHERE DATE(fecha_venta) = CURDATE()");
                        $ventas_hoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                        
                        // Pedidos activos
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_pedidos WHERE id_estado IN (1,2,3) AND estado_registro = 'ACTIVO'");
                        $pedidos_activos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                    } catch (Exception $e) {
                        // Silenciar errores
                    }
                }
                
                $totalNotif = 0;
                $notifsRecientes = [];

                if (isset($_SESSION['id_usuario']) && $pdo !== null) {
                    try {
                        $sqlNotif = "SELECT COUNT(*) as total FROM tb_notificaciones 
                                     WHERE id_usuario_destino = :id_usuario AND leido = 0
                                     AND estado_registro = 'ACTIVO'";
                        $stmtNotif = $pdo->prepare($sqlNotif);
                        $stmtNotif->execute([':id_usuario' => $_SESSION['id_usuario']]);
                        $totalNotif = (int) $stmtNotif->fetch(PDO::FETCH_ASSOC)['total'];

                        $sqlNotifRecientes = "SELECT id_notificacion, tipo, titulo, fecha_notificacion, enlace
                                              FROM tb_notificaciones 
                                              WHERE id_usuario_destino = :id_usuario AND leido = 0 
                                              AND estado_registro = 'ACTIVO'
                                              ORDER BY fecha_notificacion DESC LIMIT 5";
                        $stmtNotifRecientes = $pdo->prepare($sqlNotifRecientes);
                        $stmtNotifRecientes->execute([':id_usuario' => $_SESSION['id_usuario']]);
                        $notifsRecientes = $stmtNotifRecientes->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        error_log("Error notificaciones: " . $e->getMessage());
                    }
                }

                if (!function_exists('tiempoTranscurrido')) {
                    function tiempoTranscurrido($fecha) {
                        $ahora = new DateTime();
                        $fechaNotif = new DateTime($fecha);
                        $diff = $ahora->diff($fechaNotif);
                        if ($diff->d > 0) return $diff->d . 'd';
                        if ($diff->h > 0) return $diff->h . 'h';
                        if ($diff->i > 0) return $diff->i . 'min';
                        return 'ahora';
                    }
                }

                if (!function_exists('iconoNotificacion')) {
                    function iconoNotificacion($tipo) {
                        $iconos = [
                            'alerta_stock'       => 'exclamation-triangle text-danger',
                            'pedido_nuevo'       => 'shopping-cart text-warning',
                            'cambio_estado'      => 'sync-alt text-info',
                            'delivery_asignado'  => 'motorcycle text-primary',
                            'pedido_entregado'   => 'check-circle text-success',
                            'otro'               => 'bell text-secondary',
                        ];
                        return $iconos[$tipo] ?? 'bell text-primary';
                    }
                }
                ?>

                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" aria-label="Notificaciones">
                        <i class="far fa-bell"></i>
                        <?php if ($totalNotif > 0): ?>
                            <span class="badge badge-warning navbar-badge"><?php echo $totalNotif > 99 ? '99+' : $totalNotif; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-header">
                            <i class="fas fa-bell mr-2"></i>
                            <?php echo $totalNotif; ?> Notificación<?php echo $totalNotif !== 1 ? 'es' : ''; ?>
                        </span>
                        <div class="dropdown-divider"></div>

                        <?php if (!empty($notifsRecientes)): ?>
                            <?php foreach ($notifsRecientes as $notif): ?>
                                <a href="<?php echo $notif['enlace'] ?? URL_BASE; ?>" class="dropdown-item">
                                    <i class="fas fa-<?php echo iconoNotificacion($notif['tipo']); ?> mr-2"></i>
                                    <?php echo htmlspecialchars(mb_substr($notif['titulo'], 0, 35)) . (mb_strlen($notif['titulo']) > 35 ? '...' : ''); ?>
                                    <span class="float-right text-muted text-sm">
                                        <?php echo tiempoTranscurrido($notif['fecha_notificacion']); ?>
                                    </span>
                                </a>
                                <div class="dropdown-divider"></div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="dropdown-item text-muted">
                                <i class="fas fa-check-circle mr-2 text-success"></i>
                                No hay notificaciones nuevas
                            </span>
                            <div class="dropdown-divider"></div>
                        <?php endif; ?>

                        <a href="<?php echo URL_BASE; ?>/views/notificaciones/" class="dropdown-item dropdown-footer">
                            <i class="fas fa-eye mr-1"></i> Ver Todas
                        </a>
                    </div>
                </li>

                <!-- MENÚ DE USUARIO MEJORADO -->
                <li class="nav-item dropdown user-menu">
                    <a class="nav-link" data-toggle="dropdown" href="#" aria-label="Perfil de usuario">
                        <i class="fas fa-user-circle fa-2x text-orange"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right user-dropdown">
                        <!-- Header del usuario -->
                        <div class="user-header">
                            <div class="user-avatar-large">
                                <i class="fas fa-user-circle fa-4x text-white"></i>
                            </div>
                            <div class="user-details mt-2">
                                <h5 class="mb-0"><?php echo htmlspecialchars($_SESSION['nombres'] ?? 'Usuario'); ?></h5>
                                <p class="mb-0 text-sm"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                                <span class="badge badge-light mt-1"><?php echo htmlspecialchars($_SESSION['rol'] ?? ''); ?></span>
                            </div>
                        </div>
                        
                        <div class="dropdown-divider"></div>
                        
                        <!-- Stats rápidos -->
                        <div class="user-stats px-3 py-2">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <i class="fas fa-shopping-cart text-success"></i>
                                        <div class="stat-value"><?php echo $ventas_hoy ?? 0; ?></div>
                                        <div class="stat-label">Ventas</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <i class="fas fa-clipboard-list text-warning"></i>
                                        <div class="stat-value"><?php echo $pedidos_activos ?? 0; ?></div>
                                        <div class="stat-label">Pedidos</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <i class="fas fa-bell text-danger"></i>
                                        <div class="stat-value"><?php echo $totalNotif; ?></div>
                                        <div class="stat-label">Alertas</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="dropdown-divider"></div>
                        
                        <!-- Menú de opciones -->
                        <a href="<?php echo URL_BASE; ?>/views/perfil/" class="dropdown-item">
                            <i class="fas fa-user mr-2 text-primary"></i> Mi Perfil
                        </a>
                        <a href="<?php echo URL_BASE; ?>/views/configuracion/" class="dropdown-item">
                            <i class="fas fa-cog mr-2 text-info"></i> Configuración
                        </a>
                        <a href="<?php echo URL_BASE; ?>/views/notificaciones/" class="dropdown-item">
                            <i class="fas fa-bell mr-2 text-warning"></i> 
                            Notificaciones
                            <?php if ($totalNotif > 0): ?>
                                <span class="badge badge-warning float-right"><?php echo $totalNotif; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?php echo URL_BASE; ?>/views/ayuda/" class="dropdown-item">
                            <i class="fas fa-question-circle mr-2 text-success"></i> Ayuda
                        </a>
                        
                        <div class="dropdown-divider"></div>
                        
                        <a href="<?php echo URL_BASE; ?>/controllers/auth/logout.php" class="dropdown-item dropdown-footer text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                        </a>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button" aria-label="Pantalla completa">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- SIDEBAR -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Logo (sin texto, solo imagen) -->
            <a href="<?php echo URL_BASE; ?>" class="brand-link">
                <div class="brand-logo-container">
                    <img src="<?php echo URL_BASE; ?>/assets/public/images/Logo.png" 
                         alt="Logo Alberco" 
                         class="brand-logo">
                </div>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Panel de usuario (sin foto) -->
                <div class="user-panel mt-3 pb-3 mb-3">
                    <div class="info text-center">
                        <a href="<?php echo URL_BASE; ?>/views/perfil/" class="d-block">
                            <i class="fas fa-user-circle mr-2"></i>
                            <?php echo isset($nombre_sesion) ? htmlspecialchars($nombre_sesion) : 'Usuario'; ?>
                        </a>
                        <small class="d-block mt-1" style="color: #95a5a6; font-size: 0.75rem;">
                            <i class="fas fa-shield-alt mr-1"></i>
                            <?php echo isset($rol_sesion) ? htmlspecialchars($rol_sesion) : ''; ?>
                        </small>
                    </div>
                </div>

                <!-- Menú de navegación -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                        <li class="nav-item">
                            <a href="<?php echo URL_BASE; ?>/" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['REQUEST_URI'], '/views/') === false ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo URL_BASE; ?>/views/reportes/" class="nav-link">
                                <i class="nav-icon fas fa-chart-pie"></i>
                                <p>Reportes</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo URL_BASE; ?>/views/venta/" class="nav-link">
                                <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                <p>Facturación / Ventas</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo URL_BASE; ?>/views/caja/" class="nav-link">
                                <i class="nav-icon fas fa-cash-register"></i>
                                <p>Gestión de Caja <span class="badge badge-success right">HOY</span></p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-shopping-cart"></i>
                                <p>Pedidos <i class="fas fa-angle-left right"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?php echo URL_BASE; ?>/views/pedidos/" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Listar Pedidos</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo URL_BASE; ?>/views/pedidos/create.php" class="nav-link">
                                        <i class="far fa-plus-square nav-icon"></i>
                                        <p>Nuevo Pedido</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-chair"></i>
                                <p>Mesas <i class="fas fa-angle-left right"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?php echo URL_BASE; ?>/views/mesas/" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Listar Mesas</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo URL_BASE; ?>/views/mesas/create.php" class="nav-link">
                                        <i class="far fa-plus-square nav-icon"></i>
                                        <p>Nueva Mesa</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo URL_BASE; ?>/views/clientes/" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Clientes</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-warehouse"></i>
                                <p>Almacén <i class="fas fa-angle-left right"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?php echo URL_BASE; ?>/views/almacen/" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Listar Productos</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo URL_BASE; ?>/views/almacen/create.php" class="nav-link">
                                        <i class="far fa-plus-square nav-icon"></i>
                                        <p>Nuevo Producto</p>
                                    </a>
                                </li>
                                <?php if (isset($_SESSION['user_role_id']) && $_SESSION['user_role_id'] == 1): ?>
                                <li class="nav-item">
                                    <a href="<?php echo URL_BASE; ?>/views/almacen/papelera.php" class="nav-link">
                                        <i class="fas fa-trash-restore nav-icon text-warning"></i>
                                        <p>Papelera <span class="badge badge-warning right">Admin</span></p>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo URL_BASE; ?>/views/categorias/" class="nav-link">
                                <i class="nav-icon fas fa-tags"></i>
                                <p>Categorías</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-cart-plus"></i>
                                <p>Compras <i class="fas fa-angle-left right"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?php echo URL_BASE; ?>/views/compras/" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Listar Compras</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo URL_BASE; ?>/views/compras/create.php" class="nav-link">
                                        <i class="far fa-plus-square nav-icon"></i>
                                        <p>Nueva Compra</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo URL_BASE; ?>/views/proveedores/" class="nav-link">
                                <i class="nav-icon fas fa-truck"></i>
                                <p>Proveedores</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-user-shield"></i>
                                <p>Usuarios <i class="fas fa-angle-left right"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?php echo URL_BASE; ?>/views/usuarios/" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Listar Usuarios</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo URL_BASE; ?>/views/usuarios/create.php" class="nav-link">
                                        <i class="far fa-plus-square nav-icon"></i>
                                        <p>Nuevo Usuario</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo URL_BASE; ?>/views/roles/" class="nav-link">
                                <i class="nav-icon fas fa-user-tag"></i>
                                <p>Roles</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo URL_BASE; ?>/views/empleado/" class="nav-link">
                                <i class="nav-icon fas fa-id-badge"></i>
                                <p>Empleados</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?php echo URL_BASE; ?>/views/tracking/" class="nav-link">
                                <i class="nav-icon fas fa-route"></i>
                                <p>Seguimiento</p>
                            </a>
                        </li>

                        <!-- PERSONALIZACIÓN DEL SITIO WEB -->
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-palette"></i>
                                <p>Personalización <i class="fas fa-angle-left right"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?php echo URL_BASE; ?>/views/personalizacion/index.php" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Configuración</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo URL_BASE; ?>/views/personalizacion/temas.php" class="nav-link">
                                        <i class="fas fa-paint-brush nav-icon"></i>
                                        <p>Temas</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo URL_BASE; ?>/views/personalizacion/anuncios.php" class="nav-link">
                                        <i class="fas fa-bullhorn nav-icon"></i>
                                        <p>Anuncios</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo URL_BASE; ?>/views/personalizacion/eventos.php" class="nav-link">
                                        <i class="fas fa-calendar-alt nav-icon"></i>
                                        <p>Eventos</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Historial de Actividad -->
                        <li class="nav-item">
                            <a href="<?php echo URL_BASE; ?>/views/logs/historial.php" class="nav-link">
                                <i class="nav-icon fas fa-history"></i>
                                <p>Historial de Actividad</p>
                            </a>
                        </li>

                        <li class="nav-item nav-item-logout">
                            <a href="<?php echo URL_BASE; ?>/controllers/auth/logout.php" class="nav-link" 
                               onclick="return confirm('¿Estás seguro de cerrar sesión?');">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Cerrar Sesión</p>
                            </a>
                        </li>

                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Aquí continúa el content-wrapper -->
