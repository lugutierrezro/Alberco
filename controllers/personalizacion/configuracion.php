<?php
/**
 * Controlador de Configuración del Sitio
 */

require_once __DIR__ . '/../../models/configuracion_sitio.php';

// La sesión ya fue verificada por sesion.php
// Usar las variables globales de sesión

$model = new ConfiguracionSitio();
$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'guardar_configuraciones') {
        $configuraciones = [];
        
        // Configuraciones generales
        if (isset($_POST['configs'])) {
            foreach ($_POST['configs'] as $clave => $valor) {
                $configuraciones[$clave] = [
                    'valor' => $valor,
                    'tipo_dato' => $_POST['tipos'][$clave] ?? 'texto',
                    'categoria' => $_POST['categorias'][$clave] ?? 'general',
                    'descripcion' => $_POST['descripciones'][$clave] ?? ''
                ];
            }
        }
        
        $resultado = $model->guardarMultiples($configuraciones, $_SESSION['usuario_id']);
        
        if ($resultado) {
            $mensaje = 'Configuraciones guardadas exitosamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al guardar las configuraciones';
            $tipo_mensaje = 'error';
        }
    }
}

// Obtener todas las configuraciones agrupadas por categoría
$todasConfigs = $model->getAll();
$configsPorCategoria = [];

foreach ($todasConfigs as $config) {
    $categoria = $config['categoria'] ?? 'general';
    if (!isset($configsPorCategoria[$categoria])) {
        $configsPorCategoria[$categoria] = [];
    }
    $configsPorCategoria[$categoria][] = $config;
}

// Obtener categorías
$categorias = $model->getCategorias();
if (empty($categorias)) {
    $categorias = ['general', 'visual', 'contacto', 'redes_sociales'];
}
