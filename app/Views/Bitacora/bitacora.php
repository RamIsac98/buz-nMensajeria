<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Bitácora de Auditoría<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Estilos específicos de esta página */
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
    .input-search-width { width: 180px; }
    .input-select-width { width: 140px; }
    .main-title {
        color: var(--azul-oscuro);
        font-weight: bold;
        margin-top: 35px;
        margin-bottom: 25px;
        font-size: 1.75rem;
    }
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
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <!-- ===== MENSAJES FLASH AHORA CON SWEETALERT (eliminadas alertas Bootstrap) ===== -->

    <h2 class="main-title">Control de Solicitudes</h2>

    <!-- Filtros -->
    <div class="filter-bar mb-4">
        <form method="GET" action="<?= base_url('usuarios/bitacora') ?>" class="row g-2 align-items-center justify-content-between">
            <div class="col-auto filter-group">
                <span class="filter-label">Buscador</span>
                <input type="text" name="buscar" class="filter-input input-search-width"
                       placeholder="Usuario, IP, registro..." value="<?= esc($_GET['buscar'] ?? '') ?>">
            </div>

            <div class="col-auto filter-group">
                <span class="filter-label">Periodo</span>
                <input type="date" name="desde" class="filter-input" value="<?= esc($_GET['desde'] ?? '') ?>">
                <span class="text-muted small">al</span>
                <input type="date" name="hasta" class="filter-input" value="<?= esc($_GET['hasta'] ?? '') ?>">
            </div>

            <div class="col-auto filter-group">
                <span class="filter-label">Filtrar</span>
                <select name="tipo" class="filter-input bg-white input-select-width">
                    <option value="">-- Todos --</option>
                    <option value="Sesión" <?= (($_GET['tipo'] ?? '') === 'Sesión') ? 'selected' : '' ?>>Sesión</option>
                    <option value="Administración" <?= (($_GET['tipo'] ?? '') === 'Administración') ? 'selected' : '' ?>>Administración</option>
                    <option value="Seguridad" <?= (($_GET['tipo'] ?? '') === 'Seguridad') ? 'selected' : '' ?>>Seguridad</option>
                    <option value="Servicio Desechos" <?= (($_GET['tipo'] ?? '') === 'Servicio Desechos') ? 'selected' : '' ?>>Servicio Desechos</option>
                    <option value="Servicio Bioseguridad" <?= (($_GET['tipo'] ?? '') === 'Servicio Bioseguridad') ? 'selected' : '' ?>>Servicio Bioseguridad</option>
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

    <!-- Tabla -->
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

    <!-- Paginación -->
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
        
        <nav aria-label="Navegación de bitácora">
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
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">&laquo;&laquo;</span></li>
                    <li class="page-item disabled"><span class="page-link">&larr;</span></li>
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
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">&rarr;</span></li>
                    <li class="page-item disabled"><span class="page-link">&raquo;&raquo;</span></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="footer-text">
            Mostrando del <strong><?= $from ?></strong> al <strong><?= $to ?></strong> de un total de <strong><?= $total ?></strong> eventos.
        </div>
    </div>

    <!-- Modal para generar PDF (específico de esta página) -->
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
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- SweetAlert2 CDN (si no está ya en el layout) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Leer flashdata desde PHP
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
<?= $this->endSection() ?>