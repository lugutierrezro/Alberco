<?php
// Include init to check DB connection
require_once __DIR__ . '/../app/init.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agente de Diagnóstico - Google Maps</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .status-check { font-size: 1.2rem; margin-bottom: 10px; }
        .status-ok { color: green; font-weight: bold; }
        .status-fail { color: red; font-weight: bold; }
        .status-pending { color: orange; }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1 class="mb-4 text-center"><i class="fas fa-user-md"></i> Agente de Diagnóstico Alberco</h1>
        <p class="text-center text-muted">Verificación automática de componentes del mapa y seguimiento</p>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                Resultados del Diagnóstico
            </div>
            <div class="card-body" id="diagnosticResults">
                <div class="status-check" id="checkDB">
                    <i class="fas fa-database"></i> Conexión Base de Datos: <span class="status-pending">Verificando...</span>
                </div>
                <div class="status-check" id="checkAPI">
                    <i class="fas fa-code"></i> API de Seguimiento: <span class="status-pending">Pendiente...</span>
                </div>
                <div class="status-check" id="checkGMapLib">
                    <i class="fas fa-map"></i> Librería Google Maps: <span class="status-pending">Cargando...</span>
                </div>
                <div class="status-check" id="checkGMapAuth">
                    <i class="fas fa-key"></i> Autenticación API Key: <span class="status-pending">Esperando carga...</span>
                </div>
                <div class="status-check" id="checkRender">
                    <i class="fas fa-tv"></i> Renderizado de Mapa: <span class="status-pending">Pendiente...</span>
                </div>
            </div>
        </div>

        <!-- Área de prueba visual -->
        <div class="card mb-4">
            <div class="card-body">
                <h5>Prueba Visual de Mapa</h5>
                <div id="map" style="height: 300px; width: 100%; background: #eee;"></div>
            </div>
        </div>

        <!-- Log detallado -->
        <div class="card">
             <div class="card-header">Log Técnico</div>
             <div class="card-body bg-light">
                 <pre id="debugLog" style="max-height: 200px; overflow-y: auto; font-size: 0.8rem;"></pre>
             </div>
        </div>
    </div>

    <!-- Script de Diagnóstico -->
    <script>
        function log(msg) {
            const el = document.getElementById('debugLog');
            const time = new Date().toLocaleTimeString();
            el.innerHTML += `[${time}] ${msg}\n`;
            console.log(`[Diagnostic] ${msg}`);
        }

        function setStatus(id, status, msg) {
            const el = document.getElementById(id).querySelector('span');
            el.className = `status-${status}`;
            el.innerHTML = status === 'ok' ? `<i class="fas fa-check-circle"></i> ${msg}` : `<i class="fas fa-times-circle"></i> ${msg}`;
        }

        // 1. Verificar PHP/DB (Si esta página cargó, PHP sirve. Verificamos variable PHP inyectada)
        const dbStatus = "<?php echo isset($pdo) ? 'OK' : 'FAIL'; ?>";
        if(dbStatus === 'OK') {
            setStatus('checkDB', 'ok', 'Conexión establecida correctamente');
            log('DB Connection Check: SUCCESS');
        } else {
            setStatus('checkDB', 'fail', 'Variable $pdo no detectada en init.php');
            log('DB Connection Check: FAILED');
        }

        // 2. Verificar API Endpoint
        async function checkApi() {
            try {
                // Intentamos consultar un pedido inexistente para ver si responde JSON válido (aunque sea error 400/404)
                const response = await fetch('seguimiento_api.php?nroPedido=TEST-DIAGNOSTIC');
                const text = await response.text();
                log('API Raw Response: ' + text.substring(0, 50) + '...');
                
                try {
                    const json = JSON.parse(text);
                    setStatus('checkAPI', 'ok', 'Responde JSON correctamente');
                    log('API JSON Parse: SUCCESS');
                } catch(e) {
                    setStatus('checkAPI', 'fail', 'La API no retorna JSON válido');
                    log('API JSON Parse: FAILED - ' + e.message);
                }
            } catch(e) {
                setStatus('checkAPI', 'fail', 'Error de red al contactar API');
                log('API Network Error: ' + e.message);
            }
        }
        checkApi();

        // 3. Google Maps
        window.gm_authFailure = function() {
            setStatus('checkGMapAuth', 'fail', 'API Key rechazada por Google (Billing/Invalid)');
            log('Google Maps Auth Failure Triggered');
        };

        function onMapLoaded() {
            setStatus('checkGMapLib', 'ok', 'Script cargado');
            log('Google Maps Script Loaded');

            try {
                const center = { lat: -12.046374, lng: -77.042793 };
                const map = new google.maps.Map(document.getElementById("map"), {
                    zoom: 12,
                    center: center
                });
                
                new google.maps.Marker({
                    position: center,
                    map: map,
                    title: "Test Marker"
                });

                setStatus('checkRender', 'ok', 'Mapa dibujado correctamente');
                setStatus('checkGMapAuth', 'ok', 'API Key parece válida (sin errores inmediatos)');
                log('Map Draw: SUCCESS');

            } catch(e) {
                setStatus('checkRender', 'fail', 'Excepción al crear mapa: ' + e.message);
                log('Map Draw Error: ' + e.message);
            }
        }
    </script>
    
    <!-- Cargar API Google Maps -->
    <!-- Usamos la misma Key que en producción -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBlX0_GiA45HiIDcW_swW0SqFXd7ew3bhA&callback=onMapLoaded" async defer onerror="setStatus('checkGMapLib', 'fail', 'No se pudo cargar el script de Google (Bloqueo de red/Adblock)')"></script>
</body>
</html>
