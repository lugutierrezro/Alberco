<?php
/**
 * Test de Anuncios
 * Verificar que los anuncios se muestren correctamente
 */

// Incluir configuración
require_once __DIR__ . '/Services/configuracion_service.php';
require_once __DIR__ . '/app/init.php';

$configService = getConfiguracionService();

echo "<h1>Debug de Anuncios</h1>";
echo "<hr>";

// 1. Verificar configuración
echo "<h2>1. Configuración</h2>";
$siteConfig = $configService->getConfiguraciones();
echo "<p>Mostrar anuncios: " . ($siteConfig['mostrar_anuncios'] ? 'SÍ' : 'NO') . "</p>";

// 2. Obtener anuncios de la BD
echo "<h2>2. Anuncios en Base de Datos</h2>";
require_once __DIR__ . '/../www.sistemaadmalberco.com/models/anuncio.php';
$anuncioModel = new Anuncio();

echo "<h3>Todos los anuncios:</h3>";
$todosAnuncios = $anuncioModel->getAll(true);
echo "<p>Total: " . count($todosAnuncios) . "</p>";
echo "<pre>";
print_r($todosAnuncios);
echo "</pre>";

echo "<h3>Anuncios activos:</h3>";
$activosAnuncios = $anuncioModel->getActivos();
echo "<p>Total activos: " . count($activosAnuncios) . "</p>";
echo "<pre>";
print_r($activosAnuncios);
echo "</pre>";

echo "<h3>Anuncios posición 'top':</h3>";
$topAnuncios = $anuncioModel->getByPosicion('top');
echo "<p>Total en 'top': " . count($topAnuncios) . "</p>";
echo "<pre>";
print_r($topAnuncios);
echo "</pre>";

// 3. Obtener desde servicio
echo "<h2>3. Anuncios desde Servicio</h2>";
$anunciosServicio = $configService->getAnuncios('top');
echo "<p>Total desde servicio: " . count($anunciosServicio) . "</p>";
echo "<pre>";
print_r($anunciosServicio);
echo "</pre>";

// 4. Verificar tabla
echo "<h2>4. Estructura de Tabla</h2>";
try {
    require_once __DIR__ . '/../www.sistemaadmalberco.com/services/database/config.php';
    $sql = "SHOW COLUMNS FROM tb_anuncios_sitio";
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
} catch(Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        background: #f5f5f5;
    }
    h1 {
        color: #d32f2f;
    }
    h2 {
        color: #1976d2;
        margin-top: 30px;
    }
    pre {
        background: white;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        overflow-x: auto;
    }
</style>
