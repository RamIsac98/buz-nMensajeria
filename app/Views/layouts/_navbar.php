<?php
$rolUsuario = session()->get('rol');
$username = session()->get('username') ?? 'Sistema';

// Opciones base para todos los roles (excepto administrador y protección integral)
$baseItems = [
    'inicio'       => ['url' => 'interfazinicial/menuusuario', 'label' => 'Inicio'],
    'desechos'     => ['url' => 'desechos/formulario', 'label' => 'Solicitud de Recolección de Desechos Biológicos'],
    'bioseguridad' => ['url' => 'solicitud_bioseguridad', 'label' => 'Solicitud de Materiales de Bioseguridad'],
    'registro'     => ['url' => 'desechos/registroSolicitudes', 'label' => 'Registro']
];

// Items extra para administrador
$adminItems = [];
if ($rolUsuario === 'administrador') {
    $adminItems = [
        'gestion' => ['url' => 'desechos/gestionSolicitudes', 'label' => 'Gestión Solicitudes']
    ];
}

// Configuración (solo administrador)
$configItems = [];
if ($rolUsuario === 'administrador') {
    $configItems = [
        'config' => [
            'label' => 'Configuración',
            'submenu' => [
                ['url' => 'usuarios', 'label' => 'Gestión Usuarios'],
                ['url' => 'gestion-departamento', 'label' => 'Gestión Centros y Laboratorios'],
                ['url' => 'usuarios/bitacora', 'label' => 'Bitácora']
            ]
        ]
    ];
}

// Items exclusivos para protección integral
$proteccionItems = [];
if ($rolUsuario === 'proteccion_integral') {
    $proteccionItems = [
        'inicio'       => ['url' => 'interfazinicial/menuusuario', 'label' => 'Inicio'],
        'dashboard'    => ['url' => 'dashboard', 'label' => 'Resgistro Peso Desechos'],
        'gestion'      => ['url' => 'desechos/gestionSolicitudes', 'label' => 'Gestión Solicitudes'],
        'usuarios'     => ['url' => 'usuarios', 'label' => 'Gestión Usuarios'],
        'departamentos'=> ['url' => 'gestion-departamento', 'label' => 'Gestión Centros y Laboratorios']
    ];
}

// Construcción final del menú según el rol
if ($rolUsuario === 'administrador') {
    $menuItems = $baseItems + $adminItems + $configItems;
} elseif ($rolUsuario === 'proteccion_integral') {
    $menuItems = $proteccionItems;
} else {
    // PAI, TAI, Jefe_Laboratorio, Auxiliar
    $menuItems = $baseItems;
}

$currentPath = service('request')->getUri()->getPath();
?>

<style>
    /* ===== ESTILOS MEJORADOS PARA EL NAVBAR ===== */
    .custom-navbar {
        background: linear-gradient(135deg, #2073AF 0%, #155d8a 100%);
        padding: 0.25rem 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-bottom: none;
        transition: all 0.3s ease;
        min-height: 70px;
        display: flex;
        align-items: center;
    }

    .custom-navbar .navbar-brand {
        display: inline-flex;
        align-items: center;
        height: 50px;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: white;
        transition: transform 0.2s;
    }
    .custom-navbar .navbar-brand:hover {
        transform: scale(1.02);
        color: var(--amarillo);
    }

    .custom-navbar .navbar-nav .nav-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 42px;
        padding: 0 1.2rem;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
        margin: 0 0.2rem;
        border-radius: 40px;
        transition: all 0.25s ease;
        position: relative;
        line-height: 1.2;
    }

    .custom-navbar .navbar-nav .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.2);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .custom-navbar .navbar-nav .nav-link.active {
        background-color: var(--azul-oscuro);
        color: var(--amarillo) !important;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    .custom-navbar .dropdown-menu {
        border: none;
        background: white;
        border-radius: 16px;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
        margin-top: 0.5rem;
        padding: 0.5rem 0;
        overflow: hidden;
        animation: fadeInUp 0.2s ease;
    }

    .custom-navbar .dropdown-item {
        padding: 0.6rem 1.8rem;
        font-size: 0.9rem;
        font-weight: 500;
        color: #2c3e50;
        transition: all 0.2s;
    }

    .custom-navbar .dropdown-item:hover {
        background-color: #eef5ff;
        color: #2073AF;
        padding-left: 2rem;
    }

    .navbar-toggler {
        border: 1px solid rgba(255,255,255,0.5);
        background: transparent;
    }
    .navbar-toggler-icon {
        filter: invert(1);
    }

    .user-dropdown-toggle {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 42px;
        gap: 8px;
        padding: 0 1rem !important;
        background: rgba(255,255,255,0.1);
        border-radius: 40px;
    }
    .user-dropdown-toggle img {
        border: 1px solid white;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 992px) {
        .custom-navbar .navbar-nav .nav-link {
            height: auto;
            justify-content: flex-start;
            margin: 0.2rem 0;
            text-align: center;
        }
        .user-dropdown-toggle {
            justify-content: center !important;
            width: 100%;
        }
    }
</style>

<nav class="navbar navbar-expand-lg custom-navbar shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center" href="<?= base_url('interfazinicial/menuusuario') ?>">
            <img src="<?= base_url('img/logo.svg') ?>" alt="Logo" width="40" height="40" class="d-inline-block me-2">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($menuItems as $key => $item): ?>
                    <?php if (isset($item['submenu'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="dropdown-<?= $key ?>" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= $item['label'] ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="dropdown-<?= $key ?>">
                                <?php foreach ($item['submenu'] as $sub): ?>
                                    <li><a class="dropdown-item" href="<?= base_url($sub['url']) ?>"><?= $sub['label'] ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?= (str_contains($currentPath, $item['url']) ? 'active' : '') ?>" href="<?= base_url($item['url']) ?>">
                                <?= $item['label'] ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle user-dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= base_url('img/user.svg') ?>" width="22" height="22" class="rounded-circle" alt="User">
                        <span class="ms-1"><?= esc($username) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
                        <li>
                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalCambiarPassword">
                                <i class="bi bi-key-fill me-2"></i> Cambiar contraseña
                            </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?= base_url('login/salir') ?>">
                                <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Modal cambio de contraseña -->
<div class="modal fade" id="modalCambiarPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--azul-oscuro);">
                <h5 class="modal-title">Cambiar Contraseña</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('usuarios/cambiar_password_post') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Contraseña Actual</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nueva Contraseña</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Confirmar Nueva</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background-color: var(--azul-claro); color: white;">Actualizar Clave</button>
                </div>
            </form>
        </div>
    </div>
</div>