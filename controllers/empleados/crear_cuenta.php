<?php
// Crear cuenta de usuario para empleado

require_once __DIR__ . '/../../services/database/config.php';
require_once __DIR__ . '/../../models/empleado.php';
include('../../contans/layout/sesion.php');

// SOLO ADMINISTRADORES
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    $_SESSION['error'] = 'Acceso denegado. Solo administradores pueden crear cuentas.';
    header('Location: ' . URL_BASE . '/views/empleados/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/views/empleados/gestionar_cuentas.php');
    exit;
}

try {
    $empleadoId = (int)($_POST['id_empleado'] ?? 0);
    
    if ($empleadoId <= 0) {
        $_SESSION['error'] = 'ID de empleado inválido';
        header('Location: ' . URL_BASE . '/views/empleados/gestionar_cuentas.php');
        exit;
    }
    
    $empleadoModel = new Empleado();
    $resultado = $empleadoModel->crearCuenta($empleadoId);
    
    if ($resultado['success']) {
        $_SESSION['success'] = $resultado['message'];
        $_SESSION['credenciales'] = [
            'username' => $resultado['username'],
            'password' => $resultado['password_texto']
        ];
    } else {
        $_SESSION['error'] = $resultado['message'];
    }
    
    header('Location: ' . URL_BASE . '/views/empleados/gestionar_cuentas.php');
    exit;
    
} catch (Exception $e) {
    error_log("Error al crear cuenta: " . $e->getMessage());
    $_SESSION['error'] = 'Error al procesar la solicitud';
    header('Location: ' . URL_BASE . '/views/empleados/gestionar_cuentas.php');
    exit;
}
