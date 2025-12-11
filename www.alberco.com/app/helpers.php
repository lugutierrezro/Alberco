<?php
/**
 * Helper Functions for Alberco Sales Website
 * These functions encapsulate logic for safely using admin system controllers
 */

/**
 * Get all categories ordered
 * @param bool $withProducts Include product count
 * @return array
 */
function getCategorias($withProducts = false) {
    global $pdo; // Make $pdo available
    
    try {
        // Set parameters for controller
        $_GET['ordenadas'] = '1';
        if ($withProducts) {
            $_GET['con_productos'] = '1';
        }
        
        // Include controller (it sets $categorias_datos)
        require ADMIN_PATH . 'controllers/categorias/listar.php';
        
        // Clean up GET parameters
        unset($_GET['ordenadas']);
        unset($_GET['con_productos']);
        
        return isset($categorias_datos) ? $categorias_datos : [];
    } catch (Exception $e) {
        error_log("Error en getCategorias: " . $e->getMessage());
        return [];
    }
}

/**
 * Get products with filters
 * @param array $filters Filters: categoria, disponibles, stock_bajo
 * @return array
 */
function getProductos($filters = []) {
    global $pdo; // Make $pdo available
    
    try {
        // Set filters
        if (isset($filters['categoria'])) {
            $_GET['categoria'] = $filters['categoria'];
        }
        if (isset($filters['disponibles']) && $filters['disponibles']) {
            $_GET['disponibles'] = '1';
        }
        if (isset($filters['stock_bajo']) && $filters['stock_bajo']) {
            $_GET['stock_bajo'] = '1';
        }
        
        // Include controller (it sets $productos_datos)
        require ADMIN_PATH . 'controllers/productos/listar.php';
        
        // Clean up GET parameters
        unset($_GET['categoria']);
        unset($_GET['disponibles']);
        unset($_GET['stock_bajo']);
        
        return isset($productos_datos) ? $productos_datos : [];
    } catch (Exception $e) {
        error_log("Error en getProductos: " . $e->getMessage());
        return [];
    }
}

/**
 * Get product by ID using model
 * @param int $id Product ID
 * @return array|false
 */
function getProductoById($id) {
    try {
        $productoModel = new Producto();
        return $productoModel->getById($id);
    } catch (Exception $e) {
        error_log("Error en getProductoById: " . $e->getMessage());
        return false;
    }
}

/**
 * Get products by category using model
 * @param int $categoryId Category ID
 * @return array
 */
function getProductosByCategoria($categoryId) {
    try {
        $productoModel = new Producto();
        return $productoModel->getByCategory($categoryId);
    } catch (Exception $e) {
        error_log("Error en getProductosByCategoria: " . $e->getMessage());
        return [];
    }
}

/**
 * Get category by ID using model
 * @param int $id Category ID
 * @return array|false
 */
function getCategoriaById($id) {
    try {
        $categoriaModel = new Categoria();
        return $categoriaModel->getById($id);
    } catch (Exception $e) {
        error_log("Error en getCategoriaById: " . $e->getMessage());
        return false;
    }
}

/**
 * Get image path from admin system
 * Improved version with better error handling
 * @param string $imageFile Image file path relative to admin system
 * @param bool $returnRelative Return relative path for local fallback
 * @return string
 */
function getImagePath($imageFile, $returnRelative = false) {
    if (empty($imageFile)) {
        return $returnRelative ? 'Assets/no-image.jpg' : 'Assets/no-image.jpg';
    }
    
    // Physical path to verify existence
    $physicalPath = ADMIN_PATH . $imageFile;
    
    if (file_exists($physicalPath)) {
        // Return URL to admin system
        return URL_BASE . '/' . $imageFile;
    }
    
    // Return default image
    return $returnRelative ? '../Assets/no-image.jpg' : 'Assets/no-image.jpg';
}

/**
 * Format price in Peruvian Soles
 * @param float $price
 * @return string
 */
function formatPrice($price) {
    return 'S/ ' . number_format($price, 2);
}

/**
 * Sanitize output for HTML
 * @param string $text
 * @return string
 */
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Get stock badge HTML
 * @param int $stock
 * @return string
 */
function getStockBadge($stock) {
    if ($stock > 10) {
        return '<span class="badge bg-success"><i class="fas fa-check-circle"></i> En stock (' . $stock . ')</span>';
    } elseif ($stock > 0) {
        return '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Stock bajo (' . $stock . ')</span>';
    } else {
        return '<span class="badge bg-danger"><i class="fas fa-times-circle"></i> Agotado</span>';
    }
}

/**
 * Check if product is available for sale
 * @param array $product
 * @return bool
 */
function isProductAvailable($product) {
    return isset($product['disponible_venta']) 
        && $product['disponible_venta'] == 1 
        && isset($product['stock']) 
        && $product['stock'] > 0;
}

/**
 * Get promotion products (from "Promociones" category)
 * @param int $limit Maximum number of products
 * @return array
 */
function getPromociones($limit = 6) {
    try {
        // Get all categories
        $categorias = getCategorias();
        
        // Find "Promociones" category
        $idPromociones = null;
        foreach ($categorias as $cat) {
            $nombreCat = strtolower(trim($cat['nombre_categoria']));
            if (in_array($nombreCat, ['promociones', 'promocion', 'ofertas', 'promociones de la semana'])) {
                $idPromociones = (int)$cat['id_categoria'];
                break;
            }
        }
        
        if (!$idPromociones) {
            return [];
        }
        
        // Get products from that category
        $productos = getProductos([
            'categoria' => $idPromociones,
            'disponibles' => true
        ]);
        
        // Limit results
        return array_slice($productos, 0, $limit);
        
    } catch (Exception $e) {
        error_log("Error en getPromociones: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all active tables
 * @return array
 */
function getMesas() {
    try {
        $mesaModel = new Mesa();
        // Get all active tables (we can filter by 'disponible' if needed, but 'getAll' usually implies all valid records)
        // Since Mesa model has getDisponibles, let's use that or getAll. 
        // The model provided has getByZona, getDisponibles, getOcupadas. 
        // It inherits from BaseModel which likely has getAll.
        // Let's use getAll if available, or construct a query.
        // Given the model file content, it extends BaseModel.
        // Let's try to get all tables that are active in the system.
        // Since we want to show them in a dropdown, maybe just available ones? 
        // But for "Consumo en Local", maybe we want to select any table?
        // Let's stick to getDisponibles() as it's safer for new orders.
        return $mesaModel->getDisponibles();
    } catch (Exception $e) {
        error_log("Error en getMesas: " . $e->getMessage());
        return [];
    }
}
