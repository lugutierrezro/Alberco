/**
 * ALBERCO - Advanced Interactions & Effects
 * Microinteracciones premium y efectos visuales
 */

// ============================================
// 1. RIPPLE EFFECT EN BOTONES
// ============================================
function createRipple(event) {
    const button = event.currentTarget;
    const ripple = document.createElement('span');
    const diameter = Math.max(button.clientWidth, button.clientHeight);
    const radius = diameter / 2;

    const rect = button.getBoundingClientRect();
    ripple.style.width = ripple.style.height = `${diameter}px`;
    ripple.style.left = `${event.clientX - rect.left - radius}px`;
    ripple.style.top = `${event.clientY - rect.top - radius}px`;
    ripple.classList.add('ripple-effect');

    const existingRipple = button.getElementsByClassName('ripple-effect')[0];
    if (existingRipple) {
        existingRipple.remove();
    }

    button.appendChild(ripple);
}

// Aplicar a todos los botones
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.btn-modern, .btn, button');
    buttons.forEach(button => {
        button.addEventListener('click', createRipple);
        if (!button.style.position || button.style.position === 'static') {
            button.style.position = 'relative';
        }
        button.style.overflow = 'hidden';
    });
});

// ============================================
// 2. TILT 3D EFFECT EN CARDS
// ============================================
class VanillaTilt {
    constructor(element, settings = {}) {
        this.element = element;
        this.settings = {
            max: settings.max || 15,
            perspective: settings.perspective || 1000,
            scale: settings.scale || 1.05,
            speed: settings.speed || 400,
            glare: settings.glare || false
        };
        this.init();
    }

    init() {
        this.element.style.transform = 'perspective(' + this.settings.perspective + 'px)';
        this.element.addEventListener('mouseenter', this.onMouseEnter.bind(this));
        this.element.addEventListener('mousemove', this.onMouseMove.bind(this));
        this.element.addEventListener('mouseleave', this.onMouseLeave.bind(this));
    }

    onMouseEnter(e) {
        this.element.style.willChange = 'transform';
    }

    onMouseMove(e) {
        const rect = this.element.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const percentX = (x - centerX) / centerX;
        const percentY = (y - centerY) / centerY;
        const rotateY = percentX * this.settings.max;
        const rotateX = -percentY * this.settings.max;

        this.element.style.transform = `perspective(${this.settings.perspective}px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(${this.settings.scale}, ${this.settings.scale}, ${this.settings.scale})`;
    }

