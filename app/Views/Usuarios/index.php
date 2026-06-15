<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administración de Usuarios</title>
    <link class="html-embed" rel="stylesheet" href="<?= base_url('bootstrap5/css/bootstrap.min.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('img/logo.svg') ?>">
    
    <style>
        /* ---  (PALETA DE COLORES CORPORATIVA) --- */
        :root {
            --azul-claro: #2073AF;
            --azul-oscuro: rgba(28, 70, 110, 0.9);
            --amarillo: #ffc107;
        }

        body {
            background-color: #ffffff;
            font-family: Arial, sans-serif;
        }

        /* --- CLASES DE UTILIDAD PERSONALIZADAS (SOBREESCRITURA BOOTSTRAP) --- */
        .bg-custom-success { background-color: var(--azul-oscuro) !important; }
        .text-custom-success { color: var(--azul-oscuro) !important; }
        .btn-custom-success { background-color: var(--azul-oscuro) !important; border-color: var(--azul-oscuro) !important; }
        .alert-custom-success {
            background-color: var(--azul-oscuro) !important;
            color: white !important;
            border: 1px solid var(--azul-claro) !important;
        }

        /* --- BARRA DE NAVEGACIÓN SUPERIOR --- */
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

        /* --- SECCIÓN DE PERFIL DE USUARIO --- */
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

        /* --- COMPONENTES DE FILTROS Y HERRAMIENTAS --- */
        .filter-bar {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 8px 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-label {
            font-size: 0.8rem;
            font-weight: bold;
            color: var(--azul-oscuro);
            text-transform: uppercase;
            white-space: nowrap;
        }

        .filter-input {
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 0.9rem;
            outline: none;
        }

        .filter-input:focus { border-color: var(--azul-claro); }
        .input-search-width { width: 180px; }
        .input-select-width { width: 140px; }

        .main-title {
            color: var(--azul-oscuro);
            font-weight: bold;
            margin-top: 35px;
            margin-bottom: 25px;
            font-size: 1.75rem;
        }

        /* --- BOTONES PERSONALIZADOS --- */
        .btn-custom {
            background-color: var(--azul-claro);
            color: white;
            border: none;
            padding: 8px 20px;
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
            padding: 8px 20px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary-custom:hover {
            background-color: var(--azul-claro);
            color: white;
        }

        /* --- TABLA DE DATOS PRINCIPAL --- */
        .table-custom {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .table-custom th {
            color: var(--azul-claro);
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.85rem;
            border-bottom: 2px solid var(--azul-claro) !important;
            padding: 12px 8px;
        }

        .table-custom td {
            padding: 12px 8px;
            color: #555555;
            font-size: 0.9rem;
            border-bottom: 1px solid #eeeeee;
            vertical-align: middle;
        }

        .badge-protected {
            background-color: var(--azul-oscuro);
            padding: 8px;
        }

        /* --- BLOQUE DE PAGINACIÓN --- */
        .custom-pagination .page-item .page-link {
            background-color: var(--azul-claro);
            color: white;
            border: none;
            border-radius: 8px !important;
            margin-right: 8px;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .custom-pagination .page-item.active .page-link { background-color: var(--azul-oscuro); color: white; }
        .custom-pagination .page-item.disabled .page-link { background-color: #cccccc; color: #ffffff; }
        .footer-text { color: var(--azul-claro); font-weight: bold; font-size: 0.95rem; }
        .modal-custom-width { max-width: 450px; }

        /* --- COMPONENTE INTERRUPTOR (SLIDER TRACK) --- */
        .slider-track {
            width: 40px;
            height: 20px;
            background-color: #e0e0e0;
            border-radius: 20px;
            position: relative;
            cursor: pointer;
            transition: background-color 0.4s ease, border-color 0.4s ease;
            border: 2px solid #ccc;
            padding: 0;
            display: inline-block;
            vertical-align: middle;
            margin-left: 4px;
            margin-right: 4px;
        }

        .slider-handle {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            position: absolute;
            top: 2px;
            left: 2px;
            background-color: #ffc107;
            transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55), background-color 0.5s ease;
            box-shadow: 0 3px 6px rgba(0,0,0,0.2);
        }

        .slider-track.is-active {
            background-color: rgba(28, 70, 110, 0.1);
            border-color: rgba(28, 70, 110, 0.5);
        }

        .slider-track.is-active .slider-handle {
            transform: translateX(20px);
            background-color: rgba(28, 70, 110, 0.9);
        }
    </style>
</head>
<body class="px-4 py-3">

    <div class="container-fluid">
        
        <header class="mb-4">
            <?= view('layouts/_navbar') ?>
        </header>

        <section class="system-messages">
            <?php if (session()->getFlashdata('usuario_eliminado')) : ?>
                <div class="alert alert-danger d-flex align-items-center alert-dismissible fade show border-start border-danger border-4 shadow-sm bg-danger-subtle text-danger-emphasis" role="alert">
                    <div>
                        <h6 class="alert-heading mb-1 font-weight-bold">Registro Eliminado</h6>
                        <span class="small text-dark"><?= esc(session()->getFlashdata('usuario_eliminado')) ?></span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-custom-success alert-dismissible fade show py-2" role="alert">
                    <strong>¡Éxito!</strong> <?= esc(session()->getFlashdata('success')) ?>
                    <button type="button" class="btn-close btn-close-white py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                    <strong>¡Error!</strong> <?= esc(session()->getFlashdata('error')) ?>
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        </section>

        <main>
            <h2 class="main-title">Panel de Gestión de Usuarios</h2>

            <div class="filter-bar mb-4 mt-3">
                <form method="GET" action="<?= base_url('usuarios') ?>" class="row g-2 align-items-center justify-content-between">
            
                    <div class="col-auto filter-group">
                        <span class="filter-label">Buscar</span>
                        <input type="text" name="buscar" id="buscar" class="filter-input input-search-width" placeholder="Usuario o Cédula..." value="<?= esc($_GET['buscar'] ?? '') ?>">
                    </div>

                    <div class="col-auto filter-group">
                        <span class="filter-label">Rol</span>
                        <select name="rol" id="rol" class="filter-input bg-white input-select-width">
                            <option value="">-- Todos --</option>
                            <?php foreach ($roles_disponibles as $rol_item): ?>
                                <option value="<?= esc($rol_item) ?>" <?= (($_GET['rol'] ?? '') === $rol_item) ? 'selected' : '' ?>>
                                    <?= esc($rol_item) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-auto filter-group">
                        <span class="filter-label">Estado</span>
                        <select name="estado" id="estado" class="filter-input bg-white input-select-width">
                            <option value="">-- Todos --</option>
                            <option value="1" <?= (($_GET['estado'] ?? '') === '1') ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= (($_GET['estado'] ?? '') === '0') ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>

                    <div class="col-auto d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-link p-0 text-decoration-none filter-group border-0 bg-transparent">
                            <span class="filter-label">Buscar</span>
                        </button>
                
                        <a href="<?= base_url('usuarios') ?>" class="text-decoration-none text-dark d-flex align-items-center ms-1 btn-limpiar-filtros" title="Limpiar Filtros">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                                <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                            </svg>
                        </a>

                        <div class="vr mx-1"></div>
                        <button type="button" class="btn btn-outline-danger p-1 border-0" data-bs-toggle="modal" data-bs-target="#modalPdf" title="Generar PDF">
                            <img src="<?= base_url('img/pdf.svg') ?>" alt="PDF" width="24">
                        </button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cédula</th> 
                            <th>Usuario (Username)</th>
                            <th>Rol / Permiso</th>
                            <th>Pregunta de Seguridad</th> 
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($usuarios as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= esc($user['cedula']) ?></td>
                            <td><strong><?= esc($user['username']) ?></strong></td>
                            <td><span class="badge bg-light text-dark border"><?= esc($user['rol']) ?></span></td>
                            <td>
                                <?= !empty($user['pregunta_seguridad']) ? esc($user['pregunta_seguridad']) : '<em class="text-muted">No configurada</em>' ?>
                            </td>
                            <td>
                                <?php if ($user['id'] == session()->get('usuario_id')): ?>
                                    <span class="badge badge-protected">Tu Cuenta (Protegida)</span>
                                <?php else: ?>
                                    <a href="<?= base_url('usuarios/editar/'.$user['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar Usuario">
                                        <img src="<?= base_url('img/pencil-square.svg') ?>" alt="Editar" width="16" height="16">
                                    </a>
                                    
                                    <button type="button" 
                                            class="slider-track <?= $user['status'] == 1 ? 'is-active' : '' ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalCambiarEstado"
                                            data-url="<?= base_url('usuarios/deshabilitar/'.$user['id']) ?>"
                                            data-username="<?= esc($user['username']) ?>"
                                            data-status="<?= $user['status'] ?>"
                                            title="<?= $user['status'] == 1 ? 'Deshabilitar Usuario' : 'Habilitar Usuario' ?>">
                                        <div class="slider-handle"></div>
                                    </button>
                                    
                                    <a href="#" class="btn btn-sm btn-outline-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalCambiarEstado"
                                            data-url="<?= base_url('usuarios/eliminar/'.$user['id']) ?>"
                                            data-username="<?= esc($user['username']) ?>"
                                            data-status="eliminar"
                                            title="Eliminar Usuario">
                                        <img src="<?= base_url('img/trash-x.svg') ?>" alt="Eliminar" width="16" height="16">
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div> 

            <div class="d-flex justify-content-between align-items-center mt-4 mb-5">
                <?php 
                    //LÓGICA  
                    $total       = $pager->getTotal();
                    $perPage     = $pager->getPerPage();
                    $currentPage = $pager->getCurrentPage();
                    $totalPages  = ceil($total / $perPage);
                    
                    $from = $total > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
                    $to   = min($currentPage * $perPage, $total);

                    $startPage = max(1, $currentPage - 1);
                    $endPage   = min($totalPages, $currentPage + 1);

                    if ($currentPage == 1) {
                        $endPage = min($totalPages, 3);
                    }
                    if ($currentPage == $totalPages) {
                        $startPage = max(1, $totalPages - 2);
                    }

                    $currentGet = $_GET;
                    unset($currentGet['page']);
                    $urlParams = !empty($currentGet) ? '&' . http_build_query($currentGet) : '';
                ?>
                
                <nav aria-label="Navegación de usuarios">
                    <ul class="pagination custom-pagination m-0">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('usuarios?page=1' . $urlParams) ?>" aria-label="Primero">
                                    <span>&laquo;&laquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('usuarios?page=' . ($currentPage - 1) . $urlParams) ?>" aria-label="Anterior">
                                    <span>&larr;</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled"><span class="page-link">&laquo;&laquo;</span></li>
                            <li class="page-item disabled"><span class="page-link">&larr;</span></li>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="<?= base_url('usuarios?page=' . $i . $urlParams) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('usuarios?page=' . ($currentPage + 1) . $urlParams) ?>" aria-label="Siguiente">
                                    <span>&rarr;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('usuarios?page=' . $totalPages . $urlParams) ?>" aria-label="Último">
                                    <span>&raquo;&raquo;</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled"><span class="page-link">&rarr;</span></li>
                            <li class="page-item disabled"><span class="page-link">&raquo;&raquo;</span></li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <div class="footer-text">
                    Mostrando del <strong><?= $from ?></strong> al <strong><?= $to ?></strong> de un total de <strong><?= $total ?></strong> usuarios registrados.
                </div>
            </div>
        </main>
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
    
    <div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-labelledby="modalCambiarEstadoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-custom-width">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white" id="modalEstadoHeader">
                    <h5 class="modal-title" id="modalCambiarEstadoLabel">Confirmar Acción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="fs-5 mb-1">¿Estás seguro de que deseas <strong id="modalTextoAccion"></strong> al usuario?</p>
                    <p class="text-muted mb-1">Usuario: <strong class="text-dark" id="modalTextoUsuario"></strong></p>
                    <p id="modalTextoAdvertencia" class="text-danger small font-weight-bold mb-0 d-none">¡Esta acción es permanente y no se puede deshacer!</p>
                </div>
                <div class="modal-footer bg-light border-top p-2 justify-content-center">
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Cancelar</button>
                    <a href="#" id="btnConfirmarCambioEstado" class="btn btn-sm px-4">Confirmar</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPdf" tabindex="-1" aria-labelledby="modalPdfLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="GET" action="<?= base_url('usuarios/generarPdfUsuarios') ?>" class="modal-content border-0 shadow">
                <input type="hidden" name="buscar" value="<?= esc($_GET['buscar'] ?? '') ?>">
                <input type="hidden" name="rol" value="<?= esc($_GET['rol'] ?? '') ?>">
                <input type="hidden" name="estado" value="<?= esc($_GET['estado'] ?? '') ?>">

                <div class="modal-header text-white" style="background-color: var(--azul-oscuro);">
                    <h5 class="modal-title fs-6">Generar Reporte PDF</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Ingresa el rango de páginas a imprimir. Por defecto se inicializa en tu página actual.</p>
                    <div class="mb-3">
                        <label class="form-label text-secondary small font-weight-bold text-uppercase">Desde página:</label>
                        <input type="number" name="pagina_inicio" class="form-control form-control-sm" value="<?= esc($currentPage ?? 1) ?>" min="1" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-secondary small font-weight-bold text-uppercase">Hasta página:</label>
                        <input type="number" name="pagina_fin" class="form-control form-control-sm" value="<?= esc($currentPage ?? 1) ?>" min="1" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3">Descargar PDF</button>
                </div>
            </form>
        </div>
    </div>


    <script>
        // --- NUEVA LÓGICA DE MEMORIA DE PÁGINA Y FILTROS ---
        document.addEventListener('DOMContentLoaded', () => {
            const currentQuery = window.location.search;
            const pathName = window.location.pathname;
            const memoryKey = 'usuarios_state_memory';
            const flashKey = 'usuarios_flash_memory';

            const systemMessages = document.querySelector('.system-messages');

            // 1. Restaurar mensaje flash (alertas) si venimos de una redirección de memoria
            const savedFlash = sessionStorage.getItem(flashKey);
            if (savedFlash && systemMessages) {
                systemMessages.innerHTML = savedFlash + systemMessages.innerHTML;
                sessionStorage.removeItem(flashKey);
            }

            // 2. Gestionar la memoria de los parámetros (página, filtros)
            if (currentQuery) {
                // Si la URL actual tiene filtros o paginación, guardamos el estado exacto
                sessionStorage.setItem(memoryKey, currentQuery);
            } else {
                // Si la URL NO tiene parámetros, vemos si tenemos una memoria anterior guardada
                const savedQuery = sessionStorage.getItem(memoryKey);
                
                if (savedQuery) {
                    // Protegemos los mensajes flash (Ej: "Usuario editado exitosamente") antes de redirigir
                    const hasNativeFlash = systemMessages && systemMessages.querySelector('.alert') !== null;

                    if (hasNativeFlash) {
                        // Guardamos el HTML de la alerta para restaurarlo luego de la redirección
                        sessionStorage.setItem(flashKey, systemMessages.innerHTML);
                    }

                    // Redirigimos silenciosamente aplicando los filtros y la página recordada
                    window.location.replace(pathName + savedQuery);
                }
            }

            // 3. Limpiar memoria al presionar el botón específico de limpiar filtros
            const btnLimpiar = document.querySelector('.btn-limpiar-filtros');
            if (btnLimpiar) {
                btnLimpiar.addEventListener('click', () => {
                    sessionStorage.removeItem(memoryKey);
                });
            }
            
            // 4. (Opcional) Limpiar memoria al usar "Volver al Inicio" 
            const btnVolverInicio = document.querySelector('.btn-volver-inicio');
            if (btnVolverInicio) {
                btnVolverInicio.addEventListener('click', () => {
                    sessionStorage.removeItem(memoryKey);
                });
            }
        });

        // --- LÓGICA ORIGINAL DE MANEJO DE EVENTOS ---
        const modalCambiarEstado = document.getElementById('modalCambiarEstado');
        if (modalCambiarEstado) {
            modalCambiarEstado.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                
                // Extracción datos vía data-attributes
                const url = button.getAttribute('data-url');
                const username = button.getAttribute('data-username');
                const status = button.getAttribute('data-status'); // Valores válidos: '1' (Activo), '0' (Inactivo), 'eliminar'

                const modalHeader = document.getElementById('modalEstadoHeader');
                const textoAccion = document.getElementById('modalTextoAccion');
                const textoUsuario = document.getElementById('modalTextoUsuario');
                const textoAdvertencia = document.getElementById('modalTextoAdvertencia');
                const btnConfirmar = document.getElementById('btnConfirmarCambioEstado');

                // Inyección inicial de datos
                textoUsuario.textContent = username;
                btnConfirmar.setAttribute('href', url);
                textoAdvertencia.classList.add('d-none');
                
                // Reinicio de clases css del Header y Botón Confirmar
                modalHeader.className = 'modal-header';
                btnConfirmar.className = 'btn btn-sm px-4 font-weight-bold';

                // Enrutamiento visual según el tipo de acción detectada
                if (status === '1') {
                    textoAccion.textContent = 'DESHABILITAR';
                    textoAccion.className = 'text-warning font-weight-bold';
                    
                    modalHeader.classList.add('bg-warning', 'text-dark');
                    btnConfirmar.classList.add('btn-warning', 'text-dark');
                } else if (status === '0') {
                    textoAccion.textContent = 'HABILITAR';
                    textoAccion.className = 'text-custom-success font-weight-bold';
                    
                    modalHeader.classList.add('bg-custom-success', 'text-white');
                    btnConfirmar.classList.add('btn-custom-success', 'text-white');
                } else if (status === 'eliminar') {
                    textoAccion.textContent = 'ELIMINAR';
                    textoAccion.className = 'text-danger font-weight-bold';
                    
                    modalHeader.classList.add('bg-danger', 'text-white');
                    btnConfirmar.classList.add('btn-danger', 'text-white');
                    
                    // Alerta de eliminar permanente
                    textoAdvertencia.classList.remove('d-none');
                }
            });
        }
    </script>

    <script src="<?= base_url('bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>