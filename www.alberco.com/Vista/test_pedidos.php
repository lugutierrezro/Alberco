<!DOCTYPE html>
<html>
<head>
    <title>Test - Pedidos Para Llevar y En Local</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .test { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
        .ok { background: #4CAF50; color: white; }
        .error { background: #f44336; color: white; }
        #result { margin-top: 20px; padding: 15px; background: #e3f2fd; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>🧪 Test - Pedidos Para Llevar y En Local</h1>
    
    <div class="test">
        <h2>Test 1: Pedido Para Llevar</h2>
        <button onclick="testParaLlevar()">Probar Para Llevar</button>
    </div>
    
    <div class="test">
        <h2>Test 2: Pedido En Local (Mesa)</h2>
        <button onclick="testEnLocal()">Probar En Local</button>
    </div>
    
    <div id="result"></div>
    
    <script>
        async function testParaLlevar() {
            const result = document.getElementById('result');
            result.innerHTML = '<h3>Probando Para Llevar...</h3>';
            
            const pedido = {
                tipo_pedido: 'para_llevar',
                cliente: {
                    nombre: "Usuario Test",
                    telefono: "999999999"
                },
                productos: [{
                    id: 1,
                    nombre: "Producto Test",
                    precio: 25.00,
                    cantidad: 1
                }],
                metodo_pago: 'efectivo',
                observaciones: 'Test para llevar',
                subtotal: 25.00,
                descuento: 0,
                costo_delivery: 0,
                total: 25.00
            };
            
            try {
                const response = await fetch('procesar_pedido_directo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(pedido)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    result.innerHTML = `<h3 class="ok">✅ ÉXITO</h3><pre>${JSON.stringify(data, null, 2)}</pre>`;
                } else {
                    result.innerHTML = `<h3 class="error">❌ ERROR</h3><pre>${JSON.stringify(data, null, 2)}</pre>`;
                }
            } catch (error) {
                result.innerHTML = `<h3 class="error">❌ ERROR DE RED</h3><pre>${error.message}</pre>`;
            }
        }
        
        async function testEnLocal() {
            const result = document.getElementById('result');
            result.innerHTML = '<h3>Probando En Local...</h3>';
            
            const pedido = {
                tipo_pedido: 'mesa',
                cliente: {
                    nombre: "Usuario Test",
                    telefono: "999999999",
                    mesa: "5"
                },
                productos: [{
                    id: 1,
                    nombre: "Producto Test",
                    precio: 25.00,
                    cantidad: 1
                }],
                metodo_pago: 'yape',
                observaciones: 'Test en local',
                subtotal: 25.00,
                descuento: 0,
                costo_delivery: 0,
                total: 25.00
            };
            
            try {
                const response = await fetch('procesar_pedido_directo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(pedido)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    result.innerHTML = `<h3 class="ok">✅ ÉXITO</h3><pre>${JSON.stringify(data, null, 2)}</pre>`;
                } else {
                    result.innerHTML = `<h3 class="error">❌ ERROR</h3><pre>${JSON.stringify(data, null, 2)}</pre>`;
                }
            } catch (error) {
                result.innerHTML = `<h3 class="error">❌ ERROR DE RED</h3><pre>${error.message}</pre>`;
            }
        }
    </script>
</body>
</html>
