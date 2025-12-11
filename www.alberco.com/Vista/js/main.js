// ===================================
// ALBERCO - Funcionalidad General
// main.js
// ===================================

document.addEventListener('DOMContentLoaded', function () {
    initNavbar();
    initHeroSlider();
    initLightbox();
    initContactForm();
    initScrollReveal();
});

// === NAVBAR STICKY ===
function initNavbar() {
    const navbar = document.getElementById('navbar');
    const hamburger = document.getElementById('hamburger');
    const navbarMenu = document.getElementById('navbarMenu');

    // Sticky navbar on scroll
    window.addEventListener('scroll', function () {
        if (navbar) {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }
    });

    // Hamburger menu toggle
    if (hamburger && navbarMenu) {
        hamburger.addEventListener('click', function () {
            this.classList.toggle('active');
            navbarMenu.classList.toggle('active');
        });

        // Close menu when clicking on a link
        const menuLinks = navbarMenu.querySelectorAll('a');
        menuLinks.forEach(link => {
            link.addEventListener('click', function () {
                hamburger.classList.remove('active');
                navbarMenu.classList.remove('active');
            });
        });

        // Close menu when clicking outside
        document.addEventListener('click', function (e) {
            if (!hamburger.contains(e.target) && !navbarMenu.contains(e.target)) {
                hamburger.classList.remove('active');
                navbarMenu.classList.remove('active');
            }
        });
    }
}

// === HERO SLIDER ===
function initHeroSlider() {
    const slides = document.querySelectorAll('.slide');
    const prevBtn = document.getElementById('prevSlide');
    const nextBtn = document.getElementById('nextSlide');
    const dotsContainer = document.getElementById('sliderDots');

    if (slides.length === 0) return;

    let currentSlide = 0;
    let slideInterval;

    // Create dots
    if (dotsContainer) {
        slides.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.classList.add('slider-dot');
            if (index === 0) dot.classList.add('active');
            dot.addEventListener('click', () => goToSlide(index));
            dotsContainer.appendChild(dot);
        });
    }

    function showSlide(n) {
        slides.forEach(slide => slide.classList.remove('active'));

        currentSlide = (n + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');

        // Update dots
        const dots = document.querySelectorAll('.slider-dot');
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentSlide);
        });
    }

    function nextSlide() {
        showSlide(currentSlide + 1);
    }

    function prevSlide() {
        showSlide(currentSlide - 1);
    }

    function goToSlide(n) {
        showSlide(n);
        resetInterval();
    }

    function startInterval() {
        slideInterval = setInterval(nextSlide, 5000);
    }

    function resetInterval() {
        clearInterval(slideInterval);
        startInterval();
    }

    // Event listeners
    if (prevBtn) prevBtn.addEventListener('click', () => {
        prevSlide();
        resetInterval();
    });

    if (nextBtn) nextBtn.addEventListener('click', () => {
        nextSlide();
        resetInterval();
    });

    // Pause on hover
    const sliderContainer = document.querySelector('.hero-slider');
    if (sliderContainer) {
        sliderContainer.addEventListener('mouseenter', () => clearInterval(slideInterval));
        sliderContainer.addEventListener('mouseleave', startInterval);
    }

    // Start auto-advance
    startInterval();
}

// === LIGHTBOX ===
let currentImageIndex = 0;
let galleryImages = [];

function initLightbox() {
    const galleryItems = document.querySelectorAll('.gallery-item img');

    if (galleryItems.length > 0) {
        galleryImages = Array.from(galleryItems).map(img => img.src);
    }
}

function openLightbox(index) {
    currentImageIndex = index;
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightboxImage');

    if (lightbox && lightboxImage && galleryImages[index]) {
        lightboxImage.src = galleryImages[index];
        lightbox.style.display = 'block';
    }
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    if (lightbox) {
        lightbox.style.display = 'none';
    }
}

