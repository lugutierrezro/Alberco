<?php
require_once __DIR__ . '/../app/init.php';
require_once __DIR__ . '/../Services/auth_cliente.php';

$auth = getAuthCliente();

if ($auth->estaLogueado()) {
    header('Location: perfil_cliente.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'nombre'            => trim($_POST['nombre'] ?? ''),
        'apellidos'         => trim($_POST['apellidos'] ?? ''),
        'telefono'          => trim($_POST['telefono'] ?? ''),
        'email'             => trim($_POST['email'] ?? ''),
        'direccion'         => trim($_POST['direccion'] ?? ''),
        'tipo_documento'    => $_POST['tipo_documento'] ?? 'DNI',
        'numero_documento'  => trim($_POST['numero_documento'] ?? ''),
        'fecha_nacimiento'  => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
        'distrito'          => trim($_POST['distrito'] ?? ''),
        'ciudad'            => trim($_POST['ciudad'] ?? 'Lima'),
    ];

    // Validación básica
    if ($datos['nombre'] === '' || $datos['telefono'] === '' || $datos['direccion'] === '') {
        $error = 'Por favor completa los campos obligatorios.';
    } elseif (!preg_match('/^[0-9]{9}$/', $datos['telefono'])) {
        $error = 'El teléfono debe tener 9 dígitos numéricos.';
    } else {
        $resultado = $auth->registrar($datos);

        if (!empty($resultado['success'])) {
            header('Location: perfil_cliente.php');
            exit;
        }
        $error = $resultado['mensaje'] ?? 'Hubo un problema al registrar tu cuenta.';
    }
}

include __DIR__ . '/../includes/header.php';
?>

<!-- CSS de Leaflet (Mapas Gratis) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<link rel="stylesheet" href="css/cliente.css">

<style>
    #map {
        height: 400px;
        width: 100%;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 1; /* Importante para que no tape menús */
    }
</style>

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center" style="margin-top: 30px;">
                <div class="col-md-7">
                    <div class="card-custom fade-in">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-plus" style="font-size: 4rem; color: var(--primary-color);"></i>
                            <h2 class="mt-3" style="color: var(--dark-color);">Crear Cuenta</h2>
                            <p class="text-muted">Regístrate para disfrutar de todos los beneficios</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="formRegistro" novalidate>
                            <!-- ... (Campos del formulario igual que antes) ... -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-user"></i> Nombre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-user"></i> Apellidos</label>
                                        <input type="text" class="form-control" name="apellidos" value="<?= htmlspecialchars($_POST['apellidos'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-phone"></i> Teléfono <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="telefono" name="telefono" maxlength="9" required value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-envelope"></i> Email</label>
                                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Documentos y Ubicación (Simplificado para el ejemplo) -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Distrito</label>
                                        <input type="text" class="form-control" id="distrito" name="distrito" placeholder="Se llenará con el mapa">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Ciudad</label>
                                        <input type="text" class="form-control" id="ciudad" name="ciudad" value="Lima">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-map-marker-alt"></i> Dirección <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="direccion" name="direccion" rows="2" required placeholder="Mueve el pin en el mapa..."><?= htmlspecialchars($_POST['direccion'] ?? '') ?></textarea>
                            </div>

                            <!-- MAPA LEAFLET -->
                            <div class="form-group">
                                <label><i class="fas fa-map-marked-alt"></i> Ubica tu dirección exacta</label>
                                <div id="map"></div>
                                <small class="text-muted">Arrastra el marcador azul para ajustar tu ubicación.</small>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg btn-block mt-4">
                                <i class="fas fa-user-plus"></i> Crear mi Cuenta
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Scripts JS de Leaflet -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
// Configuración Inicial
const LIMA_COORDS = [-12.046374, -77.042793];
let map, marker;

// Inicializar Mapa
function initMap() {
    // Crear mapa centrado en Lima
    map = L.map('map').setView(LIMA_COORDS, 15);

    // Capa de OpenStreetMap (Gratis)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Crear Marcador Draggable (Arrastrable)
    marker = L.marker(LIMA_COORDS, {
        draggable: true
    }).addTo(map);

    // Evento: Al terminar de arrastrar el marcador
    marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        getAddressFromCoords(position.lat, position.lng);
    });

    // Evento: Al hacer clic en el mapa
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        getAddressFromCoords(e.latlng.lat, e.latlng.lng);
    });

    // Intentar geolocalización del navegador
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;
                const newLatLng = new L.LatLng(userLat, userLng);
                
                map.setView(newLatLng, 16);
                marker.setLatLng(newLatLng);
                getAddressFromCoords(userLat, userLng);
            },
            () => { console.log("Geolocalización no permitida o fallida."); }
        );
    }
}

// Función Geocoding Inversa (Coordenadas -> Dirección) usando Nominatim
async function getAddressFromCoords(lat, lng) {
    const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`;

    try {
        // Poner texto de carga
        document.getElementById('direccion').placeholder = "Buscando dirección...";
        
        const response = await fetch(url, {
            headers: { 'Accept-Language': 'es' } // Pedir resultados en español
        });
        const data = await response.json();

        if (data && data.address) {
            // Llenar campos del formulario
            const direccionCompleta = data.display_name; // O data.address.road + ' ' + data.address.house_number
            
            document.getElementById('direccion').value = direccionCompleta;
            
            // Intentar sacar distrito y ciudad
            const distrito = data.address.city_district || data.address.suburb || data.address.neighbourhood || '';
            const ciudad = data.address.city || data.address.town || data.address.state || 'Lima';

            document.getElementById('distrito').value = distrito;
            document.getElementById('ciudad').value = ciudad;
        }
    } catch (error) {
        console.error("Error obteniendo dirección:", error);
        document.getElementById('direccion').value = "Ubicación seleccionada en mapa (Manual)";
    }
}

// Iniciar todo al cargar la página
document.addEventListener('DOMContentLoaded', initMap);

// Validación de teléfono
const inputTel = document.getElementById('telefono');
if(inputTel){
    inputTel.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 9);
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
