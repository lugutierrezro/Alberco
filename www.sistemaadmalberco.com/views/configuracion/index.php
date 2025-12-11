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
                    <h1 class="m-0"><i class="fas fa-cog mr-2 text-orange"></i>Configuración</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo URL_BASE; ?>">Inicio</a></li>
                        <li class="breadcrumb-item active">Configuración</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Configuración del Sistema</h3>
                        </div>
                        <div class="card-body">
                            <p class="lead">
                                <i class="fas fa-info-circle text-info mr-2"></i>
                                Sección en desarrollo. Próximamente podrás configurar:
                            </p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Preferencias de notificaciones
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Configuración de reportes
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Personalización del dashboard
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Configuración de impresoras
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Ajustes de seguridad
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success mr-2"></i>
                                            Temas y apariencia
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <hr>

                            <div class="text-center">
                                <a href="<?php echo URL_BASE; ?>" class="btn btn-primary">
                                    <i class="fas fa-home mr-2"></i>Volver al Dashboard
                                </a>
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
