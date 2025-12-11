<?php
// views/reportes/index.php
$pageTitle = "Reportes y Estadísticas";
include_once '../../contans/layout/sesion.php';
include_once '../../contans/layout/parte1.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><i class="fas fa-chart-line mr-2"></i>Reportes y Estadísticas</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            
            <!-- Filtros -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label>Fecha Inicio:</label>
                            <input type="date" id="fecha_inicio" class="form-control" value="<?php echo date('Y-m-d', strtotime('-7 days')); ?>">
                        </div>
                        <div class="col-md-3">
                            <label>Fecha Fin:</label>
                            <input type="date" id="fecha_fin" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" onclick="cargarReportes()">
                                <i class="fas fa-filter mr-1"></i> Filtrar
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-secondary w-100" onclick="imprimirReporte()">
                                <i class="fas fa-print mr-1"></i> Imprimir
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPIs -->
            <div class="row" id="kpi-container">
                <!-- Se cargan dinámicamente -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="kpi-ventas">S/ 0.00</h3>
                            <p>Total Ventas</p>
                        </div>
                        <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="kpi-utilidad">S/ 0.00</h3>
                            <p>Utilidad Estimada</p>
                        </div>
                        <div class="icon"><i class="fas fa-coins"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="kpi-clientes">0</h3>
                            <p>Clientes Atendidos</p>
                        </div>
                        <div class="icon"><i class="fas fa-users"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="kpi-ticket">S/ 0.00</h3>
                            <p>Ticket Promedio</p>
                        </div>
                        <div class="icon"><i class="fas fa-receipt"></i></div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <h3 class="card-title">Evolución de Ventas</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="ventasChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <h3 class="card-title">Métodos de Pago</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="pagosChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <h3 class="card-title">Top 10 Productos Más Vendidos</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-valign-middle">
                                    <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Total</th>
                                    </tr>
                                    </thead>
                                    <tbody id="top-productos-body">
                                        <!-- Data dinámica -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                 <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <h3 class="card-title text-danger">Alertas de Stock Bajo</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Stock</th>
                                        <th>Mínimo</th>
                                        <th>Estado</th>
                                    </tr>
                                    </thead>
                                    <tbody id="stock-alerta-body">
                                        <!-- Se debe llenar con JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<?php include_once '../../contans/layout/parte2.php'; ?>

<!-- ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- jsPDF for professional PDF generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.0/jspdf.plugin.autotable.min.js"></script>

<script>
let ventasChartInstance = null;
let pagosChartInstance = null;

// Variables globales para almacenar datos del reporte (para PDF completo)
let reportData = {
    kpis: {},
    utilidad: [],
    ventasPeriodo: [],
    metodosPago: [],
    topProductos: [],
    stockBajo: [],
    topClientes: [],
    tipoPedido: [],
    ventasHora: []
};

document.addEventListener('DOMContentLoaded', function() {
    cargarReportes();
});

