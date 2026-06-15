<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Solicitudes de Desechos</title>
    <link rel="stylesheet" href="<?= base_url('bootstrap5/css/bootstrap.min.css') ?>">
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
        .input-search-width { width: 180px; }
        .input-select-width { width: 140px; }
        .input-date-width { width: 130px; }
        .main-title {
            color: var(--azul-oscuro);
            font-weight: bold;
            margin-top: 35px;
            margin-bottom: 25px;
            font-size: 1.75rem;
        }
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
        }
        .custom-pagination .page-item.active .page-link { background-color: var(--azul-oscuro); }
        .footer-text { color: var(--azul-claro); font-weight: bold; }
        .btn-custom {
            background-color: var(--azul-claro);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
        }
        .btn-custom:hover { background-color: var(--azul-oscuro); }
    </style>
</head>
<body>
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

                        <a href="<?= base_url('desechos/formulario') ?>" class="nav-link-custom">Solicitud Desechos</a>
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
    <div class="container-fluid my-5 px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="main-title">Historial de Solicitudes Procesadas</h2>
            <a href="<?= base_url('desechos/crear') ?>" class="btn text-white px-4" style="background-color: var(--azul-claro);">+ Nueva Solicitud</a>
        </div>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- BARRA DE FILTROS (igual que en index.php) -->
        <div class="filter-bar mb-4 mt-3">
            <form method="GET" action="<?= base_url('desechos/registroSolicitudes') ?>" class="row g-2 align-items-center justify-content-between">
                <div class="col-auto filter-group">
                    <span class="filter-label">Tipo SOLICITUD</span>
                    <select name="tipo_solicitud" class="filter-input input-select-width">
                        <option value="">-- Todos --</option>
                        <?php foreach ($tiposSolicitud as $tipo): ?>
                            <option value="<?= $tipo ?>" <?= ($filtros['tipo_solicitud'] ?? '') == $tipo ? 'selected' : '' ?>>
                                <?= $tipo ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-auto filter-group">
                    <span class="filter-label">Estado Solicitud</span>
                    <select name="estado_solicitud" class="filter-input input-select-width">
                        <option value="">-- Todos --</option>
                        <?php foreach ($estadosSolicitud as $est): ?>
                            <option value="<?= $est ?>" <?= ($filtros['estado_solicitud'] ?? '') == $est ? 'selected' : '' ?>>
                                <?= $est ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-auto filter-group">
                    <span class="filter-label">Fecha desde</span>
                    <input type="date" name="fecha_desde" class="filter-input input-date-width" value="<?= esc($filtros['fecha_desde'] ?? '') ?>">
                </div>

                <div class="col-auto filter-group">
                    <span class="filter-label">Fecha hasta</span>
                    <input type="date" name="fecha_hasta" class="filter-input input-date-width" value="<?= esc($filtros['fecha_hasta'] ?? '') ?>">
                </div>

                <div class="col-auto d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-link p-0 text-decoration-none filter-group border-0 bg-transparent">
                        <span class="filter-label">Buscar</span>
                    </button>
                    <a href="<?= base_url('desechos/registroSolicitudes') ?>" class="text-decoration-none text-dark" title="Limpiar Filtros">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                        </svg>
                    </a>
                </div>
            </form>
        </div>

        <!-- TABLA REDISEÑADA CON LAS COLUMNAS SOLICITADAS -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="thead-custom">
                            <tr>
                                <th>TIPO SOLICITUD</th>
                                <th>INFORME</th>
                                <th>ESTADO SOLICITUD</th>
                                <th>FECHA SOLICITADA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($solicitudes)) : ?>
                                <?php foreach ($solicitudes as $sol) : ?>
                                    <tr>
                                        <td class="fw-bold"><?= esc($sol['tipo_solicitud']) ?></td>
                                        <td>
                                            <?php if (!empty($sol['ruta_pdf'])): ?>
                                            <?php 
                                                $pdfUrl = ($sol['tipo_solicitud'] == 'Desechos Biológicos') 
                                                    ? base_url('desechos/verPdf/' . urlencode(basename($sol['ruta_pdf'])))
                                                    : base_url('bioseguridad/verPdf/' . urlencode(basename($sol['ruta_pdf'])));
                                            ?>
                                            <a href="<?= $pdfUrl ?>" target="_blank" class="btn btn-sm btn-danger">
                                                📄 PDF
                                            </a>
                                            <?php else: ?>
                                                <span class="text-muted small">Sin PDF</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                                $badgeClass = 'secondary';
                                                if ($sol['estado_solicitud'] == 'Pendiente') $badgeClass = 'warning';
                                                if ($sol['estado_solicitud'] == 'Entregado') $badgeClass = 'success';
                                                if ($sol['estado_solicitud'] == 'Cancelado') $badgeClass = 'danger';
                                            ?>
                                            <span class="badge bg-<?= $badgeClass ?>"><?= esc($sol['estado_solicitud']) ?></span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($sol['fecha_registro'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="4" class="text-muted py-5 text-center">No existen registros de solicitudes con los filtros aplicados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- PAGINACIÓN (igual que en index.php) -->
        <div class="d-flex justify-content-between align-items-center mt-4 mb-5">
            <nav aria-label="Navegación de solicitudes">
                <ul class="pagination custom-pagination m-0">
                    <?php if ($paginaActual > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= base_url('desechos/registroSolicitudes?page=1' . $urlParams) ?>" aria-label="Primero">
                                <span>&laquo;&laquo;</span>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="<?= base_url('desechos/registroSolicitudes?page=' . ($paginaActual - 1) . $urlParams) ?>" aria-label="Anterior">
                                <span>&larr;</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled"><span class="page-link">&laquo;&laquo;</span></li>
                        <li class="page-item disabled"><span class="page-link">&larr;</span></li>
                    <?php endif; ?>

                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= $i == $paginaActual ? 'active' : '' ?>">
                            <a class="page-link" href="<?= base_url('desechos/registroSolicitudes?page=' . $i . $urlParams) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($paginaActual < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= base_url('desechos/registroSolicitudes?page=' . ($paginaActual + 1) . $urlParams) ?>" aria-label="Siguiente">
                                <span>&rarr;</span>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="<?= base_url('desechos/registroSolicitudes?page=' . $totalPages . $urlParams) ?>" aria-label="Último">
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
                Mostrando del <strong><?= (($paginaActual - 1) * $porPagina) + 1 ?></strong> al <strong><?= min($paginaActual * $porPagina, $total) ?></strong> de un total de <strong><?= $total ?></strong> solicitudes.
            </div>
        </div>
    </div>

<script src="<?= base_url('bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>