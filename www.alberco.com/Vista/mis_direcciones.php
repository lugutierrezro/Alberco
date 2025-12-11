<?php
require_once(__DIR__ . "/../app/init.php");
require_once(__DIR__ . "/../Services/auth_cliente.php");

$auth = getAuthCliente();
$auth->requerirAuth();

$clienteActual = $auth->getClienteActual();
$clienteModel = new Cliente();

$cliente = $clienteModel->getById($clienteActual['id']);

include '../includes/header.php';
?>

<link rel="stylesheet" href="css/cliente.css">

<div class="content-wrapper">
    <div class="portal-header fade-in">
        <div class="container-fluid">
            <h1><i class="fas fa-map-marker-alt"></i> Mis Direcciones</h1>
            <p class="subtitle">Gestiona tus direcciones de entrega</p>
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

                    <!-- Dirección Principal -->
                    <?php if (!empty($cliente['direccion'])): ?>
                        <div class="card-custom fade-in">
                            <div class="card-header-custom">
                                <h3><i class="fas fa-home"></i> Dirección Principal</h3>
                            </div>

                            <div class="direccion-item predeterminada">
                                <span class="badge-predeterminada">
                                    <i class="fas fa-star"></i> Predeterminada
                                </span>
                                <div class="direccion-texto">
                                    <i class="fas fa-map-marker-alt text-success"></i>
                                    <?= htmlspecialchars($cliente['direccion']) ?>
                                </div>
                                <div class="text-muted">
                                    <i class="fas fa-phone"></i> <?= htmlspecialchars($cliente['telefono']) ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card-custom fade-in">
                            <div class="empty-state">
                                <i class="fas fa-map-marked-alt"></i>
                                <h3>No tienes direcciones guardadas</h3>
                                <p>Agrega una dirección para facilitar tus pedidos</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Información sobre direcciones -->
                    <div class="card-custom fade-in mt-3">
                        <div class="card-header-custom">
                            <h3><i class="fas fa-info-circle"></i> Gestión de Direcciones</h3>
                        </div>

                        <div class="alert alert-info">
                            <h5><i class="fas fa-lightbulb"></i> Funcionalidad Mejorada Próximamente</h5>
                            <p class="mb-2">Estamos trabajando en mejoras para que puedas:</p>
                            <ul class="mb-0">
                                <li>Guardar múltiples direcciones</li>
                                <li>Agregar referencias y puntos de referencia</li>
                                <li>Seleccionar dirección favorita</li>
                                <li>Editar y eliminar direcciones</li>
                                <li>Guardar ubicación en el mapa</li>
                            </ul>
                        </div>

                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> Por Ahora</h5>
                            <p class="mb-0">
                                Puedes actualizar tu dirección principal al hacer un pedido. 
                                La dirección se guardará automáticamente en tu perfil.
                            </p>
                        </div>

                        <div class="text-center mt-4">
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
