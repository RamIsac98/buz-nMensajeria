<?php
/**
 * Vista: Formulario de creación de solicitud de desechos biológicos.
 * 
 * Muestra un formulario para que el usuario solicite la recolección de desechos
 * biológicos (tipos B, C, D, variantes, estado físico, pesos, empaques, motivo, etc.).
 * Incluye validaciones en cliente complejas y modales de confirmación/advertencia.
 * 
 * Conexiones con el controlador:
 * - Carga inicial (GET): DesechosController::crear() (ruta '/desechos/crear').
 *   Recibe:
 *   - $usuario_data (array) – datos del usuario (departamento, laboratorio, username).
 *   - $codigo_automatico (string) – código único generado por el modelo.
 *   - $fecha_automatica (string) – fecha actual formateada.
 * 
 * - Envío del formulario (POST): action="<?= base_url('desechos/registrar') ?>"
 *   → DesechosController::registrar().
 *   Incluye campo oculto 'codigo_solicitud' con el código generado (el controlador
 *   lo usará o generará uno nuevo si no se envía).
 * 
 * - Mensajes flash:
 *   - 'success' → SweetAlert2 (icono éxito, timer 4s). Generado por el controlador
 *     tras un registro exitoso.
 *   - 'error' → SweetAlert2 (icono error, timer 5s, botón confirmar). Generado por
 *     validaciones fallidas o errores de base de datos.
 * 
 * - Modal de advertencia de bolsas (contenido estático): aparece al seleccionar
 *   el empaque "B" (Bolsas) como recordatorio de etiquetado. No requiere interacción
 *   con el controlador.
 * 
 * - Modal de confirmación: muestra un mensaje antes de enviar. Al confirmar,
 *   se ejecuta document.getElementById('formSolicitud').submit() que envía el
 *   formulario al controlador.
 * 
 * - Modal de errores: muestra una lista de errores de validación en cliente antes
 *   de llegar al servidor. Es contenido estático, pero los errores son generados
 *   por el JavaScript de la vista.
 * 
 * Dependencias:
 * - Layout base (layouts/base) con Bootstrap 5.
 * - SweetAlert2 (CDN) para mensajes flash.
 * - JavaScript propio para lógica dinámica (variantes, pesos, validaciones).
 * 
 * @package App\Views\desechos
 */
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Solicitud de Recolección de Desechos Biológicos<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Estilos específicos del formulario */
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

    /* Agregar estilos para el modal de errores */
    .modal-error-icon { font-size: 3rem; color: #dc3545; }
    .error-list { text-align: left; max-height: 200px; overflow-y: auto; padding-left: 20px; color: #721c24; }
    .error-list li { margin-bottom: 6px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-title">Solicitud de Recolección de Desechos Biológicos</div>

<!-- ===== MENSAJES FLASH AHORA CON SWEETALERT (no hay alertas Bootstrap) ===== -->

<div class="form-container">
    <!-- El formulario envía POST a DesechosController::registrar() -->
    <form id="formSolicitud" action="<?= base_url('desechos/registrar') ?>" method="POST">
        
        <div class="watermark-container">
            <div class="badge-fecha">Fecha: <?= $fecha_automatica ?></div>
            <div class="codigo-agua"><?= $codigo_automatico ?></div>
            <input type="hidden" name="codigo_solicitud" value="<?= $codigo_automatico ?>">
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
                            <div class="form-check"><input class="form-check-input chk-tipo" type="checkbox" name="tipo_desecho[]" value="B" id="tB"><label class="form-check-label fw-bold text-primary" for="tB">Tipo B</label></div>
                            <div class="form-check"><input class="form-check-input chk-tipo" type="checkbox" name="tipo_desecho[]" value="C" id="tC"><label class="form-check-label fw-bold text-primary" for="tC">Tipo C</label></div>
                            <div class="form-check"><input class="form-check-input chk-tipo" type="checkbox" name="tipo_desecho[]" value="D" id="tD"><label class="form-check-label fw-bold text-primary" for="tD">Tipo D</label></div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="section-title mb-2">¿Esterilizado?</div>
                        <div class="d-flex gap-3">
                            <div class="form-check"><input class="form-check-input" type="radio" name="esterilizado" value="Sí" id="estSi"><label class="form-check-label" for="estSi">Sí</label></div>
                            <div class="form-check"><input class="form-check-input" type="radio" name="esterilizado" value="No" id="estNo" checked><label class="form-check-label" for="estNo">No</label></div>
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
                        <input type="number" name="ext_telefono" class="form-control" placeholder="Ext." required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="section-title mb-2">Estado del Desecho (Uno o más)</div>
                        <div class="d-flex gap-3">
                            <div class="form-check"><input class="form-check-input chk-estado" type="checkbox" name="estado_fisico[]" value="Líquido" id="estLiq"><label class="form-check-label" for="estLiq">Líquido</label></div>
                            <div class="form-check"><input class="form-check-input chk-estado" type="checkbox" name="estado_fisico[]" value="Sólido" id="estSol"><label class="form-check-label" for="estSol">Sólido</label></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="section-title mb-2">Peso "Aproximado"</div>
                        <div class="inline-inputs">
                            <label class="form-label-custom m-0">kg</label>
                            <input type="number" step="0.01" name="peso_kg" id="inputKg" class="form-control" disabled>
                            <label class="form-label-custom m-0 ms-2">L</label>
                            <input type="number" step="0.01" name="peso_l" id="inputL" class="form-control" disabled>
                        </div>
                    </div>
                </div>

                <div class="section-title mb-2">Motivo (Cuadro de texto donde se especifica qué se va a botar)</div>
                <textarea name="motivo" class="form-control mb-4" rows="3" placeholder="Especifique el motivo del descarte..." required></textarea>
            </div>
        </div>

        <div class="row mt-4 align-items-end">
            <div class="col-md-3">
                <div class="section-title mb-2">Tipo de Empaque (Uno o más)</div>
                <div class="d-flex gap-3">
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_empaque[]" value="B" id="eB"><label class="form-check-label fw-bold" for="eB">B</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_empaque[]" value="C" id="eC"><label class="form-check-label fw-bold" for="eC">C</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_empaque[]" value="F" id="eF"><label class="form-check-label fw-bold" for="eF">CPC</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_empaque[]" value="O" id="eO"><label class="form-check-label fw-bold" for="eO">O (Otros)</label></div>
                </div>
            </div>
            
            <div class="col-md-4">
                <label class="form-label-custom text-primary">Especificación obligatoria (Por seleccionar O):</label>
                <textarea name="empaque_otro_descripcion" id="txtOtros" class="form-control" rows="2" disabled></textarea>
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

        <div class="mt-5">
            <button type="button" class="btn-submit" id="btnFakeSubmit">ENVIAR SOLICITUD DE DESECHOS</button>
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
                <p class="fs-5">¿Está seguro de que los datos introducidos son correctos?</p>
            </div>
            <div class="modal-footer justify-content-center border-0 bg-light">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Regresar y Verificar</button>
                <button type="button" id="btnRealSubmit" class="btn text-white px-4" style="background-color: var(--text-blue, #173f5f);">Confirmar Enviar</button>
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

<!-- NUEVO MODAL DE ERRORES -->
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

    const checksTipo = document.querySelectorAll('.chk-tipo');
    const cajaVar = document.getElementById('cajaVariantes');

    // Guarda las selecciones actuales de variantes
    let selectedVariants = new Set();

    // Función para recolectar las variantes marcadas actualmente
    function gatherSelectedVariants() {
        const checkboxes = cajaVar.querySelectorAll('input[type="checkbox"][name="variante_desecho[]"]');
        selectedVariants.clear();
        checkboxes.forEach(cb => {
            if (cb.checked) selectedVariants.add(cb.value);
        });
    }

    // Función para regenerar las variantes según los tipos marcados
    function rebuildVariants() {
        // Guardar estado actual antes de regenerar
        gatherSelectedVariants();

        let html = '';
        let anySelected = false;
        checksTipo.forEach(cb => {
            if (cb.checked) {
                anySelected = true;
                const tipo = cb.value;
                if (dicVariantes[tipo]) {
                    dicVariantes[tipo].forEach(v => {
                        const isChecked = selectedVariants.has(v);
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

    // Asignar evento a cada checkbox de tipo
    checksTipo.forEach(chk => chk.addEventListener('change', rebuildVariants));
    rebuildVariants();

    // Inicializar la vista al cargar la página
    rebuildVariants();

    // Control de habilitación de pesos según estado
    const estLiq = document.getElementById('estLiq');
    const estSol = document.getElementById('estSol');
    const inputL = document.getElementById('inputL');
    const inputKg = document.getElementById('inputKg');

    function actualizarPesos() {
        inputL.disabled = !estLiq.checked;
        inputKg.disabled = !estSol.checked;
        // Si están habilitados, no son requeridos por HTML5, pero la validación JS se encarga
    }
    estLiq.addEventListener('change', actualizarPesos);
    estSol.addEventListener('change', actualizarPesos);
    actualizarPesos();

    // Control de "Otros" empaque
    const eO = document.getElementById('eO');
    const txtOtros = document.getElementById('txtOtros');
    eO.addEventListener('change', function() {
        txtOtros.disabled = !this.checked;
        if (this.checked) txtOtros.setAttribute('required', 'required');
        else txtOtros.removeAttribute('required');
    });

    // Modal de bolsas (igual)
    const bolsaCheckbox = document.getElementById('eB');
    const modalBolsas = new bootstrap.Modal(document.getElementById('modalBolsasWarning'));
    if (bolsaCheckbox) {
        bolsaCheckbox.addEventListener('change', function() {
            if (this.checked) modalBolsas.show();
        });
    }

    // Modal de confirmación y de errores
    const modalConfirmacion = new bootstrap.Modal(document.getElementById('modalAlert'));
    const modalErrores = new bootstrap.Modal(document.getElementById('modalErrores'));

    // Validación en cliente con modal de errores
    document.getElementById('btnFakeSubmit').addEventListener('click', function(e) {
        e.preventDefault();

        const form = document.getElementById('formSolicitud');
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

        // 5. Extensión telefónica
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
        if (estSol.checked) {
            if (inputKg.value.trim() === '' || isNaN(inputKg.value) || parseFloat(inputKg.value) < 0) {
                errores.push('El peso en kg es obligatorio cuando se selecciona "Sólido" y debe ser un número ≥ 0.');
            }
        }
        if (estLiq.checked) {
            if (inputL.value.trim() === '' || isNaN(inputL.value) || parseFloat(inputL.value) < 0) {
                errores.push('El peso en litros es obligatorio cuando se selecciona "Líquido" y debe ser un número ≥ 0.');
            }
        }

        // Mostrar errores o confirmación
        if (errores.length > 0) {
            const lista = document.getElementById('listaErrores');
            lista.innerHTML = errores.map(err => `<li>${err}</li>`).join('');
            modalErrores.show();
            return;
        }

        modalConfirmacion.show();
    });

    document.getElementById('btnRealSubmit').addEventListener('click', () => {
        document.getElementById('formSolicitud').submit();
    });
</script>
<?= $this->endSection() ?>