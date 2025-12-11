<?php
// Asegurar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializar variables
$mensaje = null;
$icono = null;

// Detectar tipo de mensaje estándar
if (isset($_SESSION['success'])) {
    $mensaje = $_SESSION['success'];
    $icono = 'success';
    unset($_SESSION['success']);

} elseif (isset($_SESSION['error'])) {
    $mensaje = $_SESSION['error'];
    $icono = 'error';
    unset($_SESSION['error']);

} elseif (isset($_SESSION['warning'])) {
    $mensaje = $_SESSION['warning'];
    $icono = 'warning';
    unset($_SESSION['warning']);

} elseif (isset($_SESSION['info'])) {
    $mensaje = $_SESSION['info'];
    $icono = 'info';
    unset($_SESSION['info']);

}
// Mensaje personalizado
elseif (isset($_SESSION['mensaje']) && isset($_SESSION['icono'])) {
    $mensaje = $_SESSION['mensaje'];
    $icono = $_SESSION['icono'];
    unset($_SESSION['mensaje'], $_SESSION['icono']);
}

// Mostrar mensaje si existe
if (!empty($mensaje) && !empty($icono)):
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        position: 'top-end',
        icon: '<?= $icono ?>',
        title: '<?= htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?>',
        showConfirmButton: false,
        timer: 2500
    });
});
</script>
<?php endif; ?>
