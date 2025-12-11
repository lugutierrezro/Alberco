<?php
session_start();
require_once(__DIR__ . '/../../services/database/config.php');

echo "<h1>Debug Notificaciones en Navbar</h1>";
echo "<h2>1. Sesión Actual</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>2. Verificar Notificaciones para este Usuario</h2>";

if (isset($_SESSION['id_usuario'])) {
    $pdo = getDB();
    
    $id_usuario = $_SESSION['id_usuario'];
    echo "<p><strong>Buscando notificaciones para id_usuario_destino = $id_usuario</strong></p>";
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM tb_notificaciones 
        WHERE id_usuario_destino = :id_usuario 
          AND leido = 0
          AND estado_registro = 'ACTIVO'
    ");
    $stmt->execute([':id_usuario' => $id_usuario]);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<p><strong>Total no leídas:</strong> $total</p>";
    
    // Ver todas las notificaciones
    $stmt = $pdo->query("SELECT * FROM tb_notificaciones ORDER BY fecha_notificacion DESC LIMIT 10");
    $todas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Todas las notificaciones (últimas 10):</h3>";
    echo "<pre>";
    print_r($todas);
    echo "</pre>";
    
    echo "<h3>Test del código de parte1.php:</h3>";
    
    $totalNotif = 0;
    $notifsRecientes = [];
    
    try {
        $sqlNotif = "SELECT COUNT(*) as total FROM tb_notificaciones 
                     WHERE id_usuario_destino = :id_usuario AND leido = 0
                     AND estado_registro = 'ACTIVO'";
        $stmtNotif = $pdo->prepare($sqlNotif);
        $stmtNotif->execute([':id_usuario' => $id_usuario]);
        $totalNotif = (int) $stmtNotif->fetch(PDO::FETCH_ASSOC)['total'];

        $sqlNotifRecientes = "SELECT id_notificacion, tipo, titulo, fecha_notificacion, enlace
                              FROM tb_notificaciones 
                              WHERE id_usuario_destino = :id_usuario AND leido = 0 
                              AND estado_registro = 'ACTIVO'
                              ORDER BY fecha_notificacion DESC LIMIT 5";
        $stmtNotifRecientes = $pdo->prepare($sqlNotifRecientes);
        $stmtNotifRecientes->execute([':id_usuario' => $id_usuario]);
        $notifsRecientes = $stmtNotifRecientes->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    }
    
    echo "<p><strong>\$totalNotif:</strong> $totalNotif</p>";
    echo "<p><strong>\$notifsRecientes:</strong></p>";
    echo "<pre>";
    print_r($notifsRecientes);
    echo "</pre>";
    
} else {
    echo "<p style='color:red'>No hay sesión iniciada</p>";
}

echo "<hr>";
echo "<a href='../../' style='display:inline-block; padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>Volver al Dashboard</a>";
?>
