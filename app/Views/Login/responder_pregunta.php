<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pregunta de Seguridad</title>
    <link rel="stylesheet" href="<?= base_url('bootstrap5/css/bootstrap.min.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('img/logo.svg') ?>">
    
    <style>
        :root {
            --color-ivic-claro: #2073AF;
            --color-ivic-oscuro: rgba(28, 70, 110, 0.9);
            --color-accent: #ffc107;
        }

        body {
            background-color: var(--color-ivic-claro);
        }

        #btn-enviar {
            background-color: var(--color-ivic-claro);
            color: #ffffff;
            transition: all 0.3s ease;
        }

        #btn-enviar:hover {
            color: var(--color-accent);
            transform: translateY(-2px);
            background-color: var(--color-ivic-oscuro);
        }

        p {
            color: var(--color-ivic-claro);
        }

        h3, label, span, strong {
            color: var(--color-ivic-oscuro);
        }
    </style>
</head>
<body class="vh-100 d-flex align-items-center justify-content-center">

<div class="container" style="max-width: 400px;"> <div class="card shadow p-3 border-0 rounded-3"> 
        <div class="card-body p-2"> 
            
            <div class="text-center mb-3">
                <h3 class="fw-bold fs-5 mb-1">Validación de Seguridad</h3> <p class="small text-muted mb-0" style="font-size: 0.85rem;">Responde la pregunta asignada para restablecer tu contraseña.</p>
            </div>

            <?php if (isset($error) && !empty($error)): ?>
                <div class="alert alert-danger text-start small py-2 px-3 mb-3" role="alert">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('login/guardar_nueva_clave') ?>" method="POST" class="text-start">
                
                <?= csrf_field() ?>

                <input type="hidden" name="usuario_id" value="<?= $usuario_id ?>">

                <div class="mb-3 bg-light p-2 rounded border">
                    <span class="text-muted d-block fw-semibold text-uppercase" style="font-size: 0.7rem;">Tu Pregunta de Seguridad:</span>
                    <strong class="d-block mt-1 small">¿<?= esc($pregunta) ?>?</strong>
                </div>

                <div class="mb-2">
                    <label for="respuesta_seguridad" class="form-label small fw-semibold mb-1">Escribe tu Respuesta (Respeta las Mayúsculas y Minúsculas.)</label>
                    <input type="text" name="respuesta_seguridad" id="respuesta_seguridad" class="form-control form-control-sm" placeholder="Tu respuesta secreta" required autocomplete="off">
                </div>

                <hr class="text-muted my-3"> <div class="mb-2">
                    <label for="password" class="form-label small fw-semibold mb-1">Nueva Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control form-control-sm" placeholder="Mínimo 6 caracteres" required>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label small fw-semibold mb-1">Confirmar Nueva Contraseña</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control form-control-sm" placeholder="Repite tu contraseña" required>
                </div>

                <div class="d-grid gap-2 mt-3">
                    <button type="submit" class="btn fw-bold py-2 small" id='btn-enviar'>Restablecer Contraseña</button>
                    <a href="<?= base_url('login') ?>" class="btn btn-link text-decoration-none btn-sm py-0">Cancelar operación</a>
                </div>

            </form>

        </div>
    </div>
</div>

</body>
</html>