<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Verificar si ya hay una caja abierta
try {
    $sqlVerificar = "SELECT id_arqueo FROM tb_arqueo_caja 
                     WHERE estado = 'abierto' 
                     AND fecha_arqueo = CURDATE()
                     AND estado_registro = 'ACTIVO'";
    $stmtVerificar = $pdo->prepare($sqlVerificar);
    $stmtVerificar->execute();
    $cajaAbierta = $stmtVerificar->fetch();
    
    if ($cajaAbierta) {
        $_SESSION['error'] = 'Ya hay una caja abierta para el día de hoy';
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Error al verificar caja: " . $e->getMessage());
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Apertura de Caja</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-unlock"></i> Abrir Nueva Caja</h3>
                        </div>

                        <div class="card-body">
                            <form action="../../controllers/caja/abrir.php" 
                                  method="post" 
                                  id="formAbrirCaja">
                                
                                <div class="form-group">
                                    <label for="monto_inicial">Monto Inicial <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">S/</span>
                                        </div>
                                        <input type="number" 
                                               name="monto_inicial" 
                                               id="monto_inicial"
                                               class="form-control form-control-lg"
                                               step="0.01"
                                               required
                                               min="0"
                                               autofocus>
                                    </div>
                                    <small class="form-text text-muted">
                                        Ingrese el monto en efectivo con el que inicia el turno
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="observaciones">Observaciones</label>
                                    <textarea name="observaciones" 
                                              id="observaciones"
                                              class="form-control" 
                                              rows="3"
                                              placeholder="Notas adicionales sobre la apertura..."></textarea>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Información:</strong><br>
                                    - Fecha: <?php echo date('d/m/Y'); ?><br>
                                    - Hora: <?php echo date('H:i'); ?><br>
                                    - Usuario: <?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['nombres_sesion'] ?? $_SESSION['usuario_sesion'] ?? 'Usuario'); ?>
                                </div>

                                <hr>
                                
                                <div class="form-group">
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg float-right">
                                        <i class="fas fa-unlock"></i> Abrir Caja
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
    // Validación del formulario
    document.getElementById('formAbrirCaja').addEventListener('submit', function(e) {
        var monto = parseFloat(document.getElementById('monto_inicial').value);
        
        if (monto < 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El monto inicial no puede ser negativo'
            });
            return false;
        }

        // Confirmación
        e.preventDefault();
        Swal.fire({
            title: '¿Confirmar apertura de caja?',
            html: `
                <strong>Monto Inicial:</strong> S/ ${monto.toFixed(2)}
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, abrir caja',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
</script>
