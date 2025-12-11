<?php
/**
 * Servicio de Configuración del Sitio
 * Proporciona las configuraciones dinámicas a www.alberco.com
 */

// Incluir modelos del sistema administrativo
require_once __DIR__ . '/../../www.sistemaadmalberco.com/models/configuracion_sitio.php';
require_once __DIR__ . '/../../www.sistemaadmalberco.com/models/anuncio.php';
require_once __DIR__ . '/../../www.sistemaadmalberco.com/models/evento.php';

class ConfiguracionService {
    private static $instance = null;
    private $cache = [];
    private $cacheTime = 300; // 5 minutos
    
    private function __construct() {
        // Singleton
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtener todas las configuraciones
     */
    public function getConfiguraciones() {
        $cacheKey = 'configuraciones_all';
        
        if ($this->isCacheValid($cacheKey)) {
            return $this->cache[$cacheKey]['data'];
        }
        
        $model = new ConfiguracionSitio();
        $configs = $model->getAllAsArray();
        
        // Agregar configuraciones por defecto si no existen
        $defaults = $this->getDefaults();
        $configs = array_merge($defaults, $configs);
        
        $this->setCache($cacheKey, $configs);
        return $configs;
    }
    
    /**
     * Obtener configuración específica
     */
    public function get($clave, $default = null) {
        $configs = $this->getConfiguraciones();
        return $configs[$clave] ?? $default;
    }
    
    /**
     * Obtener anuncios activos
     */
    public function getAnuncios($posicion = null) {
        $cacheKey = 'anuncios_' . ($posicion ?? 'all');
        
        if ($this->isCacheValid($cacheKey)) {
            return $this->cache[$cacheKey]['data'];
        }
        
        $model = new Anuncio();
        $anuncios = $posicion 
            ? $model->getByPosicion($posicion) 
            : $model->getActivos();
        
        $this->setCache($cacheKey, $anuncios);
        return $anuncios;
    }
    
    /**
     * Obtener evento activo con temporizador
     */
    public function getEventoActivo() {
        $cacheKey = 'evento_activo';
        
        if ($this->isCacheValid($cacheKey)) {
            return $this->cache[$cacheKey]['data'];
        }
        
        $model = new Evento();
        $evento = $model->getProximo();
        
        $this->setCache($cacheKey, $evento);
        return $evento;
    }
    
    /**
     * Limpiar caché
     */
    public function clearCache() {
        $this->cache = [];
    }
    
    /**
     * Verificar si el caché es válido
     */
    private function isCacheValid($key) {
        if (!isset($this->cache[$key])) {
            return false;
        }
        
        $cacheData = $this->cache[$key];
        return (time() - $cacheData['time']) < $this->cacheTime;
    }
    
    /**
     * Guardar en caché
     */
    private function setCache($key, $data) {
        $this->cache[$key] = [
            'data' => $data,
            'time' => time()
        ];
    }
    
    /**
     * Configuraciones por defecto
     */
    private function getDefaults() {
        return [
            'tema_activo' => 'default',
            'color_primario' => '#d32f2f',
            'color_secundario' => '#ffc107',
            'color_acento' => '#43a047',
            'fondo_hero' => '',
            'efectos_activos' => false,
            'modo_oscuro' => false,
            'fuente_principal' => 'Poppins',
            'animaciones_habilitadas' => true,
            'mensaje_bienvenida' => 'Bienvenido a Alberco',
            'horario_atencion' => 'Lun-Dom: 11:00 AM - 11:00 PM',
            'telefono_contacto' => '(01) 234-5678',
            'email_contacto' => 'contacto@alberco.pe',
            'mostrar_anuncios' => true,
            'mostrar_temporizador' => true
        ];
    }
    
    /**
     * Generar CSS dinámico basado en configuraciones
     */
    public function generarCSSDinamico() {
        $configs = $this->getConfiguraciones();
        
        $css = ":root {\n";
        $css .= "    --primary-color: {$configs['color_primario']};\n";
        $css .= "    --secondary-color: {$configs['color_secundario']};\n";
        $css .= "    --accent-color: {$configs['color_acento']};\n";
        $css .= "    --font-main: '{$configs['fuente_principal']}', sans-serif;\n";
        $css .= "}\n\n";
        
        // Aplicar colores a elementos específicos del sitio
        $css .= "/* Tema Dinámico Aplicado */\n";
        
        // Navbar
        $css .= ".navbar {\n";
        $css .= "    background: linear-gradient(90deg, {$configs['color_primario']} 0%, {$configs['color_secundario']} 70%, {$configs['color_acento']} 100%) !important;\n";
        $css .= "}\n\n";
        
        // Top bar
        $css .= ".top-bar {\n";
        $css .= "    background: {$configs['color_secundario']} !important;\n";
        $css .= "}\n\n";
        
        $css .= ".top-bar .text-danger, .top-bar .text-success {\n";
        $css .= "    color: {$configs['color_primario']} !important;\n";
        $css .= "}\n\n";
        
        // Botones
        $css .= ".btn-primary {\n";
        $css .= "    background-color: {$configs['color_primario']} !important;\n";
        $css .= "    border-color: {$configs['color_primario']} !important;\n";
        $css .= "}\n\n";
        
        $css .= ".btn-primary:hover {\n";
        $css .= "    background-color: color-mix(in srgb, {$configs['color_primario']} 80%, black) !important;\n";
        $css .= "}\n\n";
        
        // Enlaces activos
        $css .= ".nav-link.active {\n";
        $css .= "    color: {$configs['color_primario']} !important;\n";
        $css .= "    border-bottom-color: {$configs['color_primario']} !important;\n";
        $css .= "}\n\n";
        
        // Badges y elementos destacados
        $css .= ".badge-primary, .bg-primary {\n";
        $css .= "    background-color: {$configs['color_primario']} !important;\n";
        $css .= "}\n\n";
        
        $css .= ".text-primary {\n";
        $css .= "    color: {$configs['color_primario']} !important;\n";
        $css .= "}\n\n";
        
        // Fondo del hero
        if (!empty($configs['fondo_hero'])) {
            $css .= ".hero, .hero-section, .banner-principal {\n";
            $css .= "    background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('{$configs['fondo_hero']}') !important;\n";
            $css .= "    background-size: cover !important;\n";
            $css .= "    background-position: center !important;\n";
            $css .= "}\n\n";
        }
        
        // Fuente principal
        $css .= "body, h1, h2, h3, h4, h5, h6 {\n";
        $css .= "    font-family: var(--font-main) !important;\n";
        $css .= "}\n\n";
        
        // Modo oscuro
        if ($configs['modo_oscuro']) {
            $css .= "body {\n";
            $css .= "    background-color: #1a1a1a !important;\n";
            $css .= "    color: #f5f5f5 !important;\n";
            $css .= "}\n\n";
            
            $css .= ".card, .bg-white {\n";
            $css .= "    background-color: #2d2d2d !important;\n";
            $css .= "    color: #f5f5f5 !important;\n";
            $css .= "}\n";
        }
        
        return $css;
    }
}

/**
 * Helper function para obtener el servicio
 */
function getConfiguracionService() {
    return ConfiguracionService::getInstance();
}
