<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Laboratorios</title>
    <style>
        @page { margin: 40px 30px; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        
        /* Estilo de Cabecera */
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #2073AF; padding-bottom: 8px; }
        .header h2 { color: #1C466E; margin: 0 0 5px 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 0; color: #666; font-size: 11px; }

        /* Estilo de Tabla */
        .table-pdf { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-pdf th { background-color: #2073AF; color: #ffffff; font-weight: bold; padding: 8px; text-align: left; }
        .table-pdf td { border: 1px solid #ddd; padding: 6px; }
        
        /* Filas alternas para mejor lectura */
        .table-pdf tr:nth-child(even) { background-color: #f2f2f2; }

        /* Línea superior sutil que divide limpiamente cuando cambia el Centro */
        .linea-agrupador { border-top: 1.5px solid #2073AF; }

        /* Pie de página */
        .footer { margin-top: 20px; font-size: 10px; color: #777; text-align: center; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reporte de Laboratorios</h2>
        <p>Fecha de generación: <?= date('d/m/Y H:i:s') ?></p>
        <?php if(!empty($depto_seleccionado) && $depto_seleccionado !== 'todos'): ?>
            <p><strong>Centro filtrado:</strong> <?= esc($laboratorios[0]['nombre_departamento'] ?? 'Seleccionado') ?></p>
        <?php endif; ?>
    </div>

    <table class="table-pdf">
        <thead>
            <tr>
                <th style="width: 10%;">ID</th>
                <th>Nombre del Laboratorio</th>
                <th>Centro</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($laboratorios)): ?>
                <?php 
                // Inicializamos la variable de control antes del ciclo
                $last_centro = null; 
                ?>
                <?php foreach ($laboratorios as $lab): ?>
                    <?php 
                    // Evaluamos el centro actual
                    $current_centro = $lab['nombre_departamento'] ?? '';
                    $mostrar_centro = ($current_centro !== $last_centro);
                    
                    // Actualizamos el control para la próxima iteración
                    $last_centro = $current_centro;
                    ?>
                    
                    <tr class="<?= $mostrar_centro ? 'linea-agrupador' : '' ?>">
                        <td style="font-weight: bold; color: #1C466E;"><?= $lab['id'] ?></td>
                        <td><?= esc($lab['nombre']) ?></td>
                        <td>
                            <?= $mostrar_centro ? esc($current_centro) : '' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align: center; padding: 20px; font-style: italic; color: #777;">
                        No se encontraron laboratorios registrados.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Reporte Automatizado de Inventario de Laboratorios.
    </div>

</body>
</html>