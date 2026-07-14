<?php
/**
 * Vista: Layout base del sistema (base.php).
 * 
 * Este es el layout principal que extienden todas las vistas del sistema.
 * Define la estructura HTML común (doctype, head, body, navbar, footer)
 * y las secciones que las vistas hijas pueden llenar: title, styles, content, scripts.
 * 
 * Conexiones con el controlador:
 * - Este layout NO llama directamente a ningún controlador, pero es el contenedor
 *   de todas las vistas renderizadas por los controladores.
 * - Todas las vistas que extienden este layout (usando <?= $this->extend('layouts/base') ?>)
 *   son servidas por los controladores correspondientes (Login, DesechosController,
 *   BioseguridadController, GestionController, Usuarios, etc.).
 * - La vista parcial 'layouts/_navbar' (incluida aquí) contiene los enlaces a
 *   los controladores (Login::salir(), Usuarios::cambiar_password_post(), etc.).
 * - El contenido dinámico (secciones 'content', 'styles', 'scripts') es inyectado
 *   por las vistas hijas, que a su vez reciben datos de los controladores.
 * - Los assets (Bootstrap CSS/JS, logo) se cargan desde la carpeta public/.
 * 
 * Dependencias:
 * - Bootstrap 5 (CSS y JS) desde public/bootstrap5/.
 * - Logo y assets desde public/img/.
 * - La vista parcial _navbar (layouts/_navbar) que contiene la barra de navegación.
 * - SweetAlert2 (cargado en _navbar para mensajes flash y bienvenida).
 * 
 * @package App\Views\layouts
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> | Sistema de Mensajería</title>
    <link rel="stylesheet" href="<?= base_url('bootstrap5/css/bootstrap.min.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('img/logo.svg') ?>">
    <style>
        :root {
            --azul-claro: #2073AF;
            --azul-oscuro: rgba(28, 70, 110, 0.9);
            --amarillo: #ffc107;
        }
        /* Estructura flex para que el footer quede siempre abajo */
        body {
            background-color: #f8f9fc;
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;  /* Ocupa todo el espacio disponible */
            padding: 30px 0;
        }
        /* Estilos del navbar ya están en el archivo CSS externo o aquí */
        /* Puedes copiar aquí los estilos del navbar si no usas archivo externo */
        
        /* Estilo adicional para el footer (opcional, pero bg-dark ya cubre) */
        footer {
            background-color: #1a2a3a; /* Color oscuro similar al de la referencia */
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>

    <?= view('layouts/_navbar') ?>

    <div class="container-fluid main-content">
        <?= $this->renderSection('content') ?>
    </div>

    <!-- Footer oscuro con la descripción solicitada -->
    <footer class="text-white py-3">
        <div class="container text-center">
            <p class="mb-0">
                Copyright © <?= date('Y') ?>. 
                Sistema de Gestión de Desechos Biológicos y Solicitud de materiales de Bioseguridad, 
                desarrollado por Ramses Isaac Mariño Vivas - Universidad Bicentenaria de Aragua.
            </p>
        </div>
    </footer>

    <script src="<?= base_url('bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>