function nextImage() {
    currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
    const lightboxImage = document.getElementById('lightboxImage');
    if (lightboxImage) {
        lightboxImage.src = galleryImages[currentImageIndex];
    }
}

function prevImage() {
    currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
    const lightboxImage = document.getElementById('lightboxImage');
    if (lightboxImage) {
        lightboxImage.src = galleryImages[currentImageIndex];
    }
}

// Close lightbox with ESC key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeLightbox();
    } else if (e.key === 'ArrowLeft') {
        prevImage();
    } else if (e.key === 'ArrowRight') {
        nextImage();
    }
});

// === CONTACT FORM ===
function initContactForm() {
    const form = document.getElementById('contactForm');
    if (!form) return;

    form.addEventListener('submit', handleContactSubmit);
}

function handleContactSubmit(e) {
    e.preventDefault();

    const name = document.getElementById('contactName')?.value;
    const email = document.getElementById('contactEmail')?.value;
    const phone = document.getElementById('contactPhone')?.value;
    const message = document.getElementById('contactMessage')?.value;

    // Validate
    if (!name || !email || !message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Campos Requeridos',
                text: 'Por favor, completa todos los campos requeridos (nombre, correo y mensaje).',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#d32f2f'
            });
        } else {
            alert('Por favor completa todos los campos requeridos');
        }
        return false;
    }

    // Validate email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Correo Inválido',
                text: 'Por favor, ingresa un correo electrónico válido (ejemplo: usuario@dominio.com).',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#d32f2f'
            });
        } else {
            alert('Por favor ingresa un correo electrónico válido');
        }
        return false;
    }

    // Here you would send the data to the backend
    // For now, just show success message

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: '¡Mensaje Enviado!',
            text: 'Gracias por contactarnos. Te responderemos a la brevedad.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#28a745'
        });
    } else {
        const successMsg = document.getElementById('contactSuccess');
        if (successMsg) {
            successMsg.style.display = 'flex';
            setTimeout(() => {
                successMsg.style.display = 'none';
            }, 5000);
        }
    }

    // Reset form
    e.target.reset();

    return false;
}

// === SCROLL REVEAL ===
function initScrollReveal() {
    const elements = document.querySelectorAll('.product-card, .category-card, .feature-box, .gallery-item');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '0';
                entry.target.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    entry.target.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, 100);

                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });

    elements.forEach(el => observer.observe(el));
}

// === SMOOTH SCROLL ===
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href === '#' || href === '') return;

        e.preventDefault();
        const target = document.querySelector(href);

        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// === UTILITY FUNCTIONS ===
function formatPrice(price) {
    return `S/ ${parseFloat(price).toFixed(2)}`;
}

function validatePhone(phone) {
    const phoneRegex = /^[0-9]{9}$/;
    return phoneRegex.test(phone.replace(/\s/g, ''));
}

function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// === SCROLL TO TOP ===
window.addEventListener('scroll', function () {
    const scrollBtn = document.getElementById('scrollToTop');
    if (scrollBtn) {
        if (window.scrollY > 300) {
            scrollBtn.style.display = 'flex';
        } else {
            scrollBtn.style.display = 'none';
        }
    }
});

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// === LOADING STATE ===
function showLoading(element) {
    if (element) {
        element.disabled = true;
        element.innerHTML = '<span class="loading"></span> Cargando...';
    }
}

function hideLoading(element, text) {
    if (element) {
        element.disabled = false;
        element.innerHTML = text;
    }
}

// === ERROR HANDLING ===
window.addEventListener('error', function (e) {
    console.error('Error:', e.error);
});

