<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/../../services/database/config.php');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>
<title>Debug Notificaciones</title>
<style>
body { font-family: Arial; padding: 20px; background: #f5f5f5; }
.section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
h2 { color: #FF6B35; border-bottom: 2px solid #FF6B35; padding-bottom: 10px; }
.success { color: #28a745; font-weight: bold; }
.error { color: #dc3545; font-weight: bold; }
.warning { color: #ffc107; font-weight: bold; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
th { background: #FF6B35; color: white; }
pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; overflow-x: auto; }
.badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; }
.badge-success { background: #28a745; color: white; }
.badge-danger { background: #dc3545; color: white; }
.badge-warning { background: #ffc107; color: #000; }
</style>
</head><body>";

echo "<h1>🔔 Diagnóstico de Notificaciones - Sistema Alberco</h1>";

try {
    $pdo = getDB();
    
    // ====================================================
    // 1. VERIFICAR TABLA tb_notificaciones
    // ====================================================
    echo "<div class='section'><h2>1. Tabla de Notificaciones</h2>";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'tb_notificaciones'");
    $tabla_existe = $stmt->rowCount() > 0;
    
    if ($tabla_existe) {
        echo "<p class='success'>✅ Tabla tb_notificaciones existe</p>";
        
        // Ver estructura
        $stmt = $pdo->query("DESCRIBE tb_notificaciones");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Estructura de la tabla:</h3>";
        echo "<table><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columnas as $col) {
            echo "<tr>";
            echo "<td><strong>" . $col['Field'] . "</strong></td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Contar notificaciones
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_notificaciones");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p><strong>Total de notificaciones:</strong> <span class='badge badge-success'>$total</span></p>";
        
        if ($total > 0) {
            $stmt = $pdo->query("SELECT * FROM tb_notificaciones ORDER BY fecha_notificacion DESC LIMIT 5");
            $notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>Últimas 5 notificaciones:</h3>";
            echo "<pre>" . print_r($notifs, true) . "</pre>";
        }
        
    } else {
        echo "<p class='error'>❌ Tabla tb_notificaciones NO existe</p>";
        echo "<p class='warning'>⚠️ Creando tabla automáticamente...</p>";
        
        // Crear tabla
        $sql = "CREATE TABLE `tb_notificaciones` (
            `id_notificacion` INT(11) NOT NULL AUTO_INCREMENT,
            `id_pedido` INT(11) DEFAULT NULL,
            `id_usuario_destino` INT(11) NOT NULL,
            `tipo` ENUM('pedido_nuevo', 'cambio_estado', 'alerta_stock', 'delivery_asignado', 'pedido_entregado', 'otro') NOT NULL,
            `titulo` VARCHAR(255) NOT NULL,
            `mensaje` TEXT NOT NULL,
            `leido` TINYINT(1) DEFAULT 0,
            `fecha_notificacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `fecha_lectura` DATETIME DEFAULT NULL,
            `prioridad` ENUM('baja', 'normal', 'alta', 'urgente') DEFAULT 'normal',
            `enlace` VARCHAR(255) DEFAULT NULL,
            `estado_registro` ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
            `fyh_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `fyh_actualizacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_notificacion`),
            KEY `id_usuario_destino` (`id_usuario_destino`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "<p class='success'>✅ Tabla creada correctamente</p>";
    }
    
    echo "</div>";
    
    // ====================================================
    // 2. VERIFICAR PEDIDOS PENDIENTES
    // ====================================================
    echo "<div class='section'><h2>2. Pedidos Pendientes</h2>";
    
    // Primero ver qué columnas tiene tb_pedidos
    $stmt = $pdo->query("DESCRIBE tb_pedidos");
    $cols_pedidos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p><strong>Columnas en tb_pedidos:</strong> " . implode(', ', $cols_pedidos) . "</p>";
    
    // Ver estados disponibles
    $stmt = $pdo->query("SELECT * FROM tb_estados WHERE estado_registro = 'ACTIVO' ORDER BY orden");
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Estados disponibles:</h3>";
    echo "<table><tr><th>ID</th><th>Nombre</th><th>Color</th><th>Orden</th></tr>";
    foreach ($estados as $est) {
        echo "<tr>";
        echo "<td>{$est['id_estado']}</td>";
        echo "<td>{$est['nombre_estado']}</td>";
        echo "<td><span style='color:{$est['color']}'>{$est['color']}</span></td>";
        echo "<td>{$est['orden']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Contar pedidos por estado
    $stmt = $pdo->query("
        SELECT e.nombre_estado, COUNT(p.id_pedido) as total
        FROM tb_estados e
        LEFT JOIN tb_pedidos p ON e.id_estado = p.id_estado AND p.estado_registro = 'ACTIVO'
        GROUP BY e.id_estado, e.nombre_estado
        ORDER BY e.orden
    ");
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Pedidos por estado:</h3>";
    echo "<table><tr><th>Estado</th><th>Cantidad</th></tr>";
    foreach ($stats as $s) {
        $badge = $s['total'] > 0 ? 'badge-success' : 'badge-warning';
        echo "<tr><td>{$s['nombre_estado']}</td><td><span class='badge $badge'>{$s['total']}</span></td></tr>";
    }
    echo "</table>";
    
    echo "</div>";
    
    // ====================================================
    // 3. VERIFICAR STOCK BAJO
    // ====================================================
    echo "<div class='section'><h2>3. Productos con Stock Bajo</h2>";
    
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM tb_almacen 
        WHERE stock <= stock_minimo 
          AND estado_registro = 'ACTIVO'
    ");
    $stock_bajo = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<p><strong>Productos con stock bajo:</strong> <span class='badge badge-" . ($stock_bajo > 0 ? 'danger' : 'success') . "'>$stock_bajo</span></p>";
    
    if ($stock_bajo > 0) {
        $stmt = $pdo->query("
            SELECT nombre, stock, stock_minimo 
            FROM tb_almacen 
            WHERE stock <= stock_minimo 
              AND estado_registro = 'ACTIVO'
            ORDER BY stock ASC
            LIMIT 10
        ");
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table><tr><th>Producto</th><th>Stock</th><th>Mínimo</th></tr>";
        foreach ($productos as $p) {
            echo "<tr><td>{$p['nombre']}</td><td class='error'>{$p['stock']}</td><td>{$p['stock_minimo']}</td></tr>";
        }
        echo "</table>";
    }
    
    echo "</div>";
    
    // ====================================================
    // 4. VERIFICAR CAJA
    // ====================================================
    echo "<div class='section'><h2>4. Estado de Caja</h2>";
    
    $stmt = $pdo->query("
        SELECT * FROM tb_arqueo_caja 
        WHERE estado = 'abierto' 
        ORDER BY fecha_arqueo DESC 
        LIMIT 1
    ");
    $caja = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($caja) {
        echo "<p class='warning'>⚠️ Hay caja abierta</p>";
        echo "<pre>" . print_r($caja, true) . "</pre>";
        
        if ($caja['fecha_arqueo'] < date('Y-m-d')) {
            echo "<p class='error'>❌ ALERTA: La caja está abierta desde un día anterior!</p>";
        }
    } else {
        echo "<p class='success'>✅ No hay caja abierta actualmente</p>";
    }
    
    echo "</div>";
    
    // ====================================================
    // 5. TEST DEL CONTROLADOR
    // ====================================================
    echo "<div class='section'><h2>5. Test del Controlador notificaciones.php</h2>";
    
    $url = 'http://localhost/www.sistemaadmalberco.com/controllers/dashboard/notificaciones.php';
    
    echo "<p><strong>URL:</strong> <a href='$url' target='_blank'>$url</a></p>";
    
    $response = @file_get_contents($url);
    
    if ($response) {
        echo "<p class='success'>✅ Controlador respondió</p>";
        echo "<h3>Respuesta:</h3>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        $data = json_decode($response, true);
        if ($data) {
            echo "<h3>JSON Parseado:</h3>";
            echo "<pre>" . print_r($data, true) . "</pre>";
        }
    } else {
        echo "<p class='error'>❌ Controlador no respondió</p>";
    }
    
    echo "</div>";
    
    // ====================================================
    // 6. RESUMEN
    // ====================================================
    echo "<div class='section'><h2>6. Resumen y Acciones</h2>";
    
    echo "<h3>Estado General:</h3>";
    echo "<ul>";
    echo "<li>Tabla tb_notificaciones: " . ($tabla_existe ? "<span class='success'>✅ OK</span>" : "<span class='error'>❌ Falta</span>") . "</li>";
    echo "<li>Pedidos activos: <span class='badge badge-info'>" . array_sum(array_column($stats, 'total')) . "</span></li>";
    echo "<li>Stock bajo: <span class='badge badge-" . ($stock_bajo > 0 ? 'danger' : 'success') . "'>$stock_bajo</span></li>";
    echo "<li>Caja abierta: " . ($caja ? "<span class='warning'>⚠️ Sí</span>" : "<span class='success'>✅ No</span>") . "</li>";
    echo "</ul>";
    
    echo "<h3>Acciones Rápidas:</h3>";
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='../test/crear_notificacion_prueba.php' class='btn' style='display:inline-block; padding:10px 20px; background:#FF6B35; color:white; text-decoration:none; border-radius:5px; margin:5px;'>Crear Notificación de Prueba</a>";
    echo "<a href='../../' class='btn' style='display:inline-block; padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px; margin:5px;'>Ir al Dashboard</a>";
    echo "<a href='javascript:location.reload()' class='btn' style='display:inline-block; padding:10px 20px; background:#28a745; color:white; text-decoration:none; border-radius:5px; margin:5px;'>Recargar Debug</a>";
    echo "</div>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section'><p class='error'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p></div>";
}

echo "<p style='text-align:center; color:#999; margin-top:40px;'>Generado: " . date('Y-m-d H:i:s') . "</p>";
echo "</body></html>";
?>
