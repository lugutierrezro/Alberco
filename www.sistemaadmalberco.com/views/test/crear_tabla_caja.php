<?php
/**
 * Crear tabla tb_caja
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../services/database/config.php';
$pdo = getDB();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Tabla tb_caja</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1>🔧 Crear Tabla tb_caja</h1>
    
    <?php
    if (isset($_POST['crear_tabla'])) {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `tb_caja` (
                `id_caja` INT(11) NOT NULL AUTO_INCREMENT,
                `turno` ENUM('MAÑANA','TARDE','NOCHE') NOT NULL,
                `fecha_apertura` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `fecha_cierre` DATETIME NULL,
                `monto_inicial` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `monto_final` DECIMAL(10,2) NULL,
                `monto_esperado` DECIMAL(10,2) NULL,
                `diferencia` DECIMAL(10,2) NULL,
                `total_ingresos` DECIMAL(10,2) NULL DEFAULT 0.00,
                `total_egresos` DECIMAL(10,2) NULL DEFAULT 0.00,
                `id_usuario_apertura` INT(11) NOT NULL,
                `id_usuario_cierre` INT(11) NULL,
                `observaciones_apertura` TEXT NULL,
                `observaciones_cierre` TEXT NULL,
                `estado` ENUM('ABIERTA','CERRADA') NOT NULL DEFAULT 'ABIERTA',
                `estado_registro` ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
                `fyh_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `fyh_actualizacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_caja`),
                KEY `idx_usuario_apertura` (`id_usuario_apertura`),
                KEY `idx_estado` (`estado`),
                KEY `idx_fecha_apertura` (`fecha_apertura`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $pdo->exec($sql);
            
            echo "<div class='alert alert-success'>";
            echo "<h4>✅ Tabla Creada Exitosamente</h4>";
            echo "<p>La tabla <code>tb_caja</code> ha sido creada correctamente.</p>";
            echo "</div>";
            
            // Mostrar estructura
            $stmt = $pdo->query("SHOW COLUMNS FROM tb_caja");
            $columns = $stmt->fetchAll();
            
            echo "<h5>Estructura de la tabla:</h5>";
            echo "<table class='table table-sm table-bordered'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td><strong>{$col['Field']}</strong></td>";
                echo "<td>{$col['Type']}</td>";
                echo "<td>{$col['Null']}</td>";
                echo "<td>{$col['Default']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<div class='alert alert-info mt-3'>";
            echo "<h5>Próximos Pasos:</h5>";
            echo "<ol>";
            echo "<li>Ve a Gestión de Caja</li>";
            echo "<li>Abre una nueva caja</li>";
            echo "<li>Registra movimientos</li>";
            echo "</ol>";
            echo "<a href='../caja/index.php' class='btn btn-primary'>Ir a Gestión de Caja</a>";
            echo "</div>";
            
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>";
            echo "<h4>❌ Error al Crear Tabla</h4>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
    } else {
        // Verificar si ya existe
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'tb_caja'");
            $existe = $stmt->fetch();
            
            if ($existe) {
                echo "<div class='alert alert-warning'>";
                echo "<h4>⚠️ La tabla ya existe</h4>";
                echo "<p>La tabla <code>tb_caja</code> ya está creada en la base de datos.</p>";
                echo "<a href='../caja/index.php' class='btn btn-primary'>Ir a Gestión de Caja</a>";
                echo "</div>";
            } else {
                ?>
                <div class="card">
                    <div class="card-header bg-warning">
                        <h4>⚠️ Tabla tb_caja No Existe</h4>
                    </div>
                    <div class="card-body">
                        <p>La tabla <code>tb_caja</code> es necesaria para el módulo de Gestión de Caja.</p>
                        
                        <h5>La tabla contendrá:</h5>
                        <ul>
                            <li>Información de apertura y cierre de caja</li>
                            <li>Montos iniciales y finales</li>
                            <li>Control de turnos (Mañana, Tarde, Noche)</li>
                            <li>Usuarios responsables</li>
                            <li>Observaciones</li>
                        </ul>
                        
                        <form method="POST">
                            <button type="submit" name="crear_tabla" class="btn btn-success btn-lg">
                                <i class="fas fa-plus"></i> Crear Tabla tb_caja
                            </button>
                            <a href="debug_errores.php" class="btn btn-secondary">Volver al Debug</a>
                        </form>
                    </div>
                </div>
                <?php
            }
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>";
            echo "Error: " . htmlspecialchars($e->getMessage());
            echo "</div>";
        }
    }
    ?>
    
    <hr>
    <a href="../../dashboard.php" class="btn btn-secondary">Volver al Dashboard</a>
</div>
</body>
</html>
