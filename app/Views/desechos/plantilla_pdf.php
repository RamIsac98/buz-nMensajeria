<?php
/**
 * Vista: Plantilla PDF para solicitudes de desechos biológicos.
 * 
 * Esta vista se utiliza exclusivamente para generar el reporte en PDF
 * de una solicitud de desechos mediante la librería Dompdf.
 * No extiende el layout base y no utiliza assets externos (CSS integrado).
 * 
 * Conexiones con el controlador:
 * - Es invocada por DesechosController::generarPdf($id) (ruta '/desechos/generarPdf/{id}')
 *   para renderizar el HTML que luego se convierte a PDF.
 * 
 * - Recibe del controlador las siguientes variables (asignadas desde la solicitud y el usuario):
 *   - $codigo_solicitud (string) – Código único de la solicitud.
 *   - $usuario_nombre (string) – Nombre completo del usuario solicitante.
 *   - $departamento (string) – Nombre del departamento/centro.
 *   - $laboratorio (string) – Nombre del laboratorio.
 *   - $ext_telefono (string) – Extensión telefónica.
 *   - $tipos_desecho (string) – Tipos de desecho seleccionados (ej. "B, C").
 *   - $variantes_desecho (string) – Variantes/especificaciones seleccionadas.
 *   - $estado (string) – Estado físico (ej. "Líquido, Sólido").
 *   - $peso_kg (float|null) – Peso en kilogramos.
 *   - $peso_l (float|null) – Volumen en litros.
 *   - $esterilizado (int) – 1 = Sí, 0 = No.
 *   - $tipo_empaque (string) – Tipos de empaque (ej. "B, C, F").
 *   - $empaque_otro_descripcion (string|null) – Descripción si se seleccionó "O" (Otros).
 *   - $motivo (string) – Motivo del descarte.
 *   - $fecha_registro (string) – Fecha y hora de registro formateada (ej. "dd/mm/YYYY HH:MM:SS").
 * 
 * - El controlador define el tamaño de papel (A4, portrait) y la orientación.
 *   En DesechosController::generarPdf() se usa:
 *   $options = new \Dompdf\Options();
 *   $options->set('isRemoteEnabled', true);
 *   $dompdf = new \Dompdf\Dompdf($options);
 *   $dompdf->loadHtml($html);
 *   $dompdf->setPaper('A4', 'portrait');
 *   $dompdf->render();
 *   $dompdf->stream('solicitud_' . $solicitud['codigo_solicitud'] . '.pdf', ['Attachment' => false]);
 * 
 * - El PDF se muestra en línea (Attachment = false) o puede descargarse (Attachment = true)
 *   según la configuración en el controlador.
 * 
 * Dependencias:
 * - Dompdf (librería PHP, no requiere assets externos).
 * - No utiliza Bootstrap ni JavaScript.
 * 
 * @package App\Views\desechos
 */
?>
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
        <h2>Solicitud de Desechos Biológicos</h2>
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
                    $empaqueMostrado = str_replace('F', 'CPC', $tipo_empaque);
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