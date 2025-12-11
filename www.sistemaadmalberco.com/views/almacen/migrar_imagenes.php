<?php
/**
 * Script de Migración de Imágenes de Productos
 * Mueve imágenes de uploads/productos/ a uploads/almacen/
 * y actualiza las rutas en la base de datos
 */

include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Solo admins pueden ejecutar
if ($_SESSION['id_rol'] != 1) {
    die("Solo administradores pueden ejecutar este script");
}

$migracion_log = [];
$errores = [];
$ejecutar = isset($_POST['confirmar_migracion']);

try {
    $pdo = getDB();
    
    // 1. Obtener productos con rutas antiguas
    $sql = "SELECT id_producto, codigo, nombre, imagen 
            FROM tb_almacen 
            WHERE imagen LIKE 'uploads/productos/%' 
            AND estado_registro = 'ACTIVO'";
    $stmt = $pdo->query($sql);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_productos = count($productos);
    
    if ($ejecutar && $total_productos > 0) {
        
        // Crear carpeta destino si no existe
        $destDir = dirname(__DIR__, 2) . '/uploads/almacen/';
        if (!file_exists($destDir)) {
            mkdir($destDir, 0777, true);
            $migracion_log[] = "✓ Carpeta uploads/almacen/ creada";
        }
        
        $pdo->beginTransaction();
        
        foreach ($productos as $producto) {
            $id = $producto['id_producto'];
            $rutaVieja = $producto['imagen'];
            
            if (empty($rutaVieja)) {
                continue;
            }
            
            // Ruta física vieja
            $archivoViejo = dirname(__DIR__, 2) . '/' . $rutaVieja;
            
            if (!file_exists($archivoViejo)) {
                $errores[] = "❌ Producto #{$id} - Archivo no existe: {$rutaVieja}";
                continue;
            }
            
            // Nueva ruta
            $nombreArchivo = basename($rutaVieja);
            $rutaNueva = 'uploads/almacen/' . $nombreArchivo;
            $archivoNuevo = dirname(__DIR__, 2) . '/' . $rutaNueva;
            
            // Mover archivo
            if (copy($archivoViejo, $archivoNuevo)) {
                
                // Actualizar BD
                $updateSql = "UPDATE tb_almacen SET imagen = ? WHERE id_producto = ?";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->execute([$rutaNueva, $id]);
                
                // Eliminar archivo viejo
                unlink($archivoViejo);
                
                $migracion_log[] = "✓ Producto #{$id} ({$producto['codigo']}) - Migrado correctamente";
                
            } else {
                $errores[] = "❌ Producto #{$id} - Error al copiar archivo";
            }
        }
        
        $pdo->commit();
        
        $migracion_log[] = "✅ MIGRACIÓN COMPLETADA - {$total_productos} productos procesados";
        
        // Intentar eliminar carpeta vieja si está vacía
        $oldDir = dirname(__DIR__, 2) . '/uploads/productos/';
        if (file_exists($oldDir)) {
            $files = scandir($oldDir);
            $files = array_diff($files, ['.', '..']);
            if (empty($files)) {
                rmdir($oldDir);
                $migracion_log[] = "✓ Carpeta uploads/productos/ eliminada (estaba vacía)";
            } else {
                $migracion_log[] = "⚠ Carpeta uploads/productos/ tiene otros archivos, no se eliminó";
            }
        }
        
    }
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    $errores[] = "❌ ERROR CRÍTICO: " . $e->getMessage();
}

?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">
                        <i class="fas fa-sync-alt"></i> Migración de Imágenes de Productos
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            
            <?php if (!$ejecutar): ?>
            
            <!-- Vista Previa -->
            <div class="alert alert-warning">
                <h5><i class="icon fas fa-exclamation-triangle"></i> Atención!</h5>
                Este script moverá las imágenes de <code>uploads/productos/</code> a <code>uploads/almacen/</code>
                y actualizará las rutas en la base de datos.
            </div>
            
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Vista Previa de la Migración</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-database"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Productos a Migrar</span>
                                    <span class="info-box-number"><?php echo $total_productos; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($total_productos > 0): ?>
                    <h5>Productos que serán migrados:</h5>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Ruta Actual</th>
                                <th>Nueva Ruta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $p): ?>
                            <tr>
                                <td><?php echo $p['id_producto']; ?></td>
                                <td><?php echo htmlspecialchars($p['codigo']); ?></td>
                                <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                                <td><code><?php echo $p['imagen']; ?></code></td>
                                <td><code>uploads/almacen/<?php echo basename($p['imagen']); ?></code></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <hr>
                    
                    <form method="POST" onsubmit="return confirm('¿Está seguro de ejecutar la migración? Este proceso es irreversible.');">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Proceso de Migración:</h5>
                            <ol>
                                <li>Se copiarán las imágenes a <code>uploads/almacen/</code></li>
                                <li>Se actualizarán las rutas en la base de datos</li>
                                <li>Se eliminarán los archivos antiguos de <code>uploads/productos/</code></li>
                                <li>Si la carpeta queda vacía, se eliminará</li>
                            </ol>
                        </div>
                        
                        <button type="submit" name="confirmar_migracion" class="btn btn-primary btn-lg">
                            <i class="fas fa-play"></i> Ejecutar Migración
                        </button>
                        <a href="index.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </form>
                    
                    <?php else: ?>
                    <div class="alert alert-success">
                        <h5><i class="icon fas fa-check"></i> ¡Todo correcto!</h5>
                        No hay productos con rutas antiguas. Todos los productos ya están usando la ruta correcta.
                    </div>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Volver al Almacén
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php else: ?>
            
            <!-- Resultados de la Migración -->
            <div class="card card-<?php echo empty($errores) ? 'success' : 'warning'; ?>">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-<?php echo empty($errores) ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                        Resultados de la Migración
                    </h3>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($migracion_log)): ?>
                    <h5>Log de Migración:</h5>
                    <div class="alert alert-success">
                        <?php foreach ($migracion_log as $log): ?>
                            <p class="mb-1"><?php echo $log; ?></p>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errores)): ?>
                    <h5>Errores:</h5>
                    <div class="alert alert-danger">
                        <?php foreach ($errores as $error): ?>
                            <p class="mb-1"><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <a href="debug_update.php?id=1" class="btn btn-info">
                        <i class="fas fa-bug"></i> Verificar con Debug
                    </a>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> Ver Almacén
                    </a>
                </div>
            </div>
            
            <?php endif; ?>
            
        </div>
    </div>
</div>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<style>
.info-box {
    min-height: 90px;
}
.table code {
    font-size: 0.85rem;
    background: #f4f4f4;
    padding: 2px 6px;
    border-radius: 3px;
}
</style>
