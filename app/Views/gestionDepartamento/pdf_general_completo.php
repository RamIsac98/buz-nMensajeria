<?php
/**
 * Vista: Plantilla PDF para Reporte General de Estructura Organizacional.
 * 
 * Esta vista se utiliza exclusivamente para generar el reporte en PDF
 * que muestra la estructura jerárquica completa del sistema: departamentos,
 * laboratorios y usuarios asociados, con su estado y rol.
 * 
 * No extiende el layout base y no utiliza assets externos (CSS integrado).
 * 
 * Conexiones con el controlador:
 * - Es invocada por GestionController::generarPdfGeneral()
 *   Ruta: '/gestion-departamento/generar-pdf-general?depto_id={id}'
 * 
 * - Recibe del controlador las siguientes variables:
 *   - $reporte (array) – Datos jerárquicos obtenidos de UsuarioModel::getReporteGeneral($depto_id)
 *     Cada fila contiene: nombre_departamento, nombre_laboratorio, nombre_usuario,
 *     apellido_usuario, username_usuario, cedula_usuario, rol_usuario, estado_usuario.
 *   - $depto_seleccionado (string|int) – Valor del filtro ('todos' o ID de departamento).
 * 
 * - El controlador (GestionController::generarPdfGeneral()) realiza:
 *   $usuarioModel = new \App\Models\UsuarioModel();
 *   $data = [
 *       'reporte'            => $usuarioModel->getReporteGeneral($depto_id),
 *       'depto_seleccionado' => $depto_id
 *   ];
 *   $html = view('gestionDepartamento/pdf_general_completo', $data);
 *   $dompdf = new \Dompdf\Dompdf();
 *   $dompdf->loadHtml($html);
 *   $dompdf->setPaper('A4', 'landscape');
 *   $dompdf->render();
 *   $dompdf->stream("Reporte_General_Estructura_y_Usuarios.pdf", ["Attachment" => true]);
 * 
 * - El PDF se descarga automáticamente (Attachment = true) en orientación landscape (horizontal).
 *   Esto permite mostrar las 6 columnas de información de manera legible.
 * 
 * - El reporte muestra agrupación visual: cada cambio de departamento o laboratorio
 *   se resalta con una línea divisoria (class="linea-agrupador") y las celdas de centro
 *   y laboratorio solo se muestran en la primera fila de cada grupo (usando variables
 *   $last_centro y $last_lab para controlar la repetición).
 * 
 * Dependencias:
 * - Dompdf (librería PHP, no requiere assets externos).
 * - No utiliza Bootstrap ni JavaScript.
 * - Estilos CSS integrados para el formato del PDF.
 * 
 * @package App\Views\gestionDepartamento
 */
?>
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
                <th style="width: 20%;">Usuario / Personal</th>
                <th style="width: 12%;">Cédula</th>
                <th style="width: 13%;">Rol / Cargo</th>
                <th style="width: 10%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($reporte)): ?>
                <?php 
                $last_centro = null;
                $last_lab = null;
                ?>
                <?php foreach ($reporte as $row): ?>
                    <?php 
                    $current_centro = $row['nombre_departamento'];
                    $current_lab = $row['nombre_laboratorio'] ?? '';

                    $mostrar_centro = ($current_centro !== $last_centro);
                    $mostrar_lab = ($mostrar_centro || $current_lab !== $last_lab);

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

                        <!-- ✅ Muestra nombre completo y username -->
                        <td>
                            <?php if (!empty($row['nombre_usuario']) && !empty($row['apellido_usuario'])): ?>
                                <strong><?= esc($row['nombre_usuario'] . ' ' . $row['apellido_usuario']) ?></strong>
                                <?php if (!empty($row['username_usuario'])): ?>
                                    <br><span class="text-muted">(<?= esc($row['username_usuario']) ?>)</span>
                                <?php endif; ?>
                            <?php elseif (!empty($row['username_usuario'])): ?>
                                <strong><?= esc($row['username_usuario']) ?></strong>
                            <?php else: ?>
                                <span class="text-muted">Sin usuario asignado</span>
                            <?php endif; ?>
                        </td>
                        
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