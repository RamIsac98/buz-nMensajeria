<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index()
    {
        if (!$this->estaLogueado()) {
            return redirect()->to(base_url('login'));
        }

        // Solo acceso para protección integral
        if (session()->get('rol') !== 'proteccion_integral') {
            return redirect()->to(base_url('interfaz_usuario_inicial'))
                             ->with('error', 'Acceso no autorizado.');
        }

        $db = \Config\Database::connect();

        // ============================================================
        // 1. OBTENER AÑOS DISPONIBLES (solo de solicitudes aprobadas)
        // ============================================================
        $sqlAnios = "SELECT DISTINCT YEAR(fecha_registro) as anio 
                     FROM solicitudes_desechos 
                     WHERE estado_solicitud = 'Entregado'
                     ORDER BY anio DESC";
        $queryAnios = $db->query($sqlAnios);
        $anios = $queryAnios->getResultArray();
        $aniosDisponibles = array_column($anios, 'anio');

        if (empty($aniosDisponibles)) {
            $aniosDisponibles = [date('Y')];
        }

        $anioSeleccionado = $this->request->getGet('anio') ?? max($aniosDisponibles);
        $trimestreSeleccionado = (int)($this->request->getGet('trimestre') ?? 0);

        // ============================================================
        // 2. DATOS PARA EL GRÁFICO (TRIMESTRAL)
        // ============================================================
        $sqlGrafico = "SELECT 
                          QUARTER(fecha_registro) as trimestre,
                          SUM(peso_kg + IFNULL(peso_l, 0)) as total_kg
                       FROM solicitudes_desechos
                       WHERE YEAR(fecha_registro) = ?
                         AND estado_solicitud = 'Entregado'
                       GROUP BY QUARTER(fecha_registro)
                       ORDER BY trimestre ASC";
        $queryGrafico = $db->query($sqlGrafico, [$anioSeleccionado]);
        $datosGrafico = $queryGrafico->getResultArray();

        $trimestres = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        foreach ($datosGrafico as $row) {
            $trimestres[$row['trimestre']] = (float)$row['total_kg'];
        }

        $labels = ['Q1', 'Q2', 'Q3', 'Q4'];
        $values = array_values($trimestres);

        if ($trimestreSeleccionado > 0 && $trimestreSeleccionado <= 4) {
            $labels = ['Q' . $trimestreSeleccionado];
            $values = [$trimestres[$trimestreSeleccionado]];
        }

        // ============================================================
        // 3. DATOS PARA LA TABLA DETALLADA (DIARIA POR MES)
        // ============================================================
        $condicionTrimestre = '';
        $params = [$anioSeleccionado];
        if ($trimestreSeleccionado > 0 && $trimestreSeleccionado <= 4) {
            $condicionTrimestre = " AND QUARTER(fecha_registro) = ?";
            $params[] = $trimestreSeleccionado;
        }

        $sqlTabla = "SELECT 
                        DATE(fecha_registro) as fecha,
                        COUNT(*) as cantidad,
                        SUM(peso_kg + IFNULL(peso_l, 0)) as total_kg
                     FROM solicitudes_desechos
                     WHERE YEAR(fecha_registro) = ?
                       AND estado_solicitud = 'Entregado'
                       $condicionTrimestre
                     GROUP BY DATE(fecha_registro)
                     ORDER BY fecha ASC";
        $queryTabla = $db->query($sqlTabla, $params);
        $datosTabla = $queryTabla->getResultArray();

        // Agrupar por mes
        $meses = [];
        foreach ($datosTabla as $row) {
            $mesNum = date('m', strtotime($row['fecha']));
            $dia = date('d', strtotime($row['fecha']));
            $meses[$mesNum][] = [
                'dia' => $dia,
                'fecha' => $row['fecha'],
                'total_kg' => $row['total_kg'],
                'cantidad' => (int)$row['cantidad'],
            ];
        }

        // Total acumulado del período
        $totalGeneral = array_sum($values);

        // ============================================================
        // 4. PASAR DATOS A LA VISTA
        // ============================================================
        $data = [
            'anios_disponibles'      => $aniosDisponibles,
            'anio_seleccionado'      => $anioSeleccionado,
            'trimestre_seleccionado' => $trimestreSeleccionado,
            'labels'                 => json_encode($labels),
            'values'                 => json_encode($values),
            'total_trimestres'       => $totalGeneral,
            'meses'                  => $meses,
        ];

        return view('dashboard/index', $data);
    }
}