<?php
/**
 * Test de Diagnóstico General del Sistema
 * Prueba reportes, ventas, y gestión de cajas
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración
define('TESTS_DIR', __DIR__);
require_once __DIR__ . '/../../services/database/config.php';

// Obtener conexión PDO
$pdo = getDB();

// Variable para almacenar resultados
$resultados = [];
$total_tests = 0;
$tests_exitosos = 0;
$tests_fallidos = 0;

/**
 * Función helper para registrar resultados
 */
function test($nombre, $condicion, $mensaje_exito = 'OK', $mensaje_error = 'FALLÓ') {
    global $resultados, $total_tests, $tests_exitosos, $tests_fallidos;
    
    $total_tests++;
    $exito = is_callable($condicion) ? $condicion() : $condicion;
    
    if ($exito) {
        $tests_exitosos++;
        $resultados[] = [
            'nombre' => $nombre,
            'estado' => 'EXITOSO',
            'mensaje' => $mensaje_exito,
            'color' => 'success'
        ];
    } else {
        $tests_fallidos++;
        $resultados[] = [
            'nombre' => $nombre,
            'estado' => 'FALLIDO',
            'mensaje' => $mensaje_error,
            'color' => 'danger'
        ];
    }
    
    return $exito;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico del Sistema - Alberco</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; padding: 20px; }
        .test-card { margin-bottom: 20px; }
        .test-item { padding: 10px; border-left: 4px solid #ddd; margin-bottom: 10px; background: white; }
        .test-item.success { border-left-color: #28a745; }
        .test-item.danger { border-left-color: #dc3545; }
        .test-item.warning { border-left-color: #ffc107; }
        .summary-box { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4"><i class="fas fa-heartbeat"></i> Diagnóstico del Sistema Alberco</h1>
        
        <?php
        // ============================================
        // 1. TESTS DE CONEXIÓN Y CONFIGURACIÓN
        // ============================================
        echo '<div class="card test-card">';
        echo '<div class="card-header bg-primary text-white"><h4 class="mb-0"><i class="fas fa-database"></i> Conexión y Configuración</h4></div>';
        echo '<div class="card-body">';
        
        test('Conexión a Base de Datos', function() use ($pdo) {
            try {
                $pdo->query('SELECT 1');
                return true;
            } catch (Exception $e) {
                return false;
            }
        }, 'Conexión exitosa', 'No se puede conectar a la base de datos');
        
        test('Tabla tb_ventas existe', function() use ($pdo) {
            $stmt = $pdo->query("SHOW TABLES LIKE 'tb_ventas'");
            return $stmt->rowCount() > 0;
        }, 'Tabla encontrada', 'Tabla no existe');
        
        test('Tabla tb_arqueo_caja existe', function() use ($pdo) {
            $stmt = $pdo->query("SHOW TABLES LIKE 'tb_arqueo_caja'");
            return $stmt->rowCount() > 0;
        }, 'Tabla encontrada', 'Tabla no existe');
        
        test('Tabla tb_movimientos_caja existe', function() use ($pdo) {
            $stmt = $pdo->query("SHOW TABLES LIKE 'tb_movimientos_caja'");
            return $stmt->rowCount() > 0;
        }, 'Tabla encontrada', 'Tabla no existe');
        
        foreach ($resultados as $r) {
            echo "<div class='test-item {$r['color']}'>";
            echo "<strong>{$r['nombre']}:</strong> <span class='badge badge-{$r['color']}'>{$r['estado']}</span> - {$r['mensaje']}";
            echo "</div>";
        }
        $resultados = [];
        
        echo '</div></div>';
        
        // ============================================
        // 2. TESTS DE MODELOS
        // ============================================
        echo '<div class="card test-card">';
        echo '<div class="card-header bg-info text-white"><h4 class="mb-0"><i class="fas fa-cubes"></i> Modelos</h4></div>';
        echo '<div class="card-body">';
        
        test('Modelo Venta existe', function() {
            return file_exists(__DIR__ . '/../../models/venta.php');
        }, 'Archivo encontrado', 'Archivo no existe');
        
        test('Modelo Venta se puede cargar', function() {
            try {
                if (file_exists(__DIR__ . '/../../models/venta.php')) {
                    require_once __DIR__ . '/../../models/venta.php';
                    return class_exists('Venta');
                }
                return false;
            } catch (Exception $e) {
                return false;
            }
        }, 'Clase cargada correctamente', 'Error al cargar la clase');
        
        test('Modelo ArqueoCaja existe', function() {
            return file_exists(__DIR__ . '/../../models/arqueocaja.php');
        }, 'Archivo encontrado', 'Archivo no existe');
        
        test('Modelo MovimientoDeCaja existe', function() {
            return file_exists(__DIR__ . '/../../models/movimientodecaja.php');
        }, 'Archivo encontrado', 'Archivo no existe');
        
        foreach ($resultados as $r) {
            echo "<div class='test-item {$r['color']}'>";
            echo "<strong>{$r['nombre']}:</strong> <span class='badge badge-{$r['color']}'>{$r['estado']}</span> - {$r['mensaje']}";
            echo "</div>";
        }
        $resultados = [];
        
        echo '</div></div>';
        
        // ============================================
        // 3. TESTS DE CONTROLADORES
        // ============================================
        echo '<div class="card test-card">';
        echo '<div class="card-header bg-warning text-dark"><h4 class="mb-0"><i class="fas fa-cogs"></i> Controladores</h4></div>';
        echo '<div class="card-body">';
        
        test('Controlador de Reportes existe', function() {
            return file_exists(__DIR__ . '/../../controllers/venta/reportes.php');
        }, 'Archivo encontrado', 'Archivo no existe');
        
        test('Controlador de Ventas existe', function() {
            return file_exists(__DIR__ . '/../../controllers/venta/registrar_venta.php');
        }, 'Archivo encontrado', 'Archivo no existe');
        
        test('Vista de impresión existe', function() {
            return file_exists(__DIR__ . '/../../views/venta/imprimir.php');
        }, 'Archivo encontrado', 'Archivo no existe');
        
        foreach ($resultados as $r) {
            echo "<div class='test-item {$r['color']}'>";
            echo "<strong>{$r['nombre']}:</strong> <span class='badge badge-{$r['color']}'>{$r['estado']}</span> - {$r['mensaje']}";
            echo "</div>";
        }
        $resultados = [];
        
        echo '</div></div>';
        
        // ============================================
        // 4. TESTS DE DATOS
        // ============================================
        echo '<div class="card test-card">';
        echo '<div class="card-header bg-success text-white"><h4 class="mb-0"><i class="fas fa-table"></i> Datos en Base de Datos</h4></div>';
        echo '<div class="card-body">';
        
        test('Hay ventas registradas', function() use ($pdo) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_ventas");
            $result = $stmt->fetch();
            return $result['total'] > 0;
        }, 'Se encontraron ventas', 'No hay ventas registradas');
        
        test('Hay clientes registrados', function() use ($pdo) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_clientes");
            $result = $stmt->fetch();
            return $result['total'] > 0;
        }, 'Se encontraron clientes', 'No hay clientes registrados');
        
        test('Hay productos registrados', function() use ($pdo) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_almacen WHERE estado_registro = 'ACTIVO'");
            $result = $stmt->fetch();
            return $result['total'] > 0;
        }, 'Se encontraron productos', 'No hay productos activos');
        
        test('Hay métodos de pago configurados', function() use ($pdo) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_metodos_pago WHERE estado_registro = 'ACTIVO'");
            $result = $stmt->fetch();
            return $result['total'] > 0;
        }, 'Métodos de pago encontrados', 'No hay métodos de pago configurados');
        
        foreach ($resultados as $r) {
            echo "<div class='test-item {$r['color']}'>";
            echo "<strong>{$r['nombre']}:</strong> <span class='badge badge-{$r['color']}'>{$r['estado']}</span> - {$r['mensaje']}";
            echo "</div>";
        }
        $resultados = [];
        
        echo '</div></div>';
        
        // ============================================
        // 5. TESTS FUNCIONALES
        // ============================================
        echo '<div class="card test-card">';
        echo '<div class="card-header bg-secondary text-white"><h4 class="mb-0"><i class="fas fa-flask"></i> Tests Funcionales</h4></div>';
        echo '<div class="card-body">';
        
        // Test: Consultar última venta
        test('Consultar última venta', function() use ($pdo) {
            try {
                $stmt = $pdo->query("SELECT * FROM tb_ventas ORDER BY fecha_venta DESC LIMIT 1");
                return $stmt->rowCount() > 0;
            } catch (Exception $e) {
                return false;
            }
        }, 'Consulta exitosa', 'Error al consultar ventas');
        
        // Test: Verificar estructura de $pdo
        test('PDO correctamente configurado', function() use ($pdo) {
            return $pdo instanceof PDO;
        }, 'PDO configurado correctamente', 'Error en configuración de PDO');
        
        foreach ($resultados as $r) {
            echo "<div class='test-item {$r['color']}'>";
            echo "<strong>{$r['nombre']}:</strong> <span class='badge badge-{$r['color']}'>{$r['estado']}</span> - {$r['mensaje']}";
            echo "</div>";
        }
        $resultados = [];
        
        echo '</div></div>';
        
        // ============================================
        // RESUMEN FINAL
        // ============================================
        $porcentaje_exito = $total_tests > 0 ? round(($tests_exitosos / $total_tests) * 100, 2) : 0;
        $estado_general = $porcentaje_exito >= 80 ? 'success' : ($porcentaje_exito >= 50 ? 'warning' : 'danger');
        ?>
        
        <div class="summary-box">
            <h3><i class="fas fa-chart-pie"></i> Resumen del Diagnóstico</h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center">
                        <h2 class="text-success"><?= $tests_exitosos ?></h2>
                        <p>Tests Exitosos</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <h2 class="text-danger"><?= $tests_fallidos ?></h2>
                        <p>Tests Fallidos</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <h2 class="text-<?= $estado_general ?>"><?= $porcentaje_exito ?>%</h2>
                        <p>Tasa de Éxito</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="alert alert-<?= $estado_general ?>">
                <strong>Estado General:</strong> 
                <?php
                if ($porcentaje_exito >= 80) {
                    echo '✅ Sistema funcionando correctamente';
                } elseif ($porcentaje_exito >= 50) {
                    echo ' Algunos problemas detectados';
                } else {
                    echo '❌ Problemas críticos detectados';
                }
                ?>
            </div>
        </div>
        
        <div class="text-center mb-4">
            <a href="../dashboard.php" class="btn btn-primary"><i class="fas fa-home"></i> Volver al Dashboard</a>
            <button onclick="location.reload()" class="btn btn-info"><i class="fas fa-sync"></i> Recargar Tests</button>
        </div>
    </div>
</body>
</html>
