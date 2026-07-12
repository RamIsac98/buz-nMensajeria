<?php
/**
 * Vista: Plantilla PDF para el reporte de usuarios.
 * 
 * Esta vista se utiliza exclusivamente para generar el reporte en PDF
 * del listado de usuarios, con los filtros aplicados y rango de páginas
 * seleccionado.
 * 
 * No extiende el layout base y no utiliza assets externos (CSS integrado).
 * 
 * Conexiones con el controlador:
 * - Es invocada por Usuarios::generarPdfUsuarios() (ruta '/usuarios/generarPdfUsuarios')
 *   La cual recibe los parámetros GET: buscar, rol, estado, pagina_inicio, pagina_fin.
 *   El controlador obtiene los registros correspondientes usando UsuarioModel::getUsuariosFiltrados()
 *   con el límite calculado según el rango de páginas.
 * 
 * - Recibe del controlador la variable $usuarios (array) – lista de usuarios a incluir en el PDF.
 * 
 * - El controlador define el tamaño de papel (A4, portrait), y genera el PDF con Dompdf.
 *   El PDF se descarga con nombre "Reporte_Usuarios_Paginas_{inicio}_al_{fin}.pdf" (Attachment = true).
 * 
 * - La vista muestra una tabla con: ID, Nombre Completo, Usuario, Cédula, Rol, Estado.
 * 
 * Dependencias:
 * - Dompdf (librería PHP, no requiere assets externos).
 * - No utiliza Bootstrap ni JavaScript.
 * - Estilos CSS integrados para el formato del PDF.
 * 
 * @package App\Views\usuarios
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Usuarios</title>
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
        <h2>Reporte de Usuarios</h2>
        <p>Fecha de generación: <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <table class="table-pdf">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Usuario</th>
                <th>Cédula</th>
                <th>Rol</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?= $usuario['id'] ?></td>
                <td><?= esc($usuario['nombre']) ?> <?= esc($usuario['apellido']) ?></td>
                <td><?= esc($usuario['username']) ?></td>
                <td><?= esc($usuario['cedula']) ?></td>
                <td><?= esc($usuario['rol']) ?></td>
                <td><?= $usuario['status'] == 1 ? 'Activo' : 'Inactivo' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        Reporte Automatizado de Control de Usuarios.
    </div>
</body>
</html>