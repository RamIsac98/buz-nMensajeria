<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte General de Estructura y Personal</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1c466e; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #1c466e; font-size: 18px; }
        .header p { margin: 5px 0 0 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #1c466e; color: white; padding: 8px; text-align: left; font-weight: bold; border: 1px solid #ddd; }
        td { padding: 7px; border: 1px solid #ddd; vertical-align: middle; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .status-active { color: green; font-weight: bold; }
        .status-inactive { color: red; font-weight: bold; }
        .text-muted { color: #888; font-style: italic; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 9px; color: #777; border-top: 1px solid #ddd; padding-top: 5px; }
        /* Línea superior sutil para identificar visualmente el cambio de grupo */
        .linea-agrupador { border-top: 1.5px solid #1c466e; }
    </style>
</head>
<body>

    <div class="header">
        <h2>REPORTE INTEGRAL: CENTROS, LABORATORIOS Y USUARIOS</h2>
        <p>Fecha de Emisión: <?= date('d/m/Y h:i A') ?> | Filtro: <?= ($depto_seleccionado === 'todos' || empty($depto_seleccionado)) ? 'Todos los Departamentos' : 'Departamento Seleccionado' ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Centro</th>
                <th style="width: 25%;">Laboratorio Asignado</th>
                <th style="width: 20%;">Personal / Usuario</th>
                <th style="width: 12%;">Cédula</th>
                <th style="width: 13%;">Rol / Cargo</th>
                <th style="width: 10%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($reporte)): ?>
                <?php 
                // Inicializamos variables de control para el agrupamiento
                $last_centro = null;
                $last_lab = null;
                ?>
                <?php foreach ($reporte as $row): ?>
                    <?php 
                    // Detectar valores de la fila actual
                    $current_centro = $row['nombre_departmento'] ?? $row['nombre_departamento'];
                    $current_lab = $row['nombre_laboratorio'] ?? '';

                    // Evaluamos si el centro o el laboratorio cambiaron respecto a la fila anterior
                    $mostrar_centro = ($current_centro !== $last_centro);
                    $mostrar_lab = ($mostrar_centro || $current_lab !== $last_lab);

                    // Guardamos los valores actuales para la siguiente iteración
                    $last_centro = $current_centro;
                    $last_lab = $current_lab;
                    ?>
                    <tr class="<?= $mostrar_lab ? 'linea-agrupador' : '' ?>">
                        
                        <td>
                            <?= $mostrar_centro ? esc($current_centro) : '' ?>
                        </td>

                        <td>
                            <?php if ($mostrar_lab): ?>
                                <?= !empty($current_lab) ? esc($current_lab) : '<span class="text-muted">Sin laboratorios registrados</span>' ?>
                            <?php endif; ?>
                        </td>

                        <td><?= !empty($row['nombre_usuario']) ? esc($row['nombre_usuario']) : '<span class="text-muted">Sin usuario asignado</span>' ?></td>
                        
                        <td><?= !empty($row['cedula_usuario']) ? esc($row['cedula_usuario']) : '-' ?></td>
                        
                        <td><?= !empty($row['rol_usuario']) ? ucfirst(esc($row['rol_usuario'])) : '-' ?></td>
                        
                        <td>
                            <?php if ($row['nombre_usuario'] !== null): ?>
                                <span class="<?= $row['estado_usuario'] == 1 ? 'status-active' : 'status-inactive' ?>">
                                    <?= $row['estado_usuario'] == 1 ? 'Activo' : 'Inactivo' ?>
                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;" class="text-muted">No se encontraron registros que coincidan con la estructura solicitada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Sistema de Gestión Interno - Página 1
    </div>

</body>
</html>