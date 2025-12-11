<?php
// Actualizar Cliente (sin JSON)

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
    $clienteId = (int)($_POST['id_cliente'] ?? 0);

    $data = [
        'nombre' => sanitize($_POST['nombre'] ?? ''),
        'apellidos' => sanitize($_POST['apellidos'] ?? ''),
        'telefono' => sanitize($_POST['telefono'] ?? ''),
        'email' => !empty($_POST['email']) ? sanitize($_POST['email']) : null,
        'direccion' => sanitize($_POST['direccion'] ?? ''),
        'referencia_direccion' => sanitize($_POST['referencia_direccion'] ?? ''),
        'distrito' => sanitize($_POST['distrito'] ?? ''),
        'ciudad' => sanitize($_POST['ciudad'] ?? 'Lima'),
        'tipo_cliente' => sanitize($_POST['tipo_cliente'] ?? 'NUEVO')
    ];

    // ✅ Validaciones
    if (empty($data['nombre']) || empty($data['telefono'])) {
        $_SESSION['error'] = 'Complete los campos obligatorios: nombre y teléfono';
        header('Location: ' . URL_BASE . '/views/clientes/update.php?id=' . $clienteId);
        exit;
    }

    if (!empty($data['email']) && !validarEmail($data['email'])) {
        $_SESSION['error'] = 'Email inválido';
        header('Location: ' . URL_BASE . '/views/clientes/update.php?id=' . $clienteId);
        exit;
    }

    // ✅ Actualizar
    $updateSql = "UPDATE tb_clientes SET
                  nombre = :nombre,
                  apellidos = :apellidos,
                  telefono = :telefono,
                  email = :email,
                  direccion = :direccion,
                  referencia_direccion = :referencia,
                  distrito = :distrito,
                  ciudad = :ciudad,
                  tipo_cliente = :tipo_cliente,
                  fyh_actualizacion = NOW()
                  WHERE id_cliente = :id";

    $stmt = $pdo->prepare($updateSql);
    $result = $stmt->execute([
        ':nombre' => $data['nombre'],
        ':apellidos' => $data['apellidos'],
        ':telefono' => $data['telefono'],
        ':email' => $data['email'],
        ':direccion' => $data['direccion'],
        ':referencia' => $data['referencia_direccion'],
        ':distrito' => $data['distrito'],
        ':ciudad' => $data['ciudad'],
        ':tipo_cliente' => $data['tipo_cliente'],
        ':id' => $clienteId
    ]);

    if ($result) {
        $_SESSION['success'] = 'Cliente actualizado correctamente';
    } else {
        $_SESSION['error'] = 'Error al actualizar el cliente';
    }

    header('Location: ' . URL_BASE . '/views/clientes/');
    exit;

} catch (PDOException $e) {
    error_log("Error al actualizar cliente: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/views/clientes/');
    exit;
}
