<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Obtener pedidos pendientes y en preparación
try {
    $sqlPedidos = "SELECT p.*, 
                          pd.id_detalle, pd.cantidad, pd.precio_unitario,
                          pr.nombre as producto_nombre,
                          c.nombres, c.apellidos,
                          m.numero_mesa
                   FROM tb_pedidos p
                   INNER JOIN tb_pedido_detalles pd ON p.id_pedido = pd.id_pedido
                   INNER JOIN tb_almacen pr ON pd.id_producto = pr.id_producto
                   INNER JOIN tb_clientes c ON p.id_cliente = c.id_cliente
                   LEFT JOIN tb_mesas m ON p.id_mesa = m.id_mesa
                   WHERE p.estado IN ('PENDIENTE', 'EN_PREPARACION')
                   AND p.estado_registro = 'ACTIVO'
                   ORDER BY p.fecha_pedido ASC";
    
    $stmtPedidos = $pdo->prepare($sqlPedidos);
    $stmtPedidos->execute();
    $pedidos_cocina = $stmtPedidos->fetchAll(PDO::FETCH_ASSOC);
    
    // Agrupar por pedido
    $pedidos_agrupados = [];
    foreach ($pedidos_cocina as $item) {
        $id_pedido = $item['id_pedido'];
        if (!isset($pedidos_agrupados[$id_pedido])) {
            $pedidos_agrupados[$id_pedido] = [
                'info' => $item,
                'productos' => []
            ];
        }
        $pedidos_agrupados[$id_pedido]['productos'][] = $item;
    }
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $pedidos_agrupados = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vista de Cocina</title>
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/dist/css/adminlte.min.css">
    <style>
        body {
            background: #2c3e50;
            font-family: Arial, sans-serif;
        }
        .pedido-ticket {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            transition: all 0.3s;
        }
        .pedido-ticket:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
        }
        .pedido-header {
            border-bottom: 3px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .producto-item {
            font-size: 1.2rem;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .tiempo-alerta {
            font-size: 2rem;
            font-weight: bold;
        }
        .tiempo-critico { color: #e74c3c; animation: parpadeo 1s infinite; }
        .tiempo-advertencia { color: #f39c12; }
        .tiempo-normal { color: #27ae60; }
        
        @keyframes parpadeo {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <div class="row mb-4">
            <div class="col-md-12 text-center">
                <h1 class="text-white">
                    <i class="fas fa-utensils"></i> VISTA DE COCINA
                </h1>
                <h3 class="text-white" id="reloj"></h3>
            </div>
        </div>

        <div class="row">
            <?php foreach ($pedidos_agrupados as $id_pedido => $pedido): 
                $info = $pedido['info'];
                $productos = $pedido['productos'];
                
                $fecha = new DateTime($info['fecha_pedido']);
                $ahora = new DateTime();
                $diff = $ahora->diff($fecha);
                $minutos = ($diff->h * 60) + $diff->i;
                
                $claseTiempo = 'tiempo-normal';
                if ($minutos > 30) $claseTiempo = 'tiempo-critico';
                elseif ($minutos > 15) $claseTiempo = 'tiempo-advertencia';
            ?>
            <div class="col-md-4">
                <div class="pedido-ticket">
                    <div class="pedido-header">
                        <h2 class="mb-0">
                            <?php echo htmlspecialchars($info['numero_comanda'] ?? 'PED-' . $id_pedido); ?>
                        </h2>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge badge-lg badge-<?php echo $info['estado'] === 'PENDIENTE' ? 'warning' : 'info'; ?>">
                                <?php echo $info['estado']; ?>
                            </span>
                            <span class="tiempo-alerta <?php echo $claseTiempo; ?>">
                                <?php echo $minutos; ?> min
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Cliente:</strong> <?php echo htmlspecialchars($info['nombres']); ?><br>
                        <?php if ($info['tipo_pedido'] === 'MESA'): ?>
                            <span class="badge badge-dark badge-lg">MESA <?php echo $info['numero_mesa']; ?></span>
                        <?php else: ?>
                            <span class="badge badge-info badge-lg"><?php echo $info['tipo_pedido']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="productos-lista">
                        <?php foreach ($productos as $prod): ?>
                        <div class="producto-item">
                            <strong><?php echo $prod['cantidad']; ?>x</strong>
                            <?php echo htmlspecialchars($prod['producto_nombre']); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($info['observaciones']): ?>
                    <div class="alert alert-warning mt-3">
                        <strong>Nota:</strong> <?php echo htmlspecialchars($info['observaciones']); ?>
                    </div>
                    <?php endif; ?>

                    <div class="mt-3">
                        <?php if ($info['estado'] === 'PENDIENTE'): ?>
                        <button class="btn btn-info btn-lg btn-block" 
                                onclick="cambiarEstado(<?php echo $id_pedido; ?>, 'EN_PREPARACION')">
                            <i class="fas fa-play"></i> INICIAR PREPARACIÓN
                        </button>
                        <?php else: ?>
                        <button class="btn btn-success btn-lg btn-block" 
                                onclick="cambiarEstado(<?php echo $id_pedido; ?>, 'LISTO')">
                            <i class="fas fa-check"></i> MARCAR LISTO
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (count($pedidos_agrupados) === 0): ?>
            <div class="col-md-12">
                <div class="text-center text-white p-5">
                    <i class="fas fa-check-circle fa-5x mb-3"></i>
                    <h2>¡Todo al día!</h2>
                    <p>No hay pedidos pendientes en cocina</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <form id="formEstado" action="../../controllers/pedido/actualizar_estado.php" method="post" style="display: none;">
        <input type="hidden" name="id_pedido" id="id_pedido_form">
        <input type="hidden" name="estado" id="estado_form">
        <input type="hidden" name="redirect" value="cocina">
    </form>

    <script src="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/plugins/jquery/jquery.min.js"></script>
    <script>
        function cambiarEstado(id, estado) {
            document.getElementById('id_pedido_form').value = id;
            document.getElementById('estado_form').value = estado;
            document.getElementById('formEstado').submit();
        }

        // Reloj
        function actualizarReloj() {
            const ahora = new Date();
            document.getElementById('reloj').textContent = ahora.toLocaleTimeString();
        }
        setInterval(actualizarReloj, 1000);
        actualizarReloj();

        // Auto-refresh cada 5 segundos
        setTimeout(function() {
            location.reload();
        }, 5000);

        // Sonido de notificación
        const audio = new Audio('<?php echo URL_BASE; ?>/assets/sounds/notification.mp3');
        // audio.play().catch(() => {});
    </script>
</body>
</html>
