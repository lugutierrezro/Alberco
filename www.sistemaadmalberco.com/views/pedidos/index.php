<?php
include('../../services/database/config.php');
include('../../contans/layout/sesion.php');
include('../../contans/layout/parte1.php');

// Obtener filtro de estado si existe
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';

// Consulta SQL adaptada a tu base de datos
try {
    $sql = "SELECT 
                p.id_pedido,
                p.numero_comanda,
                p.nro_pedido,
                p.tipo_pedido,
                p.fecha_pedido,
                p.subtotal,
                p.costo_delivery,
                p.total,
                p.direccion_entrega,
                p.observaciones,
                
                c.nombre as cliente_nombre,
                c.apellidos as cliente_apellidos,
                c.telefono as cliente_telefono,
                
                m.numero_mesa,
                
                e.nombre_estado as estado,
                e.color as estado_color,
                
                u.username as usuario_registro,
                
                emp.nombres as delivery_nombre,
                emp.apellidos as delivery_apellidos
            FROM tb_pedidos p
            LEFT JOIN tb_clientes c ON p.id_cliente = c.id_cliente
            LEFT JOIN tb_mesas m ON p.id_mesa = m.id_mesa
            LEFT JOIN tb_estados e ON p.id_estado = e.id_estado
            LEFT JOIN tb_usuarios u ON p.id_usuario_registro = u.id_usuario
            LEFT JOIN tb_empleados emp ON p.id_empleado_delivery = emp.id_empleado
            WHERE p.estado_registro = 'ACTIVO'";
    
    // Agregar filtro de estado si existe
    if (!empty($filtro_estado)) {
        $sql .= " AND e.nombre_estado = :filtro_estado";
    }
    
    $sql .= " ORDER BY p.fecha_pedido DESC";
    
    $stmt = $pdo->prepare($sql);
    
    if (!empty($filtro_estado)) {
        $stmt->bindParam(':filtro_estado', $filtro_estado);
    }
    
    $stmt->execute();
    $pedidos_datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al cargar pedidos: " . $e->getMessage() . "</div>";
    $pedidos_datos = [];
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Gestión de Pedidos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item active">Pedidos</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <!-- Filtros rápidos -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="btn-group">
                        <a href="index.php" class="btn btn-default <?php echo empty($filtro_estado) ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i> Todos
                        </a>
                        <a href="index.php?estado=Pendiente" class="btn btn-warning <?php echo $filtro_estado === 'Pendiente' ? 'active' : ''; ?>">
                            <i class="fas fa-clock"></i> Pendientes
                        </a>
                        <a href="index.php?estado=En Preparación" class="btn btn-info <?php echo $filtro_estado === 'En Preparación' ? 'active' : ''; ?>">
                            <i class="fas fa-utensils"></i> En Preparación
                        </a>
                        <a href="index.php?estado=Listo" class="btn btn-primary <?php echo $filtro_estado === 'Listo' ? 'active' : ''; ?>">
                            <i class="fas fa-check"></i> Listos
                        </a>
                        <a href="index.php?estado=En Camino" class="btn btn-secondary <?php echo $filtro_estado === 'En Camino' ? 'active' : ''; ?>">
                            <i class="fas fa-motorcycle"></i> En Camino
                        </a>
                        <a href="index.php?estado=Entregado" class="btn btn-success <?php echo $filtro_estado === 'Entregado' ? 'active' : ''; ?>">
                            <i class="fas fa-check-double"></i> Entregados
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Pedidos Registrados (<?php echo count($pedidos_datos); ?>)</h3>
                            <div class="card-tools">
                                <a href="create.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Nuevo Pedido
                                </a>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped table-hover table-sm">
                                <thead>
                                <tr>
                                    <th><center>Nro</center></th>
                                    <th><center>Código</center></th>
                                    <th><center>Fecha</center></th>
                                    <th><center>Tipo</center></th>
                                    <th><center>Cliente</center></th>
                                    <th><center>Mesa/Dirección</center></th>
                                    <th><center>Delivery</center></th>
                                    <th><center>Registrado por</center></th>
                                    <th><center>Total</center></th>
                                    <th><center>Estado</center></th>
                                    <th><center>Acciones</center></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador = 0;
                                foreach ($pedidos_datos as $pedido) {
                                    $id_pedido = $pedido['id_pedido'];
                                    $contador++;
                                    
                                    // Determinar badge de estado basado en el nombre del estado
                                    $badge_estado = 'secondary';
                                    $estado_normalizado = strtolower(str_replace(' ', '_', $pedido['estado']));
                                    
                                    switch ($estado_normalizado) {
                                        case 'pendiente': $badge_estado = 'warning'; break;
                                        case 'en_preparación': 
                                        case 'en_preparacion': $badge_estado = 'info'; break;
                                        case 'listo': $badge_estado = 'primary'; break;
                                        case 'en_camino': $badge_estado = 'secondary'; break;
                                        case 'entregado': $badge_estado = 'success'; break;
                                        case 'cancelado': $badge_estado = 'danger'; break;
                                    }
                                    
                                    // Badge de tipo
                                    $badge_tipo = 'info';
                                    $tipo_normalizado = strtolower($pedido['tipo_pedido']);
                                    switch ($tipo_normalizado) {
                                        case 'mesa': $badge_tipo = 'primary'; break;
                                        case 'delivery': $badge_tipo = 'warning'; break;
                                        case 'para_llevar': $badge_tipo = 'success'; break;
                                    }
                                    ?>
                                    <tr>
                                        <td><center><?php echo $contador; ?></center></td>
                                        <td><center>
                                            <strong><?php echo htmlspecialchars($pedido['numero_comanda'] ?? $pedido['nro_pedido'] ?? 'PED-' . $id_pedido); ?></strong>
                                        </center></td>
                                        <td><center>
                                            <small><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></small>
                                            <br>
                                            <small class="text-muted"><?php echo date('H:i', strtotime($pedido['fecha_pedido'])); ?></small>
                                        </center></td>
                                        <td><center>
                                            <span class="badge badge-<?php echo $badge_tipo; ?>">
                                                <?php echo ucwords(str_replace('_', ' ', $pedido['tipo_pedido'])); ?>
                                            </span>
                                        </center></td>
                                        <td>
                                            <?php echo htmlspecialchars($pedido['cliente_nombre'] ?? 'Sin nombre'); ?>
                                            <?php if (!empty($pedido['cliente_apellidos'])): ?>
                                                <?php echo htmlspecialchars($pedido['cliente_apellidos']); ?>
                                            <?php endif; ?>
                                            <?php if (!empty($pedido['cliente_telefono'])): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($pedido['cliente_telefono']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (strtolower($pedido['tipo_pedido']) === 'mesa' && !empty($pedido['numero_mesa'])): ?>
                                                <span class="badge badge-dark">Mesa <?php echo htmlspecialchars($pedido['numero_mesa']); ?></span>
                                            <?php elseif (strtolower($pedido['tipo_pedido']) === 'delivery' && !empty($pedido['direccion_entrega'])): ?>
                                                <small><?php echo htmlspecialchars(substr($pedido['direccion_entrega'], 0, 40)); ?><?php echo strlen($pedido['direccion_entrega']) > 40 ? '...' : ''; ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">Para llevar</small>
                                            <?php endif; ?>
                                        </td>
                                        <td><center>
                                            <?php if (!empty($pedido['delivery_nombre'])): ?>
                                                <?php echo htmlspecialchars($pedido['delivery_nombre']); ?>
                                                <?php if (!empty($pedido['delivery_apellidos'])): ?>
                                                    <?php echo htmlspecialchars($pedido['delivery_apellidos']); ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </center></td>
                                        <td><center>
                                            <small><?php echo htmlspecialchars($pedido['usuario_registro'] ?? 'N/A'); ?></small>
                                        </center></td>
                                        <td><center>
                                            <strong class="text-success">S/ <?php echo number_format($pedido['total'], 2); ?></strong>
                                        </center></td>
                                        <td><center>
                                            <span class="badge badge-<?php echo $badge_estado; ?>">
                                                <?php echo htmlspecialchars($pedido['estado']); ?>
                                            </span>
                                        </center></td>
                                        <td>
                                            <center>
                                                <div class="btn-group">
                                                    <a href="show.php?id=<?php echo $id_pedido; ?>" 
                                                       class="btn btn-info btn-sm" 
                                                       title="Ver Detalles">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <?php if ($estado_normalizado !== 'entregado' && $estado_normalizado !== 'cancelado'): ?>
                                                    <button type="button" 
                                                            class="btn btn-primary btn-sm" 
                                                            onclick="cambiarEstado(<?php echo $id_pedido; ?>)"
                                                            title="Cambiar Estado">
                                                        <i class="fa fa-exchange-alt"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php if ($estado_normalizado === 'pendiente'): ?>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            onclick="cancelarPedido(<?php echo $id_pedido; ?>)"
                                                            title="Cancelar Pedido">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </center>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambiar Estado -->
<div class="modal fade" id="modalCambiarEstado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../../controllers/pedidos/actualizar_estado.php" method="post">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">Cambiar Estado del Pedido</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_pedido" id="id_pedido_estado">
                    
                    <div class="form-group">
                        <label for="id_estado">Nuevo Estado <span class="text-danger">*</span></label>
                        <select name="id_estado" id="id_estado" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <?php
                            try {
                                $sqlEstados = "SELECT id_estado, nombre_estado, color 
                                              FROM tb_estados 
                                              WHERE estado_registro = 'ACTIVO' 
                                              ORDER BY orden ASC";
                                $stmtEstados = $pdo->prepare($sqlEstados);
                                $stmtEstados->execute();
                                $estados = $stmtEstados->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($estados as $estado) {
                                    echo '<option value="' . $estado['id_estado'] . '">' . 
                                         htmlspecialchars($estado['nombre_estado']) . '</option>';
                                }
                            } catch (PDOException $e) {
                                echo '<option value="">Error al cargar estados</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="observaciones_estado">Observaciones</label>
                        <textarea name="observaciones" 
                                  id="observaciones_estado" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Ingrese alguna observación sobre el cambio de estado..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('../../contans/layout/mensajes.php'); ?>
<?php include('../../contans/layout/parte2.php'); ?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 25,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Pedidos",
                "infoEmpty": "Mostrando 0 a 0 de 0 Pedidos",
                "infoFiltered": "(Filtrado de _MAX_ total Pedidos)",
                "lengthMenu": "Mostrar _MENU_ Pedidos",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscador:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "responsive": true, 
            "lengthChange": true, 
            "autoWidth": false,
            "order": [[0, "desc"]],
            buttons: [{
                extend: 'collection',
                text: 'Reportes',
                orientation: 'landscape',
                buttons: [{
                    text: 'Copiar',
                    extend: 'copy',
                }, {
                    extend: 'pdf'
                },{
                    extend: 'csv'
                },{
                    extend: 'excel'
                },{
                    text: 'Imprimir',
                    extend: 'print'
                }]
            },
            {
                extend: 'colvis',
                text: 'Visor de columnas',
                collectionLayout: 'fixed three-column'
            }],
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });

    function cambiarEstado(id) {
        document.getElementById('id_pedido_estado').value = id;
        $('#modalCambiarEstado').modal('show');
    }

    function cancelarPedido(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "¿Desea cancelar este pedido?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, cancelar pedido',
            cancelButtonText: 'No, mantener'
        }).then((result) => {
            if (result.isConfirmed) {
                // Buscar el ID del estado "Cancelado"
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '../../controllers/pedidos/actualizar_estado.php';
                
                var inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id_pedido';
                inputId.value = id;
                form.appendChild(inputId);
                
                var inputEstado = document.createElement('input');
                inputEstado.type = 'hidden';
                inputEstado.name = 'id_estado';
                inputEstado.value = '6'; // ID del estado Cancelado según tu BD
                form.appendChild(inputEstado);
                
                var inputObs = document.createElement('input');
                inputObs.type = 'hidden';
                inputObs.name = 'observaciones';
                inputObs.value = 'Pedido cancelado por el usuario';
                form.appendChild(inputObs);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Auto-refresh cada 60 segundos (reducido de 30 para mejor rendimiento)
    setTimeout(function(){
        location.reload();
    }, 60000);
</script>
