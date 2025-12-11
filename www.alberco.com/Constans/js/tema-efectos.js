/**
 * Efectos Animados para Temas
 * Alberco - Sistema de Personalización
 */

const TemaEfectos = {
    /**
     * Efecto de nieve cayendo (Navidad)
     */
    nieve: function () {
        const container = document.createElement('div');
        container.id = 'snow-container';
        container.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
            overflow: hidden;
        `;

        document.body.appendChild(container);

        // Crear copos de nieve
        for (let i = 0; i < 50; i++) {
            setTimeout(() => {
                const snowflake = document.createElement('div');
                snowflake.innerHTML = '❄';
                snowflake.style.cssText = `
                    position: absolute;
                    top: -20px;
                    left: ${Math.random() * 100}%;
                    font-size: ${Math.random() * 20 + 10}px;
                    color: rgba(255, 255, 255, 0.8);
                    animation: fall ${Math.random() * 3 + 2}s linear infinite;
                    animation-delay: ${Math.random() * 2}s;
                `;
                container.appendChild(snowflake);
            }, i * 100);
        }

        // CSS Animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fall {
                to {
                    transform: translateY(100vh) rotate(360deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    },

    /**
     * Efecto de fuegos artificiales (Año Nuevo)
     */
    fuegosArtificiales: function () {
        const canvas = document.createElement('canvas');
        canvas.id = 'fireworks-canvas';
        canvas.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
        `;
        document.body.appendChild(canvas);

        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const particles = [];
        const colors = ['#FFD700', '#FF6B35', '#F7931E', '#FFC107', '#FF4757'];

        class Particle {
            constructor(x, y, color) {
                this.x = x;
                this.y = y;
                this.color = color;
                this.velocity = {
                    x: (Math.random() - 0.5) * 8,
                    y: (Math.random() - 0.5) * 8
                };
                this.alpha = 1;
                this.decay = Math.random() * 0.03 + 0.015;
            }

            draw() {
                ctx.save();
                ctx.globalAlpha = this.alpha;
                ctx.fillStyle = this.color;
                ctx.beginPath();
                ctx.arc(this.x, this.y, 3, 0, Math.PI * 2);
                ctx.fill();
                ctx.restore();
            }

            update() {
                this.velocity.y += 0.1;
                this.x += this.velocity.x;
                this.y += this.velocity.y;
                this.alpha -= this.decay;
            }
        }

        function createFirework() {
            const x = Math.random() * canvas.width;
            const y = Math.random() * canvas.height / 2;
            const color = colors[Math.floor(Math.random() * colors.length)];

            for (let i = 0; i < 30; i++) {
                particles.push(new Particle(x, y, color));
            }
        }

        function animate() {
            // Limpiar canvas con transparencia total (sin fondo negro)
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            particles.forEach((particle, index) => {
                if (particle.alpha <= 0) {
                    particles.splice(index, 1);
                } else {
                    particle.update();
                    particle.draw();
                }
            });

            requestAnimationFrame(animate);
        }

        animate();
        setInterval(createFirework, 1000);
    },

    /**
     * Efecto de corazones flotantes (San Valentín)
     */
    corazones: function () {
        const container = document.createElement('div');
        container.id = 'hearts-container';
        container.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
            overflow: hidden;
        `;

        document.body.appendChild(container);

        for (let i = 0; i < 20; i++) {
            setTimeout(() => {
                const heart = document.createElement('div');
                heart.innerHTML = '💝';
                heart.style.cssText = `
                    position: absolute;
                    bottom: -50px;
                    left: ${Math.random() * 100}%;
                    font-size: ${Math.random() * 30 + 20}px;
                    animation: float ${Math.random() * 4 + 3}s ease-in infinite;
                    animation-delay: ${Math.random() * 2}s;
                    opacity: 0.7;
                `;
                container.appendChild(heart);
            }, i * 200);
        }

        const style = document.createElement('style');
        style.textContent = `
            @keyframes float {
                0% {
                    transform: translateY(0) rotate(0deg);
                    opacity: 0.7;
                }
                50% {
                    opacity: 1;
                }
                100% {
                    transform: translateY(-100vh) rotate(360deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    },

    /**
     * Efecto de murciélagos (Halloween)
     */
    murcielagos: function () {
        const container = document.createElement('div');
        container.id = 'bats-container';
        container.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
            overflow: hidden;
        `;

        document.body.appendChild(container);

        for (let i = 0; i < 15; i++) {
            setTimeout(() => {
                const bat = document.createElement('div');
                bat.innerHTML = '🦇';
                bat.style.cssText = `
                    position: absolute;
                    top: ${Math.random() * 50}%;
                    left: -50px;
                    font-size: ${Math.random() * 20 + 15}px;
                    animation: fly ${Math.random() * 5 + 5}s linear infinite;
                    animation-delay: ${Math.random() * 3}s;
                `;
                container.appendChild(bat);
            }, i * 300);
        }

        const style = document.createElement('style');
        style.textContent = `
            @keyframes fly {
                0% {
                    transform: translateX(0) translateY(0);
                }
                25% {
                    transform: translateX(25vw) translateY(-20px);
                }
                50% {
                    transform: translateX(50vw) translateY(20px);
                }
                75% {
                    transform: translateX(75vw) translateY(-10px);
                }
                100% {
                    transform: translateX(100vw) translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    },

    /**
     * Efecto de confeti (Fiestas Patrias)
     */
    confeti: function () {
        const container = document.createElement('div');
        container.id = 'confetti-container';
        container.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
            overflow: hidden;
        `;

        document.body.appendChild(container);

        const colors = ['#d32f2f', '#ffffff'];

        for (let i = 0; i < 100; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.style.cssText = `
                    position: absolute;
                    top: -10px;
                    left: ${Math.random() * 100}%;
                    width: ${Math.random() * 10 + 5}px;
                    height: ${Math.random() * 10 + 5}px;
                    background-color: ${colors[Math.floor(Math.random() * colors.length)]};
                    animation: confetti-fall ${Math.random() * 3 + 2}s linear infinite;
                    animation-delay: ${Math.random() * 2}s;
                    opacity: 0.8;
                `;
                container.appendChild(confetti);
            }, i * 50);
        }

        const style = document.createElement('style');
        style.textContent = `
            @keyframes confetti-fall {
                to {
                    transform: translateY(100vh) rotate(${Math.random() * 720}deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    },

    /**
     * Efecto de olas de verano
     */
    olas: function () {
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('id', 'waves-svg');
        svg.style.cssText = `
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 150px;
            pointer-events: none;
            z-index: 9999;
        `;

        svg.innerHTML = `
            <defs>
                <linearGradient id="wave-gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" style="stop-color:#00bcd4;stop-opacity:0.3" />
                    <stop offset="100%" style="stop-color:#00bcd4;stop-opacity:0.1" />
                </linearGradient>
            </defs>
            <path fill="url(#wave-gradient)">
                <animate attributeName="d" dur="5s" repeatCount="indefinite"
                    values="M0,50 Q250,0 500,50 T1000,50 T1500,50 T2000,50 V150 H0 Z;
                            M0,50 Q250,100 500,50 T1000,50 T1500,50 T2000,50 V150 H0 Z;
                            M0,50 Q250,0 500,50 T1000,50 T1500,50 T2000,50 V150 H0 Z"/>
            </path>
        `;

        document.body.appendChild(svg);
    },

    /**
     * Limpiar todos los efectos
     */
    limpiar: function () {
        const containers = [
            'snow-container',
            'fireworks-canvas',
            'hearts-container',
            'bats-container',
            'confetti-container',
            'waves-svg'
        ];

        containers.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.remove();
            }
        });
    }
};

// Auto-inicializar según tema activo
window.addEventListener('DOMContentLoaded', function () {
    // Obtener tema desde el atributo data del body o desde una variable global
    const temaActivo = document.body.dataset.tema || window.TEMA_ACTIVO;

    if (!temaActivo) return;

    // Mapeo de temas a efectos
    const efectosPorTema = {
        'navidad': 'nieve',
        'anio_nuevo': 'fuegosArtificiales',
        'san_valentin': 'corazones',
        'halloween': 'murcielagos',
        'fiestas_patrias': 'confeti',
        'verano': 'olas'
    };

    const efecto = efectosPorTema[temaActivo];

    if (efecto && TemaEfectos[efecto]) {
        // Esperar un poco para que la página cargue
        setTimeout(() => {
            TemaEfectos[efecto]();
        }, 500);
    }
});

// Exportar para uso manual si es necesario
window.TemaEfectos = TemaEfectos;
