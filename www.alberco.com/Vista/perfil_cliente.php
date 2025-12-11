<?php
require_once(__DIR__ . "/../app/init.php");
require_once(__DIR__ . "/../Services/auth_cliente.php");

$auth = getAuthCliente();
$auth->requerirAuth();

$clienteActual = $auth->getClienteActual();
$clienteModel = new Cliente();
$pedidoModel = new Pedido();

// Obtener datos completos del cliente
$cliente = $clienteModel->getById($clienteActual['id']);
$estadisticas = $clienteModel->getEstadisticas($clienteActual['id']);
$pedidosActivos = $clienteModel->getPedidosActivos($clienteActual['id']);

include '../includes/header.php';
?>

<link rel="stylesheet" href="css/cliente.css">

<div class="content-wrapper">
    <!-- Header del Portal -->
    <div class="portal-header fade-in">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>
                        <i class="fas fa-user-circle"></i> 
                        ¡Hola, <?= htmlspecialchars($clienteActual['nombre']) ?>!
                    </h1>
                    <p class="subtitle">Bienvenido a tu portal de cliente</p>
                </div>
                <div class="col-md-4 text-right">
                    <div class="badge-custom badge-<?= strtolower($cliente['tipo_cliente']) ?>">
                        <i class="fas fa-star"></i> Cliente <?= htmlspecialchars($cliente['tipo_cliente']) ?>
                    </div>
                    <div class="mt-2">
                        <strong style="font-size: 1.2rem;">
                            <i class="fas fa-coins"></i> <?= number_format($cliente['puntos_fidelidad'] ?? 0) ?> Puntos
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Estadísticas -->
            <div class="stats-grid fade-in">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="stat-value"><?= $estadisticas['total_compras'] ?? 0 ?></div>
                    <div class="stat-label">Total de Pedidos</div>
                </div>

                <div class="stat-card success">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign" style="color: var(--success-color);"></i>
                    </div>
                    <div class="stat-value">S/ <?= number_format($estadisticas['total_gastado'] ?? 0, 2) ?></div>
                    <div class="stat-label">Total Gastado</div>
                </div>

                <div class="stat-card info">
                    <div class="stat-icon">
                        <i class="fas fa-receipt" style="color: var(--info-color);"></i>
                    </div>
                    <div class="stat-value">S/ <?= number_format($estadisticas['ticket_promedio'] ?? 0, 2) ?></div>
                    <div class="stat-label">Ticket Promedio</div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i class="fas fa-coins" style="color: var(--warning-color);"></i>
                    </div>
                    <div class="stat-value"><?= number_format($cliente['puntos_fidelidad'] ?? 0) ?></div>
                    <div class="stat-label">Puntos Acumulados</div>
                </div>
            </div>

            <div class="row">
                <!-- Pedidos Activos -->
                <div class="col-lg-8">
                    <div class="card-custom fade-in">
                        <div class="card-header-custom">
                            <h3><i class="fas fa-clock"></i> Pedidos Activos</h3>
                        </div>

                        <?php if (empty($pedidosActivos)): ?>
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <h3>No tienes pedidos activos</h3>
                                <p>Tus pedidos en proceso aparecerán aquí</p>
                                <a href="menu.php" class="btn btn-primary">
                                    <i class="fas fa-utensils"></i> Ver Menú
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($pedidosActivos as $pedido): ?>
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
                                            <span class="badge-custom badge-<?= strtolower($pedido['nombre_estado']) ?>">
                                                <?= htmlspecialchars($pedido['nombre_estado']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="pedido-footer">
                                        <div class="pedido-total">
                                            Total: S/ <?= number_format($pedido['total'], 2) ?>
                                        </div>
                                        <div>
                                            <a href="seguimiento_pedido.php?id=<?= $pedido['numero_comanda'] ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-map-marked-alt"></i> Seguir Pedido
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Accesos Rápidos -->
                <div class="col-lg-4">
                    <div class="card-custom fade-in">
                        <div class="card-header-custom">
                            <h3><i class="fas fa-bolt"></i> Accesos Rápidos</h3>
                        </div>

                        <div class="list-group list-group-flush">
                            <a href="mis_pedidos.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-history text-primary"></i> 
                                <strong>Historial de Pedidos</strong>
                                <i class="fas fa-chevron-right float-right mt-1"></i>
                            </a>
                            <a href="mis_direcciones.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-map-marker-alt text-success"></i> 
                                <strong>Mis Direcciones</strong>
                                <i class="fas fa-chevron-right float-right mt-1"></i>
                            </a>
                            <a href="mis_puntos.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-coins text-warning"></i> 
                                <strong>Mis Puntos</strong>
                                <span class="badge badge-warning float-right"><?= $cliente['puntos_fidelidad'] ?? 0 ?></span>
                            </a>
                            <a href="menu.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-utensils text-danger"></i> 
                                <strong>Hacer un Pedido</strong>
                                <i class="fas fa-chevron-right float-right mt-1"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Información de Cuenta -->
                    <div class="card-custom fade-in mt-3">
                        <div class="card-header-custom">
                            <h3><i class="fas fa-user"></i> Mi Información</h3>
                        </div>

                        <div class="mb-3">
                            <strong><i class="fas fa-phone text-primary"></i> Teléfono:</strong><br>
                            <?= htmlspecialchars($cliente['telefono']) ?>
                        </div>

                        <?php if (!empty($cliente['email'])): ?>
                            <div class="mb-3">
                                <strong><i class="fas fa-envelope text-info"></i> Email:</strong><br>
                                <?= htmlspecialchars($cliente['email']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($cliente['direccion'])): ?>
                            <div class="mb-3">
                                <strong><i class="fas fa-map-marker-alt text-success"></i> Dirección:</strong><br>
                                <?= htmlspecialchars($cliente['direccion']) ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <strong><i class="fas fa-calendar text-warning"></i> Cliente desde:</strong><br>
                            <?= date('d/m/Y', strtotime($cliente['fyh_creacion'])) ?>
                        </div>

                        <a href="logout_cliente.php" class="btn btn-outline-danger btn-block">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>
