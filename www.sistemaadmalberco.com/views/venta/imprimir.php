<?php
// views/venta/imprimir.php
require_once '../../services/database/config.php';
require_once '../../models/venta.php';
require_once '../../models/usuario.php';

if (!Usuario::isAuthenticated()) {
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

if (!isset($_GET['id'])) {
    die('ID de venta no especificado');
}

$idVenta = $_GET['id'];
$ventaModel = new Venta();
$venta = $ventaModel->getVentaCompleta($idVenta); // Usando id_venta interno o nro_venta según lógica

// Si no encontró por nro_venta, intentar por ID interno (la función getVentasWithDetails usa nro_venta por defecto en filtro, ajustaremos si falla)
// Nota: getVentaCompleta usa getVentasWithDetails(['nro_venta' => $ventaId]). 
// Si el ID pasado es el ID interno, esto podría fallar si nro_venta != id_venta. 
// Asumiremos que se pasa el NRO_VENTA o ajustaremos. 
// Mejor: Hack rápido para soportar ambos o asumir ID interno convertimos a su lógica.
// Revisando el modelo: getVentasWithDetails usa WHERE v.nro_venta = :nro_venta.
// Si quiero buscar por ID primario, debo ajustar el modelo o pasar el filtro correcto.
// Voy a usar un filtro manual aquí si falla.

if (!$venta) {
    // Intento buscar por ID primario directo
    $sql = "SELECT nro_venta FROM tb_ventas WHERE id_venta = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idVenta]);
    $res = $stmt->fetch();
    if ($res) {
        $venta = $ventaModel->getVentaCompleta($res['nro_venta']);
    }
}

if (!$venta) {
    die('Venta no encontrada');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Venta - <?php echo $venta['serie_comprobante'] . '-' . $venta['numero_comprobante']; ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            max-width: 300px; /* Tamaño ticket aprox */
            margin: 0 auto;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .empresa {
            font-size: 18px;
            font-weight: bold;
        }
        .info-venta {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }
        .detalle {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .detalle th {
            text-align: left;
            border-bottom: 1px solid #000;
        }
        .detalle td {
            padding: 5px 0;
        }
        .totales {
            text-align: right;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
        }
        @media print {
            .no-print { display: none; }
        }
        .btn-print {
            display: block;
            width: 100%;
            padding: 10px;
            background: #000;
            color: #fff;
            text-align: center;
            text-decoration: none;
            margin-bottom: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="btn-print no-print">IMPRIMIR</button>

    <div class="header">
        <div class="empresa">ALBERCO POLLERÍA Y CHIFA</div>
        <div>RUC: 20123456789</div>
        <div>Av. Principal 123, Lima</div>
        <div>Tel: (01) 123-4567</div>
    </div>

    <div class="info-venta">
        <strong><?php echo strtoupper($venta['tipo_comprobante']); ?> DE VENTA ELECTRÓNICA</strong><br>
        <?php echo $venta['serie_comprobante'] . '-' . $venta['numero_comprobante']; ?><br>
        Fecha: <?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?><br>
        Cliente: <?php echo $venta['cliente_nombre'] . ' ' . $venta['cliente_apellidos']; ?><br>
        Doc: <?php echo $venta['cliente_documento']; ?><br>
        Atendido por: <?php echo $venta['empleado_nombres']; ?>
    </div>

    <table class="detalle">
        <thead>
            <tr>
                <th>Cant.</th>
                <th>Producto</th>
                <th>P.Unit</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($venta['detalles'] as $item): ?>
            <tr>
                <td><?php echo $item['cantidad']; ?></td>
                <td><?php echo $item['producto_nombre']; ?></td>
                <td><?php echo number_format($item['precio_unitario'], 2); ?></td>
                <td style="text-align: right;"><?php echo number_format($item['subtotal'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totales">
        <div>Subtotal: S/ <?php echo number_format($venta['subtotal'], 2); ?></div>
        <div>IGV (18%): S/ <?php echo number_format($venta['igv'], 2); ?></div>
        <?php if ($venta['descuento'] > 0): ?>
        <div>Descuento: - S/ <?php echo number_format($venta['descuento'], 2); ?></div>
        <?php endif; ?>
        <div style="font-size: 16px; font-weight: bold; margin-top: 5px;">
            TOTAL: S/ <?php echo number_format($venta['total'], 2); ?>
        </div>
        <div style="margin-top: 5px;">
            <small>Pago con: <?php echo $venta['metodo_pago']; ?></small><br>
            <small>Recibido: S/ <?php echo number_format($venta['monto_recibido'], 2); ?></small><br>
            <small>Vuelto: S/ <?php echo number_format($venta['vuelto'], 2); ?></small>
        </div>
    </div>

    <div class="footer">
        ¡Gracias por su compra!<br>
        Conserve este comprobante.<br>
        www.alberco.com
    </div>

    <script>
        // Auto-imprimir al cargar si se pasa ?print=true
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print') === 'true') {
            window.print();
        }
    </script>
</body>
</html>
