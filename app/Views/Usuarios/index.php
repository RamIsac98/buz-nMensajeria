<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Administración de Usuarios<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Estilos específicos de esta página */
    .btn-crear-usuario {
        display: inline-flex;
        align-items: center;
        background-color: var(--azul-claro);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        text-decoration: none;
    }
    .btn-crear-usuario:hover {
        background-color: var(--azul-oscuro);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
        vertical-align: middle;
    }
    .badge-protected {
        background-color: var(--azul-oscuro);
        padding: 8px;
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

    /* Clases personalizadas para el modal de cambio de estado */
    .btn-custom-success {
        background-color: var(--azul-oscuro) !important;
        border-color: var(--azul-oscuro) !important;
        color: white !important;
    }
    .btn-custom-success:hover {
        background-color: var(--azul-claro) !important;
        color: var(--amarillo) !important;
    }
    .bg-custom-success {
        background-color: var(--azul-oscuro) !important;
    }
    .alert-custom-success {
        background-color: var(--azul-oscuro) !important;
        color: white !important;
        border: 1px solid var(--azul-claro) !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-end mb-3">
        <a href="<?= base_url('usuarios/crear') ?>" class="btn btn-crear-usuario">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-plus-circle me-2" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
            </svg>
            Crear Nuevo Usuario
        </a>
    </div>

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
                        <th>Nombre Completo</th>
                        <th>Usuario (Username)</th>
                        <th>Cédula</th>
                        <th>Rol / Permiso</th>
                        <th>Pregunta de Seguridad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($usuarios as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= esc($user['nombre']) ?> <?= esc($user['apellido']) ?></td>
                        <td><strong><?= esc($user['display_username']) ?></strong></td>
                        <td><?= esc($user['cedula']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= esc($user['rol']) ?></span></td>
                        <td><?= !empty($user['pregunta_seguridad']) ? esc($user['pregunta_seguridad']) : '<em class="text-muted">No configurada</em>' ?></td>
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
                $total       = $pager->getTotal();
                $perPage     = $pager->getPerPage();
                $currentPage = $pager->getCurrentPage();
                $totalPages  = ceil($total / $perPage);
                
                $from = $total > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
                $to   = min($currentPage * $perPage, $total);

                $startPage = max(1, $currentPage - 1);
                $endPage   = min($totalPages, $currentPage + 1);

                if ($currentPage == 1) $endPage = min($totalPages, 3);
                if ($currentPage == $totalPages) $startPage = max(1, $totalPages - 2);

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


<div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-custom-width">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" id="modalEstadoHeader">
                <h5 class="modal-title">Confirmar Acción</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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

<div class="modal fade" id="modalPdf" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="GET" action="<?= base_url('usuarios/generarPdfUsuarios') ?>" class="modal-content border-0 shadow">
            <input type="hidden" name="buscar" value="<?= esc($_GET['buscar'] ?? '') ?>">
            <input type="hidden" name="rol" value="<?= esc($_GET['rol'] ?? '') ?>">
            <input type="hidden" name="estado" value="<?= esc($_GET['estado'] ?? '') ?>">

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
        </form>
    </div>
</div>

<script>
// Lógica de memoria de página y filtros (igual que antes)
document.addEventListener('DOMContentLoaded', () => {
    const currentQuery = window.location.search;
    const pathName = window.location.pathname;
    const memoryKey = 'usuarios_state_memory';
    const flashKey = 'usuarios_flash_memory';

    const systemMessages = document.querySelector('.system-messages');

    const savedFlash = sessionStorage.getItem(flashKey);
    if (savedFlash && systemMessages) {
        systemMessages.innerHTML = savedFlash + systemMessages.innerHTML;
        sessionStorage.removeItem(flashKey);
    }

    if (currentQuery) {
        sessionStorage.setItem(memoryKey, currentQuery);
    } else {
        const savedQuery = sessionStorage.getItem(memoryKey);
        if (savedQuery) {
            const hasNativeFlash = systemMessages && systemMessages.querySelector('.alert') !== null;
            if (hasNativeFlash) {
                sessionStorage.setItem(flashKey, systemMessages.innerHTML);
            }
            window.location.replace(pathName + savedQuery);
        }
    }

    const btnLimpiar = document.querySelector('.btn-limpiar-filtros');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', () => {
            sessionStorage.removeItem(memoryKey);
        });
    }
});

// Lógica del modal de cambio de estado
const modalCambiarEstado = document.getElementById('modalCambiarEstado');
if (modalCambiarEstado) {
    modalCambiarEstado.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const url = button.getAttribute('data-url');
        const username = button.getAttribute('data-username');
        const status = button.getAttribute('data-status');

        const modalHeader = document.getElementById('modalEstadoHeader');
        const textoAccion = document.getElementById('modalTextoAccion');
        const textoUsuario = document.getElementById('modalTextoUsuario');
        const textoAdvertencia = document.getElementById('modalTextoAdvertencia');
        const btnConfirmar = document.getElementById('btnConfirmarCambioEstado');

        textoUsuario.textContent = username;
        btnConfirmar.setAttribute('href', url);
        textoAdvertencia.classList.add('d-none');
        modalHeader.className = 'modal-header';
        btnConfirmar.className = 'btn btn-sm px-4 font-weight-bold';

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
            textoAdvertencia.classList.remove('d-none');
        }
    });
}
</script>
<?= $this->endSection() ?>