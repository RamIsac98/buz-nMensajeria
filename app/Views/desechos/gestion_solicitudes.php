<?php
/**
 * Vista: Gestión de Solicitudes (panel de administración).
 * 
 * Muestra un listado combinado de solicitudes de desechos y bioseguridad,
 * con opciones para filtrar, cambiar estado, editar peso de desechos (solo para
 * administradores/protección integral) y generar PDFs.
 * 
 * Conexiones con el controlador:
 * - Carga inicial y filtros (GET): DesechosController::gestionSolicitudes()
 *   Ruta: '/desechos/gestionSolicitudes'
 *   Recibe: $solicitudes, $filtros, $tiposSolicitud, $estadosSolicitud,
 *           variables de paginación ($paginaActual, $totalPages, etc.)
 * 
 * - Cambio de estado (POST): DesechosController::actualizarEstado()
 *   Ruta: '/desechos/actualizarEstado'
 *   Llamada vía AJAX/fetch con parámetros: id, tipo, estado
 *   Retorna JSON { success: true/false, error: mensaje }
 * 
 * - Obtener datos para editar peso (GET): DesechosController::obtenerPeso($id)
 *   Ruta: '/desechos/obtenerPeso/{id}'
 *   Llamada vía AJAX/fetch, retorna JSON con id, codigo, peso_kg, peso_l, estado_fisico
 * 
 * - Actualizar peso (POST): DesechosController::actualizarPeso($id)
 *   Ruta: '/desechos/actualizarPeso/{id}'
 *   Llamada vía AJAX/fetch con FormData (peso_kg, peso_l)
 *   Retorna JSON { success: true/false, error/message }
 * 
 * - Generación de PDF:
 *   - Para desechos: '/desechos/generarPdf/{id}' → DesechosController::generarPdf($id)
 *   - Para bioseguridad: '/bioseguridad/generarPdf/{id}' → BioseguridadController::generarPdf($id)
 * 
 * - Limpiar filtros: Redirige a '/desechos/gestionSolicitudes' sin parámetros
 *   → DesechosController::gestionSolicitudes()
 * 
 * - Mensajes flash:
 *   - 'success' → SweetAlert2 (icono éxito, timer 4s). Generado por el controlador
 *     tras operaciones exitosas (cambio de estado, actualización de peso, etc.)
 *   - 'error' → SweetAlert2 (icono error, timer 5s, botón confirmar). Generado por
 *     validaciones fallidas o errores de base de datos.
 * 
 * Dependencias:
 * - Layout base (layouts/base) con Bootstrap 5.
 * - SweetAlert2 (CDN) para mensajes flash.
 * - JavaScript para peticiones AJAX (cambio de estado, edición de peso).
 * - CSRF token en meta tag para seguridad en peticiones.
 * 
 * @package App\Views\desechos
 */
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Gestión de Solicitudes<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<style>
    /* ===== ESTILOS ESPECÍFICOS DE LA PÁGINA ===== */
    .main-title {
        color: var(--azul-oscuro);
        font-weight: bold;
        margin-top: 35px;
        margin-bottom: 25px;
        font-size: 1.75rem;
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
    .filter-input:focus {
        border-color: var(--azul-claro);
    }
    .input-search-width { width: 180px; }
    .input-select-width { width: 140px; }
    .input-date-width { width: 130px; }
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
    .btn-actualizar {
        background-color: var(--azul-claro);
        color: white;
        border: none;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 0.75rem;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .btn-actualizar:hover {
        background-color: var(--azul-oscuro);
    }
    .estado-select {
        width: 110px;
        padding: 4px 6px;
        font-size: 0.75rem;
        border-radius: 4px;
    }
    .badge-pendiente { background-color: #ffc107; color: #212529; }
    .badge-entregado { background-color: #28a745; color: white; }
    .badge-cancelado { background-color: #dc3545; color: white; }
    .estado-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 0.25rem;
        text-align: center;
        min-width: 85px;
    }
    .btn-editar {
        background: none;
        border: none;
        padding: 0;
        transition: opacity 0.2s;
    }
    .btn-editar:hover {
        opacity: 0.7;
    }
    .btn-editar:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    .table {
        min-width: 800px;
    }
    .table td, .table th {
        vertical-align: middle;
        white-space: nowrap;
    }
    .table td:first-child, .table th:first-child {
        padding-left: 1rem;
    }
    .table td:last-child, .table th:last-child {
        padding-right: 1rem;
    }
    @media (max-width: 768px) {
        .table {
            min-width: 100%;
        }
        .estado-select {
            width: 85px;
        }
        .btn-actualizar {
            padding: 2px 8px;
            font-size: 0.7rem;
        }
    }
    /* Estilo para campos deshabilitados */
    .form-control:disabled {
        background-color: #e9ecef;
        opacity: 0.7;
        cursor: not-allowed;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid my-5 px-4">
    <h2 class="main-title">Gestión de Solicitudes</h2>

    <!-- ===== MENSAJES FLASH AHORA CON SWEETALERT (eliminadas alertas Bootstrap) ===== -->

    <!-- Filtros -->
    <div class="filter-bar mb-4 mt-3">
        <form method="GET" action="<?= base_url('desechos/gestionSolicitudes') ?>" class="row g-2 align-items-center justify-content-between">
            <div class="col-auto filter-group">
                <span class="filter-label">Buscar</span>
                <input type="text" name="buscar" class="filter-input input-search-width" placeholder="Centro" value="<?= esc($filtros['buscar'] ?? '') ?>">
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

    <!-- Tabla de Solicitudes -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-custom">
                        <tr>
                            <th>TIPO SOLICITUD</th>
                            <th>USUARIO</th>
                            <th>CENTRO</th>
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
                                    <td class="fw-bold"><?= esc($sol['tipo_solicitud']) ?></td>
                                    <td><?= esc($sol['username'] ?? 'N/D') ?></td>
                                    <td><?= esc($sol['nombre_departamento'] ?? 'N/D') ?></td>
                                    <td><?= esc($sol['nombre_laboratorio'] ?? 'N/D') ?></td>
                                    <td><?= date('d/m/Y', strtotime($sol['fecha_registro'])) ?></td>
                                    <td class="estado-cell">
                                        <span class="estado-badge 
                                            <?= $sol['estado_solicitud'] == 'Pendiente' ? 'badge-pendiente' : '' ?>
                                            <?= $sol['estado_solicitud'] == 'Entregado' ? 'badge-entregado' : '' ?>
                                            <?= $sol['estado_solicitud'] == 'Cancelado' ? 'badge-cancelado' : '' ?>">
                                            <?= esc($sol['estado_solicitud']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 align-items-center">
                                            <a href="<?= base_url(($sol['tipo_solicitud'] == 'Desechos Biológicos' ? 'desechos' : 'bioseguridad') . '/generarPdf/' . $sol['id']) ?>" target="_blank" title="Ver PDF">
                                                <img src="<?= base_url('img/pdf.svg') ?>" alt="PDF" width="24" height="24">
                                            </a>
                                            <?php if (in_array(session()->get('rol'), ['administrador', 'proteccion_integral']) && $sol['tipo_solicitud'] == 'Desechos Biológicos'): ?>
                                                <button type="button" class="btn-editar" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEditarPeso"
                                                        data-id="<?= $sol['id'] ?>"
                                                        title="Editar peso de la solicitud">
                                                    <img src="<?= base_url('img/pensil.png') ?>" alt="Editar peso" width="20" height="20">
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 align-items-center">
                                            <select class="form-select form-select-sm estado-select" data-id="<?= $sol['id'] ?>" data-tipo="<?= $sol['tabla_origen'] ?>">
                                                <option value="Pendiente" <?= $sol['estado_solicitud'] == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                <option value="Entregado" <?= $sol['estado_solicitud'] == 'Entregado' ? 'selected' : '' ?>>Entregado</option>
                                                <option value="Cancelado" <?= $sol['estado_solicitud'] == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                            </select>
                                            <button class="btn btn-actualizar btn-actualizar-estado" data-id="<?= $sol['id'] ?>" data-tipo="<?= $sol['tabla_origen'] ?>">Actualizar</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">No hay solicitudes con los filtros aplicados.</td>
                            </tr>
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

<!-- ===== MODAL EDITAR PESO ===== -->
<div class="modal fade" id="modalEditarPeso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: var(--azul-oscuro);">
                <h5 class="modal-title">Editar Peso de la Solicitud</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarPeso" action="" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="peso_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Código de Solicitud</label>
                        <input type="text" id="peso_codigo" class="form-control" readonly>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-info text-dark" id="estadoFisicoBadge">Estado físico: Cargando...</span>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Peso (Kg)</label>
                            <input type="number" step="0.01" name="peso_kg" id="peso_kg" class="form-control" min="0" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Volumen (L)</label>
                            <input type="number" step="0.01" name="peso_l" id="peso_l" class="form-control" min="0" placeholder="0.00">
                        </div>
                    </div>
                    <small class="text-muted" id="mensajeEdicion">Ingresa valores numéricos mayores o iguales a 0. El campo que no aplica estará deshabilitado.</small>
                </div>
                <div class="modal-footer bg-light border-top p-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" style="background-color: var(--azul-claro); color: white;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Obtener el token CSRF
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

// =============================================
// 0. MENSAJES FLASH CON SWEETALERT2 (igual que en bitacora)
// =============================================
document.addEventListener('DOMContentLoaded', function() {
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

// =============================================
// 1. CAMBIO DE ESTADO (botón Actualizar)
// =============================================
document.querySelectorAll('.btn-actualizar-estado').forEach(btn => {
    btn.addEventListener('click', function() {
        const row = this.closest('tr');
        const select = row.querySelector('.estado-select');
        const id = select.getAttribute('data-id');
        const tipo = select.getAttribute('data-tipo');
        const nuevoEstado = select.value;
        const boton = this;
        const spanEstado = row.querySelector('.estado-badge');

        if (!spanEstado) {
            Swal.fire({
                icon: 'error',
                title: 'Error interno',
                text: 'No se pudo localizar el elemento de estado.',
                confirmButtonColor: '#0d3b66'
            });
            return;
        }
        if (!id || !tipo) {
            Swal.fire({
                icon: 'error',
                title: 'Datos incompletos',
                text: 'Falta el ID o tipo de solicitud.',
                confirmButtonColor: '#0d3b66'
            });
            return;
        }

        boton.disabled = true;
        boton.textContent = 'Guardando...';

        const formData = new URLSearchParams();
        formData.append('id', id);
        formData.append('tipo', tipo);
        formData.append('estado', nuevoEstado);
        formData.append('<?= csrf_token() ?>', getCsrfToken());

        fetch('<?= base_url('desechos/actualizarEstado') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const clases = {
                    'Pendiente': 'badge-pendiente',
                    'Entregado': 'badge-entregado',
                    'Cancelado': 'badge-cancelado'
                };
                spanEstado.className = 'estado-badge ' + (clases[nuevoEstado] || '');
                spanEstado.textContent = nuevoEstado;
                select.value = nuevoEstado;

                Swal.fire({
                    icon: 'success',
                    title: 'Estado actualizado',
                    text: `La solicitud ahora está en estado "${nuevoEstado}".`,
                    confirmButtonColor: '#0d3b66',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.error || 'No se pudo actualizar el estado.',
                    confirmButtonColor: '#0d3b66'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor.',
                confirmButtonColor: '#0d3b66'
            });
        })
        .finally(() => {
            boton.disabled = false;
            boton.textContent = 'Actualizar';
        });
    });
});

// =============================================
// 2. EDITAR PESO - Cargar datos en el modal
// =============================================
document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#modalEditarPeso"]').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');

        if (!id) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo obtener el ID de la solicitud.',
                confirmButtonColor: '#0d3b66'
            });
            return;
        }

        const url = '<?= base_url('desechos/obtenerPeso/') ?>' + id;

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Error en la respuesta del servidor');
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.error,
                        confirmButtonColor: '#0d3b66'
                    });
                    return;
                }

                // Rellenar campos
                document.getElementById('peso_id').value = data.id;
                document.getElementById('peso_codigo').value = data.codigo || 'Sin código';
                document.getElementById('peso_kg').value = data.peso_kg || '';
                document.getElementById('peso_l').value = data.peso_l || '';
                document.getElementById('formEditarPeso').action = '<?= base_url('desechos/actualizarPeso/') ?>' + data.id;

                // Mostrar estado físico y habilitar/deshabilitar campos
                const estadoFisico = data.estado_fisico || '';
                const kgInput = document.getElementById('peso_kg');
                const lInput = document.getElementById('peso_l');
                const badgeEstado = document.getElementById('estadoFisicoBadge');
                const mensaje = document.getElementById('mensajeEdicion');

                badgeEstado.textContent = 'Estado físico: ' + (estadoFisico || 'No definido');
                badgeEstado.className = 'badge ' + (estadoFisico.includes('Sólido') ? 'bg-primary' : 'bg-secondary') + ' text-white';

                const esSólido = estadoFisico.includes('Sólido');
                const esLíquido = estadoFisico.includes('Líquido');

                kgInput.disabled = false;
                lInput.disabled = false;
                kgInput.placeholder = '0.00';
                lInput.placeholder = '0.00';

                if (esSólido && !esLíquido) {
                    lInput.disabled = true;
                    lInput.placeholder = 'No aplica (Sólido)';
                    lInput.value = '';
                    mensaje.textContent = 'Solo se puede editar el peso en kg (solicitud Sólida).';
                } else if (esLíquido && !esSólido) {
                    kgInput.disabled = true;
                    kgInput.placeholder = 'No aplica (Líquido)';
                    kgInput.value = '';
                    mensaje.textContent = 'Solo se puede editar el volumen en litros (solicitud Líquida).';
                } else {
                    mensaje.textContent = 'Puede editar ambos campos (la solicitud tiene Sólido y Líquido).';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'Error al cargar los datos del peso.',
                    confirmButtonColor: '#0d3b66'
                });
            });
    });
});

