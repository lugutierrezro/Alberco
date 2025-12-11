<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function mostrarAlerta($tipo, $mensaje) {
    $iconos = [
        'danger' => 'fas fa-times-circle',
        'success' => 'fas fa-check-circle',
        'info' => 'fas fa-info-circle'
    ];

    echo '
    <div class="alert alert-' . $tipo . ' alert-dismissible fade show shadow-sm mt-3" role="alert" style="font-size: 15px;">
        <i class="' . $iconos[$tipo] . ' mr-2"></i>
        ' . htmlspecialchars($mensaje) . '
        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    ';
}
?>

<div class="container">
    <?php if (!empty($_SESSION['error'])): ?>
        <?php mostrarAlerta('danger', $_SESSION['error']); unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <?php mostrarAlerta('success', $_SESSION['success']); unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['mensaje'])): ?>
        <?php mostrarAlerta('info', $_SESSION['mensaje']); unset($_SESSION['mensaje']); ?>
    <?php endif; ?>
</div>

<!-- Main Footer -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">
                Anything you want
            </div>
            <strong>Copyright &copy; 2025 D3spiadado</strong> Todos los derechos reservados.
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->
    <!-- Bootstrap 4 -->
    <script src="<?php echo URL_BASE;?>/assets/public/templeates/AdminLTE-3.2.0/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="<?php echo URL_BASE;?>/assets/public/templeates/AdminLTE-3.2.0/dist/js/adminlte.min.js"></script>

    <!-- DataTables  & Plugins -->
    <script src="<?php echo URL_BASE;?>/assets/public/templeates/AdminLTE-3.2.0/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo URL_BASE;?>/assets/public/templeates/AdminLTE-3.2.0/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="<?php echo URL_BASE;?>/assets/public/templeates/AdminLTE-3.2.0/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="<?php echo URL_BASE;?>/assets/public/templeates/AdminLTE-3.2.0/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="<?php echo URL_BASE;?>/assets/public/templeates/AdminLTE-3.2.0/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?php echo URL_BASE;?>/assets/public/templeates/AdminLTE-3.2.0/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
    <script src="<?php echo URL_BASE;?>/assets/public/templeates/AdminLTE-3.2.0/plugins/jszip/jszip.min.js"></script>
    <script src="<?php echo URL_BASE;?>/assets/public/templeates/AdminLTE-3.2.0/plugins/pdfmake/pdfmake.min.js"></script>
    <script src="<?php echo URL_BASE;?>/assets/public/templeates/AdminLTE-3.2.0/plugins/pdfmake/vfs_fonts.js"></script>
    <script src="<?php echo URL_BASE;?>/assets/public/templeates/AdminLTE-3.2.0/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
    <script src="<?php echo URL_BASE;?>/assets/public/templeates/AdminLTE-3.2.0/plugins/datatables-buttons/js/buttons.print.min.js"></script>
    <script src="<?php echo URL_BASE;?>/assets/public/templeates/AdminLTE-3.2.0/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

</body>
</html>
<?php
// Enviar el buffer de salida
if (ob_get_level()) {
    ob_end_flush();
}
