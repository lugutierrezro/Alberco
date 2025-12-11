<?php
$pageTitle = "Contacto - ALBERCO Pollería y Chifa";
include '../includes/header.php';
?>

<style>
/* Contact Page Styles */
.contact-hero {
    background: linear-gradient(135deg, var(--dark) 0%, var(--dark-80) 100%);
    padding: var(--space-3xl) 0;
    position: relative;
    overflow: hidden;
}

.contact-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('../Assets/imagenes/fondo3.jpg') center/cover;
    opacity: 0.1;
}

.contact-card {
    background: var(--light);
    border-radius: var(--radius-xl);
    padding: var(--space-xl);
    box-shadow: var(--shadow-xl);
    height: 100%;
}

.contact-icon-box {
    width: 60px;
    height: 60px;
    background: var(--gradient-primary);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--light);
    font-size: 1.5rem;
    margin-bottom: var(--space-md);
}

.contact-form {
    background: var(--light);
    border-radius: var(--radius-xl);
    padding: var(--space-2xl);
    box-shadow: var(--shadow-xl);
}

.form-group-modern {
    margin-bottom: var(--space-lg);
}

.form-label-modern {
    display: block;
    font-weight: 600;
    margin-bottom: var(--space-xs);
    color: var(--dark);
}

.map-container {
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-xl);
    height: 500px;
}

.map-container iframe {
    width: 100%;
    height: 100%;
    border: 0;
}
</style>

<!-- Hero Section -->
<section class="contact-hero">
    <div class="container-modern text-center text-white">
        <div data-aos="fade-up">
            <h1 class="display-2 fw-bold mb-4">
                <span class="text-gradient" style="background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Contáctanos</span>
            </h1>
            <p class="lead" style="color: var(--light-80); max-width: 700px; margin: 0 auto;">
                Estamos aquí para atenderte. Escríbenos y te responderemos lo más pronto posible
            </p>
        </div>
    </div>
</section>

