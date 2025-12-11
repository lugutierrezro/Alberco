// ====================================
// PROCESAR PEDIDO - VERSIÓN SIMPLE
// Solo procesa el pedido, el carrito lo maneja carrito.js
// ====================================

const formPedido = document.getElementById('formPedido');
if (formPedido) {
    formPedido.addEventListener('submit', async (e) => {
        e.preventDefault();

        const carrito = JSON.parse(localStorage.getItem('carrito') || '[]');

        if (carrito.length === 0) {
            Swal.fire('Error', 'Tu carrito está vacío', 'error');
            return;
        }

        // Obtener datos del formulario
        const tipoPedido = document.querySelector('input[name="tipoPedido"]:checked')?.value;
        const metodoPago = document.querySelector('input[name="metodoPago"]:checked')?.value;

        if (!tipoPedido) {
            Swal.fire('Error', 'Selecciona el tipo de pedido', 'error');
            return;
        }

        if (!metodoPago) {
            Swal.fire('Error', 'Selecciona el método de pago', 'error');
            return;
        }

        // Datos específicos según tipo
        let direccion = null;
        let mesa = null;

        if (tipoPedido === 'delivery') {
            const dir = document.getElementById('direccionEntrega')?.value;
            const ref = document.getElementById('referenciaEntrega')?.value || '';
            const dist = document.getElementById('distritoEntrega')?.value;

            if (!dir || !dist) {
                Swal.fire('Error', 'Completa la dirección de entrega', 'error');
                return;
            }

            direccion = `${dir}, ${dist}. Ref: ${ref}`;
        } else if (tipoPedido === 'mesa') {
            mesa = document.getElementById('numeroMesa')?.value;
            if (!mesa) {
                Swal.fire('Error', 'Selecciona una mesa', 'error');
                return;
            }
        }

        // Preparar pedido
        const subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
        const costoDelivery = tipoPedido === 'delivery' ? 5.00 : 0;
        const total = subtotal + costoDelivery;

        const pedido = {
            tipo_pedido: tipoPedido,
            cliente: {
                nombre: document.getElementById('nombreCliente')?.value || 'Cliente',
                telefono: document.getElementById('telefonoCliente')?.value || '999999999',
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
            observaciones: document.getElementById('observaciones')?.value || '',
            subtotal: subtotal,
            descuento: 0,
            costo_delivery: costoDelivery,
            total: total
        };

        // Mostrar loading
        Swal.fire({
            title: 'Procesando...',
            text: 'Enviando tu pedido',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            const response = await fetch('procesar_pedido_directo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(pedido)
            });

            const responseText = await response.text();
            let data;

            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.error('Error parsing JSON:', responseText);
                throw new Error('Respuesta inválida del servidor');
            }

            if (data.success) {
                localStorage.removeItem('carrito');

                Swal.fire({
                    icon: 'success',
                    title: '¡Pedido Realizado!',
                    html: `
                        <p>Tu pedido <strong>${data.nro_pedido}</strong> ha sido registrado exitosamente.</p>
                        <p>Total: <strong>S/ ${data.total}</strong></p>
                    `,
                    confirmButtonText: 'Ver Menú'
                }).then(() => {
                    window.location.href = 'menu.php';
                });
            } else {
                Swal.fire('Error', data.error || data.mensaje || 'Error al procesar pedido', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudo conectar con el servidor: ' + error.message, 'error');
        }
    });
}
