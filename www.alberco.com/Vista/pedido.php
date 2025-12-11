<?php
$pageTitle = "Realizar Pedido - ALBERCO";
require_once(__DIR__ . "/../app/init.php");
require_once(__DIR__ . "/../Services/auth_cliente.php");

$auth = getAuthCliente();
if (!$auth->estaLogueado()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login_cliente.php?mensaje=debes_iniciar_sesion");
    exit;
}

$clienteActual = $auth->getClienteActual();
$clienteModel = new Cliente();
$datosCliente = $clienteModel->getById($clienteActual['id']);


include '../includes/header.php';
?>

<!-- CSS de Leaflet (Mapas) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

<style>
/* Pedido Page Modern */
.pedido-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--space-2xl) var(--space-lg);
}

/* Progress Stepper */
.stepper-modern {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: var(--space-2xl);
    position: relative;
}

.stepper-modern::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 25%;
    right: 25%;
    height: 3px;
    background: var(--light-90);
    z-index: 0;
}

.stepper-step {
    position: relative;
    z-index: 1;
    text-align: center;
}

.stepper-circle {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-full);
    background: var(--light-90);
    color: var(--dark-60);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--space-xs);
    font-weight: 700;
    transition: all var(--transition-base);
}

.stepper-step.active .stepper-circle {
    background: var(--gradient-primary);
    color: var(--light);
    box-shadow: var(--shadow-glow);
    transform: scale(1.2);
}

.stepper-label {
    font-size: var(--text-sm);
    color: var(--dark-60);
    font-weight: 600;
}

.stepper-step.active .stepper-label {
    color: var(--primary);
}

/* Cart Items */
.cart-modern {
    background: var(--light);
    border-radius: var(--radius-xl);
    padding: var(--space-lg);
    box-shadow: var(--shadow-md);
    margin-bottom: var(--space-lg);
}

.cart-item-modern {
    display: flex;
    gap: var(--space-md);
    padding: var(--space-md);
    border-radius: var(--radius-lg);
    margin-bottom: var(--space-sm);
    transition: all var(--transition-fast);
    background: var(--light-95);
}

.cart-item-modern:hover {
    background: var(--light-90);
}

.cart-item-image {
    width: 80px;
    height: 80px;
    border-radius: var(--radius-md);
    object-fit: cover;
}

.cart-item-info {
    flex: 1;
}

.cart-item-name {
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 0.25rem;
}

.cart-item-price {
    color: var(--primary);
    font-weight: 700;
    font-size: var(--text-lg);
}

/* Order Form */
.order-form-modern {
    background: var(--light);
    border-radius: var(--radius-xl);
    padding: var(--space-xl);
    box-shadow: var(--shadow-md);
}

.form-section-title {
    font-family: var(--font-display);
    font-size: var(--text-xl);
    font-weight: 700;
    margin-bottom: var(--space-md);
    color: var(--dark);
}

/* Radio Cards */
.radio-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: var(--space-md);
    margin-bottom: var(--space-lg);
}

.radio-card {
    position: relative;
}

.radio-card input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.radio-card-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: var(--space-lg);
    border: 2px solid var(--light-90);
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: all var(--transition-base);
    background: var(--light-95);
    min-height: 140px;
}

.radio-card input:checked + .radio-card-label {
    border-color: var(--primary);
    background: linear-gradient(135deg, rgba(255,61,0,0.05) 0%, rgba(255,193,7,0.05) 100%);
    box-shadow: 0 0 0 3px rgba(255,61,0,0.1);
}

.radio-card-icon {
    font-size: 2.5rem;
    margin-bottom: var(--space-sm);
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.radio-card-title {
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.radio-card-subtitle {
    font-size: var(--text-sm);
    color: var(--dark-60);
}

/* Summary Card */
.summary-card {
    background: var(--gradient-dark);
    color: var(--light);
    border-radius: var(--radius-xl);
    padding: var(--space-xl);
    box-shadow: var(--shadow-xl);
    position: sticky;
    top: 100px;
}

.summary-title {
    font-family: var(--font-display);
    font-size: var(--text-2xl);
    font-weight: 700;
    margin-bottom: var(--space-lg);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: var(--space-sm) 0;
    color: var(--light-80);
}

.summary-total {
    border-top: 2px solid rgba(255,255,255,0.2);
    padding-top: var(--space-md);
    margin-top: var(--space-md);
}

.summary-total .summary-row {
    font-size: var(--text-xl);
    font-weight: 700;
    color: var(--light);
}

/* Input Modern */
.input-modern {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--light-90);
    border-radius: var(--radius-md);
    font-size: var(--text-base);
    transition: all var(--transition-fast);
    background: var(--light-95);
}

.input-modern:focus {
    outline: none;
    border-color: var(--primary);
    background: var(--light);
}

.select-modern {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--light-90);
    border-radius: var(--radius-md);
    font-size: var(--text-base);
    background: var(--light-95);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.select-modern:focus {
    outline: none;
    border-color: var(--primary);
    background: var(--light);
}

.textarea-modern {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--light-90);
    border-radius: var(--radius-md);
    font-size: var(--text-base);
    resize: vertical;
    min-height: 100px;
    font-family: inherit;
    transition: all var(--transition-fast);
    background: var(--light-95);
}

