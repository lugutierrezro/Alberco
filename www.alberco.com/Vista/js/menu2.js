// Carrito en localStorage
let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

function obtenerCarrito() {
    return JSON.parse(localStorage.getItem('carrito')) || [];
}

// Actualizar contador del carrito
function actualizarContador() {
    let carrito = obtenerCarrito();
    const totalItems = carrito.reduce((sum, item) => sum + item.cantidad, 0);
    const cartCount = document.getElementById('cartCount');
    if (cartCount) {
        cartCount.textContent = totalItems;
        cartCount.classList.add('animate__animated', 'animate__bounce');
        setTimeout(() => cartCount.classList.remove('animate__animated', 'animate__bounce'), 500);
    }
}

// Animación de vuelo del producto al carrito
function animarProducto(imgSrc) {
    const carritoIcon = document.querySelector('.cart-icon');
    const productoImg = document.createElement('img');
    productoImg.src = imgSrc;
    productoImg.style.position = 'fixed';
    productoImg.style.width = '60px';
    productoImg.style.height = '60px';
    productoImg.style.zIndex = 9999;
    productoImg.style.transition = 'all 0.8s ease-in-out';

    document.body.appendChild(productoImg);

    // Posición inicial en el centro de la pantalla
    productoImg.style.top = (window.innerHeight / 2 - 30) + 'px';
    productoImg.style.left = (window.innerWidth / 2 - 30) + 'px';

    setTimeout(() => {
        const rect = carritoIcon.getBoundingClientRect();
        productoImg.style.top = rect.top + 'px';
        productoImg.style.left = rect.left + 'px';
        productoImg.style.width = '20px';
        productoImg.style.height = '20px';
        productoImg.style.opacity = 0.5;
    }, 50);

    setTimeout(() => {
        productoImg.remove();
    }, 900);
}

// Agregar producto al carrito
function agregarAlCarrito(id, nombre, precio, imgSrc = null) {
    let carrito = obtenerCarrito();

    const index = carrito.findIndex(item => item.id === id);
    if (index !== -1) {
        carrito[index].cantidad += 1;
    } else {
        carrito.push({ id: id, nombre, precio, cantidad: 1 });
    }

    localStorage.setItem('carrito', JSON.stringify(carrito));
    actualizarContador();

    // Animación
    if (imgSrc) animarProducto(imgSrc);

    // Notificación
    Swal.fire({
        icon: 'success',
        title: '¡Producto agregado!',
        text: `${nombre} se agregó a tu carrito exitosamente.`,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000
    });
}

// Inicializar contador al cargar la página
document.addEventListener('DOMContentLoaded', actualizarContador);
// Ver carrito
function verCarrito() {
    if (carrito.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'Carrito Vacío',
            text: 'No has agregado productos aún. ¡Explora nuestro menú!',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#d32f2f'
        });
        return;
    }

    let html = '<div class="text-start">';
    let total = 0;

    carrito.forEach(item => {
        const subtotal = item.precio * item.cantidad;
        total += subtotal;
        html += `
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <div>
                    <strong>${item.nombre}</strong><br>
                    <small>S/ ${item.precio.toFixed(2)} x ${item.cantidad}</small>
                </div>
                <div class="fw-bold">S/ ${subtotal.toFixed(2)}</div>
            </div>
        `;
    });

    html += `
        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
            <h5>TOTAL:</h5>
            <h5 class="text-danger fw-bold">S/ ${total.toFixed(2)}</h5>
        </div>
    </div>`;

    Swal.fire({
        title: '🛒 Tu Carrito de Compras',
        html: html,
        width: 600,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-check"></i> Confirmar Pedido',
        cancelButtonText: '<i class="fas fa-trash"></i> Vaciar Carrito',
        confirmButtonColor: '#d32f2f',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'pedido.php';
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            vaciarCarrito();
        }
    });
}

// Vaciar carrito
function vaciarCarrito() {
    Swal.fire({
        title: '¿Vaciar el carrito?',
        text: 'Se eliminarán todos los productos de tu carrito. Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d32f2f',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, vaciar carrito',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            carrito = [];
            localStorage.removeItem('carrito');
            actualizarContador();
            Swal.fire({
                icon: 'success',
                title: '¡Carrito vaciado!',
                text: 'Todos los productos fueron eliminados exitosamente',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}

// Filtrar por categoría
function filtrarCategoria(idCategoria, event) {
    const items = document.querySelectorAll('.product-item');
    const buttons = document.querySelectorAll('.category-btn');

    // Activar botón
    buttons.forEach(btn => btn.classList.remove('active'));
    if (event) event.target.classList.add('active');

    // Filtrar productos
    items.forEach(item => {
        if (idCategoria === 0 || item.dataset.category == idCategoria) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Buscar productos
document.getElementById('searchInput').addEventListener('input', function () {
    const texto = this.value.toLowerCase();
    const items = document.querySelectorAll('.product-item');

    items.forEach(item => {
        const nombre = item.querySelector('.card-title').textContent.toLowerCase();
        const descripcion = item.querySelector('.card-text').textContent.toLowerCase();

        if (nombre.includes(texto) || descripcion.includes(texto)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Inicializar contador
actualizarContador();
