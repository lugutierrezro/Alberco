<?php
// Obtener Movimientos de Caja (sin JSON - preparar datos)

try {
    $fecha = $_GET['fecha'] ?? date('Y-m-d');
    $tipo = $_GET['tipo'] ?? null;
    
    if (isset($_GET['fecha_inicio']) && isset($_GET['fecha_fin'])) {
        // Rango de fechas
        $sql = "SELECT m.*, u.username as usuario_nombre
                FROM tb_movimientos_caja m
                INNER JOIN tb_usuarios u ON m.id_usuario = u.id_usuario
                WHERE DATE(m.fecha_movimiento) BETWEEN :fecha_inicio AND :fecha_fin";
        
        $params = [
            ':fecha_inicio' => $_GET['fecha_inicio'],
            ':fecha_fin' => $_GET['fecha_fin']
        ];
        
        if ($tipo) {
            $sql .= " AND m.tipo_movimiento = :tipo";
            $params[':tipo'] = strtoupper($tipo);
        }
        
        $sql .= " ORDER BY m.fecha_movimiento DESC";
        
    } else {
        // Un solo día
        $sql = "SELECT m.*, u.username as usuario_nombre
                FROM tb_movimientos_caja m
                INNER JOIN tb_usuarios u ON m.id_usuario = u.id_usuario
                WHERE DATE(m.fecha_movimiento) = :fecha";
        
        $params = [':fecha' => $fecha];
        
        if ($tipo) {
            $sql .= " AND m.tipo_movimiento = :tipo";
            $params[':tipo'] = strtoupper($tipo);
        }
        
        $sql .= " ORDER BY m.fecha_movimiento DESC";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $movimientos_caja = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al obtener movimientos: " . $e->getMessage());
    $movimientos_caja = [];
}

// NO usar echo, print, jsonResponse()
