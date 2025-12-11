<?php
// Crear Cliente (sin JSON)

require_once __DIR__ . '/../../services/database/config.php';
include('../../contans/layout/sesion.php');

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'Debe iniciar sesión';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/views/clientes/');
    exit;
}

try {
    // Datos sanitizados
    $data = [
        'codigo_cliente' => !empty($_POST['codigo_cliente']) ? sanitize($_POST['codigo_cliente']) : 'CLI-' . time(),
        'nombre' => sanitize($_POST['nombres'] ?? ''), // ✅ CAMBIO: ahora se llama nombre
        'apellidos' => sanitize($_POST['apellidos'] ?? ''),
        'telefono' => sanitize($_POST['telefono'] ?? ''),
        'email' => !empty($_POST['email']) ? sanitize($_POST['email']) : null,
        'direccion' => sanitize($_POST['direccion'] ?? ''),
        'referencia_direccion' => sanitize($_POST['referencia_direccion'] ?? ''),
        'distrito' => sanitize($_POST['distrito'] ?? ''),
        'ciudad' => sanitize($_POST['ciudad'] ?? 'Lima'),
        'tipo_documento' => sanitize($_POST['tipo_documento'] ?? 'DNI'),
        'numero_documento' => !empty($_POST['numero_documento']) ? sanitize($_POST['numero_documento']) : null,
        'fecha_nacimiento' => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
        'tipo_cliente' => sanitize($_POST['tipo_cliente'] ?? 'NUEVO')
    ];

    // ✅ Validaciones
    if (empty($data['nombre']) || empty($data['telefono'])) {
        $_SESSION['error'] = 'Complete los campos obligatorios: nombre y teléfono';
        header('Location: ' . URL_BASE . '/views/clientes/create.php');
        exit;
    }

    if (!empty($data['email']) && !validarEmail($data['email'])) {
        $_SESSION['error'] = 'Email inválido';
        header('Location: ' . URL_BASE . '/views/clientes/create.php');
        exit;
    }

    // ✅ Verificar código duplicado
    $checkCodigoSql = "SELECT id_cliente FROM tb_clientes WHERE codigo_cliente = ?";
    $checkCodigoStmt = $pdo->prepare($checkCodigoSql);
    $checkCodigoStmt->execute([$data['codigo_cliente']]);

    if ($checkCodigoStmt->fetch()) {
        $_SESSION['error'] = 'El código de cliente ya existe';
        header('Location: ' . URL_BASE . '/views/clientes/create.php');
        exit;
    }

    // ✅ INSERT CORRECTO SEGÚN LA TABLA
    $insertSql = "INSERT INTO tb_clientes 
                  (codigo_cliente, nombre, apellidos, telefono, email, direccion,
                   referencia_direccion, distrito, ciudad, tipo_documento,
                   numero_documento, fecha_nacimiento, tipo_cliente, fyh_creacion)
                  VALUES 
                  (:codigo, :nombre, :apellidos, :telefono, :email, :direccion,
                   :referencia, :distrito, :ciudad, :tipo_doc,
                   :num_doc, :fecha_nac, :tipo_cliente, NOW())";

    $stmt = $pdo->prepare($insertSql);
    $result = $stmt->execute([
        ':codigo' => $data['codigo_cliente'],
        ':nombre' => $data['nombre'], // ✅ corregido
        ':apellidos' => $data['apellidos'],
        ':telefono' => $data['telefono'],
        ':email' => $data['email'],
        ':direccion' => $data['direccion'],
        ':referencia' => $data['referencia_direccion'],
        ':distrito' => $data['distrito'],
        ':ciudad' => $data['ciudad'],
        ':tipo_doc' => $data['tipo_documento'],
        ':num_doc' => $data['numero_documento'],
        ':fecha_nac' => $data['fecha_nacimiento'],
        ':tipo_cliente' => $data['tipo_cliente']
    ]);

    if ($result) {
        $_SESSION['success'] = 'Cliente creado correctamente - ' . $data['codigo_cliente'];
        header('Location: ' . URL_BASE . '/views/clientes/');
    } else {
        $_SESSION['error'] = 'Error al crear el cliente';
        header('Location: ' . URL_BASE . '/views/clientes/create.php');
    }
    exit;

} catch (PDOException $e) {
    error_log("Error al crear cliente: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/views/clientes/create.php');
    exit;
}
