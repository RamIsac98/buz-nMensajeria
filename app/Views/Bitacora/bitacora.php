<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bitácora de Auditoría</title>
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
            margin-bottom: 25px;
            font-size: 1.75rem;
        }

        /* Barra de Filtros Unificada */
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

        .filter-input:focus {
            border-color: var(--azul-claro);
        }

        .input-search-width {
            width: 180px;
        }

        .input-select-width {
            width: 140px;
        }

        /* Tabla de Control manteniendo las 7 columnas originales */
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
        }

        /* Paginación estilo bloques del diseño */
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

        .custom-pagination .page-item.active .page-link {
            background-color: var(--azul-oscuro);
            color: white;
        }

        .custom-pagination .page-item.disabled .page-link {
            background-color: #cccccc;
            color: #ffffff;
        }

        .footer-text {
            color: var(--azul-claro);
            font-weight: bold;
            font-size: 0.95rem;
        }

        .modal-custom-width {
            max-width: 450px;
        }

        .btn-modal-submit {
            background-color: var(--azul-claro);
        }

        .btn-modal-submit:hover {
            background-color: var(--azul-oscuro);
            color: white;
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
                            $is_home = ($current_path === '' || $current_path === '/' || str_contains($current_path, 'interfazinicial/menuusuario'));
                        ?>

                        <a href="<?= base_url('interfazinicial/menuusuario') ?>" class="nav-link-custom <?= $is_home ? 'active' : '' ?>">
                            Inicio
                        </a>

                        <a href="<?= base_url('solicitud_desechos') ?>" class="nav-link-custom">Solicitud Desechos</a>
                        <a href="<?= base_url('solicitud_bioseguridad') ?>" class="nav-link-custom">Solicitud Bioseguridad</a>
                        <a href="<?= base_url('registro') ?>" class="nav-link-custom">Registro</a>
                        
                        <div class="d-flex align-items-center h-100 dropdown">
                            <a href="#" class="nav-link-custom active dropdown-toggle" id="configMenu" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                                Configuración
                            </a>
                            <ul class="dropdown-menu custom-dropdown-menu border-0 shadow mt-0" aria-labelledby="configMenu">
                                <li><a class="dropdown-item" href="<?= base_url('usuarios') ?>">Gestión Usuarios</a></li>
                                <li><a class="dropdown-item" href="<?= base_url('gestion-departamento') ?>">Gestión Departamentos</a></li>
                                <li><a class="dropdown-item" href="<?= base_url('usuarios/bitacora') ?>">Bitácora</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="d-flex align-items-center h-100 user-section">

                        <div class="dropdown">
                            <a href="#" class="user-dropdown-toggle dropdown-toggle" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?= base_url('img/user.svg') ?>" class="user-icon-img" alt="User Icon">
                                <span>Usuario <strong><?= esc(session()->get('username') ?? 'Sistema') ?></strong></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end custom-dropdown-menu" aria-labelledby="userMenu">
                                <li>
                                    <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCambiarPassword">
                                        Cambiar contraseña
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="<?= base_url('login/salir') ?>">
                                        Cerrar sesión
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                <?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                <?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <h2 class="main-title">Control de Solicitudes</h2>

        <div class="filter-bar mb-4">
            <form method="GET" action="<?= base_url('usuarios/bitacora') ?>" class="row g-2 align-items-center justify-content-between">
                
                <div class="col-auto filter-group">
                    <span class="filter-label">Buscador</span>
                    <input type="text" name="buscar" id="buscar" class="filter-input input-search-width"
                           placeholder="Usuario, IP, registro..." value="<?= esc($_GET['buscar'] ?? '') ?>">
                </div>

                <div class="col-auto filter-group">
                    <span class="filter-label">Periodo</span>
                    <input type="date" name="desde" id="desde" class="filter-input" value="<?= esc($_GET['desde'] ?? '') ?>">
                    <span class="text-muted small">al</span>
                    <input type="date" name="hasta" id="hasta" class="filter-input" value="<?= esc($_GET['hasta'] ?? '') ?>">
                </div>

                <div class="col-auto filter-group">
                    <span class="filter-label">Filtrar</span>
                    <select name="tipo" id="tipo" class="filter-input bg-white input-select-width">
                        <option value="">-- Todos --</option>
                        <option value="Sesión" <?= (($_GET['tipo'] ?? '') === 'Sesión') ? 'selected' : '' ?>>Sesión</option>
                        <option value="Administración" <?= (($_GET['tipo'] ?? '') === 'Administración') ? 'selected' : '' ?>>Administración</option>
                        <option value="Seguridad" <?= (($_GET['tipo'] ?? '') === 'Seguridad') ? 'selected' : '' ?>>Seguridad</option>
                    </select>
                </div>

                <div class="col-auto d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-link p-0 text-decoration-none filter-group border-0 bg-transparent">
                        <span class="filter-label">Buscar</span>
                    </button>
                    
                    <a href="<?= base_url('usuarios/bitacora') ?>" class="text-decoration-none text-dark d-flex align-items-center ms-1" title="Limpiar Filtros">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                        </svg>
                    </a>

                    <div class="vr mx-1"></div>
                    <button type="button" class="btn btn-outline-danger p-1 border-0 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalPdf" title="Generar PDF">
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
                        <th>Usuario</th>
                        <th>Tipo Solicitud</th>
                        <th>Detalle / Registro</th>
                        <th>Fecha y Hora</th>
                        <th>Dirección IP</th>
                        <th>Acción Realizada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($bitacora) && is_array($bitacora)): ?>
                        <?php foreach($bitacora as $log): ?>
                        <tr>
                            <td><?= $log['id'] ?></td>
                            <td>
                                <?= !empty($log['username']) ? esc($log['username']) : '<em class="text-muted">Sistema / Anónimo</em>' ?>
                            </td>
                            <td><?= esc($log['tipo_solicitud']) ?></td>
                            <td><?= esc($log['registro']) ?></td>
                            <td><?= esc($log['fecha']) ?></td>
                            <td><code><?= esc($log['ip']) ?></code></td>
                            <td><?= esc($log['accion']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay registros en la bitácora actualmente con los filtros seleccionados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div> 

        <div class="d-flex justify-content-between align-items-center mt-4 mb-5">
            <?php 
                $total = $pager->getTotal();
                $perPage = $pager->getPerPage();
                $currentPage = $pager->getCurrentPage();
                $totalPages = ceil($total / $perPage);
                
                $from = $total > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
                $to = min($currentPage * $perPage, $total);

                $currentGet = $_GET;
                unset($currentGet['page']);
                $urlParams = !empty($currentGet) ? '&' . http_build_query($currentGet) : '';
            ?>
            
            <nav aria-label="Navegación de bitacora">
                <div class="toolbar-bar d-flex justify-content-between align-items-center">

                <ul class="pagination custom-pagination m-0">
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= base_url('usuarios/bitacora?page=1' . $urlParams) ?>" aria-label="Primero">
                                <span>&laquo;&laquo;</span>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="<?= base_url('usuarios/bitacora?page=' . ($currentPage - 1) . $urlParams) ?>" aria-label="Anterior">
                                <span>&larr;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php 
                        $maxBotonesVisibles = 3;
                        $start = max(1, $currentPage - 1);
                        $end = min($totalPages, $start + $maxBotonesVisibles - 1);

                        if (($end - $start + 1) < $maxBotonesVisibles) {
                            $start = max(1, $end - $maxBotonesVisibles + 1);
                        }
                    ?>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="<?= base_url('usuarios/bitacora?page=' . $i . $urlParams) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= base_url('usuarios/bitacora?page=' . ($currentPage + 1) . $urlParams) ?>" aria-label="Siguiente">
                                <span>&rarr;</span>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="<?= base_url('usuarios/bitacora?page=' . $totalPages . $urlParams) ?>" aria-label="Último">
                                <span>&raquo;&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
           
            </nav>

            <div class="footer-text">
                Mostrando del <strong><?= $from ?></strong> al <strong><?= $to ?></strong> de un total de <strong><?= $total ?></strong> eventos.
            </div>
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
                            <label for="current_password" class="form-label text-secondary small font-weight-bold text-uppercase">Contraseña Actual</label>
                            <input type="password" name="current_password" id="current_password" class="form-control form-control-sm" required placeholder="Ingresa tu clave actual">
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label text-secondary small font-weight-bold text-uppercase">Nueva Contraseña</label>
                            <input type="password" name="new_password" id="new_password" class="form-control form-control-sm" required placeholder="Mínimo 6 caracteres">
                        </div>
                        <div class="mb-0">
                            <label for="confirm_password" class="form-label text-secondary small font-weight-bold text-uppercase">Confirmar Nueva Contraseña</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control form-control-sm" required placeholder="Repite la nueva clave">
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top p-2">
                        <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white btn-sm px-3 btn-modal-submit">Actualizar Clave</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPdf" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <form action="<?= base_url('usuarios/generarPdfBitacora') ?>" method="GET">
                <input type="hidden" name="buscar" value="<?= esc($_GET['buscar'] ?? '') ?>">
                <input type="hidden" name="desde" value="<?= esc($_GET['desde'] ?? '') ?>">
                <input type="hidden" name="hasta" value="<?= esc($_GET['hasta'] ?? '') ?>">
                <input type="hidden" name="tipo" value="<?= esc($_GET['tipo'] ?? '') ?>">

                <div class="modal-content border-0 shadow">
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
                </div>
            </form>
        </div>
    </div>
    
    <script src="<?= base_url('bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>