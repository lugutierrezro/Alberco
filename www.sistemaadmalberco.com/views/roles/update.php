<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');

// Obtener ID del rol a editar
$id_rol_get = $_GET['id'] ?? 0;

if ($id_rol_get <= 0) {
    $_SESSION['error'] = 'ID de rol inválido';
    header('Location: index.php');
    exit;
}

// Obtener datos del rol
try {
    $sql = "SELECT * FROM tb_roles WHERE id_rol = ? AND estado_registro = 'ACTIVO'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_rol_get]);
    $rol_dato = $stmt->fetch();
    
    if (!$rol_dato) {
        $_SESSION['error'] = 'Rol no encontrado';
        header('Location: index.php');
        exit;
    }
    
    $rol = $rol_dato['rol'];
    $descripcion = $rol_dato['descripcion'] ?? '';
    
} catch (PDOException $e) {
    error_log("Error al obtener rol: " . $e->getMessage());
    $_SESSION['error'] = 'Error al cargar datos del rol';
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
                    <h1 class="m-0">Edición del Rol</h1>
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
                            <h3 class="card-title">Actualice los datos con cuidado</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="../../controllers/rol/actualizar.php" method="post">
                                <input type="hidden" name="id_rol" value="<?php echo $id_rol_get; ?>">
                                
                                <div class="form-group">
                                    <label for="rol">Nombre del Rol <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="rol" 
                                           id="rol"
                                           class="form-control"
                                           value="<?php echo htmlspecialchars($rol); ?>"
                                           required
                                           maxlength="50">
                                </div>

                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <textarea name="descripcion" 
                                              id="descripcion"
                                              class="form-control" 
                                              rows="3"><?php echo htmlspecialchars($descripcion); ?></textarea>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Nota:</strong> No se pueden modificar los roles del sistema 
                                    (ADMINISTRADOR, CAJERO, DELIVERY, COCINERO)
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
