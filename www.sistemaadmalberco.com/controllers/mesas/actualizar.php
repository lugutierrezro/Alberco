<?php
/**
 * Controlador: Actualizar Mesa
 * Procesa el formulario de actualización de mesa
 */

require_once __DIR__ . '/../../services/database/config.php';
require_once __DIR__ . '/../../models/mesas.php';

// Verificar sesión activa
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['mensaje'] = 'Debe iniciar sesión';
    $_SESSION['icono'] = 'error';
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . URL_BASE . '/views/mesas');
    exit;
}

try {
    // Instanciar modelo
    $mesaModel = new Mesa();
    
    // Obtener ID de la mesa
    $id_mesa = isset($_POST['id_mesa']) ? filter_var($_POST['id_mesa'], FILTER_VALIDATE_INT) : false;
    
    if (!$id_mesa) {
        $_SESSION['mensaje'] = 'ID de mesa no válido';
        $_SESSION['icono'] = 'error';
        header('Location: ' . URL_BASE . '/views/mesas');
        exit;
    }
    
    // Verificar que la mesa existe
    $mesaExistente = $mesaModel->getById($id_mesa);
    if (!$mesaExistente) {
        $_SESSION['mensaje'] = 'La mesa no existe';
        $_SESSION['icono'] = 'error';
        header('Location: ' . URL_BASE . '/views/mesas');
        exit;
    }
    
    // Sanitizar y validar datos
    $numero_mesa = isset($_POST['numero_mesa']) ? trim(htmlspecialchars($_POST['numero_mesa'], ENT_QUOTES, 'UTF-8')) : '';
    $zona = isset($_POST['zona']) ? trim(htmlspecialchars($_POST['zona'], ENT_QUOTES, 'UTF-8')) : '';
    $descripcion = isset($_POST['descripcion']) ? trim(htmlspecialchars($_POST['descripcion'], ENT_QUOTES, 'UTF-8')) : '';
    $estado = isset($_POST['estado']) ? trim(htmlspecialchars($_POST['estado'], ENT_QUOTES, 'UTF-8')) : 'disponible';
    $capacidad = isset($_POST['capacidad']) ? filter_var($_POST['capacidad'], FILTER_VALIDATE_INT) : false;
    
    // Validaciones obligatorias
    if (empty($numero_mesa)) {
        $_SESSION['mensaje'] = 'El número de mesa es obligatorio';
        $_SESSION['icono'] = 'error';
        header('Location: ' . URL_BASE . '/views/mesas/edit.php?id=' . $id_mesa);
        exit;
    }
    
    if (empty($zona)) {
        $_SESSION['mensaje'] = 'La zona es obligatoria';
        $_SESSION['icono'] = 'error';
        header('Location: ' . URL_BASE . '/views/mesas/edit.php?id=' . $id_mesa);
        exit;
    }
    
    if ($capacidad === false || $capacidad < 1) {
        $_SESSION['mensaje'] = 'La capacidad debe ser un número mayor a 0';
        $_SESSION['icono'] = 'error';
        header('Location: ' . URL_BASE . '/views/mesas/edit.php?id=' . $id_mesa);
        exit;
    }
    
    // Verificar que el número de mesa no exista (excepto para esta misma mesa)
    if ($mesaModel->numeroExists($numero_mesa, $id_mesa)) {
        $_SESSION['mensaje'] = 'El número de mesa ' . htmlspecialchars($numero_mesa, ENT_QUOTES, 'UTF-8') . ' ya existe';
        $_SESSION['icono'] = 'warning';
        header('Location: ' . URL_BASE . '/views/mesas/edit.php?id=' . $id_mesa);
        exit;
    }
    
    // Validar estado permitido
    $estadosPermitidos = ['disponible', 'ocupada', 'reservada', 'mantenimiento'];
    if (!in_array($estado, $estadosPermitidos)) {
        $estado = 'disponible';
    }
    
    // Preparar datos para actualización
    $datos = [
        'numero_mesa' => $numero_mesa,
        'zona' => strtoupper($zona),
        'capacidad' => $capacidad,
        'descripcion' => $descripcion,
        'estado' => $estado,
        'fyh_actualizacion' => date('Y-m-d H:i:s')
    ];
    
    // Actualizar mesa usando el modelo
    $resultado = $mesaModel->update($id_mesa, $datos);
    
    if ($resultado) {
        $_SESSION['mensaje'] = 'Mesa ' . htmlspecialchars($numero_mesa, ENT_QUOTES, 'UTF-8') . ' actualizada correctamente';
        $_SESSION['icono'] = 'success';
        header('Location: ' . URL_BASE . '/views/mesas');
    } else {
        $_SESSION['mensaje'] = 'Error al actualizar la mesa. Intente nuevamente.';
        $_SESSION['icono'] = 'error';
        header('Location: ' . URL_BASE . '/views/mesas/edit.php?id=' . $id_mesa);
    }
    exit;
    
} catch (PDOException $e) {
    // Log del error
    error_log("Error PDO al actualizar mesa: " . $e->getMessage() . " | Archivo: " . __FILE__ . " | Línea: " . $e->getLine());
    
    $_SESSION['mensaje'] = 'Error en el servidor al procesar la solicitud';
    $_SESSION['icono'] = 'error';
    header('Location: ' . URL_BASE . '/views/mesas/edit.php?id=' . (isset($id_mesa) ? $id_mesa : ''));
    exit;
    
} catch (Exception $e) {
    error_log("Error general al actualizar mesa: " . $e->getMessage() . " | Archivo: " . __FILE__);
    
    $_SESSION['mensaje'] = 'Error inesperado al actualizar la mesa';
    $_SESSION['icono'] = 'error';
    header('Location: ' . URL_BASE . '/views/mesas/edit.php?id=' . (isset($id_mesa) ? $id_mesa : ''));
    exit;
}
