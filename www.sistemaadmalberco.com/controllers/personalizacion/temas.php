<?php
/**
 * Controlador de Temas Predefinidos
 */

require_once __DIR__ . '/../../models/configuracion_sitio.php';

// La sesión ya fue verificada por sesion.php

$modelConfig = new ConfiguracionSitio();
$mensaje = '';
$tipo_mensaje = '';

// Definición de temas predefinidos
$temas = [
    'navidad' => [
        'nombre' => 'Navidad 🎄',
        'descripcion' => 'Tema festivo navideño con colores rojo, verde y dorado',
        'preview' => 'https://images.unsplash.com/photo-1512389142860-9c449e58a543?w=400',
        'configuraciones' => [
            'tema_activo' => 'navidad',
            'color_primario' => '#c62828',
            'color_secundario' => '#2e7d32',
            'color_acento' => '#ffd700',
            'fondo_hero' => 'https://images.unsplash.com/photo-1512389142860-9c449e58a543?w=1920',
            'efectos_activos' => true,
            'mensaje_bienvenida' => '🎄 ¡Feliz Navidad! Disfruta de nuestras promociones navideñas',
            'fuente_principal' => 'Mountains of Christmas'
        ]
    ],
    'anio_nuevo' => [
        'nombre' => 'Año Nuevo 🎉',
        'descripcion' => 'Tema elegante para celebrar el año nuevo con dorado y negro',
        'preview' => 'https://images.unsplash.com/photo-1467810563316-b5476525c0f9?w=400',
        'configuraciones' => [
            'tema_activo' => 'anio_nuevo',
            'color_primario' => '#ffd700',
            'color_secundario' => '#212121',
            'color_acento' => '#c0c0c0',
            'fondo_hero' => 'https://images.unsplash.com/photo-1467810563316-b5476525c0f9?w=1920',
            'efectos_activos' => true,
            'mensaje_bienvenida' => '🎉 ¡Feliz Año Nuevo! Empieza el año con nuestras ofertas',
            'fuente_principal' => 'Playfair Display'
        ]
    ],
    'san_valentin' => [
        'nombre' => 'San Valentín 💝',
        'descripcion' => 'Tema romántico con tonos rosados y rojos',
        'preview' => 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=400',
        'configuraciones' => [
            'tema_activo' => 'san_valentin',
            'color_primario' => '#e91e63',
            'color_secundario' => '#f06292',
            'color_acento' => '#ff4081',
            'fondo_hero' => 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=1920',
            'efectos_activos' => true,
            'mensaje_bienvenida' => '💝 Celebra el amor con nuestras promociones especiales',
            'fuente_principal' => 'Dancing Script'
        ]
    ],
    'halloween' => [
        'nombre' => 'Halloween 🎃',
        'descripcion' => 'Tema oscuro y misterioso para Halloween',
        'preview' => 'https://images.unsplash.com/photo-1509557965875-b88c97052f0e?w=400',
        'configuraciones' => [
            'tema_activo' => 'halloween',
            'color_primario' => '#ff6f00',
            'color_secundario' => '#212121',
            'color_acento' => '#9c27b0',
            'fondo_hero' => 'https://images.unsplash.com/photo-1509557965875-b88c97052f0e?w=1920',
            'efectos_activos' => true,
            'mensaje_bienvenida' => '🎃 ¡Feliz Halloween! Ofertas de miedo',
            'fuente_principal' => 'Creepster'
        ]
    ],
    'fiestas_patrias' => [
        'nombre' => 'Fiestas Patrias 🇵🇪',
        'descripcion' => 'Tema patriótico con los colores de Perú',
        'preview' => 'https://images.unsplash.com/photo-1531968455001-5c5272a41129?w=400',
        'configuraciones' => [
            'tema_activo' => 'fiestas_patrias',
            'color_primario' => '#d32f2f',
            'color_secundario' => '#ffffff',
            'color_acento' => '#d32f2f',
            'fondo_hero' => 'https://images.unsplash.com/photo-1531968455001-5c5272a41129?w=1920',
            'efectos_activos' => true,
            'mensaje_bienvenida' => '🇵🇪 ¡Viva el Perú! Celebra con nosotros',
            'fuente_principal' => 'Poppins'
        ]
    ],
    'verano' => [
        'nombre' => 'Verano ☀️',
        'descripcion' => 'Tema fresco y colorido para el verano',
        'preview' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400',
        'configuraciones' => [
            'tema_activo' => 'verano',
            'color_primario' => '#00bcd4',
            'color_secundario' => '#ffeb3b',
            'color_acento' => '#ff9800',
            'fondo_hero' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1920',
            'efectos_activos' => true,
            'mensaje_bienvenida' => '☀️ Disfruta del verano con nuestras refrescantes ofertas',
            'fuente_principal' => 'Quicksand'
        ]
    ],
    'default' => [
        'nombre' => 'Tema Original 🏠',
        'descripcion' => 'Tema clásico de Alberco con rojo característico',
        'preview' => '',
        'configuraciones' => [
            'tema_activo' => 'default',
            'color_primario' => '#d32f2f',
            'color_secundario' => '#ffc107',
            'color_acento' => '#43a047',
            'fondo_hero' => '',
            'efectos_activos' => false,
            'mensaje_bienvenida' => 'Bienvenido a Alberco',
            'fuente_principal' => 'Poppins'
        ]
    ]
];

// Procesar aplicación de tema
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'aplicar_tema') {
        $temaId = $_POST['tema_id'] ?? '';
        
        if (isset($temas[$temaId])) {
            $tema = $temas[$temaId];
            $configuraciones = [];
            
            foreach ($tema['configuraciones'] as $clave => $valor) {
                $tipo = is_bool($valor) ? 'booleano' : (is_numeric($valor) ? 'numero' : 'texto');
                $configuraciones[$clave] = [
                    'valor' => $valor,
                    'tipo_dato' => $tipo,
                    'categoria' => 'visual',
                    'descripcion' => ucfirst(str_replace('_', ' ', $clave))
                ];
            }
            
            $resultado = $modelConfig->guardarMultiples($configuraciones, $id_usuario_sesion ?? null);
            
            if ($resultado) {
                $mensaje = "Tema '{$tema['nombre']}' aplicado exitosamente";
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al aplicar el tema';
                $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = 'Tema no encontrado';
            $tipo_mensaje = 'error';
        }
    }
}

// Obtener tema activo actual
$temaActual = $modelConfig->getByClave('tema_activo');
$temaActualId = $temaActual ? $temaActual['valor'] : 'default';
