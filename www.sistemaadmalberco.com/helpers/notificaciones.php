<?php
/**
 * Helper para crear notificaciones
 */

function crearNotificacion($pdo, $id_usuario, $tipo, $titulo, $mensaje, $url = null) {
    try {
        $sql = "INSERT INTO tb_notificaciones 
                (id_usuario, tipo, titulo, mensaje, url, fecha_creacion)
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_usuario, $tipo, $titulo, $mensaje, $url]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Error al crear notificación: " . $e->getMessage());
        return false;
    }
}

function crearNotificacionTodos($pdo, $tipo, $titulo, $mensaje, $url = null) {
    try {
        // Obtener todos los usuarios activos
        $sqlUsuarios = "SELECT id_usuario FROM tb_usuarios WHERE estado = 'ACTIVO'";
        $stmtUsuarios = $pdo->prepare($sqlUsuarios);
        $stmtUsuarios->execute();
        $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_COLUMN);
        
        $sql = "INSERT INTO tb_notificaciones 
                (id_usuario, tipo, titulo, mensaje, url, fecha_creacion)
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $pdo->prepare($sql);
        
        foreach ($usuarios as $id_usuario) {
            $stmt->execute([$id_usuario, $tipo, $titulo, $mensaje, $url]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Error al crear notificaciones para todos: " . $e->getMessage());
        return false;
    }
}

function notificarStockBajo($pdo, $producto_id, $producto_nombre, $stock) {
    $titulo = "Stock Bajo: " . $producto_nombre;
    $mensaje = "El producto tiene solo " . $stock . " unidades disponibles.";
    $url = URL_BASE . "/views/almacen/show.php?id=" . $producto_id;
    
    crearNotificacionTodos($pdo, 'STOCK', $titulo, $mensaje, $url);
}

function notificarNuevoPedido($pdo, $pedido_id, $numero_comanda) {
    $titulo = "Nuevo Pedido: " . $numero_comanda;
    $mensaje = "Se ha registrado un nuevo pedido.";
    $url = URL_BASE . "/views/pedidos/show.php?id=" . $pedido_id;
    
    crearNotificacionTodos($pdo, 'PEDIDO', $titulo, $mensaje, $url);
}
