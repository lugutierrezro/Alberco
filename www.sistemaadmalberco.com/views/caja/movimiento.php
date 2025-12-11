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
        $_SESSION['error'] = 'No hay una caja abierta. Debe abrir una caja primero';
        header('Location: index.php');
        exit;
    }
    
    // Obtener movimientos de la caja actual
    $sqlMovimientos = "SELECT * FROM tb_movimientos_caja 
                      WHERE id_caja = ? 
                      ORDER BY fecha_movimiento DESC";
    
    $stmtMovimientos = $pdo->prepare($sqlMovimientos);
    $stmtMovimientos->execute([$caja_actual['id_caja']]);
    $movimientos = $stmtMovimientos->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al obtener movimientos: " . $e->getMessage());
    $movimientos = [];
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Movimientos de Caja</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Caja</a></li>
                        <li class="breadcrumb-item active">Movimientos</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Formulario de Nuevo Movimiento -->
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-plus"></i> Registrar Movimiento</h3>
                        </div>
                        <div class="card-body">
                            <form action="../../controllers/caja/registrar_movimiento.php" 
                                  method="post" 
                                  id="formMovimiento">
                                
                                <input type="hidden" name="id_caja" value="<?php echo $caja_actual['id_caja']; ?>">
                                
                                <div class="form-group">
                                    <label for="tipo_movimiento">
                                        Tipo de Movimiento <span class="text-danger">*</span>
                                    </label>
                                    <select name="tipo_movimiento" id="tipo_movimiento" class="form-control" required>
                                        <option value="">Seleccione...</option>
                                        <option value="INGRESO">Ingreso</option>
                                        <option value="EGRESO">Egreso</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="concepto">
                                        Concepto <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           name="concepto" 
                                           id="concepto"
                                           class="form-control"
                                           placeholder="Descripción del movimiento"
                                           required
                                           maxlength="200">
                                </div>

                                <div class="form-group">
                                    <label for="monto">
                                        Monto <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">S/</span>
                                        </div>
                                        <input type="number" 
                                               name="monto" 
                                               id="monto"
                                               class="form-control"
                                               step="0.01"
                                               required
                                               min="0.01">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="forma_pago">
                                        Forma de Pago <span class="text-danger">*</span>
                                    </label>
                                    <select name="forma_pago" id="forma_pago" class="form-control" required>
                                        <option value="EFECTIVO">Efectivo</option>
                                        <option value="TARJETA">Tarjeta</option>
                                        <option value="TRANSFERENCIA">Transferencia</option>
                                        <option value="YAPE">Yape</option>
                                        <option value="PLIN">Plin</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="observaciones">Observaciones</label>
                                    <textarea name="observaciones" 
                                              id="observaciones"
                                              class="form-control" 
                                              rows="2"></textarea>
                                </div>

                                <hr>
                                
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Registrar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Lista de Movimientos -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list"></i> Movimientos Registrados</h3>
                            <div class="card-tools">
                                <span class="badge badge-primary">
                                    Turno: <?php echo htmlspecialchars($caja_actual['turno']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (count($movimientos) > 0): ?>
                            <table id="tablaMovimientos" class="table table-bordered table-striped table-hover table-sm">
                                <thead>
                                <tr>
                                    <th><center>Hora</center></th>
                                    <th><center>Tipo</center></th>
                                    <th>Concepto</th>
                                    <th><center>Forma de Pago</center></th>
                                    <th class="text-right">Monto</th>
                                    <th><center>Acción</center></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($movimientos as $mov): ?>
                                <tr>
                                    <td><center><?php echo date('H:i:s', strtotime($mov['fecha_movimiento'])); ?></center></td>
                                    <td><center>
                                        <?php if ($mov['tipo_movimiento'] === 'INGRESO'): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-arrow-up"></i> Ingreso
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">
                                                <i class="fas fa-arrow-down"></i> Egreso
                                            </span>
                                        <?php endif; ?>
                                    </center></td>
                                    <td>
                                        <?php echo htmlspecialchars($mov['concepto']); ?>
                                        <?php if ($mov['observaciones']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($mov['observaciones']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><center>
                                        <?php
                                        $icono = 'fa-money-bill';
                                        switch ($mov['forma_pago']) {
                                            case 'EFECTIVO': $icono = 'fa-money-bill-wave'; break;
                                            case 'TARJETA': $icono = 'fa-credit-card'; break;
                                            case 'TRANSFERENCIA': $icono = 'fa-university'; break;
                                            case 'YAPE':
                                            case 'PLIN': $icono = 'fa-mobile-alt'; break;
                                        }
                                        ?>
                                        <i class="fas <?php echo $icono; ?>"></i>
                                        <?php echo htmlspecialchars($mov['forma_pago']); ?>
                                    </center></td>
                                    <td class="text-right">
                                        <?php if ($mov['tipo_movimiento'] === 'INGRESO'): ?>
                                            <strong class="text-success">+S/ <?php echo number_format($mov['monto'], 2); ?></strong>
                                        <?php else: ?>
                                            <strong class="text-danger">-S/ <?php echo number_format($mov['monto'], 2); ?></strong>
                                        <?php endif; ?>
                                    </td>
                                    <td><center>
                                        <?php if ($mov['es_venta'] != 1): ?>
                                        <button type="button" 
                                                class="btn btn-danger btn-xs" 
                                                onclick="eliminarMovimiento(<?php echo $mov['id_movimiento']; ?>)"
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php else: ?>
                                        <span class="badge badge-secondary">Venta</span>
                                        <?php endif; ?>
                                    </center></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                No hay movimientos registrados en esta caja.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminar -->
<form id="formEliminar" action="../../controllers/caja/eliminar_movimiento.php" method="post" style="display: none;">
    <input type="hidden" name="id_movimiento" id="id_movimiento_eliminar">
</form>

<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
    // DataTable
    $(function () {
        $("#tablaMovimientos").DataTable({
            "pageLength": 25,
            "order": [[0, "desc"]],
            "language": {
                "emptyTable": "No hay movimientos",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Movimientos",
                "infoEmpty": "Mostrando 0 a 0 de 0 Movimientos",
                "infoFiltered": "(Filtrado de _MAX_ total)",
                "lengthMenu": "Mostrar _MENU_",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron resultados",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "responsive": true,
            "autoWidth": false
        });
    });

    // Validación
    document.getElementById('formMovimiento').addEventListener('submit', function(e) {
        var monto = parseFloat(document.getElementById('monto').value);
        
        if (monto <= 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El monto debe ser mayor a cero'
            });
            return false;
        }
    });

    // Eliminar movimiento
    function eliminarMovimiento(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "¿Desea eliminar este movimiento?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('id_movimiento_eliminar').value = id;
                document.getElementById('formEliminar').submit();
            }
        });
    }
</script>