<!-- Contact Info Cards -->
<section class="section-spacing" style="background: var(--light-95);">
    <div class="container-modern">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="0">
                <div class="contact-card text-center">
                    <div class="contact-icon-box mx-auto">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Ubicación</h3>
                    <p class="text-muted">
                        Av. 5 de Agosto Mz. A1 Lt.13<br>
                        Anexo 8, Jicamarca<br>
                        Lima, Perú
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="contact-card text-center">
                    <div class="contact-icon-box mx-auto">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Teléfono</h3>
                    <p class="text-muted mb-2">
                        <a href="tel:012345678" class="text-decoration-none" style="color: var(--dark);">
                            (01) 234-5678
                        </a>
                    </p>
                    <p class="text-muted">
                        <a href="tel:987654321" class="text-decoration-none" style="color: var(--dark);">
                            987 654 321
                        </a>
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="contact-card text-center">
                    <div class="contact-icon-box mx-auto">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Horario</h3>
                    <p class="text-muted mb-1">
                        <strong>Lunes a Domingo</strong>
                    </p>
                    <p class="text-muted">
                        11:00 AM - 11:00 PM
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form & Map -->
<section class="section-spacing">
    <div class="container-modern">
        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-6" data-aos="fade-right">
                <div class="contact-form">
                    <h2 class="display-5 fw-bold mb-4">
                        Envíanos un <span class="text-gradient">Mensaje</span>
                    </h2>
                    <p class="text-muted mb-4">
                        Completa el formulario y nos pondremos en contacto contigo a la brevedad
                    </p>

                    <form id="contactForm">
                        <div class="form-group-modern">
                            <label class="form-label-modern">Nombre Completo</label>
                            <input type="text" class="input-modern" id="nombre" placeholder="Tu nombre" required>
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Email</label>
                            <input type="email" class="input-modern" id="email" placeholder="tu@email.com" required>
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Teléfono</label>
                            <input type="tel" class="input-modern" id="telefono" placeholder="987 654 321" required>
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Asunto</label>
                            <select class="select-modern" id="asunto" required>
                                <option value="">Selecciona un asunto...</option>
                                <option value="consulta">Consulta General</option>
                                <option value="pedido">Pedidos</option>
                                <option value="reclamo">Reclamo</option>
                                <option value="sugerencia">Sugerencia</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Mensaje</label>
                            <textarea class="textarea-modern" id="mensaje" placeholder="Escribe tu mensaje aquí..." rows="5" required></textarea>
                        </div>

                        <button type="submit" class="btn-modern btn-primary w-100">
                            <i class="fas fa-paper-plane me-2"></i>
                            Enviar Mensaje
                        </button>
                    </form>
                </div>
            </div>

            <!-- Map -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="map-container">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3900.9340315447693!2d-76.91252352487541!3d-12.01847228817698!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9105cef5a7e6fca3%3A0x9e3f14b4adbc2b16!2s2376%2BXQ8%2C%20Lurigancho-Chosica%2015461!5e0!3m2!1ses!2spe!4v1730347000000!5m2!1ses!2spe" 
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>

                <!-- Social Media -->
                <div class="mt-4 text-center">
                    <h3 class="fw-bold mb-3">Síguenos en Redes Sociales</h3>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="#" class="social-link" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="TikTok">
                            <i class="fab fa-tiktok"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section-spacing" style="background: var(--light-95);">
    <div class="container-modern">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4 fw-bold mb-3">
                Preguntas <span class="text-gradient">Frecuentes</span>
            </h2>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="card-modern mb-3" data-aos="fade-up" data-aos-delay="0">
                        <div class="p-4" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#faq1">
                            <h5 class="fw-bold mb-0">
                                <i class="fas fa-question-circle text-primary me-2"></i>
                                ¿Cuál es el tiempo de entrega?
                            </h5>
                        </div>
                        <div id="faq1" class="collapse show" data-bs-parent="#faqAccordion">
                            <div class="p-4 pt-0">
                                <p class="text-muted mb-0">
                                    El tiempo de entrega varía según la zona: 30-45 minutos para delivery, 
                                    15-20 minutos para para llevar, y servicio inmediato en local.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card-modern mb-3" data-aos="fade-up" data-aos-delay="100">
                        <div class="p-4" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#faq2">
                            <h5 class="fw-bold mb-0">
                                <i class="fas fa-question-circle text-primary me-2"></i>
                                ¿Cuáles son los métodos de pago?
                            </h5>
                        </div>
                        <div id="faq2" class="collapse" data-bs-parent="#faqAccordion">
                            <div class="p-4 pt-0">
                                <p class="text-muted mb-0">
                                    Aceptamos efectivo, Yape, Plin y tarjetas de crédito/débito. 
                                    También puedes pagar contra entrega.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card-modern mb-3" data-aos="fade-up" data-aos-delay="200">
                        <div class="p-4" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#faq3">
                            <h5 class="fw-bold mb-0">
                                <i class="fas fa-question-circle text-primary me-2"></i>
                                ¿Tienen servicio a domicilio?
                            </h5>
                        </div>
                        <div id="faq3" class="collapse" data-bs-parent="#faqAccordion">
                            <div class="p-4 pt-0">
                                <p class="text-muted mb-0">
                                    Sí, tenemos servicio de delivery en toda la zona este de Lima. 
                                    Consulta disponibilidad para tu distrito.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Contact Form Submission
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        nombre: document.getElementById('nombre').value,
        email: document.getElementById('email').value,
        telefono: document.getElementById('telefono').value,
        asunto: document.getElementById('asunto').value,
        mensaje: document.getElementById('mensaje').value
    };
    
    // Simulate submission
    Swal.fire({
        icon: 'success',
        title: '¡Mensaje Enviado!',
        text: 'Gracias por contactarnos. Te responderemos pronto.',
        confirmButtonColor: '#FF3D00'
    }).then(() => {
        this.reset();
    });
    
    // TODO: Implement actual form submission to backend
    console.log('Form data:', formData);
});
</script>

<?php include '../includes/footer.php'; ?>
