<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧪 Debug API Móvil - Alberco</title>
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
            max-width: 1400px;
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
        
        .header p {
            color: #666;
            font-size: 1.1rem;
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
        }
        
        .stat-card h3 {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .endpoints-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }
        
        .endpoint-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .endpoint-header {
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .endpoint-header h2 {
            font-size: 1.3rem;
            margin-bottom: 5px;
        }
        
        .endpoint-header .method {
            display: inline-block;
            padding: 5px 10px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        
        .endpoint-body {
            padding: 20px;
        }
        
        .endpoint-body p {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .result {
            margin-top: 15px;
            padding: 15px;
            border-radius: 8px;
            display: none;
        }
        
        .result.success {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        
        .result.error {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }
        
        .result pre {
            margin-top: 10px;
            padding: 10px;
            background: rgba(0,0,0,0.1);
            border-radius: 5px;
            overflow-x: auto;
            font-size: 0.85rem;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 10px;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Debug API Móvil - Alberco</h1>
            <p>Panel de pruebas para endpoints de aplicación móvil</p>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <h3>6</h3>
                <p>Endpoints Totales</p>
            </div>
            <div class="stat-card">
                <h3 id="testsRun">0</h3>
                <p>Pruebas Ejecutadas</p>
            </div>
            <div class="stat-card">
                <h3 id="testsSuccess">0</h3>
                <p>Exitosas</p>
            </div>
            <div class="stat-card">
                <h3 id="testsFailed">0</h3>
                <p>Fallidas</p>
            </div>
        </div>
        
        <div class="endpoints-grid">
            <!-- 1. LOGIN EMPLEADO -->
            <div class="endpoint-card">
                <div class="endpoint-header">
                    <h2>🔐 Login Empleado</h2>
                    <span class="method">POST</span>
                    <p style="margin-top: 10px; font-size: 0.9rem;">/api/auth/login_empleado.php</p>
                </div>
                <div class="endpoint-body">
                    <p>Autenticación de empleados con email y contraseña</p>
                    <form id="form-login">
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" name="email" placeholder="empleado@alberco.com" required>
                        </div>
                        <div class="form-group">
                            <label>Password:</label>
                            <input type="password" name="password" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="btn">🚀 Probar Login</button>
                    </form>
                    <div class="loading"><div class="spinner"></div></div>
                    <div class="result"></div>
                </div>
            </div>
            
            <!-- 2. LISTAR POR ESTADO -->
            <div class="endpoint-card">
                <div class="endpoint-header">
                    <h2>📋 Listar por Estado</h2>
                    <span class="method">GET</span>
                    <p style="margin-top: 10px; font-size: 0.9rem;">/api/pedidos/listar_por_estado.php</p>
                </div>
                <div class="endpoint-body">
                    <p>Lista pedidos filtrados por estado o tipo</p>
                    <form id="form-listar-estado">
                        <div class="form-group">
                            <label>Tipo:</label>
                            <select name="tipo">
                                <option value="">Todos los pedidos activos</option>
                                <option value="cocina">Solo Cocina (Pendiente/Preparación/Listo)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Estado ID (opcional):</label>
                            <input type="number" name="estado_id" placeholder="1, 2, 3, etc.">
                        </div>
                        <button type="submit" class="btn">🔍 Buscar Pedidos</button>
                    </form>
                    <div class="loading"><div class="spinner"></div></div>
                    <div class="result"></div>
                </div>
            </div>
            
            <!-- 3. POR DELIVERY -->
            <div class="endpoint-card">
                <div class="endpoint-header">
                    <h2>🛵 Por Delivery</h2>
                    <span class="method">GET</span>
                    <p style="margin-top: 10px; font-size: 0.9rem;">/api/pedidos/por_delivery.php</p>
                </div>
                <div class="endpoint-body">
                    <p>Pedidos asignados a un delivery específico</p>
                    <form id="form-por-delivery">
                        <div class="form-group">
                            <label>ID Empleado Delivery:</label>
                            <input type="number" name="empleado_id" placeholder="Ej: 1" required>
                        </div>
                        <button type="submit" class="btn">📦 Ver Pedidos</button>
                    </form>
                    <div class="loading"><div class="spinner"></div></div>
                    <div class="result"></div>
                </div>
            </div>
            
            <!-- 4. DETALLE PEDIDO -->
            <div class="endpoint-card">
                <div class="endpoint-header">
                    <h2>📄 Detalle Pedido</h2>
                    <span class="method">GET</span>
                    <p style="margin-top: 10px; font-size: 0.9rem;">/api/pedidos/detalle.php</p>
                </div>
                <div class="endpoint-body">
                    <p>Información completa de un pedido (productos + tracking)</p>
                    <form id="form-detalle">
                        <div class="form-group">
                            <label>ID Pedido:</label>
                            <input type="number" name="pedido_id" placeholder="Ej: 1" required>
                        </div>
                        <button type="submit" class="btn">🔍 Ver Detalle</button>
                    </form>
                    <div class="loading"><div class="spinner"></div></div>
                    <div class="result"></div>
                </div>
            </div>
            
            <!-- 5. CAMBIAR ESTADO -->
            <div class="endpoint-card">
                <div class="endpoint-header">
                    <h2>🔄 Cambiar Estado</h2>
                    <span class="method">POST</span>
                    <p style="margin-top: 10px; font-size: 0.9rem;">/api/pedidos/cambiar_estado.php</p>
                </div>
                <div class="endpoint-body">
                    <p>Actualiza el estado de un pedido</p>
                    <form id="form-cambiar-estado">
                        <div class="form-group">
                            <label>ID Pedido:</label>
                            <input type="number" name="pedido_id" placeholder="Ej: 1" required>
                        </div>
                        <div class="form-group">
                            <label>Nuevo Estado:</label>
                            <select name="estado_id" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="1">1 - Pendiente</option>
                                <option value="2">2 - En Preparación</option>
                                <option value="3">3 - Listo</option>
                                <option value="4">4 - En Camino</option>
                                <option value="5">5 - Entregado</option>
                                <option value="6">6 - Cancelado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>ID Empleado (opcional):</label>
                            <input type="number" name="empleado_id" placeholder="Ej: 1">
                        </div>
                        <div class="form-group">
                            <label>Observaciones (opcional):</label>
                            <input type="text" name="observaciones" placeholder="Comentarios...">
                        </div>
                        <button type="submit" class="btn">✅ Actualizar Estado</button>
                    </form>
                    <div class="loading"><div class="spinner"></div></div>
                    <div class="result"></div>
                </div>
            </div>
            
            <!-- 6. ACTUALIZAR UBICACION -->
            <div class="endpoint-card">
                <div class="endpoint-header">
                    <h2>📍 Actualizar Ubicación</h2>
                    <span class="method">POST</span>
                    <p style="margin-top: 10px; font-size: 0.9rem;">/api/tracking/actualizar_ubicacion.php</p>
                </div>
                <div class="endpoint-body">
                    <p>Registra ubicación GPS del delivery en tiempo real</p>
                    <form id="form-ubicacion">
                        <div class="form-group">
                            <label>ID Pedido:</label>
                            <input type="number" name="pedido_id" placeholder="Ej: 1" required>
                        </div>
                        <div class="form-group">
                            <label>ID Empleado Delivery:</label>
                            <input type="number" name="empleado_id" placeholder="Ej: 1" required>
                        </div>
                        <div class="form-group">
                            <label>Latitud:</label>
                            <input type="text" name="latitud" placeholder="-12.046374" required>
                        </div>
                        <div class="form-group">
                            <label>Longitud:</label>
                            <input type="text" name="longitud" placeholder="-77.042793" required>
                        </div>
                        <div class="form-group">
                            <label>Observaciones (opcional):</label>
                            <input type="text" name="observaciones" placeholder="En ruta...">
                        </div>
                        <button type="submit" class="btn">📌 Registrar Ubicación</button>
                    </form>
                    <div class="loading"><div class="spinner"></div></div>
                    <div class="result"></div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Configuración de la ruta base - Auto-detecta entorno
        const isLocalhost = window.location.hostname === 'localhost' || 
                           window.location.hostname === '127.0.0.1';
        
        const BASE_URL = isLocalhost 
            ? window.location.origin + '/www.sistemaadmalberco.com/api'
            : 'https://allwiya.pe/www.sistemaadmalberco.com/api';
        
        let testsRun = 0;
        let testsSuccess = 0;
        let testsFailed = 0;
        
        function updateStats() {
            document.getElementById('testsRun').textContent = testsRun;
            document.getElementById('testsSuccess').textContent = testsSuccess;
            document.getElementById('testsFailed').textContent = testsFailed;
        }
        
        function showLoading(card, show) {
            const loading = card.querySelector('.loading');
            const btn = card.querySelector('.btn');
            if (show) {
                loading.style.display = 'block';
                btn.disabled = true;
            } else {
                loading.style.display = 'none';
                btn.disabled = false;
            }
        }
        
        function showResult(card, success, data) {
            const result = card.querySelector('.result');
            result.style.display = 'block';
            result.className = 'result ' + (success ? 'success' : 'error');
            
            const message = success ? '✅ Éxito' : '❌ Error';
            result.innerHTML = `<strong>${message}</strong><pre>${JSON.stringify(data, null, 2)}</pre>`;
            
            testsRun++;
            if (success) {
                testsSuccess++;
            } else {
                testsFailed++;
            }
            updateStats();
        }
        
        // 1. LOGIN EMPLEADO
        document.getElementById('form-login').addEventListener('submit', async function(e) {
            e.preventDefault();
            const card = this.closest('.endpoint-card');
            showLoading(card, true);
            
            const formData = new FormData(this);
            const data = {
                email: formData.get('email'),
                password: formData.get('password')
            };
            
            try {
                const response = await fetch(BASE_URL + '/auth/login_empleado.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                showResult(card, result.success, result);
            } catch (error) {
                showResult(card, false, {error: error.message});
            } finally {
                showLoading(card, false);
            }
        });
        
        // 2. LISTAR POR ESTADO
        document.getElementById('form-listar-estado').addEventListener('submit', async function(e) {
            e.preventDefault();
            const card = this.closest('.endpoint-card');
            showLoading(card, true);
            
            const formData = new FormData(this);
            let url = BASE_URL + '/pedidos/listar_por_estado.php?';
            if (formData.get('tipo')) url += 'tipo=' + formData.get('tipo') + '&';
            if (formData.get('estado_id')) url += 'estado_id=' + formData.get('estado_id');
            
            try {
                const response = await fetch(url);
                const result = await response.json();
                showResult(card, result.success, result);
            } catch (error) {
                showResult(card, false, {error: error.message});
            } finally {
                showLoading(card, false);
            }
        });
        
        // 3. POR DELIVERY
        document.getElementById('form-por-delivery').addEventListener('submit', async function(e) {
            e.preventDefault();
            const card = this.closest('.endpoint-card');
            showLoading(card, true);
            
            const formData = new FormData(this);
            const url = BASE_URL + '/pedidos/por_delivery.php?empleado_id=' + formData.get('empleado_id');
            
            try {
                const response = await fetch(url);
                const result = await response.json();
                showResult(card, result.success, result);
            } catch (error) {
                showResult(card, false, {error: error.message});
            } finally {
                showLoading(card, false);
            }
        });
        
        // 4. DETALLE PEDIDO
        document.getElementById('form-detalle').addEventListener('submit', async function(e) {
            e.preventDefault();
            const card = this.closest('.endpoint-card');
            showLoading(card, true);
            
            const formData = new FormData(this);
            const url = BASE_URL + '/pedidos/detalle.php?pedido_id=' + formData.get('pedido_id');
            
            try {
                const response = await fetch(url);
                const result = await response.json();
                showResult(card, result.success, result);
            } catch (error) {
                showResult(card, false, {error: error.message});
            } finally {
                showLoading(card, false);
            }
        });
        
        // 5. CAMBIAR ESTADO
        document.getElementById('form-cambiar-estado').addEventListener('submit', async function(e) {
            e.preventDefault();
            const card = this.closest('.endpoint-card');
            showLoading(card, true);
            
            const formData = new FormData(this);
            const data = {
                pedido_id: formData.get('pedido_id'),
                estado_id: formData.get('estado_id'),
                empleado_id: formData.get('empleado_id') || null,
                observaciones: formData.get('observaciones') || ''
            };
            
            try {
                const response = await fetch(BASE_URL + '/pedidos/cambiar_estado.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                showResult(card, result.success, result);
            } catch (error) {
                showResult(card, false, {error: error.message});
            } finally {
                showLoading(card, false);
            }
        });
        
        // 6. ACTUALIZAR UBICACION
        document.getElementById('form-ubicacion').addEventListener('submit', async function(e) {
            e.preventDefault();
            const card = this.closest('.endpoint-card');
            showLoading(card, true);
            
            const formData = new FormData(this);
            const data = {
                pedido_id: formData.get('pedido_id'),
                empleado_id: formData.get('empleado_id'),
                latitud: formData.get('latitud'),
                longitud: formData.get('longitud'),
                observaciones: formData.get('observaciones') || ''
            };
            
            try {
                const response = await fetch(BASE_URL + '/tracking/actualizar_ubicacion.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                showResult(card, result.success, result);
            } catch (error) {
                showResult(card, false, {error: error.message});
            } finally {
                showLoading(card, false);
            }
        });
    </script>
</body>
</html>
