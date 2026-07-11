<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Editar Solicitud de Desechos<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Estilos específicos del formulario de edición */
    .page-title {
        color: var(--text-blue, #173f5f);
        font-weight: bold;
        margin: 25px 0 15px 40px;
        font-size: 1.4rem;
    }
    .form-container {
        background-color: var(--bg-form, #f7f7f7);
        border-radius: 15px;
        padding: 40px;
        margin: 0 40px 40px 40px;
        position: relative;
    }
    .watermark-container {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 15px;
        margin-bottom: 30px;
    }
    .badge-fecha {
        background-color: #2b78a9;
        color: white;
        padding: 6px 15px;
        border-radius: 4px;
        font-weight: bold;
        font-size: 0.9rem;
    }
    .codigo-agua {
        font-size: 1.5rem;
        color: #d0d0d0;
        font-weight: bold;
        letter-spacing: 1px;
    }
    .section-title {
        color: var(--text-blue, #173f5f);
        font-weight: bold;
        font-size: 1rem;
        margin-bottom: 15px;
    }
    .form-label-custom {
        font-size: 0.85rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 4px;
        display: block;
    }
    .input-readonly {
        background-color: #e9ecef;
        border: 1px solid var(--border-color, #ced4da);
        color: #555;
    }
    .form-control, .form-select {
        border: 1px solid var(--border-color, #ced4da);
        border-radius: 4px;
        font-size: 0.9rem;
        padding: 6px 10px;
    }
    .variants-box {
        border: 1px dashed var(--text-blue, #173f5f);
        padding: 15px;
        border-radius: 4px;
        background: transparent;
        min-height: 100px;
        max-height: 300px;
        overflow-y: auto;
        display: none;
    }
    .variants-box .form-check-label {
        font-size: 0.85rem;
        color: #666;
    }
    .btn-submit {
        background-color: #2b78a9;
        color: var(--amarillo, #ffc107);
        font-weight: bold;
        border: none;
        padding: 12px 0;
        width: 100%;
        border-radius: 4px;
        font-size: 1rem;
        transition: 0.3s;
    }
    .btn-submit:hover {
        background-color: var(--azul-oscuro, rgba(28,70,110,0.9));
    }
    .inline-inputs {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .inline-inputs input {
        width: 70px;
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

    .modal-error-icon { font-size: 3rem; color: #dc3545; }
    .error-list { text-align: left; max-height: 200px; overflow-y: auto; padding-left: 20px; color: #721c24; }
    .error-list li { margin-bottom: 6px; }
    .badge-edicion { background-color: #ffc107; color: #212529; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; margin-left: 15px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title">
    Editar Solicitud de Desechos
    <span class="badge-edicion">Modo Edición</span>
</div>

<!-- ===== MENSAJES FLASH AHORA CON SWEETALERT (no hay alertas Bootstrap) ===== -->

<div class="form-container">
    <form id="formSolicitud" action="<?= base_url('desechos/actualizar/' . $id_solicitud) ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="id" value="<?= $id_solicitud ?>">
        
        <div class="watermark-container">
            <div class="badge-fecha">Fecha: <?= $fecha_automatica ?></div>
            <div class="codigo-agua"><?= $codigo_automatico ?></div>
        </div>

        <div class="row g-5">
            <div class="col-md-5">
                <div class="section-title">Datos del Solicitante</div>
                <div class="row g-2 mb-4">
                    <div class="col-md-12">
                        <label class="form-label-custom">Nombre Completo (Usuario)</label>
                        <input type="text" class="form-control input-readonly" value="<?= esc($usuario_data['username']) ?>" readonly>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-7">
                        <div class="section-title mb-2">Tipo de Desecho (Uno o más)</div>
                        <div class="d-flex gap-3">
                            <?php 
                            $tipos_seleccionados = explode(', ', $solicitud['tipos_desecho'] ?? '');
                            ?>
                            <div class="form-check"><input class="form-check-input chk-tipo" type="checkbox" name="tipo_desecho[]" value="B" id="tB" <?= in_array('B', $tipos_seleccionados) ? 'checked' : '' ?>><label class="form-check-label fw-bold text-primary" for="tB">Tipo B</label></div>
                            <div class="form-check"><input class="form-check-input chk-tipo" type="checkbox" name="tipo_desecho[]" value="C" id="tC" <?= in_array('C', $tipos_seleccionados) ? 'checked' : '' ?>><label class="form-check-label fw-bold text-primary" for="tC">Tipo C</label></div>
                            <div class="form-check"><input class="form-check-input chk-tipo" type="checkbox" name="tipo_desecho[]" value="D" id="tD" <?= in_array('D', $tipos_seleccionados) ? 'checked' : '' ?>><label class="form-check-label fw-bold text-primary" for="tD">Tipo D</label></div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="section-title mb-2">¿Esterilizado?</div>
                        <div class="d-flex gap-3">
                            <div class="form-check"><input class="form-check-input" type="radio" name="esterilizado" value="Sí" id="estSi" <?= $solicitud['esterilizado'] ? 'checked' : '' ?>><label class="form-check-label" for="estSi">Sí</label></div>
                            <div class="form-check"><input class="form-check-input" type="radio" name="esterilizado" value="No" id="estNo" <?= !$solicitud['esterilizado'] ? 'checked' : '' ?>><label class="form-check-label" for="estNo">No</label></div>
                        </div>
                    </div>
                </div>

                <label class="form-label-custom mt-3">Lista General de Componentes Disponibles (Según Tipo Seleccionado)</label>
                <div class="variants-box" id="cajaVariantes"></div>
            </div>

            <div class="col-md-7">
                <div class="section-title">Ubicación del Laboratorio</div>
                <div class="row g-2 mb-4">
                    <div class="col-md-5">
                        <label class="form-label-custom">Centro</label>
                        <input type="text" class="form-control input-readonly" value="<?= esc($usuario_data['departamento']) ?>" readonly>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label-custom">Laboratorio</label>
                        <input type="text" class="form-control input-readonly" value="<?= esc($usuario_data['nombre_laboratorio']) ?>" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label-custom">Resumen (Ext.)</label>
                        <input type="number" name="ext_telefono" class="form-control" placeholder="Ext." value="<?= esc($solicitud['ext_telefono']) ?>" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="section-title mb-2">Estado del Desecho (Uno o más)</div>
                        <div class="d-flex gap-3">
                            <?php 
                            $estados_seleccionados = explode(', ', $solicitud['estado'] ?? '');
                            ?>
                            <div class="form-check"><input class="form-check-input chk-estado" type="checkbox" name="estado_fisico[]" value="Líquido" id="estLiq" <?= in_array('Líquido', $estados_seleccionados) ? 'checked' : '' ?>><label class="form-check-label" for="estLiq">Líquido</label></div>
                            <div class="form-check"><input class="form-check-input chk-estado" type="checkbox" name="estado_fisico[]" value="Sólido" id="estSol" <?= in_array('Sólido', $estados_seleccionados) ? 'checked' : '' ?>><label class="form-check-label" for="estSol">Sólido</label></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="section-title mb-2">Peso "Aproximado"</div>
                        <div class="inline-inputs">
                            <label class="form-label-custom m-0">kg</label>
                            <input type="number" step="0.01" name="peso_kg" id="inputKg" class="form-control" value="<?= esc($solicitud['peso_kg']) ?>">
                            <label class="form-label-custom m-0 ms-2">L</label>
                            <input type="number" step="0.01" name="peso_l" id="inputL" class="form-control" value="<?= esc($solicitud['peso_l']) ?>">
                        </div>
                    </div>
                </div>

                <div class="section-title mb-2">Motivo (Cuadro de texto donde se especifica qué se va a botar)</div>
                <textarea name="motivo" class="form-control mb-4" rows="3" placeholder="Especifique el motivo del descarte..." required><?= esc($solicitud['motivo']) ?></textarea>
            </div>
        </div>

        <div class="row mt-4 align-items-end">
            <div class="col-md-3">
                <div class="section-title mb-2">Tipo de Empaque (Uno o más)</div>
                <div class="d-flex gap-3">
                    <?php 
                    $empaques_seleccionados = explode(', ', $solicitud['tipo_empaque'] ?? '');
                    ?>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_empaque[]" value="B" id="eB" <?= in_array('B', $empaques_seleccionados) ? 'checked' : '' ?>><label class="form-check-label fw-bold" for="eB">B</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_empaque[]" value="C" id="eC" <?= in_array('C', $empaques_seleccionados) ? 'checked' : '' ?>><label class="form-check-label fw-bold" for="eC">C</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_empaque[]" value="F" id="eF" <?= in_array('F', $empaques_seleccionados) ? 'checked' : '' ?>><label class="form-check-label fw-bold" for="eF">CPC</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_empaque[]" value="O" id="eO" <?= in_array('O', $empaques_seleccionados) ? 'checked' : '' ?>><label class="form-check-label fw-bold" for="eO">O (Otros)</label></div>
                </div>
            </div>
            
            <div class="col-md-4">
                <label class="form-label-custom text-primary">Especificación obligatoria (Por seleccionar 'Otros'):</label>
                <textarea name="empaque_otro_descripcion" id="txtOtros" class="form-control" rows="2" <?= empty($solicitud['empaque_otro_descripcion']) ? 'disabled' : '' ?>><?= esc($solicitud['empaque_otro_descripcion']) ?></textarea>
            </div>

            <div class="col-md-5">
                <div class="p-3 bg-white" style="border: 1px solid var(--border-color, #ced4da); border-radius: 4px;">
                    <h6 class="form-label-custom text-primary mb-1">Descripción de códigos:</h6>
                    <div style="font-size: 0.8rem; font-weight: bold; color: #555;">
                        B: Bolsas | C: Cajas <br>
                        CPC: Contenedor Pulso Cortante | O: Otros (Requiere especificar)
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 d-flex gap-3">
            <a href="<?= base_url('desechos/registroSolicitudes') ?>" class="btn btn-secondary" style="flex: 0 0 auto;">Cancelar</a>
            <button type="button" class="btn-submit" id="btnFakeSubmit" style="flex: 1;">ACTUALIZAR SOLICITUD</button>
        </div>
    </form>
</div>

<!-- Modal de confirmación específico de esta página -->
<div class="modal fade" id="modalAlert" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header text-white" style="background-color: var(--text-blue, #173f5f);">
                <h5 class="modal-title">Confirmación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <p class="fs-5">¿Está seguro de que desea actualizar esta solicitud?</p>
                <p class="text-danger fw-bold">Solo podrá editar esta solicitud una vez. Después de guardar, no podrá volver a modificarla.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 bg-light">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Regresar y Verificar</button>
                <button type="button" id="btnRealSubmit" class="btn text-white px-4" style="background-color: var(--text-blue, #173f5f);">Confirmar Actualización</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de advertencia para Bolsas (Tipo de empaque B) -->
<div class="modal fade" id="modalBolsasWarning" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: var(--azul-oscuro, rgba(28,70,110,0.9));">
                <h5 class="modal-title">Atención</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <p class="fs-6">Ha seleccionado el tipo de empaque <strong>B (Bolsas)</strong>.</p>
                <p class="text-danger fw-bold">Es obligatorio identificar las bolsas, cajas y Contenedores Pulso Cortantes con el nombre del Laboratorio y la fecha.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 bg-light">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="background-color: var(--azul-claro);">Entendido</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de advertencia única (se muestra al cargar la página) -->
<div class="modal fade" id="modalAdvertenciaUnica" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: var(--azul-oscuro);">
                <h5 class="modal-title">Importante</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <p class="fs-6 fw-bold">Esta solicitud solo se puede modificar <strong class="text-danger">UNA VEZ</strong>.</p>
                <p class="text-muted">Después de guardar los cambios, no podrá volver a editarla. Revise bien los datos antes de confirmar.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 bg-light">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="background-color: var(--azul-claro);">Entendido</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de errores (idéntico al de formulario.php) -->
<div class="modal fade" id="modalErrores" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background-color: #f8d7da; border-bottom: 2px solid #f5c6cb;">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-circle me-2"></i> Errores en el formulario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <div class="modal-error-icon">⚠️</div>
                    <p class="mt-2 fw-bold">Por favor, corrija los siguientes errores:</p>
                </div>
                <ul class="error-list" id="listaErrores"></ul>
            </div>
            <div class="modal-footer justify-content-center border-0 bg-light">
                <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">Entendido, revisaré</button>
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
    // LÓGICA DEL FORMULARIO (variantes, pesos, etc.)
    // =============================================
    // Al cargar la página, mostrar el modal de advertencia única
    document.addEventListener('DOMContentLoaded', function() {
        const modalAdvertencia = new bootstrap.Modal(document.getElementById('modalAdvertenciaUnica'));
        modalAdvertencia.show();
    });

    // Diccionario de variantes por tipo (actualizado según clasificación)
    const dicVariantes = {
        'B': [
            'Lencería',
            'Guantes',
            'Batas',
            'Gasas',
            'Plásticos',
            'Tubos plásticos',
            'Algodón',
            'Papel',
            'Cartón',
            'Geles de agarosa',
            'Geles acrilamidas',
            'Cultivos celulares',
            'Pipetas',
            'Tubos de ensayo'
        ],
        'C': [
            'Bisturís',
            'Agujas',
            'puntas',
            'vidrios rotos de laboratorio'
        ],
        'D': [
            'Ratones',
            'Conejos',
            'Chivos',
            'Ovejas',
            'Ranas',
            'Especies Oceánicas',
            'Otros animales',
            'Vísceras',
            'Miembros',
            'Restos de Tejidos',
            'Fluidos corporales',
            'Órganos',
            'Restos biológicos humanos'
        ]
    };

    // Variantes actuales guardadas en la solicitud (para precargar)
    const variantesGuardadas = <?= json_encode(explode(', ', $solicitud['variantes_desecho'] ?? '')) ?>;

    const checksTipo = document.querySelectorAll('.chk-tipo');
    const cajaVar = document.getElementById('cajaVariantes');
    let selectedVariants = new Set();

    function gatherSelectedVariants() {
        const checkboxes = cajaVar.querySelectorAll('input[type="checkbox"][name="variante_desecho[]"]');
        selectedVariants.clear();
        checkboxes.forEach(cb => { if (cb.checked) selectedVariants.add(cb.value); });
    }

    function rebuildVariants() {
        gatherSelectedVariants();
        let html = '';
        let anySelected = false;
        checksTipo.forEach(cb => {
            if (cb.checked) {
                anySelected = true;
                const tipo = cb.value;
                if (dicVariantes[tipo]) {
                    dicVariantes[tipo].forEach(v => {
                        const isChecked = variantesGuardadas.includes(v) || selectedVariants.has(v);
                        html += `<div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="variante_desecho[]" value="${v}" ${isChecked ? 'checked' : ''}>
                                    <label class="form-check-label">${v}</label>
                                 </div>`;
                    });
                }
            }
        });
        cajaVar.innerHTML = html;
        cajaVar.style.display = anySelected ? 'block' : 'none';
    }

    checksTipo.forEach(chk => chk.addEventListener('change', rebuildVariants));
    rebuildVariants();

    // Control de pesos
    const estLiq = document.getElementById('estLiq');
    const estSol = document.getElementById('estSol');
    const inputL = document.getElementById('inputL');
    const inputKg = document.getElementById('inputKg');

    function actualizarPesos() {
        inputL.disabled = !estLiq.checked;
        inputKg.disabled = !estSol.checked;
        // Si están habilitados y no tienen valor, no se marcan como required para no interferir con la validación HTML5
    }
    estLiq.addEventListener('change', actualizarPesos);
    estSol.addEventListener('change', actualizarPesos);
    actualizarPesos();

    // Control de "Otros"
    const eO = document.getElementById('eO');
    const txtOtros = document.getElementById('txtOtros');
    eO.addEventListener('change', function() {
        txtOtros.disabled = !this.checked;
        if (this.checked) txtOtros.setAttribute('required', 'required');
        else txtOtros.removeAttribute('required');
    });
    if (eO.checked) {
        txtOtros.disabled = false;
        txtOtros.setAttribute('required', 'required');
    }

    // Modal de bolsas
    const bolsaCheckbox = document.getElementById('eB');
    const modalBolsas = new bootstrap.Modal(document.getElementById('modalBolsasWarning'));
    if (bolsaCheckbox) {
        bolsaCheckbox.addEventListener('change', function() {
            if (this.checked) modalBolsas.show();
        });
    }

    // Modales de confirmación y errores
    const modalConfirmacion = new bootstrap.Modal(document.getElementById('modalAlert'));
    const modalErrores = new bootstrap.Modal(document.getElementById('modalErrores'));

    // ======== ÚNICO MANEJADOR PARA EL BOTÓN DE ENVÍO ========
    document.getElementById('btnFakeSubmit').addEventListener('click', function(e) {
        e.preventDefault();

        const form = document.getElementById('formSolicitud');
        // Validación HTML5 (campos con required)
        if (!form.reportValidity()) return;

        let errores = [];

        // 1. Tipos de desecho
        const tipos = document.querySelectorAll('input[name="tipo_desecho[]"]:checked');
        if (tipos.length === 0) {
            errores.push('Seleccione al menos un tipo de desecho.');
        }

        // 2. Estados físicos
        const estados = document.querySelectorAll('input[name="estado_fisico[]"]:checked');
        if (estados.length === 0) {
            errores.push('Seleccione al menos un estado físico.');
        }

        // 3. Tipos de empaque
        const empaques = document.querySelectorAll('input[name="tipo_empaque[]"]:checked');
        if (empaques.length === 0) {
            errores.push('Seleccione al menos un tipo de empaque.');
        }

        // 4. "Otros" empaque
        if (eO.checked) {
            if (txtOtros.value.trim() === '') {
                errores.push('Debe especificar la descripción para el empaque "Otros".');
            }
        }

        // 5. Extensión telefónica (numérica)
        const ext = document.querySelector('input[name="ext_telefono"]');
        if (ext && ext.value.trim() !== '' && !/^\d+$/.test(ext.value.trim())) {
            errores.push('La extensión telefónica debe ser un número.');
        }

        // 6. Motivo
        const motivo = document.querySelector('textarea[name="motivo"]');
        if (motivo && motivo.value.trim() === '') {
            errores.push('El motivo es obligatorio.');
        }

        // 7. Variantes (si hay tipos seleccionados)
        if (tipos.length > 0) {
            const variantes = document.querySelectorAll('input[name="variante_desecho[]"]:checked');
            if (variantes.length === 0) {
                errores.push('Debe seleccionar al menos una especificación (variante) para el tipo de desecho elegido.');
            }
        }

        // 8. Pesos según estado
        const solidoChecked = document.getElementById('estSol').checked;
        const liquidoChecked = document.getElementById('estLiq').checked;
        const pesoKg = document.querySelector('input[name="peso_kg"]');
        const pesoL = document.querySelector('input[name="peso_l"]');

        if (solidoChecked) {
            if (pesoKg.value.trim() === '' || isNaN(pesoKg.value) || parseFloat(pesoKg.value) < 0) {
                errores.push('El peso en kg es obligatorio cuando se selecciona "Sólido" y debe ser un número ≥ 0.');
            }
        }
        if (liquidoChecked) {
            if (pesoL.value.trim() === '' || isNaN(pesoL.value) || parseFloat(pesoL.value) < 0) {
                errores.push('El peso en litros es obligatorio cuando se selecciona "Líquido" y debe ser un número ≥ 0.');
            }
        }

        // Si hay errores, mostrar modal de errores
        if (errores.length > 0) {
            const lista = document.getElementById('listaErrores');
            lista.innerHTML = errores.map(err => `<li>${err}</li>`).join('');
            modalErrores.show();
            return;
        }

        // Todo correcto: mostrar modal de confirmación
        modalConfirmacion.show();
    });

    document.getElementById('btnRealSubmit').addEventListener('click', () => {
        document.getElementById('formSolicitud').submit();
    });
</script>
<?= $this->endSection() ?>