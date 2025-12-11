<?php
// Test script to debug promotions
require_once 'app/init.php';

echo "=== DEBUGGING PROMOCIONES ===\n\n";

// 1. Test getCategorias
echo "1. Getting all categories:\n";
$categorias = getCategorias();
echo "Found " . count($categorias) . " categories:\n";
foreach ($categorias as $cat) {
    echo "  - ID: {$cat['id_categoria']}, Name: {$cat['nombre_categoria']}\n";
}
echo "\n";

// 2. Find Promociones category
echo "2. Looking for 'Promociones' category:\n";
$idPromociones = null;
foreach ($categorias as $cat) {
    $nombre = strtolower(trim($cat['nombre_categoria']));
    echo "  Checking: '{$nombre}' == 'promociones'? ";
    if ($nombre == 'promociones') {
        $idPromociones = (int)$cat['id_categoria'];
        echo "YES! ID = $idPromociones\n";
        break;
    } else {
        echo "No\n";
    }
}

if (!$idPromociones) {
    echo "  ERROR: Promociones category not found!\n\n";
    exit;
}

echo "\n";

// 3. Get products from Promociones category
echo "3. Getting products from Promociones category (ID: $idPromociones):\n";
$productos = getProductos([
    'categoria' => $idPromociones,
    'disponibles' => true
]);

echo "Found " . count($productos) . " products:\n";
foreach ($productos as $prod) {
    echo "  - ID: {$prod['id_producto']}, Name: {$prod['nombre']}, Stock: {$prod['stock']}, Disponible: {$prod['disponible_venta']}\n";
}
echo "\n";

// 4. Test getPromociones function
echo "4. Testing getPromociones() function:\n";
$promociones = getPromociones(6);
echo "getPromociones() returned " . count($promociones) . " products:\n";
foreach ($promociones as $promo) {
    echo "  - ID: {$promo['id_producto']}, Name: {$promo['nombre']}\n";
}
echo "\n";

echo "=== END DEBUG ===\n";
?>
