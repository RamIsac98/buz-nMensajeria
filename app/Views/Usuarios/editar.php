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
    .cedula-group {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .cedula-group .form-select {
        width: auto;
        flex: 0 0 120px;
    }
    .cedula-group .form-control {
        flex: 1;
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
                    <input type="text" name="nombre" id="nombre" class="form-control" value="<?= esc($usuario['nombre']) ?>" required maxlength="25">
                </div>
                <div class="mb-3">
                    <label class="form-label">Apellido</label>
                    <input type="text" name="apellido" id="apellido" class="form-control" value="<?= esc($usuario['apellido']) ?>" required maxlength="25">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nombre de Usuario (Username)</label>
                    <input type="text" name="username" id="username" class="form-control" value="<?= esc($usuario['username']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipo de Cédula</label>
                    <div class="cedula-group">
                        <select name="tipo_cedula" id="tipo_cedula" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="V" <?= ($usuario['tipo_cedula'] ?? '') == 'V' ? 'selected' : '' ?>>Venezolano (V)</option>
                            <option value="E" <?= ($usuario['tipo_cedula'] ?? '') == 'E' ? 'selected' : '' ?>>Extranjero (E)</option>
                        </select>
                        <input type="number" name="cedula" id="cedula" class="form-control" placeholder="Número de cédula" value="<?= esc($usuario['cedula']) ?>" required maxlength="8" <?= isset($usuario['tipo_cedula']) && $usuario['tipo_cedula'] !== '' ? '' : 'disabled' ?>>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rol / Permiso</label>
                    <select name="rol" id="rol" class="form-select" required>
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
                    <input type="password" name="password" id="password" class="form-control" placeholder="Mínimo 6 caracteres (opcional)">
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

<!-- Modal de confirmación -->
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

<!-- Modal de error -->
<div class="modal fade" id="modalError" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-custom-width">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background-color: #f8d7da; border-bottom: 2px solid #f5c6cb;">
                <h5 class="modal-title text-danger">Error de Validación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="fs-6 mb-1" id="errorMsg">Error de validación.</p>
            </div>
            <div class="modal-footer bg-light border-top p-2 justify-content-center">
                <button type="button" class="btn btn-danger btn-sm px-4" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const formEditar = document.getElementById('formEditarUsuario');
    const modalConfirmar = new bootstrap.Modal(document.getElementById('modalConfirmarEdicion'));
    const btnAbrirModal = document.getElementById('btnAbrirModal');
    const btnConfirmarActualizar = document.getElementById('btnConfirmarActualizar');

    // ---- Referencias a campos ----
    const nombre = document.getElementById('nombre');
    const apellido = document.getElementById('apellido');
    const username = document.getElementById('username');
    const tipoCedula = document.getElementById('tipo_cedula');
    const inputCedula = document.getElementById('cedula');
    const rol = document.getElementById('rol');
    const deptoSelect = document.getElementById('id_departamento');
    const labSelect = document.getElementById('id_laboratorio');
    const password = document.getElementById('password');

    // ---- Habilitar cédula si ya hay tipo seleccionado ----
    if (tipoCedula.value !== '') {
        inputCedula.disabled = false;
    }

    // ---- Lógica de habilitación de cédula ----
    tipoCedula.addEventListener('change', function() {
        if (this.value !== '') {
            inputCedula.disabled = false;
            inputCedula.focus();
        } else {
            inputCedula.disabled = true;
            inputCedula.value = '';
        }
    });

    // ---- Validación antes de abrir modal de confirmación ----
    btnAbrirModal.addEventListener('click', function () {
        const errorModal = new bootstrap.Modal(document.getElementById('modalError'));
        const errorMsg = document.getElementById('errorMsg');

        // 1. Validar nombre
        if (nombre.value.trim() === '') {
            errorMsg.textContent = 'El campo "Nombre" es obligatorio.';
            errorModal.show();
            return;
        }
        if (nombre.value.length > 25) {
            errorMsg.textContent = 'El campo "Nombre" no puede exceder los 25 caracteres.';
            errorModal.show();
            return;
        }
        if (nombre.value.length < 6) {
            errorMsg.textContent = 'El campo "Nombre" debe tener al menos 6 caracteres.';
            errorModal.show();
            return;
        }
        if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre.value)) {
            errorMsg.textContent = 'El campo "Nombre" solo debe contener letras y espacios.';
            errorModal.show();
            return;
        }

        // 2. Validar apellido
        if (apellido.value.trim() === '') {
            errorMsg.textContent = 'El campo "Apellido" es obligatorio.';
            errorModal.show();
            return;
        }
        if (apellido.value.length > 25) {
            errorMsg.textContent = 'El campo "Apellido" no puede exceder los 25 caracteres.';
            errorModal.show();
            return;
        }
        if (apellido.value.length < 6) {
            errorMsg.textContent = 'El campo "Apellido" debe tener al menos 6 caracteres.';
            errorModal.show();
            return;
        }
        if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(apellido.value)) {
            errorMsg.textContent = 'El campo "Apellido" solo debe contener letras y espacios.';
            errorModal.show();
            return;
        }

        // 3. Validar username
        const usernameVal = username.value.trim();
        if (usernameVal === '') {
            errorMsg.textContent = 'El campo "Username" es obligatorio.';
            errorModal.show();
            return;
        }
        if (usernameVal.length < 3) {
            errorMsg.textContent = 'El campo "Username" debe tener al menos 3 caracteres.';
            errorModal.show();
            return;
        }
        if (/\s/.test(usernameVal)) {
            errorMsg.textContent = 'El campo "Username" no puede contener espacios.';
            errorModal.show();
            return;
        }
        if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(usernameVal)) {
            errorMsg.textContent = 'El campo "Username" solo debe contener letras.';
            errorModal.show();
            return;
        }

        // 4. Validar tipo de cédula
        if (tipoCedula.value === '') {
            errorMsg.textContent = 'Debes seleccionar el tipo de cédula (Venezolano o Extranjero).';
            errorModal.show();
            return;
        }

        // 5. Validar cédula: obligatoria, 8 dígitos numéricos
        const cedulaVal = inputCedula.value.trim();
        if (cedulaVal === '') {
            errorMsg.textContent = 'El campo "Cédula" es obligatorio.';
            errorModal.show();
            return;
        }
        if (!/^\d{8}$/.test(cedulaVal)) {
            errorMsg.textContent = 'La cédula debe ser un número de 8 dígitos (ej. 12345678).';
            errorModal.show();
            return;
        }
        if (!/^\d{8}$/.test(cedulaVal)) {
        errorMsg.textContent = 'La cédula debe ser un número de 8 dígitos (ej. 12345678).';
        errorModal.show();
            return;
        }

        // 6. Validar rol
        if (rol.value === '') {
            errorMsg.textContent = 'Debes seleccionar un rol válido.';
            errorModal.show();
            return;
        }

        // 7. Validar centro
        if (deptoSelect.value === '') {
            errorMsg.textContent = 'Debes seleccionar un Centro.';
            errorModal.show();
            return;
        }

        // 8. Validar laboratorio
        if (labSelect.disabled === true || labSelect.value === '') {
            errorMsg.textContent = 'Debes seleccionar un Laboratorio válido.';
            errorModal.show();
            return;
        }

        // 9. Validar contraseña (opcional, pero si se ingresa, mínimo 6 caracteres)
        const passVal = password.value.trim();
        if (passVal !== '' && passVal.length < 6) {
            errorMsg.textContent = 'La nueva contraseña, si se proporciona, debe tener al menos 6 caracteres.';
            errorModal.show();
            return;
        }

        if (password.value.includes(' ')) {
        errorMsg.textContent = 'La contraseña no puede contener espacios en blanco.';
        errorModal.show();
        return;
        }
        

        // ---- Si todo es válido, mostrar confirmación ----
        if (formEditar.checkValidity()) {
            modalConfirmar.show();
        } else {
            formEditar.reportValidity();
        }
    });

    // ---- Confirmar envío ----
    btnConfirmarActualizar.addEventListener('click', function () {
        formEditar.submit();
    });

    // ---- Filtro dinámico de laboratorios ----
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
});
</script>
<?= $this->endSection() ?>