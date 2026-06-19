<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud <?= esc($codigo_solicitud) ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 30px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .info-usuario { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Comprobante de Solicitud de Desechos</h2>
        <p><strong>Código:</strong> <?= esc($codigo_solicitud) ?></p>
    </div>

    <table class="table">
        <tr><th>Nombre del Usuario</th><td><?= esc($usuario_nombre) ?></td></tr>
        <tr><th>Centro / Laboratorio</th><td><?= esc($departamento) ?> / <?= esc($laboratorio) ?></td></tr>
        <tr><th>Extensión / Teléfono</th><td><?= esc($ext_telefono) ?></td></tr>
        <tr><th>Tipo de Desecho</th><td><?= esc($tipos_desecho) ?></td></tr>
        <tr><th>Especificaciones de Desechos</th><td><?= esc($variantes_desecho) ?></td></tr>
        <tr><th>Estado Físico</th><td><?= esc($estado) ?></td></tr>
        <tr><th>Peso (Kg) / Volumen (L)</th><td><?= esc($peso_kg) ?> Kg / <?= esc($peso_l) ?> L</td>
        <tr><th>Esterilizado</th><td><?= $esterilizado == 1 ? 'Sí' : 'No' ?></td>
        <tr><th>Tipo de Empaque</th>
            <td>
                <?php
                    // Reemplazar 'F' por 'CPC' en la lista de empaques
                    $empaqueMostrado = str_replace('F', 'CPC', $tipo_empaque);
                    // Si hay 'CPC' y además 'O', se muestra normal
                    echo esc($empaqueMostrado) . (isset($empaque_otro_descripcion) && !empty($empaque_otro_descripcion) ? ' - ' . esc($empaque_otro_descripcion) : '');
                ?>
            </td>
        </tr>
        <tr><th>Motivo</th><td><?= esc($motivo) ?></td>
    </table>

    <div class="info-usuario">
        <p><strong>Fecha de registro:</strong> <?= esc($fecha_registro) ?></p>
    </div>
</body>
</html>