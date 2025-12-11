<?php
require_once(__DIR__ . "/../app/init.php");
require_once(__DIR__ . "/../Services/auth_cliente.php");

$auth = getAuthCliente();
$auth->requerirAuth();

$clienteActual = $auth->getClienteActual();
$clienteModel = new Cliente();

$cliente = $clienteModel->getById($clienteActual['id']);
$puntosActuales = $cliente['puntos_fidelidad'] ?? 0;

include '../includes/header.php';
?>

<link rel="stylesheet" href="css/cliente.css">

<div class="content-wrapper">
    <div class="portal-header fade-in">
        <div class="container-fluid">
            <h1><i class="fas fa-coins"></i> Mis Puntos de Fidelidad</h1>
            <p class="subtitle">Acumula puntos y disfruta de recompensas</p>
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
                    </div>

                    <!-- Display de Puntos -->
                    <div class="puntos-display fade-in">
                        <div class="puntos-valor">
                            <i class="fas fa-coins"></i> <?= number_format($puntosActuales) ?>
                        </div>
                        <div class="puntos-label">Puntos Disponibles</div>
                    </div>

                    <div class="row">
                        <!-- Cómo Funciona -->
                        <div class="col-md-6">
                            <div class="card-custom fade-in">
                                <div class="card-header-custom">
                                    <h3><i class="fas fa-info-circle"></i> ¿Cómo Funciona?</h3>
                                </div>

                                <div class="alert alert-info">
                                    <h5><i class="fas fa-gift"></i> Gana Puntos</h5>
                                    <ul class="mb-0">
                                        <li>Por cada <strong>S/ 10</strong> en compras = <strong>1 punto</strong></li>
                                        <li>Los puntos se acumulan automáticamente</li>
                                        <li>Sin fecha de vencimiento</li>
                                    </ul>
                                </div>

                                <div class="alert alert-success">
                                    <h5><i class="fas fa-star"></i> Niveles de Cliente</h5>
                                    <ul class="mb-0">
                                        <li><strong>NUEVO:</strong> Cliente recién registrado</li>
                                        <li><strong>OCASIONAL:</strong> Más de S/ 0 en compras</li>
                                        <li><strong>FRECUENTE:</strong> Más de S/ 1,000 en compras</li>
                                        <li><strong>VIP:</strong> Más de S/ 5,000 en compras</li>
                                    </ul>
                                </div>

                                <div class="text-center">
                                    <p class="mb-2">Tu nivel actual:</p>
                                    <div class="badge-custom badge-<?= strtolower($cliente['tipo_cliente']) ?>" 
                                         style="font-size: 1.2rem; padding: 10px 20px;">
                                        <i class="fas fa-star"></i> <?= htmlspecialchars($cliente['tipo_cliente']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recompensas Disponibles -->
                        <div class="col-md-6">
                            <div class="card-custom fade-in">
                                <div class="card-header-custom">
                                    <h3><i class="fas fa-gift"></i> Recompensas</h3>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fas fa-construction"></i> 
                                    <strong>Próximamente</strong><br>
                                    Estamos preparando increíbles recompensas para ti. ¡Sigue acumulando puntos!
                                </div>

                                <!-- Ejemplos de recompensas futuras -->
                                <div class="list-group">
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-percentage text-primary"></i>
                                                <strong>10% de Descuento</strong>
                                                <br>
                                                <small class="text-muted">En tu próximo pedido</small>
                                            </div>
                                            <span class="badge badge-primary badge-pill">100 pts</span>
                                        </div>
                                    </div>

                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-pizza-slice text-danger"></i>
                                                <strong>Pizza Gratis</strong>
                                                <br>
                                                <small class="text-muted">Pizza mediana de tu elección</small>
                                            </div>
                                            <span class="badge badge-danger badge-pill">250 pts</span>
                                        </div>
                                    </div>

                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-shipping-fast text-success"></i>
                                                <strong>Delivery Gratis</strong>
                                                <br>
                                                <small class="text-muted">En tu próximo pedido</small>
                                            </div>
                                            <span class="badge badge-success badge-pill">50 pts</span>
                                        </div>
                                    </div>

                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-crown text-warning"></i>
                                                <strong>Combo Especial VIP</strong>
                                                <br>
                                                <small class="text-muted">Combo exclusivo para clientes VIP</small>
                                            </div>
                                            <span class="badge badge-warning badge-pill">500 pts</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historial de Puntos -->
                    <div class="card-custom fade-in mt-3">
                        <div class="card-header-custom">
                            <h3><i class="fas fa-history"></i> Historial de Puntos</h3>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            El historial detallado de puntos estará disponible próximamente
                        </div>

                        <div class="text-center py-4">
                            <p class="text-muted">Sigue comprando para acumular más puntos</p>
                            <a href="menu.php" class="btn btn-primary">
                                <i class="fas fa-shopping-cart"></i> Hacer un Pedido
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>
