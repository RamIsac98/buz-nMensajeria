<?php
/**
 * Vista de inicio de sesión (Login).
 * 
 * Muestra el formulario de autenticación con campos de usuario y contraseña.
 * Incluye mensajes flash con SweetAlert2 para notificaciones de éxito o error.
 * 
 * Conexiones con el controlador:
 * - Formulario envía POST a la ruta 'login/autenticar' (método autenticar() en Login controller).
 * - Enlace "¿Olvidaste tu contraseña?" redirige a 'login/olvide_contrasena' (método olvideContrasena()).
 * - Los mensajes flash se generan en el controlador y se muestran aquí via SweetAlert2.
 * 
 * Dependencias:
 * - Bootstrap 5 (CSS y JS)
 * - SweetAlert2 (para alertas)
 * - Logo y assets desde la carpeta public.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestión de Desechos Biológicos</title>
    <!-- Carga de Bootstrap CSS desde assets -->
    <link href="<?= base_url('bootstrap5/css/bootstrap.min.css') ?>" rel="stylesheet">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('img/logo.svg') ?>">
    <style>
        /* Estilos personalizados de la vista */
        :root {
            --azul-claro: #2073AF;
            --azul-oscuro: rgba(28, 70, 110, 0.9);
            --amarillo: #ffc107;
        }
        body {
            background: linear-gradient(135deg, var(--azul-claro) 0%, #145a8a 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-card {
            max-width: 460px;
            width: 100%;
            border-radius: 1.5rem;
            background: white;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1.5rem 3rem rgba(0,0,0,0.2) !important;
        }
        .text-ivic { color: var(--azul-claro); }
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 0.75rem;
            padding: 0.7rem 1rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: var(--azul-claro);
            box-shadow: 0 0 0 0.25rem rgba(32, 115, 175, 0.25);
            outline: none;
        }
        .btn-login {
            background-color: var(--azul-oscuro);
            color: white;
            border: none;
            border-radius: 2rem;
            padding: 0.7rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background-color: var(--azul-claro);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            color: var(--amarillo);
        }
        .forgot-password {
            color: var(--azul-claro);
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s;
        }
        .forgot-password:hover {
            color: var(--amarillo);
            text-decoration: underline;
        }
        .footer-note {
            font-size: 0.75rem;
            color: #6c757d;
        }
    </style>
</head>
<body>

    <div class="card login-card border-0 shadow-lg p-4 p-sm-5">
        <div class="text-center mb-4">
            <!-- Logo y título -->
            <img src="<?= base_url('img/logo.svg') ?>" alt="Logo" class="mb-3" style="width: 80px; height: auto;">
            <h2 class="h5 fw-bold text-ivic m-0 lh-base">
                Sistema de Gestión de Desechos<br>Biológicos
            </h2>
            <p class="text-muted small mt-2">Acceso seguro al sistema</p>
        </div>
        
        <!-- ===== MENSAJES FLASH CON SWEETALERT2 ===== -->
        <!-- Los mensajes flash son establecidos por el controlador Login en métodos como autenticar() o nuevaClave() -->
        <?php if (session()->getFlashdata('success') || session()->getFlashdata('error')): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                <?php if(session()->getFlashdata('success')): ?>
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: '<?= esc(session()->getFlashdata('success')) ?>',
                        confirmButtonColor: '#2073AF',
                        timer: 4000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                <?php endif; ?>

                <?php if(session()->getFlashdata('error')): ?>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: '<?= esc(session()->getFlashdata('error')) ?>',
                        confirmButtonColor: '#d33',
                        timer: 5000,
                        timerProgressBar: true,
                        showConfirmButton: true
                    });
                <?php endif; ?>
            });
        </script>
        <?php endif; ?>

        <!-- ===== FORMULARIO DE LOGIN ===== -->
        <!-- Envía POST al controlador Login::autenticar() -->
        <form action="<?= base_url('login/autenticar') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="username" class="form-label">Nombre de Usuario</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="Ej. jperez" required autofocus>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-login w-100 py-2 fw-bold">Iniciar Sesión</button>
                <!-- Enlace a recuperación de contraseña: controlador Login::olvideContrasena() -->
                <a href="<?= base_url('login/olvide_contrasena') ?>" class="btn-link-custom text-center mt-2">¿Olvidaste tu contraseña?</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="<?= base_url('bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>