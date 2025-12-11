<?php
// Activity Feed Controller
require_once(__DIR__ . '/../../services/database/config.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDB();
    
    $actividades = [];
    
    // Últimos pedidos
    $stmt = $pdo->prepare("
        SELECT 
            'pedido' as tipo,
            id_pedido as id,
            CONCAT('Nuevo Pedido #', id_pedido) as titulo,
            fecha_pedido as fecha,
            TIMESTAMPDIFF(MINUTE, fecha_pedido, NOW()) as minutos_transcurridos
        FROM tb_pedidos
        ORDER BY fecha_pedido DESC
        LIMIT 3
    ");
    $stmt->execute();
    $actividades = array_merge($actividades, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Últimas ventas
    $stmt = $pdo->prepare("
        SELECT 
            'venta' as tipo,
            v.id_venta as id,
            CONCAT('Venta Completada - S/ ', FORMAT(v.total, 2)) as titulo,
            v.fecha_venta as fecha,
            TIMESTAMPDIFF(MINUTE, v.fecha_venta, NOW()) as minutos_transcurridos
        FROM tb_ventas v
        WHERE v.estado_venta = 'completada'
        ORDER BY v.fecha_venta DESC
        LIMIT 2
    ");
    $stmt->execute();
    $actividades = array_merge($actividades, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Nuevos clientes
    $stmt = $pdo->prepare("
        SELECT 
            'cliente' as tipo,
            id_cliente as id,
            CONCAT('Nuevo Cliente: ', nombre) as titulo,
            fecha_registro as fecha,
            TIMESTAMPDIFF(MINUTE, fecha_registro, NOW()) as minutos_transcurridos
        FROM tb_clientes
        ORDER BY fecha_registro DESC
        LIMIT 2
    ");
    $stmt->execute();
    $actividades = array_merge($actividades, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Arqueos de caja
    $stmt = $pdo->prepare("
        SELECT 
            'caja' as tipo,
            id_arqueo as id,
            CASE 
                WHEN estado = 'abierto' THEN 'Caja Abierta'
                ELSE 'Caja Cerrada'
            END as titulo,
            COALESCE(fecha_arqueo, NOW()) as fecha,
            TIMESTAMPDIFF(MINUTE, COALESCE(fecha_arqueo, NOW()), NOW()) as minutos_transcurridos
        FROM tb_arqueo_caja
        ORDER BY fecha_arqueo DESC
        LIMIT 1
    ");
    $stmt->execute();
    $actividades = array_merge($actividades, $stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Ordenar todas las actividades por fecha
    usort($actividades, function($a, $b) {
        return strtotime($b['fecha']) - strtotime($a['fecha']);
    });
    
    // Limitar a las 6 más recientes
    $actividades = array_slice($actividades, 0, 6);
    
    // Formatear tiempo transcurrido
    foreach ($actividades as &$act) {
        $minutos = $act['minutos_transcurridos'];
        
        if ($minutos < 1) {
            $act['tiempo_texto'] = 'Justo ahora';
        } elseif ($minutos < 60) {
            $act['tiempo_texto'] = "Hace $minutos " . ($minutos == 1 ? 'minuto' : 'minutos');
        } elseif ($minutos < 1440) {
            $horas = floor($minutos / 60);
            $act['tiempo_texto'] = "Hace $horas " . ($horas == 1 ? 'hora' : 'horas');
        } else {
            $dias = floor($minutos / 1440);
            $act['tiempo_texto'] = "Hace $dias " . ($dias == 1 ? 'día' : 'días');
        }
        
        // Asignar icono según tipo
        switch ($act['tipo']) {
            case 'pedido':
                $act['icono'] = 'fa-shopping-cart';
                break;
            case 'venta':
                $act['icono'] = 'fa-dollar-sign';
                break;
            case 'cliente':
                $act['icono'] = 'fa-user-plus';
                break;
            case 'caja':
                $act['icono'] = 'fa-cash-register';
                break;
            default:
                $act['icono'] = 'fa-info-circle';
        }
    }
    
    echo json_encode([
        'success' => true,
        'actividades' => $actividades
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
