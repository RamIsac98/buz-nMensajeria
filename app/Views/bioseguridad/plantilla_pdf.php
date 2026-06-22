<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud Bioseguridad <?= esc($codigo_solicitud) ?></title>
    <style>
        body { font-family: Arial; }
        .header { text-align: center; margin-bottom: 30px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .table th { background: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Solicitud de materiales Bioseguridad</h2>
        <p><strong>Código:</strong> <?= esc($codigo_solicitud) ?></p>
    </div>
    <table class="table">
        <tr><th>Usuario</th><td><?= esc($usuario_nombre) ?></td></tr>
        <tr><th>Centro / Laboratorio</th><td><?= esc($departamento) ?> / <?= esc($laboratorio) ?></td></tr>
        <tr><th>Extensión</th><td><?= esc($ext_telefono) ?></td></tr>
        <tr><th>Contenedores Pulso Cortante</th><td><?= $contenedores_pulso_cantidad ?></td></tr>
        <tr><th>Bolsas Rojas (P/M/G)</th><td><?= $bolsas_rojas_pequena ?> / <?= $bolsas_rojas_mediana ?> / <?= $bolsas_rojas_grande ?></td></tr>
        <tr><th>¿Quién retira?</th><td><?= ($quien_retira == 'mi_persona') ? 'Mi persona' : 'Otra persona: ' . esc($nombre_otra_persona) ?></td></tr>
        <tr><th>Fecha registro</th><td><?= esc($fecha_registro) ?></td></tr>
    </table>
</body>
</html>