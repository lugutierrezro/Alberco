<?php
/**
 * Debug del Módulo de Mesas
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
    <title>Debug Módulo Mesas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
    </style>
</head>
<body>
<div class="container mt-4">
    <h1><i class="fas fa-chair"></i> Debug Módulo de Mesas</h1>
    <p class="text-muted">Ejecutado: <?php echo date('d/m/Y H:i:s'); ?></p>
    
    <hr>
    
    <?php
    // 1. Verificar si existe tb_mesas
    echo '<div class="card mb-3">';
    echo '<div class="card-header bg-primary text-white"><h4>1. Verificar Tabla tb_mesas</h4></div>';
    echo '<div class="card-body">';
    
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'tb_mesas'");
        $existe = $stmt->fetch();
        
        if ($existe) {
            echo "<div class='alert alert-success'><i class='fas fa-check'></i> La tabla <code>tb_mesas</code> existe</div>";
            
            // Mostrar estructura
            $stmt = $pdo->query("SHOW COLUMNS FROM tb_mesas");
            $columns = $stmt->fetchAll();
            
            echo "<h5>Estructura de la tabla:</h5>";
            echo "<table class='table table-sm table-bordered'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td><code>{$col['Field']}</code></td>";
                echo "<td>{$col['Type']}</td>";
                echo "<td>{$col['Null']}</td>";
                echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Contar registros
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_mesas");
            $total = $stmt->fetch()['total'];
            echo "<p><strong>Total de mesas:</strong> $total</p>";
            
            if ($total > 0) {
                // Mostrar mesas
                $stmt = $pdo->query("SELECT * FROM tb_mesas LIMIT 10");
                $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h5>Primeras mesas:</h5>";
                echo "<pre>" . print_r($mesas, true) . "</pre>";
            } else {
                echo "<div class='alert alert-warning'>No hay mesas registradas en la base de datos.</div>";
                echo "<p><strong>Acción sugerida:</strong> Create algunas mesas de prueba.</p>";
            }
            
        } else {
            echo "<div class='alert alert-danger'><i class='fas fa-times'></i> La tabla <code>tb_mesas</code> NO existe</div>";
            echo "<p><strong>Acción requerida:</strong> Crear la tabla tb_mesas</p>";
            
            echo "<h5>Script SQL sugerido:</h5>";
            echo "<pre>";
            echo "CREATE TABLE `tb_mesas` (
  `id_mesa` INT(11) NOT NULL AUTO_INCREMENT,
  `numero_mesa` VARCHAR(10) NOT NULL,
  `capacidad` INT(11) NOT NULL DEFAULT 4,
  `zona` VARCHAR(50) DEFAULT 'Principal',
  `estado` ENUM('disponible','ocupada','reservada','mantenimiento') DEFAULT 'disponible',
  `qr_code` VARCHAR(255) DEFAULT NULL,
  `observaciones` TEXT DEFAULT NULL,
  `estado_registro` ENUM('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  `fyh_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fyh_actualizacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mesa`),
  UNIQUE KEY `numero_mesa` (`numero_mesa`),
  KEY `idx_estado` (`estado`),
  KEY `idx_zona` (`zona`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            echo "</pre>";
        }
        
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
    echo '</div></div>';
    
    // 2. Probar consulta de listar
    echo '<div class="card mb-3">';
    echo '<div class="card-header bg-info text-white"><h4>2. Probar Consulta Listar</h4></div>';
    echo '<div class="card-body">';
    
    try {
        include __DIR__ . '/../../controllers/mesas/listar.php';
        
        echo "<p><strong>Mesas encontradas:</strong> " . count($mesas_datos) . "</p>";
        
        if (!empty($mesas_datos)) {
            echo "<pre>" . print_r($mesas_datos, true) . "</pre>";
        } else {
            echo "<div class='alert alert-warning'>No se encontraron mesas con los criterios actuales</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error al ejecutar listar.php: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
    echo '</div></div>';
    
    // 3. Probar estadísticas
    echo '<div class="card mb-3">';
    echo '<div class="card-header bg-success text-white"><h4>3. Probar Estadísticas</h4></div>';
    echo '<div class="card-body">';
    
    try {
        include __DIR__ . '/../../controllers/mesas/estadisticas.php';
        
        echo "<pre>" . print_r($estadisticas_mesas, true) . "</pre>";
        
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error al ejecutar estadisticas.php: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
    echo '</div></div>';
    
    // 4. Verificar tb_pedidos
    echo '<div class="card mb-3">';
    echo '<div class="card-header bg-warning"><h4>4. Verificar tb_pedidos</h4></div>';
    echo '<div class="card-body">';
    
    try {
        $stmt = $pdo->query("SELECT DISTINCT id_estado, estado FROM tb_pedidos p JOIN tb_estados e ON p.id_estado = e.id_estado");
        $estados = $stmt->fetchAll();
        
        echo "<p><strong>Estados usados en tb_pedidos:</strong></p>";
        echo "<pre>" . print_r($estados, true) . "</pre>";
        
    } catch (Exception $e) {
        echo "<p class='text-muted'>No se pudo consultar estados: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo '</div></div>';
    ?>
    
    <div class="text-center mb-4">
        <a href="../mesas/index.php" class="btn btn-primary">Ir a Módulo de Mesas</a>
        <a href="debug_errores.php" class="btn btn-secondary">Debug General</a>
    </div>
</div>
</body>
</html>
