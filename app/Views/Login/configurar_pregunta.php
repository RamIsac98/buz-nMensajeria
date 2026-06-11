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
            --color-ivic-claro: #2073AF;
            --color-ivic-oscuro: rgba(28, 70, 110, 0.9);
            --color-accent: #ffc107;
        }

        body, #btn-enviar{
            background-color: var(--color-ivic-claro);
        }

        #btn-enviar{
            color: #ffff;
            transition: all 0.3s ease;

        }
        #btn-enviar:hover {
            color: var(--color-accent);
            transform: translateY(-2px);
            background-color: var(--color-ivic-oscuro);

        }

        
        p, #nota {
            color: var(--color-ivic-claro);
        }

        h3, label, option{
            color:  var(--color-ivic-oscuro);
        }
        
    </style>

</head>
<body>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-sm p-4 style-card" style="width: 100%; max-width: 500px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold" id='titulo'>Configuración de Seguridad</h3>
            <p class=" small">Por favor, establece una pregunta de seguridad para recuperar tu cuenta en el futuro.</p>
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
                <label for="pregunta_seguridad" class="form-label fw-semibold">Selecciona una Pregunta:</label>
                <select class="form-select" name="pregunta_seguridad" id="pregunta_seguridad" required>
                    <option value="" selected disabled>-- Elige una opción --</option>
                    <option value="¿Cuál es el nombre de tu primera mascota?">¿Cuál es el nombre de tu primera mascota?</option>
                    <option value="¿Cuál es el nombre de la ciudad donde naciste?">¿Cuál es el nombre de la ciudad donde naciste?</option>
                    <option value="¿Cuál es tu película favorita de la infancia?">¿Cuál es tu película favorita de la infancia?</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="respuesta_seguridad" class="form-label fw-semibold">Tu Respuesta Secreta:</label>
                <input type="text" class="form-control" name="respuesta_seguridad" id="respuesta_seguridad" placeholder="Escribe tu respuesta aquí..." required autocomplete="off">
                <div class="form-text " id='nota'>Nota: Recuerda bien esta respuesta; distingue entre mayúsculas y minúsculas al recuperarla.</div>
            </div>

            <button type="submit" class="btn  w-100 py-2 fw-bold" id='btn-enviar'>Guardar y Continuar</button>
        </form>
    </div>
</div>

<script src="<?= base_url('bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>