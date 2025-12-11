<?php
// Estadísticas de Mesas (sin JSON - preparar datos)

try {
    $sql = "SELECT 
                COUNT(*) as total_mesas,
                SUM(CASE WHEN estado = 'disponible' THEN 1 ELSE 0 END) as disponibles,
                SUM(CASE WHEN estado = 'ocupada' THEN 1 ELSE 0 END) as ocupadas,
                SUM(CASE WHEN estado = 'reservada' THEN 1 ELSE 0 END) as reservadas,
                SUM(CASE WHEN estado = 'mantenimiento' THEN 1 ELSE 0 END) as mantenimiento,
                SUM(capacidad) as capacidad_total,
                ROUND(AVG(capacidad), 2) as capacidad_promedio
            FROM tb_mesas
            WHERE estado_registro = 'ACTIVO'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $estadisticas_mesas = $stmt->fetch();
    
} catch (PDOException $e) {
    error_log("Error al obtener estadísticas: " . $e->getMessage());
    $estadisticas_mesas = null;
}

// NO usar echo, print, jsonResponse()
