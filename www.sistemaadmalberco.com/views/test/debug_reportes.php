<?php
/**
 * Debug: Verificar Reportes
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
    <title>Debug - Reportes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1>🔍 Debug: Reportes</h1>
    
    <?php
    // 1. Verificar datos en tb_ventas
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-info text-white'><h4>1. Datos en tb_ventas</h4></div>";
    echo "<div class='card-body'>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_ventas");
    $totalVentas = $stmt->fetch()['total'];
    
    echo "<div class='alert alert-info'>";
    echo "<strong>Total de ventas:</strong> $totalVentas";
    echo "</div>";
    
    if ($totalVentas > 0) {
        $stmt = $pdo->query("
            SELECT v.*, c.nombre, c.apellidos, u.username
            FROM tb_ventas v
            LEFT JOIN tb_clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN tb_usuarios u ON v.id_usuario = u.id_usuario
            ORDER BY v.fecha_venta DESC
            LIMIT 5
        ");
        $ventas = $stmt->fetchAll();
        
        echo "<table class='table table-sm'>";
        echo "<tr><th>ID</th><th>Nro</th><th>Cliente</th><th>Total</th><th>Fecha</th></tr>";
        foreach ($ventas as $v) {
            echo "<tr>";
            echo "<td>{$v['id_venta']}</td>";
            echo "<td>{$v['nro_venta']}</td>";
            echo "<td>{$v['nombre']} {$v['apellidos']}</td>";
            echo "<td>S/ {$v['total']}</td>";
            echo "<td>{$v['fecha_venta']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "</div></div>";
    
    // 2. Probar consultas de reportes
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-warning'><h4>2. Probar Consultas de Reportes</h4></div>";
    echo "<div class='card-body'>";
    
    $fechaInicio = date('Y-m-d', strtotime('-7 days'));
    $fechaFin = date('Y-m-d');
    
    echo "<p><strong>Rango:</strong> $fechaInicio a $fechaFin</p>";
    
    // Ventas por periodo
    try {
        $sql = "SELECT DATE(fecha_venta) as fecha, 
                       COUNT(*) as num_ventas,
                       SUM(total) as total_dia
                FROM tb_ventas
                WHERE fecha_venta BETWEEN :inicio AND :fin
                AND estado_venta = 'completada'
                GROUP BY DATE(fecha_venta)
                ORDER BY fecha";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':inicio' => $fechaInicio . ' 00:00:00',
            ':fin' => $fechaFin . ' 23:59:59'
        ]);
        $ventasPeriodo = $stmt->fetchAll();
        
        echo "<div class='alert alert-success'>";
        echo "<strong>✅ Ventas por Periodo:</strong> " . count($ventasPeriodo) . " días con ventas";
        echo "</div>";
        
        if (!empty($ventasPeriodo)) {
            echo "<table class='table table-sm'>";
            echo "<tr><th>Fecha</th><th>Num Ventas</th><th>Total</th></tr>";
            foreach ($ventasPeriodo as $vp) {
                echo "<tr>";
                echo "<td>{$vp['fecha']}</td>";
                echo "<td>{$vp['num_ventas']}</td>";
                echo "<td>S/ " . number_format($vp['total_dia'], 2) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
    
    // Productos más vendidos
    try {
        $sql = "SELECT p.nombre, p.nombre_categoria,
                       SUM(dv.cantidad) as total_vendido,
                       SUM(dv.subtotal) as total_ingresos
                FROM tb_detalle_ventas dv
                INNER JOIN tb_ventas v ON dv.id_venta = v.id_venta
                INNER JOIN tb_almacen p ON dv.id_producto = p.id_producto
                WHERE v.fecha_venta BETWEEN :inicio AND :fin
                AND v.estado_venta = 'completada'
                GROUP BY dv.id_producto
                ORDER BY total_vendido DESC
                LIMIT 5";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':inicio' => $fechaInicio . ' 00:00:00',
            ':fin' => $fechaFin . ' 23:59:59'
        ]);
        $productos = $stmt->fetchAll();
        
        echo "<div class='alert alert-success'>";
        echo "<strong>✅ Productos Más Vendidos:</strong> " . count($productos) . " productos";
        echo "</div>";
        
        if (!empty($productos)) {
            echo "<table class='table table-sm'>";
            echo "<tr><th>Producto</th><th>Cantidad</th><th>Ingresos</th></tr>";
            foreach ($productos as $prod) {
                echo "<tr>";
                echo "<td>{$prod['nombre']}</td>";
                echo "<td>{$prod['total_vendido']}</td>";
                echo "<td>S/ " . number_format($prod['total_ingresos'], 2) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error productos: " . $e->getMessage() . "</div>";
    }
    
    // Métodos de pago
    try {
        $sql = "SELECT mp.nombre_metodo,
                       COUNT(*) as num_transacciones,
                       SUM(v.total) as total_monto
                FROM tb_ventas v
                INNER JOIN tb_metodos_pago mp ON v.id_metodo_pago = mp.id_metodo
                WHERE v.fecha_venta BETWEEN :inicio AND :fin
                AND v.estado_venta = 'completada'
                GROUP BY v.id_metodo_pago";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':inicio' => $fechaInicio . ' 00:00:00',
            ':fin' => $fechaFin . ' 23:59:59'
        ]);
        $metodos = $stmt->fetchAll();
        
        echo "<div class='alert alert-success'>";
        echo "<strong>✅ Métodos de Pago:</strong> " . count($metodos) . " métodos usados";
        echo "</div>";
        
        if (!empty($metodos)) {
            echo "<table class='table table-sm'>";
            echo "<tr><th>Método</th><th>Transacciones</th><th>Total</th></tr>";
            foreach ($metodos as $met) {
                echo "<tr>";
                echo "<td>{$met['nombre_metodo']}</td>";
                echo "<td>{$met['num_transacciones']}</td>";
                echo "<td>S/ " . number_format($met['total_monto'], 2) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error métodos: " . $e->getMessage() . "</div>";
    }
    
    echo "</div></div>";
    
    // 3. Verificar modelo Venta
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-secondary text-white'><h4>3. Verificar Modelo Venta</h4></div>";
    echo "<div class='card-body'>";
    
    if (file_exists(__DIR__ . '/../../models/venta.php')) {
        require_once __DIR__ . '/../../models/venta.php';
        
        try {
            $ventaModel = new Venta();
            
            // Probar método reporteVentasPeriodo
            if (method_exists($ventaModel, 'reporteVentasPeriodo')) {
                $resultado = $ventaModel->reporteVentasPeriodo($fechaInicio, $fechaFin);
                echo "<div class='alert alert-success'>";
                echo "✅ Método reporteVentasPeriodo existe y retorna: " . count($resultado) . " registros";
                echo "</div>";
            } else {
                echo "<div class='alert alert-danger'>";
                echo "❌ Método reporteVentasPeriodo NO existe";
                echo "</div>";
            }
            
            // Probar método productosMasVendidos
            if (method_exists($ventaModel, 'productosMasVendidos')) {
                $resultado = $ventaModel->productosMasVendidos($fechaInicio, $fechaFin, 5);
                echo "<div class='alert alert-success'>";
                echo "✅ Método productosMasVendidos existe y retorna: " . count($resultado) . " registros";
                echo "</div>";
            } else {
                echo "<div class='alert alert-danger'>";
                echo "❌ Método productosMasVendidos NO existe";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>";
            echo "Error al instanciar modelo: " . $e->getMessage();
            echo "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Modelo venta.php no encontrado</div>";
    }
    
    echo "</div></div>";
    
    // 4. Probar endpoint de reportes
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-primary text-white'><h4>4. Probar Endpoint de Reportes</h4></div>";
    echo "<div class='card-body'>";
    
    $url = "http://localhost/www.sistemaadmalberco.com/controllers/venta/reportes.php?tipo=ventas_periodo&fecha_inicio=$fechaInicio&fecha_fin=$fechaFin";
    
    echo "<p><strong>URL:</strong> <code>$url</code></p>";
    
    try {
        $response = @file_get_contents($url);
        if ($response) {
            $data = json_decode($response, true);
            if ($data) {
                echo "<div class='alert alert-success'>";
                echo "✅ Endpoint responde correctamente";
                echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
                echo "</div>";
            } else {
                echo "<div class='alert alert-warning'>";
                echo "⚠️ Respuesta no es JSON válido: $response";
                echo "</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>";
            echo "❌ No se pudo conectar al endpoint";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
    
    echo "</div></div>";
    ?>
    
    <a href="../reportes/index.php" class="btn btn-primary">Ir a Reportes</a>
    <a href="../../dashboard.php" class="btn btn-secondary">Dashboard</a>
</div>
</body>
</html>
