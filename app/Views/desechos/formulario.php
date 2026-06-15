<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Desechos Biológicos</title>
    <link rel="stylesheet" href="<?= base_url('bootstrap5/css/bootstrap.min.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('img/logo.svg') ?>">
    <style>
        :root {
            --azul-claro: #2073AF;
            --azul-oscuro: rgba(28, 70, 110, 0.9);
            --amarillo: #ffc107;
            --bg-tab-active: #173f5f;
            --bg-body: #ffffff;
            --bg-form: #f7f7f7;
            --text-blue: #173f5f;
            --border-color: #ced4da;
        }

        body { font-family: Arial, sans-serif; background-color: var(--bg-body); margin: 0; }

        /* HEADER & NAVBAR IDÉNTICO AL DISEÑO */
        .custom-navbar {
            background-color: var(--azul-claro);
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 65px;
        }

        .nav-brand-container {
            display: flex;
            align-items: center;
            padding-left: 20px;
        }

        .logo-placeholder {
            width: 40px;
            height: 40px;
            margin-right: 15px;
            display: inline-block;
            overflow: hidden;
            background-color: transparent;
        }

        .logo-placeholder img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            padding: 0 30px;
            height: 65px;
            font-size: 1.1rem;
            transition: background-color 0.2s ease;
        }

        .nav-link-custom:hover {
            background-color: rgba(0, 0, 0, 0.1);
            color: white;
        }

        .nav-link-custom.active {
            background-color: var(--azul-oscuro);
            color: var(--amarillo) !important;
            font-weight: 500;
        }

        /* --- SECCIÓN DE PERFIL DE USUARIO --- */
        .user-section {
            padding-right: 25px;
        }

        .user-dropdown-toggle {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .user-dropdown-toggle:hover, .user-dropdown-toggle:focus {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--amarillo);
        }

        .user-icon-img {
            width: 20px;
            height: 20px;
        }

        .custom-dropdown-menu {
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .custom-dropdown-menu .dropdown-item {
            font-size: 0.9rem;
            color: #444;
            padding: 8px 16px;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
        }

        .custom-dropdown-menu .dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--azul-claro);
        }

        /* FORMULARIO ESTÉTICA GRIS */
        .page-title { color: var(--text-blue); font-weight: bold; margin: 25px 0 15px 40px; font-size: 1.4rem; }
        .form-container { background-color: var(--bg-form); border-radius: 15px; padding: 40px; margin: 0 40px 40px 40px; position: relative; }
        
        .watermark-container { display: flex; justify-content: flex-end; align-items: center; gap: 15px; margin-bottom: 30px; }
        .badge-fecha { background-color: #2b78a9; color: white; padding: 6px 15px; border-radius: 4px; font-weight: bold; font-size: 0.9rem;}
        .codigo-agua { font-size: 1.5rem; color: #d0d0d0; font-weight: bold; letter-spacing: 1px; }

        .section-title { color: var(--text-blue); font-weight: bold; font-size: 1rem; margin-bottom: 15px; }
        .form-label-custom { font-size: 0.85rem; font-weight: bold; color: #333; margin-bottom: 4px; display: block; }
        
        .input-readonly { background-color: #e9ecef; border: 1px solid var(--border-color); color: #555; }
        .form-control, .form-select { border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.9rem; padding: 6px 10px; }
        
        /* BLOQUES DASHED (Variantes) */
        .variants-box { border: 1px dashed var(--text-blue); padding: 15px; border-radius: 4px; background: transparent; min-height: 100px; display: none; }
        .variants-box .form-check-label { font-size: 0.85rem; color: #666; }

        .btn-submit { background-color: #2b78a9; color: var(--amarillo); font-weight: bold; border: none; padding: 12px 0; width: 100%; border-radius: 4px; font-size: 1rem; transition: 0.3s; }
        .btn-submit:hover { background-color: var(--azul-oscuro); }
        
        /* ALINEACIONES ESPECÍFICAS DEL MOCKUP */
        .inline-inputs { display: flex; align-items: center; gap: 10px; }
        .inline-inputs input { width: 70px; }
    </style>
</head>
<body>

    <header class="mb-4">
                <nav class="custom-navbar rounded-1">
                    <div class="nav-brand-container">
                        <div class="logo-placeholder"> 
                            <img src="<?= base_url('img/logo.svg') ?>" alt="logo">
                        </div>
                        
                        <?php 
                            $current_path = service('request')->getUri()->getPath();
                            $is_home = ($current_path === '' || $current_path === '/' || str_contains($current_path, 'interfazinicial/menuusuario'));
                            
                            // Obtenemos los datos de la sesión para validarlos
                            $rolUsuario = session()->get('rol');
                            $cedulaUsuario = session()->get('cedula');
                        ?>

                        <a href="<?= base_url('interfazinicial/menuusuario') ?>" class="nav-link-custom <?= $is_home ? 'active' : '' ?>">
                            Inicio
                        </a>

                        <a href="<?= base_url('desechos/formulario') ?>" class="nav-link-custom">Solicitud Desechos</a>
                        <a href="<?= base_url('solicitud_bioseguridad') ?>" class="nav-link-custom">Solicitud Bioseguridad</a>
                        <a href="<?= base_url('desechos/registroSolicitudes') ?>" class="nav-link-custom">Registro</a>
                        
                        <?php if ($rolUsuario === 'administrador'): ?>
                            <div class="d-flex align-items-center h-100 dropdown">
                                <a href="#" class="nav-link-custom dropdown-toggle" id="configMenu" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                                    Configuración
                                </a>
                                <ul class="dropdown-menu custom-dropdown-menu border-0 shadow mt-0" aria-labelledby="configMenu">
                                    <li><a class="dropdown-item" href="<?= base_url('usuarios') ?>">Gestión Usuarios</a></li>
                                <li><a class="dropdown-item" href="<?= base_url('gestion-departamento') ?>">Gestión Departamentos</a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('usuarios/bitacora') ?>">Bitácora</a></li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    
                    <div class="d-flex align-items-center h-100 user-section">
                        <div class="dropdown">
                            <a href="#" class="user-dropdown-toggle dropdown-toggle" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?= base_url('img/user.svg') ?>" class="user-icon-img" alt="User Icon">
                                <span>Usuario <strong><?= esc(session()->get('username') ?? 'Sistema') ?></strong></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end custom-dropdown-menu" aria-labelledby="userMenu">
                                <li>
                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalCambiarPassword">
                                        Cambiar contraseña
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?= base_url('login/salir') ?>">
                                        Cerrar sesión
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
    </header>

    <div class="page-title">Solicitud de Bioseguridad - Servicio de Desechos</div>

    <div class="form-container">
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
                        <div class="col-md-7">
                            <label class="form-label-custom">Nombre Completo (Usuario)</label>
                            <input type="text" class="form-control input-readonly" value="<?= esc($usuario_data['username']) ?>" readonly>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-custom">Rol</label>
                            <input type="text" class="form-control input-readonly" value="<?= esc($usuario_data['rol']) ?>" readonly>
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
                    <div class="variants-box" id="cajaVariantes">
                        </div>
                </div>

                <div class="col-md-7">
                    <div class="section-title">Ubicación del Laboratorio</div>
                    <div class="row g-2 mb-4">
                        <div class="col-md-5">
                            <label class="form-label-custom">Centro / Departamento</label>
                            <input type="text" class="form-control input-readonly" value="<?= esc($usuario_data['departamento']) ?>" readonly>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-custom">Laboratorio Interno</label>
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
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_empaque[]" value="F" id="eF"><label class="form-check-label fw-bold" for="eF">F</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_empaque[]" value="O" id="eO"><label class="form-check-label fw-bold" for="eO">O (Otros)</label></div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label-custom text-primary">Especificación obligatoria (Por seleccionar 'Otros'):</label>
                    <textarea name="empaque_otro_descripcion" id="txtOtros" class="form-control" rows="2" disabled></textarea>
                </div>

                <div class="col-md-5">
                    <div class="p-3 bg-white" style="border: 1px solid var(--border-color); border-radius: 4px;">
                        <h6 class="form-label-custom text-primary mb-1">Descripción de códigos:</h6>
                        <div style="font-size: 0.8rem; font-weight: bold; color: #555;">
                            B: Bolsas | C: Cajas <br>
                            F: Frascos | O: Otros (Requiere especificar)
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <button type="button" class="btn-submit" id="btnFakeSubmit">ENVIAR SOLICITUD DE DESECHO</button>
            </div>
        </form>
    </div>

    <div class="modal fade" id="modalAlert" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header text-white" style="background-color: var(--text-blue);">
                    <h5 class="modal-title">Confirmación</h5>
                </div>
                <div class="modal-body text-center p-4">
                    <p class="fs-5">¿Está seguro de que los datos introducidos son correctos?</p>
                </div>
                <div class="modal-footer justify-content-center border-0 bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Regresar y Verificar</button>
                    <button type="button" id="btnRealSubmit" class="btn text-white px-4" style="background-color: var(--text-blue);">Confirmar Enviar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>
    <script>
        // Lógica de Variantes
        const dicVariantes = {
            'B': ['[Tipo B] - Placas de Petri y guantes contaminados', '[Tipo B] - Muestras de sangre'],
            'C': ['[Tipo C] - Solventes orgánicos y reactivos usados', '[Tipo C] - Cepas bacterianas'],
            'D': ['[Tipo D] - Agujas e inyectadoras', '[Tipo D] - Vidrio roto de laboratorio']
        };

        const checksTipo = document.querySelectorAll('.chk-tipo');
        const cajaVar = document.getElementById('cajaVariantes');

        checksTipo.forEach(chk => {
            chk.addEventListener('change', () => {
                let html = '';
                let haySeleccion = false;
                checksTipo.forEach(c => {
                    if(c.checked) {
                        haySeleccion = true;
                        dicVariantes[c.value].forEach(v => {
                            html += `<div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="variante_desecho[]" value="${v}">
                                        <label class="form-check-label">${v}</label>
                                     </div>`;
                        });
                    }
                });
                cajaVar.innerHTML = html;
                cajaVar.style.display = haySeleccion ? 'block' : 'none';
            });
        });

        // Activar Pesos
        document.getElementById('estLiq').addEventListener('change', e => document.getElementById('inputL').disabled = !e.target.checked);
        document.getElementById('estSol').addEventListener('change', e => document.getElementById('inputKg').disabled = !e.target.checked);
        
        // Activar Otros Empaques
        document.getElementById('eO').addEventListener('change', e => {
            const txt = document.getElementById('txtOtros');
            txt.disabled = !e.target.checked;
            if(e.target.checked) txt.setAttribute('required', 'required');
            else txt.removeAttribute('required');
        });

        // Modal
        const modalInstance = new bootstrap.Modal(document.getElementById('modalAlert'));
        document.getElementById('btnFakeSubmit').addEventListener('click', () => {
            if(document.getElementById('formSolicitud').reportValidity()) modalInstance.show();
        });
        document.getElementById('btnRealSubmit').addEventListener('click', () => {
            document.getElementById('formSolicitud').submit();
        });
    </script>
</body>
</html>