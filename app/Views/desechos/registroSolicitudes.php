<?php
/**
 * Vista: Historial de Solicitudes (registro para usuarios regulares).
 * 
 * Muestra el historial de solicitudes combinadas de desechos y bioseguridad
 * con filtros por tipo, estado y rango de fechas. Permite visualizar PDFs
 * y editar solicitudes (si el usuario es el creador o administrador y la
 * solicitud no ha sido editada previamente).
 * 
 * Conexiones con el controlador:
 * - Carga inicial y filtros (GET): DesechosController::registroSolicitudes()
 *   Ruta: '/desechos/registroSolicitudes'
 *   Recibe: $solicitudes, $filtros, $tiposSolicitud, $estadosSolicitud,
 *           variables de paginación ($paginaActual, $totalPages, etc.)
 * 
 * - Generación de PDF:
 *   - Para desechos: '/desechos/generarPdf/{id}' → DesechosController::generarPdf($id)
 *   - Para bioseguridad: '/bioseguridad/generarPdf/{id}' → BioseguridadController::generarPdf($id)
 *   Ambos abren el PDF en una nueva pestaña (target="_blank").
 * 
 * - Edición de solicitudes (botón lápiz):
 *   - Para desechos: '/desechos/editar/{id}' → DesechosController::editar($id)
 *   - Para bioseguridad: '/bioseguridad/editar/{id}' → BioseguridadController::editar($id)
 *   Solo visible si el usuario es el creador o administrador Y $editado == 0.
 *   El controlador verifica permisos y restricción de edición única.
 * 
 * - Limpiar filtros: Redirige a '/desechos/registroSolicitudes' sin parámetros
 *   → DesechosController::registroSolicitudes()
 * 
 * - Mensajes flash:
 *   - 'success' → SweetAlert2 (icono éxito, timer 4s). Generado por el controlador
 *     tras operaciones exitosas (edición, registro, etc.).
 *   - 'error' → SweetAlert2 (icono error, timer 5s, botón confirmar). Generado por
 *     validaciones fallidas o errores de base de datos.
 * 
 * Dependencias:
 * - Layout base (layouts/base) con Bootstrap 5.
 * - SweetAlert2 (CDN) para mensajes flash.
 * - Logo y assets desde public/img/ (pdf.svg, pensil.png).
 * 
 * @package App\Views\desechos
 */
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Historial de Solicitudes<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    :root {
        --azul-claro: #2073AF;
        --azul-oscuro: rgba(28, 70, 110, 0.9);
        --amarillo: #ffc107;
    }

    .thead-custom {
        background-color: var(--azul-oscuro);
        color: white;
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
    .custom-pagination .page-item.active .page-link {
        background-color: var(--azul-oscuro);
    }
    .footer-text {
        color: var(--azul-claro);
        font-weight: bold;
    }

    /* Botón de editar (lápiz) igual que en gestión */
    .btn-editar {
        background: none;
        border: none;
        padding: 0;
        transition: opacity 0.2s;
        display: inline-flex;
        align-items: center;
    }
    .btn-editar:hover {
        opacity: 0.7;
    }
    .btn-editar:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid my-5 px-4">
    <h2 class="main-title">Historial de Solicitudes</h2>

    <!-- ===== MENSAJES FLASH AHORA CON SWEETALERT (eliminadas alertas Bootstrap) ===== -->

    <!-- Filtros -->
    <div class="filter-bar mb-4 mt-3">
        <form method="GET" action="<?= base_url('desechos/registroSolicitudes') ?>" class="row g-2 align-items-center justify-content-between">
            <div class="col-auto filter-group">
                <span class="filter-label">Tipo Solicitud</span>
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

    <!-- Tabla con acciones -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-custom">
                        <tr>
                            <th>TIPO SOLICITUD</th>
                            <th>USUARIO</th>
                            <th>INFORME</th>
                            <th>ESTADO SOLICITUD</th>
                            <th>FECHA SOLICITADA</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($solicitudes)) : ?>
                            <?php foreach ($solicitudes as $sol) : ?>
                                <tr>
                                    <td class="fw-bold"><?= esc($sol['tipo_solicitud']) ?></td>
                                    <td><?= esc($sol['username'] ?? 'N/D') ?></td>
                                    <td>
                                        <a href="<?= base_url(($sol['tipo_solicitud'] == 'Desechos Biológicos' ? 'desechos' : 'bioseguridad') . '/generarPdf/' . $sol['id']) ?>" target="_blank">
                                            <img src="<?= base_url('img/pdf.svg') ?>" alt="PDF" width="24" height="24">
                                        </a>
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
                                    <td>
                                        <?php
                                        $usuario_actual = session()->get('usuario_id');
                                        $es_creador = ($usuario_actual == $sol['usuario_id']);
                                        $es_admin = (session()->get('rol') === 'administrador');
                                        $editado = $sol['editado'] ?? 0;

                                        if (($es_creador || $es_admin) && $editado == 0) {
                                            $url_editar = base_url(($sol['tipo_solicitud'] == 'Desechos Biológicos' ? 'desechos' : 'bioseguridad') . '/editar/' . $sol['id']);
                                            echo '<a href="'.$url_editar.'" class="btn-editar" title="Editar solicitud">
                                                    <img src="'.base_url('img/pensil.png').'" alt="Editar" width="20" height="20">
                                                  </a>';
                                        } else {
                                            echo '<span class="text-muted small">No editable</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="text-muted py-5 text-center">No existen registros con los filtros aplicados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-5">
        <nav aria-label="Navegación de solicitudes">
            <ul class="pagination custom-pagination m-0">
                <?php if ($paginaActual > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= base_url('desechos/registroSolicitudes?page=1' . $urlParams) ?>">&laquo;&laquo;</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?= base_url('desechos/registroSolicitudes?page=' . ($paginaActual - 1) . $urlParams) ?>">&larr;</a>
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
                        <a class="page-link" href="<?= base_url('desechos/registroSolicitudes?page=' . ($paginaActual + 1) . $urlParams) ?>">&rarr;</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?= base_url('desechos/registroSolicitudes?page=' . $totalPages . $urlParams) ?>">&raquo;&raquo;</a>
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- SweetAlert2 (si no está en el layout) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Leer flashdata desde PHP (success y error)
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