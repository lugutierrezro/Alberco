<?php
/**
 * Controlador del historial de actividad
 */

include('../../services/database/config.php');
include('../../contans/layout/sesion.php');
require_once __DIR__ . '/../../models/auditoria.php';
require_once __DIR__ . '/../../models/usuario.php';

// Verificar que el usuario sea administrador
if ($rol_sesion !== 'ADMINISTRADOR') {
    header('Location: ../../dashboard.php');
    exit;
}

$auditoriaModel = new Auditoria();
$usuarioModel = new Usuario();

// Parámetros de paginación
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 50;
$offset = ($pagina - 1) * $por_pagina;

// Filtros
$filtros = [
    'id_usuario' => $_GET['id_usuario'] ?? null,
    'modulo' => $_GET['modulo'] ?? null,
    'accion' => $_GET['accion'] ?? null,
    'nivel' => $_GET['nivel'] ?? null,
    'fecha_desde' => $_GET['fecha_desde'] ?? null,
    'fecha_hasta' => $_GET['fecha_hasta'] ?? null,
    'busqueda' => $_GET['busqueda'] ?? null,
    'limite' => $por_pagina,
    'offset' => $offset
];

// Limpiar filtros vacíos
$filtros = array_filter($filtros, function($value) {
    return $value !== null && $value !== '';
});

// Obtener actividades
$actividades = $auditoriaModel->getActividades($filtros);
$total_registros = $auditoriaModel->contarActividades($filtros);
$total_paginas = ceil($total_registros / $por_pagina);

// Obtener estadísticas
$estadisticas = $auditoriaModel->getEstadisticas(
    $filtros['fecha_desde'] ?? null,
    $filtros['fecha_hasta'] ?? null
);

// Obtener listas para filtros
$modulos = $auditoriaModel->getModulos();
$acciones = $auditoriaModel->getAcciones();
$usuarios = $usuarioModel->getAll();

// Exportar a CSV si se solicita
if (isset($_GET['exportar']) && $_GET['exportar'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=historial_actividad_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Encabezados
    fputcsv($output, ['Fecha/Hora', 'Usuario', 'Módulo', 'Acción', 'Descripción', 'Nivel', 'IP']);
    
    // Datos (sin límite para exportar todo)
    $filtros_exportar = $filtros;
    unset($filtros_exportar['limite']);
    unset($filtros_exportar['offset']);
    
    $todos = $auditoriaModel->getActividades($filtros_exportar);
    
    foreach ($todos as $act) {
        fputcsv($output, [
            $act['fecha_accion'],
            $act['nombre_completo'] ?? $act['username'] ?? 'Sistema',
            $act['modulo'] ?? '',
            $act['accion'],
            $act['descripcion'],
            $act['nivel'],
            $act['ip_address']
        ]);
    }
    
    fclose($output);
    exit;
}
