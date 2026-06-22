<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Editar Usuario<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .main-title {
        color: var(--azul-oscuro);
        font-weight: bold;
        margin-top: 20px;
        margin-bottom: 20px;
        font-size: 1.75rem;
        text-align: center;
    }
    .custom-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        background-color: #fff;
    }
    .custom-card-header {
        background-color: var(--azul-oscuro);
        color: #ffff;
        border-top-left-radius: 8px !important;
        border-top-right-radius: 8px !important;
        padding: 15px 20px;
    }
    .btn-custom {
        background-color: var(--azul-claro);
        color: white;
        border: none;
        transition: background 0.2s;
    }
    .btn-custom:hover {
        background-color: var(--azul-oscuro);
        color: white;
    }
    .form-label {
        color: var(--azul-oscuro);
        font-weight: bold;
        font-size: 0.9rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--azul-claro);
        box-shadow: 0 0 0 0.25rem rgba(32, 115, 175, 0.25);
    }
    .security-box {
        background-color: #f8f9fa;
        border: 1px dashed #ced4da;
        border-radius: 6px;
    }
    .modal-custom-width {
        max-width: 450px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container" style="max-width: 600px;">
    <h2 class="main-title">Edición de Usuario</h2>

    <div class="card custom-card">
        <div class="card-header custom-card-header">
            <h5 class="mb-0 fw-bold">Modificar Datos de Usuario</h5>
        </div>
        <div class="card-body p-4">
            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <form id="formEditarUsuario" action="<?= base_url('usuarios/actualizar/'.$usuario['id']) ?>" method="POST">
                <?= csrf_field() ?> 
                
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="<?= esc($usuario['nombre']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Apellido</label>
                    <input type="text" name="apellido" class="form-control" value="<?= esc($usuario['apellido']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nombre de Usuario (Username)</label>
                    <input type="text" name="username" class="form-control" value="<?= esc($usuario['username']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Cédula de Identidad</label>
                    <input type="text" name="cedula" class="form-control" value="<?= esc($usuario['cedula']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rol / Permiso</label>
                    <select name="rol" class="form-select" required>
                        <option value="Administrador" <?= $usuario['rol'] == 'Administrador' ? 'selected' : '' ?>>Administrador</option>
                        <option value="Jefe_Laboratorio" <?= $usuario['rol'] == 'Jefe_Laboratorio' ? 'selected' : '' ?>>Jefe Laboratorio</option>
                        <option value="TAI" <?= $usuario['rol'] == 'TAI' ? 'selected' : '' ?>>TAI</option>
                        <option value="PAI" <?= $usuario['rol'] == 'PAI' ? 'selected' : '' ?>>PAI</option>
                        <option value="Auxiliar" <?= $usuario['rol'] == 'Auxiliar' ? 'selected' : '' ?>>Auxiliar</option>
                        <option value="proteccion_integral" <?= $usuario['rol'] == 'proteccion_integral' ? 'selected' : '' ?>>Protección Integral</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Centro</label>
                    <select name="id_departamento" id="id_departamento" class="form-select" required>
                        <option value="" disabled>Selecciona un Centro...</option>
                        <?php if (!empty($departamentos)): ?>
                            <?php foreach ($departamentos as $depto): ?>
                                <option value="<?= $depto['id'] ?>" <?= $id_departamento_actual == $depto['id'] ? 'selected' : '' ?>>
                                    <?= esc($depto['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Laboratorio Asignado</label>
                    <select name="id_laboratorio" id="id_laboratorio" class="form-select" required>
                        <option value="" disabled>Selecciona un laboratorio...</option>
                        <?php if (!empty($laboratorios)): ?>
                            <?php foreach ($laboratorios as $lab): ?>
                                <option value="<?= $lab['id'] ?>" <?= $usuario['laboratorio_id'] == $lab['id'] ? 'selected' : '' ?>>
                                    <?= esc($lab['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>No hay laboratorios disponibles en este Centro</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label">Nueva Contraseña <span class="text-muted fw-normal" style="font-size: 0.8rem;">(Dejar en blanco si no deseas cambiarla)</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Escribe la nueva contraseña si aplica">
                </div>

                <div class="security-box p-3 mb-4 text-start">
                    <p class="mb-1 text-secondary font-weight-bold" style="font-size: 0.85rem; text-transform: uppercase;">Pregunta de Seguridad actual</p>
                    <p class="small bg-white p-2 border rounded text-dark">
                        <?= !empty($usuario['pregunta_seguridad']) ? esc($usuario['pregunta_seguridad']) : '<em>No tiene pregunta configurada.</em>' ?>
                    </p>
                    
                    <?php if(!empty($usuario['pregunta_seguridad'])): ?>
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="eliminar_pregunta" value="1" id="delQuestion">
                            <label class="form-check-label text-danger fw-bold" for="delQuestion" style="font-size: 0.9rem;">
                                Eliminar/Restablecer Pregunta de Seguridad
                            </label>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-between pt-2 border-top mt-3">
                    <a href="<?= base_url('usuarios') ?>" class="btn btn-outline-secondary px-4">Cancelar</a>
                    <button type="button" id="btnAbrirModal" class="btn btn-success px-4" style="background-color: var(--azul-claro); border: none;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmación específico de esta página -->
<div class="modal fade" id="modalConfirmarEdicion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-custom-width">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: var(--azul-oscuro);">
                <h5 class="modal-title">Confirmar Modificación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="fs-5 mb-1">¿Estás seguro de que deseas guardar los cambios?</p>
                <p class="text-muted small mb-0">Los datos del usuario se actualizarán en el sistema.</p>
            </div>
            <div class="modal-footer bg-light border-top p-2 justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarActualizar" class="btn btn-sm px-4 text-white font-weight-bold" style="background-color: var(--azul-claro);">Confirmar</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Modal de confirmación
    const formEditar = document.getElementById('formEditarUsuario');
    const modalElement = document.getElementById('modalConfirmarEdicion');
    const modalConfirmar = new bootstrap.Modal(modalElement);
    const btnAbrirModal = document.getElementById('btnAbrirModal');
    const btnConfirmarActualizar = document.getElementById('btnConfirmarActualizar');

    if (btnAbrirModal) {
        btnAbrirModal.addEventListener('click', function () {
            if (formEditar.checkValidity()) {
                modalConfirmar.show();
            } else {
                formEditar.reportValidity();
            }
        });
    }

    if (btnConfirmarActualizar) {
        btnConfirmarActualizar.addEventListener('click', function () {
            formEditar.submit();
        });
    }

    // Filtro dinámico de laboratorios según departamento
    const deptoSelect = document.getElementById('id_departamento');
    const labSelect = document.getElementById('id_laboratorio');

    if (deptoSelect && labSelect) {
        deptoSelect.addEventListener('change', function() {
            const deptoId = this.value;
            labSelect.innerHTML = '<option value="" disabled selected>Cargando laboratorios...</option>';
            labSelect.disabled = true;

            if (deptoId && deptoId !== "") {
                const url = `<?= site_url('usuarios/obtener_laboratorios_por_depto') ?>/${deptoId}`;
                fetch(url)
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        labSelect.innerHTML = '<option value="" disabled selected>Selecciona un laboratorio...</option>';
                        if (data && data.length > 0) {
                            data.forEach(lab => {
                                labSelect.innerHTML += `<option value="${lab.id}">${lab.nombre}</option>`;
                            });
                            labSelect.disabled = false;
                        } else {
                            labSelect.innerHTML = '<option value="" disabled>No hay laboratorios en este Centro</option>';
                            labSelect.disabled = true;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al cargar laboratorios.');
                        labSelect.innerHTML = '<option value="" disabled>Error al cargar laboratorios</option>';
                        labSelect.disabled = true;
                    });
            } else {
                labSelect.innerHTML = '<option value="" disabled selected>Selecciona primero un Centro...</option>';
                labSelect.disabled = true;
            }
        });
    }
});
</script>
<?= $this->endSection() ?>