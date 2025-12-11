<?php
/**
 * Controlador de Anuncios
 */

require_once __DIR__ . '/../../models/anuncio.php';

// La sesión ya fue verificada por sesion.php

$model = new Anuncio();
$mensaje = '';
$tipo_mensaje = '';
$accion = $_GET['accion'] ?? 'listar';
$idAnuncio = $_GET['id'] ?? null;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accionPost = $_POST['accion'] ?? '';
    
    if ($accionPost === 'crear') {
        $datos = [
            'titulo' => $_POST['titulo'],
            'contenido' => $_POST['contenido'],
            'tipo' => $_POST['tipo'],
            'prioridad' => $_POST['prioridad'],
            'fecha_inicio' => $_POST['fecha_inicio'] ?: null,
            'fecha_fin' => $_POST['fecha_fin'] ?: null,
            'activo' => isset($_POST['activo']) ? 1 : 0,
            'posicion' => $_POST['posicion'],
            'estilo_css' => $_POST['estilo_css'] ?: null,
            'id_usuario' => $id_usuario_sesion ?? null
        ];
        
        $resultado = $model->crearAnuncio($datos);
        if ($resultado) {
            $mensaje = 'Anuncio creado exitosamente';
            $tipo_mensaje = 'success';
            $accion = 'listar';
        } else {
            $mensaje = 'Error al crear el anuncio';
            $tipo_mensaje = 'error';
        }
    }
    
    elseif ($accionPost === 'editar') {
        $id = $_POST['id_anuncio'];
        $datos = [
            'titulo' => $_POST['titulo'],
            'contenido' => $_POST['contenido'],
            'tipo' => $_POST['tipo'],
            'prioridad' => $_POST['prioridad'],
            'fecha_inicio' => $_POST['fecha_inicio'] ?: null,
            'fecha_fin' => $_POST['fecha_fin'] ?: null,
            'activo' => isset($_POST['activo']) ? 1 : 0,
            'posicion' => $_POST['posicion'],
            'estilo_css' => $_POST['estilo_css'] ?: null
        ];
        
        $resultado = $model->actualizarAnuncio($id, $datos);
        if ($resultado) {
            $mensaje = 'Anuncio actualizado exitosamente';
            $tipo_mensaje = 'success';
            $accion = 'listar';
        } else {
            $mensaje = 'Error al actualizar el anuncio';
            $tipo_mensaje = 'error';
        }
    }
    
    elseif ($accionPost === 'eliminar') {
        $id = $_POST['id_anuncio'];
        $resultado = $model->delete($id);
        if ($resultado) {
            $mensaje = 'Anuncio eliminado exitosamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al eliminar el anuncio';
            $tipo_mensaje = 'error';
        }
        $accion = 'listar';
    }
    
    elseif ($accionPost === 'toggle') {
        $id = $_POST['id_anuncio'];
        $resultado = $model->toggleActivo($id);
        echo json_encode(['success' => $resultado]);
        exit;
    }
}

// Obtener datos según la acción
$anuncios = [];
$anuncio = null;
$estadisticas = $model->getEstadisticas();

if ($accion === 'listar') {
    $anuncios = $model->getAll();
} elseif ($accion === 'editar' && $idAnuncio) {
    $anuncio = $model->getById($idAnuncio);
    if (!$anuncio) {
        $mensaje = 'Anuncio no encontrado';
        $tipo_mensaje = 'error';
        $accion = 'listar';
    }
}