// =============================================
// 3. ENVÍO DEL FORMULARIO DE EDICIÓN DE PESO
// =============================================
document.getElementById('formEditarPeso').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = e.target;
    const kgInput = document.getElementById('peso_kg');
    const lInput = document.getElementById('peso_l');
    const kgVal = kgInput.value.trim();
    const lVal = lInput.value.trim();

    // Validar solo campos habilitados
    if (!kgInput.disabled && kgVal !== '') {
        const kg = parseFloat(kgVal);
        if (isNaN(kg) || kg < 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Dato inválido',
                text: 'El peso en kg debe ser un número mayor o igual a 0.',
                confirmButtonColor: '#0d3b66'
            });
            return;
        }
    }
    if (!lInput.disabled && lVal !== '') {
        const l = parseFloat(lVal);
        if (isNaN(l) || l < 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Dato inválido',
                text: 'El volumen en litros debe ser un número mayor o igual a 0.',
                confirmButtonColor: '#0d3b66'
            });
            return;
        }
    }

    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.textContent;

    submitBtn.disabled = true;
    submitBtn.textContent = 'Guardando...';

    fetch(form.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const modalElement = document.getElementById('modalEditarPeso');
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
            modalInstance.hide();

            Swal.fire({
                icon: 'success',
                title: 'Peso actualizado',
                text: data.message || 'Los valores se actualizaron correctamente.',
                confirmButtonColor: '#0d3b66',
                timer: 2000,
                timerProgressBar: true
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error || 'No se pudo actualizar el peso.',
                confirmButtonColor: '#0d3b66'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'Intenta nuevamente. Detalle: ' + error.message,
            confirmButtonColor: '#0d3b66'
        });
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
    });
});
</script>
<?= $this->endSection() ?>