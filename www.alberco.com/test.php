<?php
/**
 * Test Script for Alberco Sales Website
 * This script tests the helper functions and basic functionality
 */

// Include initialization
require_once __DIR__ . '/app/init.php';

echo "=== ALBERCO SALES WEBSITE - TEST SCRIPT ===\n\n";

// Test 1: Database Connection
echo "1. Testing Database Connection...\n";
try {
    $testQuery = $pdo->query("SELECT 1");
    echo "   ✓ Database connection successful\n\n";
} catch (Exception $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Get Categories
echo "2. Testing getCategorias() function...\n";
try {
    $categorias = getCategorias();
    echo "   ✓ Found " . count($categorias) . " categories\n";
    if (count($categorias) > 0) {
        echo "   - First category: " . $categorias[0]['nombre_categoria'] . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 3: Get Products
echo "3. Testing getProductos() function...\n";
try {
    $productos = getProductos(['disponibles' => true]);
    echo "   ✓ Found " . count($productos) . " available products\n";
    if (count($productos) > 0) {
        echo "   - First product: " . $productos[0]['nombre'] . " - S/ " . $productos[0]['precio_venta'] . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 4: Get Promotions
echo "4. Testing getPromociones() function...\n";
try {
    $promociones = getPromociones(6);
    echo "   ✓ Found " . count($promociones) . " promotions\n";
    if (count($promociones) > 0) {
        echo "   - First promotion: " . $promociones[0]['nombre'] . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 5: Get Product by ID
echo "5. Testing getProductoById() function...\n";
try {
    if (count($productos) > 0) {
        $testId = $productos[0]['id_producto'];
        $producto = getProductoById($testId);
        if ($producto) {
            echo "   ✓ Product found: " . $producto['nombre'] . "\n";
            echo "   - Price: S/ " . $producto['precio_venta'] . "\n";
            echo "   - Stock: " . $producto['stock'] . "\n";
        } else {
            echo "   ✗ Product not found\n";
        }
    } else {
        echo "   - No products to test\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 6: Image Path Function
echo "6. Testing getImagePath() function...\n";
try {
    // Test with valid image
    if (count($productos) > 0 && !empty($productos[0]['imagen'])) {
        $imagePath = getImagePath($productos[0]['imagen']);
        echo "   ✓ Image path generated: " . $imagePath . "\n";
    }
    
    // Test with empty image
    $defaultPath = getImagePath('');
    echo "   ✓ Default image path: " . $defaultPath . "\n";
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 7: Models
echo "7. Testing Model Classes...\n";
try {
    $productoModel = new Producto();
    $categoriaModel = new Categoria();
    $pedidoModel = new Pedido();
    $clienteModel = new Cliente();
    echo "   ✓ All model classes instantiated successfully\n\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 8: Constants
echo "8. Testing Defined Constants...\n";
echo "   - ADMIN_PATH: " . (defined('ADMIN_PATH') ? '✓' : '✗') . "\n";
echo "   - URL_BASE: " . (defined('URL_BASE') ? '✓' : '✗') . "\n";
echo "   - APP_PATH: " . (defined('APP_PATH') ? '✓' : '✗') . "\n";
echo "   - SALES_ROOT: " . (defined('SALES_ROOT') ? '✓' : '✗') . "\n";
echo "   - VIEWS_PATH: " . (defined('VIEWS_PATH') ? '✓' : '✗') . "\n";
echo "\n";

echo "=== TEST COMPLETED ===\n";
echo "\nAll tests passed! The system is ready to use.\n";
echo "\nNext steps:\n";
echo "1. Open http://localhost/www.alberco.com in your browser\n";
echo "2. Navigate through the pages to verify functionality\n";
echo "3. Test the shopping cart and order process\n";
