<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
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

        /* Factorización: Se cambió el ID por una clase para evitar duplicidad de IDs en el HTML */
        .btn-custom {
            background-color: var(--color-ivic-claro);
            color: #ffff;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-custom:hover {
            color: var(--color-accent);
            transform: translateY(-2px);
            background-color: var(--color-ivic-oscuro);
        }
        
        p, #nota {
            color: var(--color-ivic-claro);
        }

        h3, label {
            color: var(--color-ivic-oscuro);
        }
    </style>
</head>
<body class="vh-100 d-flex align-items-center justify-content-center">

<div class="container" style="max-width: 500px;">
    <div class="card shadow-lg p-4 p-sm-5 border-0">
        <div class="card-body text-center">
            
            <h3 class="mb-3 fw-bold">Recuperación de Cuenta</h3>
            <p class="small mb-4">Ingresa tu cédula de identidad para verificar tu perfil en el sistema.</p>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger text-start small mb-3" role="alert">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('login/validar_usuario') ?>" method="POST" class="text-start">
                
                <div class="mb-4">
                    <label for="cedula" class="form-label fw-semibold">Cédula de Identidad</label>
                    <input type="text" name="cedula" id="cedula" class="form-control text-start" placeholder="Ej: 12345678" required>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-custom fw-bold py-2 shadow-sm">Verificar Datos</button>
                    <a href="<?= base_url('login') ?>" class="btn btn-custom btn-sm py-2">Volver al Login</a>
                </div>

            </form>

        </div>
    </div>
</div>

</body>
</html>