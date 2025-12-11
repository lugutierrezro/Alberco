<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');
include ('../../controllers/mesas/listar.php'); 
include ('../../controllers/mesas/estadisticas.php'); 
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Gestión de Mesas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item active">Mesas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">

            <!-- Estadísticas -->
            <div class="row mb-3">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $estadisticas_mesas['total_mesas'] ?? 0; ?></h3>
                            <p>Total de Mesas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-th"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $estadisticas_mesas['disponibles'] ?? 0; ?></h3>
                            <p>Disponibles</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo $estadisticas_mesas['ocupadas'] ?? 0; ?></h3>
                            <p>Ocupadas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $estadisticas_mesas['reservadas'] ?? 0; ?></h3>
                            <p>Reservadas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vista de Tarjetas -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header bg-gradient-primary">
                            <h3 class="card-title"><i class="fas fa-th"></i> Estado de las Mesas</h3>
                            <div class="card-tools">
                                <button type="button" id="autoRefreshBtn" class="btn btn-sm btn-success" onclick="toggleAutoRefresh()" title="Auto-actualizar">
                                    <i class="fas fa-pause"></i>
                                </button>
                                <a href="create.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Nueva Mesa
                                </a>
                                <button type="button" class="btn btn-sm btn-info" onclick="verVistaTarjetas()">
                                    <i class="fas fa-th"></i> Tarjetas
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="verVistaTabla()">
                                    <i class="fas fa-list"></i> Tabla
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Filtros rápidos -->
                        <div class="card-body bg-light pb-2">
                            <div class="text-center">
                                <span class="filter-chip" onclick="filtrarPorZona(null)">
                                    <i class="fas fa-globe"></i> Todas
                                </span>
                                <span class="filter-chip" onclick="filtrarPorZona('Salón Principal')">
                                    <i class="fas fa-utensils"></i> Salón Principal
                                </span>
                                <span class="filter-chip" onclick="filtrarPorZona('Terraza')">
                                    <i class="fas fa-tree"></i> Terraza
                                </span>
                                <span class="filter-chip" onclick="filtrarPorZona('Salón VIP')">
                                    <i class="fas fa-star"></i> Salón VIP
                                </span>
                                <span class="filter-chip" onclick="filtrarPorZona('PRINCIPAL')">
                                    <i class="fas fa-home"></i> Principal
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            
                            <!-- Vista Tarjetas -->
                            <div id="vistaTarjetas">
                                <div class="row">
                                    <?php foreach ($mesas_datos as $mesa){
                                        
                                        $id_mesa = $mesa['id_mesa'];
                                        $numero_mesa = $mesa['numero_mesa'];
                                        $capacidad = $mesa['capacidad'];
                                        $estado = $mesa['estado'];
                                        $zona = $mesa['zona'] ?? 'Principal';
                                        $pedidos_activos = $mesa['pedidos_activos'] ?? 0;

                                        // Estados correctos en minúscula
                                        $clase_estado = 'mesa-disponible';
                                        $icono_estado = 'fa-check-circle';
                                        $texto_estado = 'Disponible';
                                        
                                        switch ($estado) {
                                            case 'ocupada':
                                                $clase_estado = 'mesa-ocupada';
                                                $icono_estado = 'fa-users';
                                                $texto_estado = 'Ocupada';
                                                break;
                                            case 'reservada':
                                                $clase_estado = 'mesa-reservada';
                                                $icono_estado = 'fa-calendar-check';
                                                $texto_estado = 'Reservada';
                                                break;
                                            case 'mantenimiento':
                                                $clase_estado = 'mesa-mantenimiento';
                                                $icono_estado = 'fa-tools';
                                                $texto_estado = 'Mantenimiento';
                                                break;
                                        }
                                    ?>
                                    
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3 mesa-card-container" data-zona="<?php echo htmlspecialchars($zona); ?>">
                                        <div class="card mesa-card <?php echo $clase_estado; ?>" 
                                             style="cursor: pointer;"
                                             onclick="verDetalleMesa(<?php echo $id_mesa; ?>)">
                                             
                                            <?php if ($pedidos_activos > 0): ?>
                                            <span class="pedidos-badge">
                                                <?php echo $pedidos_activos; ?> <i class="fas fa-clipboard-list"></i>
                                            </span>
                                            <?php endif; ?>
                                            
                                            <div class="card-body text-center p-3">
                                                <i class="fas fa-chair mesa-icon"></i>
                                                
                                                <div class="mesa-numero"><?php echo $numero_mesa; ?></div>
                                                
                                                <div class="capacidad-info">
                                                    <i class="fas fa-user"></i> <?php echo $capacidad; ?> personas
                                                </div>
                                                
                                                <div class="mb-2">
                                                    <small><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($zona); ?></small>
                                                </div>

                                                <div class="badge badge-light badge-lg mb-3" style="font-size: 1rem;">
                                                    <i class="fas <?php echo $icono_estado; ?>"></i>
                                                    <?php echo $texto_estado; ?>
                                                </div>
                                                
                                                <!-- Botones de acción rápida -->
                                                <div class="mt-3" onclick="event.stopPropagation();">
                                                    <div class="btn-group-vertical w-100" role="group">
                                                        <?php if ($estado == 'disponible'): ?>
                                                        <button class="btn btn-sm btn-light mb-1" 
                                                                onclick="cambiarEstadoRapido(<?php echo $id_mesa; ?>, 'ocupada')"
                                                                title="Marcar ocupada">
                                                            <i class="fas fa-users"></i> Ocupar
                                                        </button>
                                                        <?php elseif ($estado == 'ocupada'): ?>
                                                        <button class="btn btn-sm btn-light mb-1" 
                                                                onclick="cambiarEstadoRapido(<?php echo $id_mesa; ?>, 'disponible')"
                                                                title="Liberar mesa">
                                                            <i class="fas fa-check"></i> Liberar
                                                        </button>
                                                        <?php endif; ?>
                                                        
                                                        <a href="update.php?id=<?php echo $id_mesa; ?>" 
                                                           class="btn btn-sm btn-light"
                                                           title="Editar">
                                                            <i class="fas fa-edit"></i> Editar
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php } ?>
                                </div>
                            </div>

                            <!-- Vista Tabla -->
                            <div id="vistaTabla" style="display: none;">
                                <table id="example1" class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th><center>Nro</center></th>
                                            <th><center>Mesa</center></th>
                                            <th><center>Capacidad</center></th>
                                            <th><center>Zona</center></th>
                                            <th><center>Estado</center></th>
                                            <th><center>Pedidos Activos</center></th>
                                            <th><center>Acciones</center></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $contador = 0;
                                        foreach ($mesas_datos as $mesa){
                                            $contador++;
                                            $estado = $mesa['estado'];

                                            // Badge estado tabla
                                            $badge_estado = 'success';
                                            switch ($estado) {
                                                case 'ocupada': $badge_estado = 'danger'; break;
                                                case 'reservada': $badge_estado = 'warning'; break;
                                                case 'mantenimiento': $badge_estado = 'secondary'; break;
                                            }
                                        ?>
                                        <tr>
                                            <td><center><?php echo $contador; ?></center></td>
                                            <td><center><strong>Mesa <?php echo $mesa['numero_mesa']; ?></strong></center></td>
                                            <td><center><?php echo $mesa['capacidad']; ?> personas</center></td>
                                            <td><center><?php echo htmlspecialchars($mesa['zona'] ?? 'Principal'); ?></center></td>
                                            <td><center>
                                                <span class="badge badge-<?php echo $badge_estado; ?>">
                                                    <?php echo ucfirst($estado); ?>
                                                </span>
                                            </center></td>
                                            <td><center>
                                                <span class="badge badge-info">
                                                    <?php echo $mesa['pedidos_activos'] ?? 0; ?>
                                                </span>
                                            </center></td>
                                            <td>
                                                <center>
                                                    <div class="btn-group">
                                                        <button type="button" 
                                                                class="btn btn-primary btn-sm" 
                                                                onclick="cambiarEstadoMesa(<?php echo $mesa['id_mesa']; ?>)"
                                                                title="Cambiar estado">
                                                            <i class="fas fa-exchange-alt"></i>
                                                        </button>
                                                        <a href="update.php?id=<?php echo $mesa['id_mesa']; ?>" 
                                                           class="btn btn-success btn-sm" 
                                                           title="Editar">
                                                            <i class="fa fa-pencil-alt"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-danger btn-sm" 
                                                                onclick="confirmarEliminar(<?php echo $mesa['id_mesa']; ?>)"
                                                                title="Eliminar">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </center>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                        </div> <!-- card-body -->
                    </div> <!-- card -->
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Panel de acciones flotantes -->
<div class="quick-actions-panel">
    <button class="fab-button" onclick="window.location.href='create.php'" title="Nueva Mesa">
        <i class="fas fa-plus"></i>
    </button>
    <button class="fab-button" onclick="location.reload()" title="Actualizar">
        <i class="fas fa-sync-alt"></i>
    </button>
</div>

<!-- Modal Cambiar Estado -->
<div class="modal fade" id="modalCambiarEstado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../../controllers/mesas/cambiar_estado.php" method="post">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">Cambiar Estado de Mesa</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_mesa" id="id_mesa_estado">
                    
                    <div class="form-group">
                        <label for="estado">Nuevo Estado</label>
                        <select name="estado" id="estado" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="disponible">Disponible</option>
                            <option value="ocupada">Ocupada</option>
                            <option value="reservada">Reservada</option>
                            <option value="mantenimiento">Mantenimiento</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form para eliminar -->
<form id="formEliminar" action="../../controllers/mesas/eliminar.php" method="post" style="display: none;">
    <input type="hidden" name="id_mesa" id="id_mesa_eliminar">
</form>

<?php include ('../../contans/layout/parte2.php'); ?>
<?php include ('../../contans/layout/mensajes.php'); ?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 10,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Mesas",
                "infoEmpty": "Mostrando 0 a 0 de 0 Mesas",
                "infoFiltered": "(Filtrado de _MAX_ total Mesas)",
                "lengthMenu": "Mostrar _MENU_ Mesas",
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
            "autoWidth": false
        });
    });

    function verVistaTarjetas() {
        document.getElementById('vistaTarjetas').style.display = 'block';
        document.getElementById('vistaTabla').style.display = 'none';
    }

    function verVistaTabla() {
        document.getElementById('vistaTarjetas').style.display = 'none';
        document.getElementById('vistaTabla').style.display = 'block';
    }

    function cambiarEstadoMesa(id) {
        document.getElementById('id_mesa_estado').value = id;
        $('#modalCambiarEstado').modal('show');
    }

    function verDetalleMesa(id) {
        window.location.href = 'show.php?id=' + id;
    }

    function confirmarEliminar(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "¿Desea eliminar esta mesa?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('id_mesa_eliminar').value = id;
                document.getElementById('formEliminar').submit();
            }
        });
    }

    // Auto-refresh cada 30s
    setTimeout(() => location.reload(), 30000);
