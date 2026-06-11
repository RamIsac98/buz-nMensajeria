<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Bitácora de Auditoría</title>
    <style>
        @page { margin: 40px 30px; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #2073AF; padding-bottom: 8px; }
        .header h2 { color: #1C466E; margin: 0 0 5px 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 0; color: #666; font-size: 11px; }
        .table-pdf { width: 100%; border-collapse: collapse; }
        .table-pdf th { background-color: #2073AF; color: #ffffff; font-weight: bold; padding: 8px; text-align: left; }
        .table-pdf td { border: 1px solid #ddd; padding: 6px; }
        .footer { margin-top: 20px; font-size: 10px; color: #777; text-align: center; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Bitácora de Auditoría</h2>
        <p>Fecha de generación: <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <table class="table-pdf">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Tipo</th>
                <th>Registro</th>
                <th>Fecha</th>
                <th>IP</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($bitacora) && is_array($bitacora)): ?>
                <?php foreach($bitacora as $log): ?>
                <tr>
                    <td><?= $log['id'] ?></td>
                    <td><?= !empty($log['username']) ? esc($log['username']) : 'Sistema' ?></td>
                    <td><?= esc($log['tipo_solicitud']) ?></td>
                    <td><?= esc($log['registro']) ?></td>
                    <td><?= esc($log['fecha']) ?></td>
                    <td><?= esc($log['ip']) ?></td>
                    <td><?= esc($log['accion']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No hay registros disponibles.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Reporte Automatizado de Control Interno y Auditoría del Sistema.
    </div>
</body>
</html>