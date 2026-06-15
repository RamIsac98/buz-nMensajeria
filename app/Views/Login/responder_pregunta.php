<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Seguridad</title>
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

        .security-card {
            max-width: 450px;
            width: 100%;
            border-radius: 1.5rem;
            background: white;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .security-card:hover {
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
            font-size: 0.85rem;
        }

        .form-control, .form-control-sm {
            border: 2px solid #e0e0e0;
            border-radius: 0.75rem;
            padding: 0.6rem 1rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: var(--azul-claro);
            box-shadow: 0 0 0 0.25rem rgba(32, 115, 175, 0.25);
            outline: none;
        }

        .btn-reestablecer {
            background-color: var(--azul-oscuro);
            color: white;
            border: none;
            border-radius: 2rem;
            padding: 0.7rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-reestablecer:hover {
            background-color: var(--azul-claro);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            color: var(--amarillo);
        }

        .question-box {
            background-color: #f8f9fa;
            border-left: 4px solid var(--azul-claro);
            border-radius: 0.75rem;
            padding: 0.8rem;
        }
        .question-text {
            font-size: 0.9rem;
            color: #2c3e50;
        }
        .alert {
            border-radius: 2rem;
            font-size: 0.85rem;
        }
        .btn-cancel {
            color: #6c757d;
            font-size: 0.8rem;
            transition: color 0.2s;
        }
        .btn-cancel:hover {
            color: var(--amarillo);
        }
    </style>
</head>
<body>

<div class="card security-card border-0 shadow-lg p-4 p-sm-5">
    <div class="text-center mb-4">
        <img src="<?= base_url('img/logo.svg') ?>" alt="Logo" class="mb-3" style="width: 70px; height: auto;">
        <h3 class="fw-bold fs-4">Validación de Seguridad</h3>
        <p class="text-muted small">Responde la pregunta asignada para restablecer tu contraseña.</p>
    </div>

    <?php if (isset($error) && !empty($error)): ?>
        <div class="alert alert-danger text-center py-2 mb-4" role="alert">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('login/guardar_nueva_clave') ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="usuario_id" value="<?= $usuario_id ?>">

        <div class="question-box mb-4">
            <span class="text-muted d-block fw-semibold text-uppercase" style="font-size: 0.7rem;">Tu pregunta de seguridad:</span>
            <strong class="d-block mt-1 question-text">¿<?= esc($pregunta) ?>?</strong>
        </div>

        <div class="mb-3">
            <label for="respuesta_seguridad" class="form-label">Tu respuesta (respeta mayúsculas y minúsculas)</label>
            <input type="text" name="respuesta_seguridad" id="respuesta_seguridad" class="form-control" 
                   placeholder="Escribe tu respuesta secreta" required autocomplete="off">
        </div>

        <hr class="my-3">

        <div class="mb-3">
            <label for="password" class="form-label">Nueva Contraseña</label>
            <input type="password" name="password" id="password" class="form-control" 
                   placeholder="Mínimo 6 caracteres" required>
        </div>

        <div class="mb-4">
            <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                   placeholder="Repite tu contraseña" required>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-reestablecer w-100 py-2 fw-bold">Restablecer Contraseña</button>
            <a href="<?= base_url('login') ?>" class="btn btn-link text-decoration-none btn-cancel text-center">Cancelar operación</a>
        </div>
    </form>
</div>

<script src="<?= base_url('bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>