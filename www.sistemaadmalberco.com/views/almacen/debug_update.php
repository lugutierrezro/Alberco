<?php
/**
 * Debug de Actualización de Productos
 * Sistema de diagnóstico completo para el módulo de almacén
 */

include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

$debug_info = [];
$id_producto = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ============================================
// DIAGNÓSTICO COMPLETO
// ============================================

try {
    $pdo = getDB();
    
    // 1. Verificar producto existe
    $debug_info['1_producto_existe'] = [
        'titulo' => '1. Verificación de Producto',
        'estado' => 'checking'
    ];
    
    if ($id_producto > 0) {
        $sql = "SELECT * FROM tb_almacen WHERE id_producto = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_producto]);
        $producto = $stmt->fetch();
        
        if ($producto) {
            $debug_info['1_producto_existe']['estado'] = 'success';
            $debug_info['1_producto_existe']['data'] = $producto;
            $debug_info['1_producto_existe']['mensaje'] = "Producto encontrado: " . $producto['nombre'];
        } else {
            $debug_info['1_producto_existe']['estado'] = 'error';
            $debug_info['1_producto_existe']['mensaje'] = "Producto ID $id_producto NO existe";
        }
    } else {
        $debug_info['1_producto_existe']['estado'] = 'warning';
        $debug_info['1_producto_existe']['mensaje'] = "No se proporcionó ID de producto";
    }
    
    // 2. Verificar permisos de carpeta uploads
    $debug_info['2_permisos_upload'] = [
        'titulo' => '2. Verificación de Carpeta Uploads',
        'estado' => 'checking'
    ];
    
    $uploadDir = dirname(__DIR__, 2) . '/uploads/almacen/';
    $uploadDirExists = file_exists($uploadDir);
    $uploadDirWritable = is_writable($uploadDir);
    
    $debug_info['2_permisos_upload']['uploadDir'] = $uploadDir;
    $debug_info['2_permisos_upload']['exists'] = $uploadDirExists;
    $debug_info['2_permisos_upload']['writable'] = $uploadDirWritable;
    
    if (!$uploadDirExists) {
        $debug_info['2_permisos_upload']['estado'] = 'error';
        $debug_info['2_permisos_upload']['mensaje'] = "Carpeta NO existe";
        
        // Intentar crear
        if (mkdir($uploadDir, 0777, true)) {
            $debug_info['2_permisos_upload']['estado'] = 'warning';
            $debug_info['2_permisos_upload']['mensaje'] = "Carpeta creada automáticamente";
        }
    } elseif (!$uploadDirWritable) {
        $debug_info['2_permisos_upload']['estado'] = 'error';
        $debug_info['2_permisos_upload']['mensaje'] = "Carpeta SIN permisos de escritura";
    } else {
        $debug_info['2_permisos_upload']['estado'] = 'success';
        $debug_info['2_permisos_upload']['mensaje'] = "Carpeta existe y tiene permisos correctos";
    }
    
    // 3. Verificar imagen actual
    if (isset($producto)) {
        $debug_info['3_imagen_actual'] = [
            'titulo' => '3. Verificación de Imagen Actual',
            'estado' => 'checking'
        ];
        
        $imagenPath = $producto['imagen'];
        $debug_info['3_imagen_actual']['ruta_bd'] = $imagenPath;
        
        if (!empty($imagenPath)) {
            $fullPath = dirname(__DIR__, 2) . '/' . $imagenPath;
            $debug_info['3_imagen_actual']['ruta_fisica'] = $fullPath;
            $debug_info['3_imagen_actual']['existe'] = file_exists($fullPath);
            
            if (file_exists($fullPath)) {
                $debug_info['3_imagen_actual']['estado'] = 'success';
                $debug_info['3_imagen_actual']['tamaño'] = filesize($fullPath);
                $debug_info['3_imagen_actual']['url'] = URL_BASE . '/' . $imagenPath;
                $debug_info['3_imagen_actual']['mensaje'] = "Imagen encontrada y accesible";
            } else {
                $debug_info['3_imagen_actual']['estado'] = 'error';
                $debug_info['3_imagen_actual']['mensaje'] = "Archivo de imagen NO existe en el disco";
            }
        } else {
            $debug_info['3_imagen_actual']['estado'] = 'warning';
            $debug_info['3_imagen_actual']['mensaje'] = "Producto sin imagen asignada";
        }
    }
    
    // 4. Verificar categorías disponibles
    $debug_info['4_categorias'] = [
        'titulo' => '4. Verificación de Categorías',
        'estado' => 'checking'
    ];
    
    $sqlCat = "SELECT COUNT(*) as total FROM tb_categorias WHERE estado_registro = 'ACTIVO'";
    $stmtCat = $pdo->query($sqlCat);
    $totalCat = $stmtCat->fetch()['total'];
    
    if ($totalCat > 0) {
        $debug_info['4_categorias']['estado'] = 'success';
        $debug_info['4_categorias']['total'] = $totalCat;
        $debug_info['4_categorias']['mensaje'] = "$totalCat categorías activas disponibles";
    } else {
        $debug_info['4_categorias']['estado'] = 'error';
        $debug_info['4_categorias']['mensaje'] = "NO hay categorías activas";
    }
    
    // 5. Verificar últimas actualizaciones
    $debug_info['5_historial'] = [
        'titulo' => '5. Historial de Actualizaciones',
        'estado' => 'checking'
    ];
    
    $sqlHist = "SELECT id_producto, codigo, nombre, fyh_actualizacion 
                FROM tb_almacen 
                WHERE estado_registro = 'ACTIVO'
                ORDER BY fyh_actualizacion DESC 
                LIMIT 5";
    $stmtHist = $pdo->query($sqlHist);
    $historial = $stmtHist->fetchAll(PDO::FETCH_ASSOC);
    
    $debug_info['5_historial']['estado'] = 'success';
    $debug_info['5_historial']['ultimas_actualizaciones'] = $historial;
    
    // 6. Verificar configuración PHP
    $debug_info['6_php_config'] = [
        'titulo' => '6. Configuración PHP',
        'estado' => 'info',
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'file_uploads' => ini_get('file_uploads') ? 'Habilitado' : 'Deshabilitado'
    ];
    
    // 7. Test de escritura
    $debug_info['7_test_escritura'] = [
        'titulo' => '7. Test de Escritura en BD',
        'estado' => 'checking'
    ];
    
    try {
        $testSql = "SELECT 1 as test";
        $testStmt = $pdo->query($testSql);
        $testResult = $testStmt->fetch();
        
        if ($testResult['test'] == 1) {
            $debug_info['7_test_escritura']['estado'] = 'success';
            $debug_info['7_test_escritura']['mensaje'] = "Conexión a BD operativa";
        }
    } catch (Exception $e) {
        $debug_info['7_test_escritura']['estado'] = 'error';
        $debug_info['7_test_escritura']['mensaje'] = "Error en BD: " . $e->getMessage();
    }
    
} catch (Exception $e) {
    $debug_info['error_general'] = [
        'titulo' => 'ERROR GENERAL',
        'estado' => 'error',
        'mensaje' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ];
}