.textarea-modern:focus {
    outline: none;
    border-color: var(--primary);
    background: var(--light);
}

@media (max-width: 768px) {
    .summary-card {
        position: static;
        margin-top: var(--space-xl);
    }
}

/* Mapa Leaflet */
#mapaPedido {
    height: 350px;
    width: 100%;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    z-index: 1;
    margin-top: var(--space-md);
}
</style>

<div class="pedido-container">
    <!-- Stepper -->
    <div class="stepper-modern" data-aos="fade-down">
        <div class="stepper-step active">
            <div class="stepper-circle">1</div>
            <div class="stepper-label">Carrito</div>
        </div>
        <div class="stepper-step">
            <div class="stepper-circle">2</div>
            <div class="stepper-label">Detalles</div>
        </div>
        <div class="stepper-step">
            <div class="stepper-circle">3</div>
            <div class="stepper-label">Confirmación</div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column - Cart & Form -->
        <div class="col-lg-8">
            <!-- Cart -->
            <div class="cart-modern" data-aos="fade-right">
                <h3 class="form-section-title">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Tu Carrito
                </h3>
                <div id="carritoContainer"></div>
                
                <button class="btn-modern btn-outline mt-3" id="vaciarCarrito">
                    <i class="fas fa-trash me-2"></i>
                    Vaciar Carrito
                </button>
            </div>

            <!-- Order Form -->
            <div class="order-form-modern" data-aos="fade-right" data-aos-delay="100">
                <form id="formPedido">
                    <!-- Tipo de Pedido -->
                    <h3 class="form-section-title">
                        <i class="fas fa-concierge-bell me-2"></i>
                        ¿Cómo quieres tu pedido?
                    </h3>
                    <div class="radio-cards">
                        <div class="radio-card">
                            <input type="radio" name="tipoPedido" id="tipo_delivery" value="delivery" required>
                            <label for="tipo_delivery" class="radio-card-label">
                                <i class="fas fa-motorcycle radio-card-icon"></i>
                                <div class="radio-card-title">Delivery</div>
                                <div class="radio-card-subtitle">30-45 min</div>
                            </label>
                        </div>
                        <div class="radio-card">
                            <input type="radio" name="tipoPedido" id="tipo_llevar" value="para_llevar" required>
                            <label for="tipo_llevar" class="radio-card-label">
                                <i class="fas fa-shopping-bag radio-card-icon"></i>
                                <div class="radio-card-title">Para Llevar</div>
                                <div class="radio-card-subtitle">15-20 min</div>
                            </label>
                        </div>
                        <div class="radio-card">
                            <input type="radio" name="tipoPedido" id="tipo_mesa" value="mesa" required>
                            <label for="tipo_mesa" class="radio-card-label">
                                <i class="fas fa-utensils radio-card-icon"></i>
                                <div class="radio-card-title">En Local</div>
                                <div class="radio-card-subtitle">Elige mesa</div>
                            </label>
                        </div>
                    </div>

                    <!-- Dirección (for delivery) - CON MAPA -->
                    <div id="seccionDireccion" style="display: none;">
                        <h3 class="form-section-title mt-4">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Dirección de Entrega
                        </h3>
                        
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-map-marked-alt me-2"></i>Ubica tu dirección en el mapa</label>
                            
                            <!-- Buscador de direcciones -->
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="buscadorMapa" placeholder="Buscar dirección (ej: Av. Arequipa 1500, Lima)">
                                <button class="btn btn-outline-secondary" type="button" onclick="buscarEnMapa()">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                            
                            <div id="mapaPedido"></div>
                            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Arrastra el marcador, busca tu dirección o haz clic en el mapa.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-map-pin me-2"></i>Dirección Completa</label>
                            <textarea class="textarea-modern" id="direccionEntrega" rows="2" placeholder="Se llenará automáticamente con el mapa..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-building me-2"></i>Referencia</label>
                            <input type="text" class="input-modern" id="referenciaEntrega" placeholder="Ej: Casa azul, frente al parque">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-city me-2"></i>Distrito</label>
                            <input type="text" class="input-modern" id="distritoEntrega" placeholder="Se llenará con el mapa" required readonly>
                        </div>
                    </div>

                    <!-- Mesa (for local) -->
                    <div id="seccionMesa" style="display: none;">
                        <h3 class="form-section-title mt-4">
                            <i class="fas fa-chair me-2"></i>
                            Selecciona tu Mesa
                        </h3>
                        <select class="select-modern" id="numeroMesa">
                            <option value="">Selecciona...</option>
                            <?php 
                            $mesas = getMesas();
                            foreach($mesas as $mesa): 
                            ?>
                                <option value="<?= $mesa['id_mesa'] ?>">
                                    Mesa <?= $mesa['numero_mesa'] ?> (Cap: <?= $mesa['capacidad'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Método de Pago -->
                    <h3 class="form-section-title mt-4">
                        <i class="fas fa-credit-card me-2"></i>
                        Método de Pago
                    </h3>
                    <div class="radio-cards">
                        <div class="radio-card">
                            <input type="radio" name="metodoPago" id="pago_efectivo" value="efectivo" required>
                            <label for="pago_efectivo" class="radio-card-label">
                                <i class="fas fa-money-bill-wave radio-card-icon"></i>
                                <div class="radio-card-title">Efectivo</div>
                            </label>
                        </div>
                        <div class="radio-card">
                            <input type="radio" name="metodoPago" id="pago_yape" value="yape">
                            <label for="pago_yape" class="radio-card-label">
                                <i class="fas fa-mobile-alt radio-card-icon"></i>
                                <div class="radio-card-title">Yape</div>
                            </label>
                        </div>
                        <div class="radio-card">
                            <input type="radio" name="metodoPago" id="pago_tarjeta" value="tarjeta">
                            <label for="pago_tarjeta" class="radio-card-label">
                                <i class="fas fa-credit-card radio-card-icon"></i>
                                <div class="radio-card-title">Tarjeta</div>
                            </label>
                        </div>
                    </div>

                    <!-- Hidden Fields -->
                    <input type="hidden" id="nombreCliente" value="<?= htmlspecialchars($datosCliente['nombre']) ?>">
                    <input type="hidden" id="telefonoCliente" value="<?= htmlspecialchars($datosCliente['telefono']) ?>">

                    <!-- Observaciones -->
                    <h3 class="form-section-title mt-4">
                        <i class="fas fa-comment me-2"></i>
                        Observaciones (opcional)
                    </h3>
                    <textarea class="textarea-modern" id="observaciones" placeholder="Alguna indicación especial..."></textarea>

                    <!-- Buttons -->
                    <div class="mt-4 d-flex gap-3">
                        <button type="submit" class="btn-modern btn-primary flex-1">
                            <i class="fas fa-check-circle me-2"></i>
                            Confirmar Pedido
                        </button>
                        <a href="menu.php" class="btn-modern btn-outline flex-1">
                            <i class="fas fa-arrow-left me-2"></i>
                            Seguir Comprando
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column - Summary -->
        <div class="col-lg-4">
            <div class="summary-card" data-aos="fade-left">
                <h3 class="summary-title">Resumen del Pedido</h3>
                
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <strong id="subtotalResumen">S/ 0.00</strong>
                </div>
                <div class="summary-row">
                    <span>Delivery:</span>
                    <strong id="deliveryResumen">S/ 0.00</strong>
                </div>
                
                <div class="summary-total">
                    <div class="summary-row">
                        <span>Total:</span>
                        <strong id="totalResumen">S/ 0.00</strong>
                    </div>
                </div>

                <div class="mt-4 p-3" style="background: rgba(255,255,255,0.1); border-radius: var(--radius-md);">
                    <small style="color: var(--light-80);">
                        <i class="fas fa-info-circle me-2"></i>
                        Los precios incluyen IGV. El tiempo de entrega puede variar según la zona.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Configuración de URLs del servidor -->
<script src="./config.js.php"></script>

<!-- SweetAlert para mensajes -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Leaflet JS (Mapas) -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<!-- Scripts del sistema de pedidos -->
<script src="./js/carrito.js"></script>
<script src="./js/pedido.js"></script>

<script>
// Variables globales para el mapa
let mapaPedido, markerPedido;
const LIMA_COORDS = [-12.046374, -77.042793];

// Inicializar mapa cuando se selecciona delivery
function inicializarMapa() {
    if (mapaPedido) return; // Ya está inicializado
    
    // Esperar un momento para que el div sea visible
    setTimeout(() => {
        // Crear mapa centrado en Lima
        mapaPedido = L.map('mapaPedido').setView(LIMA_COORDS, 13);
        
        // Capa de OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(mapaPedido);
        
        // Crear marcador arrastrable
        markerPedido = L.marker(LIMA_COORDS, {
            draggable: true
        }).addTo(mapaPedido);
        
        // Evento al arrastrar el marcador
        markerPedido.on('dragend', function(e) {
            const position = markerPedido.getLatLng();
            obtenerDireccion(position.lat, position.lng);
        });
        
        // Evento al hacer clic en el mapa
        mapaPedido.on('click', function(e) {
            markerPedido.setLatLng(e.latlng);
            obtenerDireccion(e.latlng.lat, e.latlng.lng);
        });
        
        // Intentar geolocalización
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const newLatLng = new L.LatLng(lat, lng);
                    
                    mapaPedido.setView(newLatLng, 16);
                    markerPedido.setLatLng(newLatLng);
                    obtenerDireccion(lat, lng);
                },
                () => { console.log("Geolocalización no disponible"); }
            );
        }
    }, 100);

}

