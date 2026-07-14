<?php
/**
 * Vista: Bitácora de Auditoría (listado con filtros y paginación).
 * 
 * Muestra el historial de eventos del sistema con opciones de búsqueda,
 * filtros por tipo, rango de fechas y exportación a PDF.
 * 
 * Conexiones con el controlador:
 * - Formulario de filtros: envía GET a 'usuarios/bitacora' → BaseController::bitacora()
 *   (parámetros: buscar, desde, hasta, tipo)
 * - Enlace "Limpiar Filtros": redirige a 'usuarios/bitacora' sin parámetros.
 * - Botón PDF: abre modal que envía formulario a 'usuarios/generarPdfBitacora'
 *   → BaseController::generarPdfBitacora() (con parámetros ocultos de filtros + página_inicio/fin)
 * - Los mensajes flash (success/error) son mostrados vía SweetAlert2 y son generados
 *   por el controlador en operaciones como guardar/editar/eliminar.
 * 
 * Dependencias:
 * - Layout base (layouts/base) que incluye Bootstrap, estilos comunes y scripts.
 * - SweetAlert2 para mensajes flash.
 * - Pager de CodeIgniter para paginación (objeto $pager pasado desde el controlador).
 * - Datos: $bitacora (array de registros), $pager (objeto paginador).
 * 
 * @package App\Views\Bitacora
 */
