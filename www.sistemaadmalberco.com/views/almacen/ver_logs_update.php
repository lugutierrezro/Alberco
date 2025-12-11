<?php
/**
 * Visor de Logs de Actualización
 * Muestra los últimos logs del proceso de actualización de productos
 */

include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Buscar archivo de log de PHP
$logFiles = [
    'C:/xampp/php/logs/php_error_log',
    'C:/xampp/apache/logs/error.log',
    'C:/xampp/logs/php_error_log',
    dirname(__DIR__, 2) . '/logs/error.log'
];

$logContent = '';
$logFile = '';

foreach ($logFiles as $file) {
    if (file_exists($file)) {
        $logFile = $file;
        $content = file_get_contents($file);
        
        // Filtrar solo logs de actualización de productos
        $lines = explode("\n", $content);
        $relevantLines = [];
        $capturing = false;
        
        foreach ($lines as $line) {
            if (strpos($line, 'INICIO ACTUALIZAR PRODUCTO') !== false) {
                $capturing = true;
            }
            
            if ($capturing) {
                $relevantLines[] = $line;
            }
            
            if (strpos($line, 'FIN ACTUALIZAR PRODUCTO') !== false) {
                $capturing = false;
            }
        }
        
        $logContent = implode("\n", array_slice($relevantLines, -100)); // Últimas 100 líneas
        break;
    }
}

// Test rápido de actualización
$testResult = null;
if (isset($_POST['test_update'])) {
    try {
        $pdo = getDB();
        $testSql = "SELECT id_producto, nombre, fyh_actualizacion FROM tb_almacen WHERE estado_registro = 'ACTIVO' LIMIT 1";
        $stmt = $pdo->query($testSql);
        $producto = $stmt->fetch();
        
        if ($producto) {
            $testResult = [
                'id' => $producto['id_producto'],
                'nombre' => $producto['nombre'],
                'ultima_actualizacion' => $producto['fyh_actualizacion']
            ];
            
            // Intentar una actualización sin cambios (para verificar permisos)
            $updateTest = "UPDATE tb_almacen SET fyh_actualizacion = NOW() WHERE id_producto = ?";
            $stmtUpdate = $pdo->prepare($updateTest);
            $result = $stmtUpdate->execute([$producto['id_producto']]);
            
            $testResult['update_success'] = $result;
            $testResult['rows_affected'] = $stmtUpdate->rowCount();
        }
    } catch (Exception $e) {
        $testResult = ['error' => $e->getMessage()];
    }
}

?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">
                        <i class="fas fa-file-alt"></i> Logs de Actualización de Productos
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <!-- Test de actualización -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-vial"></i> Test de Actualización</h3>
                </div>
                <div class="card-body">
                    <p>Ejecutar un test rápido para verificar que las actualizaciones funcionan:</p>
                    
                    <form method="POST">
                        <button type="submit" name="test_update" class="btn btn-info">
                            <i class="fas fa-play"></i> Ejecutar Test
                        </button>
                    </form>
                    
                    <?php if ($testResult !== null): ?>
                    <hr>
                    <?php if (isset($testResult['error'])): ?>
                        <div class="alert alert-danger">
                            <strong>❌ Error:</strong> <?php echo $testResult['error']; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-<?php echo $testResult['update_success'] ? 'success' : 'danger'; ?>">
                            <h5><strong>Resultado del Test:</strong></h5>
                            <p>Producto ID: <?php echo $testResult['id']; ?></p>
                            <p>Nombre: <?php echo $testResult['nombre']; ?></p>
                            <p>Última actualización: <?php echo $testResult['ultima_actualizacion']; ?></p>
                            <p><strong>UPDATE exitoso:</strong> <?php echo $testResult['update_success'] ? '✅ SÍ' : '❌ NO'; ?></p>
                            <p><strong>Rows affected:</strong> <?php echo $testResult['rows_affected']; ?></p>
                        </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Logs -->
            <div class="card card-dark">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-terminal"></i> Logs de PHP</h3>
                    <div class="card-tools">
                        <?php if ($logFile): ?>
                        <span class="badge badge-success">
                            <i class="fas fa-check"></i> Log encontrado
                        </span>
                        <?php else: ?>
                        <span class="badge badge-danger">
                            <i class="fas fa-times"></i> Log no encontrado
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($logFile): ?>
                    <p><strong>Archivo:</strong> <code><?php echo $logFile; ?></code></p>
                    
                    <?php if (!empty($logContent)): ?>
                    <pre style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 5px; max-height: 600px; overflow-y: auto;"><?php echo htmlspecialchars($logContent); ?></pre>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        No hay logs de actualización de productos todavía. Intenta actualizar un producto y recarga esta página.
                    </div>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> No se encontró archivo de log</h5>
                        <p>Intenté buscar en estas ubicaciones:</p>
                        <ul>
                            <?php foreach ($logFiles as $file): ?>
                            <li><code><?php echo $file; ?></code></li>
                            <?php endforeach; ?>
                        </ul>
                        <p>Para habilitar logs de PHP, edita <code>php.ini</code> y configura:</p>
                        <pre>error_log = C:/xampp/php/logs/php_error_log
log_errors = On</pre>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Instrucciones -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-lightbulb"></i> Cómo Diagnosticar</h3>
                </div>
                <div class="card-body">
                    <ol>
                        <li>Ve a <a href="update.php?id=1" target="_blank">actualizar un producto</a></li>
                        <li>Haz algún cambio (nombre, precio, stock, etc.)</li>
                        <li>Guarda los cambios</li>
                        <li>Vuelve aquí y recarga la página</li>
                        <li>Revisa los logs arriba para ver qué pasó</li>
                    </ol>
                    
                    <hr>
                    
                    <h5>¿Qué buscar en los logs?</h5>
                    <ul>
                        <li>✅ <code>"Producto actualizado correctamente"</code> = Funcionó</li>
                        <li>❌ <code>"ERROR:"</code> = Indica dónde falló</li>
                        <li>📊 <code>"Rows affected: 1"</code> = La BD se actualizó</li>
                        <li>⚠️ <code>"Rows affected: 0"</code> = No se actualizó nada</li>
                    </ul>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>
