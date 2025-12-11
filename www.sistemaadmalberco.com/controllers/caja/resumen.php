<?php
// Resumen de Caja (sin JSON - preparar datos)

// DEBUG TEMPORAL
error_log("=== EJECUTANDO RESUMEN.PHP ===");

try {
    $fecha = $_GET['fecha'] ?? date('Y-m-d');
    
    // Obtener arqueo de caja abierta (buscar por estado, no por fecha exacta)
    $arqueoSql = "SELECT a.*, 
                         CONCAT(COALESCE(e.nombres, u.username), ' ', COALESCE(e.apellidos, '')) as nombre_usuario 
                  FROM tb_arqueo_caja a
                  INNER JOIN tb_usuarios u ON a.id_usuario_apertura = u.id_usuario
                  LEFT JOIN tb_empleados e ON u.id_empleado = e.id_empleado
                  WHERE a.estado = 'abierto'
                  AND a.estado_registro = 'ACTIVO'
                  ORDER BY a.id_arqueo DESC 
                  LIMIT 1";
    
    $arqueoStmt = $pdo->prepare($arqueoSql);
    $arqueoStmt->execute();
    $caja_actual = $arqueoStmt->fetch(PDO::FETCH_ASSOC);
    $arqueo_dato = $caja_actual;
    
    // DEBUG TEMPORAL
    error_log("Caja actual encontrada: " . ($caja_actual ? "SÍ" : "NO"));
    if ($caja_actual) {
        error_log("ID Arqueo: " . $caja_actual['id_arqueo']);
        error_log("Estado: " . $caja_actual['estado']);
        $fecha = $caja_actual['fecha_arqueo'];
        error_log("Fecha usada: " . $fecha);
    }
    
    // Obtener resumen de movimientos del día
    $movimientosSql = "SELECT 
                          tipo_movimiento,
                          COUNT(*) as cantidad,
                          SUM(monto) as total
                       FROM tb_movimientos_caja
                       WHERE DATE(fecha_movimiento) = ?
                       GROUP BY tipo_movimiento";
    
    $movimientosStmt = $pdo->prepare($movimientosSql);
    $movimientosStmt->execute([$fecha]);
    $resumen_movimientos = $movimientosStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular totales
    $totalIngresos = 0;
    $totalEgresos = 0;
    
    foreach ($resumen_movimientos as $mov) {
        if ($mov['tipo_movimiento'] === 'INGRESO') {
            $totalIngresos = $mov['total'];
        } elseif ($mov['tipo_movimiento'] === 'EGRESO') {
            $totalEgresos = $mov['total'];
        }
    }
    
    
    // Ventas del día (desde tb_pedidos y tb_ventas)
    $ventasSql = "SELECT 
                     COUNT(DISTINCT v.id_venta) as total_pedidos,
                     COALESCE(SUM(v.total), 0) as total_ventas,
                     COALESCE(AVG(v.total), 0) as ticket_promedio,
                     COALESCE(SUM(v.propina), 0) as total_propinas
                  FROM tb_ventas v
                  WHERE DATE(v.fecha_venta) = ?
                  AND v.estado_registro = 'ACTIVO'";
    
    $ventasStmt = $pdo->prepare($ventasSql);
    $ventasStmt->execute([$fecha]);
    $ventas_dia = $ventasStmt->fetch(PDO::FETCH_ASSOC);
    
    // Calcular saldo actual
    $saldo_actual = ($caja_actual['saldo_inicial'] ?? 0) + $totalIngresos - $totalEgresos;
    
    // Preparar array resumen para la vista
    $resumen_caja = [
        'total_ingresos' => $totalIngresos,
        'total_egresos' => $totalEgresos,
        'saldo_actual' => $saldo_actual,
        'total_transacciones' => count($resumen_movimientos)
    ];
    
    // Alias para compatibilidad de bucles
    $resumen_por_metodo = [];
    
    // Obtener resumen por forma de pago
    $metodoPagoSql = "SELECT 
                        forma_pago,
                        COUNT(*) as cantidad,
                        SUM(monto) as total
                      FROM tb_movimientos_caja
                      WHERE DATE(fecha_movimiento) = ?
                      AND tipo_movimiento = 'INGRESO'
                      AND estado_registro = 'ACTIVO'
                      GROUP BY forma_pago";
    
    $metodoStmt = $pdo->prepare($metodoPagoSql);
    $metodoStmt->execute([$fecha]);
    $resumen_por_metodo = $metodoStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener movimientos recientes (últimos 10)
    $movRecientesSql = "SELECT *
                        FROM tb_movimientos_caja
                        WHERE DATE(fecha_movimiento) = ?
                        AND estado_registro = 'ACTIVO'
                        ORDER BY fecha_movimiento DESC
                        LIMIT 10";
    
    $movRecientesStmt = $pdo->prepare($movRecientesSql);
    $movRecientesStmt->execute([$fecha]);
    $movimientos_recientes = $movRecientesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener última caja cerrada
    $ultimaCajaSql = "SELECT *,
                            saldo_inicial as monto_inicial,
                            saldo_real as monto_final
                      FROM tb_arqueo_caja
                      WHERE estado = 'cerrado'
                      AND estado_registro = 'ACTIVO'
                      ORDER BY fecha_arqueo DESC, fyh_creacion DESC
                      LIMIT 1";
    
    $ultimaCajaStmt = $pdo->query($ultimaCajaSql);
    $ultima_caja = $ultimaCajaStmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("❌ ERROR EN RESUMEN.PHP: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $caja_actual = null;
    $arqueo_dato = null;
    $resumen_movimientos = [];
    $ventas_dia = ['total_pedidos' => 0, 'total_ventas' => 0, 'ticket_promedio' => 0, 'total_propinas' => 0];
    $resumen_caja = ['total_ingresos'=>0, 'total_egresos'=>0, 'saldo_actual'=>0, 'total_transacciones'=>0];
    $resumen_por_metodo = [];
    $movimientos_recientes = [];
    $ultima_caja = null;
}

error_log("=== FIN RESUMEN.PHP ===");
error_log("Caja actual al final: " . ($caja_actual ? "existe" : "NULL"));
