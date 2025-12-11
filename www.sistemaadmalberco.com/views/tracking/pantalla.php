<?php
include ('../../services/database/config.php');

// Obtener pedidos listos
try {
    $sql = "SELECT p.*, 
                   c.nombres, c.apellidos,
                   m.numero_mesa
            FROM tb_pedidos p
            INNER JOIN tb_clientes c ON p.id_cliente = c.id_cliente
            LEFT JOIN tb_mesas m ON p.id_mesa = m.id_mesa
            WHERE p.estado = 'LISTO'
            AND p.estado_registro = 'ACTIVO'
            ORDER BY p.fecha_pedido ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $pedidos_listos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $pedidos_listos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Órdenes Listas - Pollería Alberco</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700&display=fallback">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/assets/public/templeates/AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            font-family: 'Source Sans Pro', sans-serif;
            color: white;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        
        .header h1 {
            font-size: 4rem;
            font-weight: 700;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.3);
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 2rem;
            opacity: 0.9;
        }
        
        .pedidos-container {
            padding: 40px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 30px;
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }
        
        .pedido-card {
            background: white;
            color: #333;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: slideIn 0.5s ease-out;
            position: relative;
            overflow: hidden;
        }
        
        .pedido-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        }
        
        .numero-orden {
            font-size: 4rem;
            font-weight: 700;
            color: #ff6b35;
            text-align: center;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            animation: pulse 2s infinite;
        }
        
        .info-pedido {
            font-size: 1.8rem;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .tipo-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 1.5rem;
            font-weight: 600;
            margin: 10px 0;
        }
        
        .badge-mesa {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
        }
        
        .badge-llevar {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }
        
        .badge-delivery {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }
        
        .tiempo {
            background: #f39c12;
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-size: 1.6rem;
            font-weight: 600;
            margin-top: 20px;
        }
        
        .vacio {
            grid-column: 1 / -1;
            text-align: center;
            padding: 100px 20px;
        }
        
        .vacio i {
            font-size: 8rem;
            margin-bottom: 30px;
            opacity: 0.3;
        }
        
        .vacio h2 {
            font-size: 3rem;
            opacity: 0.7;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        .reloj {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0,0,0,0.5);
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 2rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="reloj" id="reloj"></div>
    
    <div class="header">
        <h1><i class="fas fa-drumstick-bite"></i> POLLERÍA ALBERCO</h1>
        <p>ÓRDENES LISTAS PARA RECOGER</p>
    </div>
    
    <div class="pedidos-container">
        <?php if (count($pedidos_listos) > 0): ?>
            <?php foreach ($pedidos_listos as $pedido): 
                $badgeClass = 'badge-llevar';
                $tipoTexto = 'PARA LLEVAR';
                $icono = 'fa-shopping-bag';
                
                if ($pedido['tipo_pedido'] === 'MESA') {
                    $badgeClass = 'badge-mesa';
                    $tipoTexto = 'MESA ' . $pedido['numero_mesa'];
                    $icono = 'fa-utensils';
                } elseif ($pedido['tipo_pedido'] === 'DELIVERY') {
                    $badgeClass = 'badge-delivery';
                    $tipoTexto = 'DELIVERY';
                    $icono = 'fa-motorcycle';
                }
            ?>
            <div class="pedido-card">
                <div class="numero-orden">
                    <?php echo htmlspecialchars($pedido['numero_comanda'] ?? 'PED-' . $pedido['id_pedido']); ?>
                </div>
                
                <div class="info-pedido">
                    <i class="fas <?php echo $icono; ?>"></i>
                    <div class="tipo-badge <?php echo $badgeClass; ?>">
                        <?php echo $tipoTexto; ?>
                    </div>
                </div>
                
                <div class="info-pedido">
                    <strong><?php echo htmlspecialchars($pedido['nombres'] . ' ' . ($pedido['apellidos'] ?? '')); ?></strong>
                </div>
                
                <div class="tiempo">
                    <i class="fas fa-clock"></i>
                    LISTO HACE 
                    <?php
                    $fecha = new DateTime($pedido['fyh_actualizacion'] ?? $pedido['fecha_pedido']);
                    $ahora = new DateTime();
                    $diff = $ahora->diff($fecha);
                    echo ($diff->h * 60) + $diff->i;
                    ?> MIN
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="vacio">
                <i class="fas fa-check-circle"></i>
                <h2>NO HAY ÓRDENES PENDIENTES</h2>
                <p style="font-size: 2rem; margin-top: 20px; opacity: 0.6;">
                    Todas las órdenes han sido entregadas
                </p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Reloj
        function actualizarReloj() {
            const ahora = new Date();
            const opciones = { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: false 
            };
            document.getElementById('reloj').textContent = ahora.toLocaleTimeString('es-PE', opciones);
        }
        setInterval(actualizarReloj, 1000);
        actualizarReloj();
        
        // Auto-refresh cada 5 segundos
        setTimeout(function() {
            location.reload();
        }, 5000);
        
        // Sonido cuando hay nuevas órdenes
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUKvl8bllGwY5k9n0yHkpBSt+zPLaizsIDGS47OycUQwOT6zn8bllHQU2jdXzzn0sBS1+0/LbiTUIG2m98OScTwwOUKvl8bllGgU5lNn0yHkpBSuBzvLZijYIG2m88OScTgwOUKzl8bllGwU5lNn0yHkpBSuBzvLZijYIG2m88OScTgwNUKzl8bllGwU4lNn0x3kpBSuBzvLZijYIG2m88OScTgwOUKzl8bllHAU4lNn0yHkpBSuBzvLZijYIG2q88OScTgwOUKvl8bllHAU4lNn0yHkpBSuBzvLZizYIGmq88OSbTgwOUKvl8rllHAU4k9n0yHkqBSuBzvLZizUIGmm88OSbTgwNUKvl8rllHAU4k9n0yHkqBSuAzvLZizUIGmm88OSbTQwNUKzl8rllHAU4lNn0yHkqBSuBzvLZizUIGmq88OScTQwNUKvl8bllHAU4lNn0yHkqBSuBzvLZizYIGmq88OScTQwMUKzl8rllGwU4lNn0yXkqBSuBzvLZizYIGmq98OScTQwNUKvl8bllGwU4lNn0yXkqBSuBzvLZizYIGmm98OScTQwNUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIGmm98OScTQwNUKvl8blmHAU4lNn0yHkqBSuBzvLZizYIGmm98OScTQwOUKvl8blmHAU4lNn0yHkqBSuBzvLZizYIGmm88OScTQwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIGmm88OScTQwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIGmm88OScTQwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIGmm88OScTAwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIGmm88OScTAwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIG2m88OScTAwOUKvl8blmGwU3lNn0yHkqBSuBzvLZizYIG2m88OScTAwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIG2m88OScTAwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIG2m88OScTAwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIG2m88OScTAwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIG2m88OScTAwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIG2m88OScTAwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIG2m88OScTAwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIG2m88OScTAwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIG2m88OScTAwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIG2m88OSbTAwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIG2m88OSbTAwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIG2m88OSbTAwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIG2m88OSbTAwOUKvl8blmGwU4lNn0yHkqBSuBzvLZizYIG2m88OSbTAwOUKvl8blmGwU4lNn0yHkqBQ==');
        // audio.play().catch(() => {});
    </script>
</body>
</html>
