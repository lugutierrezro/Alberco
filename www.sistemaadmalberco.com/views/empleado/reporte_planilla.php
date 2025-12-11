<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');

// Obtener lista de empleados para el reporte
try {
    $sql = "SELECT e.*, r.rol as nombre_rol 
            FROM tb_empleados e
            LEFT JOIN tb_roles r ON e.id_rol = r.id_rol 
            WHERE e.estado_registro = 'ACTIVO' 
            ORDER BY e.apellidos, e.nombres";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al cargar datos: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Planilla de Empleados - Alberco</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            font-weight: bold;
            background-color: #e6e6e6;
        }
        .no-print {
            margin-bottom: 20px;
            text-align: right;
        }
        .btn-print {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn-print:hover {
            background-color: #0056b3;
        }
        .badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 10px;
            color: white;
            background-color: #6c757d;
        }
        .badge-active { background-color: #28a745; }
        .badge-inactive { background-color: #dc3545; }
        
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 10px;
            }
            @page {
                size: landscape;
                margin: 1cm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <p><em>Generando reporte... Por favor seleccione "Guardar como PDF" en la ventana de impresión.</em></p>
        <button onclick="window.print()" class="btn-print">Volver a Imprimir</button>
        <button onclick="window.close()" class="btn-print" style="background-color: #6c757d;">Cerrar</button>
    </div>

    <script>
        window.onload = function() {
            // Esperar un poco a que carguen estilos/imágenes si las hubiera
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>

    <div class="header">
        <h1><?php echo EMPRESA_NOMBRE; ?></h1>
        <p>RUC: 20600000001</p>
        <p><?php echo EMPRESA_DIRECCION; ?></p>
        <h2 style="margin-top: 10px;">REPORTE DE PLANILLA DE EMPLEADOS</h2>
        <p>Fecha de Emisión: <?php echo date('d/m/Y H:i'); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="30">#</th>
                <th width="80">CÓDIGO</th>
                <th>EMPLEADO</th>
                <th width="100">DOCUMENTO</th>
                <th width="100">ROL</th>
                <th width="90">FECHA ING.</th>
                <th width="80">ESTADO</th>
                <th width="100">SALARIO (S/)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $contador = 0;
            $total_planilla = 0;
            
            foreach ($empleados as $emp): 
                $contador++;
                $nombre_completo = strtoupper($emp['apellidos'] . ', ' . $emp['nombres']);
                $sueldo = !empty($emp['salario']) ? $emp['salario'] : 0;
                
                // Solo sumar al total si está activo (opcional, dependiendo de requerimiento)
                if ($emp['estado_laboral'] == 'ACTIVO') {
                    $total_planilla += $sueldo;
                }
            ?>
            <tr>
                <td class="text-center"><?php echo $contador; ?></td>
                <td class="text-center"><?php echo htmlspecialchars($emp['codigo_empleado']); ?></td>
                <td><?php echo htmlspecialchars($nombre_completo); ?></td>
                <td class="text-center">
                    <?php echo htmlspecialchars($emp['tipo_documento']); ?><br>
                    <?php echo htmlspecialchars($emp['numero_documento']); ?>
                </td>
                <td class="text-center"><?php echo htmlspecialchars($emp['nombre_rol']); ?></td>
                <td class="text-center">
                    <?php echo !empty($emp['fecha_contratacion']) ? date('d/m/Y', strtotime($emp['fecha_contratacion'])) : '-'; ?>
                </td>
                <td class="text-center">
                    <span class="badge <?php echo $emp['estado_laboral'] == 'ACTIVO' ? 'badge-active' : 'badge-inactive'; ?>">
                        <?php echo $emp['estado_laboral']; ?>
                    </span>
                </td>
                <td class="text-right">
                    <?php echo number_format($sueldo, 2); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="7" class="text-right">TOTAL PLANILLA MENSUAL (ACTIVOS):</td>
                <td class="text-right">S/ <?php echo number_format($total_planilla, 2); ?></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px; font-size: 10px; color: #666;">
        <p>Notas:</p>
        <ul>
            <li>El cálculo del total solo incluye empleados con estado laboral ACTIVO.</li>
            <li>Este reporte es para uso interno administrativo exclusivamente.</li>
        </ul>
    </div>

</body>
</html>
