<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link href="<?= base_url('bootstrap5/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= base_url('img/logo.svg') ?>">
    <style>
        :root {
            --azul-claro: #2073AF;
            --azul-oscuro: rgba(28, 70, 110, 0.9);
            --amarillo: #ffc107;
        }

        body {
            background: linear-gradient(135deg, var(--azul-claro) 0%, #145a8a 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .recovery-card {
            max-width: 500px;
            width: 100%;
            border-radius: 1.5rem;
            background: white;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .recovery-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1.5rem 3rem rgba(0,0,0,0.2) !important;
        }

        h3 {
            color: var(--azul-oscuro);
            font-weight: 700;
        }

        .form-label {
            color: var(--azul-oscuro);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 0.75rem;
            padding: 0.7rem 1rem;
            transition: all 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--azul-claro);
            box-shadow: 0 0 0 0.25rem rgba(32, 115, 175, 0.25);
            outline: none;
        }

        .btn-verificar {
            background-color: var(--azul-oscuro);
            color: white;
            border: none;
            border-radius: 2rem;
            padding: 0.7rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-verificar:hover {
            background-color: var(--azul-claro);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            color: var(--amarillo);
        }

        .btn-volver {
            background-color: transparent;
            color: #6c757d;
            border: 1px solid #dee2e6;
            transition: all 0.2s;
        }
        .btn-volver:hover {
            background-color: #f8f9fa;
            color: var(--azul-claro);
            border-color: var(--azul-claro);
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
</head>
<body>

<div class="card recovery-card border-0 shadow-lg p-4 p-sm-5">
    <div class="text-center mb-4">
        <img src="<?= base_url('img/logo.svg') ?>" alt="Logo" class="mb-3" style="width: 70px; height: auto;">
        <h3 class="fw-bold fs-4">Recuperación de Cuenta</h3>
        <p class="text-muted small">Ingresa tu cédula de identidad (con tipo) para verificar tu perfil en el sistema.</p>
    </div>

    <!-- ===== SWEETALERT2 PARA MENSAJES FLASH ===== -->
    <?php if (session()->getFlashdata('error')): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?= esc(session()->getFlashdata('error')) ?>',
                    confirmButtonColor: '#d33',
                    timer: 5000,
                    timerProgressBar: true,
                    showConfirmButton: true
                });
            });
        </script>
    <?php endif; ?>

    <form action="<?= base_url('login/validar_usuario') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="mb-4">
            <label for="tipo_cedula" class="form-label">Tipo de Cédula</label>
            <div class="cedula-group">
                <select name="tipo_cedula" id="tipo_cedula" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <option value="V" <?= old('tipo_cedula') == 'V' ? 'selected' : '' ?>>Venezolano (V)</option>
                    <option value="E" <?= old('tipo_cedula') == 'E' ? 'selected' : '' ?>>Extranjero (E)</option>
                </select>
                <input type="text" name="cedula" id="cedula" class="form-control" 
                       placeholder="Número de cédula" value="<?= old('cedula') ?>" 
                       required autocomplete="off" maxlength="10">
            </div>
            <small class="text-muted">La cédula debe tener entre 6 y 10 dígitos numéricos.</small>
        </div>

        <div class="d-grid gap-3">
            <button type="submit" class="btn btn-verificar w-100 py-2 fw-bold">Verificar Datos</button>
            <a href="<?= base_url('login') ?>" class="btn btn-volver w-100 py-2 fw-semibold">Volver al Login</a>
        </div>
    </form>
</div>

<script src="<?= base_url('bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>