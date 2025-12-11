<?php
/**
 * Script de Actualización de Campo unidad_medida
 * Agrega el campo y valores por defecto a productos antiguos
 */

include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Solo admins
if ($_SESSION['id_rol'] != 1) {
    die("Solo administradores pueden ejecutar este script");
}

$log = [];
$ejecutar = isset($_POST['confirmar_update']);

try {
    $pdo = getDB();
    
    // 1. Verificar si la columna existe
    $checkColumn = $pdo->query("SHOW COLUMNS FROM tb_almacen LIKE 'unidad_medida'");
    $columnaExiste = $checkColumn->rowCount() > 0;
    
    if (!$columnaExiste) {
        if ($ejecutar) {
            // Agregar columna
            $pdo->exec("ALTER TABLE tb_almacen ADD COLUMN unidad_medida VARCHAR(20) DEFAULT 'UNIDAD' AFTER tiempo_preparacion");
            $log[] = "✓ Columna 'unidad_medida' agregada a tb_almacen";
        } else {
            $log[] = "⚠ La columna 'unidad_medida' NO existe en tb_almacen";
        }
    } else {
        $log[] = "✓ La columna 'unidad_medida' ya existe";
    }
    
    // 2. Actualizar productos con NULL o vacío
    $sql = "UPDATE tb_almacen 
            SET unidad_medida = 'UNIDAD' 
            WHERE unidad_medida IS NULL OR unidad_medida = ''";
    
    if ($ejecutar) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $affected = $stmt->rowCount();
        
        if ($affected > 0) {
            $log[] = "✓ Actualizados $affected productos con unidad_medida = 'UNIDAD'";
        } else {
            $log[] = "✓ Todos los productos ya tienen unidad_medida definida";
        }
    } else {
        // Solo contar
        $countSql = "SELECT COUNT(*) as total FROM tb_almacen WHERE unidad_medida IS NULL OR unidad_medida = ''";
        $stmt = $pdo->query($countSql);
        $total = $stmt->fetch()['total'];
        $log[] = "$total productos necesitan actualización de unidad_medida";
    }
    
} catch (Exception $e) {
    $log[] = "❌ ERROR: " . $e->getMessage();
}

?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">
                        <i class="fas fa-database"></i> Actualizar Campo unidad_medida
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <?php if (!$ejecutar): ?>
            
            <div class="alert alert-info">
                <h5><i class="icon fas fa-info-circle"></i> Información</h5>
                Este script agregará el campo <code>unidad_medida</code> a la tabla <code>tb_almacen</code>
                si no existe, y asignará el valor por defecto 'UNIDAD' a todos los productos que no lo tengan.
            </div>
            
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Diagnóstico Actual</h3>
                </div>
                <div class="card-body">
                    <?php foreach ($log as $item): ?>
                        <p><?php echo $item; ?></p>
                    <?php endforeach; ?>
                    
                    <hr>
                    
                    <form method="POST">
                        <button type="submit" name="confirmar_update" class="btn btn-primary btn-lg">
                            <i class="fas fa-database"></i> Ejecutar Actualización
                        </button>
                        <a href="index.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </form>
                </div>
            </div>
            
            <?php else: ?>
            
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-check-circle"></i> Actualización Completada</h3>
                </div>
                <div class="card-body">
                    <?php foreach ($log as $item): ?>
                        <p><?php echo $item; ?></p>
                    <?php endforeach; ?>
                    
                    <hr>
                    
                    <a href="show.php?id=1" class="btn btn-info">
                        <i class="fas fa-eye"></i> Ver Producto de Prueba
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
