<?php
/**
 * Controlador de Eventos
 */

require_once __DIR__ . '/../../models/evento.php';

// La sesión ya fue verificada por sesion.php

$model = new Evento();
$mensaje = '';
$tipo_mensaje = '';
$accion = $_GET['accion'] ?? 'listar';
$idEvento = $_GET['id'] ?? null;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accionPost = $_POST['accion'] ?? '';
    
    if ($accionPost === 'crear') {
        $datos = [
            'nombre_evento' => $_POST['nombre_evento'],
            'descripcion' => $_POST['descripcion'],
            'fecha_evento' => $_POST['fecha_evento'],
            'mensaje_antes' => $_POST['mensaje_antes'],
            'mensaje_durante' => $_POST['mensaje_durante'],
            'mensaje_despues' => $_POST['mensaje_despues'],
            'mostrar_contador' => isset($_POST['mostrar_contador']) ? 1 : 0,
            'activo' => isset($_POST['activo']) ? 1 : 0,
            'estilo_json' => $_POST['estilo_json'] ?: null
        ];
        
        $resultado = $model->crearEvento($datos);
        if ($resultado) {
            $mensaje = 'Evento creado exitosamente';
            $tipo_mensaje = 'success';
            $accion = 'listar';
        } else {
            $mensaje = 'Error al crear el evento';
            $tipo_mensaje = 'error';
        }
    }
    
    elseif ($accionPost === 'editar') {
        $id = $_POST['id_evento'];
        $datos = [
            'nombre_evento' => $_POST['nombre_evento'],
            'descripcion' => $_POST['descripcion'],
            'fecha_evento' => $_POST['fecha_evento'],
            'mensaje_antes' => $_POST['mensaje_antes'],
            'mensaje_durante' => $_POST['mensaje_durante'],
            'mensaje_despues' => $_POST['mensaje_despues'],
            'mostrar_contador' => isset($_POST['mostrar_contador']) ? 1 : 0,
            'activo' => isset($_POST['activo']) ? 1 : 0,
            'estilo_json' => $_POST['estilo_json'] ?: null
        ];
        
        $resultado = $model->update($id, $datos);
        if ($resultado) {
            $mensaje = 'Evento actualizado exitosamente';
            $tipo_mensaje = 'success';
            $accion = 'listar';
        } else {
            $mensaje = 'Error al actualizar el evento';
            $tipo_mensaje = 'error';
        }
    }
    
    elseif ($accionPost === 'eliminar') {
        $id = $_POST['id_evento'];
        $resultado = $model->delete($id);
        if ($resultado) {
            $mensaje = 'Evento eliminado exitosamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al eliminar el evento';
            $tipo_mensaje = 'error';
        }
        $accion = 'listar';
    }
}

// Obtener datos
$eventos = [];
$evento = null;
$estadisticas = $model->getEstadisticas();

if ($accion === 'listar') {
    $eventos = $model->getAll();
} elseif ($accion === 'editar' && $idEvento) {
    $evento = $model->getById($idEvento);
    if (!$evento) {
        $mensaje = 'Evento no encontrado';
        $tipo_mensaje = 'error';
        $accion = 'listar';
    }
}
