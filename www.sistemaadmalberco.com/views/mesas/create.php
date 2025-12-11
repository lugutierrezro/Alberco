<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Registrar Nueva Mesa</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Complete los datos de la mesa</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="../../controllers/mesas/crear.php" 
                                  method="post" 
                                  id="formCrearMesa">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="numero_mesa">
                                                Número de Mesa <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" 
                                                   name="numero_mesa" 
                                                   id="numero_mesa"
                                                   class="form-control"
                                                   placeholder="1"
                                                   required
                                                   min="1">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="capacidad">
                                                Capacidad (personas) <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" 
                                                   name="capacidad" 
                                                   id="capacidad"
                                                   class="form-control"
                                                   placeholder="4"
                                                   required
                                                   min="1"
                                                   max="20">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="zona">Zona</label>
                                    <select name="zona" id="zona" class="form-control">
                                        <option value="PRINCIPAL">Principal</option>
                                        <option value="TERRAZA">Terraza</option>
                                        <option value="VIP">VIP</option>
                                        <option value="EXTERIOR">Exterior</option>
                                        <option value="SALON_PRIVADO">Salón Privado</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="estado">Estado Inicial</label>
                                    <select name="estado" id="estado" class="form-control">
                                        <option value="DISPONIBLE" selected>Disponible</option>
                                        <option value="MANTENIMIENTO">Mantenimiento</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <textarea name="descripcion" 
                                              id="descripcion"
                                              class="form-control" 
                                              rows="3"
                                              placeholder="Características especiales de la mesa..."></textarea>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Nota:</strong> El número de mesa debe ser único.
                                </div>

                                <hr>
                                
                                <div class="form-group">
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar Mesa
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
    document.getElementById('formCrearMesa').addEventListener('submit', function(e) {
        var numeroMesa = document.getElementById('numero_mesa').value;
        var capacidad = document.getElementById('capacidad').value;
        
        if (numeroMesa <= 0 || capacidad <= 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El número de mesa y la capacidad deben ser mayores a 0'
            });
            return false;
        }
    });
</script>
