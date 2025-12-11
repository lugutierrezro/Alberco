<?php
/**
 * Test: Crear Venta de Prueba
 * Este script crea una venta de prueba para verificar que el sistema funciona
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../services/database/config.php';

// Obtener conexión
$pdo = getDB();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Venta de Prueba</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1>Crear Venta de Prueba</h1>
    
    <?php
    try {
        // Verificar si ya hay ventas
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_ventas");
        $count = $stmt->fetch()['total'];
        
        echo "<div class='alert alert-info'>Ventas actuales en sistema: <strong>$count</strong></div>";
        
        if (isset($_POST['crear_venta'])) {
            $pdo->beginTransaction();
            
            // 1. Obtener primer cliente
            $stmt = $pdo->query("SELECT id_cliente FROM tb_clientes WHERE estado_registro = 'ACTIVO' LIMIT 1");
            $cliente = $stmt->fetch();
            
            if (!$cliente) {
                throw new Exception("No hay clientes en el sistema");
            }
            
            // 2. Obtener primer usuario
            $stmt = $pdo->query("SELECT id_usuario FROM tb_usuarios WHERE estado_registro = 'ACTIVO' LIMIT 1");
            $usuario = $stmt->fetch();
            
            if (!$usuario) {
                throw new Exception("No hay usuarios en el sistema");
            }
            
            // 3. Obtener tipo de comprobante (Boleta)
            $stmt = $pdo->query("SELECT id_tipo_comprobante FROM tb_tipo_comprobante WHERE nombre_tipo = 'Boleta' LIMIT 1");
            $comprobante = $stmt->fetch();
            
            if (!$comprobante) {
                throw new Exception("No hay tipos de comprobante configurados");
            }
            
            // 4. Obtener método de pago (Efectivo)
            $stmt = $pdo->query("SELECT id_metodo FROM tb_metodos_pago WHERE nombre_metodo = 'Efectivo' LIMIT 1");
            $metodo = $stmt->fetch();
            
            if (!$metodo) {
                throw new Exception("No hay métodos de pago configurados");
            }
            
            // 5. Obtener productos disponibles
            $stmt = $pdo->query("SELECT id_producto, nombre, precio_venta FROM tb_almacen WHERE estado_registro = 'ACTIVO' AND stock > 0 LIMIT 3");
            $productos = $stmt->fetchAll();
            
            if (empty($productos)) {
                throw new Exception("No hay productos disponibles");
            }
            
            // 6. Calcular totales
            $subtotal = 0;
            $igv = 0;
            $descuento = 0;
            $detalles = [];
            
            foreach ($productos as $producto) {
                $cantidad = rand(1, 3);
                $precio = $producto['precio_venta'];
                $subtotal_item = $cantidad * $precio;
                $subtotal += $subtotal_item;
                
                $detalles[] = [
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotal_item
                ];
            }
            
            $igv = $subtotal * 0.18;
            $total = $subtotal + $igv;
            
            // 7. Obtener siguiente número de venta
            $stmt = $pdo->query("SELECT COALESCE(MAX(nro_venta), 0) + 1 as siguiente FROM tb_ventas");
            $nro_venta = $stmt->fetch()['siguiente'];
            
            // 8. Insertar venta
            $sql = "INSERT INTO tb_ventas (
                nro_venta, serie_comprobante, numero_comprobante,
                id_cliente, id_usuario, id_tipo_comprobante, id_metodo_pago,
                subtotal, igv, descuento, total,
                monto_recibido, vuelto, estado_venta
            ) VALUES (
                :nro_venta, 'B001', :numero_comprobante,
                :id_cliente, :id_usuario, :id_tipo_comprobante, :id_metodo_pago,
                :subtotal, :igv, :descuento, :total,
                :monto_recibido, :vuelto, 'completada'
            )";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nro_venta' => $nro_venta,
                ':numero_comprobante' => str_pad($nro_venta, 8, '0', STR_PAD_LEFT),
                ':id_cliente' => $cliente['id_cliente'],
                ':id_usuario' => $usuario['id_usuario'],
                ':id_tipo_comprobante' => $comprobante['id_tipo_comprobante'],
                ':id_metodo_pago' => $metodo['id_metodo'],
                ':subtotal' => $subtotal,
                ':igv' => $igv,
                ':descuento' => $descuento,
                ':total' => $total,
                ':monto_recibido' => ceil($total),
                ':vuelto' => ceil($total) - $total
            ]);
            
            $id_venta = $pdo->lastInsertId();
            
            // 9. Insertar detalles
            $sql = "INSERT INTO tb_detalle_ventas (
                id_venta, id_producto, cantidad, precio_unitario, subtotal
            ) VALUES (
                :id_venta, :id_producto, :cantidad, :precio_unitario, :subtotal
            )";
            
            $stmt = $pdo->prepare($sql);
            foreach ($detalles as $detalle) {
                $stmt->execute([
                    ':id_venta' => $id_venta,
                    ':id_producto' => $detalle['id_producto'],
                    ':cantidad' => $detalle['cantidad'],
                    ':precio_unitario' => $detalle['precio_unitario'],
                    ':subtotal' => $detalle['subtotal']
                ]);
            }
            
            // 10. Actualizar stock
            foreach ($detalles as $detalle) {
                $pdo->prepare("UPDATE tb_almacen SET stock = stock - :cantidad WHERE id_producto = :id_producto")
                    ->execute([
                        ':cantidad' => $detalle['cantidad'],
                        ':id_producto' => $detalle['id_producto']
                    ]);
            }
            
            $pdo->commit();
            
            echo "<div class='alert alert-success'>";
            echo "<h4>✅ Venta Creada Exitosamente</h4>";
            echo "<p><strong>ID Venta:</strong> $id_venta</p>";
            echo "<p><strong>Número:</strong> $nro_venta</p>";
            echo "<p><strong>Total:</strong> S/ " . number_format($total, 2) . "</p>";
            echo "<p><strong>Productos:</strong> " . count($detalles) . "</p>";
            echo "</div>";
            
            echo "<a href='../reportes/index.php' class='btn btn-primary'>Ver Reportes</a> ";
            echo "<a href='diagnostico_sistema.php' class='btn btn-info'>Ejecutar Diagnóstico</a>";
            
        } else {
            ?>
            <form method="POST">
                <button type="submit" name="crear_venta" class="btn btn-success btn-lg">
                    <i class="fas fa-plus"></i> Crear Venta de Prueba
                </button>
            </form>
            <?php
        }
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "<div class='alert alert-danger'>";
        echo "<h4>Error</h4>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
    ?>
    
    <hr>
    <h3>Estado del Sistema</h3>
    <table class="table table-bordered">
        <?php
        $tablas = [
            'tb_clientes' => 'Clientes',
            'tb_almacen' => 'Productos',
            'tb_usuarios' => 'Usuarios',
            'tb_metodos_pago' => 'Métodos de Pago',
            'tb_tipo_comprobante' => 'Tipos de Comprobante',
            'tb_ventas' => 'Ventas'
        ];
        
        foreach ($tablas as $tabla => $nombre) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabla");
            $total = $stmt->fetch()['total'];
            $color = $total > 0 ? 'success' : 'danger';
            echo "<tr>";
            echo "<td><strong>$nombre</strong></td>";
            echo "<td><span class='badge badge-$color'>$total registros</span></td>";
            echo "</tr>";
        }
        ?>
    </table>
    
    <a href="../../dashboard.php" class="btn btn-secondary">Volver al Dashboard</a>
</div>
</body>
</html>
