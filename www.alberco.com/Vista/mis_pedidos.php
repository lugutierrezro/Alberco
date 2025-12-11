<?php
require_once(__DIR__ . "/../app/init.php");
require_once(__DIR__ . "/../Services/auth_cliente.php");

$auth = getAuthCliente();
$auth->requerirAuth();

$clienteActual = $auth->getClienteActual();
$pedidoModel = new Pedido();
$clienteModel = new Cliente();

// Obtener historial de pedidos
$sql = "SELECT p.*, e.nombre_estado, e.color
        FROM tb_pedidos p
        INNER JOIN tb_estados e ON p.id_estado = e.id_estado
        WHERE p.id_cliente = :cliente_id
        AND p.estado_registro = 'ACTIVO'
        ORDER BY p.fecha_pedido DESC
        LIMIT 50";

$pedidos = $pedidoModel->query($sql, [':cliente_id' => $clienteActual['id']]);

include '../includes/header.php';
?>

<link rel="stylesheet" href="css/cliente.css">

<div class="content-wrapper">
    <div class="portal-header fade-in">
        <div class="container-fluid">
            <h1><i class="fas fa-history"></i> Mis Pedidos</h1>
            <p class="subtitle">Historial completo de tus pedidos</p>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="mb-3">
                        <a href="perfil_cliente.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Perfil
                        </a>
                        <a href="menu.php" class="btn btn-primary float-right">
                            <i class="fas fa-plus"></i> Hacer un Nuevo Pedido
                        </a>
                    </div>

                    <div class="card-custom fade-in">
                        <div class="card-header-custom">
                            <h3><i class="fas fa-list"></i> Historial de Pedidos</h3>
                        </div>

                        <?php if (empty($pedidos)): ?>
                            <div class="empty-state">
                                <i class="fas fa-receipt"></i>
                                <h3>No tienes pedidos aún</h3>
                                <p>Realiza tu primer pedido y aparecerá aquí</p>
                                <a href="menu.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-utensils"></i> Ver Menú
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($pedidos as $pedido): 
                                $detalles = $pedidoModel->getDetallePedido($pedido['id_pedido']);
                            ?>
                                <div class="pedido-item">
                                    <div class="pedido-header">
                                        <div>
                                            <div class="pedido-numero">
                                                <i class="fas fa-hashtag"></i> <?= htmlspecialchars($pedido['numero_comanda']) ?>
                                            </div>
                                            <div class="pedido-fecha">
                                                <i class="far fa-calendar"></i> 
                                                <?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="badge-custom badge-<?= strtolower(str_replace(' ', '', $pedido['nombre_estado'])) ?>">
                                                <?= htmlspecialchars($pedido['nombre_estado']) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="pedido-body">
                                        <div class="mb-2">
                                            <strong><i class="fas fa-concierge-bell"></i> Tipo:</strong> 
                                            <?php
                                            $tipos = [
                                                'delivery' => 'Delivery',
                                                'para_llevar' => 'Para Llevar',
                                                'mesa' => 'Consumo en Local'
                                            ];
                                            echo $tipos[$pedido['tipo_pedido']] ?? $pedido['tipo_pedido'];
                                            ?>
                                        </div>

                                        <?php if (!empty($detalles)): ?>
                                            <div class="mb-2">
                                                <strong><i class="fas fa-shopping-basket"></i> Productos:</strong>
                                                <ul class="pedido-productos">
                                                    <?php foreach ($detalles as $detalle): ?>
                                                        <li>
                                                            <?= $detalle['cantidad'] ?>x 
                                                            <?= htmlspecialchars($detalle['producto_nombre']) ?> 
                                                            - S/ <?= number_format($detalle['precio_unitario'] * $detalle['cantidad'], 2) ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($pedido['observaciones'])): ?>
                                            <div class="mb-2">
                                                <strong><i class="fas fa-comment"></i> Observaciones:</strong>
                                                <p class="text-muted mb-0"><?= htmlspecialchars($pedido['observaciones']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="pedido-footer">
                                        <div class="pedido-total">
                                            Total: S/ <?= number_format($pedido['total'], 2) ?>
                                        </div>
                                        <div>
                                            <?php if ($pedido['id_estado'] != 5 && $pedido['id_estado'] != 6): ?>
                                                <a href="seguimiento_pedido.php?id=<?= $pedido['numero_comanda'] ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-map-marked-alt"></i> Seguir Pedido
                                                </a>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="verDetalle(<?= $pedido['id_pedido'] ?>)">
                                                <i class="fas fa-eye"></i> Ver Detalle
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function verDetalle(pedidoId) {
    // Aquí podrías implementar un modal con más detalles
    Swal.fire({
        title: 'Detalle del Pedido',
        text: 'Funcionalidad de detalle completo en desarrollo',
        icon: 'info'
    });
}
</script>

<?php include '../includes/footer.php'; ?>
