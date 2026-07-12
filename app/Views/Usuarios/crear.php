<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Crear Usuario<?= $this->endSection() ?>

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
    <h2 class="main-title">Registro de Usuarios</h2>

    <!-- ===== MENSAJES FLASH CON SWEETALERT2 (eliminadas alertas Bootstrap) ===== -->

    <div class="card custom-card">
        <div class="card-header custom-card-header">
            <h5 class="mb-0 fw-bold">Formulario de Registro</h5>
        </div>
        <div class="card-body p-4">
            <form id="formCrearUsuario" action="<?= base_url('usuarios/guardar') ?>" method="POST">
                <?= csrf_field() ?> 
                
                <div class="mb-3">
                    <label class="form-label">Nombres</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ej. Juan" value="<?= old('nombre') ?>" required maxlength="25">
                </div>
                <div class="mb-3">
                    <label class="form-label">Apellidos</label>
                    <input type="text" name="apellido" id="apellido" class="form-control" placeholder="Ej. Pérez" value="<?= old('apellido') ?>" required maxlength="25">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nombre de Usuario (Username)</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Ej. juan.perez" value="<?= old('username') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipo de Cédula</label>
                    <div class="cedula-group">
                        <select name="tipo_cedula" id="tipo_cedula" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="V" <?= old('tipo_cedula') == 'V' ? 'selected' : '' ?>>Venezolano (V)</option>
                            <option value="E" <?= old('tipo_cedula') == 'E' ? 'selected' : '' ?>>Extranjero (E)</option>
                        </select>
                        <input type="number" name="cedula" id="cedula" class="form-control" placeholder="Número de cédula" value="<?= old('cedula') ?>" required maxlength="10" disabled>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rol / Permiso</label>
                    <select name="rol" id="rol" class="form-select" required>
                        <option value="" disabled selected>Selecciona un rol...</option>
                        <option value="Administrador" <?= old('rol') == 'Administrador' ? 'selected' : '' ?>>Administrador</option>
                        <option value="Jefe_Laboratorio" <?= old('rol') == 'Jefe_Laboratorio' ? 'selected' : '' ?>>Jefe Laboratorio</option>
                        <option value="TAI" <?= old('rol') == 'TAI' ? 'selected' : '' ?>>TAI</option>
                        <option value="PAI" <?= old('rol') == 'PAI' ? 'selected' : '' ?>>PAI</option>
                        <option value="Auxiliar" <?= old('rol') == 'Auxiliar' ? 'selected' : '' ?>>Auxiliar</option>
                        <option value="proteccion_integral" <?= old('rol') == 'proteccion_integral' ? 'selected' : '' ?>>Protección Integral</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Centro</label>
                    <select name="id_departamento" id="id_departamento" class="form-select" required>
                        <option value="" disabled selected>Selecciona un Centro...</option>
                        <?php if (!empty($departamentos)): ?>
                            <?php foreach ($departamentos as $depto): ?>
                                <option value="<?= $depto['id'] ?>" <?= old('id_departamento') == $depto['id'] ? 'selected' : '' ?>><?= esc($depto['nombre']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Laboratorio</label>
                    <select name="id_laboratorio" id="id_laboratorio" class="form-select" required>
                        <option value="" disabled selected>Selecciona primero un Centro...</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label">Contraseña de Acceso</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
                </div>

                <div class="d-flex justify-content-between pt-2 border-top mt-3">
                    <a href="<?= base_url('usuarios') ?>" class="btn btn-outline-secondary px-4">Cancelar</a>
                    <button type="button" id="btnAbrirModal" class="btn btn-success px-4" style="background-color: var(--azul-claro); border: none;">Registrar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="modalConfirmarRegistro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-custom-width">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: var(--azul-oscuro);">
                <h5 class="modal-title">Confirmar Registro</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="fs-5 mb-1">¿Estás seguro de que deseas registrar este usuario?</p>
                <p class="text-muted small mb-0">Asegúrate de que los datos ingresados sean correctos.</p>
            </div>
            <div class="modal-footer bg-light border-top p-2 justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarGuardar" class="btn btn-sm px-4 text-white font-weight-bold" style="background-color: var(--azul-claro);">Confirmar</button>
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
<!-- SweetAlert2 (por seguridad, aunque el layout ya lo incluya) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // =============================================
    // MENSAJES FLASH CON SWEETALERT2 (success/error)
    // =============================================
    document.addEventListener('DOMContentLoaded', function() {
        <?php if(session()->getFlashdata('success')): ?>
            console.log('Mensaje success recibido: <?= esc(session()->getFlashdata('success')) ?>');
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
            console.log('Mensaje error recibido: <?= esc(session()->getFlashdata('error')) ?>');
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
    // LÓGICA DEL FORMULARIO
    // =============================================
    document.addEventListener('DOMContentLoaded', function () {
        const formCrear = document.getElementById('formCrearUsuario');
        const modalConfirmar = new bootstrap.Modal(document.getElementById('modalConfirmarRegistro'));
        const btnAbrirModal = document.getElementById('btnAbrirModal');
        const btnConfirmarGuardar = document.getElementById('btnConfirmarGuardar');

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
        btnAbrirModal.addEventListener('click', function (event) {
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
                errorMsg.textContent = 'El campo "Cédula" es obligatorio. (solo debe contener números sin espacios ni puntos).';
                errorModal.show();
                return;
            }
            else if (!/^\d+$/.test(cedulaVal)) {
                errorMsg.textContent = 'La cédula solo debe contener números.';
                errorModal.show();
                return;
            }
            if (!/^\d{6,10}$/.test(cedulaVal)) {
            errorMsg.textContent = 'La cédula debe tener entre 6 y 10 dígitos numéricos.';
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

            // 8. Validar laboratorio (debe estar habilitado y tener valor)
            if (labSelect.disabled === true || labSelect.value === '') {
                errorMsg.textContent = 'Debes seleccionar un Laboratorio válido.';
                errorModal.show();
                return;
            }

            // 9. Validar contraseña (mínimo 6 caracteres)
            if (password.value.trim() === '') {
                errorMsg.textContent = 'El campo "Contraseña" es obligatorio.';
                errorModal.show();
                return;
            }

            if (password.value.includes(' ')) {
                errorMsg.textContent = 'La contraseña no puede contener espacios en blanco.';
                errorModal.show();
                return;
            }
            if (password.value.length < 6) {
                errorMsg.textContent = 'La contraseña debe tener al menos 6 caracteres.';
                errorModal.show();
                return;
            }

            // ---- Si todo es válido, mostrar confirmación ----
            if (formCrear.checkValidity()) {
                modalConfirmar.show();
            } else {
                formCrear.reportValidity();
            }
        });

        // ---- Confirmar envío ----
        btnConfirmarGuardar.addEventListener('click', function () {
            formCrear.submit();
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
                labSelect.innerHTML = '<option value="" disabled selected>Selecciona primero un centro...</option>';
                labSelect.disabled = true;
            }
        });
    });
</script>
<?= $this->endSection() ?>