function cargarReportes() {
    const inicio = document.getElementById('fecha_inicio').value;
    const fin = document.getElementById('fecha_fin').value;
    
    // 1. Cargar KPIs del Dashboard
    fetch(`../../controllers/venta/reportes.php?tipo=kpis_dashboard&fecha_inicio=${inicio}&fecha_fin=${fin}`)
        .then(response => response.json())
        .then(res => {
            if(res.success && res.data) {
                reportData.kpis = res.data;
                document.getElementById('kpi-ventas').innerText = 'S/ ' + parseFloat(res.data.ventas_totales || 0).toFixed(2);
                document.getElementById('kpi-clientes').innerText = (res.data.clientes_atendidos || 0);
                document.getElementById('kpi-ticket').innerText = 'S/ ' + parseFloat(res.data.ticket_promedio || 0).toFixed(2);
            }
        });
    
    // 2. Cargar Utilidad
    fetch(`../../controllers/venta/reportes.php?tipo=utilidad&fecha_inicio=${inicio}&fecha_fin=${fin}`)
        .then(response => response.json())
        .then(res => {
            if(res.success && res.data.length > 0) {
                reportData.utilidad = res.data;
                let utilTotal = 0;
                res.data.forEach(d => {
                    let val = parseFloat(d.utilidad_bruta);
                    if(!isNaN(val)) utilTotal += val;
                });
                document.getElementById('kpi-utilidad').innerText = 'S/ ' + utilTotal.toFixed(2);
            }
        });
    
    // 3. Gráfico Ventas por Periodo
    fetch(`../../controllers/venta/reportes.php?tipo=ventas_periodo&fecha_inicio=${inicio}&fecha_fin=${fin}`)
        .then(response => response.json())
        .then(res => {
            if(res.success) {
                reportData.ventasPeriodo = res.data;
                renderVentasChart(res.data);
            }
        });

    // 4. Gráfico Métodos de Pago
    fetch(`../../controllers/venta/reportes.php?tipo=metodos_pago&fecha_inicio=${inicio}&fecha_fin=${fin}`)
        .then(response => response.json())
        .then(res => {
            if(res.success) {
                reportData.metodosPago = res.data;
                renderPagosChart(res.data);
            }
        });

    // 5. Top Productos
    fetch(`../../controllers/venta/reportes.php?tipo=productos_mas_vendidos&fecha_inicio=${inicio}&fecha_fin=${fin}`)
        .then(response => response.json())
        .then(res => {
            if(res.success) {
                reportData.topProductos = res.data;
                renderTopProductos(res.data);
            }
        });
    
    // 6. Alertas de Stock Bajo
    fetch(`../../controllers/venta/reportes.php?tipo=stock_bajo`)
        .then(response => response.json())
        .then(res => {
            if(res.success) {
                reportData.stockBajo = res.data;
                renderStockAlertas(res.data);
            }
        });
    
    // 7. Top Clientes
    fetch(`../../controllers/venta/reportes.php?tipo=top_clientes&fecha_inicio=${inicio}&fecha_fin=${fin}&limite=10`)
        .then(response => response.json())
        .then(res => {
            if(res.success) reportData.topClientes = res.data;
        });
    
    // 8. Tipo de Pedido
    fetch(`../../controllers/venta/reportes.php?tipo=tipo_pedido&fecha_inicio=${inicio}&fecha_fin=${fin}`)
        .then(response => response.json())
        .then(res => {
            if(res.success) reportData.tipoPedido = res.data;
        });
    
    // 9. Ventas por Hora
    fetch(`../../controllers/venta/reportes.php?tipo=ventas_hora&fecha_inicio=${inicio}&fecha_fin=${fin}`)
        .then(response => response.json())
        .then(res => {
            if(res.success) reportData.ventasHora = res.data;
        });
}

