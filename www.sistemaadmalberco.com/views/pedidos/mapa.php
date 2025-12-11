<?php
session_start();
require_once __DIR__ . '/../../services/database/config.php';

// Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ' . URL_BASE . '/views/login/');
    exit;
}

// Obtener pedidos delivery activos
try {
    $sql = "SELECT 
                p.id_pedido,
                p.nro_pedido,
                p.numero_comanda,
                p.direccion_entrega,
                p.latitud_entrega,
                p.longitud_entrega,
                p.total,
                p.fecha_pedido,
                CONCAT(c.nombre, ' ', COALESCE(c.apellidos, '')) as cliente_nombre,
                c.telefono as cliente_telefono,
                CONCAT(e.nombres, ' ', e.apellidos) as delivery_nombre,
                e.celular as delivery_celular,
                es.nombre_estado,
                es.color as estado_color,
                (SELECT latitud FROM tb_tracking_delivery 
                 WHERE id_pedido = p.id_pedido 
                 ORDER BY fecha_registro DESC LIMIT 1) as ultima_latitud,
                (SELECT longitud FROM tb_tracking_delivery 
                 WHERE id_pedido = p.id_pedido 
                 ORDER BY fecha_registro DESC LIMIT 1) as ultima_longitud,
                (SELECT fecha_registro FROM tb_tracking_delivery 
                 WHERE id_pedido = p.id_pedido 
                 ORDER BY fecha_registro DESC LIMIT 1) as ultima_actualizacion
            FROM tb_pedidos p
            INNER JOIN tb_clientes c ON p.id_cliente = c.id_cliente
            INNER JOIN tb_estados es ON p.id_estado = es.id_estado
            LEFT JOIN tb_empleados e ON p.id_empleado_delivery = e.id_empleado
            WHERE p.tipo_pedido = 'delivery'
            AND p.id_estado IN (1, 2, 3, 4)
            AND p.estado_registro = 'ACTIVO'
            ORDER BY p.fecha_pedido DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error al obtener pedidos: " . $e->getMessage());
    $pedidos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de Pedidos Delivery - Alberco</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        #map {
            height: calc(100vh - 120px);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .pedidos-sidebar {
            height: calc(100vh - 120px);
            overflow-y: auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 15px;
        }
        
        .pedido-card {
            border-left: 4px solid #0d6efd;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .pedido-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .pedido-card.active {
            border-left-color: #dc3545;
            background-color: #fff3cd;
        }
        
        .badge-estado {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .stats-card h3 {
            font-size: 2rem;
            margin: 0;
        }
        
        .stats-card p {
            margin: 0;
            opacity: 0.9;
        }
        
        .delivery-marker {
            background-color: #dc3545;
            border: 3px solid white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }
        
        .destination-marker {
            background-color: #28a745;
            border: 3px solid white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        
        .auto-refresh {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background: white;
            padding: 10px 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <div class="row mb-3">
            <div class="col-12">
                <h2><i class="fas fa-map-marked-alt"></i> Mapa de Pedidos Delivery en Tiempo Real</h2>
                <p class="text-muted">Monitoreo de todos los pedidos delivery activos</p>
            </div>
        </div>
        
        <div class="row">
            <!-- Sidebar de pedidos -->
            <div class="col-md-4">
                <div class="stats-card">
                    <h3 id="totalPedidos"><?php echo count($pedidos); ?></h3>
                    <p><i class="fas fa-motorcycle"></i> Pedidos Delivery Activos</p>
                </div>
                
                <div class="pedidos-sidebar" id="pedidosList">
                    <?php if (empty($pedidos)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No hay pedidos delivery activos en este momento.
                        </div>
                    <?php else: ?>
                        <?php foreach ($pedidos as $pedido): ?>
                            <div class="card pedido-card" 
                                 data-pedido-id="<?php echo $pedido['id_pedido']; ?>"
                                 data-lat="<?php echo $pedido['ultima_latitud'] ?? $pedido['latitud_entrega']; ?>"
                                 data-lng="<?php echo $pedido['ultima_longitud'] ?? $pedido['longitud_entrega']; ?>"
                                 data-dest-lat="<?php echo $pedido['latitud_entrega']; ?>"
                                 data-dest-lng="<?php echo $pedido['longitud_entrega']; ?>">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0">
                                            <i class="fas fa-receipt"></i> <?php echo $pedido['nro_pedido']; ?>
                                        </h6>
                                        <span class="badge badge-estado" style="background-color: <?php echo $pedido['estado_color']; ?>">
                                            <?php echo $pedido['nombre_estado']; ?>
                                        </span>
                                    </div>
                                    
                                    <p class="mb-1 small">
                                        <i class="fas fa-user"></i> <?php echo $pedido['cliente_nombre']; ?>
                                    </p>
                                    
                                    <p class="mb-1 small">
                                        <i class="fas fa-motorcycle"></i> 
                                        <?php echo $pedido['delivery_nombre'] ?? 'Sin asignar'; ?>
                                    </p>
                                    
                                    <p class="mb-1 small text-muted">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?php echo substr($pedido['direccion_entrega'], 0, 40) . '...'; ?>
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> 
                                            <?php echo date('H:i', strtotime($pedido['fecha_pedido'])); ?>
                                        </small>
                                        <strong class="text-primary">
                                            S/ <?php echo number_format($pedido['total'], 2); ?>
                                        </strong>
                                    </div>
                                    
                                    <?php if ($pedido['ultima_actualizacion']): ?>
                                        <small class="text-success">
                                            <i class="fas fa-satellite-dish"></i> 
                                            Última actualización: <?php echo date('H:i:s', strtotime($pedido['ultima_actualizacion'])); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Mapa -->
            <div class="col-md-8">
                <div class="position-relative">
                    <div class="auto-refresh">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="autoRefresh" checked>
                            <label class="form-check-label" for="autoRefresh">
                                <i class="fas fa-sync-alt"></i> Auto-actualizar (10s)
                            </label>
                        </div>
                    </div>
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Datos de pedidos desde PHP
        const pedidosData = <?php echo json_encode($pedidos); ?>;
        
        // Inicializar mapa centrado en Lima, Perú
        const map = L.map('map').setView([-12.0464, -77.0428], 13);
        
        // Agregar capa de OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        
        // Almacenar marcadores
        const markers = {};
        const destinationMarkers = {};
        const polylines = {};
        
        // Función para crear icono de delivery
        function createDeliveryIcon() {
            return L.divIcon({
                className: 'delivery-marker',
                html: '<i class="fas fa-motorcycle" style="color: white; font-size: 14px;"></i>',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });
        }
        
        // Función para crear icono de destino
        function createDestinationIcon() {
            return L.divIcon({
                className: 'destination-marker',
                html: '<i class="fas fa-home" style="color: white; font-size: 12px;"></i>',
                iconSize: [25, 25],
                iconAnchor: [12, 12]
            });
        }
        
        // Agregar marcadores al mapa
        function addMarkers() {
            pedidosData.forEach(pedido => {
                const deliveryLat = parseFloat(pedido.ultima_latitud || pedido.latitud_entrega);
                const deliveryLng = parseFloat(pedido.ultima_longitud || pedido.longitud_entrega);
                const destLat = parseFloat(pedido.latitud_entrega);
                const destLng = parseFloat(pedido.longitud_entrega);
                
                if (!isNaN(deliveryLat) && !isNaN(deliveryLng)) {
                    // Marcador de delivery
                    const deliveryMarker = L.marker([deliveryLat, deliveryLng], {
                        icon: createDeliveryIcon()
                    }).addTo(map);
                    
                    deliveryMarker.bindPopup(`
                        <div style="min-width: 200px;">
                            <h6><i class="fas fa-motorcycle"></i> ${pedido.nro_pedido}</h6>
                            <p class="mb-1"><strong>Cliente:</strong> ${pedido.cliente_nombre}</p>
                            <p class="mb-1"><strong>Delivery:</strong> ${pedido.delivery_nombre || 'Sin asignar'}</p>
                            <p class="mb-1"><strong>Estado:</strong> <span class="badge" style="background-color: ${pedido.estado_color}">${pedido.nombre_estado}</span></p>
                            <p class="mb-0"><strong>Total:</strong> S/ ${parseFloat(pedido.total).toFixed(2)}</p>
                        </div>
                    `);
                    
                    markers[pedido.id_pedido] = deliveryMarker;
                    
                    // Marcador de destino
                    if (!isNaN(destLat) && !isNaN(destLng)) {
                        const destMarker = L.marker([destLat, destLng], {
                            icon: createDestinationIcon()
                        }).addTo(map);
                        
                        destMarker.bindPopup(`
                            <div>
                                <h6><i class="fas fa-map-marker-alt"></i> Destino</h6>
                                <p class="mb-0">${pedido.direccion_entrega}</p>
                            </div>
                        `);
                        
                        destinationMarkers[pedido.id_pedido] = destMarker;
                        
                        // Línea de ruta
                        const polyline = L.polyline([
                            [deliveryLat, deliveryLng],
                            [destLat, destLng]
                        ], {
                            color: pedido.estado_color,
                            weight: 3,
                            opacity: 0.6,
                            dashArray: '10, 10'
                        }).addTo(map);
                        
                        polylines[pedido.id_pedido] = polyline;
                    }
                }
            });
            
            // Ajustar vista del mapa para mostrar todos los marcadores
            if (Object.keys(markers).length > 0) {
                const group = L.featureGroup(Object.values(markers));
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }
        
        // Agregar marcadores iniciales
        addMarkers();
        
        // Click en tarjeta de pedido
        document.querySelectorAll('.pedido-card').forEach(card => {
            card.addEventListener('click', function() {
                const pedidoId = this.dataset.pedidoId;
                const lat = parseFloat(this.dataset.lat);
                const lng = parseFloat(this.dataset.lng);
                
                // Remover clase active de todas las tarjetas
                document.querySelectorAll('.pedido-card').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                
                // Centrar mapa y abrir popup
                if (!isNaN(lat) && !isNaN(lng)) {
                    map.setView([lat, lng], 16);
                    if (markers[pedidoId]) {
                        markers[pedidoId].openPopup();
                    }
                }
            });
        });
        
        // Auto-refresh
        let refreshInterval;
        const autoRefreshCheckbox = document.getElementById('autoRefresh');
        
        function startAutoRefresh() {
            refreshInterval = setInterval(() => {
                location.reload();
            }, 10000); // 10 segundos
        }
        
        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }
        
        autoRefreshCheckbox.addEventListener('change', function() {
            if (this.checked) {
                startAutoRefresh();
            } else {
                stopAutoRefresh();
            }
        });
        
        // Iniciar auto-refresh si está marcado
        if (autoRefreshCheckbox.checked) {
            startAutoRefresh();
        }
    </script>
</body>
</html>
