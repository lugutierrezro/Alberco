<?php
/**
 * Script de prueba para verificar el registro de pedidos
 * Ejecutar desde: http://localhost/www.alberco.com/Vista/test_pedido.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . "/../app/init.php");

echo "<h1>Test de Registro de Pedidos</h1>";
echo "<pre>";

// 1. Verificar conexión a base de datos
echo "=== 1. VERIFICANDO CONEXIÓN A BASE DE DATOS ===\n";
try {
    // Usar la función getDB() del sistema admin
    $pdo = getDB();
    
    echo "✅ Conexión exitosa\n";
    $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();
    echo "Base de datos: $dbName\n\n";
    
    // Verificar que sea la base de datos correcta
    if ($dbName !== 'sistema_gestion_alberco_v3') {
        echo "⚠️  ADVERTENCIA: La base de datos actual es '$dbName'\n";
        echo "   Se esperaba 'sistema_gestion_alberco_v3'\n\n";
    }
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n\n";
    die();
}

// 2. Verificar que existe el stored procedure
echo "=== 2. VERIFICANDO STORED PROCEDURE ===\n";
try {
    $stmt = $pdo->query("SHOW PROCEDURE STATUS WHERE Db = '$dbName' AND Name = 'sp_crear_pedido'");
    $proc = $stmt->fetch();
    if ($proc) {
        echo "✅ Stored procedure 'sp_crear_pedido' existe\n\n";
    } else {
        echo "❌ Stored procedure 'sp_crear_pedido' NO existe\n";
        echo "   Verifica que hayas ejecutado el script SQL completo\n\n";
        die();
    }
} catch (Exception $e) {
    echo "❌ Error al verificar SP: " . $e->getMessage() . "\n\n";
}

// 3. Verificar modelos
echo "=== 3. VERIFICANDO MODELOS ===\n";
try {
    $pedidoModel = new Pedido();
    echo "✅ Modelo Pedido cargado\n";
    
    $clienteModel = new Cliente();
    echo "✅ Modelo Cliente cargado\n";
    
    $productoModel = new Producto();
    echo "✅ Modelo Producto cargado\n\n";
} catch (Exception $e) {
    echo "❌ Error al cargar modelos: " . $e->getMessage() . "\n\n";
    die();
}

// 4. Buscar o crear cliente de prueba
echo "=== 4. BUSCANDO CLIENTE DE PRUEBA ===\n";
$telefono = '999888777';
$cliente = $clienteModel->getByTelefono($telefono);

if (!$cliente) {
    echo "Cliente no existe, creando...\n";
    try {
        $clienteId = $clienteModel->create([
            'nombre' => 'Cliente Prueba Sistema',
            'telefono' => $telefono,
            'direccion' => 'Av. Prueba 123, Lima',
            'tipo_cliente' => 'NUEVO',
            'estado_registro' => 'ACTIVO',
            'fyh_creacion' => date('Y-m-d H:i:s')
        ]);
        echo "✅ Cliente creado con ID: $clienteId\n\n";
    } catch (Exception $e) {
        echo "❌ Error al crear cliente: " . $e->getMessage() . "\n\n";
        die();
    }
} else {
    $clienteId = $cliente['id_cliente'];
    echo "✅ Cliente encontrado con ID: $clienteId\n";
    echo "   Nombre: " . $cliente['nombre'] . "\n";
    echo "   Teléfono: " . $cliente['telefono'] . "\n\n";
}

// 5. Obtener un producto de prueba
echo "=== 5. OBTENIENDO PRODUCTO DE PRUEBA ===\n";
try {
    $stmt = $pdo->query("SELECT id_producto, nombre, precio_venta as precio FROM tb_almacen WHERE estado_registro = 'ACTIVO' AND stock > 0 LIMIT 1");
    $producto = $stmt->fetch();
    
    if ($producto) {
        echo "✅ Producto encontrado:\n";
        echo "   ID: " . $producto['id_producto'] . "\n";
        echo "   Nombre: " . $producto['nombre'] . "\n";
        echo "   Precio: S/ " . $producto['precio'] . "\n\n";
    } else {
        echo "❌ No hay productos disponibles\n";
        echo "   Agrega productos a la tabla tb_almacen primero\n\n";
        die();
    }
} catch (Exception $e) {
    echo "❌ Error al obtener producto: " . $e->getMessage() . "\n\n";
    die();
}

// 6. Crear pedido de prueba
echo "=== 6. CREANDO PEDIDO DE PRUEBA ===\n";
try {
    $pedidoData = [
        'tipo_pedido' => 'delivery',
        'id_mesa' => null,
        'id_cliente' => $clienteId,
        'id_usuario' => 1, // Usuario admin por defecto
        'direccion_entrega' => 'Av. Prueba 123, Lima - Referencia: Frente al parque',
        'latitud' => -12.0464,
        'longitud' => -77.0428,
        'observaciones' => 'Pedido de prueba desde test_pedido.php - ' . date('Y-m-d H:i:s')
    ];
    
    $detalles = [
        [
            'id_producto' => $producto['id_producto'],
            'cantidad' => 2,
            'precio_unitario' => $producto['precio'],
            'observaciones' => 'Sin cebolla'
        ]
    ];
    
    echo "Datos del pedido:\n";
    echo "  Tipo: " . $pedidoData['tipo_pedido'] . "\n";
    echo "  Cliente ID: " . $pedidoData['id_cliente'] . "\n";
    echo "  Dirección: " . $pedidoData['direccion_entrega'] . "\n\n";
    
    echo "Detalles del pedido:\n";
    foreach ($detalles as $det) {
        echo "  - Producto ID: " . $det['id_producto'] . "\n";
        echo "    Cantidad: " . $det['cantidad'] . "\n";
        echo "    Precio: S/ " . $det['precio_unitario'] . "\n";
        echo "    Subtotal: S/ " . ($det['cantidad'] * $det['precio_unitario']) . "\n";
    }
    echo "\n";
    
    echo "Llamando a crearPedido()...\n";
    $resultado = $pedidoModel->crearPedido($pedidoData, $detalles);
    
    echo "\nResultado:\n";
    print_r($resultado);
    echo "\n";
    
    if ($resultado['success']) {
        echo "✅ ¡PEDIDO CREADO EXITOSAMENTE!\n";
        echo "   Número de comanda: " . $resultado['numero_comanda'] . "\n";
        echo "   ID del pedido: " . ($resultado['id_pedido'] ?? 'N/A') . "\n";
        echo "   Mensaje: " . $resultado['mensaje'] . "\n\n";
        
        // Verificar que se guardó en la base de datos
        echo "=== 7. VERIFICANDO EN BASE DE DATOS ===\n";
        $stmt = $pdo->prepare("SELECT * FROM tb_pedidos WHERE numero_comanda = ?");
        $stmt->execute([$resultado['numero_comanda']]);
        $pedidoGuardado = $stmt->fetch();
        
        if ($pedidoGuardado) {
            echo "✅ Pedido encontrado en la base de datos\n";
            echo "   ID: " . $pedidoGuardado['id_pedido'] . "\n";
            echo "   Número: " . $pedidoGuardado['nro_pedido'] . "\n";
            echo "   Total: S/ " . $pedidoGuardado['total'] . "\n";
            echo "   Estado ID: " . $pedidoGuardado['id_estado'] . "\n\n";
            
            // Verificar detalles
            $stmt = $pdo->prepare("SELECT * FROM tb_detalle_pedidos WHERE id_pedido = ?");
            $stmt->execute([$pedidoGuardado['id_pedido']]);
            $detallesGuardados = $stmt->fetchAll();
            
            echo "   Detalles guardados: " . count($detallesGuardados) . " items\n";
            foreach ($detallesGuardados as $det) {
                echo "   - Producto ID: " . $det['id_producto'] . ", Cantidad: " . $det['cantidad'] . "\n";
            }
        } else {
            echo "❌ Pedido NO encontrado en la base de datos\n";
        }
        
    } else {
        echo "❌ ERROR AL CREAR PEDIDO\n";
        echo "   Mensaje: " . $resultado['mensaje'] . "\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Excepción al crear pedido:\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n\n";
}

echo "=== FIN DEL TEST ===\n";
echo "</pre>";

echo "<hr>";
echo "<h3>Acciones:</h3>";
echo "<ul>";
echo "<li><a href='menu.php'>Ir al Menú</a></li>";
echo "<li><a href='pedido.php'>Ir a Carrito</a></li>";
echo "<li><a href='test_pedido.php'>Ejecutar test nuevamente</a></li>";
echo "</ul>";
?>