function renderVentasChart(data) {
    const ctx = document.getElementById('ventasChart').getContext('2d');
    
    const labels = data.map(item => item.fecha);
    const values = data.map(item => item.total_dia);

    if(ventasChartInstance) ventasChartInstance.destroy();

    ventasChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ventas (S/)',
                data: values,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

function renderPagosChart(data) {
    const ctx = document.getElementById('pagosChart').getContext('2d');
    
    const labels = data.map(item => item.nombre_metodo);
    const values = data.map(item => item.total_monto);
    const colors = ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'];

    if(pagosChartInstance) pagosChartInstance.destroy();

    pagosChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function renderTopProductos(data) {
    const tbody = document.getElementById('top-productos-body');
    tbody.innerHTML = '';
    
    data.forEach(item => {
        const row = `
            <tr>
                <td>${item.nombre} <br> <small class="text-muted">${item.nombre_categoria}</small></td>
                <td>${item.total_vendido}</td>
                <td class="text-success">S/ ${parseFloat(item.total_ingresos).toFixed(2)}</td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}

function renderStockAlertas(data) {
    const tbody = document.getElementById('stock-alerta-body');
    tbody.innerHTML = '';
    
    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No hay productos con stock bajo</td></tr>';
        return;
    }
    
    data.forEach(item => {
        const stockClase = item.stock === 0 ? 'badge-danger' : 'badge-warning';
        const estadoTexto = item.stock === 0 ? 'Agotado' : 'Stock Bajo';
        
        const row = `
            <tr>
                <td>${item.nombre} <br> <small class="text-muted">${item.nombre_categoria || 'Sin categoría'}</small></td>
                <td class="text-${item.stock === 0 ? 'danger' : 'warning'} font-weight-bold">${item.stock}</td>
                <td>${item.stock_minimo}</td>
                <td><span class="badge ${stockClase}">${estadoTexto}</span></td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}

async function imprimirReporte() {
    try {
        // Verificar que jsPDF esté cargado
        if (typeof window.jspdf === 'undefined') {
            alert('Error: La librería jsPDF no se ha cargado correctamente. Por favor recarga la página.');
            return;
        }
        
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        
        // Datos del reporte
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFin = document.getElementById('fecha_fin').value;
        const fechaGeneracion = new Date().toLocaleString('es-PE');
        
        // Colores corporativos
        const primaryColor = [41, 128, 185]; // Azul
        const secondaryColor = [52, 73, 94]; // Gris oscuro
        const accentColor = [46, 204, 113]; // Verde
        
        let yPos = 20;
    
    // ==================== HEADER ====================
    // Logo y título
    doc.setFillColor(...primaryColor);
    doc.rect(0, 0, 210, 40, 'F');
    
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(24);
    doc.setFont('helvetica', 'bold');
    doc.text('ALBERCO', 20, 20);
    
    doc.setFontSize(14);
    doc.setFont('helvetica', 'normal');
    doc.text('Reporte de Ventas y Estadísticas', 20, 30);
    
    // Fecha del reporte
    doc.setFontSize(9);
    doc.text(`Período: ${fechaInicio} al ${fechaFin}`, 150, 20, { align: 'right' });
    doc.text(`Generado: ${fechaGeneracion}`, 150, 25, { align: 'right' });
    
    yPos = 50;
    
    // ==================== RESUMEN EJECUTIVO ====================
    doc.setTextColor(...secondaryColor);
    doc.setFontSize(16);
    doc.setFont('helvetica', 'bold');
    doc.text('Resumen Ejecutivo', 20, yPos);
    
    yPos += 10;
    
    // KPIs en cajas
    const kpis = [
        { label: 'Total Ventas', value: document.getElementById('kpi-ventas').innerText, color: [52, 152, 219] },
        { label: 'Utilidad', value: document.getElementById('kpi-utilidad').innerText, color: [46, 204, 113] },
        { label: 'Clientes', value: document.getElementById('kpi-clientes').innerText, color: [241, 196, 15] },
        { label: 'Ticket Promedio', value: document.getElementById('kpi-ticket').innerText, color: [231, 76, 60] }
    ];
    
    const boxWidth = 42;
    const boxHeight = 20;
    let xPos = 20;
    
    kpis.forEach((kpi, index) => {
        // Caja con color
        doc.setFillColor(...kpi.color);
        doc.roundedRect(xPos, yPos, boxWidth, boxHeight, 2, 2, 'F');
        
        // Texto
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(8);
        doc.setFont('helvetica', 'normal');
        doc.text(kpi.label, xPos + boxWidth/2, yPos + 6, { align: 'center' });
        
        doc.setFontSize(14);
        doc.setFont('helvetica', 'bold');
        doc.text(kpi.value, xPos + boxWidth/2, yPos + 15, { align: 'center' });
        
        xPos += boxWidth + 5;
    });
    
    yPos += 30;
    
    // ==================== GRÁFICO DE VENTAS ====================
    doc.setTextColor(...secondaryColor);
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text('Evolución de Ventas', 20, yPos);
    
    yPos += 5;
    
    // Convertir gráfico a imagen
    const ventasCanvas = document.getElementById('ventasChart');
    if (ventasCanvas) {
        const ventasImg = ventasCanvas.toDataURL('image/png');
        doc.addImage(ventasImg, 'PNG', 20, yPos, 170, 60);
        yPos += 65;
    }
    
    // ==================== NUEVA PÁGINA - DISTRIBUCIÓN ====================
    doc.addPage();
    yPos = 20;
    
    doc.setTextColor(...secondaryColor);
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text('Distribución por Métodos de Pago', 20, yPos);
    
    yPos += 5;
    
    // Gráfico de métodos de pago
    const pagosCanvas = document.getElementById('pagosChart');
    if (pagosCanvas) {
        const pagosImg = pagosCanvas.toDataURL('image/png');
        doc.addImage(pagosImg, 'PNG', 55, yPos, 100, 60);
        yPos += 70;
    }
    
    // ==================== TOP PRODUCTOS ====================
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text('Top 10 Productos Más Vendidos', 20, yPos);
    
    yPos += 5;
    
    if (reportData.topProductos && reportData.topProductos.length > 0) {
        const productosRows = reportData.topProductos.slice(0, 10).map(item => [
            item.nombre,
            item.total_vendido,
            'S/ ' + parseFloat(item.total_ingresos).toFixed(2)
        ]);
        
        doc.autoTable({
            startY: yPos,
            head: [['Producto', 'Cant.', 'Ingresos']],
            body: productosRows,
            theme: 'grid',
            headStyles: { fillColor: primaryColor, fontSize: 10, fontStyle: 'bold' },
            bodyStyles: { fontSize: 9 },
            columnStyles: {
                0: { cellWidth: 100 },
                1: { cellWidth: 40, halign: 'center' },
                2: { cellWidth: 45, halign: 'right' }
            },
            margin: { left: 20, right: 20 }
        });
        yPos = doc.lastAutoTable.finalY + 10;
    }
    
    // ==================== ALERTAS DE STOCK ====================
    if (yPos > 200) {
        doc.addPage();
        yPos = 20;
    }
    
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text('Alertas de Stock Bajo', 20, yPos);
    
    yPos += 5;
    
    if (reportData.stockBajo && reportData.stockBajo.length > 0) {
        const stockRows = reportData.stockBajo.map(item => [
            item.nombre,
            item.stock,
            item.stock_minimo,
            item.stock === 0 ? 'Agotado' : 'Stock Bajo'
        ]);
        
        doc.autoTable({
            startY: yPos,
            head: [['Producto', 'Stock', 'Mín', 'Estado']],
            body: stockRows,
            theme: 'grid',
            headStyles: { fillColor: [231, 76, 60], fontSize: 10, fontStyle: 'bold' },
            bodyStyles: { fontSize: 9 },
            columnStyles: {
                0: { cellWidth: 90 },
                1: { cellWidth: 30, halign: 'center' },
                2: { cellWidth: 30, halign: 'center' },
                3: { cellWidth: 35, halign: 'center' }
            },
            margin: { left: 20, right: 20 }
        });
        yPos = doc.lastAutoTable.finalY + 10;
    }
    
    // ==================== NUEVA PÁGINA - TOP CLIENTES ====================
    doc.addPage();
    yPos = 20;
    
    doc.setTextColor(...secondaryColor);
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text('Top 10 Mejores Clientes', 20, yPos);
    yPos += 5;
    
    if (reportData.topClientes && reportData.topClientes.length > 0) {
        const clientesRows = reportData.topClientes.map(c => [
            c.nombre + ' ' + (c.apellidos || ''),
            c.total_compras,
            'S/ ' + parseFloat(c.total_gastado).toFixed(2),
            'S/ ' + parseFloat(c.ticket_promedio).toFixed(2)
        ]);
        
        doc.autoTable({
            startY: yPos,
            head: [['Cliente', 'Compras', 'Total Gastado', 'Ticket Prom.']],
            body: clientesRows,
            theme: 'grid',
            headStyles: { fillColor: [46, 204, 113], fontSize: 10 },
            bodyStyles: { fontSize: 9 },
            columnStyles: {
                0: { cellWidth: 70 },
                1: { cellWidth: 35, halign: 'center' },
                2: { cellWidth: 40, halign: 'right' },
                3: { cellWidth: 40, halign: 'right' }
            }
        });
        yPos = doc.lastAutoTable.finalY + 15;
    }
    
    // ==================== ANÁLISIS POR TIPO DE PEDIDO ====================
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text('Distribución por Tipo de Pedido', 20, yPos);
    yPos += 5;
    
    if (reportData.tipoPedido && reportData.tipoPedido.length > 0) {
        const tipoPedidoRows = reportData.tipoPedido.map(t => [
            t.tipo_pedido === 'delivery' ? 'Delivery' : t.tipo_pedido === 'para_llevar' ? 'Para Llevar' : 'En Local',
            t.total_ventas,
            'S/ ' + parseFloat(t.total_monto).toFixed(2),
            'S/ ' + parseFloat(t.ticket_promedio).toFixed(2)
        ]);
        
        doc.autoTable({
            startY: yPos,
            head: [['Tipo', 'Pedidos', 'Total', 'Ticket Prom.']],
            body: tipoPedidoRows,
            theme: 'grid',
            headStyles: { fillColor: [241, 196, 15], fontSize: 10 },
            bodyStyles: { fontSize: 9 },
            columnStyles: {
                0: { cellWidth: 50 },
                1: { cellWidth: 35, halign: 'center' },
                2: { cellWidth: 50, halign: 'right' },
                3: { cellWidth: 50, halign: 'right' }
            }
        });
        yPos = doc.lastAutoTable.finalY + 15;
    }
    
    // ==================== ANÁLISIS DE HORARIOS ====================
    if (yPos > 200) {
        doc.addPage();
        yPos = 20;
    }
    
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text('Análisis de Ventas por Horario', 20, yPos);
    yPos += 5;
    
    if (reportData.ventasHora && reportData.ventasHora.length > 0) {
        // Encontrar hora pico
        const horaPico = reportData.ventasHora.reduce((max, h) => 
            parseFloat(h.total_monto) > parseFloat(max.total_monto) ? h : max
        );
        
        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(...accentColor);
        doc.text(`Hora Pico: ${horaPico.hora}:00 hrs - S/ ${parseFloat(horaPico.total_monto).toFixed(2)} en ventas`, 20, yPos);
        yPos += 10;
        
        // Tabla de horarios (top 10)
        const horariosRows = reportData.ventasHora
            .sort((a,b) => parseFloat(b.total_monto) - parseFloat(a.total_monto))
            .slice(0, 10)
            .map(h => [
                h.hora + ':00',
                h.total_ventas,
                'S/ ' + parseFloat(h.total_monto).toFixed(2)
            ]);
        
        doc.setTextColor(...secondaryColor);
        doc.autoTable({
            startY: yPos,
            head: [['Hora', 'Ventas', 'Monto Total']],
            body: horariosRows,
            theme: 'grid',
            headStyles: { fillColor: [52, 152, 219], fontSize: 10 },
            bodyStyles: { fontSize: 9 },
            columnStyles: {
                0: { cellWidth: 60, halign: 'center' },
                1: { cellWidth: 60, halign: 'center' },
                2: { cellWidth: 65, halign: 'right' }
            }
        });
        yPos = doc.lastAutoTable.finalY + 15;
    }
    
    // ==================== INTERPRETACIONES Y ANÁLISIS ====================
    if (yPos > 200) {
        doc.addPage();
        yPos = 20;
    }
    
    doc.setFillColor(46, 204, 113);
    doc.rect(20, yPos-5, 170, 10, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text('INTERPRETACIONES Y ANÁLISIS DE NEGOCIO', 105, yPos, { align: 'center' });
    yPos += 15;
    
    doc.setTextColor(...secondaryColor);
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.text('Resumen del Período', 20, yPos);
    yPos += 8;
    
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    
    // Análisis automático basado en datos
    const kpisData = reportData.kpis;
    const interpretaciones = [];
    
    if (kpisData.ventas_totales) {
        const ticketProm = parseFloat(kpisData.ticket_promedio) || 0;
        interpretaciones.push(
            `• El negocio generó S/ ${parseFloat(kpisData.ventas_totales).toFixed(2)} en ventas totales durante el período analizado.`
        );
        interpretaciones.push(
            `• Se atendieron ${kpisData.clientes_atendidos} clientes únicos con un ticket promedio de S/  ${ticketProm.toFixed(2)}.`
        );
        
        if (ticketProm > 80) {
            interpretaciones.push(
                `• ✓ El ticket promedio es alto, indicando buena valoración de los productos.`
            );
        } else if (ticketProm < 40) {
            interpretaciones.push(
                `• ⚠ El ticket promedio es bajo. Considere estrategias de upselling.`
            );
        }
    }
    
    if (reportData.topProductos && reportData.topProductos.length > 0) {
        const topProd = reportData.topProductos[0];
        interpretaciones.push(
            `• El producto más vendido fue "${topProd.nombre}" con ${topProd.total_vendido} unidades.`
        );
    }
    
    if (reportData.metodosPago && reportData.metodosPago.length > 0) {
        const topMetodo = reportData.metodosPago[0];
        interpretaciones.push(
            `• El método de pago preferido es ${topMetodo.nombre_metodo} (${parseFloat(topMetodo.porcentaje || 0).toFixed(1)}% de ventas).`
        );
    }
    
    if (reportData.stockBajo && reportData.stockBajo.length > 0) {
        const agotados = reportData.stockBajo.filter(p => p.stock === 0).length;
        if (agotados > 0) {
            interpretaciones.push(
                `• ⚠ ALERTA: ${agotados} producto(s) agotado(s). Requiere reposición urgente.`
            );
        }
    }
    
    interpretaciones.forEach(texto => {
        if (yPos > 270) {
            doc.addPage();
            yPos = 20;
        }
        const lines = doc.splitTextToSize(texto, 170);
        doc.text(lines, 20, yPos);
        yPos += (lines.length * 6) + 2;
    });
    
    yPos += 5;
    
    // ==================== RECOMENDACIONES ====================
    if (yPos > 240) {
        doc.addPage();
        yPos = 20;
    }
    
    doc.setFillColor(41, 128, 185);
    doc.rect(20, yPos-5, 170, 10, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.text('RECOMENDACIONES ESTRATÉGICAS', 105, yPos, { align: 'center' });
    yPos += 15;
    
    doc.setTextColor(...secondaryColor);
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    
    const recomendaciones = [
        '1. Gestión de Inventario: Reponer productos agotados y mantener stock óptimo de productos estrella.',
        '2. Horarios Pico: Considerar reforzar personal durante las horas de mayor demanda para mejorar servicio.',
        '3. Fidelización: Implementar programa de puntos o descuentos para clientes frecuentes identificados.',
        '4. Productos Populares: Asegurar disponibilidad constante de los productos del Top 10.',
        '5. Métodos de Pago: Facilitar los métodos de pago preferidos por los clientes.',
        '6. Análisis Continuo: Realizar este reporte semanalmente para detectar tendencias tempranas.'
    ];
    
    recomendaciones.forEach(rec => {
        if (yPos > 270) {
            doc.addPage();
            yPos = 20;
        }
        const lines = doc.splitTextToSize(rec, 170);
        doc.text(lines, 20, yPos);
        yPos += (lines.length * 6) + 4;
    });
    
    // ==================== FOOTER EN TODAS LAS PÁGINAS ====================
    const pageCount = doc.internal.getNumberOfPages();
    
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        
        // Línea divisoria
        doc.setDrawColor(200, 200, 200);
        doc.line(20, 280, 190, 280);
        
        // Footer
        doc.setFontSize(8);
        doc.setTextColor(128, 128, 128);
        doc.setFont('helvetica', 'normal');
        doc.text('ALBERCO - Sistema de Gestión de Ventas', 105, 285, { align: 'center' });
        doc.text(`Página ${i} de ${pageCount}`, 105, 290, { align: 'center' });
    }
    
    // Guardar PDF
    const fileName = `Reporte_Ventas_${fechaInicio}_${fechaFin}.pdf`;
    
    // Usar blob para descarga correcta
    const pdfBlob = doc.output('blob');
    const blobUrl = URL.createObjectURL(pdfBlob);
    const link = document.createElement('a');
    link.href = blobUrl;
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(blobUrl);
    
    // Notificación de éxito
    console.log('PDF generado correctamente:', fileName);
    
    } catch (error) {
        console.error('Error al generar PDF:', error);
        alert('Error al generar el PDF: ' + error.message + '\n\nPor favor verifica la consola para más detalles.');
    }
}
</script>

<style type="text/css" media="print">
   .no-print { display: none; }
   /* Asegurar que gráficos se impriman */
   canvas { max-width: 100% !important; }
</style>
