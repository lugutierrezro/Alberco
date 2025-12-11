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
                    <h1 class="m-0">Registrar Nuevo Proveedor</h1>
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
                            <h3 class="card-title">Complete los datos del proveedor</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="../../controllers/proveedores/crear.php" 
                                  method="post" 
                                  id="formCrearProveedor">
                                
                                <!-- Información Básica -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="text-primary">
                                            <i class="fas fa-building"></i> Información Básica
                                        </h5>
                                        <hr>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="codigo_proveedor">
                                                Código <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   name="codigo_proveedor" 
                                                   id="codigo_proveedor"
                                                   class="form-control"
                                                   value="PROV-<?php echo time(); ?>"
                                                   readonly
                                                   required>
                                            <small class="form-text text-muted">Generado automáticamente</small>
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="nombre_proveedor">
                                                Nombre del Proveedor <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   name="nombre_proveedor" 
                                                   id="nombre_proveedor"
                                                   class="form-control"
                                                   placeholder="Nombre del contacto principal"
                                                   required
                                                   maxlength="200">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="empresa">
                                                Empresa <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   name="empresa" 
                                                   id="empresa"
                                                   class="form-control"
                                                   placeholder="Razón social de la empresa"
                                                   required
                                                   maxlength="200">
                                        </div>
                                    </div>
                                </div>

                                <!-- Contacto -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="text-primary">
                                            <i class="fas fa-phone"></i> Datos de Contacto
                                        </h5>
                                        <hr>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="celular">
                                                Celular <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   name="celular" 
                                                   id="celular"
                                                   class="form-control"
                                                   placeholder="999 999 999"
                                                   required
                                                   maxlength="20">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="telefono">Teléfono</label>
                                            <input type="text" 
                                                   name="telefono" 
                                                   id="telefono"
                                                   class="form-control"
                                                   placeholder="(01) 123-4567"
                                                   maxlength="20">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" 
                                                   name="email" 
                                                   id="email"
                                                   class="form-control"
                                                   placeholder="proveedor@ejemplo.com"
                                                   maxlength="150">
                                        </div>
                                    </div>
                                </div>

                                <!-- Información Legal y Dirección -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="text-primary">
                                            <i class="fas fa-file-invoice"></i> Información Legal y Ubicación
                                        </h5>
                                        <hr>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="ruc">RUC</label>
                                            <input type="text" 
                                                   name="ruc" 
                                                   id="ruc"
                                                   class="form-control"
                                                   placeholder="20123456789"
                                                   maxlength="11"
                                                   pattern="[0-9]{11}">
                                            <small class="form-text text-muted">11 dígitos</small>
                                        </div>
                                    </div>

                                    <div class="col-md-9">
                                        <div class="form-group">
                                            <label for="direccion">
                                                Dirección <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   name="direccion" 
                                                   id="direccion"
                                                   class="form-control"
                                                   placeholder="Av. Principal 123, Distrito, Ciudad"
                                                   required
                                                   maxlength="300">
                                        </div>
                                    </div>
                                </div>

                                <!-- Información Adicional -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="text-primary">
                                            <i class="fas fa-info-circle"></i> Información Adicional
                                        </h5>
                                        <hr>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="contacto_nombre">Nombre del Contacto</label>
                                            <input type="text" 
                                                   name="contacto_nombre" 
                                                   id="contacto_nombre"
                                                   class="form-control"
                                                   placeholder="Nombre del representante"
                                                   maxlength="200">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="banco">Banco</label>
                                            <select name="banco" id="banco" class="form-control">
                                                <option value="">-- Seleccione --</option>
                                                <option value="BCP">BCP - Banco de Crédito del Perú</option>
                                                <option value="BBVA">BBVA Continental</option>
                                                <option value="INTERBANK">Interbank</option>
                                                <option value="SCOTIABANK">Scotiabank</option>
                                                <option value="BANBIF">Banbif</option>
                                                <option value="BN">Banco de la Nación</option>
                                                <option value="PICHINCHA">Banco Pichincha</option>
                                                <option value="OTRO">Otro</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="numero_cuenta">Número de Cuenta</label>
                                            <input type="text" 
                                                   name="numero_cuenta" 
                                                   id="numero_cuenta"
                                                   class="form-control"
                                                   placeholder="00-000-000000000000"
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
                                        <i class="fas fa-save"></i> Guardar Proveedor
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
// Validación del RUC (solo números)
document.getElementById('ruc').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// Validación del celular (solo números y espacios)
document.getElementById('celular').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9\s]/g, '');
});

// Validación del teléfono
document.getElementById('telefono').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9\s\-\(\)]/g, '');
});

// Validación del formulario antes de enviar
document.getElementById('formCrearProveedor').addEventListener('submit', function(e) {
    var codigo = document.getElementById('codigo_proveedor').value.trim();
    var nombre = document.getElementById('nombre_proveedor').value.trim();
    var celular = document.getElementById('celular').value.trim();
    var empresa = document.getElementById('empresa').value.trim();
    var direccion = document.getElementById('direccion').value.trim();
    
    // Validar campos obligatorios
    if (codigo === '' || nombre === '' || celular === '' || empresa === '' || direccion === '') {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Error de Validación',
            text: 'Complete todos los campos obligatorios marcados con (*)',
            confirmButtonText: 'Entendido'
        });
        return false;
    }
    
    // Validar longitud del celular
    if (celular.length < 9) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Celular Inválido',
            text: 'El celular debe tener al menos 9 dígitos',
            confirmButtonText: 'Entendido'
        });
        return false;
    }
    
    // Validar RUC si está completado
    var ruc = document.getElementById('ruc').value.trim();
    if (ruc !== '' && ruc.length !== 11) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'RUC Inválido',
            text: 'El RUC debe tener exactamente 11 dígitos',
            confirmButtonText: 'Entendido'
        });
        return false;
    }
    
    // Validar email si está completado
    var email = document.getElementById('email').value.trim();
    if (email !== '') {
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Email Inválido',
                text: 'Ingrese un email válido (ejemplo@dominio.com)',
                confirmButtonText: 'Entendido'
            });
            return false;
        }
    }
});

// Confirmación antes de enviar
document.getElementById('formCrearProveedor').addEventListener('submit', function(e) {
    var nombre = document.getElementById('nombre_proveedor').value.trim();
    var empresa = document.getElementById('empresa').value.trim();
    
    if (!confirm('¿Está seguro de registrar al proveedor "' + nombre + '" de la empresa "' + empresa + '"?')) {
        e.preventDefault();
        return false;
    }
});
</script>
