<?php
// Obtener el rol desde la sesión
$rolUsuario = session()->get('rol');
$username = session()->get('username') ?? 'Sistema';

// Definir qué opciones puede ver cada rol
$menuItems = [];

// Opciones base para todos los roles autenticados
$baseItems = [
    'inicio'       => ['url' => 'interfazinicial/menuusuario', 'label' => 'Inicio'],
    'desechos'     => ['url' => 'desechos/formulario', 'label' => 'Solicitud Desechos'],
    'bioseguridad' => ['url' => 'solicitud_bioseguridad', 'label' => 'Solicitud Bioseguridad'],
    'registro'     => ['url' => 'desechos/registroSolicitudes', 'label' => 'Registro']
];

// Solo administrador puede ver Configuración (con submenús)
$configItems = [];
if ($rolUsuario === 'administrador') {
    $configItems = [
        'gestion' => ['url' => 'desechos/gestionSolicitudes', 'label' => 'Gestión Solicitudes'],        
        'config' => [
            'label' => 'Configuración',
            'submenu' => [
                ['url' => 'usuarios', 'label' => 'Gestión Usuarios'],
                ['url' => 'gestion-departamento', 'label' => 'Gestión Departamentos'],
                ['url' => 'usuarios/bitacora', 'label' => 'Bitácora']
            ]
        ]
    ];
}

// Combinar según el rol (todos ven las opciones base, solo admin ve configuración)
$menuItems = $baseItems + $configItems;

// Determinar la ruta actual para marcar como activo
$currentPath = service('request')->getUri()->getPath();
?>

<nav class="custom-navbar rounded-1">
    <div class="nav-brand-container">
        <div class="logo-placeholder">
            <img src="<?= base_url('img/logo.svg') ?>" alt="logo">
        </div>

        <!-- Iterar sobre los elementos del menú -->
        <?php foreach ($menuItems as $key => $item): ?>
            <?php if (isset($item['submenu'])): ?>
                <!-- Menú desplegable para Configuración -->
                <div class="dropdown d-flex align-items-center h-100">
                    <a href="#" class="nav-link-custom dropdown-toggle" id="dropdown-<?= $key ?>" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= $item['label'] ?>
                    </a>
                    <ul class="dropdown-menu custom-dropdown-menu border-0 shadow mt-0" aria-labelledby="dropdown-<?= $key ?>">
                        <?php foreach ($item['submenu'] as $sub): ?>
                            <li><a class="dropdown-item" href="<?= base_url($sub['url']) ?>"><?= $sub['label'] ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <!-- Enlace normal -->
                <a href="<?= base_url($item['url']) ?>" 
                   class="nav-link-custom <?= (str_contains($currentPath, $item['url']) ? 'active' : '') ?>">
                    <?= $item['label'] ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="d-flex align-items-center h-100 user-section">
        <div class="dropdown">
            <a href="#" class="user-dropdown-toggle dropdown-toggle" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?= base_url('img/user.svg') ?>" class="user-icon-img" alt="User Icon">
                <span>Usuario <strong><?= esc($username) ?></strong></span>
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

<!-- Modal de cambio de contraseña (debe estar en todas las vistas o incluirlo una sola vez) -->
<div class="modal fade" id="modalCambiarPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-custom-width">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: var(--azul-oscuro);">
                <h5 class="modal-title">Cambiar Contraseña</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('usuarios/cambiar_password_post') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Contraseña Actual</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva Contraseña</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Confirmar Nueva</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-custom btn-sm">Actualizar Clave</button>
                </div>
            </form>
        </div>
    </div>
</div>