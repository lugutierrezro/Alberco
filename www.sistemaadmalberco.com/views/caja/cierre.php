<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Verificar si hay una caja abierta
try {
    $sqlCaja = "SELECT * FROM tb_caja 
                WHERE estado = 'ABIERTA' AND id_usuario = ?";
    $stmtCaja = $pdo->prepare($sqlCaja);
    $stmtCaja->execute([$_SESSION['id_usuario']]);
    $caja_actual = $stmtCaja->fetch();
    
    if (!$caja_actual) {
        $_SESSION['error'] = 'No hay una caja abierta para cerrar';
        header('Location: index.php');
        exit;
    }
    
    // Calcular resumen de la caja
    $sqlResumen = "SELECT 
                      SUM(CASE WHEN tipo_movimiento = 'INGRESO' THEN monto ELSE 0 END) as total_ingresos,
                      SUM(CASE WHEN tipo_movimiento = 'EGRESO' THEN monto ELSE 0 END) as total_egresos,
                      COUNT(*) as total_movimientos
                   FROM tb_movimientos_caja
                   WHERE id_caja = ?";
    
    $stmtResumen = $pdo->prepare($sqlResumen);
    $stmtResumen->execute([$caja_actual['id_caja']]);
    $resumen = $stmtResumen->fetch();
    
    $monto_esperado = $caja_actual['monto_inicial'] + 
                      ($resumen['total_ingresos'] ?? 0) - 
                      ($resumen['total_egresos'] ?? 0);
    
    // Resumen por forma de pago
    $sqlPorMetodo = "SELECT 
                        forma_pago,
                        SUM(monto) as total,
                        COUNT(*) as cantidad
                     FROM tb_movimientos_caja
                     WHERE id_caja = ? AND tipo_movimiento = 'INGRESO'
                     GROUP BY forma_pago
                     ORDER BY total DESC";
    
    $stmtMetodo = $pdo->prepare($sqlPorMetodo);
    $stmtMetodo->execute([$caja_actual['id_caja']]);
    $resumen_metodos = $stmtMetodo->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al obtener datos de caja: " . $e->getMessage());
    $_SESSION['error'] = 'Error al cargar datos de la caja';
    header('Location: index.php');
    exit;
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Cierre de Caja</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <form action="../../controllers/caja/cerrar.php" 
                  method="post" 
                  id="formCerrarCaja">
                
                <input type="hidden" name="id_caja" value="<?php echo $caja_actual['id_caja']; ?>">
                
                <div class="row">
                    <!-- Resumen de la Caja -->
                    <div class="col-md-8">
                        <div class="card card-danger">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-lock"></i> Resumen del Turno</h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <strong>Turno:</strong> <?php echo htmlspecialchars($caja_actual['turno']); ?> |
                                    <strong>Apertura:</strong> <?php echo date('d/m/Y H:i', strtotime($caja_actual['fecha_apertura'])); ?> |
                                    <strong>Duración:</strong> 
                                    <?php
                                    $inicio = new DateTime($caja_actual['fecha_apertura']);
                                    $ahora = new DateTime();
                                    $duracion = $inicio->diff($ahora);
                                    echo $duracion->format('%h horas %i minutos');
                                    ?>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="info-box bg-info">
                                            <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Monto Inicial</span>
                                                <span class="info-box-number">S/ <?php echo number_format($caja_actual['monto_inicial'], 2); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="info-box bg-success">
                                            <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Ingresos</span>
                                                <span class="info-box-number">S/ <?php echo number_format($resumen['total_ingresos'] ?? 0, 2); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="info-box bg-danger">
                                            <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Egresos</span>
                                                <span class="info-box-number">S/ <?php echo number_format($resumen['total_egresos'] ?? 0, 2); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h5><i class="fas fa-money-bill-wave"></i> Detalle por Forma de Pago</h5>
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Forma de Pago</th>
                                        <th class="text-right">Cantidad</th>
                                        <th class="text-right">Monto Total</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                                    $total_efectivo = 0;
                                    foreach ($resumen_metodos as $metodo): 
                                        if ($metodo['forma_pago'] === 'EFECTIVO') {
                                            $total_efectivo = $metodo['total'];
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($metodo['forma_pago']); ?></td>
                                        <td class="text-right">
                                            <span class="badge badge-info"><?php echo $metodo['cantidad']; ?></span>
                                        </td>
                                        <td class="text-right">
                                            <strong>S/ <?php echo number_format($metodo['total'], 2); ?></strong>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="alert alert-warning">
                                            <h5><i class="fas fa-calculator"></i> Monto Esperado en Caja</h5>
                                            <h3>S/ <?php echo number_format($monto_esperado, 2); ?></h3>
                                            <small>
                                                Monto Inicial (S/ <?php echo number_format($caja_actual['monto_inicial'], 2); ?>) + 
                                                Efectivo Recibido (S/ <?php echo number_format($total_efectivo, 2); ?>) - 
                                                Egresos (S/ <?php echo number_format($resumen['total_egresos'] ?? 0, 2); ?>)
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de Cierre -->
                    <div class="col-md-4">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-check"></i> Datos de Cierre</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="monto_final">
                                        Monto Final Real <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">S/</span>
                                        </div>
                                        <input type="number" 
                                               name="monto_final" 
                                               id="monto_final"
                                               class="form-control form-control-lg"
                                               step="0.01"
                                               required
                                               min="0"
                                               autofocus>
                                    </div>
                                    <small class="form-text text-muted">
                                        Cuente el efectivo real en caja e ingréselo aquí
                                    </small>
                                </div>

                                <!-- Diferencia calculada automáticamente -->
                                <div class="form-group">
                                    <label>Diferencia</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">S/</span>
                                        </div>
                                        <input type="text" 
                                               id="diferencia_display"
                                               class="form-control"
                                               readonly
                                               value="0.00">
                                    </div>
                                    <small id="diferencia_mensaje" class="form-text"></small>
                                </div>

                                <div class="form-group">
                                    <label for="observaciones_cierre">Observaciones</label>
                                    <textarea name="observaciones_cierre" 
                                              id="observaciones_cierre"
                                              class="form-control" 
                                              rows="4"
                                              placeholder="Notas sobre el cierre, incidencias, etc."></textarea>
                                </div>

                                <div class="alert alert-danger">
                                    <strong>Atención:</strong> Una vez cerrada la caja, no podrá realizar más movimientos en este turno.
                                </div>

                                <hr>
                                
                                <a href="index.php" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-danger btn-block btn-lg">
                                    <i class="fas fa-lock"></i> Cerrar Caja
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
    var montoEsperado = <?php echo $monto_esperado; ?>;

    // Calcular diferencia automáticamente
    document.getElementById('monto_final').addEventListener('input', function() {
        var montoFinal = parseFloat(this.value) || 0;
        var diferencia = montoFinal - montoEsperado;
        
        var displayDiferencia = document.getElementById('diferencia_display');
        var mensajeDiferencia = document.getElementById('diferencia_mensaje');
        
        displayDiferencia.value = diferencia.toFixed(2);
        
        if (diferencia > 0) {
            displayDiferencia.style.color = '#28a745'; // Verde
            mensajeDiferencia.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Sobrante</span>';
        } else if (diferencia < 0) {
            displayDiferencia.style.color = '#dc3545'; // Rojo
            mensajeDiferencia.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Faltante</span>';
        } else {
            displayDiferencia.style.color = '#000';
            mensajeDiferencia.innerHTML = '<span class="text-info"><i class="fas fa-check"></i> Cuadra exacto</span>';
        }
    });

    // Confirmación antes de cerrar
    document.getElementById('formCerrarCaja').addEventListener('submit', function(e) {
        e.preventDefault();
        
        var montoFinal = parseFloat(document.getElementById('monto_final').value) || 0;
        var diferencia = montoFinal - montoEsperado;
        
        var mensaje = `
            <strong>Monto Esperado:</strong> S/ ${montoEsperado.toFixed(2)}<br>
            <strong>Monto Final:</strong> S/ ${montoFinal.toFixed(2)}<br>
            <strong>Diferencia:</strong> <span class="${diferencia >= 0 ? 'text-success' : 'text-danger'}">
                S/ ${diferencia.toFixed(2)}
            </span>
        `;
        
        Swal.fire({
            title: '¿Confirmar cierre de caja?',
            html: mensaje,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, cerrar caja',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
</script>
