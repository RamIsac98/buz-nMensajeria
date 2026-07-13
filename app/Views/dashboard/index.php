<?php
/**
 * Vista: Dashboard de peso trimestral de desechos biológicos.
 * 
 * Muestra un gráfico de barras con el peso total de desechos aprobados (estado 'Entregado')
 * agrupado por trimestre, con filtros por año y trimestre, y una tabla detallada por día/mes.
 * 
 * Conexiones con el controlador:
 * - Es servida por DashboardController::index() (ruta '/dashboard').
 * - Datos recibidos del controlador:
 *   - $anios_disponibles (array) – años con registros.
 *   - $anio_seleccionado (int) – año actualmente seleccionado.
 *   - $trimestre_seleccionado (int) – trimestre filtrado (0 = todos).
 *   - $labels (json) – etiquetas para el gráfico (ej. ["Q1","Q2","Q3","Q4"]).
 *   - $values (json) – valores numéricos para el gráfico.
 *   - $total_trimestres (float) – suma total de kg del período filtrado.
 *   - $meses (array) – datos diarios agrupados por mes para la tabla detallada.
 * 
 * - Filtros (año y trimestre):
 *   - Los selects tienen valores que, al hacer clic en "Actualizar", redirigen a
 *     DashboardController::index() con parámetros GET: ?anio=YYYY&trimestre=N.
 *   - La URL se construye en JavaScript: window.location.href = base_url('dashboard') + '?anio=' + anio + '&trimestre=' + trimestre.
 * 
 * - Mensajes flash:
 *   - Se muestran via SweetAlert2 (success/error) generados por el controlador (por ejemplo, al redirigir desde otras acciones).
 * 
 * Dependencias:
 * - Layout base (layouts/base) con Bootstrap y estilos comunes.
 * - Chart.js (vía CDN, cargado probablemente en el layout) para el gráfico.
 * - SweetAlert2 para mensajes flash.
 * 
 * @package App\Views\dashboard
 */
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Peso trimestral de Desechos Biológicos<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .filter-bar {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    .card-chart {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .total-box {
        background: linear-gradient(135deg, #2073AF, #155d8a);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        margin-bottom: 1.5rem;
    }
    .total-box h3 {
        margin: 0;
        font-weight: 300;
        font-size: 1.1rem;
    }
    .total-box .number {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0.2rem 0 0.5rem 0;
    }
    .total-box small {
        opacity: 0.8;
        font-size: 0.8rem;
    }
    .table-detail {
        font-size: 0.9rem;
    }
    .table-detail th {
        background-color: var(--azul-oscuro);
        color: white;
    }
    .table-detail td, .table-detail th {
        vertical-align: middle;
    }
    .mes-header {
        background-color: #e9ecef;
        font-weight: 600;
        cursor: default;
    }
    .mes-header td {
        padding: 6px 12px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <h2 class="main-title">Peso Trimestral de Desechos Biológicos</h2>

    <!-- Total acumulado (datos del controlador) -->
    <div class="row">
        <div class="col-md-4">
            <div class="total-box">
                <h3>Total <?= $trimestre_seleccionado ? 'Q' . $trimestre_seleccionado : 'Anual' ?></h3>
                <div class="number"><?= number_format($total_trimestres, 2) ?> <small>kg</small></div>
                <small>Acumulado de desechos aprobados (Retirado)</small>
            </div>
        </div>
    </div>

    <!-- Filtros: invocan a DashboardController::index() con parámetros GET -->
    <div class="filter-bar d-flex flex-wrap align-items-center gap-3">
        <div>
            <label class="form-label fw-bold mb-0 me-2">Año:</label>
            <select id="selectAnio" class="form-select form-select-sm d-inline-block" style="width: auto;">
                <?php foreach ($anios_disponibles as $anio): ?>
                    <option value="<?= $anio ?>" <?= $anio == $anio_seleccionado ? 'selected' : '' ?>><?= $anio ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label fw-bold mb-0 me-2">Trimestre:</label>
            <select id="selectTrimestre" class="form-select form-select-sm d-inline-block" style="width: auto;">
                <option value="0" <?= $trimestre_seleccionado == 0 ? 'selected' : '' ?>>Todos</option>
                <option value="1" <?= $trimestre_seleccionado == 1 ? 'selected' : '' ?>>Q1 (Ene–Mar)</option>
                <option value="2" <?= $trimestre_seleccionado == 2 ? 'selected' : '' ?>>Q2 (Abr–Jun)</option>
                <option value="3" <?= $trimestre_seleccionado == 3 ? 'selected' : '' ?>>Q3 (Jul–Sep)</option>
                <option value="4" <?= $trimestre_seleccionado == 4 ? 'selected' : '' ?>>Q4 (Oct–Dic)</option>
            </select>
        </div>
        <button id="btnActualizar" class="btn btn-primary btn-sm">Actualizar</button>
    </div>

    <!-- Gráfico -->
    <div class="card card-chart">
        <div class="card-body">
            <canvas id="chartDesechos" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Tabla detallada con meses en español -->
    <div class="card card-chart mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Detalle diario de desechos aprobados</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-detail table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Mes</th>
                            <th>Día</th>
                            <th>Fecha</th>
                            <th class="text-center">N° Registros</th>
                            <th class="text-end">Peso (kg)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($meses)): ?>
                            <?php 
                            // Array con nombres de meses en español
                            $meses_es = [
                                '01' => 'Enero',
                                '02' => 'Febrero',
                                '03' => 'Marzo',
                                '04' => 'Abril',
                                '05' => 'Mayo',
                                '06' => 'Junio',
                                '07' => 'Julio',
                                '08' => 'Agosto',
                                '09' => 'Septiembre',
                                '10' => 'Octubre',
                                '11' => 'Noviembre',
                                '12' => 'Diciembre'
                            ];
                            ?>
                            <?php foreach ($meses as $mesNum => $dias): ?>
                                <?php 
                                $mesNumero = str_pad($mesNum, 2, '0', STR_PAD_LEFT);
                                $nombreMes = $meses_es[$mesNumero] ?? $mesNumero;
                                ?>
                                <tr class="mes-header">
                                    <td colspan="5">
                                        <strong><?= $nombreMes ?> (<?= $mesNum ?>)</strong>
                                        <span class="badge bg-secondary float-end">
                                            Total: <?= number_format(array_sum(array_column($dias, 'total_kg')), 2) ?> kg
                                        </span>
                                    </td>
                                </tr>
                                <?php foreach ($dias as $dia): ?>
                                    <tr>
                                        <td></td>
                                        <td><?= $dia['dia'] ?></td>
                                        <td><?= date('d/m/Y', strtotime($dia['fecha'])) ?></td>
                                        <td class="text-center"><span class="text-dark"><?= $dia['cantidad'] ?></span></td>
                                        <td class="text-end"><?= number_format($dia['total_kg'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            <tr class="bg-light fw-bold">
                                <td colspan="4" class="text-end">TOTAL GENERAL</td>
                                <td class="text-end"><?= number_format($total_trimestres, 2) ?></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    No hay solicitudes aprobadas en este período.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- SweetAlert2 (por seguridad, aunque el layout ya lo incluya) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Chart.js (se carga explícitamente por si el layout no lo incluye) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // =============================================
    // MENSAJES FLASH CON SWEETALERT2 (success/error)
    // =============================================
    document.addEventListener('DOMContentLoaded', function() {
        <?php if(session()->getFlashdata('success')): ?>
            console.log('Mensaje success recibido: <?= esc(session()->getFlashdata('success')) ?>');
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '<?= esc(session()->getFlashdata('success')) ?>',
                confirmButtonColor: '#2073AF',
                timer: 4000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        <?php endif; ?>

        <?php if(session()->getFlashdata('error')): ?>
            console.log('Mensaje error recibido: <?= esc(session()->getFlashdata('error')) ?>');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?= esc(session()->getFlashdata('error')) ?>',
                confirmButtonColor: '#d33',
                timer: 5000,
                timerProgressBar: true,
                showConfirmButton: true
            });
        <?php endif; ?>
    });

    // =============================================
    // LÓGICA DEL GRÁFICO Y FILTROS
    // =============================================
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('chartDesechos').getContext('2d');
        let chart;

        // Los datos vienen del controlador ya como JSON (ej. ["Q1","Q2","Q3","Q4"] o [0,0,0,0])
        // Se imprimen directamente sin volver a codificar.
        const labels = <?= $labels ?>;
        const values = <?= $values ?>;

        // Función para renderizar el gráfico
        function renderChart(labels, values) {
            // Validar que sean arrays y tengan datos
            if (!Array.isArray(labels) || !Array.isArray(values) || labels.length === 0) {
                // Mostrar mensaje en el canvas
                ctx.font = '16px sans-serif';
                ctx.fillStyle = '#999';
                ctx.textAlign = 'center';
                ctx.fillText('No hay datos para mostrar', ctx.canvas.width/2, ctx.canvas.height/2);
                return;
            }

            // Si el chart ya existe, destruirlo para recrearlo
            if (chart) chart.destroy();

            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total kg',
                        data: values,
                        backgroundColor: 'rgba(32, 115, 175, 0.7)',
                        borderColor: 'rgba(32, 115, 175, 1)',
                        borderWidth: 1,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' kg';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Kilogramos'
                            }
                        }
                    }
                }
            });
        }

        // Render inicial
        renderChart(labels, values);

        // Evento para actualizar filtros
        document.getElementById('btnActualizar').addEventListener('click', function() {
            const anio = document.getElementById('selectAnio').value;
            const trimestre = document.getElementById('selectTrimestre').value;
            window.location.href = '<?= base_url('dashboard') ?>?anio=' + anio + '&trimestre=' + trimestre;
        });
    });
</script>
<?= $this->endSection() ?>