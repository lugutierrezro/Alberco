<?php
// Listar Notificaciones del Usuario (sin JSON - preparar datos)

if (!isset($_SESSION['id_usuario'])) {
    $notificaciones_datos = [];
    $resumen_notificaciones = [
        'no_leidas' => 0,
        'stock_bajo' => 0,
        'pedidos_pendientes' => 0,
        'mesas_ocupadas' => 0
    ];
} else {
    try {
        // Filtros
        $soloNoLeidas = isset($_GET['leidas']) && $_GET['leidas'] === '0';
        $tipoFiltro = $_GET['tipo'] ?? null;
        
        // Query de notificaciones
        $sql = "SELECT * FROM tb_notificaciones 
                WHERE id_usuario_destino = :usuario
                  AND estado_registro = 'ACTIVO'";
        
        if ($soloNoLeidas) {
            $sql .= " AND leido = 0";
        }
        
        if ($tipoFiltro) {
            $sql .= " AND tipo = :tipo";
        }
        
        $sql .= " ORDER BY fecha_notificacion DESC LIMIT 50";
        
        $stmt = $pdo->prepare($sql);
        $params = [':usuario' => $_SESSION['id_usuario']];
        
        if ($tipoFiltro) {
            $params[':tipo'] = $tipoFiltro;
        }
        
        $stmt->execute($params);
        $notificaciones_datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // RESUMEN - Estadísticas para los KPIs
        // 1. Notificaciones no leídas
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tb_notificaciones 
                               WHERE id_usuario_destino = :usuario AND leido = 0
                               AND estado_registro = 'ACTIVO'");
        $stmt->execute([':usuario' => $_SESSION['id_usuario']]);
        $no_leidas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // 2. Stock bajo
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_almacen 
                            WHERE stock <= stock_minimo AND estado_registro = 'ACTIVO'");
        $stock_bajo = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // 3. Pedidos pendientes (estados 1 y 2)
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_pedidos 
                            WHERE id_estado IN (1, 2) AND estado_registro = 'ACTIVO'");
        $pedidos_pendientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // 4. Mesas ocupadas
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_mesas 
                            WHERE estado = 'ocupada'");
        $mesas_ocupadas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $resumen_notificaciones = [
            'no_leidas' => $no_leidas,
            'stock_bajo' => $stock_bajo,
            'pedidos_pendientes' => $pedidos_pendientes,
            'mesas_ocupadas' => $mesas_ocupadas
        ];
        
    } catch (PDOException $e) {
        error_log("Error al obtener notificaciones: " . $e->getMessage());
        $notificaciones_datos = [];
        $resumen_notificaciones = [
            'no_leidas' => 0,
            'stock_bajo' => 0,
            'pedidos_pendientes' => 0,
            'mesas_ocupadas' => 0
        ];
    }
}

// NO usar echo, print, jsonResponse()
