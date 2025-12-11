<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Ventas | Inicio de Sesión</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../assets/public/templeates/AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="../../assets/public/templeates/AdminLTE-3.2.0/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../../assets/public/templeates/AdminLTE-3.2.0/dist/css/adminlte.min.css">
    <!-- Libreria Sweetalert2-->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <!-- /.login-logo -->


        <style>
            body.login-page {
                background: linear-gradient(135deg, #f77b72ff 0%, #def380ff 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Source Sans Pro', sans-serif;
            }

            .login-box {
                width: 420px;
                margin: 2rem auto;
            }

            .logo-container {
                background: rgba(255, 255, 255, 0.95);
                border-radius: 20px;
                padding: 2rem;
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
                margin-bottom: 2rem;
                transition: transform 0.3s ease;
            }

            .logo-container:hover {
                transform: translateY(-5px);
            }

            .logo-container img {
                width: 100%;
                max-width: 250px;
                height: auto;
                display: block;
                margin: 0 auto;
                filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
            }

            .card {
                border: none;
                border-radius: 20px;
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
                overflow: hidden;
                backdrop-filter: blur(10px);
            }

            .card-header {
                background: linear-gradient(135deg, #ea666dff 0%, #e3f57eff 100%);
                border: none;
                padding: 2rem;
                text-align: center;
            }

            .card-header .h1 {
                color: #ffffff;
                font-weight: 700;
                font-size: 1.8rem;
                margin: 0;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
                text-decoration: none;
                display: block;
            }

            .card-header .h1 b {
                font-weight: 800;
            }

            .card-body {
                padding: 2.5rem;
                background: #ffffff;
            }

            .login-box-msg {
                text-align: center;
                font-size: 1.15rem;
                color: #555;
                font-weight: 600;
                margin-bottom: 2rem;
                padding-bottom: 1rem;
                border-bottom: 2px solid #f0f0f0;
            }

            .login-box-msg i {
                color: #ea7a66ff;
                margin-right: 0.5rem;
            }

            .input-group {
                margin-bottom: 1.5rem;
            }

            .form-control {
                border: 2px solid #e8e8e8;
                border-right: none;
                border-radius: 10px 0 0 10px;
                padding: 0.75rem 1rem;
                font-size: 1rem;
                transition: all 0.3s ease;
                height: 48px;
            }

            .form-control:focus {
                border-color: #ea6666ff;
                box-shadow: none;
                background-color: #f8f9ff;
            }

            .input-group-text {
                background: linear-gradient(135deg, #f5ff70ff 0%, #f8270bff 100%);
                border: 2px solid #f50d05ff;
                border-left: none;
                border-radius: 0 10px 10px 0;
                color: #ffffff;
                width: 48px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .input-group-text .fas {
                font-size: 1.1rem;
            }

            hr {
                margin: 1.5rem 0;
                border-top: 2px solid #f0f0f0;
            }

            .btn-primary {
                background: linear-gradient(135deg, #f0740eff 0%, #764ba2 100%);
                border: none;
                border-radius: 10px;
                padding: 0.85rem 1.5rem;
                font-size: 1.1rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            }

            .btn-primary:hover {
                transform: translateY(-3px);
                box-shadow: 0 6px 20px rgba(233, 2, 2, 0.6);
                background: linear-gradient(135deg, #fc3402ff 0%, #f55d17ff 100%);
            }

            .btn-primary:active {
                transform: translateY(-1px);
            }

            /* Animación de entrada */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .login-box {
                animation: fadeInUp 0.6s ease-out;
            }

            /* Efecto de placeholder animado */
            .form-control::placeholder {
                color: #aaa;
                transition: all 0.3s ease;
            }

            .form-control:focus::placeholder {
                color: #f38c8cff;
                transform: translateX(5px);
            }

            /* Responsive */
            @media (max-width: 576px) {
                .login-box {
                    width: 90%;
                    margin: 1rem auto;
                }

                .card-body {
                    padding: 1.5rem;
                }

                .logo-container {
                    padding: 1.5rem;
                }
            }
        </style>
        </head>

        <body class="hold-transition login-page">

            <?php
            if (isset($_SESSION['mensaje'])) {
                $respuesta = $_SESSION['mensaje']; ?>
                <script>
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        title: '¡Error de Autenticación!',
                        text: '<?php echo $respuesta; ?>',
                        showConfirmButton: true,
                        confirmButtonText: 'Intentar de nuevo',
                        confirmButtonColor: '#667eea',
                        background: '#fff',
                        customClass: {
                            popup: 'animated fadeInDown'
                        }
                    })
                </script>
            <?php
                unset($_SESSION['mensaje']);
            }
            ?>

            <div class="login-box">
                <!-- Logo Container -->
                <div class="logo-container">
                    <img src="../../assets/public/images/Logo.png" alt="Logo Sistema de Ventas">
                </div>

                <!-- Login Card -->
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <a href="#" class="h1">
                            <b>Sistema de</b> VENTAS
                        </a>
                    </div>

                    <div class="card-body">
                        <p class="login-box-msg">
                            <i class="fas fa-user-lock"></i>
                            Ingrese sus datos
                        </p>

                        <form action="../../controllers/auth/login.php" method="post">
                            <div class="input-group">
                                <input type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Correo Electrónico"
                                    required
                                    autocomplete="email"
                                    autofocus>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-envelope"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="input-group">
                                <input type="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Contraseña"
                                    required
                                    autocomplete="current-password">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-lock"></span>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-sign-in-alt mr-2"></i>
                                        Ingresar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <!-- jQuery -->
            <script src="../../assets/public/templeates/AdminLTE-3.2.0/plugins/jquery/jquery.min.js"></script>
            <!-- Bootstrap 4 -->
            <script src="../../assets/public/templeates/AdminLTE-3.2.0/plugins/jquery/jquery.min.js"></script>
            <!-- AdminLTE App -->
            <script src="../../assets/public/templeates/AdminLTE-3.2.0/dist/js/adminlte.min.js"></script>
        </body>

</html>