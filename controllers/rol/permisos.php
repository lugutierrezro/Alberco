<?php
/**
 * Gestionar Permisos de Rol (SIN JSON)
 */

require_once __DIR__ . '/../../services/database/config.php';
require_once MODELS_PATH . 'Rol.php';
require_once MODELS_PATH . 'Usuario.php';

if (!Usuario::isAuthenticated()) {
    die('No autenticado');
}

if ($_SESSION['user_role'] !== 'ADMINISTRADOR') {
    die('No tiene permisos para esta acción');
}

try {
    $rolModel = new Rol();

    // Obtener permisos de un rol
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_rol'])) {
        $rolId = (int)$_GET['id_rol'];

        $permisos = $rolModel->getPermisos($rolId);

        echo "<pre>";
        print_r($permisos);
        echo "</pre>";
        exit;
    }

    // Actualizar permisos de un rol
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rolId = (int)$_POST['id_rol'];
        $permisos = json_decode($_POST['permisos'], true);

        if (!is_array($permisos)) {
            die('Formato de permisos inválido');
        }

        if ($rolModel->updatePermisos($rolId, $permisos)) {
            echo 'Permisos actualizados correctamente';
        } else {
            echo 'Error al actualizar permisos';
        }

        exit;
    }

} catch (Exception $e) {
    error_log("Error al gestionar permisos: " . $e->getMessage());
    die('Error al procesar la solicitud');
}
