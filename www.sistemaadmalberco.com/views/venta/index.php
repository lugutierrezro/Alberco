<?php
// views/venta/index.php

// Anti-cache headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include('../../services/database/config.php');
include('../../contans/layout/sesion.php');
include('../../models/venta.php'); // Asegurar importe del modelo
include('../../contans/layout/parte1.php');

$ventaModel = new Venta();
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Obtener ventas (todas, incluyendo pendientes y completadas)
$ventas = $ventaModel->getVentasWithDetails([
    'fecha_inicio' => $fecha_inicio,
    'fecha_fin' => $fecha_fin
    // Sin filtro de estado para ver todas las ventas
]);
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Gestión de Ventas y Facturación</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item active">Ventas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-invoice-dollar mr-1"></i>
                        Listado de Ventas
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <form method="GET" class="row mb-4">
                        <div class="col-md-3">
                            <label>Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" value="<?php echo $fecha_inicio; ?>">
                        </div>
                        <div class="col-md-3">
                            <label>Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" value="<?php echo $fecha_fin; ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table id="tabla-ventas" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Comprobante</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Método Pago</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ventas as $index => $venta): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <span class="badge badge-info"><?php echo $venta['tipo_comprobante']; ?></span><br>
                                        <strong><?php echo $venta['serie_comprobante'] . '-' . $venta['numero_comprobante']; ?></strong>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?></td>
                                    <td>
                                        <?php echo $venta['cliente_nombre'] . ' ' . $venta['cliente_apellidos']; ?><br>
                                        <small class="text-muted"><?php echo $venta['cliente_documento']; ?></small>
                                    </td>
                                    <td>
                                        <?php 
                                        // Debug: mostrar valor real
                                        echo htmlspecialchars($venta['metodo_pago']); 
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($venta['estado_venta'] === 'pendiente'): ?>
                                            <span class="badge badge-warning">Pendiente</span>
                                        <?php elseif ($venta['estado_venta'] === 'completada'): ?>
                                            <span class="badge badge-success">Completada</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?php echo ucfirst($venta['estado_venta']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-success font-weight-bold">S/ <?php echo number_format($venta['total'], 2); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="imprimir.php?id=<?php echo $venta['nro_venta']; ?>&print=true" target="_blank" class="btn btn-secondary btn-sm" title="Imprimir Comprobante">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="anularVenta(<?php echo $venta['id_venta']; ?>, '<?php echo $venta['serie_comprobante'] . '-' . $venta['numero_comprobante']; ?>')" title="Anular Venta">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../../contans/layout/parte2.php'); ?>

<script>
    $(function () {
        $("#tabla-ventas").DataTable({
            "responsive": true, "lengthChange": false, "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print"],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [[ 2, "desc" ]]
        }).buttons().container().appendTo('#tabla-ventas_wrapper .col-md-6:eq(0)');
    });

    function anularVenta(id, comprobante) {
        Swal.fire({
            title: '¿Anular Venta?',
            text: "Se anulará el comprobante " + comprobante + " y se devolverá el stock.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, anular',
            input: 'text',
            inputPlaceholder: 'Motivo de anulación',
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes escribir un motivo'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Llamada AJAX para anular (necesitaría crear endpoint o usar uno existente)
                // Por ahora, mostrar mensaje de que se requiere implementación en backend si no existe endpoint JSON
                Swal.fire('Info', 'Funcionalidad de anulación segura pendiente de endpoint.', 'info');
            }
        });
    }
</script>
