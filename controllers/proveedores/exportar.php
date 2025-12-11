<?php
// Exportar Proveedores a CSV (ESTE ES CORRECTO - mantiene descarga)

require_once __DIR__ . '/../../services/database/config.php';

if (!isset($_SESSION['id_usuario'])) {
    die('No autenticado');
}

try {
    $sql = "SELECT p.*,
            (SELECT COUNT(*) FROM tb_compras c 
             WHERE c.id_proveedor = p.id_proveedor) as total_compras,
            (SELECT COALESCE(SUM(total), 0) FROM tb_compras c 
             WHERE c.id_proveedor = p.id_proveedor) as total_gastado,
            (SELECT MAX(fecha_compra) FROM tb_compras c 
             WHERE c.id_proveedor = p.id_proveedor) as ultima_compra
            FROM tb_proveedores p
            WHERE p.estado_registro = 'ACTIVO'
            ORDER BY p.nombre_proveedor";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Headers para descarga CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="proveedores_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeceras
    fputcsv($output, [
        'Código', 'Nombre', 'Empresa', 'RUC', 'Celular', 'Teléfono',
        'Email', 'Dirección', 'Ciudad', 'Total Compras', 'Monto Total', 'Última Compra'
    ]);
    
    // Datos
    foreach ($proveedores as $proveedor) {
        fputcsv($output, [
            $proveedor['codigo_proveedor'],
            $proveedor['nombre_proveedor'],
            $proveedor['empresa'],
            $proveedor['ruc'] ?? '',
            $proveedor['celular'],
            $proveedor['telefono'] ?? '',
            $proveedor['email'] ?? '',
            $proveedor['direccion'],
            $proveedor['ciudad'] ?? '',
            $proveedor['total_compras'] ?? 0,
            $proveedor['total_gastado'] ?? 0,
            $proveedor['ultima_compra'] ?? ''
        ]);
    }
    
    fclose($output);
    exit;
    
} catch (PDOException $e) {
    error_log("Error al exportar proveedores: " . $e->getMessage());
    die('Error al exportar');
}
