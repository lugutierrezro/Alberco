<?php
/**
 * Test: Verificar Integración Pedido → Venta
 * Simula el flujo completo de crear un pedido y marcarlorejo como entregado
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
    <title>Test Pedido → Venta</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container mt-5">
    <h1><i class="fas fa-vial"></i> Test: Integración Pedido → Venta</h1>
    <p class="lead">Este test verifica que al marcar un pedido como "Entregado", se genere automáticamente la venta.</p>
    
    <?php
    if (isset($_POST['marcar_entregado'])) {
        try {
            $id_pedido = (int)$_POST['id_pedido'];
            
            // Obtener estado "Entregado" (normalmente id = 5)
            $stmt = $pdo->query("SELECT id_estado FROM tb_estados WHERE nombre_estado = 'Entregado' LIMIT 1");
            $estado = $stmt->fetch();
            $id_estado_entregado = $estado ? $estado['id_estado'] : 5;
            
            echo "<div class='alert alert-info'>";
            echo "<h5>Marcando pedido #$id_pedido como Entregado...</h5>";
            echo "</div>";
            
            // Simular actualización de estado
            $_POST['id_pedido'] = $id_pedido;
            $_POST['id_estado'] = $id_estado_entregado;
            $_POST['observaciones'] = 'Test automático - Pedido entregado';
            $_SESSION['id_usuario'] = 1; // Usuario test
            
            // Incluir el controlador
            ob_start();
            include __DIR__ . '/../../controllers/pedidos/actualizar_estado.php';
            $output = ob_get_clean();
            
            // Verificar si se creó la venta
            $stmt = $pdo->prepare("SELECT * FROM tb_ventas WHERE id_pedido = ?");
            $stmt->execute([$id_pedido]);
            $venta = $stmt->fetch();
            
            if ($venta) {
                echo "<div class='alert alert-success'>";
                echo "<h4>✅ ¡ÉXITO! Venta Generada Automáticamente</h4>";
                echo "<p><strong>ID Venta:</strong> {$venta['id_venta']}</p>";
                echo "<p><strong>Número:</strong> {$venta['nro_venta']}</p>";
                echo "<p><strong>Total:</strong> S/ " . number_format($venta['total'], 2) . "</p>";
                echo "<p><strong>Estado:</strong> {$venta['estado_venta']}</p>";
                echo "</div>";
                
                // Verificar detalles
                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tb_detalle_ventas WHERE id_venta = ?");
                $stmt->execute([$venta['id_venta']]);
                $detalles = $stmt->fetch()['total'];
                
                echo "<div class='alert alert-info'>";
                echo "<p><strong>Detalles de venta:</strong> $detalles productos</p>";
                echo "</div>";
                
            } else {
                echo "<div class='alert alert-warning'>";
                echo "<h4>⚠️ No se generó la venta</h4>";
                echo "<p>Puede que el pedido ya tuviera una venta o que ocurrió un error.</p>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>";
            echo "<h4>Error</h4>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
    }
    ?>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3>Pedidos Disponibles para Probar</h3>
        </div>
        <div class="card-body">
            <?php
            // Listar pedidos que NO están entregados y NO tienen venta
            $sql = "SELECT p.id_pedido, p.nro_pedido, p.total, e.nombre_estado,
                           c.nombres, c.apellidos,
                           (SELECT COUNT(*) FROM tb_ventas WHERE id_pedido = p.id_pedido) as tiene_venta
                    FROM tb_pedidos p
                    INNER JOIN tb_estados e ON p.id_estado = e.id_estado
                    LEFT JOIN tb_clientes c ON p.id_cliente = c.id_cliente
                    WHERE p.estado_registro = 'ACTIVO'
                    ORDER BY p.fecha_pedido DESC
                    LIMIT 10";
            
            $stmt = $pdo->query($sql);
            $pedidos = $stmt->fetchAll();
            
            if (empty($pedidos)) {
                echo "<div class='alert alert-warning'>No hay pedidos en el sistema para probar.</div>";
                echo "<a href='../pedidos/create.php' class='btn btn-success'>Crear Pedido</a>";
            } else {
                echo "<table class='table table-bordered'>";
                echo "<thead>";
                echo "<tr>";
                echo "<th>ID</th>";
                echo "<th>Número</th>";
                echo "<th>Cliente</th>";
                echo "<th>Total</th>";
                echo "<th>Estado</th>";
                echo "<th>Tiene Venta</th>";
                echo "<th>Acción</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                
                foreach ($pedidos as $pedido) {
                    $tiene_venta = $pedido['tiene_venta'] > 0;
                    $puede_marcar = !$tiene_venta && $pedido['nombre_estado'] !== 'Entregado';
                    
                    echo "<tr>";
                    echo "<td>{$pedido['id_pedido']}</td>";
                    echo "<td>{$pedido['nro_pedido']}</td>";
                    echo "<td>{$pedido['nombres']} {$pedido['apellidos']}</td>";
                    echo "<td>S/ " . number_format($pedido['total'], 2) . "</td>";
                    echo "<td><span class='badge badge-info'>{$pedido['nombre_estado']}</span></td>";
                    echo "<td>";
                    if ($tiene_venta) {
                        echo "<span class='badge badge-success'><i class='fas fa-check'></i> Sí</span>";
                    } else {
                        echo "<span class='badge badge-secondary'><i class='fas fa-times'></i> No</span>";
                    }
                    echo "</td>";
                    echo "<td>";
                    
                    if ($puede_marcar) {
                        echo "<form method='POST' style='display:inline;'>";
                        echo "<input type='hidden' name='id_pedido' value='{$pedido['id_pedido']}'>";
                        echo "<button type='submit' name='marcar_entregado' class='btn btn-sm btn-success'>";
                        echo "<i class='fas fa-check-circle'></i> Marcar Entregado";
                        echo "</button>";
                        echo "</form>";
                    } else {
                        echo "<span class='text-muted'>-</span>";
                    }
                    
                    echo "</td>";
                    echo "</tr>";
                }
                
                echo "</tbody>";
                echo "</table>";
            }
            ?>
        </div>
    </div>
    
    <hr>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <h4>Verificar Reportes</h4>
            <p>Después de generar ventas, verifica que aparezcan en:</p>
            <a href="../reportes/index.php" class="btn btn-primary btn-block" target="_blank">
                <i class="fas fa-chart-line"></i> Ver Reportes
            </a>
        </div>
        <div class="col-md-6">
            <h4>Diagnóstico del Sistema</h4>
            <p>Ejecuta el diagnóstico para verificar el estado general:</p>
            <a href="diagnostico_sistema.php" class="btn btn-info btn-block">
                <i class="fas fa-heartbeat"></i> Ejecutar Diagnóstico
            </a>
        </div>
    </div>
    
    <a href="../../dashboard.php" class="btn btn-secondary mt-3">Volver al Dashboard</a>
</div>
</body>
</html>