    onMouseLeave(e) {
        this.element.style.transform = `perspective(${this.settings.perspective}px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
        this.element.style.willChange = 'auto';
    }
}

// Aplicar tilt a cards
document.addEventListener('DOMContentLoaded', function () {
    const cards = document.querySelectorAll('.product-card-premium, .product-card-menu, .value-card, .contact-card');
    cards.forEach(card => {
        new VanillaTilt(card, {
            max: 8,
            speed: 400,
            scale: 1.02
        });
    });
});

// ============================================
// 3. PARALLAX SCROLL
// ============================================
function initParallax() {
    const parallaxElements = document.querySelectorAll('[data-parallax]');

    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;

        parallaxElements.forEach(el => {
            const speed = el.dataset.parallax || 0.5;
            const yPos = -(scrolled * speed);
            el.style.transform = `translateY(${yPos}px)`;
        });
    });
}

document.addEventListener('DOMContentLoaded', initParallax);

// ============================================
// 4. SMOOTH SCROLL MEJORADO
// ============================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && href !== '#!') {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
});

// ============================================
// 5. CURSOR CON EFECTO GLOW/NUBLINA
// ============================================
function initGlowCursor() {
    const cursorGlow = document.createElement('div');
    cursorGlow.classList.add('cursor-glow');
    document.body.appendChild(cursorGlow);

    let mouseX = 0, mouseY = 0;
    let glowX = 0, glowY = 0;

    document.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
    });

    function animateGlow() {
        const distX = mouseX - glowX;
        const distY = mouseY - glowY;
        glowX += distX * 0.15;
        glowY += distY * 0.15;

        cursorGlow.style.left = glowX + 'px';
        cursorGlow.style.top = glowY + 'px';

        requestAnimationFrame(animateGlow);
    }

    animateGlow();

    // Expand glow on hover
    const hoverables = document.querySelectorAll('a, button, .btn, input, textarea, select, .product-card-premium, .product-card-menu');
    hoverables.forEach(el => {
        el.addEventListener('mouseenter', () => {
            cursorGlow.classList.add('cursor-hover');
        });
        el.addEventListener('mouseleave', () => {
            cursorGlow.classList.remove('cursor-hover');
        });
    });
}

// Activar solo en desktop
if (window.innerWidth > 768) {
    document.addEventListener('DOMContentLoaded', initGlowCursor);
}

// ============================================
// 6. LOADING ANIMATION CON LOGO ALBERCO
// ============================================
function initPageLoader() {
    // Detectar tema activo
    const temaActivo = document.body.dataset.tema || '';
    const isAnioNuevo = temaActivo === 'anio_nuevo';

    // Detectar si estamos en Vista/ o en raíz
    const isInVista = window.location.pathname.includes('/Vista/');
    const logoPath = isInVista ? '../Assets/imagenes/AbercoLogo.png' : 'Assets/imagenes/AbercoLogo.png';

    const loader = document.createElement('div');
    loader.classList.add('page-loader');

    if (isAnioNuevo) {
        // Loader especial para Año Nuevo
        loader.style.background = 'linear-gradient(135deg, #212121 0%, #0A0A0A 100%)';
        loader.innerHTML = `
            <div class="loader-content">
                <div class="loader-logo" style="margin-bottom: 2rem;">
                    <img src="${logoPath}" alt="Alberco" style="max-width: 200px; filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.5));" onerror="this.style.display='none'">
                </div>
                <h1 style="
                    font-size: 3rem; 
                    color: #FFD700; 
                    font-family: 'Playfair Display', serif;
                    font-weight: 900;
                    margin-bottom: 1rem;
                    text-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
                    animation: shimmer 2s ease-in-out infinite;
                ">🎉 ¡Feliz Año Nuevo! 🎉</h1>
                <p style="
                    font-size: 1.5rem; 
                    color: #C0C0C0;
                    font-weight: 300;
                    margin-bottom: 2rem;
                ">Empieza el año con nuestras ofertas</p>
                <div style="
                    width: 60px;
                    height: 60px;
                    margin: 0 auto;
                    border: 4px solid rgba(255, 215, 0, 0.2);
                    border-top-color: #FFD700;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                "></div>
            </div>
        `;
    } else {
        // Loader normal
        loader.innerHTML = `
            <div class="loader-content">
                <div class="loader-logo">
                    <img src="${logoPath}" alt="Alberco" class="loader-logo-img" onerror="this.style.display='none'">
                    <div class="loader-fire">
                        <span class="flame flame-1">🔥</span>
                        <span class="flame flame-2">🔥</span>
                        <span class="flame flame-3">🔥</span>
                    </div>
                </div>
                <p class="loader-text">Cargando sabor premium...</p>
            </div>
        `;
    }

    document.body.prepend(loader);

    // Tiempo de espera según tema (reducido para mejor UX)
    const displayTime = isAnioNuevo ? 1000 : 400;

    window.addEventListener('load', () => {
        setTimeout(() => {
            loader.classList.add('fade-out');
            setTimeout(() => loader.remove(), 500);
        }, displayTime);
    });
}

document.addEventListener('DOMContentLoaded', initPageLoader);

// ============================================
// 7. IMAGE ZOOM ON HOVER
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    const zoomImages = document.querySelectorAll('.product-card-image img, .gallery-item-modern img');
    zoomImages.forEach(img => {
        img.style.transition = 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
    });
});

// ============================================
// 8. NUMBER COUNTER ANIMATION (mejorado)
// ============================================
function animateValue(element, start, end, duration) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    const isDecimal = end % 1 !== 0;

    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        element.textContent = isDecimal ? current.toFixed(1) : Math.floor(current);
    }, 16);
}

// ============================================
// 9. NAVBAR HIDE ON SCROLL DOWN / SHOW ON SCROLL UP
// ============================================
let lastScrollTop = 0;
const navbar = document.getElementById('mainNavbar');

window.addEventListener('scroll', function () {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

    if (scrollTop > lastScrollTop && scrollTop > 100) {
        // Scrolling down
        if (navbar) navbar.style.transform = 'translateY(-100%)';
    } else {
        // Scrolling up
        if (navbar) navbar.style.transform = 'translateY(0)';
    }

    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
}, false);

// ============================================
// 10. SHAKE EFFECT ON ERROR
// ============================================
function shakeElement(element) {
    element.classList.add('shake-animation');
    setTimeout(() => element.classList.remove('shake-animation'), 500);
}

// ============================================
// 11. LAZY BACKGROUND IMAGES
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    const lazyBackgrounds = document.querySelectorAll('[data-bg]');

    const bgObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const bg = entry.target.dataset.bg;
                entry.target.style.backgroundImage = `url(${bg})`;
                bgObserver.unobserve(entry.target);
            }
        });
    });

    lazyBackgrounds.forEach(el => bgObserver.observe(el));
});

// ============================================
// 12. FORM INPUT ANIMATIONS
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.input-modern, .select-modern, .textarea-modern');

    inputs.forEach(input => {
        input.addEventListener('focus', function () {
            this.parentElement.classList.add('input-focused');
        });

        input.addEventListener('blur', function () {
            this.parentElement.classList.remove('input-focused');
            if (this.value) {
                this.parentElement.classList.add('input-filled');
            } else {
                this.parentElement.classList.remove('input-filled');
            }
        });
    });
});

// ============================================
// 13. STICKY HEADER SHADOW
// ============================================
window.addEventListener('scroll', function () {
    const navbar = document.getElementById('mainNavbar');
    if (navbar) {
        if (window.scrollY > 50) {
            navbar.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)';
        } else {
            navbar.style.boxShadow = '0 2px 8px rgba(0,0,0,0.08)';
        }
    }
});

// ============================================
// 14. ADD TO CART ANIMATION
// ============================================
function animateAddToCart(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check me-2"></i>Agregado!';
    button.style.background = '#00C853';
    button.disabled = true;

    setTimeout(() => {
        button.innerHTML = originalText;
        button.style.background = '';
        button.disabled = false;
    }, 1500);

    // Animate cart badge
    const cartBadge = document.getElementById('cartBadge');
    if (cartBadge) {
        cartBadge.style.animation = 'none';
        setTimeout(() => {
            cartBadge.style.animation = 'pulse 0.5s ease-in-out';
        }, 10);
    }
}

// ============================================
// 15. REVEAL ON SCROLL (adicional a AOS)
// ============================================
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('revealed');
        }
    });
}, { threshold: 0.1 });

document.addEventListener('DOMContentLoaded', function () {
    const revealElements = document.querySelectorAll('.reveal-on-scroll');
    revealElements.forEach(el => revealObserver.observe(el));
});

// ============================================
// EXPORT FUNCTIONS (si se necesitan)
// ============================================
window.AlbercoEffects = {
    shake: shakeElement,
    animateAddToCart: animateAddToCart,
    createRipple: createRipple
};

console.log('✨ Alberco Advanced Effects Loaded');
