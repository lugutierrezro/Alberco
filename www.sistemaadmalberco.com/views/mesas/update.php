<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Obtener ID de la mesa a editar
$id_mesa_get = $_GET['id'] ?? 0;

if ($id_mesa_get <= 0) {
    $_SESSION['error'] = 'ID de mesa inválido';
    header('Location: index.php');
    exit;
}

// Obtener datos de la mesa
try {
    $sql = "SELECT * FROM tb_mesas WHERE id_mesa = ? AND estado_registro = 'ACTIVO'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_mesa_get]);
    $mesa_dato = $stmt->fetch();
    
    if (!$mesa_dato) {
        $_SESSION['error'] = 'Mesa no encontrada';
        header('Location: index.php');
        exit;
    }
    
    $numero_mesa = $mesa_dato['numero_mesa'];
    $capacidad = $mesa_dato['capacidad'];
    $zona = $mesa_dato['zona'] ?? 'PRINCIPAL';
    $estado = $mesa_dato['estado'];
    $descripcion = $mesa_dato['descripcion'] ?? '';
    
} catch (PDOException $e) {
    error_log("Error al obtener mesa: " . $e->getMessage());
    $_SESSION['error'] = 'Error al cargar datos de la mesa';
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
                    <h1 class="m-0">Editar Mesa</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Actualice los datos de la mesa</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="../../controllers/mesas/actualizar.php" 
                                  method="post" 
                                  id="formActualizarMesa">
                                
                                <input type="hidden" name="id_mesa" value="<?php echo $id_mesa_get; ?>">
                                
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
                                                   value="<?php echo $numero_mesa; ?>"
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
                                                   value="<?php echo $capacidad; ?>"
                                                   required
                                                   min="1"
                                                   max="20">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="zona">Zona</label>
                                    <select name="zona" id="zona" class="form-control">
                                        <option value="PRINCIPAL" <?php echo $zona === 'PRINCIPAL' ? 'selected' : ''; ?>>Principal</option>
                                        <option value="TERRAZA" <?php echo $zona === 'TERRAZA' ? 'selected' : ''; ?>>Terraza</option>
                                        <option value="VIP" <?php echo $zona === 'VIP' ? 'selected' : ''; ?>>VIP</option>
                                        <option value="EXTERIOR" <?php echo $zona === 'EXTERIOR' ? 'selected' : ''; ?>>Exterior</option>
                                        <option value="SALON_PRIVADO" <?php echo $zona === 'SALON_PRIVADO' ? 'selected' : ''; ?>>Salón Privado</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="estado">Estado</label>
                                    <select name="estado" id="estado" class="form-control">
                                        <option value="DISPONIBLE" <?php echo $estado === 'DISPONIBLE' ? 'selected' : ''; ?>>Disponible</option>
                                        <option value="OCUPADA" <?php echo $estado === 'OCUPADA' ? 'selected' : ''; ?>>Ocupada</option>
                                        <option value="RESERVADA" <?php echo $estado === 'RESERVADA' ? 'selected' : ''; ?>>Reservada</option>
                                        <option value="MANTENIMIENTO" <?php echo $estado === 'MANTENIMIENTO' ? 'selected' : ''; ?>>Mantenimiento</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <textarea name="descripcion" 
                                              id="descripcion"
                                              class="form-control" 
                                              rows="3"><?php echo htmlspecialchars($descripcion); ?></textarea>
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
    document.getElementById('formActualizarMesa').addEventListener('submit', function(e) {
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
