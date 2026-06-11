<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestión de Desechos Biológicos</title>
    <link href="<?= base_url('bootstrap5/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= base_url('img/logo.svg') ?>">

    <style>
        /* Variables de Identidad Corporativa */
        :root {
            --color-ivic-claro: #2073AF;
            --color-ivic-oscuro: rgba(28, 70, 110, 0.9);
            --color-accent: #ffc107;
        }

        body {
            background-color: var(--color-ivic-claro);
        }

        /* Contenedor de Login Modernizado */
        .login-card {
            max-width: 460px;
            border-radius: 1.5rem !important;
        }

        .text-ivic { color: var(--color-ivic-claro); }

        /* Campos de Entrada Personalizados */
        .form-control {
            border: 2px solid var(--color-ivic-oscuro);
            border-radius: 0.5rem;
            padding: 0.6rem 1rem;
            color: var(--color-ivic-oscuro);
        }

        /* Estado de Enfoque (Focus) con destello Amarillo */
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
            border-color: var(--color-accent);
            outline: none;
        }

        /* Botón de Iniciar Sesión */
        .btn-login {
            background-color: var(--color-ivic-oscuro);
            color: #ffffff;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            color: var(--color-accent);
            transform: translateY(-2px);
            background-color: var(--color-ivic-oscuro);

        }

        /* Enlace de recuperación */
        .forgot-password {
            color: var(--color-ivic-claro);
            font-size: 0.85rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: var(--color-accent);
        }
    </style>
</head>
<body class="vh-100 d-flex align-items-center justify-content-center p-3">

    <div class="card login-card w-100 border-0 shadow-lg p-4 p-sm-5">
        
        <div class="text-center mb-4">
            <img src="<?= base_url('img/logo.svg') ?>" alt="Logo de la Empresa" class="mb-3" style="width: 70px; height: auto;">
            <h2 class="h5 fw-bold text-ivic m-0 lh-base">
                Sistemas de Gestión de Desechos<br>Biológicos
            </h2>
        </div>
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger text-center py-2 mb-4 small fw-semibold border-0 text-danger bg-danger bg-opacity-10" role="alert">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('login/autenticar') ?>" method="POST" autocomplete="off">
            
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold text-ivic small">Usuario</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label fw-semibold text-ivic small">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-login rounded-pill w-100 py-2 fw-semibold shadow-sm">
                Iniciar Sesión
            </button>

        </form>

        <div class="text-center mt-4">
            <a href="<?= base_url('login/olvide_contrasena') ?>" class="forgot-password text-decoration-none">
                ¿Olvidaste tu contraseña?
            </a>
        </div>

    </div>
    
    <script>
        // Restablece el formulario si el usuario regresa con los botones de navegación del navegador
        window.addEventListener('pageshow', function (event) {
            const loginForm = document.querySelector('form'); 
            if (loginForm) {
                loginForm.reset();
            }
        });
    </script>
    <script src="<?= base_url('bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>