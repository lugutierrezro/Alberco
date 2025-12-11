<?php
// Asegurar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../../services/database/config.php');
include('../../contans/layout/sesion.php');
include('../../contans/layout/parte1.php');

// Validar conexión PDO
if (!isset($pdo) || !$pdo) {
    die("Error: No se encontró la conexión a la base de datos.");
}

// Obtener ID del proveedor desde GET
$id_proveedor_get = 0;
if (isset($_GET['id']) && $_GET['id'] !== '') {
    $id_proveedor_get = (int)$_GET['id'];
}

try {
    // Consulta segura: ignora espacios y mayúsculas/minúsculas en estado_registro
    $sql = "SELECT * FROM tb_proveedores 
            WHERE id_proveedor = :id 
              AND UPPER(TRIM(estado_registro)) = 'ACTIVO'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id_proveedor_get]);
    $proveedor_dato = $stmt->fetch(PDO::FETCH_ASSOC);

    // Depuración opcional
    /*
    echo '<pre>';
    echo "ID buscado: "; var_dump($id_proveedor_get);
    echo "Resultado consulta: "; var_dump($proveedor_dato);
    echo '</pre>';
    exit;
    */

    // Validar si se obtuvo algún registro
    if (empty($proveedor_dato) || !is_array($proveedor_dato)) {
        $_SESSION['error'] = 'Proveedor no encontrado131';
        header('Location: index.php');
        exit;
    }

} catch (PDOException $e) {
    error_log("Error al obtener proveedor: " . $e->getMessage());
    $_SESSION['error'] = 'Error al cargar datos del proveedor';
    header('Location: index.php');
    exit;
}
?>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Editar Proveedor</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Actualice los datos del proveedor</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="../../controllers/proveedores/actualizar.php"
                                method="post"
                                id="formActualizarProveedor">

                                <input type="hidden" name="id_proveedor" value="<?php echo $id_proveedor_get; ?>">


                                <!-- Información Básica -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="text-success">
                                            <i class="fas fa-building"></i> Información Básica
                                        </h5>
                                        <hr>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="codigo_proveedor">Código</label>
                                            <input type="text"
                                                id="codigo_proveedor"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($proveedor_dato['codigo_proveedor'], ENT_QUOTES, 'UTF-8'); ?>"
                                                disabled>
                                            <small class="form-text text-muted">No modificable</small>
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
                                                value="<?php echo htmlspecialchars($proveedor_dato['nombre_proveedor'], ENT_QUOTES, 'UTF-8'); ?>"
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
                                                value="<?php echo htmlspecialchars($proveedor_dato['empresa'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                required
                                                maxlength="200">
                                        </div>
                                    </div>
                                </div>

                                <!-- Datos de Contacto -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="text-success">
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
                                                value="<?php echo htmlspecialchars($proveedor_dato['celular'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
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
                                                value="<?php echo htmlspecialchars($proveedor_dato['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
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
                                                value="<?php echo htmlspecialchars($proveedor_dato['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                maxlength="150">
                                        </div>
                                    </div>
                                </div>

                                <!-- Información Legal y Dirección -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="text-success">
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
                                                value="<?php echo htmlspecialchars($proveedor_dato['ruc'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
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
                                                value="<?php echo htmlspecialchars($proveedor_dato['direccion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                required
                                                maxlength="300">
                                        </div>
                                    </div>
                                </div>

                                <!-- Información Adicional -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="text-success">
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
                                                value="<?php echo htmlspecialchars($proveedor_dato['contacto_nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                maxlength="200">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="banco">Banco</label>
                                            <select name="banco" id="banco" class="form-control">
                                                <option value="">-- Seleccione --</option>
                                                <option value="BCP" <?php echo ($proveedor_dato['banco'] ?? '') === 'BCP' ? 'selected' : ''; ?>>BCP - Banco de Crédito del Perú</option>
                                                <option value="BBVA" <?php echo ($proveedor_dato['banco'] ?? '') === 'BBVA' ? 'selected' : ''; ?>>BBVA Continental</option>
                                                <option value="INTERBANK" <?php echo ($proveedor_dato['banco'] ?? '') === 'INTERBANK' ? 'selected' : ''; ?>>Interbank</option>
                                                <option value="SCOTIABANK" <?php echo ($proveedor_dato['banco'] ?? '') === 'SCOTIABANK' ? 'selected' : ''; ?>>Scotiabank</option>
                                                <option value="BANBIF" <?php echo ($proveedor_dato['banco'] ?? '') === 'BANBIF' ? 'selected' : ''; ?>>Banbif</option>
                                                <option value="BN" <?php echo ($proveedor_dato['banco'] ?? '') === 'BN' ? 'selected' : ''; ?>>Banco de la Nación</option>
                                                <option value="PICHINCHA" <?php echo ($proveedor_dato['banco'] ?? '') === 'PICHINCHA' ? 'selected' : ''; ?>>Banco Pichincha</option>
                                                <option value="OTRO" <?php echo ($proveedor_dato['banco'] ?? '') === 'OTRO' ? 'selected' : ''; ?>>Otro</option>
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
                                                value="<?php echo htmlspecialchars($proveedor_dato['numero_cuenta'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                maxlength="50">
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="form-group">
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-sync-alt"></i> Actualizar Proveedor
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

<?php include('../../contans/layout/mensajes.php'); ?>
<?php include('../../contans/layout/parte2.php'); ?>

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
    document.getElementById('formActualizarProveedor').addEventListener('submit', function(e) {
        var nombre = document.getElementById('nombre_proveedor').value.trim();
        var celular = document.getElementById('celular').value.trim();
        var empresa = document.getElementById('empresa').value.trim();
        var direccion = document.getElementById('direccion').value.trim();

        // Validar campos obligatorios
        if (nombre === '' || celular === '' || empresa === '' || direccion === '') {
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

        // Confirmación antes de actualizar
        return confirm('¿Está seguro de actualizar los datos del proveedor "' + nombre + '"?');
    });
</script>