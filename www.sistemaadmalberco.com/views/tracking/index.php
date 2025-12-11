<?php
include('../../services/database/config.php');
include('../../contans/layout/sesion.php');
include('../../contans/layout/parte1.php');

// Obtener pedidos activos
try {
    $sqlPedidos = "SELECT p.*, 
                          c.nombre as cliente_nombre, 
                          c.apellidos as cliente_apellidos, 
                          c.telefono as cliente_telefono,
                          m.numero_mesa,
                          u.username as usuario_registro,
                          e.nombre_estado as estado,
                          e.color as estado_color
                   FROM tb_pedidos p
                   INNER JOIN tb_clientes c ON p.id_cliente = c.id_cliente
                   LEFT JOIN tb_mesas m ON p.id_mesa = m.id_mesa
                   INNER JOIN tb_usuarios u ON p.id_usuario_registro = u.id_usuario
                   INNER JOIN tb_estados e ON p.id_estado = e.id_estado
                   WHERE e.nombre_estado IN ('Pendiente', 'En Preparación', 'Listo', 'En Camino')
                   AND p.estado_registro = 'ACTIVO'
                   ORDER BY p.fecha_pedido ASC";
    
    $stmtPedidos = $pdo->prepare($sqlPedidos);
    $stmtPedidos->execute();
    $pedidos_activos = $stmtPedidos->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al obtener pedidos: " . $e->getMessage());
    $pedidos_activos = [];
}

