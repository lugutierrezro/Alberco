<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Obtener ID del cliente a editar
$id_cliente_get = $_GET['id'] ?? 0;

if ($id_cliente_get <= 0) {
    $_SESSION['error'] = 'ID de cliente inválido';
    header('Location: index.php');
    exit;
}

// Obtener datos del cliente
try {
    $sql = "SELECT * FROM tb_clientes WHERE id_cliente = ? AND estado_registro = 'ACTIVO'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_cliente_get]);
    $cliente_dato = $stmt->fetch();
    
    if (!$cliente_dato) {
        $_SESSION['error'] = 'Cliente no encontrado';
        header('Location: index.php');
        exit;
    }
    
    // Asignar variables
    $codigo_cliente = $cliente_dato['codigo_cliente'];
    $nombre = $cliente_dato['nombre'];
    $apellidos = $cliente_dato['apellidos'] ?? '';
    $tipo_documento = $cliente_dato['tipo_documento'] ?? 'DNI';
    $numero_documento = $cliente_dato['numero_documento'] ?? '';
    $telefono = $cliente_dato['telefono'];
    $email = $cliente_dato['email'] ?? '';
    $direccion = $cliente_dato['direccion'] ?? '';
    $referencia_direccion = $cliente_dato['referencia_direccion'] ?? '';
    $distrito = $cliente_dato['distrito'] ?? '';
    $ciudad = $cliente_dato['ciudad'] ?? 'Lima';
    $fecha_nacimiento = $cliente_dato['fecha_nacimiento'] ?? '';
    $tipo_cliente = $cliente_dato['tipo_cliente'] ?? 'NUEVO';
    
} catch (PDOException $e) {
    error_log("Error al obtener cliente: " . $e->getMessage());
    $_SESSION['error'] = 'Error al cargar datos del cliente';
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
                    <h1 class="m-0">Editar Cliente</h1>
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
                            <h3 class="card-title">Actualice los datos del cliente</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="../../controllers/clientes/actualizar.php" 
                                  method="post" 
                                  id="formActualizarCliente">
                                
                                <input type="hidden" name="id_cliente" value="<?php echo $id_cliente_get; ?>">
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="text-success"><i class="fas fa-user"></i> Información Personal</h5>
                                        <hr>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="codigo_cliente">Código Cliente</label>
                                            <input type="text" 
                                                   id="codigo_cliente"
                                                   class="form-control"
                                                   value="<?php echo htmlspecialchars($codigo_cliente); ?>"
                                                   disabled>
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="nombre">
                                                Nombres <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   name="nombre" 
                                                   id="nombre"
                                                   class="form-control"
                                                   value="<?php echo htmlspecialchars($nombre); ?>"
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
                                                   value="<?php echo htmlspecialchars($apellidos); ?>"
                                                   maxlength="100">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="telefono">
                                                Teléfono <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   name="telefono" 
                                                   id="telefono"
                                                   class="form-control"
                                                   value="<?php echo htmlspecialchars($telefono); ?>"
                                                   required
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
                                                   value="<?php echo htmlspecialchars($email); ?>">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="tipo_cliente">Tipo de Cliente</label>
                                            <select name="tipo_cliente" id="tipo_cliente" class="form-control">
                                                <option value="NUEVO" <?php echo $tipo_cliente === 'NUEVO' ? 'selected' : ''; ?>>Nuevo</option>
                                                <option value="FRECUENTE" <?php echo $tipo_cliente === 'FRECUENTE' ? 'selected' : ''; ?>>Frecuente</option>
                                                <option value="VIP" <?php echo $tipo_cliente === 'VIP' ? 'selected' : ''; ?>>VIP</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 class="text-success"><i class="fas fa-map-marker-alt"></i> Dirección</h5>
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
                                                   value="<?php echo htmlspecialchars($direccion); ?>"
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
                                                   value="<?php echo htmlspecialchars($distrito); ?>"
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
                                                   value="<?php echo htmlspecialchars($referencia_direccion); ?>"
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
                                                   value="<?php echo htmlspecialchars($ciudad); ?>"
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
                                        <i class="fas fa-sync-alt"></i> Actualizar
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
    document.getElementById('formActualizarCliente').addEventListener('submit', function(e) {
        var nombre = document.getElementById('nombre').value.trim();
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
</script>
