<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Principal - Sistema de Mensajería</title>
    <link rel="stylesheet" href="<?= base_url('bootstrap5/css/bootstrap.min.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('img/logo.svg') ?>">
    
    <style>
        :root {
            --azul-claro: #2073AF;
            --azul-oscuro: rgba(28, 70, 110, 0.9);
            --amarillo: #ffc107;
        }

        body {
            background-color: #ffffff;
            font-family: Arial, sans-serif;
        }

        /* Navbar estilo pestañas del diseño */
        .custom-navbar {
            background-color: var(--azul-claro);
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 65px;
        }

        .nav-brand-container {
            display: flex;
            align-items: center;
            padding-left: 20px;
        }

        .logo-placeholder {
            width: 40px;
            height: 40px;
            margin-right: 15px;
            display: inline-block;
            overflow: hidden;
            background-color: transparent;
        }

        .logo-placeholder img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            padding: 0 30px;
            height: 65px;
            font-size: 1.1rem;
            transition: background-color 0.2s ease;
        }

        .nav-link-custom:hover {
            background-color: rgba(0, 0, 0, 0.1);
            color: white;
        }

        .nav-link-custom.active {
            background-color: var(--azul-oscuro);
            color: var(--amarillo) !important;
            font-weight: 500;
        }

        /* Sección de usuario con Dropdown */
        .user-section {
            padding-right: 25px;
        }

        .user-dropdown-toggle {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .user-dropdown-toggle:hover, .user-dropdown-toggle:focus {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--amarillo);
        }

        .user-icon-img {
            width: 20px;
            height: 20px;
        }

        /* Personalización del menú desplegable */
        .custom-dropdown-menu {
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .custom-dropdown-menu .dropdown-item {
            font-size: 0.9rem;
            color: #444;
            padding: 8px 16px;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
        }

        .custom-dropdown-menu .dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--azul-claro);
        }

        /* Título Principal */
        .main-title {
            color: var(--azul-oscuro);
            font-weight: bold;
            margin-top: 35px;
            margin-bottom: 10px;
            font-size: 1.75rem;
        }

        /* Botones Custom */
        .btn-custom {
            background-color: var(--azul-claro);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            background-color: var(--azul-oscuro);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-outline-secondary-custom {
            color: var(--azul-claro);
            border: 1px solid var(--azul-claro);
            background: transparent;
            padding: 10px 20px;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-outline-secondary-custom:hover {
            background-color: var(--azul-claro);
            color: white;
        }

        /* Ancho personalizado para el Modal */
        .modal-custom-width {
            max-width: 450px;
        }

        /* Tarjetas de opciones */
        .panel-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .panel-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

    </style>
</head>
<body class="px-4 py-3">

    <div class="container-fluid">
        
        <header class="mb-4">
                <nav class="custom-navbar rounded-1">
                    <div class="nav-brand-container">
                        <div class="logo-placeholder"> 
                            <img src="<?= base_url('img/logo.svg') ?>" alt="logo">
                        </div>
                        
                        <?php 
                            $current_path = service('request')->getUri()->getPath();
                            $is_home = ($current_path === '' || $current_path === '/' || str_contains($current_path, 'interfaz_usuario_inicial'));
                            
                            // Obtenemos los datos de la sesión para validarlos
                            $rolUsuario = session()->get('rol');
                            $cedulaUsuario = session()->get('cedula');
                        ?>

                        <a href="<?= base_url('interfaz_usuario_inicial') ?>" class="nav-link-custom <?= $is_home ? 'active' : '' ?>">
                            Inicio
                        </a>

                        <a href="<?= base_url('solicitud_desechos') ?>" class="nav-link-custom">Solicitud Desechos</a>
                        <a href="<?= base_url('solicitud_bioseguridad') ?>" class="nav-link-custom">Solicitud Bioseguridad</a>
                        <a href="<?= base_url('desechos/registroSolicitudes') ?>" class="nav-link-custom">Registro</a>
                        
                        <?php if ($rolUsuario === 'administrador'): ?>
                            <div class="d-flex align-items-center h-100 dropdown">
                                <a href="#" class="nav-link-custom dropdown-toggle" id="configMenu" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                                    Configuración
                                </a>
                                <ul class="dropdown-menu custom-dropdown-menu border-0 shadow mt-0" aria-labelledby="configMenu">
                                    <li><a class="dropdown-item" href="<?= base_url('usuarios') ?>">Gestión Usuarios</a></li>
                                <li><a class="dropdown-item" href="<?= base_url('gestion-departamento') ?>">Gestión Departamentos</a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('usuarios/bitacora') ?>">Bitácora</a></li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    
                    <div class="d-flex align-items-center h-100 user-section">
                        <div class="dropdown">
                            <a href="#" class="user-dropdown-toggle dropdown-toggle" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?= base_url('img/user.svg') ?>" class="user-icon-img" alt="User Icon">
                                <span>Usuario <strong><?= esc(session()->get('username') ?? 'Sistema') ?></strong></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end custom-dropdown-menu" aria-labelledby="userMenu">
                                <li>
                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalCambiarPassword">
                                        Cambiar contraseña
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?= base_url('login/salir') ?>">
                                        Cerrar sesión
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
        </header>

        <div class="mb-4">
            <h2 class="main-title">Panel de Control Principal</h2>
            <p class="text-muted" style="font-size: 1.05rem;">
                Selecciona una de las opciones disponibles según tus permisos para realizar una solicitud en el sistema:
            </p>
        </div>

    </div>

    <div class="modal fade" id="modalCambiarPassword" tabindex="-1" aria-labelledby="modalCambiarPasswordLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-custom-width">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white" style="background-color: var(--azul-oscuro);">
                    <h5 class="modal-title" id="modalCambiarPasswordLabel">Cambiar Contraseña</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= base_url('usuarios/cambiar_password_post') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-secondary small font-weight-bold text-uppercase">Contraseña Actual</label>
                            <input type="password" name="current_password" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small font-weight-bold text-uppercase">Nueva Contraseña</label>
                            <input type="password" name="new_password" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label text-secondary small font-weight-bold text-uppercase">Confirmar Nueva</label>
                            <input type="password" name="confirm_password" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top p-2">
                        <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-custom btn-sm px-3">Actualizar Clave</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= base_url('bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>