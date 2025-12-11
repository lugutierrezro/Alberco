<?php
include ('../../database/..');
include ('../www.administracionalberco.com/Contans/layout/sesion.php');//SESION

include ('../www.administracionalberco.com/Contans/layout/parte1.php');
include ('../../../models/cliente/cliente.php');

$clienteModel = new Cliente($pdo);
$clientes = $clienteModel->obtenerClientes();
?>

<style>
    /* Estilos personalizados con colores rojo, amarillo, naranja */
    .content-header {
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        box-shadow: 0 5px 20px rgba(255, 107, 53, 0.3);
    }
    
    .content-header h1 {
        color: #ffffff;
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }
    
    .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.9);
        font-weight: 600;
    }
    
    .breadcrumb-item.active {
        color: #ffffff;
        font-weight: 700;
    }
    
    .card-primary.card-outline {
        border-top: 4px solid #ff6b35;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        border-radius: 15px;
    }
    
    .card-header {
        background: linear-gradient(135deg, #fff5e6 0%, #ffe8cc 100%);
        border-bottom: 3px solid #f7931e;
    }
    
    .card-title {
        color: #ff6b35;
        font-weight: 700;
        font-size: 1.3rem;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        border: none;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4);
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #f7931e 0%, #ffc107 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 107, 53, 0.6);
    }
    
    .modal-header.bg-primary {
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%) !important;
        color: #ffffff;
    }
    
    .modal-header.bg-success {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%) !important;
        color: #ffffff;
    }
    
    .modal-header.bg-info {
        background: linear-gradient(135deg, #f7931e 0%, #ffc107 100%) !important;
        color: #ffffff;
    }
    
    .btn-info {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        border: none;
    }
    
    .btn-success {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        border: none;
    }
    
    .btn-danger {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        border: none;
    }
    
    .table-hover tbody tr:hover {
        background: rgba(255, 107, 53, 0.1);
    }
    
    .btn-search-api {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: #ffffff;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-search-api:hover {
        background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
        transform: scale(1.05);
    }
    
    .badge-api-live {
        background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        color: #ffffff;
        padding: 0.3rem 0.6rem;
        border-radius: 8px;
        font-weight: 600;
        animation: pulse-api 2s infinite;
    }
    
    @keyframes pulse-api {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
</style>

<div class="content-wrapper">
    <div class="content-header"> 
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-users-cog mr-2"></i>Gestión de Clientes</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL;?>">Inicio</a></li>
                        <li class="breadcrumb-item active">Clientes</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-2"></i>Listado de Clientes
                                <span class="badge badge-api-live ml-2">
                                    <i class="fas fa-plug"></i> Busqueda
                                </span>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalNuevoCliente">
                                    <i class="fas fa-plus"></i> Nuevo Cliente
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tabla_clientes" class="table table-bordered table-striped table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Nro</th>
                                        <th>Nombre</th>
                                        <th>Tipo Doc.</th>
                                        <th>Nro Documento</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th>Dirección</th>
                                        <th>Fecha Registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $contador = 0;
                                    foreach ($clientes as $cliente) {
                                        $contador++;
                                    ?>
                                        <tr>
                                            <td><?php echo $contador; ?></td>
                                            <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($cliente['tipo_documento'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($cliente['numero_documento'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                                            <td><?php echo htmlspecialchars($cliente['email'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars(substr($cliente['direccion'] ?? '-', 0, 30)); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($cliente['fyh_creacion'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-info btn-sm" onclick="verCliente(<?php echo $cliente['id_cliente']; ?>)" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-success btn-sm" onclick="editarCliente(<?php echo $cliente['id_cliente']; ?>)" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="eliminarCliente(<?php echo $cliente['id_cliente']; ?>)" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Cliente -->
<div class="modal fade" id="modalNuevoCliente">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title">
                    <i class="fas fa-user-plus mr-2"></i>Registrar Nuevo Cliente
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formNuevoCliente">
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Busqueda:</strong> Consulta automática de DNI Y RUC
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo de Documento *</label>
                                <select name="tipo_documento" id="tipo_documento" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                    <option value="DNI">DNI</option>
                                    <option value="RUC">RUC</option>
                                    <option value="CE">Carnet de Extranjería</option>
                                    <option value="PASAPORTE">Pasaporte</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Número de Documento *</label>
                                <input type="text" name="numero_documento" id="numero_documento" class="form-control" required maxlength="11">
                                <small class="form-text text-muted">DNI: 8 dígitos | RUC: 11 dígitos</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-search-api btn-block" id="btnBuscarDocumento">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nombre Completo / Razón Social *</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Teléfono *</label>
                                <input type="text" name="telefono" id="telefono" class="form-control" required maxlength="9">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" id="email" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Dirección</label>
                        <textarea name="direccion" id="direccion" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cliente
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Cliente -->
<div class="modal fade" id="modalEditarCliente">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>Editar Cliente
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formEditarCliente">
                <input type="hidden" name="id_cliente" id="edit_id_cliente">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de Documento *</label>
                                <select name="tipo_documento" id="edit_tipo_documento" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                    <option value="DNI">DNI</option>
                                    <option value="RUC">RUC</option>
                                    <option value="CE">Carnet de Extranjería</option>
                                    <option value="PASAPORTE">Pasaporte</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Número de Documento *</label>
                                <input type="text" name="numero_documento" id="edit_numero_documento" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nombre Completo *</label>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Teléfono *</label>
                                <input type="text" name="telefono" id="edit_telefono" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Dirección</label>
                        <textarea name="direccion" id="edit_direccion" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Cliente -->
<div class="modal fade" id="modalVerCliente">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title">
                    <i class="fas fa-user-circle mr-2"></i>Detalles del Cliente
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="contenidoVerCliente">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    $("#tabla_clientes").DataTable({
        "pageLength": 10,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "responsive": true,
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#tabla_clientes_wrapper .col-md-6:eq(0)');
});

// ============================================
// CONSULTA DIRECTA A LA API (SIN BACKEND PHP)
// ============================================
const API_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6ImpvcnkxMDAtemlvQGhvdG1haWwuY29tIn0.bDF-KMUzEFMFgb6YX6ew9YF44JsAQQUKOWYQXljud4I';

$('#btnBuscarDocumento').click(function() {
    const tipo = $('#tipo_documento').val();
    const numero = $('#numero_documento').val();
    
    if (!tipo || !numero) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Seleccione tipo y número de documento',
            confirmButtonColor: '#ff6b35'
        });
        return;
    }

    // Validaciones
    if (tipo === 'DNI' && numero.length !== 8) {
        Swal.fire({
            icon: 'error',
            title: 'Error de validación',
            text: 'El DNI debe tener 8 dígitos',
            confirmButtonColor: '#ff6b35'
        });
        return;
    }

    if (tipo === 'RUC' && numero.length !== 11) {
        Swal.fire({
            icon: 'error',
            title: 'Error de validación',
            text: 'El RUC debe tener 11 dígitos',
            confirmButtonColor: '#ff6b35'
        });
        return;
    }

    const btn = $(this);
    const originalHtml = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i> Consultando...').prop('disabled', true);

    // Determinar URL según tipo de documento
    let apiUrl = '';
    if (tipo === 'DNI') {
        apiUrl = `https://dniruc.apisperu.com/api/v1/dni/${numero}?token=${API_TOKEN}`;
    } else if (tipo === 'RUC') {
        apiUrl = `https://dniruc.apisperu.com/api/v1/ruc/${numero}?token=${API_TOKEN}`;
    } else {
        Swal.fire({
            icon: 'info',
            title: 'Información',
            text: 'La consulta API solo funciona para DNI y RUC',
            confirmButtonColor: '#ff6b35'
        });
        btn.html(originalHtml).prop('disabled', false);
        return;
    }

    // Llamada directa a la API usando fetch
    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta de la API');
            }
            return response.json();
        })
        .then(data => {
            console.log('Respuesta API:', data);
            btn.html(originalHtml).prop('disabled', false);
            
            if (tipo === 'DNI') {
                // Procesar respuesta DNI
                if (data.nombres && data.apellidoPaterno && data.apellidoMaterno) {
                    const nombreCompleto = `${data.nombres} ${data.apellidoPaterno} ${data.apellidoMaterno}`;
                    $('#nombre').val(nombreCompleto);
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Datos encontrados!',
                        html: `<strong>DNI validado por RENIEC</strong><br>
                               <small>${nombreCompleto}</small>`,
                        confirmButtonColor: '#ff6b35',
                        timer: 3000
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'DNI no encontrado',
                        text: 'El número de DNI no existe en RENIEC',
                        confirmButtonColor: '#ff6b35'
                    });
                }
            } else if (tipo === 'RUC') {
                // Procesar respuesta RUC
                if (data.razonSocial || data.nombre) {
                    $('#nombre').val(data.razonSocial || data.nombre);
                    $('#direccion').val(data.direccion || '');
                    
                    let estadoHtml = '';
                    if (data.estado) {
                        estadoHtml = `<br><small>Estado: ${data.estado} - ${data.condicion || ''}</small>`;
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Datos encontrados!',
                        html: `<strong>RUC validado por SUNAT</strong><br>
                               <small>${data.razonSocial || data.nombre}</small>
                               ${estadoHtml}`,
                        confirmButtonColor: '#ff6b35',
                        timer: 3500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'RUC no encontrado',
                        text: 'El número de RUC no existe en SUNAT',
                        confirmButtonColor: '#ff6b35'
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.html(originalHtml).prop('disabled', false);
            
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                html: `<strong>No se pudo conectar con la API</strong><br>
                       <small>Verifique su conexión a internet o intente más tarde</small><br>
                       <small class="text-muted">Error: ${error.message}</small>`,
                confirmButtonColor: '#ff6b35'
            });
        });
});

// Crear cliente
$('#formNuevoCliente').submit(function(e) {
    e.preventDefault();

    $.ajax({
        url: '<?php echo $URL; ?>/app/controllers/clientes/crear_cliente.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message,
                    confirmButtonColor: '#ff6b35'
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message,
                    confirmButtonColor: '#ff6b35'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo procesar la solicitud',
                confirmButtonColor: '#ff6b35'
            });
        }
    });
});

// Ver cliente
function verCliente(id) {
    $('#modalVerCliente').modal('show');
    $('#contenidoVerCliente').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x text-primary"></i></div>');

    $.get('<?php echo $URL; ?>/app/controllers/clientes/ver_cliente.php', {id: id}, function(response) {
        if (response.success) {
            const c = response.cliente;
            const stats = response.estadisticas;
            
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-user text-warning mr-2"></i>Nombre:</strong> ${c.nombre}</p>
                        <p><strong><i class="fas fa-id-card text-info mr-2"></i>Documento:</strong> ${c.tipo_documento} - ${c.numero_documento}</p>
                        <p><strong><i class="fas fa-phone text-success mr-2"></i>Teléfono:</strong> ${c.telefono}</p>
                        <p><strong><i class="fas fa-envelope text-danger mr-2"></i>Email:</strong> ${c.email || 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-map-marker-alt text-primary mr-2"></i>Dirección:</strong> ${c.direccion || 'N/A'}</p>
                        <p><strong><i class="fas fa-shopping-cart text-warning mr-2"></i>Total Pedidos:</strong> ${stats.total_pedidos || 0}</p>
                        <p><strong><i class="fas fa-money-bill-wave text-success mr-2"></i>Total Gastado:</strong> S/ ${parseFloat(stats.total_gastado || 0).toFixed(2)}</p>
                        <p><strong><i class="fas fa-calendar-alt text-info mr-2"></i>Última Compra:</strong> ${stats.ultima_compra || 'N/A'}</p>
                    </div>
                </div>
            `;
            
            $('#contenidoVerCliente').html(html);
        } else {
            $('#contenidoVerCliente').html('<p class="text-danger">Error al cargar datos</p>');
        }
    }, 'json');
}

// Editar cliente
function editarCliente(id) {
    $.get('<?php echo $URL; ?>/app/controllers/clientes/ver_cliente.php', {id: id}, function(response) {
        if (response.success) {
            const c = response.cliente;
            $('#edit_id_cliente').val(c.id_cliente);
            $('#edit_tipo_documento').val(c.tipo_documento);
            $('#edit_numero_documento').val(c.numero_documento);
            $('#edit_nombre').val(c.nombre);
            $('#edit_telefono').val(c.telefono);
            $('#edit_email').val(c.email);
            $('#edit_direccion').val(c.direccion);
            $('#modalEditarCliente').modal('show');
        }
    }, 'json');
}

$('#formEditarCliente').submit(function(e) {
    e.preventDefault();

    $.ajax({
        url: '<?php echo $URL; ?>/app/controllers/clientes/actualizar_cliente.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message,
                    confirmButtonColor: '#ff6b35'
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message,
                    confirmButtonColor: '#ff6b35'
                });
            }
        }
    });
});

// Eliminar cliente
function eliminarCliente(id) {
    Swal.fire({
        title: '¿Eliminar cliente?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?php echo $URL; ?>/app/controllers/clientes/eliminar_cliente.php', {id_cliente: id}, function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: response.message,
                        confirmButtonColor: '#ff6b35'
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                        confirmButtonColor: '#ff6b35'
                    });
                }
            }, 'json');
        }
    });
}
</script>

<?php include '../../layout/parte2.php'; ?>
<?php include '.../../layout/mensajes.php'; ?>
