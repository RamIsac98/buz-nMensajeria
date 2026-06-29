<?php

namespace App\Controllers;

use App\Models\DashboardModel;

class DashboardController extends BaseController
{
    public function index()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        if (session()->get('rol') !== 'proteccion_integral') return redirect()->to(base_url('interfaz_usuario_inicial'))->with('error', 'Acceso no autorizado.');

        $dashboardModel = new DashboardModel();

        $aniosDisponibles = $dashboardModel->getAniosDisponibles();
        $anioSeleccionado = $this->request->getGet('anio') ?? max($aniosDisponibles);
        $trimestreSeleccionado = (int)($this->request->getGet('trimestre') ?? 0);

        // Gráfico
        $trimestres = $dashboardModel->getDatosGrafico($anioSeleccionado);
        $labels = ['Q1', 'Q2', 'Q3', 'Q4'];
        $values = array_values($trimestres);

        if ($trimestreSeleccionado > 0 && $trimestreSeleccionado <= 4) [$labels, $values] = [['Q' . $trimestreSeleccionado], [$trimestres[$trimestreSeleccionado]]];

        // Tabla detallada
        $datosDiarios = $dashboardModel->getDatosDiarios($anioSeleccionado, $trimestreSeleccionado ?: null);
        $meses = $dashboardModel->agruparPorMes($datosDiarios);
        $totalGeneral = array_sum($values);

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