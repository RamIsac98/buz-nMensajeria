<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Editar Solicitud de Bioseguridad<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Estilos específicos de esta página */
    :root {
        --azul-claro: #2073AF;
        --azul-oscuro: rgba(28, 70, 110, 0.9);
        --amarillo: #ffc107;
        --borde: #ced4da;
    }
    .container-form {
        max-width: 1200px;
        margin: 30px auto;
        background: white;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        padding: 30px 40px 40px;
    }
    .form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 15px;
    }
    .page-title {
        font-size: 1.6rem;
        font-weight: bold;
        color: var(--azul-oscuro);
        margin: 0;
    }
    .codigo-fecha {
        text-align: right;
    }
    .badge-fecha {
        background-color: #e9ecef;
        color: #495057;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
    }
    .codigo-agua {
        font-size: 1.2rem;
        font-weight: bold;
        color: #6c757d;
        margin-top: 5px;
    }
    .section-title {
        font-size: 1.2rem;
        font-weight: bold;
        color: var(--azul-oscuro);
        margin: 20px 0 15px 0;
        border-left: 5px solid var(--azul-claro);
        padding-left: 12px;
    }
    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
        font-size: 0.9rem;
    }
    .form-control, .form-select {
        border: 1px solid var(--borde);
        border-radius: 6px;
        padding: 8px 12px;
    }
    .readonly-field {
        background-color: #e9ecef;
    }
    .btn-submit {
        background-color: var(--azul-claro);
        color: white;
        font-weight: bold;
        padding: 12px 30px;
        border-radius: 40px;
        border: none;
        font-size: 1rem;
        transition: 0.3s;
        width: 100%;
    }
    .btn-submit:hover {
        background-color: var(--azul-oscuro);
        transform: translateY(-1px);
    }
    .badge-edicion {
        background-color: #ffc107;
        color: #212529;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: bold;
        margin-left: 15px;
    }
    .warning-material {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
        padding: 10px;
        border-radius: 6px;
        margin-top: 10px;
        font-size: 0.85rem;
        display: none;
    }
    @media (max-width: 768px) {
        .container-form { padding: 20px; }
        .form-header { flex-direction: column; align-items: start; gap: 10px; }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-form">
    <div class="form-header">
        <div class="page-title">
            Editar Solicitud de Bioseguridad
            <span class="badge-edicion">Modo Edición</span>
        </div>
        <div class="codigo-fecha">
            <div class="badge-fecha">Fecha: <?= $fecha_automatica ?></div>
            <div class="codigo-agua"><?= $codigo_automatico ?></div>
        </div>
    </div>

    <form id="formBioseguridad" action="<?= base_url('bioseguridad/actualizar/' . $id_solicitud) ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" value="PUT">
        <input type="hidden" name="id" value="<?= $id_solicitud ?>">

        <!-- Datos del Solicitante (sin rol) -->
        <div class="section-title">Datos del Solicitante</div>
        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <label class="form-label">Nombre Completo</label>
                <input type="text" class="form-control readonly-field" value="<?= esc($usuario_data['username']) ?>" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Extensión Tel.</label>
                <input type="text" name="ext_telefono" class="form-control" value="<?= esc($solicitud['ext_telefono']) ?>" required>
            </div>
        </div>

        <!-- Ubicación del Laboratorio -->
        <div class="section-title">Ubicación del Laboratorio</div>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">Centro / Departamento</label>
                <input type="text" class="form-control readonly-field" value="<?= esc($usuario_data['departamento']) ?>" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Laboratorio</label>
                <input type="text" class="form-control readonly-field" value="<?= esc($usuario_data['nombre_laboratorio']) ?>" readonly>
            </div>
        </div>

        <!-- Material Requerido -->
        <div class="section-title">Material Requerido (Seleccione una o ambas opciones)</div>

        <div class="row g-3 align-items-end mb-4">
            <div class="col-md-4">
                <label class="form-label">Contenedores de Pulso Cortante</label>
                <input type="number" name="contenedores_pulso_cantidad" id="pulsoCantidad" class="form-control" min="0" max="3" value="<?= esc($solicitud['contenedores_pulso_cantidad']) ?>">
                <small class="text-muted">Máximo 3 unidades</small>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Bolsas Rojas (máximo 10 unidades por cada tamaño)</label>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Pequeña</label>
                    <input type="number" name="bolsas_rojas_pequena" id="bolsaPeq" class="form-control" min="0" max="10" value="<?= esc($solicitud['bolsas_rojas_pequena']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Mediana</label>
                    <input type="number" name="bolsas_rojas_mediana" id="bolsaMed" class="form-control" min="0" max="10" value="<?= esc($solicitud['bolsas_rojas_mediana']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Grande</label>
                    <input type="number" name="bolsas_rojas_grande" id="bolsaGra" class="form-control" min="0" max="10" value="<?= esc($solicitud['bolsas_rojas_grande']) ?>">
                </div>
            </div>
            <div id="bolsasWarning" class="text-danger small mt-2" style="display:none;">⚠️ Cada tamaño de bolsa tiene un límite máximo de 10 unidades.</div>
            <div id="materialWarning" class="warning-material">⚠️ Debes seleccionar al menos un material (contenedor o bolsa roja).</div>
        </div>

        <div class="section-title">¿Quién retira el material?</div>
        <div class="mb-3">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="quien_retira" id="miPersona" value="mi_persona" <?= $solicitud['quien_retira'] == 'mi_persona' ? 'checked' : '' ?>>
                <label class="form-check-label" for="miPersona">Mi persona</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="quien_retira" id="otraPersona" value="otra_persona" <?= $solicitud['quien_retira'] == 'otra_persona' ? 'checked' : '' ?>>
                <label class="form-check-label" for="otraPersona">Otro</label>
            </div>
        </div>
        <div class="mb-4" id="nombreOtraDiv" style="<?= $solicitud['quien_retira'] == 'otra_persona' ? 'display:block;' : 'display:none;' ?>">
            <label class="form-label">Especifique el nombre de quien retira:</label>
            <input type="text" name="nombre_otra_persona" id="nombreOtraPersona" class="form-control" placeholder="Nombre completo" value="<?= esc($solicitud['nombre_otra_persona']) ?>" <?= $solicitud['quien_retira'] == 'otra_persona' ? 'required' : '' ?>>
        </div>

        <div class="mt-5 d-flex gap-3">
            <a href="<?= base_url('desechos/registroSolicitudes') ?>" class="btn btn-secondary" style="flex: 0 0 auto;">Cancelar</a>
            <button type="button" id="btnFakeSubmit" class="btn-submit" style="flex: 1;">ACTUALIZAR SOLICITUD</button>
        </div>
    </form>
</div>

<!-- Modal de confirmación con colores de la plantilla -->
<div class="modal fade" id="modalConfirm" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--azul-oscuro); color: white;">
                <h5 class="modal-title">Confirmar Actualización</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea actualizar esta solicitud?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnRealSubmit" class="btn" style="background-color: var(--azul-claro); color: white;">Confirmar</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Mostrar/ocultar campo "otra persona"
    const radioMi = document.getElementById('miPersona');
    const radioOtro = document.getElementById('otraPersona');
    const nombreDiv = document.getElementById('nombreOtraDiv');
    const nombreInput = document.getElementById('nombreOtraPersona');

    function toggleNombreOtra() {
        if (radioOtro.checked) {
            nombreDiv.style.display = 'block';
            nombreInput.setAttribute('required', 'required');
        } else {
            nombreDiv.style.display = 'none';
            nombreInput.removeAttribute('required');
        }
    }
    radioMi.addEventListener('change', toggleNombreOtra);
    radioOtro.addEventListener('change', toggleNombreOtra);
    toggleNombreOtra();

    // Validación de bolsas rojas (cada tamaño máximo 10)
    const bolsaPeq = document.getElementById('bolsaPeq');
    const bolsaMed = document.getElementById('bolsaMed');
    const bolsaGra = document.getElementById('bolsaGra');
    const warningBolsas = document.getElementById('bolsasWarning');
    const materialWarning = document.getElementById('materialWarning');

    function validarBolsasIndividuales() {
        let peq = parseInt(bolsaPeq.value) || 0;
        let med = parseInt(bolsaMed.value) || 0;
        let gra = parseInt(bolsaGra.value) || 0;
        let error = false;

        if (peq > 10) { bolsaPeq.value = 10; peq = 10; error = true; }
        if (med > 10) { bolsaMed.value = 10; med = 10; error = true; }
        if (gra > 10) { bolsaGra.value = 10; gra = 10; error = true; }

        if (error) {
            warningBolsas.style.display = 'block';
        } else {
            warningBolsas.style.display = 'none';
        }
        return !error;
    }

    // Validar que al menos un material esté solicitado (contenedor >0 o alguna bolsa >0)
    function validarMaterialRequerido() {
        const contenedores = parseInt(document.getElementById('pulsoCantidad').value) || 0;
        const bolsaP = parseInt(bolsaPeq.value) || 0;
        const bolsaM = parseInt(bolsaMed.value) || 0;
        const bolsaG = parseInt(bolsaGra.value) || 0;
        const totalBolsas = bolsaP + bolsaM + bolsaG;
        if (contenedores === 0 && totalBolsas === 0) {
            materialWarning.style.display = 'block';
            return false;
        } else {
            materialWarning.style.display = 'none';
            return true;
        }
    }

    // Asignar eventos a los inputs de bolsas
    bolsaPeq.addEventListener('input', validarBolsasIndividuales);
    bolsaMed.addEventListener('input', validarBolsasIndividuales);
    bolsaGra.addEventListener('input', validarBolsasIndividuales);

    // Validación contenedores (máximo 3)
    const pulsoInput = document.getElementById('pulsoCantidad');
    pulsoInput.addEventListener('change', function() {
        if (this.value > 3) this.value = 3;
        if (this.value < 0) this.value = 0;
        if (this.value > 0) materialWarning.style.display = 'none';
    });
    // Ocultar advertencia si se cambian bolsas
    [bolsaPeq, bolsaMed, bolsaGra].forEach(input => {
        input.addEventListener('input', function() {
            if (parseInt(this.value) > 0) materialWarning.style.display = 'none';
        });
    });

    // Modal de confirmación con validación adicional
    const modal = new bootstrap.Modal(document.getElementById('modalConfirm'));
    const form = document.getElementById('formBioseguridad');
    document.getElementById('btnFakeSubmit').addEventListener('click', function(e) {
        // Validar campos obligatorios del formulario
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        // Validar bolsas individuales
        if (!validarBolsasIndividuales()) {
            warningBolsas.scrollIntoView({ behavior: 'smooth' });
            return;
        }
        // Validar que haya al menos un material
        if (!validarMaterialRequerido()) {
            materialWarning.scrollIntoView({ behavior: 'smooth' });
            return;
        }
        modal.show();
    });
    document.getElementById('btnRealSubmit').addEventListener('click', function() {
        form.submit();
    });
</script>
<?= $this->endSection() ?>