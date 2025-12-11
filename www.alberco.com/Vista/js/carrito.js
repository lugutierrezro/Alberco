// ===================================
// ALBERCO - Sistema de Carrito de Compras
// carrito.js (Data Layer)
// ===================================

// Obtener carrito desde LocalStorage
function getCart() {
    try {
        const cart = localStorage.getItem('carrito');
        let parsedCart = cart ? JSON.parse(cart) : [];

        // Sanear rutas de imágenes antiguas que puedan estar corruptas
        let needsSave = false;
        parsedCart.forEach(item => {
            if (item.imagen && typeof item.imagen === 'string' && (item.imagen.includes('://') || item.imagen.includes('uploads/'))) {
                const partes = item.imagen.split('/');
                item.imagen = partes[partes.length - 1];
                needsSave = true;
            }
        });

        if (needsSave) {
            localStorage.setItem('carrito', JSON.stringify(parsedCart));
        }

        return parsedCart;
    } catch (error) {
        console.error('Error al obtener el carrito:', error);
        return [];
    }
}

// Guardar carrito en LocalStorage y actualizar badge
function saveCart(cart) {
    try {
        localStorage.setItem('carrito', JSON.stringify(cart));
        updateCartBadge();

        // Disparar evento para que otros scripts sepan que cambió el carrito
        window.dispatchEvent(new CustomEvent('cartUpdated', { detail: cart }));
    } catch (error) {
        console.error('Error al guardar el carrito:', error);
    }
}

// Agregar producto al carrito
function addToCart(producto, cantidad = 1) {
    let cart = getCart();
    const existingItem = cart.find(item => item.id === producto.id);

    // Normalizar ruta de imagen - extraer solo el nombre del archivo
    let imagenNormalizada = producto.imagen;
    if (imagenNormalizada && typeof imagenNormalizada === 'string') {
        // Si es una URL completa, extraer solo el nombre del archivo
        if (imagenNormalizada.includes('://') || imagenNormalizada.includes('uploads/almacen/')) {
            const partes = imagenNormalizada.split('/');
            imagenNormalizada = partes[partes.length - 1];
        }
    }

    if (existingItem) {
        existingItem.cantidad += cantidad;
    } else {
        cart.push({
            id: producto.id,
            nombre: producto.nombre,
            precio: producto.precio,
            imagen: imagenNormalizada,
            categoria: producto.categoria,
            cantidad: cantidad
        });
    }

    saveCart(cart);
    showSuccessMessage('Producto agregado al carrito');
}

// Actualizar cantidad (Uso general)
function updateQuantity(productId, newQuantity) {
    if (newQuantity < 1) return;

    let cart = getCart();
    const item = cart.find(item => item.id === productId);

    if (item) {
        item.cantidad = newQuantity;
        saveCart(cart);
    }
}

// Eliminar producto (Uso general)
function removeFromCart(productId) {
    let cart = getCart();
    cart = cart.filter(item => item.id !== productId);
    saveCart(cart);
}

// Calcular total
function calculateTotal() {
    const cart = getCart();
    return cart.reduce((total, item) => total + (item.precio * item.cantidad), 0);
}

// Contar items totales
function getCartItemCount() {
    const cart = getCart();
    return cart.reduce((total, item) => total + item.cantidad, 0);
}

// Actualizar badge en el header
function updateCartBadge() {
    const badge = document.getElementById('cartBadge');
    if (badge) {
        const count = getCartItemCount();
        badge.textContent = count;
        // Mostrar u ocultar badge o contador según el diseño
        badge.style.display = count >= 0 ? 'flex' : 'none'; // Siempre flex para mantener layout o count > 0
    }

    // Si existe otro elemento de contador (ej. en versión móvil)
    const mobileCount = document.getElementById('cartCount');
    if (mobileCount) {
        mobileCount.textContent = getCartItemCount();
    }
}

// Vaciar carrito
function clearCart() {
    localStorage.removeItem('carrito');
    updateCartBadge();
    window.dispatchEvent(new CustomEvent('cartUpdated', { detail: [] }));
}

// Mostrar mensaje de éxito (Toast)
function showSuccessMessage(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
        });
    } else {
        console.log("Mensaje: " + message);
    }
}

// Inicializar badge al cargar
document.addEventListener('DOMContentLoaded', function () {
    updateCartBadge();
});
