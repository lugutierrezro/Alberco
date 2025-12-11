<?php include '../includes/header.php'; ?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

<style>
    /* Estilos para marcadores personalizados Leaflet */
    .custom-icon {
        text-align: center;
        color: white;
        font-weight: bold;
    }
    .custom-icon img {
        width: 100%;
        height: auto;
        filter: drop-shadow(0 3px 5px rgba(0,0,0,0.5));
    }
</style>

<div class="container my-5">
    <h2 class="mb-4 text-center"><i class="fas fa-map-marked-alt"></i> Seguimiento de Pedido</h2>

    <!-- Formulario de búsqueda -->
    <form id="formSeguimiento" class="mb-4">
        <div class="mb-3">
            <label for="nroPedido" class="form-label">Ingrese su Número de Pedido:</label>
            <input 
                type="text" 
                class="form-control" 
                id="nroPedido" 
                name="nroPedido" 
                placeholder="Ejemplo: PED-2025-000123" 
                required
                style="text-transform: uppercase;"
            >
            <small class="form-text text-muted">
                <i class="fas fa-info-circle"></i> Ingrese el código que aparece en su comprobante
            </small>
        </div>
        <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-search"></i> Ver Seguimiento
        </button>
    </form>

    <!-- Resultado del pedido -->
    <div id="resultadoSeguimiento" class="mt-4"></div>

    <!-- Mapa de ubicación (Leaflet) -->
    <div id="mapContainer" class="mt-4" style="display: none;">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-location-arrow"></i> Ubicación en Tiempo Real</h5>
                <span class="badge bg-white text-primary rounded-pill px-3">En vivo</span>
            </div>
            <div class="card-body p-0 position-relative">
                <div id="map" style="height: 500px; width: 100%; z-index: 1;"></div>
                
                <!-- Overlay de estado -->
                <div id="statusOverlay" style="position: absolute; top: 10px; right: 10px; z-index: 1000; background: rgba(255,255,255,0.9); padding: 10px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 250px; display: none;">
                    <h6 class="mb-1 fw-bold text-success"><i class="fas fa-satellite-dish animate__animated animate__pulse animate__infinite"></i> GPS Activo</h6>
                    <small class="text-muted" id="statusText">Actualizando ubicación...</small>
                </div>
            </div>
            <div class="card-footer text-muted bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <small><i class="fas fa-sync-alt fa-spin text-primary"></i> Actualización automática</small>
                    <small>Mapa cortesía de OpenStreetMap</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
let map = null;
let deliveryMarker = null;
let destinationMarker = null;
let routeLine = null;
let refreshInterval = null;
let currentPedidoId = null;

// Coordenadas del Restaurante (Fallback)
const RESTAURANT_COORDS = [-12.046374, -77.042793]; // Lima Default

// Iconos personalizados
const deliveryIcon = L.icon({
    iconUrl: 'https://cdn-icons-png.flaticon.com/512/7541/7541900.png',
    iconSize: [50, 50],
    iconAnchor: [25, 25],
    popupAnchor: [0, -25]
});

const destIcon = L.icon({
    iconUrl: 'https://cdn-icons-png.flaticon.com/512/1483/1483336.png',
    iconSize: [40, 40],
    iconAnchor: [20, 20],
    popupAnchor: [0, -20]
});

const restaurantIcon = L.icon({
    iconUrl: 'https://cdn-icons-png.flaticon.com/512/4287/4287725.png',
    iconSize: [40, 40],
    iconAnchor: [20, 20],
    popupAnchor: [0, -20]
});

