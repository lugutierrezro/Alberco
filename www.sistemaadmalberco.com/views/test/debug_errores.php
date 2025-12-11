<?php
/**
 * Debug Completo del Sistema
 * Verifica errores, warnings y estado general
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
    <title>Debug del Sistema</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .debug-section { margin-bottom: 20px; }
        .success { color: #28a745; }
        .warning { color: #ffc107; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
<div class="container mt-4">
    <h1><i class="fas fa-bug"></i> Debug Completo del Sistema</h1>
    <p class="text-muted">Ejecutado: <?php echo date('d/m/Y H:i:s'); ?></p>
    
    <hr>
    
    <?php
    // ===================================
    // 1. VERIFICAR SESIÓN
    // ===================================
    echo '<div class="card debug-section">';
    echo '<div class="card-header bg-primary text-white"><h4><i class="fas fa-user-lock"></i> 1. Estado de Sesión</h4></div>';
    echo '<div class="card-body">';
    
    echo "<h5>Variables de Sesión:</h5>";
    echo "<table class='table table-sm table-bordered'>";
    echo "<tr><th>Variable</th><th>Valor</th><th>Estado</th></tr>";
    
    $session_vars = [
        'sesion' => 'Sesión activa',
        'id_usuario' => 'ID del usuario',
        'user_name' => 'Nombre del usuario',
        'user_email' => 'Email del usuario',
        'user_role' => 'Rol del usuario',
        'nombres_sesion' => 'Nombres (legacy)',
        'usuario_sesion' => 'Usuario (legacy)'
    ];
    
    foreach ($session_vars as $var => $desc) {
        $existe = isset($_SESSION[$var]);
        $valor = $existe ? $_SESSION[$var] : 'NO DEFINIDA';
        $clase = $existe ? 'success' : 'warning';
        $icono = $existe ? 'fa-check' : 'fa-exclamation-triangle';
        
        echo "<tr>";
        echo "<td><code>\$_SESSION['$var']</code></td>";
        echo "<td>" . htmlspecialchars($valor) . "</td>";
        echo "<td><i class='fas $icono $clase'></i> $desc</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Verificar autenticación
    $autenticado = isset($_SESSION['sesion']) && $_SESSION['sesion'] === 'ok';
    if ($autenticado) {
        echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> <strong>Usuario autenticado correctamente</strong></div>";
    } else {
        echo "<div class='alert alert-danger'><i class='fas fa-times-circle'></i> <strong>Usuario NO autenticado</strong></div>";
    }
    
    echo '</div></div>';
    
    // ===================================
    // 2. VERIFICAR ERRORES PHP
    // ===================================
    echo '<div class="card debug-section">';
    echo '<div class="card-header bg-danger text-white"><h4><i class="fas fa-exclamation-triangle"></i> 2. Errores PHP Recientes</h4></div>';
    echo '<div class="card-body">';
    
    $errorLogPath = 'C:/xampp/apache/logs/error.log';
    if (file_exists($errorLogPath)) {
        $lines = file($errorLogPath);
        $recentLines = array_slice($lines, -50); // Últimas 50 líneas
        
        $errores = [];
        $warnings = [];
        $notices = [];
        
        foreach ($recentLines as $line) {
            if (strpos($line, 'sistemaadmalberco') !== false) {
                if (stripos($line, 'Fatal error') !== false || stripos($line, 'Parse error') !== false) {
                    $errores[] = $line;
                } elseif (stripos($line, 'Warning') !== false) {
                    $warnings[] = $line;
                } elseif (stripos($line, 'Notice') !== false || stripos($line, 'Deprecated') !== false) {
                    $notices[] = $line;
                }
            }
        }
        
        echo "<h5>Resumen:</h5>";
        echo "<ul>";
        echo "<li class='error'><strong>Errores Fatales:</strong> " . count($errores) . "</li>";
        echo "<li class='warning'><strong>Warnings:</strong> " . count($warnings) . "</li>";
        echo "<li class='info'><strong>Notices/Deprecated:</strong> " . count($notices) . "</li>";
        echo "</ul>";
        
        if (!empty($errores)) {
            echo "<h5 class='error'>Errores Fatales:</h5>";
            echo "<pre class='text-danger'>";
            foreach (array_slice($errores, -5) as $error) {
                echo htmlspecialchars($error);
            }
            echo "</pre>";
        }
        
        if (!empty($warnings)) {
            echo "<h5 class='warning'>Warnings Recientes:</h5>";
            echo "<pre class='text-warning'>";
            foreach (array_slice($warnings, -10) as $warning) {
                echo htmlspecialchars($warning);
            }
            echo "</pre>";
        }
        
        if (!empty($notices)) {
            echo "<h5 class='info'>Notices/Deprecated:</h5>";
            echo "<pre class='text-info'>";
            foreach (array_slice($notices, -10) as $notice) {
                echo htmlspecialchars($notice);
            }
            echo "</pre>";
        }
        
        if (empty($errores) && empty($warnings) && empty($notices)) {
            echo "<div class='alert alert-success'><i class='fas fa-check'></i> No hay errores recientes relacionados con el sistema</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>No se encontró el archivo de logs</div>";
    }
    
    echo '</div></div>';
    
    // ===================================
    // 3. VERIFICAR BASE DE DATOS
    // ===================================
    echo '<div class="card debug-section">';
    echo '<div class="card-header bg-info text-white"><h4><i class="fas fa-database"></i> 3. Estado de Base de Datos</h4></div>';
    echo '<div class="card-body">';
    
    try {
        $stmt = $pdo->query("SELECT VERSION() as version");
        $version = $stmt->fetch()['version'];
        echo "<div class='alert alert-success'><i class='fas fa-check'></i> Conexión exitosa - MySQL $version</div>";
        
        // Verificar tablas críticas
        $tablas_criticas = [
            'tb_ventas',
            'tb_detalle_ventas',
            'tb_pedidos',
            'tb_detalle_pedidos',
            'tb_clientes',
            'tb_usuarios',
            'tb_almacen',
            'tb_arqueo_caja',
            'tb_movimientos_caja',
            'tb_metodos_pago'
        ];
        
        echo "<h5>Tablas Críticas:</h5>";
        echo "<table class='table table-sm'>";
        echo "<tr><th>Tabla</th><th>Registros</th><th>Estado</th></tr>";
        
        foreach ($tablas_criticas as $tabla) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabla");
                $total = $stmt->fetch()['total'];
                $clase = $total > 0 ? 'success' : 'warning';
                $icono = $total > 0 ? 'fa-check' : 'fa-exclamation-triangle';
                
                echo "<tr>";
                echo "<td><code>$tabla</code></td>";
                echo "<td><strong>$total</strong></td>";
                echo "<td><i class='fas $icono $clase'></i></td>";
                echo "</tr>";
            } catch (PDOException $e) {
                echo "<tr>";
                echo "<td><code>$tabla</code></td>";
                echo "<td colspan='2' class='error'>ERROR: " . htmlspecialchars($e->getMessage()) . "</td>";
                echo "</tr>";
            }
        }
        
        echo "</table>";
        
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'><i class='fas fa-times'></i> Error de conexión: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
    echo '</div></div>';
    
    // ===================================
    // 4. VERIFICAR ARCHIVOS CRÍTICOS
    // ===================================
    echo '<div class="card debug-section">';
    echo '<div class="card-header bg-warning"><h4><i class="fas fa-file-code"></i> 4. Archivos Críticos</h4></div>';
    echo '<div class="card-body">';
    
    $archivos_criticos = [
        'Modelos' => [
            __DIR__ . '/../../models/venta.php',
            __DIR__ . '/../../models/pedido.php',
            __DIR__ . '/../../models/usuario.php',
            __DIR__ . '/../../models/database.php'
        ],
        'Controladores' => [
            __DIR__ . '/../../controllers/venta/reportes.php',
            __DIR__ . '/../../controllers/pedidos/actualizar_estado.php',
            __DIR__ . '/../../controllers/caja/abrir.php',
            __DIR__ . '/../../controllers/caja/resumen.php'
        ],
        'Vistas' => [
            __DIR__ . '/../reportes/index.php',
            __DIR__ . '/../caja/index.php',
            __DIR__ . '/../caja/apertura.php'
        ]
    ];
    
    foreach ($archivos_criticos as $categoria => $archivos) {
        echo "<h5>$categoria:</h5>";
        echo "<ul>";
        foreach ($archivos as $archivo) {
            $existe = file_exists($archivo);
            $clase = $existe ? 'success' : 'error';
            $icono = $existe ? 'fa-check' : 'fa-times';
            $nombre = basename($archivo);
            
            echo "<li class='$clase'>";
            echo "<i class='fas $icono'></i> <code>$nombre</code>";
            if (!$existe) {
                echo " <strong>(NO EXISTE)</strong>";
            }
            echo "</li>";
        }
        echo "</ul>";
    }
    
    echo '</div></div>';
    
    // ===================================
    // 5. VERIFICAR CONFIGURACIÓN PHP
    // ===================================
    echo '<div class="card debug-section">';
    echo '<div class="card-header bg-secondary text-white"><h4><i class="fas fa-cog"></i> 5. Configuración PHP</h4></div>';
    echo '<div class="card-body">';
    
    echo "<table class='table table-sm'>";
    echo "<tr><th>Configuración</th><th>Valor</th></tr>";
    echo "<tr><td>PHP Version</td><td><strong>" . phpversion() . "</strong></td></tr>";
    echo "<tr><td>display_errors</td><td>" . ini_get('display_errors') . "</td></tr>";
    echo "<tr><td>error_reporting</td><td>" . error_reporting() . "</td></tr>";
    echo "<tr><td>max_execution_time</td><td>" . ini_get('max_execution_time') . "s</td></tr>";
    echo "<tr><td>memory_limit</td><td>" . ini_get('memory_limit') . "</td></tr>";
    echo "<tr><td>post_max_size</td><td>" . ini_get('post_max_size') . "</td></tr>";
    echo "<tr><td>upload_max_filesize</td><td>" . ini_get('upload_max_filesize') . "</td></tr>";
    echo "<tr><td>session.save_path</td><td>" . session_save_path() . "</td></tr>";
    echo "<tr><td>Timezone</td><td>" . date_default_timezone_get() . "</td></tr>";
    echo "</table>";
    
    echo '</div></div>';
    
    // ===================================
    // 6. RESUMEN GENERAL
    // ===================================
    echo '<div class="card debug-section">';
    echo '<div class="card-header bg-success text-white"><h4><i class="fas fa-clipboard-check"></i> 6. Resumen General</h4></div>';
    echo '<div class="card-body">';
    
    $problemas = [];
    
    if (!$autenticado) {
        $problemas[] = "Usuario no autenticado";
    }
    
    if (!empty($errores)) {
        $problemas[] = count($errores) . " errores fatales encontrados";
    }
    
    if (!empty($warnings)) {
        $problemas[] = count($warnings) . " warnings encontrados";
    }
    
    if (empty($problemas)) {
        echo "<div class='alert alert-success'>";
        echo "<h4><i class='fas fa-check-circle'></i> Sistema Funcionando Correctamente</h4>";
        echo "<p>No se encontraron problemas críticos.</p>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-warning'>";
        echo "<h4><i class='fas fa-exclamation-triangle'></i> Problemas Detectados</h4>";
        echo "<ul>";
        foreach ($problemas as $problema) {
            echo "<li>$problema</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    echo "<h5>Acciones Recomendadas:</h5>";
    echo "<ul>";
    echo "<li>Revisar logs de error PHP regularmente</li>";
    echo "<li>Verificar que todas las variables de sesión estén definidas</li>";
    echo "<li>Mantener respaldos de la base de datos</li>";
    echo "<li>Actualizar PHP a la última versión estable</li>";
    echo "</ul>";
    
    echo '</div></div>';
    ?>
    
    <div class="text-center mb-4">
        <a href="../../dashboard.php" class="btn btn-primary">Volver al Dashboard</a>
        <a href="diagnostico_sistema.php" class="btn btn-info">Diagnóstico del Sistema</a>
        <a href="debug_reportes.php" class="btn btn-secondary">Debug de Reportes</a>
    </div>
</div>
</body>
</html>