// Obtener estados disponibles
try {
    $sqlEstados = "SELECT id_estado, nombre_estado, color 
                   FROM tb_estados 
                   WHERE estado_registro = 'ACTIVO' 
                   ORDER BY orden ASC";
    $stmtEstados = $pdo->prepare($sqlEstados);
    $stmtEstados->execute();
    $estados = $stmtEstados->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al obtener estados: " . $e->getMessage());
    $estados = [];
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-chart-line"></i> Seguimiento de Pedidos en Tiempo Real
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Pedidos</a></li>
                        <li class="breadcrumb-item active">Tracking</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <!-- Resumen de Estados -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="count-pendientes">0</h3>
                            <p>Pendientes</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="count-preparacion">0</h3>
                            <p>En Preparación</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3 id="count-listos">0</h3>
                            <p>Listos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3 id="count-camino">0</h3>
                            <p>En Camino</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-motorcycle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pedidos por Estado -->
            <div class="row">
                <!-- PENDIENTES -->
                <div class="col-md-6 col-lg-3">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-clock"></i> Pendientes</h3>
                        </div>
                        <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                            <div id="pedidos-pendientes">
                                <div class="p-3 text-center text-muted">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EN PREPARACIÓN -->
                <div class="col-md-6 col-lg-3">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-utensils"></i> En Preparación</h3>
                        </div>
                        <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                            <div id="pedidos-preparacion">
                                <div class="p-3 text-center text-muted">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LISTOS -->
                <div class="col-md-6 col-lg-3">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-check"></i> Listos</h3>
                        </div>
                        <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                            <div id="pedidos-listos">
                                <div class="p-3 text-center text-muted">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EN CAMINO -->
                <div class="col-md-6 col-lg-3">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-motorcycle"></i> En Camino</h3>
                        </div>
                        <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                            <div id="pedidos-camino">
                                <div class="p-3 text-center text-muted">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle del Pedido -->
<div class="modal fade" id="modalDetallePedido" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalle del Pedido
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detalle-pedido-body">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Cargando...</p>
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
                    <h5 class="modal-title">
                        <i class="fas fa-exchange-alt"></i> Cambiar Estado del Pedido
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_pedido" id="id_pedido_estado">
                    <input type="hidden" name="redirect" value="tracking">
                    
                    <div class="form-group">
                        <label for="id_estado">Nuevo Estado <span class="text-danger">*</span></label>
                        <select name="id_estado" id="id_estado" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($estados as $estado): ?>
                                <option value="<?php echo $estado['id_estado']; ?>">
                                    <?php echo htmlspecialchars($estado['nombre_estado']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="observaciones_tracking">Observaciones (opcional)</label>
                        <textarea name="observaciones" 
                                  id="observaciones_tracking" 
                                  class="form-control" 
                                  rows="2"
                                  placeholder="Ingrese observaciones..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Estado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('../../contans/layout/mensajes.php'); ?>
<?php include('../../contans/layout/parte2.php'); ?>

<script>
    // Datos de pedidos (convertir PHP a JSON de forma segura)
    const pedidos = <?php echo json_encode($pedidos_activos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    // Función para normalizar nombres de estado
    function normalizarEstado(estado) {
        const mapa = {
            'Pendiente': 'PENDIENTE',
            'En Preparación': 'EN_PREPARACION',
            'Listo': 'LISTO',
            'En Camino': 'EN_CAMINO',
            'Entregado': 'ENTREGADO',
            'Cancelado': 'CANCELADO'
        };
        return mapa[estado] || estado.toUpperCase().replace(/ /g, '_');
    }

    // Renderizar pedidos
    function renderizarPedidos() {
        const pendientes = document.getElementById('pedidos-pendientes');
        const preparacion = document.getElementById('pedidos-preparacion');
        const listos = document.getElementById('pedidos-listos');
        const camino = document.getElementById('pedidos-camino');
        
        pendientes.innerHTML = '';
        preparacion.innerHTML = '';
        listos.innerHTML = '';
        camino.innerHTML = '';
        
        let countPendientes = 0;
        let countPreparacion = 0;
        let countListos = 0;
        let countCamino = 0;
        
        pedidos.forEach(pedido => {
            const card = crearCardPedido(pedido);
            const estadoNormalizado = normalizarEstado(pedido.estado);
            
            switch (estadoNormalizado) {
                case 'PENDIENTE':
                    pendientes.innerHTML += card;
                    countPendientes++;
                    break;
                case 'EN_PREPARACION':
                    preparacion.innerHTML += card;
                    countPreparacion++;
                    break;
                case 'LISTO':
                    listos.innerHTML += card;
                    countListos++;
                    break;
                case 'EN_CAMINO':
                    camino.innerHTML += card;
                    countCamino++;
                    break;
            }
        });
        
        // Actualizar contadores
        document.getElementById('count-pendientes').textContent = countPendientes;
        document.getElementById('count-preparacion').textContent = countPreparacion;
        document.getElementById('count-listos').textContent = countListos;
        document.getElementById('count-camino').textContent = countCamino;
        
        // Mensajes si no hay pedidos
        if (countPendientes === 0) pendientes.innerHTML = '<div class="p-3 text-center text-muted"><i class="fas fa-check-circle"></i><br>Sin pedidos pendientes</div>';
        if (countPreparacion === 0) preparacion.innerHTML = '<div class="p-3 text-center text-muted"><i class="fas fa-check-circle"></i><br>Sin pedidos en preparación</div>';
        if (countListos === 0) listos.innerHTML = '<div class="p-3 text-center text-muted"><i class="fas fa-check-circle"></i><br>Sin pedidos listos</div>';
        if (countCamino === 0) camino.innerHTML = '<div class="p-3 text-center text-muted"><i class="fas fa-check-circle"></i><br>Sin pedidos en camino</div>';
    }

    // Crear card de pedido
    function crearCardPedido(pedido) {
        const tiempoTranscurrido = calcularTiempo(pedido.fecha_pedido);
        const colorTiempo = tiempoTranscurrido > 30 ? 'danger' : (tiempoTranscurrido > 15 ? 'warning' : 'success');
        
        let iconoTipo = 'fa-utensils';
        let badgeTipo = 'badge-info';
        const tipoPedidoLower = pedido.tipo_pedido.toLowerCase();
        
        if (tipoPedidoLower === 'delivery') {
            iconoTipo = 'fa-motorcycle';
            badgeTipo = 'badge-warning';
        } else if (tipoPedidoLower === 'para_llevar') {
            iconoTipo = 'fa-shopping-bag';
            badgeTipo = 'badge-success';
        } else if (tipoPedidoLower === 'mesa') {
            iconoTipo = 'fa-chair';
            badgeTipo = 'badge-primary';
        }
        
        const numeroComanda = pedido.numero_comanda || pedido.nro_pedido || 'PED-' + pedido.id_pedido;
        const nombreCliente = (pedido.cliente_nombre || '') + ' ' + (pedido.cliente_apellidos || '');
        
        return `
            <div class="pedido-card p-3 border-bottom" style="cursor: pointer;" 
                 onclick="verDetalle(${pedido.id_pedido})">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="mb-0">
                            <i class="fas ${iconoTipo}"></i>
                            ${escapeHtml(numeroComanda)}
                        </h5>
                        <small class="text-muted">
                            ${escapeHtml(nombreCliente.trim())}
                        </small>
                    </div>
                    <span class="badge badge-${colorTiempo}">
                        <i class="fas fa-clock"></i> ${tiempoTranscurrido} min
                    </span>
                </div>
                <div class="mb-2">
                    ${tipoPedidoLower === 'mesa' && pedido.numero_mesa ? 
                        '<span class="badge badge-dark"><i class="fas fa-chair"></i> Mesa ' + escapeHtml(pedido.numero_mesa) + '</span>' : 
                        '<span class="badge ' + badgeTipo + '">' + escapeHtml(pedido.tipo_pedido) + '</span>'
                    }
                    <strong class="ml-2 text-success">
                        <i class="fas fa-money-bill-wave"></i> S/ ${parseFloat(pedido.total).toFixed(2)}
                    </strong>
                </div>
                <div class="btn-group btn-group-sm w-100">
                    <button class="btn btn-outline-primary" 
                            onclick="event.stopPropagation(); cambiarEstado(${pedido.id_pedido})"
                            title="Cambiar Estado">
                        <i class="fas fa-exchange-alt"></i> Cambiar
                    </button>
                    <button class="btn btn-outline-info" 
                            onclick="event.stopPropagation(); verDetalle(${pedido.id_pedido})"
                            title="Ver Detalles">
                        <i class="fas fa-eye"></i> Ver
                    </button>
                </div>
            </div>
        `;
    }

    // Escapar HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Calcular tiempo transcurrido
    function calcularTiempo(fecha) {
        const ahora = new Date();
        const pedido = new Date(fecha);
        const diff = Math.floor((ahora - pedido) / 60000); // minutos
        return diff;
    }

    // Cambiar estado
    function cambiarEstado(id) {
        document.getElementById('id_pedido_estado').value = id;
        $('#modalCambiarEstado').modal('show');
    }

    // Ver detalle
    function verDetalle(id) {
        const pedido = pedidos.find(p => p.id_pedido == id);
        if (!pedido) {
            alert('Pedido no encontrado');
            return;
        }
        
        const nombreCliente = (pedido.cliente_nombre || '') + ' ' + (pedido.cliente_apellidos || '');
        const numeroComanda = pedido.numero_comanda || pedido.nro_pedido || 'PED-' + pedido.id_pedido;
        const fechaFormateada = new Date(pedido.fecha_pedido).toLocaleString('es-PE');
        
        const html = `
            <div class="row">
                <div class="col-md-6">
                    <dl>
                        <dt><i class="fas fa-hashtag"></i> Pedido:</dt>
                        <dd><strong>${escapeHtml(numeroComanda)}</strong></dd>
                        
                        <dt><i class="fas fa-user"></i> Cliente:</dt>
                        <dd>${escapeHtml(nombreCliente.trim())}</dd>
                        
                        <dt><i class="fas fa-phone"></i> Teléfono:</dt>
                        <dd><a href="tel:${pedido.cliente_telefono}">${escapeHtml(pedido.cliente_telefono)}</a></dd>
                        
                        <dt><i class="fas fa-tag"></i> Tipo:</dt>
                        <dd><span class="badge badge-info">${escapeHtml(pedido.tipo_pedido)}</span></dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl>
                        <dt><i class="fas fa-info-circle"></i> Estado:</dt>
                        <dd><span class="badge badge-primary">${escapeHtml(pedido.estado)}</span></dd>
                        
                        <dt><i class="fas fa-money-bill-wave"></i> Total:</dt>
                        <dd><strong class="text-success">S/ ${parseFloat(pedido.total).toFixed(2)}</strong></dd>
                        
                        <dt><i class="fas fa-clock"></i> Hora:</dt>
                        <dd>${fechaFormateada}</dd>
                        
                        ${pedido.numero_mesa ? `
                        <dt><i class="fas fa-chair"></i> Mesa:</dt>
                        <dd><span class="badge badge-dark">Mesa ${escapeHtml(pedido.numero_mesa)}</span></dd>
                        ` : ''}
                    </dl>
                </div>
            </div>
            ${pedido.observaciones ? `
            <hr>
            <div class="alert alert-info">
                <strong><i class="fas fa-comment"></i> Observaciones:</strong><br>
                ${escapeHtml(pedido.observaciones)}
            </div>
            ` : ''}
            <hr>
            <div class="text-center">
                <a href="<?php echo URL_BASE; ?>/views/pedidos/show.php?id=${pedido.id_pedido}" 
                   class="btn btn-primary" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Ver Detalle Completo
                </a>
            </div>
        `;
        
        document.getElementById('detalle-pedido-body').innerHTML = html;
        $('#modalDetallePedido').modal('show');
    }

    // Inicializar
    renderizarPedidos();

    // Auto-refresh cada 30 segundos
    let refreshInterval = setInterval(function() {
        location.reload();
    }, 30000);

    // Limpiar intervalo al cerrar
    window.addEventListener('beforeunload', function() {
        clearInterval(refreshInterval);
    });
</script>

<style>
    .pedido-card {
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }
    
    .pedido-card:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        border-left-color: #007bff;
    }
    
    .card-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .card-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .card-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }
    
    .card-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    dl {
        margin-bottom: 0;
    }
    
    dt {
        font-weight: 600;
        color: #6c757d;
        margin-top: 10px;
    }
    
    dd {
        margin-bottom: 5px;
    }
</style>
