<?php
// Asegurar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../../services/database/config.php');
include('../../contans/layout/sesion.php');

// Validar conexión PDO
if (!isset($pdo) || !$pdo) {
    die("Error: No se encontró la conexión a la base de datos.");
}

// Recibir ID del proveedor desde POST
$id_proveedor = isset($_POST['id_proveedor']) ? (int)$_POST['id_proveedor'] : 0;

if ($id_proveedor <= 0) {
    $_SESSION['error'] = 'ID de proveedor inválido';
    header('Location: ../../views/proveedores/index.php');
    exit;
}

// Recibir datos del formulario
$nombre_proveedor = trim($_POST['nombre_proveedor'] ?? '');
$empresa         = trim($_POST['empresa'] ?? '');
$celular         = trim($_POST['celular'] ?? '');
$telefono        = trim($_POST['telefono'] ?? '');
$email           = trim($_POST['email'] ?? '');
$ruc             = trim($_POST['ruc'] ?? '');
$direccion       = trim($_POST['direccion'] ?? '');
$contacto_nombre = trim($_POST['contacto_nombre'] ?? '');
$banco           = trim($_POST['banco'] ?? '');
$numero_cuenta   = trim($_POST['numero_cuenta'] ?? '');

// Validar campos obligatorios
if ($nombre_proveedor === '' || $empresa === '' || $celular === '' || $direccion === '') {
    $_SESSION['error'] = 'Complete todos los campos obligatorios';
    header('Location: ../../views/proveedores/edit.php?id=' . $id_proveedor);
    exit;
}

try {
    // Validar que el proveedor exista y esté activo
    $sqlCheck = "SELECT * FROM tb_proveedores 
                 WHERE id_proveedor = :id 
                   AND UPPER(TRIM(estado_registro)) = 'ACTIVO'";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([':id' => $id_proveedor]);
    $proveedor = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$proveedor) {
        $_SESSION['error'] = 'Proveedor no encontrado o inactivo';
        header('Location: ../../views/proveedores/index.php');
        exit;
    }

    // Preparar UPDATE
    $sqlUpdate = "UPDATE tb_proveedores SET
                    nombre_proveedor = :nombre_proveedor,
                    empresa         = :empresa,
                    celular         = :celular,
                    telefono        = :telefono,
                    email           = :email,
                    ruc             = :ruc,
                    direccion       = :direccion,
                    contacto_nombre = :contacto_nombre,
                    banco           = :banco,
                    numero_cuenta   = :numero_cuenta,
                    fyh_actualizacion = NOW()
                  WHERE id_proveedor = :id";

    $stmtUpdate = $pdo->prepare($sqlUpdate);

    $stmtUpdate->execute([
        ':nombre_proveedor' => $nombre_proveedor,
        ':empresa'          => $empresa,
        ':celular'          => $celular,
        ':telefono'         => $telefono,
        ':email'            => $email,
        ':ruc'              => $ruc,
        ':direccion'        => $direccion,
        ':contacto_nombre'  => $contacto_nombre,
        ':banco'            => $banco,
        ':numero_cuenta'    => $numero_cuenta,
        ':id'               => $id_proveedor
    ]);

    $_SESSION['success'] = 'Proveedor actualizado correctamente';
    header('Location: ../../views/proveedores/index.php');
    exit;

} catch (PDOException $e) {
    error_log("Error al actualizar proveedor: " . $e->getMessage());
    $_SESSION['error'] = 'Error al actualizar proveedor';
    header('Location: ../../views/proveedores/edit.php?id=' . $id_proveedor);
    exit;
}
?>
