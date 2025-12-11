<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Avanzado - Sistema de Pedidos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0d1117;
            color: #c9d1d9;
            padding: 20px;
        }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { color: #58a6ff; margin-bottom: 30px; font-size: 32px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(600px, 1fr)); gap: 20px; }
        .panel {
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .panel h2 {
            color: #58a6ff;
            margin-bottom: 15px;
            font-size: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #30363d;
        }
        .test-section {
            background: #0d1117;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .status { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .status.ok { background: #238636; color: #fff; }
        .status.error { background: #da3633; color: #fff; }
        .status.warning { background: #d29922; color: #000; }
        .status.info { background: #1f6feb; color: #fff; }
        pre {
            background: #0d1117;
            padding: 12px;
            border-radius: 4px;
            overflow-x: auto;
            border: 1px solid #30363d;
            color: #8b949e;
            font-size: 13px;
            line-height: 1.5;
        }
        .json { color: #79c0ff; }
        .sql { color: #ffa657; }
        button {
            background: #238636;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            margin: 5px;
        }
        button:hover { background: #2ea043; }
        button.secondary { background: #21262d; border: 1px solid #30363d; }
        button.secondary:hover { background: #30363d; }
        #consoleOutput {
            background: #000;
            color: #0f0;
            padding: 15px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            height: 400px;
            overflow-y: auto;
            margin-top: 10px;
        }
        .log-entry { margin: 5px 0; padding: 5px; }
        .log-entry.error { color: #ff6b6b; }
        .log-entry.success { color: #51cf66; }
        .log-entry.info { color: #339af0; }
        input, select, textarea {
            background: #0d1117;
            border: 1px solid #30363d;
            color: #c9d1d9;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            width: 100%;
            margin: 5px 0;
        }
        label { display: block; margin: 10px 0 5px; font-weight: 600; color: #8b949e; }
        .form-group { margin: 15px 0; }
        .radio-group { display: flex; gap: 20px; }
        .radio-group label { display: inline-flex; align-items: center; margin: 0; }
        .radio-group input[type="radio"] { margin-right: 8px; width: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔬 Debug Avanzado - Sistema de Pedidos Alberco</h1>
        
        <div class="grid">
            <!-- PANEL 1: Test de Pedido en Tiempo Real -->
            <div class="panel">
                <h2>📝 Test de Creación de Pedido</h2>
                
                <div class="form-group">
                    <label>Tipo de Pedido:</label>
                    <div class="radio-group">
                        <label><input type="radio" name="tipoPedido" value="delivery" checked> Delivery</label>
                        <label><input type="radio" name="tipoPedido" value="para_llevar"> Para Llevar</label>
                        <label><input type="radio" name="tipoPedido" value="mesa"> En Local</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Método de Pago:</label>
                    <div class="radio-group">
                        <label><input type="radio" name="metodoPago" value="efectivo" checked> Efectivo</label>
                        <label><input type="radio" name="metodoPago" value="yape"> Yape</label>
                        <label><input type="radio" name="metodoPago" value="tarjeta"> Tarjeta</label>
                    </div>
                </div>
                
                <div class="form-group" id="mesaGroup" style="display:none;">
                    <label>Número de Mesa:</label>
                    <input type="number" id="numeroMesa" placeholder="Ej: 5">
                </div>
                
                <div class="form-group">
                    <label>Dirección (si es delivery):</label>
                    <input type="text" id="direccion" placeholder="Ej: Av. Lima 123">
                </div>
                
                <div class="form-group">
                    <label>Observaciones:</label>
                    <textarea id="observaciones" rows="2" placeholder="Opcional"></textarea>
                </div>
                
                <button onclick="enviarPedidoTest()">🚀 Enviar Pedido de Prueba</button>
                <button class="secondary" onclick="clearConsole()">🗑️ Limpiar Consola</button>
                
                <div id="consoleOutput"></div>
            </div>
            
            <!-- PANEL 2: Validación de Datos -->
            <div class="panel">
                <h2>✅ Validación de Datos Enviados</h2>
                <div id="validationResults"></div>
            </div>
        </div>
        
        <!-- PANEL 3: Verificación de Frontend -->
        <div class="panel">
            <h2>🎨 Verificación de Frontend (pedido.js)</h2>
            <button onclick="verificarFrontend()">🔍 Verificar Código Frontend</button>
            <div id="frontendCheck"></div>
        </div>
        
        <!-- PANEL 4: Verificación de Base de Datos -->
        <div class="panel">
            <h2>💾 Estado de la Base de Datos</h2>
            <button onclick="verificarBD()">🔍 Verificar Base de Datos</button>
            <div id="dbCheck"></div>
        </div>
    </div>

    <script>
        // Toggle mesa section
        document.querySelectorAll('input[name="tipoPedido"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('mesaGroup').style.display = 
                    this.value === 'mesa' ? 'block' : 'none';
            });
        });
        
        function log(message, type = 'info') {
            const output = document.getElementById('consoleOutput');
            const entry = document.createElement('div');
            entry.className = `log-entry ${type}`;
            entry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            output.appendChild(entry);
            output.scrollTop = output.scrollHeight;
        }
        
        function clearConsole() {
            document.getElementById('consoleOutput').innerHTML = '';
        }
        
        async function enviarPedidoTest() {
            log('=== INICIANDO TEST DE PEDIDO ===', 'info');
            
            const tipoPedido = document.querySelector('input[name="tipoPedido"]:checked').value;
            const metodoPago = document.querySelector('input[name="metodoPago"]:checked').value;
            const direccion = document.getElementById('direccion').value;
            const observaciones = document.getElementById('observaciones').value;
            const mesa = document.getElementById('numeroMesa').value;
            
            log(`Tipo de pedido: ${tipoPedido}`, 'info');
            log(`Método de pago: ${metodoPago}`, 'info');
            
            const pedido = {
                tipo_pedido: tipoPedido,
                cliente: {
                    nombre: "Test Usuario",
                    telefono: "999999999",
                    direccion: tipoPedido === 'delivery' ? direccion : "",
                    mesa: tipoPedido === 'mesa' ? mesa : null
                },
                productos: [{
                    id: 1,
                    nombre: "Producto Test",
                    precio: 25.00,
                    cantidad: 1,
                    observaciones: null
                }],
                metodo_pago: metodoPago,
                observaciones: observaciones,
                subtotal: 25.00,
                descuento: 0,
                costo_delivery: tipoPedido === 'delivery' ? 5 : 0,
                total: tipoPedido === 'delivery' ? 30 : 25
            };
            
            log('Datos a enviar:', 'info');
            log(JSON.stringify(pedido, null, 2), 'info');
            
            // Mostrar validación
            mostrarValidacion(pedido);
            
            try {
                log('Enviando a procesar_pedido_directo.php...', 'info');
                
                const response = await fetch('procesar_pedido_directo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(pedido)
                });
                
                log(`Status HTTP: ${response.status}`, response.ok ? 'success' : 'error');
                
                const text = await response.text();
                log('Respuesta recibida:', 'info');
                
                try {
                    const data = JSON.parse(text);
                    log(JSON.stringify(data, null, 2), data.success ? 'success' : 'error');
                    
                    if (data.success) {
                        log(`✅ PEDIDO CREADO: ${data.nro_pedido}`, 'success');
                    } else {
                        log(`❌ ERROR: ${data.mensaje}`, 'error');
                    }
                } catch(e) {
                    log('❌ Respuesta no es JSON válido:', 'error');
                    log(text.substring(0, 500), 'error');
                }
                
            } catch(error) {
                log(`❌ ERROR DE RED: ${error.message}`, 'error');
            }
        }
        
        function mostrarValidacion(pedido) {
            const container = document.getElementById('validationResults');
            let html = '<div class="test-section">';
            
            // Validar tipo de pedido
            if (pedido.tipo_pedido) {
                html += `<div><span class="status ok">✓</span> Tipo de pedido: <strong>${pedido.tipo_pedido}</strong></div>`;
            } else {
                html += `<div><span class="status error">✗</span> Tipo de pedido no definido</div>`;
            }
            
            // Validar método de pago
            if (pedido.metodo_pago) {
                html += `<div><span class="status ok">✓</span> Método de pago: <strong>${pedido.metodo_pago}</strong></div>`;
            } else {
                html += `<div><span class="status error">✗</span> Método de pago no definido</div>`;
            }
            
            // Validar datos específicos
            if (pedido.tipo_pedido === 'delivery') {
                if (pedido.cliente.direccion) {
                    html += `<div><span class="status ok">✓</span> Dirección: ${pedido.cliente.direccion}</div>`;
                } else {
                    html += `<div><span class="status warning">⚠</span> Delivery sin dirección</div>`;
                }
            }
            
            if (pedido.tipo_pedido === 'mesa') {
                if (pedido.cliente.mesa) {
                    html += `<div><span class="status ok">✓</span> Mesa: ${pedido.cliente.mesa}</div>`;
                } else {
                    html += `<div><span class="status warning">⚠</span> Pedido en local sin mesa</div>`;
                }
            }
            
            html += `<div><span class="status info">ℹ</span> Total: S/ ${pedido.total.toFixed(2)}</div>`;
            html += '</div>';
            
            html += '<pre class="json">' + JSON.stringify(pedido, null, 2) + '</pre>';
            container.innerHTML = html;
        }
        
        async function verificarFrontend() {
            const container = document.getElementById('frontendCheck');
            container.innerHTML = '<p>Verificando archivos JavaScript...</p>';
            
            try {
                const response = await fetch('js/pedido.js');
                const content = await response.text();
                
                let html = '<div class="test-section">';
                
                // Verificar funciones críticas
                const checks = [
                    { name: 'renderCarrito', found: content.includes('function renderCarrito') },
                    { name: 'actualizarResumen', found: content.includes('function actualizarResumen') },
                    { name: 'validación tipoPedido', found: content.includes('tipoPedidoRadio') },
                    { name: 'validación metodoPago', found: content.includes('metodoPagoRadio') },
                    { name: 'envío AJAX', found: content.includes('fetch(') }
                ];
                
                checks.forEach(check => {
                    const status = check.found ? 'ok' : 'error';
                    const icon = check.found ? '✓' : '✗';
                    html += `<div><span class="status ${status}">${icon}</span> ${check.name}</div>`;
                });
                
                html += '</div>';
                container.innerHTML = html;
                
            } catch(error) {
                container.innerHTML = `<div class="status error">✗ Error al verificar: ${error.message}</div>`;
            }
        }
        
        async function verificarBD() {
            const container = document.getElementById('dbCheck');
            container.innerHTML = '<p>Consultando base de datos...</p>';
            
            try {
                const response = await fetch('verificar_bd.php');
                const data = await response.json();
                
                let html = '<div class="test-section">';
                html += `<div><span class="status ${data.conexion ? 'ok' : 'error'}">${data.conexion ? '✓' : '✗'}</span> Conexión a BD</div>`;
                
                if (data.columnas) {
                    html += '<h3 style="margin-top: 15px;">Columnas de tb_pedidos:</h3>';
                    data.columnas.forEach(col => {
                        html += `<div><span class="status info">•</span> ${col}</div>`;
                    });
                }
                
                if (data.ultimo_pedido) {
                    html += '<h3 style="margin-top: 15px;">Último pedido:</h3>';
                    html += '<pre class="json">' + JSON.stringify(data.ultimo_pedido, null, 2) + '</pre>';
                }
                
                html += '</div>';
                container.innerHTML = html;
                
            } catch(error) {
                container.innerHTML = `<div class="status error">✗ Error: ${error.message}</div>`;
            }
        }
    </script>
</body>
</html>
