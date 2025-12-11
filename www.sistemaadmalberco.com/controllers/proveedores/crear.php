<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../services/database/config.php';
include('../../contans/layout/sesion.php');

// Validar que la conexión PDO exista
if (!isset($pdo) || !$pdo) {
    die("Error: No se encontró la conexión a la base de datos.");
}

// Solo permitir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Método no permitido';
    header('Location: ' . URL_BASE . '/views/proveedores/');
    exit;
}

// Recibir datos del formulario
$codigo          = trim($_POST['codigo_proveedor'] ?? '');
$nombre          = trim($_POST['nombre_proveedor'] ?? '');
$celular         = trim($_POST['celular'] ?? '');
$telefono        = trim($_POST['telefono'] ?? null);
$empresa         = trim($_POST['empresa'] ?? '');
$ruc             = trim($_POST['ruc'] ?? null);
$email           = trim($_POST['email'] ?? null);
$direccion       = trim($_POST['direccion'] ?? '');
$contacto_nombre = trim($_POST['contacto_nombre'] ?? null);
$banco           = trim($_POST['banco'] ?? null);
$numero_cuenta   = trim($_POST['numero_cuenta'] ?? null);

// Validar campos obligatorios
if ($codigo === '' || $nombre === '' || $celular === '' || $empresa === '' || $direccion === '') {
    $_SESSION['error'] = 'Complete los campos obligatorios';
    header('Location: ' . URL_BASE . '/views/proveedores/create.php');
    exit;
}

try {
    // Preparar INSERT
    $sql = "INSERT INTO tb_proveedores (
                codigo_proveedor,
                nombre_proveedor,
                celular,
                telefono,
                empresa,
                ruc,
                email,
                direccion,
                contacto_nombre,
                banco,
                numero_cuenta,
                estado_registro,
                fyh_creacion,
                fyh_actualizacion
            ) VALUES (
                :codigo, :nombre, :celular, :telefono, :empresa, :ruc, :email,
                :direccion, :contacto_nombre, :banco, :numero_cuenta,
                'ACTIVO', NOW(), NOW()
            )";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':codigo'          => $codigo,
        ':nombre'          => $nombre,
        ':celular'         => $celular,
        ':telefono'        => $telefono ?: null,
        ':empresa'         => $empresa,
        ':ruc'             => $ruc ?: null,
        ':email'           => $email ?: null,
        ':direccion'       => $direccion,
        ':contacto_nombre' => $contacto_nombre ?: null,
        ':banco'           => $banco ?: null,
        ':numero_cuenta'   => $numero_cuenta ?: null
    ]);

    $_SESSION['success'] = 'Proveedor registrado correctamente';
    header('Location: ' . URL_BASE . '/views/proveedores/');
    exit;

} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Clave duplicada
        $_SESSION['error'] = 'El código del proveedor ya existe';
    } else {
        $_SESSION['error'] = 'Error al procesar la solicitud';
        error_log("ERROR CREATE PROVEEDOR: " . $e->getMessage());
    }

    header('Location: ' . URL_BASE . '/views/proveedores/create.php');
    exit;
}
?>
