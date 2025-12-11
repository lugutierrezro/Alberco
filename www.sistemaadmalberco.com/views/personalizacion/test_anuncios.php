<?php
// Habilitar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico de Anuncios</h1>";

// 1. Verificar inclusión de config
echo "<h2>1. Cargando config.php...</h2>";
try {
    include('../../services/database/config.php');
    echo "✅ config.php cargado<br>";
    echo "PDO: " . (isset($pdo) ? "Conectado" : "No conectado") . "<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// 2. Verificar sesion.php
echo "<h2>2. Cargando sesion.php...</h2>";
try {
    include('../../contans/layout/sesion.php');
    echo "✅ sesion.php cargado<br>";
    echo "ID Usuario: " . ($id_usuario_sesion ?? 'No definido') . "<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// 3. Verificar modelo
echo "<h2>3. Cargando modelo Anuncio...</h2>";
try {
    require_once __DIR__ . '/../../models/anuncio.php';
    echo "✅ Modelo Anuncio cargado<br>";
    
    $model = new Anuncio();
    echo "✅ Instancia de Anuncio creada<br>";
    
    $estadisticas = $model->getEstadisticas();
    echo "✅ Estadísticas obtenidas: <pre>" . print_r($estadisticas, true) . "</pre>";
    
    $anuncios = $model->getAll();
    echo "✅ Anuncios encontrados: " . count($anuncios) . "<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

// 4. Verificar controlador
echo "<h2>4. Cargando controlador...</h2>";
try {
    require_once __DIR__ . '/../../controllers/personalizacion/anuncios.php';
    echo "✅ Controlador cargado<br>";
    echo "Acción: " . ($accion ?? 'No definida') . "<br>";
    echo "Mensaje: " . ($mensaje ?? 'No hay mensaje') . "<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr><p><a href='anuncios.php'>Ir a Anuncios</a></p>";
?>