// Inicializar mapa
function initMap(deliveryLat, deliveryLng, destLat, destLng, pedidoInfo) {
    document.getElementById('mapContainer').style.display = 'block';
    
    // Determinar puntos de inicio y fin
    const hasDeliveryCoords = (deliveryLat && deliveryLng);
    
    const startPos = hasDeliveryCoords
        ? [deliveryLat, deliveryLng] 
        : RESTAURANT_COORDS;
        
    const destPos = (destLat && destLng) 
        ? [destLat, destLng] 
        : [startPos[0] + 0.01, startPos[1] + 0.01];

    if (!map) {
        // Crear mapa si no existe
        map = L.map('map').setView(startPos, 13);
        
        // Capa de OpenStreetMap (Gratis y confiable)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
    }
    
    // Configurar marcador de Inicio (Moto o Restaurante)
    const isRestaurant = !hasDeliveryCoords;
    const startTitle = isRestaurant ? "Restaurante Alberco" : ("Repartidor: " + pedidoInfo.repartidor);
    const startIconMarker = isRestaurant ? restaurantIcon : deliveryIcon;
    const startPopup = `
        <div style="text-align: center;">
            <h6 class="mb-1 fw-bold">${isRestaurant ? '<i class="fas fa-store"></i> Restaurante' : '<i class="fas fa-motorcycle"></i> Repartidor'}</h6>
            <p class="mb-1">${startTitle}</p>
            <span class="badge ${isRestaurant ? 'bg-warning' : 'bg-success'} text-white">
                ${isRestaurant ? 'Preparando pedido' : 'En camino'}
            </span>
        </div>
    `;

    if (deliveryMarker) {
        deliveryMarker.setLatLng(startPos);
        deliveryMarker.setIcon(startIconMarker);
        deliveryMarker.setPopupContent(startPopup);
    } else {
        deliveryMarker = L.marker(startPos, { icon: startIconMarker })
            .addTo(map)
            .bindPopup(startPopup);
            
        if (!isRestaurant) {
            deliveryMarker.openPopup();
        }
    }

    // Configurar marcador de Destino
    const destPopup = `
        <div style="text-align: center;">
            <h6 class="mb-1 fw-bold"><i class="fas fa-map-marker-alt"></i> Destino</h6>
            <p class="mb-0 small">${pedidoInfo.direccion_entrega}</p>
        </div>
    `;

    if (destinationMarker) {
        destinationMarker.setLatLng(destPos);
        destinationMarker.setPopupContent(destPopup);
    } else {
        destinationMarker = L.marker(destPos, { icon: destIcon })
            .addTo(map)
            .bindPopup(destPopup);
    }

    // Línea de ruta (Polyline)
    const latlngs = [startPos, destPos];

    if (routeLine) {
        routeLine.setLatLngs(latlngs);
    } else {
        routeLine = L.polyline(latlngs, {
            color: '#d32f2f',
            weight: 5,
            opacity: 0.8,
            dashArray: '10, 10',
            lineCap: 'round'
        }).addTo(map);
    }

    // Ajustar vista para mostrar ambos puntos
    const bounds = L.latLngBounds(latlngs);
    map.fitBounds(bounds, { padding: [50, 50] });

    // Calcular distancia y mostrar info
    const distance = map.distance(startPos, destPos) / 1000; // km
    const estimatedTime = Math.ceil(distance / 0.5); // ~30km/h

    const infoDivId = 'distance-info-div';
    let infoDiv = document.getElementById(infoDivId);
    
    if (!infoDiv) {
        infoDiv = document.createElement('div');
        infoDiv.id = infoDivId;
        infoDiv.className = 'alert alert-light border mt-3 shadow-sm d-flex align-items-center justify-content-between animate__animated animate__fadeInUp';
        document.getElementById('mapContainer').appendChild(infoDiv);
    }
    
    infoDiv.innerHTML = `
        <div>
            <i class="fas fa-route text-primary fa-lg me-2"></i>
            <span>Distancia: <strong>${distance.toFixed(2)} km</strong></span>
        </div>
        <div>
            <i class="fas fa-stopwatch text-danger fa-lg me-2"></i>
            <span>Llegada aprox: <strong>${estimatedTime} min</strong></span>
        </div>
    `;
    
    // Actualizar overlay de estado
    const statusOverlay = document.getElementById('statusOverlay');
    const statusText = document.getElementById('statusText');
    
    statusOverlay.style.display = 'block';
    if(isRestaurant) {
        statusOverlay.innerHTML = '<h6 class="mb-0 text-warning"><i class="fas fa-store"></i> En restaurante</h6>';
    } else {
        statusOverlay.innerHTML = '<h6 class="mb-1 fw-bold text-success"><i class="fas fa-satellite-dish fa-spin"></i> GPS Activo</h6><small>Rastreando en vivo</small>';
    }
}

