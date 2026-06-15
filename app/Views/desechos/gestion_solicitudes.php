<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Solicitudes</title>
    <link rel="stylesheet" href="<?= base_url('bootstrap5/css/bootstrap.min.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('img/logo.svg') ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <style>
        :root {
            --azul-claro: #2073AF;
            --azul-oscuro: rgba(28, 70, 110, 0.9);
            --amarillo: #ffc107;
        }
        body {
            background-color: #ffffff;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
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
            padding: 0 25px;
            height: 65px;
            font-size: 1rem;
            transition: 0.2s;
        }
        .nav-link-custom:hover {
            background-color: rgba(0,0,0,0.1);
            color: white;
        }
        .nav-link-custom.active {
            background-color: var(--azul-oscuro);
            color: var(--amarillo) !important;
            font-weight: 500;
        }
        .user-section {
            padding-right: 25px;
        }
        .user-dropdown-toggle {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
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
        .thead-custom { background-color: var(--azul-oscuro); color: white; }
        .btn-custom-sm {
            background-color: var(--azul-claro);
            color: white;
            border: none;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .btn-custom-sm:hover { background-color: var(--azul-oscuro); }
    </style>
</head>
<body>

<header class="mb-4">
    <?= view('layouts/_navbar') ?>
</header>

<div class="container-fluid my-5 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="main-title">Gestión de Solicitudes (Cambio de Estado)</h2>
    </div>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="filter-bar mb-4 mt-3">
        <form method="GET" action="<?= base_url('desechos/gestionSolicitudes') ?>" class="row g-2 align-items-center justify-content-between">
            <div class="col-auto filter-group">
                <span class="filter-label">Buscar</span>
                <input type="text" name="buscar" class="filter-input input-search-width" placeholder="Código o Usuario..." value="<?= esc($filtros['buscar'] ?? '') ?>">
            </div>
            <div class="col-auto filter-group">
                <span class="filter-label">Tipo Solicitud</span>
                <select name="tipo_solicitud" class="filter-input input-select-width">
                    <option value="">-- Todos --</option>
                    <?php foreach ($tiposSolicitud as $tipo): ?>
                        <option value="<?= $tipo ?>" <?= ($filtros['tipo_solicitud'] ?? '') == $tipo ? 'selected' : '' ?>><?= $tipo ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto filter-group">
                <span class="filter-label">Estado Solicitud</span>
                <select name="estado_solicitud" class="filter-input input-select-width">
                    <option value="">-- Todos --</option>
                    <?php foreach ($estadosSolicitud as $est): ?>
                        <option value="<?= $est ?>" <?= ($filtros['estado_solicitud'] ?? '') == $est ? 'selected' : '' ?>><?= $est ?></option>
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
                <a href="<?= base_url('desechos/gestionSolicitudes') ?>" class="text-decoration-none text-dark" title="Limpiar Filtros">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                        <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                    </svg>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla con columna de acciones -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-custom">
                        <tr>
                            <th>TIPO SOLICITUD</th>
                            <th>USUARIO</th>
                            <th>CENTRO / DEPARTAMENTO</th>
                            <th>LABORATORIO</th>
                            <th>FECHA</th>
                            <th>ESTADO ACTUAL</th>
                            <th>REPORTE</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($solicitudes)) : ?>
                            <?php foreach ($solicitudes as $sol) : ?>
                                <tr id="fila-<?= $sol['id'] ?>">
                                    <td><?= esc($sol['tipo_solicitud']) ?></td>
                                    <td><?= esc($sol['username'] ?? 'N/D') ?></td>
                                    <td><?= esc($sol['nombre_departamento'] ?? 'N/D') ?></td>
                                    <td><?= esc($sol['nombre_laboratorio'] ?? 'N/D') ?></td>
                                    <td><?= date('d/m/Y', strtotime($sol['fecha_registro'])) ?></td>
                                    <td>
                                        <span class="badge 
                                            <?= $sol['estado_solicitud'] == 'Pendiente' ? 'bg-warning' : '' ?>
                                            <?= $sol['estado_solicitud'] == 'Entregado' ? 'bg-success' : '' ?>
                                            <?= $sol['estado_solicitud'] == 'Cancelado' ? 'bg-danger' : '' ?>">
                                            <?= esc($sol['estado_solicitud']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($sol['ruta_pdf'])): ?>
                                            <?php 
                                                $pdfUrl = ($sol['tipo_solicitud'] == 'Desechos Biológicos') 
                                                    ? base_url('desechos/verPdf/' . urlencode(basename($sol['ruta_pdf'])))
                                                    : base_url('bioseguridad/verPdf/' . urlencode(basename($sol['ruta_pdf'])));
                                            ?>
                                            <a href="<?= $pdfUrl ?>" target="_blank" title="Ver PDF">
                                                <img src="<?= base_url('img/pdf.svg') ?>" alt="PDF" width="24" height="24">
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Sin PDF</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 align-items-center">
                                            <select class="form-select form-select-sm estado-select" style="width: 130px;" data-id="<?= $sol['id'] ?>" data-tipo="<?= $sol['tabla_origen'] ?>">
                                                <option value="Pendiente" <?= $sol['estado_solicitud'] == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                <option value="Entregado" <?= $sol['estado_solicitud'] == 'Entregado' ? 'selected' : '' ?>>Entregado</option>
                                                <option value="Cancelado" <?= $sol['estado_solicitud'] == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                            </select>
                                            <button class="btn btn-custom-sm btn-actualizar" data-id="<?= $sol['id'] ?>" data-tipo="<?= $sol['tabla_origen'] ?>">Actualizar</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="8" class="text-center py-5">No hay solicitudes con los filtros aplicados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-5">
        <nav aria-label="Navegación">
            <ul class="pagination custom-pagination m-0">
                <?php if ($paginaActual > 1): ?>
                    <li class="page-item"><a class="page-link" href="<?= base_url('desechos/gestionSolicitudes?page=1' . $urlParams) ?>">&laquo;&laquo;</a></li>
                    <li class="page-item"><a class="page-link" href="<?= base_url('desechos/gestionSolicitudes?page=' . ($paginaActual - 1) . $urlParams) ?>">&larr;</a></li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">&laquo;&laquo;</span></li>
                    <li class="page-item disabled"><span class="page-link">&larr;</span></li>
                <?php endif; ?>
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <li class="page-item <?= $i == $paginaActual ? 'active' : '' ?>">
                        <a class="page-link" href="<?= base_url('desechos/gestionSolicitudes?page=' . $i . $urlParams) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($paginaActual < $totalPages): ?>
                    <li class="page-item"><a class="page-link" href="<?= base_url('desechos/gestionSolicitudes?page=' . ($paginaActual + 1) . $urlParams) ?>">&rarr;</a></li>
                    <li class="page-item"><a class="page-link" href="<?= base_url('desechos/gestionSolicitudes?page=' . $totalPages . $urlParams) ?>">&raquo;&raquo;</a></li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">&rarr;</span></li>
                    <li class="page-item disabled"><span class="page-link">&raquo;&raquo;</span></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="footer-text">
            Mostrando del <strong><?= (($paginaActual - 1) * $porPagina) + 1 ?></strong> al <strong><?= min($paginaActual * $porPagina, $total) ?></strong> de <strong><?= $total ?></strong> solicitudes.
        </div>
    </div>
</div>

<script src="<?= base_url('bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>
<script>
// Función para obtener el token CSRF
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

// Actualizar estado mediante AJAX
document.querySelectorAll('.btn-actualizar').forEach(btn => {
    btn.addEventListener('click', function() {
        const row = this.closest('tr');
        const select = row.querySelector('.estado-select');
        const id = select.getAttribute('data-id');
        const tipo = select.getAttribute('data-tipo');
        const nuevoEstado = select.value;
        const boton = this;
        const spanEstado = row.querySelector('td:nth-child(5) span');

        // Deshabilitar botón durante la petición
        boton.disabled = true;
        boton.textContent = 'Guardando...';

        fetch('<?= base_url('desechos/actualizarEstado') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                'id': id,
                'tipo': tipo,
                'estado': nuevoEstado,
                '<?= csrf_token() ?>': getCsrfToken()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar badge de estado
                let badgeClass = '';
                if (nuevoEstado === 'Pendiente') badgeClass = 'bg-warning';
                else if (nuevoEstado === 'Entregado') badgeClass = 'bg-success';
                else if (nuevoEstado === 'Cancelado') badgeClass = 'bg-danger';
                spanEstado.className = 'badge ' + badgeClass;
                spanEstado.textContent = nuevoEstado;
                // Mostrar mensaje temporal
                const alerta = document.createElement('div');
                alerta.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
                alerta.style.zIndex = '9999';
                alerta.innerHTML = 'Estado actualizado correctamente. <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                document.body.appendChild(alerta);
                setTimeout(() => alerta.remove(), 3000);
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión. Intente nuevamente.');
        })
        .finally(() => {
            boton.disabled = false;
            boton.textContent = 'Actualizar';
        });
    });
});
</script>
</body>
</html>