// === PREVENT FORM RESUBMISSION ===
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// === FORM PEDIDO (DESACTIVADO - Ahora se maneja en pedido.js) ===
// Este código antiguo está comentado porque ahora usamos el sistema de carrito con localStorage
// manejado por pedido.js. Dejarlo activo causa conflictos.
/*
const formPedido = document.getElementById('formPedido');
if (formPedido) {
    formPedido.addEventListener('submit', function (e) {
        e.preventDefault();

        const direccion = document.getElementById('direccion')?.value;
        const productosSeleccionados = [];

        document.querySelectorAll('input[name="producto[]"]:checked').forEach(function (checkbox) {
            const id_producto = checkbox.value;
            const precio = parseFloat(checkbox.getAttribute('data-precio'));
            const cantidadInput = document.querySelector(`input[name="cantidad_${id_producto}"]`);
            const cantidad = cantidadInput ? parseInt(cantidadInput.value) : 1;
            const nombre = checkbox.getAttribute('data-nombre');

            productosSeleccionados.push({
                id_producto: id_producto,
                cantidad: cantidad,
                precio_unitario: precio,
                nombre: nombre
            });
        });

        if (productosSeleccionados.length === 0) {
            alert('Por favor, seleccione al menos un producto.');
            return;
        }

        const pedidoData = {
            direccion: direccion,
            productos: productosSeleccionados,
            total: productosSeleccionados.reduce((acc, p) => acc + p.precio_unitario * p.cantidad, 0)
        };

        // Aquí se hace llamada AJAX a backend para guardar el pedido (ejemplo simplificado)
        fetch('pedido_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(pedidoData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const mensaje = document.getElementById('mensaje');
                    if (mensaje) {
                        mensaje.innerHTML = 'Pedido realizado con éxito. ID: ' + data.pedidoId;
                    }
                    formPedido.reset();
                } else {
                    const mensaje = document.getElementById('mensaje');
                    if (mensaje) {
                        mensaje.innerHTML = 'Error al realizar el pedido.';
                    }
                }
            })
            .catch(err => {
                const mensaje = document.getElementById('mensaje');
                if (mensaje) {
                    mensaje.innerHTML = 'Error de red.';
                }
                console.error(err);
            });
    });
}
*/

// === FORM SEGUIMIENTO (only on seguimiento page) ===
const formSeguimiento = document.getElementById('formSeguimiento');
if (formSeguimiento) {
    formSeguimiento.addEventListener('submit', function (e) {
        e.preventDefault();

        const pedidoId = document.getElementById('pedidoId')?.value;
        const resultadoDiv = document.getElementById('resultadoSeguimiento');
        const mapDiv = document.getElementById('map');

        if (resultadoDiv) resultadoDiv.innerHTML = '';
        if (mapDiv) mapDiv.style.display = 'none';

        fetch(`seguimiento_api.php?pedidoId=${pedidoId}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    if (resultadoDiv) {
                        resultadoDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                    }
                    return;
                }

                // Mostrar estado actual y historial
                let html = `<h4>Estado Actual: <span class="badge bg-info text-dark">${data.pedido.estado}</span></h4>`;
                html += `<h5 class="mt-3">Historial de Seguimiento:</h5><ul class="list-group">`;

                data.seguimiento.forEach(s => {
                    html += `<li class="list-group-item">
                                <strong>${new Date(s.fecha_estado).toLocaleString()}</strong><br>
                                Estado: ${s.estado}<br>
                                ${s.ubicacion_actual ? `Ubicación: ${s.ubicacion_actual}<br>` : ''}
                                ${s.descripcion ? `Comentario: ${s.descripcion}` : ''}
                             </li>`;
                });
                html += '</ul>';

                if (resultadoDiv) resultadoDiv.innerHTML = html;

                // Buscar última ubicación válida para mapa
                const ultimoConUbicacion = [...data.seguimiento].reverse().find(s => s.ubicacion_actual);

                if (ultimoConUbicacion && window.initMap && mapDiv) {
                    mapDiv.style.display = 'block';
                    window.initMap(ultimoConUbicacion.ubicacion_actual);
                }
            })
            .catch(() => {
                if (resultadoDiv) {
                    resultadoDiv.innerHTML = `<div class="alert alert-danger">Error al obtener el seguimiento. Intente nuevamente.</div>`;
                }
            });
    });
}