// Función para cargar datos del pedido
async function cargarSeguimiento(nroPedido) {
    try {
        const response = await fetch(`seguimiento_api.php?nroPedido=${encodeURIComponent(nroPedido)}`);
        const data = await response.json();
        
        if (!data.success) {
            document.getElementById('resultadoSeguimiento').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle"></i> ${data.message}
                </div>
            `;
            // NO OCULTAR EL MAPA. Mostrarlo por defecto.
            document.getElementById('mapContainer').style.display = 'block';
            if(!map) {
                map = L.map('map').setView(RESTAURANT_COORDS, 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                L.marker(RESTAURANT_COORDS).addTo(map).bindPopup("Restaurante Alberco (Vista por defecto)").openPopup();
            }
            return;
        }

        // --- RENDERIZADO DE RESULTADOS (HTML) ---
        let estadoBadge = 'bg-info';
        if (data.pedido.estado === 'Entregado') estadoBadge = 'bg-success';
        if (data.pedido.estado.toLowerCase().includes('camino')) estadoBadge = 'bg-warning text-dark';

        let html = `
            <div class="card shadow-sm border-0 animate__animated animate__fadeIn">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #d32f2f 0%, #ff5252 100%); color: white;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-receipt"></i> Pedido ${data.pedido.nro_pedido || nroPedido}</h5>
                        <span class="badge bg-white text-danger">${data.pedido.fecha_pedido}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center mb-4">
                        <div class="col-md-8">
                             <h4 class="mb-1 text-dark">
                                Estado: <span class="badge ${estadoBadge}">${data.pedido.estado}</span>
                             </h4>
                             <p class="text-muted mb-0"><i class="fas fa-motorcycle"></i> Repartidor: <strong>${data.pedido.repartidor}</strong></p>
                        </div>
                        <div class="col-md-4 text-end">
                             <h3 class="text-success fw-bold">${data.pedido.total}</h3>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded border h-100">
                                <small class="text-muted d-block uppercase"><i class="fas fa-map-marker-alt text-danger"></i> Dirección de Entrega</small>
                                <strong class="d-block mt-1">${data.pedido.direccion_entrega}</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded border h-100">
                                <small class="text-muted d-block uppercase"><i class="fas fa-user text-primary"></i> Cliente</small>
                                <strong class="d-block mt-1">${data.pedido.cliente_nombre || 'Cliente'}</strong>
                            </div>
                        </div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 text-secondary">
                        <i class="fas fa-history"></i> Historial de Estados
                    </h6>
                    <div class="timeline">
        `;

        data.seguimiento.forEach((s, index) => {
            if(s.estado === 'Posición Actual') return; // Ocultar tracking raw del timeline

            const isLast = index === data.seguimiento.length - 1;
            html += `
                <div class="timeline-item ${isLast ? 'active' : ''}">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold ${isLast ? 'text-primary' : ''}">${s.estado}</span>
                            <small class="text-muted">${new Date(s.fecha_estado).toLocaleTimeString('es-PE', {hour: '2-digit', minute:'2-digit'})}</small>
                        </div>
                        ${s.descripcion ? `<small class="text-muted d-block mt-1">${s.descripcion}</small>` : ''}
                    </div>
                </div>
            `;
        });

        html += `
                    </div>
                </div>
            </div>
        `;

        document.getElementById('resultadoSeguimiento').innerHTML = html;

        // --- LÓGICA MAPA (LEAFLET) ---
        
        let destLat = parseFloat(data.pedido.latitud_entrega);
        let destLng = parseFloat(data.pedido.longitud_entrega);
        
        // Corrección signo Perú (si es positivo, invertir)
        if (destLat > 0) destLat = -destLat;
        if (destLng > 0) destLng = -destLng;
        
        // Obtener coords delivery
        let deliveryLat = null, deliveryLng = null;
        if (data.tracking) {
            deliveryLat = parseFloat(data.tracking.latitud);
            deliveryLng = parseFloat(data.tracking.longitud);
        } else {
            // Fallback historial
            const ultimoConUbicacion = [...data.seguimiento].reverse().find(s => s.ubicacion_actual);
            if (ultimoConUbicacion) {
                [deliveryLat, deliveryLng] = ultimoConUbicacion.ubicacion_actual.split(',').map(parseFloat);
            }
        }
        
        if (deliveryLat > 0) deliveryLat = -deliveryLat;
        if (deliveryLng > 0) deliveryLng = -deliveryLng;

        // Mostrar mapa si hay destino válido
        if (isNaN(destLat) || isNaN(destLng) || destLat === 0 || destLng === 0) {
             // FALLBACK CRITICO: Si no hay coordenadas, usar Restaurante para mostrar ALGO
             console.warn("No hay coordenadas de destino. Usando fallback.");
             destLat = RESTAURANT_COORDS[0] + 0.005; // Un poco desplazado
             destLng = RESTAURANT_COORDS[1] + 0.005;
             
             // Avisar en el UI
             const statusText = document.getElementById('statusText');
             if(statusText) statusText.innerText = "Ubicación aproximada (Dirección no geolocalizada)";
        }
        
        // FORZAR VISIBILIDAD SIEMPRE
        document.getElementById('mapContainer').style.display = 'block';
        
        // Renderizar mapa
        initMap(deliveryLat, deliveryLng, destLat, destLng, data.pedido);

    } catch (error) {
        console.error('Error:', error);
        // Aun con error, intentemos mostrar el mapa vacio si es posible
        document.getElementById('mapContainer').style.display = 'block';
        if(!map) {
             map = L.map('map').setView(RESTAURANT_COORDS, 13);
             L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        }
        
        document.getElementById('resultadoSeguimiento').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> 
                Ocurrió un error (ver consola), pero aquí está el mapa base.
            </div>
        `;
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("formSeguimiento");
    const resultado = document.getElementById("resultadoSeguimiento");

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const nroPedido = document.getElementById("nroPedido").value.trim().toUpperCase();

        if (!nroPedido) {
            resultado.innerHTML = `<div class="alert alert-warning">Ingrese un número válido.</div>`;
            return;
        }

        currentPedidoId = nroPedido;

        resultado.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-grow text-danger" role="status"></div>
                <p class="mt-3 text-muted">Buscando su pedido...</p>
            </div>
        `;

        await cargarSeguimiento(nroPedido);

        // Auto-refresh
        if (refreshInterval) clearInterval(refreshInterval);
        refreshInterval = setInterval(() => {
            if (currentPedidoId) cargarSeguimiento(currentPedidoId);
        }, 8000); // 8 segundos
    });
});
</script>

<style>
/* Estilos Timeline */
.timeline { position: relative; padding-left: 30px; margin-top: 15px; }
.timeline-item { position: relative; padding-bottom: 20px; }
.timeline-item:last-child { padding-bottom: 0; }
.timeline-marker { position: absolute; left: -30px; top: 0; width: 12px; height: 12px; border-radius: 50%; background: #ccc; border: 2px solid #fff; box-shadow: 0 0 0 2px #ccc; }
.timeline-item.active .timeline-marker { background: #d32f2f; box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.3); }
.timeline::before { content: ''; position: absolute; left: -25px; top: 5px; height: 100%; width: 2px; background: #eee; }
.timeline-content { background: #fff; padding: 10px 15px; border-radius: 8px; border: 1px solid #eee; transition: transform 0.2s; }
.timeline-content:hover { transform: translateX(5px); border-color: #d32f2f; }
</style>

<?php include '../includes/footer.php'; ?>
