<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Gestión de Departamentos y Laboratorios<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Estilos específicos de esta página */
    .sub-title {
        color: var(--azul-claro);
        font-size: 1.2rem;
        font-weight: bold;
        border-bottom: 2px solid var(--amarillo);
        padding-bottom: 8px;
    }
    .btn-custom {
        background-color: var(--azul-claro);
        color: white;
        border: none;
        padding: 6px 10px;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .btn-custom:hover {
        background-color: var(--azul-oscuro);
        color: white;
        transform: translateY(-1px);
    }
    .btn-outline-custom {
        color: var(--azul-claro);
        border: 1px solid var(--azul-claro);
        background: transparent;
        padding: 6px 10px;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .btn-outline-custom:hover {
        background-color: var(--azul-claro);
        color: white;
    }
    .btn-icon {
        background: none;
        border: none;
        padding: 2px 5px;
        transition: transform 0.2s;
    }
    .btn-icon:hover { transform: scale(1.2); }
    .btn-icon svg { width: 20px; height: 20px; }
    .empty-state { text-align: center; padding: 30px; color: #888; font-style: italic; }
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
    .text-gold { color: var(--amarillo); }
    .filter-bar {
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        padding: 12px 15px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .filter-label {
        font-size: 0.85rem;
        font-weight: bold;
        color: var(--azul-oscuro);
        text-transform: uppercase;
    }
    .filter-input {
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        padding: 6px 12px;
        font-size: 0.95rem;
        outline: none;
        min-width: 250px;
        transition: all 0.2s ease;
    }
    .filter-input:focus { border-color: var(--azul-claro); }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <!-- Mensajes flash -->
    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-custom-success alert-dismissible fade show py-2" role="alert">
            <strong>¡Éxito!</strong> <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close btn-close-white py-2" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
            <strong>¡Error!</strong> <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <h2 class="main-title mb-4 mt-3">Panel de Gestión de Centros / Laboratorio</h2>

    <!-- Barra de filtros y botones -->
    <div class="filter-bar mb-4 mt-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="filter-label">Buscar Laboratorios por Centro:</span>
            <select id="filtroDepartamento" class="filter-input bg-white">
                <option value="todos">-- Mostrar Todos --</option>
                <?php if(!empty($todos_departamentos)): ?>
                    <?php foreach ($todos_departamentos as $depto): ?>
                        <option value="<?= $depto['id'] ?>"><?= esc($depto['nombre']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="d-flex gap-3">
            <button class="btn btn-danger btn-sm fw-bold shadow-sm btn-pdf-trigger" data-type="general">
                <img src="<?= base_url('img/pdf.svg') ?>" style="width:18px; vertical-align:middle;" alt="PDF"> Reporte General
            </button>
            <button type="button" class="btn btn-outline-custom" data-bs-toggle="modal" data-bs-target="#modalNuevoDepartamento">
                + Añadir Centro
            </button>
            <button type="button" class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#modalNuevoLaboratorio">
                + Añadir Laboratorio
            </button>
            <button type="button" class="btn btn-outline-danger btn-pdf-trigger" data-type="especifico">
                <img src="<?= base_url('img/pdf.svg') ?>" style="width:18px; vertical-align:middle;" alt="PDF">
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Columna de Departamentos -->
        <div class="col-lg-5">
            <h3 class="sub-title mb-3">Centros Registrados</h3>
            <div class="table-responsive bg-white p-3 border rounded shadow-sm">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr><th width="15%">ID</th><th>Nombre del Centro</th><th width="20%">Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($departamentos)): ?>
                            <?php foreach($departamentos as $depto): ?>
                            <tr>
                                <td><strong>#<?= $depto['id'] ?></strong></td>
                                <td><?= esc($depto['nombre']) ?></td>
                                <td class="text-center">
                                    <button class="btn-icon text-gold btn-editar-depto" data-id="<?= $depto['id'] ?>" data-nombre="<?= esc($depto['nombre']) ?>" title="Editar">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button class="btn-icon text-danger btn-eliminar-trigger" data-tipo="departamento" data-id="<?= $depto['id'] ?>" title="Eliminar">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="empty-state">No hay Centros registrados aún.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <nav class="mt-3">
                <ul class="pagination pagination-sm">
                    <li class="page-item <?= ($pager_dept['actual'] <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page_dept=<?= $pager_dept['actual'] - 1 ?>&page_lab=<?= $pager_lab['actual'] ?>">Anterior</a>
                    </li>
                    <li class="page-item disabled"><span class="page-link">Pág <?= $pager_dept['actual'] ?> de <?= $pager_dept['total'] ?: 1 ?></span></li>
                    <li class="page-item <?= ($pager_dept['actual'] >= $pager_dept['total']) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page_dept=<?= $pager_dept['actual'] + 1 ?>&page_lab=<?= $pager_lab['actual'] ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Columna de Laboratorios -->
        <div class="col-lg-7">
            <h3 class="sub-title mb-3">Laboratorios <span id="textoFiltroLab" class="text-muted fs-6 fw-normal"></span></h3>
            <div class="table-responsive bg-white p-3 border rounded shadow-sm">
                <table class="table table-custom align-middle" id="tablaLaboratorios">
                    <thead>
                        <tr><th width="10%">ID</th><th>Nombre del Laboratorio</th><th>Pertenece al Centro.</th><th width="20%">Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($laboratorios)): ?>
                            <?php foreach($laboratorios as $lab): ?>
                            <tr class="lab-row" data-dept-id="<?= $lab['departamento_id'] ?>">
                                <td><strong>#<?= $lab['id'] ?></strong></td>
                                <td><?= esc($lab['nombre']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= esc($lab['nombre_departamento'] ?? 'Dpto. ID: '.$lab['departamento_id']) ?></span></td>
                                <td class="text-center">
                                    <button class="btn-icon text-gold btn-editar-lab" data-id="<?= $lab['id'] ?>" data-nombre="<?= esc($lab['nombre']) ?>" data-depto="<?= $lab['departamento_id'] ?>" title="Editar">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button class="btn-icon text-danger btn-eliminar-trigger" data-tipo="laboratorio" data-id="<?= $lab['id'] ?>" title="Eliminar">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="rowVaciaLabs"><td colspan="4" class="empty-state">No hay laboratorios registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <nav class="mt-3">
                <ul class="pagination pagination-sm">
                    <li class="page-item <?= ($pager_lab['actual'] <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page_dept=<?= $pager_dept['actual'] ?>&page_lab=<?= $pager_lab['actual'] - 1 ?>">Anterior</a>
                    </li>
                    <li class="page-item disabled"><span class="page-link">Pág <?= $pager_lab['actual'] ?> de <?= $pager_lab['total'] ?: 1 ?></span></li>
                    <li class="page-item <?= ($pager_lab['actual'] >= $pager_lab['total']) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page_dept=<?= $pager_dept['actual'] ?>&page_lab=<?= $pager_lab['actual'] + 1 ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Modales específicos de esta página (se mantienen igual, pero sin el modal de cambio de contraseña) -->

<!-- Modal para Nuevo Departamento -->
<div class="modal fade" id="modalNuevoDepartamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: var(--azul-oscuro);">
                <h5 class="modal-title">Añadir Nuevo Departamento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('gestion-departamento/guardar-departamento') ?>?page_dept=<?= $pager_dept['actual'] ?>&page_lab=<?= $pager_lab['actual'] ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-secondary small font-weight-bold text-uppercase">Nombre del Departamento</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej. Ingeniería de Sistemas" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-2">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-custom px-3">Guardar Departamento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Nuevo Laboratorio -->
<div class="modal fade" id="modalNuevoLaboratorio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: var(--azul-oscuro);">
                <h5 class="modal-title">Añadir Nuevo Laboratorio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('gestion-departamento/guardar-laboratorio') ?>?page_dept=<?= $pager_dept['actual'] ?>&page_lab=<?= $pager_lab['actual'] ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-secondary small font-weight-bold text-uppercase">Seleccionar Departamento</label>
                        <select name="departamento_id" class="form-select" required>
                            <option value="">-- Seleccione un Dpto --</option>
                            <?php if(!empty($todos_departamentos)): ?>
                                <?php foreach ($todos_departamentos as $depto): ?>
                                    <option value="<?= $depto['id'] ?>"><?= esc($depto['nombre']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-secondary small font-weight-bold text-uppercase">Nombre del Laboratorio</label>
                        <input type="text" name="nombre_laboratorio" class="form-control" placeholder="Ej. Laboratorio de Redes" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-2">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-custom px-3">Guardar Laboratorio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Departamento -->
<div class="modal fade" id="modalEditarDepto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <form action="<?= base_url('gestion-departamento/editar-departamento') ?>?page_dept=<?= $pager_dept['actual'] ?>&page_lab=<?= $pager_lab['actual'] ?>" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="id" id="editDeptoId">
            <div class="modal-header text-dark bg-warning">
                <h5 class="modal-title fw-bold">Editar Departamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <label class="form-label text-secondary small font-weight-bold text-uppercase">Nombre del Departamento</label>
                <input type="text" name="nombre" id="editDeptoNombre" class="form-control" required>
            </div>
            <div class="modal-footer bg-light p-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning fw-bold">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Laboratorio -->
<div class="modal fade" id="modalEditarLab" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <form action="<?= base_url('gestion-departamento/editar-laboratorio') ?>?page_dept=<?= $pager_dept['actual'] ?>&page_lab=<?= $pager_lab['actual'] ?>" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="id" id="editLabId">
            <div class="modal-header text-dark bg-warning">
                <h5 class="modal-title fw-bold">Editar Laboratorio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label text-secondary small font-weight-bold text-uppercase">Pertenece al Departamento</label>
                    <select name="departamento_id" id="editLabDeptoId" class="form-select" required></select>
                </div>
                <div>
                    <label class="form-label text-secondary small font-weight-bold text-uppercase">Nombre del Laboratorio</label>
                    <input type="text" name="nombre_laboratorio" id="editLabNombre" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer bg-light p-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning fw-bold">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <form id="formEliminar" action="" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="mb-0 fs-5 text-secondary">¿Está seguro que desea eliminar este elemento?</p>
                <small class="text-danger fw-bold">Esta acción no se puede deshacer.</small>
            </div>
            <div class="modal-footer bg-light p-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger px-4">Sí, Eliminar</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalDepto = new bootstrap.Modal(document.getElementById('modalEditarDepto'));
        const modalLab = new bootstrap.Modal(document.getElementById('modalEditarLab'));
        const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminar'));

        const filtroDepto = document.getElementById('filtroDepartamento');
        const filasLabs = document.querySelectorAll('.lab-row');
        const textoFiltro = document.getElementById('textoFiltroLab');

        if (filtroDepto) {
            const aplicarFiltrado = (deptoSeleccionado) => {
                let visibles = 0;
                textoFiltro.textContent = (deptoSeleccionado !== 'todos') 
                    ? `(Filtrados por: ${filtroDepto.options[filtroDepto.selectedIndex].text})` 
                    : '';

                filasLabs.forEach(fila => {
                    if (deptoSeleccionado === 'todos' || fila.getAttribute('data-dept-id') === deptoSeleccionado) {
                        fila.style.display = '';
                        visibles++;
                    } else {
                        fila.style.display = 'none';
                    }
                });

                let rowVacia = document.getElementById('rowVaciaLabs');
                if (visibles === 0 && !rowVacia) {
                    document.querySelector('#tablaLaboratorios tbody').insertAdjacentHTML('beforeend', 
                        '<tr id="rowVaciaLabs"><td colspan="4" class="empty-state">No hay laboratorios para el departamento seleccionado.</td></tr>');
                } else if (visibles > 0 && rowVacia) {
                    rowVacia.remove();
                }
            };

            const filtroGuardado = localStorage.getItem('filtroDepartamento');
            if (filtroGuardado) {
                filtroDepto.value = filtroGuardado;
                if (!filtroDepto.value) filtroDepto.value = 'todos';
            }
            aplicarFiltrado(filtroDepto.value);

            filtroDepto.addEventListener('change', function() {
                localStorage.setItem('filtroDepartamento', this.value);
                aplicarFiltrado(this.value);
            });
        }

        // Eventos de botones (editar, eliminar, pdf)
        document.body.addEventListener('click', (e) => {
            const btnEdDepto = e.target.closest('.btn-editar-depto');
            if (btnEdDepto) {
                document.getElementById('editDeptoId').value = btnEdDepto.dataset.id;
                document.getElementById('editDeptoNombre').value = btnEdDepto.dataset.nombre;
                modalDepto.show();
                return;
            }

            const btnEdLab = e.target.closest('.btn-editar-lab');
            if (btnEdLab) {
                document.getElementById('editLabId').value = btnEdLab.dataset.id;
                document.getElementById('editLabNombre').value = btnEdLab.dataset.nombre;
                const selectDestino = document.getElementById('editLabDeptoId');
                selectDestino.innerHTML = filtroDepto.innerHTML;
                selectDestino.querySelector('option[value="todos"]')?.remove();
                selectDestino.value = btnEdLab.dataset.depto;
                modalLab.show();
                return;
            }

            const btnDel = e.target.closest('.btn-eliminar-trigger');
            if (btnDel) {
                const form = document.getElementById('formEliminar');
                form.action = `<?= base_url('gestion-departamento/eliminar-') ?>${btnDel.dataset.tipo}/${btnDel.dataset.id}?page_dept=<?= $pager_dept['actual'] ?>&page_lab=<?= $pager_lab['actual'] ?>`;
                modalEliminar.show();
                return;
            }

            const btnPdf = e.target.closest('.btn-pdf-trigger');
            if (btnPdf) {
                const deptoId = filtroDepto.value;
                const esGeneral = btnPdf.dataset.type === 'general';
                const endpoint = esGeneral ? 'generar-pdf-general' : 'generar-pdf';
                const url = `<?= base_url('gestion-departamento/') ?>${endpoint}?depto_id=${deptoId}`;
                if (esGeneral) {
                    window.open(url, '_blank');
                } else {
                    window.location.href = url;
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>