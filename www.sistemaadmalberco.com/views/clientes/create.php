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
                    <h1 class="m-0">Registro de Nuevo Cliente</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Complete los datos del cliente</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="../../controllers/clientes/crear.php" 
                                  method="post" 
                                  id="formCrearCliente">
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="text-primary"><i class="fas fa-user"></i> Información Personal</h5>
                                        <hr>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="codigo_cliente">Código Cliente</label>
                                            <input type="text" 
                                                   name="codigo_cliente" 
                                                   id="codigo_cliente"
                                                   class="form-control"
                                                   value="CLI-<?php echo time(); ?>"
                                                   readonly>
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="nombres">
                                                Nombres <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   name="nombres" 
                                                   id="nombres"
                                                   class="form-control"
                                                   placeholder="Nombres del cliente"
                                                   required
                                                   maxlength="100">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="apellidos">Apellidos</label>
                                            <input type="text" 
                                                   name="apellidos" 
                                                   id="apellidos"
                                                   class="form-control"
                                                   placeholder="Apellidos del cliente"
                                                   maxlength="100">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="tipo_documento">Tipo Documento</label>
                                            <select name="tipo_documento" id="tipo_documento" class="form-control">
                                                <option value="DNI">DNI</option>
                                                <option value="RUC">RUC</option>
                                                <option value="CE">Carnet Extranjería</option>
                                                <option value="PASAPORTE">Pasaporte</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="numero_documento">Número Documento</label>
                                            <input type="text" 
                                                   name="numero_documento" 
                                                   id="numero_documento"
                                                   class="form-control"
                                                   placeholder="12345678"
                                                   maxlength="15">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="telefono">
                                                Teléfono <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   name="telefono" 
                                                   id="telefono"
                                                   class="form-control"
                                                   placeholder="999999999"
                                                   required
                                                   maxlength="20">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="fecha_nacimiento">Fecha Nacimiento</label>
                                            <input type="date" 
                                                   name="fecha_nacimiento" 
                                                   id="fecha_nacimiento"
                                                   class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" 
                                                   name="email" 
                                                   id="email"
                                                   class="form-control"
                                                   placeholder="cliente@ejemplo.com">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tipo_cliente">Tipo de Cliente</label>
                                            <select name="tipo_cliente" id="tipo_cliente" class="form-control">
                                                <option value="NUEVO">Nuevo</option>
                                                <option value="FRECUENTE">Frecuente</option>
                                                <option value="VIP">VIP</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="text-primary"><i class="fas fa-map-marker-alt"></i> Dirección</h5>
                                        <hr>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="direccion">Dirección</label>
                                            <input type="text" 
                                                   name="direccion" 
                                                   id="direccion"
                                                   class="form-control"
                                                   placeholder="Av. Principal 123"
                                                   maxlength="200">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="distrito">Distrito</label>
                                            <input type="text" 
                                                   name="distrito" 
                                                   id="distrito"
                                                   class="form-control"
                                                   placeholder="San Isidro"
                                                   maxlength="50">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="referencia_direccion">Referencia</label>
                                            <input type="text" 
                                                   name="referencia_direccion" 
                                                   id="referencia_direccion"
                                                   class="form-control"
                                                   placeholder="Cerca al parque, casa de color blanco"
                                                   maxlength="200">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="ciudad">Ciudad</label>
                                            <input type="text" 
                                                   name="ciudad" 
                                                   id="ciudad"
                                                   class="form-control"
                                                   value="Lima"
                                                   maxlength="50">
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                
                                <div class="form-group">
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar Cliente
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
    document.getElementById('formCrearCliente').addEventListener('submit', function(e) {
        var nombres = document.getElementById('nombres').value.trim();
        var telefono = document.getElementById('telefono').value.trim();
        
        if (nombres === '' || telefono === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Complete los campos obligatorios: Nombres y Teléfono'
            });
            return false;
        }
    });

    // Validar DNI cuando se selecciona tipo documento DNI
    document.getElementById('tipo_documento').addEventListener('change', function() {
        var numeroDoc = document.getElementById('numero_documento');
        if (this.value === 'DNI') {
            numeroDoc.setAttribute('maxlength', '8');
            numeroDoc.setAttribute('pattern', '[0-9]{8}');
        } else if (this.value === 'RUC') {
            numeroDoc.setAttribute('maxlength', '11');
            numeroDoc.setAttribute('pattern', '[0-9]{11}');
        } else {
            numeroDoc.removeAttribute('pattern');
            numeroDoc.setAttribute('maxlength', '15');
        }
    });
</script>
