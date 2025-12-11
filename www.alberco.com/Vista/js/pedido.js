// ====================================
// ALBERCO - Sistema de Pedidos
// Usa carrito.js para gestión de datos
// ====================================

console.log('pedido.js cargado');

const costoDelivery = 5.00;

// Renderizar carrito usando datos de carrito.js
function renderCarrito() {
    const container = document.getElementById('carritoContainer');
    if (!container) return;

    const carrito = getCart(); // Usa función de carrito.js

    if (carrito.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-5x text-muted mb-3"></i>
                <h4 class="text-muted">Tu carrito está vacío</h4>
                <p class="text-muted">¡Explora nuestro menú y agrega tus productos favoritos!</p>
                <a href="menu.php" class="btn btn-primary mt-3">
                    <i class="fas fa-utensils"></i> Ver Menú
                </a>
            </div>
        `;
        const btnConfirmar = document.getElementById('btnConfirmar');
        if (btnConfirmar) btnConfirmar.disabled = true;
        actualizarResumen();
        return;
    }

    let html = '<div class="table-responsive"><table class="table table-hover mb-0">';
    html += '<thead><tr><th>Producto</th><th>Precio</th><th width="120">Cantidad</th><th>Subtotal</th><th width="80">Acción</th></tr></thead><tbody>';

    // Verificar configuración
    const adminUrl = (typeof APP_CONFIG !== 'undefined' && APP_CONFIG.ADMIN_URL_BASE)
        ? APP_CONFIG.ADMIN_URL_BASE
        : 'http://localhost/www.sistemaadmalberco.com'; // Fallback seguro

    if (typeof APP_CONFIG === 'undefined') {
        console.error('APP_CONFIG no está definido. Verifique config.js.php');
    }

    carrito.forEach((item) => {
        const subtotal = item.precio * item.cantidad;

        // Construir URL de imagen de forma segura
        let imgHtml = '';
        if (item.imagen) {
            const imgSrc = `${adminUrl}/uploads/almacen/${item.imagen}`;
            imgHtml = `<img src="${imgSrc}" 
                      class="img-thumbnail mr-2" style="width: 60px; height: 60px; object-fit: cover;" 
                      onerror="this.src='../Assets/no-image.jpg'">`;
        }

        html += `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        ${imgHtml}
                        <strong>${item.nombre}</strong>
                    </div>
                </td>
                <td class="align-middle">S/ ${item.precio.toFixed(2)}</td>
                <td class="align-middle">
                    <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary" onclick="cambiarCantidad(${item.id}, ${item.cantidad - 1})">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" class="form-control text-center" value="${item.cantidad}" 
                               min="1" readonly style="max-width: 60px;">
                        <button class="btn btn-outline-secondary" onclick="cambiarCantidad(${item.id}, ${item.cantidad + 1})">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </td>
                <td class="align-middle font-weight-bold">S/ ${subtotal.toFixed(2)}</td>
                <td class="align-middle">
                    <button class="btn btn-danger btn-sm" onclick="eliminarItem(${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table></div>';
    container.innerHTML = html;

    const btnConfirmar = document.getElementById('btnConfirmar');
    if (btnConfirmar) btnConfirmar.disabled = false;
    actualizarResumen();
}

// Cambiar cantidad - usa updateQuantity de carrito.js
function cambiarCantidad(productId, nuevaCantidad) {
    if (nuevaCantidad < 1) return;
    updateQuantity(productId, nuevaCantidad);
    renderCarrito();
}

// Eliminar item - usa removeFromCart de carrito.js
function eliminarItem(productId) {
    const carrito = getCart();
    const item = carrito.find(i => i.id === productId);

    if (!item) return;

    Swal.fire({
        title: '¿Eliminar producto?',
        text: `¿Deseas eliminar "${item.nombre}" de tu carrito?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d32f2f',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            removeFromCart(productId);
            renderCarrito();

            Swal.fire({
                icon: 'success',
                title: 'Producto eliminado',
                text: 'El producto se eliminó de tu carrito',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}

// Vaciar carrito
const btnVaciarCarrito = document.getElementById('vaciarCarrito');
if (btnVaciarCarrito) {
    btnVaciarCarrito.addEventListener('click', function () {
        const carrito = getCart();
        if (carrito.length === 0) return;

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
                clearCart();
                renderCarrito();

                Swal.fire({
                    icon: 'success',
                    title: '¡Carrito vaciado!',
                    text: 'Todos los productos fueron eliminados',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });
}

// Actualizar resumen
function actualizarResumen() {
    const carrito = getCart();
    const subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);

    // Obtener tipo de pedido de los radio buttons
    const tipoPedidoRadio = document.querySelector('input[name="tipoPedido"]:checked');
    const tipoPedido = tipoPedidoRadio ? tipoPedidoRadio.value : '';

    const delivery = tipoPedido === 'delivery' ? costoDelivery : 0;
    const total = subtotal + delivery;

    const subtotalEl = document.getElementById('subtotalResumen');
    const deliveryEl = document.getElementById('deliveryResumen');
    const totalEl = document.getElementById('totalResumen');

    if (subtotalEl) subtotalEl.textContent = 'S/ ' + subtotal.toFixed(2);
    if (deliveryEl) deliveryEl.textContent = 'S/ ' + delivery.toFixed(2);
    if (totalEl) totalEl.textContent = 'S/ ' + total.toFixed(2);
}

// Cambio en tipo de pedido - actualizar resumen
document.querySelectorAll('input[name="tipoPedido"]').forEach(radio => {
    radio.addEventListener('change', actualizarResumen);
});

// Confirmar pedido
const formPedido = document.getElementById('formPedido');
if (formPedido) {
    formPedido.addEventListener('submit', async function (e) {
        e.preventDefault();

        const carrito = getCart();

        console.log('=== INICIO CONFIRMACIÓN DE PEDIDO ===');
        console.log('Carrito actual:', carrito);
        console.log('Cantidad de productos:', carrito.length);

        if (carrito.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Carrito Vacío',
                text: 'No tienes productos en tu carrito. ¡Explora nuestro menú y agrega tus favoritos!',
                confirmButtonText: 'Ver Menú',
                confirmButtonColor: '#d32f2f'
            }).then(() => {
                window.location.href = 'menu.php';
            });
            return;
        }

        // Obtener datos del formulario
        const tipoPedidoRadio = document.querySelector('input[name="tipoPedido"]:checked');
        const metodoPagoRadio = document.querySelector('input[name="metodoPago"]:checked');

        if (!tipoPedidoRadio) {
            Swal.fire({
                icon: 'warning',
                title: 'Tipo de pedido requerido',
                text: 'Por favor, selecciona cómo quieres tu pedido (Delivery, Para Llevar o En Local).',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#d32f2f'
            });
            return;
        }

        if (!metodoPagoRadio) {
            Swal.fire({
                icon: 'warning',
                title: 'Método de pago requerido',
                text: 'Por favor, selecciona tu método de pago.',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#d32f2f'
            });
            return;
        }

        const tipoPedido = tipoPedidoRadio.value;
        const metodoPago = metodoPagoRadio.value;
        const nombreCliente = document.getElementById('nombreCliente')?.value || 'Cliente';
        const telefonoCliente = document.getElementById('telefonoCliente')?.value || '999999999';
        const observaciones = document.getElementById('observaciones')?.value || '';

        // Datos específicos según tipo
        let direccion = null;
        let mesa = null;

        if (tipoPedido === 'delivery') {
            const dir = document.getElementById('direccionEntrega')?.value;
            const ref = document.getElementById('referenciaEntrega')?.value || '';
            const dist = document.getElementById('distritoEntrega')?.value;

            if (!dir || !dist) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Dirección requerida',
                    text: 'Por favor, ingresa tu dirección completa y distrito para continuar con el pedido.',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#d32f2f'
                });
                return;
            }

            direccion = `${dir}, ${dist}. Ref: ${ref}`;
        } else if (tipoPedido === 'mesa') {
            mesa = document.getElementById('numeroMesa')?.value;
            if (!mesa) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Mesa requerida',
                    text: 'Por favor, selecciona el número de mesa para continuar con el pedido.',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#d32f2f'
                });
                return;
            }
        }

        // Mostrar loading
        Swal.fire({
            title: 'Procesando tu pedido...',
            text: 'Estamos registrando tu pedido en nuestro sistema',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            // Calcular totales
            const subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            const delivery = tipoPedido === 'delivery' ? costoDelivery : 0;
            const total = subtotal + delivery;

            // Preparar datos del pedido
            const pedido = {
                tipo_pedido: tipoPedido,
                cliente: {
                    nombre: nombreCliente,
                    telefono: telefonoCliente,
                    direccion: direccion,
                    mesa: mesa,
                    email: null
                },
                productos: carrito.map(item => ({
                    id: item.id,
                    nombre: item.nombre,
                    precio: item.precio,
                    cantidad: item.cantidad,
                    observaciones: null
                })),
                metodo_pago: metodoPago,
                observaciones: observaciones,
                subtotal: subtotal,
                descuento: 0,
                costo_delivery: delivery,
                total: total
            };

            console.log('=== DATOS DEL PEDIDO A ENVIAR ===');
            console.log('Pedido completo:', JSON.stringify(pedido, null, 2));

            // Enviar al servidor
            const response = await fetch('procesar_pedido_directo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(pedido)
            });

            console.log('=== RESPUESTA DEL SERVIDOR ===');
            console.log('Response status:', response.status);

            const responseText = await response.text();
            console.log('Response text:', responseText);

            let data;
            try {
                data = JSON.parse(responseText);
                console.log('Response data:', data);
            } catch (e) {
                console.error('Error parsing JSON:', e);
                throw new Error('El servidor no devolvió un JSON válido');
            }

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Pedido Confirmado Exitosamente!',
                    html: `
                        <p style="font-size: 1.1em; margin-bottom: 15px;">Tu pedido ha sido registrado correctamente</p>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;">
                            <p><strong>Número de Pedido:</strong> <span style="color: #d32f2f; font-size: 1.2em;">${data.nro_pedido}</span></p>
                            <p><strong>Total a Pagar:</strong> <span style="color: #28a745; font-size: 1.2em;">S/ ${data.total.toFixed(2)}</span></p>
                        </div>
                        <p>${data.mensaje}</p>
                    `,
                    confirmButtonText: 'Ir al Inicio',
                    confirmButtonColor: '#28a745'
                }).then(() => {
                    // Limpiar carrito
                    clearCart();
                    window.location.href = '../index.php';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al Procesar el Pedido',
                    html: `
                        <p>${data.mensaje || data.error || 'Ocurrió un error al procesar tu pedido'}</p>
                        <p style="margin-top: 15px; color: #666;">Por favor, verifica tus datos e intenta nuevamente.</p>
                    `,
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#d32f2f'
                });
            }

        } catch (error) {
            console.error('Error completo:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de Conexión',
                html: `
                    <p>No se pudo conectar con el servidor.</p>
                    <p style="margin-top: 10px;">Por favor, verifica tu conexión a internet e intenta nuevamente.</p>
                    <small style="color: #999; margin-top: 15px; display: block;">Error técnico: ${error.message}</small>
                `,
                confirmButtonText: 'Reintentar',
                confirmButtonColor: '#d32f2f'
            });
        }
    });
}

// Escuchar eventos de actualización del carrito
window.addEventListener('cartUpdated', () => {
    renderCarrito();
});

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function () {
    console.log('=== PÁGINA PEDIDO CARGADA ===');
    const carrito = getCart();
    console.log('Carrito cargado:', carrito);
    console.log('Total productos:', carrito.length);

    renderCarrito();
});