?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">
                        <i class="fas fa-bug"></i> Debug - Actualización de Productos
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            
            <!-- Búsqueda rápida -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Seleccionar Producto para Debug</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="form-inline">
                        <div class="form-group mr-3">
                            <label for="id" class="mr-2">ID Producto:</label>
                            <input type="number" 
                                   name="id" 
                                   id="id" 
                                   class="form-control" 
                                   value="<?php echo $id_producto; ?>"
                                   min="1"
                                   required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Diagnosticar
                        </button>
                    </form>
                </div>
            </div>

            <!-- Resultados del diagnóstico -->
            <?php if ($id_producto > 0): ?>
            <div class="row">
                <?php foreach ($debug_info as $key => $info): ?>
                <div class="col-md-6">
                    <div class="card 
                        <?php 
                        echo $info['estado'] === 'success' ? 'card-success' : 
                             ($info['estado'] === 'error' ? 'card-danger' : 
                             ($info['estado'] === 'warning' ? 'card-warning' : 'card-info')); 
                        ?>">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-
                                    <?php 
                                    echo $info['estado'] === 'success' ? 'check-circle' : 
                                         ($info['estado'] === 'error' ? 'times-circle' : 
                                         ($info['estado'] === 'warning' ? 'exclamation-triangle' : 'info-circle')); 
                                    ?>">
                                </i>
                                <?php echo $info['titulo']; ?>
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-
                                    <?php 
                                    echo $info['estado'] === 'success' ? 'success' : 
                                         ($info['estado'] === 'error' ? 'danger' : 
                                         ($info['estado'] === 'warning' ? 'warning' : 'info')); 
                                    ?>">
                                    <?php echo strtoupper($info['estado']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (isset($info['mensaje'])): ?>
                                <p><strong><?php echo $info['mensaje']; ?></strong></p>
                            <?php endif; ?>
                            
                            <?php if (isset($info['data'])): ?>
                                <hr>
                                <h6>Datos del Producto:</h6>
                                <table class="table table-sm table-striped">
                                    <tr><td><strong>ID:</strong></td><td><?php echo $info['data']['id_producto']; ?></td></tr>
                                    <tr><td><strong>Código:</strong></td><td><?php echo $info['data']['codigo']; ?></td></tr>
                                    <tr><td><strong>Nombre:</strong></td><td><?php echo $info['data']['nombre']; ?></td></tr>
                                    <tr><td><strong>Stock:</strong></td><td><?php echo $info['data']['stock']; ?></td></tr>
                                    <tr><td><strong>Precio Venta:</strong></td><td>S/ <?php echo $info['data']['precio_venta']; ?></td></tr>
                                    <tr><td><strong>Última actualización:</strong></td><td><?php echo $info['data']['fyh_actualizacion']; ?></td></tr>
                                </table>
                            <?php endif; ?>
                            
                            <?php if (isset($info['uploadDir'])): ?>
                                <small>
                                    <strong>Ruta:</strong> <code><?php echo $info['uploadDir']; ?></code><br>
                                    <strong>Existe:</strong> <?php echo $info['exists'] ? '✓ Sí' : '✗ No'; ?><br>
                                    <strong>Escribible:</strong> <?php echo $info['writable'] ? '✓ Sí' : '✗ No'; ?>
                                </small>
                            <?php endif; ?>
                            
                            <?php if (isset($info['ruta_bd'])): ?>
                                <small>
                                    <strong>Ruta en BD:</strong> <code><?php echo $info['ruta_bd']; ?></code><br>
                                    <?php if (isset($info['ruta_fisica'])): ?>
                                    <strong>Ruta física:</strong> <code><?php echo $info['ruta_fisica']; ?></code><br>
                                    <?php endif; ?>
                                    <?php if (isset($info['existe'])): ?>
                                    <strong>Archivo existe:</strong> <?php echo $info['existe'] ? '✓ Sí' : '✗ No'; ?><br>
                                    <?php endif; ?>
                                    <?php if (isset($info['url'])): ?>
                                    <strong>URL:</strong> <a href="<?php echo $info['url']; ?>" target="_blank"><?php echo $info['url']; ?></a><br>
                                    <?php endif; ?>
                                </small>
                                
                                <?php if (isset($info['existe']) && $info['existe']): ?>
                                <hr>
                                <img src="<?php echo $info['url']; ?>" class="img-thumbnail" style="max-width: 200px;">
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if (isset($info['total'])): ?>
                                <p>Total: <strong><?php echo $info['total']; ?></strong></p>
                            <?php endif; ?>
                            
                            <?php if (isset($info['ultimas_actualizaciones'])): ?>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Código</th>
                                            <th>Nombre</th>
                                            <th>Actualización</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($info['ultimas_actualizaciones'] as $item): ?>
                                        <tr>
                                            <td><?php echo $item['id_producto']; ?></td>
                                            <td><?php echo $item['codigo']; ?></td>
                                            <td><?php echo $item['nombre']; ?></td>
                                            <td><small><?php echo $item['fyh_actualizacion']; ?></small></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                            
                            <?php if (isset($info['upload_max_filesize'])): ?>
                                <table class="table table-sm table-bordered">
                                    <tr><td>upload_max_filesize</td><td><?php echo $info['upload_max_filesize']; ?></td></tr>
                                    <tr><td>post_max_size</td><td><?php echo $info['post_max_size']; ?></td></tr>
                                    <tr><td>memory_limit</td><td><?php echo $info['memory_limit']; ?></td></tr>
                                    <tr><td>max_execution_time</td><td><?php echo $info['max_execution_time']; ?>s</td></tr>
                                    <tr><td>file_uploads</td><td><?php echo $info['file_uploads']; ?></td></tr>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- JSON completo para desarrolladores -->
            <div class="card card-dark">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-code"></i> Debug JSON Completo</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" onclick="copyDebugJSON()">
                            <i class="fas fa-copy"></i> Copiar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <pre id="debugJSON" style="max-height: 400px; overflow-y: auto; background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 5px;"><?php echo json_encode($debug_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
function copyDebugJSON() {
    const text = document.getElementById('debugJSON').textContent;
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Copiado',
            text: 'JSON copiado al portapapeles',
            timer: 1500,
            showConfirmButton: false
        });
    });
}
</script>
