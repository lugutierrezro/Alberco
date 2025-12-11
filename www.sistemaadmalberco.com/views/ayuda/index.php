<?php
include ('../../services/database/config.php');
include ('../../contans/layout/sesion.php');
include ('../../contans/layout/parte1.php');
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-question-circle mr-2 text-orange"></i>Centro de Ayuda</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item active">Ayuda</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Categorías de Ayuda -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-widget">
                        <div class="card-body text-center">
                            <i class="fas fa-book fa-4x text-primary mb-3"></i>
                            <h5>Guías y Tutoriales</h5>
                            <p class="text-muted">Aprende a usar el sistema paso a paso</p>
                            <button class="btn btn-primary btn-sm" onclick="alert('Guías próximamente disponibles')">
                                Ver Guías
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-widget">
                        <div class="card-body text-center">
                            <i class="fas fa-video fa-4x text-danger mb-3"></i>
                            <h5>Videos Tutoriales</h5>
                            <p class="text-muted">Mira videos explicativos del sistema</p>
                            <button class="btn btn-danger btn-sm" onclick="alert('Videos próximamente disponibles')">
                                Ver Videos
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-widget">
                        <div class="card-body text-center">
                            <i class="fas fa-headset fa-4x text-success mb-3"></i>
                            <h5>Soporte Técnico</h5>
                            <p class="text-muted">Contacta con nuestro equipo</p>
                            <a href="https://wa.me/51980711209" target="_blank" class="btn btn-success btn-sm">
                                <i class="fab fa-whatsapp mr-2"></i>WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preguntas Frecuentes -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-question mr-2"></i>Preguntas Frecuentes</h3>
                        </div>
                        <div class="card-body">
                            <div id="accordion">
                                <!-- Pregunta 1 -->
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title w-100">
                                            <a class="d-block" data-toggle="collapse" href="#faq1">
                                                ¿Cómo registro una nueva venta?
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="faq1" class="collapse show" data-parent="#accordion">
                                        <div class="card-body">
                                            Ve al menú <strong>Ventas > Nueva Venta</strong>, selecciona los productos, cliente y método de pago.
                                        </div>
                                    </div>
                                </div>

                                <!-- Pregunta 2 -->
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title w-100">
                                            <a class="d-block collapsed" data-toggle="collapse" href="#faq2">
                                                ¿Cómo genero un reporte?
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="faq2" class="collapse" data-parent="#accordion">
                                        <div class="card-body">
                                            Accede a <strong>Reportes</strong>, selecciona el tipo de reporte y el rango de fechas deseado.
                                        </div>
                                    </div>
                                </div>

                                <!-- Pregunta 3 -->
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title w-100">
                                            <a class="d-block collapsed" data-toggle="collapse" href="#faq3">
                                                ¿Cómo cambio mi contraseña?
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="faq3" class="collapse" data-parent="#accordion">
                                        <div class="card-body">
                                            Ve a <strong>Mi Perfil > Seguridad</strong> e ingresa tu contraseña actual y la nueva contraseña.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include_once('../../contans/layout/mensajes.php'); 
include_once('../../contans/layout/parte2.php'); 
?>
