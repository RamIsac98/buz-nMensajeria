<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Bioseguridad</title>
    <link rel="stylesheet" href="<?= base_url('bootstrap5/css/bootstrap.min.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('img/logo.svg') ?>">
    <style>
        :root {
            --azul-claro: #2073AF;
            --azul-oscuro: rgba(28, 70, 110, 0.9);
            --amarillo: #ffc107;
            --gris-fondo: #f2f2f2;
            --borde: #ced4da;
        }
        body {
            background-color: #ffffff;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
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
        @media (max-width: 768px) {
            .container-form { padding: 20px; }
            .form-header { flex-direction: column; align-items: start; gap: 10px; }
        }
    </style>
</head>
<body>

    <!-- Barra de navegación (igual que antes) -->
        <header class="mb-4">
                <nav class="custom-navbar rounded-1">
                    <div class="nav-brand-container">
                        <div class="logo-placeholder"> 
                            <img src="<?= base_url('img/logo.svg') ?>" alt="logo">
                        </div>
                        
                        <?php 
                            $current_path = service('request')->getUri()->getPath();
                            $is_home = ($current_path === '' || $current_path === '/' || str_contains($current_path, 'interfaz_usuario_inicial'));
                            
                            // Obtenemos los datos de la sesión para validarlos
                            $rolUsuario = session()->get('rol');
                            $cedulaUsuario = session()->get('cedula');
                        ?>

                        <a href="<?= base_url('interfaz_usuario_inicial') ?>" class="nav-link-custom <?= $is_home ? 'active' : '' ?>">
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

    <div class="container-form">
        <div class="form-header">
            <div class="page-title">Solicitud de Bioseguridad</div>
            <div class="codigo-fecha">
                <div class="badge-fecha">Fecha: <?= $fecha_automatica ?></div>
                <div class="codigo-agua"><?= $codigo_automatico ?></div>
                <input type="hidden" name="codigo_solicitud" value="<?= $codigo_automatico ?>">
            </div>
        </div>

        <form id="formBioseguridad" action="<?= base_url('bioseguridad/registrar') ?>" method="POST">
            <?= csrf_field() ?>

            <!-- Datos del Solicitante (sin rol) -->
            <div class="section-title">Datos del Solicitante</div>
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <label class="form-label">Nombre Completo</label>
                    <input type="text" class="form-control readonly-field" value="<?= esc($usuario_data['username']) ?>" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Extensión Tel.</label>
                    <input type="text" name="ext_telefono" class="form-control" placeholder="Ej: 1234" required>
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
                    <label class="form-label">Laboratorio Interno</label>
                    <input type="text" class="form-control readonly-field" value="<?= esc($usuario_data['nombre_laboratorio']) ?>" readonly>
                </div>
            </div>

            <!-- Material Requerido -->
            <div class="section-title">Material Requerido (Seleccione una o ambas opciones)</div>

            <div class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Contenedores de Pulso Cortante</label>
                    <input type="number" name="contenedores_pulso_cantidad" id="pulsoCantidad" class="form-control" min="0" max="3" value="0">
                    <small class="text-muted">Máximo 3 unidades</small>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Bolsas Rojas (total máximo 10 unidades)</label>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small">Pequeña</label>
                        <input type="number" name="bolsas_rojas_pequena" id="bolsaPeq" class="form-control" min="0" max="10" value="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Mediana</label>
                        <input type="number" name="bolsas_rojas_mediana" id="bolsaMed" class="form-control" min="0" max="10" value="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Grande</label>
                        <input type="number" name="bolsas_rojas_grande" id="bolsaGra" class="form-control" min="0" max="10" value="0">
                    </div>
                </div>
                <div id="bolsasWarning" class="text-danger small mt-2" style="display:none;">El total de bolsas no puede superar 10.</div>
            </div>

            <div class="section-title">¿Quién retira el material?</div>
            <div class="mb-3">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="quien_retira" id="miPersona" value="mi_persona" checked>
                    <label class="form-check-label" for="miPersona">Mi persona</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="quien_retira" id="otraPersona" value="otra_persona">
                    <label class="form-check-label" for="otraPersona">Otro</label>
                </div>
            </div>
            <div class="mb-4" id="nombreOtraDiv" style="display: none;">
                <label class="form-label">Especifique el nombre de quien retira:</label>
                <input type="text" name="nombre_otra_persona" id="nombreOtraPersona" class="form-control" placeholder="Nombre completo">
            </div>

            <div class="mt-5">
                <button type="button" id="btnFakeSubmit" class="btn-submit">ENVIAR SOLICITUD</button>
            </div>
        </form>
    </div>

    <!-- Modal de confirmación -->
    <div class="modal fade" id="modalConfirm" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Confirmar envío</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que los datos son correctos?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnRealSubmit" class="btn btn-primary">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>
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

        // Validación total bolsas
        const bolsaPeq = document.getElementById('bolsaPeq');
        const bolsaMed = document.getElementById('bolsaMed');
        const bolsaGra = document.getElementById('bolsaGra');
        const warningBolsas = document.getElementById('bolsasWarning');

        function validarTotalBolsas() {
            let total = (parseInt(bolsaPeq.value)||0) + (parseInt(bolsaMed.value)||0) + (parseInt(bolsaGra.value)||0);
            if (total > 10) {
                warningBolsas.style.display = 'block';
                return false;
            } else {
                warningBolsas.style.display = 'none';
                return true;
            }
        }
        bolsaPeq.addEventListener('input', validarTotalBolsas);
        bolsaMed.addEventListener('input', validarTotalBolsas);
        bolsaGra.addEventListener('input', validarTotalBolsas);

        // Validación contenedores
        const pulsoInput = document.getElementById('pulsoCantidad');
        pulsoInput.addEventListener('change', function() {
            if (this.value > 3) this.value = 3;
            if (this.value < 0) this.value = 0;
        });

        // Modal
        const modal = new bootstrap.Modal(document.getElementById('modalConfirm'));
        const form = document.getElementById('formBioseguridad');
        document.getElementById('btnFakeSubmit').addEventListener('click', function(e) {
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            if (!validarTotalBolsas()) {
                warningBolsas.scrollIntoView({ behavior: 'smooth' });
                return;
            }
            modal.show();
        });
        document.getElementById('btnRealSubmit').addEventListener('click', function() {
            form.submit();
        });
    </script>
</body>
</html>