?>
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
    /* Estilo para el botón de backup y dropdown */
    .btn-backup-action {
        transition: all 0.2s;
    }
    .btn-backup-action:active {
        transform: scale(0.95);
    }
    #backupDropdown .dropdown-item {
        font-size: 0.85rem;
        padding: 6px 12px;
    }
    #backupDropdown .dropdown-item .text-truncate {
        max-width: 140px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <!-- ===== MENSAJES FLASH CON SWEETALERT ===== -->
    <!-- Los mensajes flash son establecidos por el controlador BaseController en métodos bitacora() y generarPdfBitacora() -->
    <h2 class="main-title">Control de Solicitudes</h2>

    <!-- Filtros -->
    <div class="filter-bar mb-4">
        <!-- Formulario que envía GET a BaseController::bitacora() -->
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
                <!-- Enlace para limpiar filtros: redirige a BaseController::bitacora() sin parámetros -->
                <a href="<?= base_url('usuarios/bitacora') ?>" class="text-decoration-none text-dark d-flex align-items-center ms-1" title="Limpiar Filtros">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                        <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                    </svg>
                </a>
                <div class="vr mx-1"></div>
                <!-- Botón que abre el modal para generar PDF (invoca BaseController::generarPdfBitacora) -->
                <button type="button" class="btn btn-outline-danger p-1 border-0 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalPdf" title="Generar PDF">
                    <img src="<?= base_url('img/pdf.svg') ?>" alt="PDF" width="24">
                </button>
                <div class="vr mx-1"></div>

                <!-- ===== NUEVO: BOTÓN BACKUP CON DROPDOWN ===== -->
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-success p-1 border-0 d-flex align-items-center btn-backup-action" id="btnBackup" title="Crear Backup" onclick="crearBackup()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-database-fill" viewBox="0 0 16 16">
                            <path d="M3.904 1.777C4.978 1.289 6.427 1 8 1s3.022.289 4.096.777C13.125 2.245 14 2.993 14 4s-.875 1.755-1.904 2.223C11.022 6.711 9.573 7 8 7s-3.022-.289-4.096-.777C2.875 5.755 2 5.007 2 4s.875-1.755 1.904-2.223Z"/>
                            <path d="M2 7v1c0 .753.666 1.424 1.755 1.89C4.829 10.355 6.278 10.5 8 10.5s3.171-.145 4.245-.61C13.334 9.424 14 8.753 14 8V7c0 .753-.666 1.424-1.755 1.89C11.171 9.355 9.722 9.5 8 9.5s-3.171-.145-4.245-.61C2.666 8.424 2 7.753 2 7Z"/>
                            <path d="M2 11v1c0 .753.666 1.424 1.755 1.89C4.829 14.355 6.278 14.5 8 14.5s3.171-.145 4.245-.61C13.334 13.424 14 12.753 14 12v-1c0 .753-.666 1.424-1.755 1.89C11.171 13.355 9.722 13.5 8 13.5s-3.171-.145-4.245-.61C2.666 12.424 2 11.753 2 11Z"/>
                        </svg>
                    </button>
                    <button type="button" class="btn btn-outline-success p-1 border-0 d-flex align-items-center dropdown-toggle dropdown-toggle-split btn-backup-action" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="visually-hidden">Gestionar Backups</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" id="backupDropdown">
                        <li><span class="dropdown-header text-primary"><strong>Gestión de Backups</strong></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li id="backupListItems">
                            <span class="dropdown-item text-muted">Cargando backups...</span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-success" href="#" onclick="crearBackup(); return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle me-2" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                </svg>
                                Crear nuevo backup
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- ===== FIN BOTÓN BACKUP ===== -->
            </div>
        </form>
    </div>

    <!-- Tabla de registros (datos provistos por BaseController::bitacora()) -->
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

   <!-- Paginación (utiliza el objeto $pager del controlador) -->
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
                <!-- Enlaces de paginación que llaman a BaseController::bitacora() con parámetro page -->
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

    <!-- Enlaces de paginación que llaman a BaseController::bitacora() con parámetro page -->
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

    // ===== FUNCIONES PARA BACKUP =====
    function crearBackup() {
        const btn = document.getElementById('btnBackup');
        const originalHTML = btn.innerHTML;
        
        // Deshabilitar botón y mostrar spinner
        btn.disabled = true;
        btn.innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <span class="visually-hidden">Cargando...</span>
        `;
        btn.style.opacity = '0.7';

        fetch('<?= base_url('backup/create') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Restaurar botón
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            btn.style.opacity = '1';

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '✅ Backup creado',
                    text: 'Archivo: ' + data.filename,
                    confirmButtonColor: '#2073AF',
                    timer: 4000,
                    timerProgressBar: true
                });
                // Recargar lista de backups en el dropdown
                cargarListaBackups();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al crear backup',
                    text: data.message,
                    confirmButtonColor: '#d33'
                });
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            btn.style.opacity = '1';
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: error.message,
                confirmButtonColor: '#d33'
            });
        });
    }

    function cargarListaBackups() {
        const container = document.getElementById('backupListItems');
        container.innerHTML = '<span class="dropdown-item text-muted">Cargando...</span>';

        fetch('<?= base_url('backup/list') ?>')
            .then(response => response.json())
            .then(backups => {
                if (backups.length === 0) {
                    container.innerHTML = '<span class="dropdown-item text-muted">No hay backups disponibles</span>';
                    return;
                }

                // Mostrar solo los 5 más recientes
                const recent = backups.slice(0, 5);
                container.innerHTML = recent.map(b => `
                    <div class="dropdown-item d-flex justify-content-between align-items-center py-1">
                        <div class="d-flex flex-column">
                            <small class="text-truncate" style="max-width: 150px;">${b.filename}</small>
                            <small class="text-muted">${formatBytes(b.size)} - ${b.created_at}</small>
                        </div>
                        <div>
                            <a href="<?= base_url('backup/download') ?>/${b.filename}" 
                               class="text-success me-2" 
                               title="Descargar"
                               onclick="event.stopPropagation();">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                </svg>
                            </a>
                            <a href="#" 
                               class="text-danger" 
                               title="Eliminar"
                               onclick="eliminarBackup('${b.filename}'); event.stopPropagation(); return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                `).join('');

                if (backups.length > 5) {
                    container.innerHTML += `
                        <div class="dropdown-item text-center text-muted small">
                            + ${backups.length - 5} más...
                        </div>
                    `;
                }
            })
            .catch(error => {
                container.innerHTML = '<span class="dropdown-item text-danger">Error al cargar backups</span>';
                console.error('Error:', error);
            });
    }

    function eliminarBackup(filename) {
        Swal.fire({
            title: '¿Eliminar backup?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('<?= base_url('backup/delete') ?>/' + filename, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        cargarListaBackups();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: error.message
                    });
                });
            }
        });
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Cargar lista de backups al abrir el dropdown
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggle = document.querySelector('[data-bs-toggle="dropdown"]');
        if (dropdownToggle) {
            dropdownToggle.addEventListener('click', function() {
                // Pequeño delay para que el dropdown se abra
                setTimeout(cargarListaBackups, 100);
            });
        }
        
        // También cargar al cargar la página (para tener datos en caché)
        cargarListaBackups();
    });
</script>
<?= $this->endSection() ?>