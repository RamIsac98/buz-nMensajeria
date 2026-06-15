<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Pregunta de Seguridad</title>
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
            max-width: 500px;
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

        .btn-guardar {
            background-color: var(--azul-oscuro);
            color: white;
            border: none;
            border-radius: 2rem;
            padding: 0.7rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-guardar:hover {
            background-color: var(--azul-claro);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            color: var(--amarillo);
        }

        .form-text {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .alert {
            border-radius: 2rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="card security-card border-0 shadow-lg p-4 p-sm-5">
    <div class="text-center mb-4">
        <img src="<?= base_url('img/logo.svg') ?>" alt="Logo" class="mb-3" style="width: 70px; height: auto;">
        <h3 class="fw-bold">Configuración de Seguridad</h3>
        <p class="text-muted small">Establece una pregunta de seguridad para recuperar tu cuenta en el futuro.</p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('info')): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('info') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('usuarios/guardar_pregunta') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="pregunta_seguridad" class="form-label">Selecciona una Pregunta:</label>
            <select class="form-select" name="pregunta_seguridad" id="pregunta_seguridad" required>
                <option value="" selected disabled>-- Elige una opción --</option>
                <option value="¿Cuál es el nombre de tu primera mascota?">¿Cuál es el nombre de tu primera mascota?</option>
                <option value="¿Cuál es el nombre de la ciudad donde naciste?">¿Cuál es el nombre de la ciudad donde naciste?</option>
                <option value="¿Cuál es tu película favorita de la infancia?">¿Cuál es tu película favorita de la infancia?</option>
            </select>
        </div>

        <div class="mb-4">
            <label for="respuesta_seguridad" class="form-label">Tu Respuesta Secreta:</label>
            <input type="text" class="form-control" name="respuesta_seguridad" id="respuesta_seguridad" 
                   placeholder="Escribe tu respuesta aquí..." required autocomplete="off">
            <div class="form-text mt-2">
                Nota: Recuerda bien esta respuesta; distingue entre mayúsculas y minúsculas al recuperarla.
            </div>
        </div>

        <button type="submit" class="btn btn-guardar w-100 py-2 fw-bold">Guardar y Continuar</button>
    </form>
</div>

<script src="<?= base_url('bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>