// Buscar dirección en el mapa
async function buscarEnMapa() {
    const query = document.getElementById('buscadorMapa').value;
    if (!query) return;
    
    // Agregar "Peru" para mejorar precisión
    const searchText = `${query}, Peru`;
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchText)}`;
    
    try {
        Swal.fire({
            title: 'Buscando...',
            text: 'Localizando dirección',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        const response = await fetch(url);
        const data = await response.json();
        
        Swal.close();
        
        if (data && data.length > 0) {
            const firstResult = data[0];
            const lat = parseFloat(firstResult.lat);
            const lon = parseFloat(firstResult.lon);
            const newLatLng = new L.LatLng(lat, lon);
            
            mapaPedido.setView(newLatLng, 17);
            markerPedido.setLatLng(newLatLng);
            obtenerDireccion(lat, lon);
        } else {
            Swal.fire('No encontrado', 'No pudimos encontrar esa dirección. Intenta ser más específico o mueve el pin manualmente.', 'warning');
        }
    } catch (error) {
        console.error('Error en búsqueda:', error);
        Swal.close();
        Swal.fire('Error', 'Ocurrió un error al buscar la dirección.', 'error');
    }
}

// Enter en el buscador
document.getElementById('buscadorMapa')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        buscarEnMapa();
    }
});

// Obtener dirección desde coordenadas (con proxy para evitar CORS)
async function obtenerDireccion(lat, lng) {
    // Usar proxy local en vez de Nominatim directo para evitar CORS
    const url = `proxy_geocoding.php?lat=${lat}&lon=${lng}`;
    
    try {
        document.getElementById('direccionEntrega').placeholder = "Buscando dirección...";
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.error) {
            console.error("Error del proxy:", data);
            document.getElementById('direccionEntrega').placeholder = "Mueve el marcador para actualizar";
            return;
        }
        
        if (data && data.address) {
            const direccion = data.display_name;
            const distrito = data.address.city_district || data.address.suburb || data.address.neighbourhood || 'Lima';
            
            document.getElementById('direccionEntrega').value = direccion;
            document.getElementById('distritoEntrega').value = distrito;
        }
    } catch (error) {
        console.error("Error obteniendo dirección:", error);
        document.getElementById('direccionEntrega').placeholder = "Mueve el marcador para actualizar";
    }
}

// Show/hide sections based on order type
document.querySelectorAll('input[name="tipoPedido"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const deliverySection = document.getElementById('seccionDireccion');
        const mesaSection = document.getElementById('seccionMesa');
        
        // Campos de delivery que tienen required
        const direccionField = document.getElementById('direccionEntrega');
        const distritoField = document.getElementById('distritoEntrega');
        
        if (this.value === 'delivery') {
            deliverySection.style.display = 'block';
            mesaSection.style.display = 'none';
            // Agregar required a campos de delivery
            if (direccionField) direccionField.setAttribute('required', 'required');
            if (distritoField) distritoField.setAttribute('required', 'required');
            inicializarMapa(); // Inicializar mapa cuando se muestra
        } else if (this.value === 'mesa') {
            mesaSection.style.display = 'block';
            deliverySection.style.display = 'none';
            // Quitar required de campos de delivery
            if (direccionField) direccionField.removeAttribute('required');
            if (distritoField) distritoField.removeAttribute('required');
        } else { // para_llevar
            deliverySection.style.display = 'none';
            mesaSection.style.display = 'none';
            // Quitar required de campos de delivery
            if (direccionField) direccionField.removeAttribute('required');
            if (distritoField) distritoField.removeAttribute('required');
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
