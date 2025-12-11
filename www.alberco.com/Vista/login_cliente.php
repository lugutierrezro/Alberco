<?php
require_once(__DIR__ . "/../app/init.php");
require_once(__DIR__ . "/../Services/auth_cliente.php");

$auth = getAuthCliente();

// Si ya está logueado, redirigir al perfil
if ($auth->estaLogueado()) {
    header("Location: perfil_cliente.php");
    exit;
}

// Procesar login
$error = '';
$mensaje = '';

// Verificar si viene con un mensaje (ej: desde el carrito)
if (isset($_GET['mensaje'])) {
    if ($_GET['mensaje'] === 'debes_iniciar_sesion') {
        $mensaje = 'Debes iniciar sesión para realizar un pedido';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefono = $_POST['telefono'] ?? '';
    
    $resultado = $auth->login($telefono);
    
    if ($resultado['success']) {
        // Verificar si hay una URL de redirección guardada
        $redirectUrl = $_SESSION['redirect_after_login'] ?? 'perfil_cliente.php';
        unset($_SESSION['redirect_after_login']); // Limpiar la sesión
        
        header("Location: $redirectUrl");
        exit;
    } else {
        $error = $resultado['mensaje'];
    }
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="css/cliente.css">

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center" style="margin-top: 50px;">
                <div class="col-md-5">
                    <div class="card-custom fade-in">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-circle" style="font-size: 4rem; color: var(--primary-color);"></i>
                            <h2 class="mt-3" style="color: var(--dark-color);">Acceder con tu Teléfono</h2>
                            <p class="text-muted">Ingresa tu número para acceder a tu cuenta</p>
                        </div>

                        <?php if ($mensaje): ?>
                            <div class="alert alert-info alert-dismissible fade show">
                                <i class="fas fa-info-circle"></i> <?= htmlspecialchars($mensaje) ?>
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Acceso simplificado:</strong> Solo necesitas tu número de teléfono para acceder.
                        </div>

                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="telefono">
                                    <i class="fas fa-phone"></i> Número de Teléfono
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="telefono" 
                                       name="telefono" 
                                       placeholder="999999999" 
                                       required
                                       pattern="[0-9]{9}"
                                       maxlength="9"
                                       autofocus
                                       value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                                <small class="form-text text-muted">Ingresa el teléfono con el que te registraste</small>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                            </button>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="mb-2">¿No tienes una cuenta?</p>
                            <a href="registro_cliente.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus"></i> Registrarse
                            </a>
                        </div>

                        <div class="text-center mt-3">
                            <a href="../index.php" class="text-muted">
                                <i class="fas fa-arrow-left"></i> Volver al inicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
// Validación de teléfono en tiempo real
document.getElementById('telefono').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '').substring(0, 9);
});
</script>

<?php include '../includes/footer.php'; ?>
