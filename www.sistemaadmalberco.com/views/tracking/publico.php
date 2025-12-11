<?php
include ('../../services/database/config.php');

// Obtener código de seguimiento
$codigo_seguimiento = $_GET['codigo'] ?? '';

$pedido_encontrado = null;
if ($codigo_seguimiento) {
    try {
        $sql = "SELECT p.*, 
                       c.nombres, c.apellidos,
                       m.numero_mesa
                FROM tb_pedidos p
                INNER JOIN tb_clientes c ON p.id_cliente = c.id_cliente
                LEFT JOIN tb_mesas m ON p.id_mesa = m.id_mesa
                WHERE p.codigo_seguimiento = ? AND p.estado_registro = 'ACTIVO'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigo_seguimiento]);
        $pedido_encontrado = $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seguimiento de Pedido - Pollería Alberco</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/dist/css/adminlte.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .tracking-container {
            max-width: 600px;
            width: 100%;
        }
        .tracking-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 30px;
        }
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-section img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid #ff6b35;
        }
        .progress-tracker {
            position: relative;
            padding: 30px 0;
        }
        .progress-step {
            position: relative;
            text-align: center;
            margin-bottom: 40px;
        }
        .progress-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        .progress-step.active .progress-icon {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            animation: pulse 2s infinite;
        }
        .progress-step.completed .progress-icon {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
        }
        .progress-step.pending .progress-icon {
            background: #ecf0f1;
            color: #95a5a6;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 107, 53, 0.7); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(255, 107, 53, 0); }
        }
        .search-form {
            margin-bottom: 30px;
        }
        .pedido-info {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .tiempo-estimado {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="tracking-container">
        <div class="tracking-card">
            <div class="logo-section">
                <img src="<?php echo URL_BASE; ?>/assets/public/images/Logo.png" alt="Logo Alberco">
                <h2 class="mt-3">Pollería Alberco</h2>
                <p class="text-muted">Seguimiento de Pedido</p>
            </div>

            <?php if (!$codigo_seguimiento || !$pedido_encontrado): ?>
                <!-- Formulario de Búsqueda -->
                <div class="search-form">
                    <form action="" method="get">
                        <div class="input-group input-group-lg">
                            <input type="text" 
                                   name="codigo" 
                                   class="form-control" 
                                   placeholder="Ingrese su código de seguimiento"
                                   required
                                   value="<?php echo htmlspecialchars($codigo_seguimiento); ?>">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <?php if ($codigo_seguimiento && !$pedido_encontrado): ?>
                    <div class="alert alert-danger mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        No se encontró ningún pedido con ese código.
                    </div>
                    <?php endif; ?>
                </div>

                <div class="text-center text-muted">
                    <i class="fas fa-info-circle"></i>
                    <p>Ingrese el código que recibió al realizar su pedido</p>
                </div>

            <?php else: ?>
                <!-- Información del Pedido -->
                <div class="pedido-info">
                    <div class="row">
                        <div class="col-6">
                            <strong>Pedido:</strong><br>
                            <?php echo htmlspecialchars($pedido_encontrado['numero_comanda'] ?? 'PED-' . $pedido_encontrado['id_pedido']); ?>
                        </div>
                        <div class="col-6 text-right">
                            <strong>Total:</strong><br>
                            S/ <?php echo number_format($pedido_encontrado['total'], 2); ?>
                        </div>
                    </div>
                    <hr style="border-color: rgba(255,255,255,0.3);">
                    <div class="row">
                        <div class="col-12">
                            <strong>Cliente:</strong> <?php echo htmlspecialchars($pedido_encontrado['nombres'] . ' ' . ($pedido_encontrado['apellidos'] ?? '')); ?><br>
                            <strong>Tipo:</strong> <?php echo htmlspecialchars($pedido_encontrado['tipo_pedido']); ?>
                            <?php if ($pedido_encontrado['tipo_pedido'] === 'MESA'): ?>
                                - Mesa <?php echo $pedido_encontrado['numero_mesa']; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tiempo Estimado -->
                <?php if ($pedido_encontrado['estado'] !== 'ENTREGADO' && $pedido_encontrado['estado'] !== 'CANCELADO'): ?>
                <div class="tiempo-estimado">
                    <i class="fas fa-clock text-warning"></i>
                    <strong>Tiempo estimado de preparación:</strong> 
                    <?php
                    $fecha = new DateTime($pedido_encontrado['fecha_pedido']);
                    $ahora = new DateTime();
                    $diff = $ahora->diff($fecha);
                    $transcurrido = ($diff->h * 60) + $diff->i;
                    $estimado = 30; // minutos estimados
                    $restante = max(0, $estimado - $transcurrido);
                    echo $restante . ' minutos';
                    ?>
                </div>
                <?php endif; ?>

                <!-- Seguimiento del Pedido -->
                <div class="progress-tracker">
                    <?php
                    $estados = [
                        'PENDIENTE' => ['icon' => 'fa-clock', 'title' => 'Pedido Recibido', 'desc' => 'Hemos recibido tu pedido'],
                        'EN_PREPARACION' => ['icon' => 'fa-utensils', 'title' => 'En Preparación', 'desc' => 'Tu pedido se está preparando'],
                        'LISTO' => ['icon' => 'fa-check', 'title' => 'Listo', 'desc' => 'Tu pedido está listo'],
                        'EN_CAMINO' => ['icon' => 'fa-motorcycle', 'title' => 'En Camino', 'desc' => 'El pedido va en camino'],
                        'ENTREGADO' => ['icon' => 'fa-home', 'title' => 'Entregado', 'desc' => '¡Disfruta tu pedido!']
                    ];

                    $estado_actual = $pedido_encontrado['estado'];
                    $orden = ['PENDIENTE', 'EN_PREPARACION', 'LISTO'];
                    
                    if ($pedido_encontrado['tipo_pedido'] === 'DELIVERY') {
                        $orden[] = 'EN_CAMINO';
                    }
                    $orden[] = 'ENTREGADO';

                    $index_actual = array_search($estado_actual, $orden);
                    
                    foreach ($orden as $index => $estado):
                        $info = $estados[$estado];
                        $clase = 'pending';
                        if ($index < $index_actual) $clase = 'completed';
                        elseif ($index === $index_actual) $clase = 'active';
                    ?>
                    <div class="progress-step <?php echo $clase; ?>">
                        <div class="progress-icon">
                            <i class="fas <?php echo $info['icon']; ?>"></i>
                        </div>
                        <h5><?php echo $info['title']; ?></h5>
                        <p class="text-muted mb-0"><?php echo $info['desc']; ?></p>
                        <?php if ($clase === 'active'): ?>
                            <small class="text-primary">
                                <i class="fas fa-circle"></i> Ahora
                            </small>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($pedido_encontrado['observaciones']): ?>
                <div class="alert alert-info">
                    <strong><i class="fas fa-comment"></i> Nota del pedido:</strong><br>
                    <?php echo htmlspecialchars($pedido_encontrado['observaciones']); ?>
                </div>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <a href="publico.php" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Buscar Otro Pedido
                    </a>
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-sync"></i> Actualizar Estado
                    </button>
                </div>

                <?php if ($pedido_encontrado['estado'] === 'ENTREGADO'): ?>
                <div class="text-center mt-4">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <h4>¡Pedido Entregado!</h4>
                        <p>Esperamos que disfrutes tu comida. ¡Gracias por tu preferencia!</p>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="text-center mt-4 text-muted">
                <small>
                    <i class="fas fa-phone"></i> ¿Problemas? Llama al: (01) 234-5678
                </small>
            </div>
        </div>
    </div>

    <script src="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/plugins/jquery/jquery.min.js"></script>
    <script>
        // Auto-refresh cada 30 segundos si hay un pedido activo
        <?php if ($pedido_encontrado && $pedido_encontrado['estado'] !== 'ENTREGADO' && $pedido_encontrado['estado'] !== 'CANCELADO'): ?>
        setTimeout(function() {
            location.reload();
        }, 30000);
        <?php endif; ?>
    </script>
</body>
</html>
