<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Historial de Solicitudes de Desechos</title>
        <link rel="stylesheet" href="<?= base_url('bootstrap5/css/bootstrap.min.css') ?>">
        <style>
            :root { --azul-oscuro: rgba(28, 70, 110, 0.9); --azul-claro: #2073AF; }
            .thead-custom { background-color: var(--azul-oscuro); color: white; }
            .badge-solido { background-color: #6c757d; }
            .badge-liquido { background-color: var(--azul-claro); }
        </style>
    </head>
    <body>

        <div class="container-fluid my-5 px-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-dark font-weight-bold">Historial de Solicitudes Procesadas</h2>
                <a href="<?= base_url('desechos/crear') ?>" class="btn text-white px-4" style="background-color: var(--azul-claro);">+ Nueva Solicitud</a>
            </div>

            <?php if (session()->getFlashdata('success')) : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center">
                            <thead class="thead-custom">
                                <tr>
                                    <th>Código</th>
                                    <th>Fecha</th>
                                    <th>Usuario</th>
                                    <th>Ubicación</th>
                                    <th>Ext.</th>
                                    <th>Tipo Desecho</th>
                                    <th>Estado/Peso</th>
                                    <th>Esterilizado</th>
                                    <th>Empaque</th>
                                    <th>Motivo</th>
                                    <th>Documento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($solicitudes_desechos)) : ?>
                                    <?php foreach ($solicitudes_desechos as $sol) : ?>
                                        <tr>
                                            <td class="font-weight-bold text-primary"><?= esc($sol['codigo_solicitud']) ?></td>
                                            <td class="small text-secondary"><?= esc($sol['fecha_registro']) ?></td>
                                            <td><?= esc($sol['username']) ?></td>
                                            <td class="small"><?= esc($sol['nombre_departamento']) ?> / <span class="text-muted"><?= esc($sol['nombre_laboratorio']) ?></span></td>
                                            <td><span class="badge bg-light text-dark border">EXT-<?= esc($sol['ext_telefono']) ?></span></td>
                                            <td>
                                                <span class="badge bg-dark"><?= esc($sol['tipos_desecho']) ?></span>
                                                <div class="text-muted" style="font-size:0.75rem; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= esc($sol['variantes_desecho']) ?>">
                                                    <?= esc($sol['variantes_desecho']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?= strpos($sol['estado'], 'Liquido') !== false ? 'badge-liquido' : 'badge-solido' ?>"><?= esc($sol['estado']) ?></span>
                                                <div class="small font-weight-bold text-muted">
                                                    <?= $sol['peso_kg'] > 0 ? $sol['peso_kg'] . ' Kg ' : '' ?>
                                                    <?= $sol['peso_l'] > 0 ? $sol['peso_l'] . ' L' : '' ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?= $sol['esterilizado'] == 1 ? 'bg-success' : 'bg-danger' ?>"><?= $sol['esterilizado'] == 1 ? 'Sí' : 'No' ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-dark">Tipo <?= esc($sol['tipo_empaque']) ?></span>
                                                <?php if($sol['empaque_otro_descripcion']): ?>
                                                    <div class="small text-muted text-italic">"<?= esc($sol['empaque_otro_descripcion']) ?>"</div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-start small" style="max-width: 250px;"><?= esc($sol['motivo']) ?></td>
                                            
                                            <td>
                                                <?php if (!empty($sol['ruta_pdf'])): ?>
                                                    <a href="<?= base_url('desechos/verPdf/' . urlencode(basename($sol['ruta_pdf']))) ?>" 
                                                    target="_blank" class="btn btn-sm btn-danger">
                                                    📄 PDF
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">Sin PDF</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="11" class="text-muted py-5">No existen registros de solicitudes biológicas actualmente en el sistema.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script src="<?= base_url('bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>
    </body>
</html>