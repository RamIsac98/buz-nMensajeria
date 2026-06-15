<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> | Sistema de Mensajería</title>
    <link rel="stylesheet" href="<?= base_url('bootstrap5/css/bootstrap.min.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('img/logo.svg') ?>">
    <style>
        :root {
            --azul-claro: #2073AF;
            --azul-oscuro: rgba(28, 70, 110, 0.9);
            --amarillo: #ffc107;
        }
        body {
            background-color: #f8f9fc;
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
        }
        .main-content {
            min-height: calc(100vh - 80px);
            padding: 30px 0;
        }
        /* Estilos del navbar ya están en el archivo CSS externo o aquí */
        /* Puedes copiar aquí los estilos del navbar si no usas archivo externo */
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>

    <?= view('layouts/_navbar') ?>

    <div class="container-fluid main-content">
        <?= $this->renderSection('content') ?>
    </div>

    <script src="<?= base_url('bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>