<?php
/**
 * Debug: Verificar por qué no se genera la venta
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
    <title>Debug - Generación de Ventas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1>🔍 Debug: Generación de Ventas</h1>
    
    <?php
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-info text-white'><h4>1. Verificar Estados</h4></div>";
    echo "<div class='card-body'>";
    
    $stmt = $pdo->query("SELECT * FROM tb_estados WHERE estado_registro = 'ACTIVO' ORDER BY id_estado");
    $estados = $stmt->fetchAll();
    
    echo "<table class='table table-sm'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th></tr>";
    foreach ($estados as $est) {
        $highlight = ($est['nombre_estado'] == 'Entregado') ? 'table-success' : '';
        echo "<tr class='$highlight'>";
        echo "<td><strong>{$est['id_estado']}</strong></td>";
        echo "<td>{$est['nombre_estado']}</td>";
        echo "<td>{$est['descripcion']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $stmt = $pdo->query("SELECT id_estado FROM tb_estados WHERE nombre_estado = 'Entregado' LIMIT 1");
    $estadoEntregado = $stmt->fetch();
    $idEntregado = $estadoEntregado ? $estadoEntregado['id_estado'] : 'NO ENCONTRADO';
    
    echo "<div class='alert alert-warning'>";
    echo "<strong>ID del estado 'Entregado':</strong> $idEntregado";
    echo "</div>";
    
    echo "</div></div>";
    
    // Verificar estructura de tb_ventas
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-info text-white'><h4>2. Verificar Tabla tb_ventas</h4></div>";
    echo "<div class='card-body'>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM tb_ventas");
    $columns = $stmt->fetchAll();
    
    $hasIdPedido = false;
    echo "<table class='table table-sm'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        if ($col['Field'] == 'id_pedido') {
            $hasIdPedido = true;
            echo "<tr class='table-success'>";
        } else {
            echo "<tr>";
        }
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if (!$hasIdPedido) {
        echo "<div class='alert alert-danger'>";
        echo "<strong>⚠️ PROBLEMA:</strong> La tabla tb_ventas NO tiene la columna 'id_pedido'";
        echo "<p>Necesitas ejecutar:</p>";
        echo "<code>ALTER TABLE tb_ventas ADD COLUMN id_pedido INT NULL;</code>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-success'>✅ La columna id_pedido existe</div>";
    }
    
    echo "</div></div>";
    
    // Verificar pedidos entregados
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-info text-white'><h4>3. Pedidos en Estado 'Entregado'</h4></div>";
    echo "<div class='card-body'>";
    
    if ($idEntregado !== 'NO ENCONTRADO') {
        $stmt = $pdo->prepare("
            SELECT p.id_pedido, p.nro_pedido, p.total, p.fecha_pedido,
                   (SELECT COUNT(*) FROM tb_ventas WHERE id_pedido = p.id_pedido) as tiene_venta
            FROM tb_pedidos p
            WHERE p.id_estado = ?
            ORDER BY p.fecha_pedido DESC
            LIMIT 5
        ");
        $stmt->execute([$idEntregado]);
        $pedidosEntregados = $stmt->fetchAll();
        
        if (empty($pedidosEntregados)) {
            echo "<div class='alert alert-warning'>No hay pedidos en estado 'Entregado'</div>";
        } else {
            echo "<table class='table table-sm'>";
            echo "<tr><th>ID Pedido</th><th>Número</th><th>Total</th><th>Fecha</th><th>Tiene Venta</th></tr>";
            foreach ($pedidosEntregados as $p) {
                echo "<tr>";
                echo "<td>{$p['id_pedido']}</td>";
                echo "<td>{$p['nro_pedido']}</td>";
                echo "<td>S/ {$p['total']}</td>";
                echo "<td>{$p['fecha_pedido']}</td>";
                echo "<td>";
                if ($p['tiene_venta'] > 0) {
                    echo "<span class='badge badge-success'>SÍ</span>";
                } else {
                    echo "<span class='badge badge-danger'>NO</span>";
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    echo "</div></div>";
    
    // Verificar logs de error
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-info text-white'><h4>4. Logs de Error PHP</h4></div>";
    echo "<div class='card-body'>";
    
    $errorLogPath = 'C:/xampp/apache/logs/error.log';
    if (file_exists($errorLogPath)) {
        $lines = file($errorLogPath);
        $recentLines = array_slice($lines, -20);
        
        echo "<pre style='max-height: 300px; overflow-y: scroll;'>";
        foreach ($recentLines as $line) {
            if (strpos($line, 'Venta') !== false || strpos($line, 'venta') !== false) {
                echo "<strong class='text-success'>$line</strong>";
            } elseif (strpos($line, 'Error') !== false || strpos($line, 'error') !== false) {
                echo "<strong class='text-danger'>$line</strong>";
            } else {
                echo $line;
            }
        }
        echo "</pre>";
    } else {
        echo "<div class='alert alert-warning'>No se encontró el archivo de logs</div>";
    }
    
    echo "</div></div>";
    
    // Test manual
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-warning'><h4>5. Test Manual de Generación</h4></div>";
    echo "<div class='card-body'>";
    
    if (isset($_POST['test_generar'])) {
        $id_pedido = (int)$_POST['id_pedido_test'];
        
        echo "<div class='alert alert-info'><strong>Intentando generar venta para pedido #$id_pedido...</strong></div>";
        
        try {
            // Verificar si existe venta
            $stmt = $pdo->prepare("SELECT id_venta FROM tb_ventas WHERE id_pedido = ?");
            $stmt->execute([$id_pedido]);
            $ventaExiste = $stmt->fetch();
            
            if ($ventaExiste) {
                echo "<div class='alert alert-warning'>Ya existe una venta (ID: {$ventaExiste['id_venta']}) para este pedido</div>";
            } else {
                // Obtener datos del pedido
                $stmt = $pdo->prepare("SELECT * FROM tb_pedidos WHERE id_pedido = ?");
                $stmt->execute([$id_pedido]);
                $pedido = $stmt->fetch();
                
                if (!$pedido) {
                    throw new Exception("Pedido no encontrado");
                }
                
                echo "<div class='alert alert-info'>";
                echo "<strong>Datos del pedido:</strong><br>";
                echo "Subtotal: S/ {$pedido['subtotal']}<br>";
                echo "Total: S/ {$pedido['total']}<br>";
                echo "Cliente ID: {$pedido['id_cliente']}<br>";
                echo "Usuario ID: {$pedido['id_usuario_registro']}<br>";
                echo "</div>";
                
                // Generar número de venta
                $stmt = $pdo->query("SELECT COALESCE(MAX(nro_venta), 0) + 1 as siguiente FROM tb_ventas");
                $nroVenta = $stmt->fetch()['siguiente'];
                
                echo "<div class='alert alert-info'>Número de venta a generar: <strong>$nroVenta</strong></div>";
                
                // Intentar crear venta
                $pdo->beginTransaction();
                
                $sql = "INSERT INTO tb_ventas (
                    nro_venta, serie_comprobante, numero_comprobante,
                    id_cliente, id_usuario, id_tipo_comprobante, id_metodo_pago,
                    id_pedido, subtotal, igv, descuento, total,
                    estado_venta, fecha_venta
                ) VALUES (?, 'B001', ?, ?, ?, 1, 1, ?, ?, ?, ?, ?, 'completada', NOW())";
                
                $igv = $pedido['subtotal'] * 0.18;
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $nroVenta,
                    str_pad($nroVenta, 8, '0', STR_PAD_LEFT),
                    $pedido['id_cliente'],
                    $pedido['id_usuario_registro'],
                    $id_pedido,
                    $pedido['subtotal'],
                    $igv,
                    $pedido['descuento'],
                    $pedido['total']
                ]);
                
                $idVenta = $pdo->lastInsertId();
                
                $pdo->commit();
                
                echo "<div class='alert alert-success'>";
                echo "<h5>✅ Venta Creada Exitosamente</h5>";
                echo "<strong>ID Venta:</strong> $idVenta<br>";
                echo "<strong>Número:</strong> $nroVenta<br>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo "<div class='alert alert-danger'>";
            echo "<strong>Error:</strong> " . $e->getMessage();
            echo "</div>";
        }
    }
    
    // Listar pedidos para test
    $stmt = $pdo->query("
        SELECT p.id_pedido, p.nro_pedido, p.total, e.nombre_estado
        FROM tb_pedidos p
        INNER JOIN tb_estados e ON p.id_estado = e.id_estado
        WHERE p.estado_registro = 'ACTIVO'
        ORDER BY p.fecha_pedido DESC
        LIMIT 5
    ");
    $pedidosTest = $stmt->fetchAll();
    
    if (!empty($pedidosTest)) {
        echo "<form method='POST'>";
        echo "<div class='form-group'>";
        echo "<label>Selecciona un pedido para generar venta manualmente:</label>";
        echo "<select name='id_pedido_test' class='form-control'>";
        foreach ($pedidosTest as $p) {
            echo "<option value='{$p['id_pedido']}'>";
            echo "#{$p['nro_pedido']} - S/ {$p['total']} - Estado: {$p['nombre_estado']}";
            echo "</option>";
        }
        echo "</select>";
        echo "</div>";
        echo "<button type='submit' name='test_generar' class='btn btn-warning'>Generar Venta Manualmente</button>";
        echo "</form>";
    }
    
    echo "</div></div>";
    ?>
    
    <a href="../../dashboard.php" class="btn btn-secondary">Volver</a>
</div>
</body>
</html>
