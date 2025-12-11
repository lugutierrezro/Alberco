<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');
include ('../../controllers/usuario/listar.php'); 
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Listado de Usuarios</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Usuarios Registrados</h3>
                            <div class="card-tools">
                                <a href="inactivos.php" class="btn btn-danger btn-sm mr-2">
                                    <i class="fas fa-user-slash"></i> Usuarios Inactivos
                                </a>
                                <a href="create.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-user-plus"></i> Nuevo Usuario
                                </a>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th><center>Nro</center></th>
                                    <th><center>Nombres</center></th>
                                    <th><center>Email</center></th>
                                    <th><center>Rol</center></th>
                                    <th><center>Estado</center></th>
                                    <th><center>Acciones</center></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador = 0;
                                foreach ($usuarios_datos as $usuarios_dato){
                                    $id_usuario = $usuarios_dato['id_usuario'];
                                    $contador++;
                                    
                                    // Determinar badge de estado
                                    $estado = $usuarios_dato['estado_registro'] ?? 'ACTIVO';
                                    $badge_estado = $estado === 'ACTIVO' ? 'success' : 'danger';
                                    ?>
                                    <tr>
                                        <td><center><?php echo $contador; ?></center></td>
                                        <td><?php echo htmlspecialchars($usuarios_dato['nombres'] ?? $usuarios_dato['username']); ?></td>
                                        <td><?php echo htmlspecialchars($usuarios_dato['email']); ?></td>
                                        <td><center>
                                            <span class="badge badge-info">
                                                <?php echo htmlspecialchars($usuarios_dato['rol'] ?? 'Sin rol'); ?>
                                            </span>
                                        </center></td>
                                        <td><center>
                                            <span class="badge badge-<?php echo $badge_estado; ?>">
                                                <?php echo $estado; ?>
                                            </span>
                                        </center></td>
                                        <td>
                                            <center>
                                                <div class="btn-group">
                                                    <a href="show.php?id=<?php echo $id_usuario; ?>" 
                                                       class="btn btn-info btn-sm" 
                                                       title="Ver">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <a href="update.php?id=<?php echo $id_usuario; ?>" 
                                                       class="btn btn-success btn-sm" 
                                                       title="Editar">
                                                        <i class="fa fa-pencil-alt"></i>
                                                    </a>
                                                    <?php if ($id_usuario != $id_usuario_sesion): ?>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            onclick="confirmarEliminar(<?php echo $id_usuario; ?>)"
                                                            title="Eliminar">
                                                        <i class="fa fa-trash"></i>
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


<?php include ('../../contans/layout/mensajes.php'); ?>
<?php include ('../../contans/layout/parte2.php'); ?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 10,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Usuarios",
                "infoEmpty": "Mostrando 0 a 0 de 0 Usuarios",
                "infoFiltered": "(Filtrado de _MAX_ total Usuarios)",
                "lengthMenu": "Mostrar _MENU_ Usuarios",
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

    function confirmarEliminar(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "El usuario será marcado como inactivo",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../../controllers/usuario/eliminar.php',
                    type: 'POST',
                    data: { id_usuario: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error eliminando usuario:', xhr, status, error);
                        let errorMsg = 'No se pudo eliminar el usuario.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            errorMsg = xhr.responseText;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al procesar',
                            html: errorMsg + '<br><small>Status: ' + xhr.status + '</small>'
                        });
                    }
                });
            }
        });
    }
</script>
