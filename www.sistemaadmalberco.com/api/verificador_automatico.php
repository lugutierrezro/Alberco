<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✅ Verificador Automático de APIs - Alberco</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .header h1 {
            color: #667eea;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 1.1rem;
            margin-top: 10px;
        }
        
        .status-testing {
            background: #ffc107;
            color: #856404;
        }
        
        .status-success {
            background: #28a745;
            color: white;
        }
        
        .status-error {
            background: #dc3545;
            color: white;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            font-size: 2.5rem;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .stat-card.success h3 { color: #28a745; }
        .stat-card.error h3 { color: #dc3545; }
        .stat-card.pending h3 { color: #ffc107; }
        .stat-card.info h3 { color: #667eea; }
        
        .api-tests {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .api-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .api-card:hover {
            transform: translateY(-5px);
        }
        
        .api-header {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }
        
        .api-header h3 {
            color: #333;
            margin-bottom: 5px;
        }
        
        .api-header .method {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.75rem;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .method.get { background: #28a745; color: white; }
        .method.post { background: #007bff; color: white; }
        
        .api-body {
            padding: 20px;
        }
        
        .test-status {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .test-status.testing {
            background: #fff3cd;
            color: #856404;
        }
        
        .test-status.success {
            background: #d4edda;
            color: #155724;
        }
        
        .test-status.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .details {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 0.85rem;
        }
        
        .details strong {
            color: #667eea;
        }
        
        .progress-bar {
            width: 100%;
            height: 25px;
            background: #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.5s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .timestamp {
            text-align: center;
            color: #666;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        
        .refresh-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            margin: 20px auto;
            display: block;
            transition: all 0.3s;
        }
        
        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Verificador Automático de APIs</h1>
            <div class="status-badge status-testing" id="globalStatus">
                <span class="spinner" style="display: inline-block; vertical-align: middle;"></span>
                Verificando...
            </div>
        </div>
        
        <div class="progress-bar">
            <div class="progress-fill" id="progress" style="width: 0%">0%</div>
        </div>
        
        <div class="stats">
            <div class="stat-card info">
                <h3 id="totalTests">6</h3>
                <p>Total de APIs</p>
            </div>
            <div class="stat-card pending">
                <h3 id="pendingTests">6</h3>
                <p>Pendientes</p>
            </div>
            <div class="stat-card success">
                <h3 id="successTests">0</h3>
                <p>Exitosas</p>
            </div>
            <div class="stat-card error">
                <h3 id="failedTests">0</h3>
                <p>Fallidas</p>
            </div>
        </div>
        
        <div class="api-tests" id="apiTests">
            <!-- Se llenarán dinámicamente -->
        </div>
        
        <button class="refresh-btn" onclick="location.reload()">🔄 Volver a Verificar</button>
        
        <div class="timestamp" id="timestamp"></div>
    </div>
    
    <script>
        // Configuración de la ruta base - Auto-detecta entorno
        const isLocalhost = window.location.hostname === 'localhost' || 
                           window.location.hostname === '127.0.0.1';
        
        const BASE_URL = isLocalhost 
            ? window.location.origin + '/www.sistemaadmalberco.com/api'
            : 'https://allwiya.pe/www.sistemaadmalberco.com/api';
        
        const apis = [
            {
                name: '🔐 Login Empleado',
                method: 'POST',
                endpoint: '/auth/login_empleado.php',
                testData: {
                    email: 'test@test.com',
                    password: 'test123'
                },
                expectedSuccess: false, // No esperamos que sea exitoso con credenciales falsas
                description: 'Autenticación de empleados'
            },
            {
                name: '📋 Listar Pedidos',
                method: 'GET',
                endpoint: '/pedidos/listar_por_estado.php',
                testData: null,
                expectedSuccess: true,
                description: 'Lista todos los pedidos activos'
            },
            {
                name: '📋 Listar Pedidos (Cocina)',
                method: 'GET',
                endpoint: '/pedidos/listar_por_estado.php?tipo=cocina',
                testData: null,
                expectedSuccess: true,
                description: 'Lista pedidos para cocina'
            },
            {
                name: '🛵 Pedidos por Delivery',
                method: 'GET',
                endpoint: '/pedidos/por_delivery.php?empleado_id=1',
                testData: null,
                expectedSuccess: true,
                description: 'Pedidos asignados a delivery'
            },
            {
                name: '📄 Detalle de Pedido',
                method: 'GET',
                endpoint: '/pedidos/detalle.php?pedido_id=1',
                testData: null,
                expectedSuccess: null, // Puede o no existir el pedido 1
                description: 'Detalle completo de un pedido'
            },
            {
                name: '📍 Tracking GPS',
                method: 'POST',
                endpoint: '/tracking/actualizar_ubicacion.php',
                testData: {
                    pedido_id: 999,
                    empleado_id: 999,
                    latitud: -12.046374,
                    longitud: -77.042793
                },
                expectedSuccess: false, // No esperamos éxito con IDs falsos
                description: 'Actualizar ubicación de delivery'
            }
        ];
        
        let testsCompleted = 0;
        let testsPassed = 0;
        let testsFailed = 0;
        
        function updateProgress() {
            const progress = Math.round((testsCompleted / apis.length) * 100);
            document.getElementById('progress').style.width = progress + '%';
            document.getElementById('progress').textContent = progress + '%';
            
            document.getElementById('pendingTests').textContent = apis.length - testsCompleted;
            document.getElementById('successTests').textContent = testsPassed;
            document.getElementById('failedTests').textContent = testsFailed;
            
            if (testsCompleted === apis.length) {
                const globalStatus = document.getElementById('globalStatus');
                if (testsFailed === 0) {
                    globalStatus.className = 'status-badge status-success';
                    globalStatus.innerHTML = '✅ Todas las APIs Funcionan Correctamente';
                } else {
                    globalStatus.className = 'status-badge status-error';
                    globalStatus.innerHTML = '❌ ' + testsFailed + ' API(s) con Problemas';
                }
            }
        }
        
        async function testAPI(api, index) {
            const cardId = 'api-' + index;
            const card = document.getElementById(cardId);
            const statusDiv = card.querySelector('.test-status');
            const detailsDiv = card.querySelector('.details');
            
            const url = BASE_URL + api.endpoint;
            const startTime = performance.now();
            
            try {
                const options = {
                    method: api.method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                };
                
                if (api.method === 'POST' && api.testData) {
                    options.body = JSON.stringify(api.testData);
                }
                
                const response = await fetch(url, options);
                const duration = Math.round(performance.now() - startTime);
                const contentType = response.headers.get('content-type');
                
                let result;
                let isJSON = false;
                
                if (contentType && contentType.includes('application/json')) {
                    result = await response.json();
                    isJSON = true;
                } else {
                    result = await response.text();
                }
                
                // Evaluar si el test pasó
                let passed = false;
                let message = '';
                
                if (!isJSON) {
                    message = 'Error: No retorna JSON';
                    statusDiv.className = 'test-status error';
                    statusDiv.innerHTML = '❌ FALLÓ';
                    testsFailed++;
                } else if (response.status !== 200) {
                    message = `HTTP ${response.status}`;
                    statusDiv.className = 'test-status error';
                    statusDiv.innerHTML = '❌ FALLÓ';
                    testsFailed++;
                } else if (!result.hasOwnProperty('success')) {
                    message = 'Respuesta sin campo "success"';
                    statusDiv.className = 'test-status error';
                    statusDiv.innerHTML = '❌ FALLÓ';
                    testsFailed++;
                } else {
                    // API responde correctamente con JSON y campo success
                    passed = true;
                    message = result.mensaje || (result.success ? 'Exitoso' : 'Esperado');
                    statusDiv.className = 'test-status success';
                    statusDiv.innerHTML = '✅ FUNCIONANDO';
                    testsPassed++;
                }
                
                detailsDiv.innerHTML = `
                    <strong>Estado:</strong> HTTP ${response.status}<br>
                    <strong>Tiempo:</strong> ${duration}ms<br>
                    <strong>Respuesta:</strong> ${message}<br>
                    ${result.total !== undefined ? `<strong>Registros:</strong> ${result.total}<br>` : ''}
                `;
                
            } catch (error) {
                statusDiv.className = 'test-status error';
                statusDiv.innerHTML = '❌ ERROR';
                detailsDiv.innerHTML = `<strong>Error:</strong> ${error.message}`;
                testsFailed++;
            }
            
            testsCompleted++;
            updateProgress();
        }
        
        function createAPICards() {
            const container = document.getElementById('apiTests');
            
            apis.forEach((api, index) => {
                const card = document.createElement('div');
                card.className = 'api-card';
                card.id = 'api-' + index;
                
                card.innerHTML = `
                    <div class="api-header">
                        <h3>${api.name}</h3>
                        <span class="method ${api.method.toLowerCase()}">${api.method}</span>
                        <span style="font-size: 0.85rem; color: #666;">${api.endpoint}</span>
                    </div>
                    <div class="api-body">
                        <p style="color: #666; margin-bottom: 15px;">${api.description}</p>
                        <div class="test-status testing">
                            <div class="spinner"></div>
                            Probando...
                        </div>
                        <div class="details"></div>
                    </div>
                `;
                
                container.appendChild(card);
            });
        }
        
        async function runAllTests() {
            // Mostrar timestamp
            const now = new Date();
            document.getElementById('timestamp').textContent = 
                '🕐 Última verificación: ' + now.toLocaleString('es-PE');
            
            // Crear las tarjetas
            createAPICards();
            
            // Ejecutar tests con pequeños delays para mejor visualización
            for (let i = 0; i < apis.length; i++) {
                await new Promise(resolve => setTimeout(resolve, 300)); // 300ms entre tests
                await testAPI(apis[i], i);
            }
        }
        
        // Iniciar automáticamente al cargar la página
        window.addEventListener('load', () => {
            setTimeout(runAllTests, 500); // Pequeño delay para mejor UX
        });
    </script>
</body>
</html>