</script>

<style>
    /* Animaciones personalizadas */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    /* Tarjetas de mesa mejoradas */
    .mesa-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        animation: slideIn 0.5s ease-out;
        position: relative;
        overflow: hidden;
    }
    
    .mesa-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 12px 24px rgba(0,0,0,0.2);
    }
    
    .mesa-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }
    
    .mesa-card:hover::before {
        left: 100%;
    }
    
    /* Estados con efectos */
    .mesa-disponible {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }
    
    .mesa-ocupada {
        background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
        color: white;
        animation: pulse 2s infinite;
    }
    
    .mesa-reservada {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        color: white;
    }
    
    .mesa-mantenimiento {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }
    
    /* Botones de acción rápida */
    .quick-action-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        font-size: 24px;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border: none;
        margin: 5px;
    }
    
    .quick-action-btn:hover {
        transform: scale(1.15) rotate(5deg);
        box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    }
    
    .quick-action-btn:active {
        transform: scale(0.95);
    }
    
    /* Badge de pedidos con animación */
    .pedidos-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #ff4444;
        color: white;
        border-radius: 20px;
        padding: 5px 12px;
        font-weight: bold;
        animation: pulse 1.5s infinite;
        z-index: 10;
    }
    
    /* Iconos animados */
    .mesa-icon {
        font-size: 3rem;
        margin-bottom: 10px;
        transition: all 0.3s;
    }
    
    .mesa-card:hover .mesa-icon {
        transform: rotate(360deg) scale(1.2);
    }
    
    /* Panel de acciones rápidas flotante */
    .quick-actions-panel {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .fab-button {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        border: none;
        box-shadow: 0 4px 12px rgba(0,123,255,0.4);
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    
    .fab-button:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(0,123,255,0.6);
    }
    
    /* Filtros animados */
    .filter-chip {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 25px;
        margin: 5px;
        cursor: pointer;
        transition: all 0.3s;
        border: 2px solid #dee2e6;
        background: white;
    }
    
    .filter-chip:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .filter-chip.active {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        border-color: #007bff;
    }
    
    /* Número de mesa destacado */
    .mesa-numero {
        font-size: 2.5rem;
        font-weight: bold;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }
    
    /* Información de capacidad */
    .capacidad-info {
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
        padding: 5px 10px;
        margin: 5px 0;
    }
    
    /* Responsive mejoras */
    @media (max-width: 768px) {
        .mesa-card {
            margin-bottom: 15px;
        }
        .quick-actions-panel {
            bottom: 15px;
            right: 15px;
        }
    }
    
    /* Loading spinner */
    .loading-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #007bff;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 20px auto;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<script>
    // Acciones rápidas mejoradas
    function cambiarEstadoRapido(id, nuevoEstado) {
        Swal.fire({
            title: '¿Cambiar estado?',
            text: `Estado: ${nuevoEstado}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar',
            timer: 3000,
            timerProgressBar: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Crear form y enviar
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../../controllers/mesas/cambiar_estado.php';
                
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id_mesa';
                inputId.value = id;
                
                const inputEstado = document.createElement('input');
                inputEstado.type = 'hidden';
                inputEstado.name = 'estado';
                inputEstado.value = nuevoEstado;
                
                form.appendChild(inputId);
                form.appendChild(inputEstado);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    
    // Filtrado rápido por zona
    function filtrarPorZona(zona) {
        const mesas = document.querySelectorAll('.mesa-card-container');
        mesas.forEach(mesa => {
            const mesaZona = mesa.dataset.zona;
            if (!zona || mesaZona === zona) {
                mesa.style.display = 'block';
                mesa.style.animation = 'slideIn 0.5s';
            } else {
                mesa.style.display = 'none';
            }
        });
        
        // Actualizar chips activos
        document.querySelectorAll('.filter-chip').forEach(chip => {
            chip.classList.remove('active');
        });
        if (zona) {
            event.target.classList.add('active');
        }
    }
    
    // Auto-refresh mejorado con indicador visual
    let autoRefreshInterval;
    function toggleAutoRefresh() {
        const button = document.getElementById('autoRefreshBtn');
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
            button.innerHTML = '<i class="fas fa-play"></i>';
            button.classList.remove('btn-danger');
            button.classList.add('btn-success');
        } else {
            autoRefreshInterval = setInterval(() => {
                location.reload();
            }, 15000);
            button.innerHTML = '<i class="fas fa-pause"></i>';
            button.classList.remove('btn-success');
            button.classList.add('btn-danger');
        }
    }
    
    // Iniciar auto-refresh por defecto
    document.addEventListener('DOMContentLoaded', function() {
        toggleAutoRefresh();
    });
</script>
