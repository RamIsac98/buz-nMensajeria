<?php
/**
 * Controlador del dashboard para el rol 'proteccion_integral'.
 * 
 * Muestra estadísticas de solicitudes de desechos entregados,
 * con gráficos trimestrales y tabla detallada por mes/día.
 * 
 * Hereda de BaseController para verificación de sesión y auditoría.
 * 
 * Dependencias:
 * - DashboardModel para consultas de datos estadísticos.
 */
namespace App\Controllers;

use App\Models\DashboardModel;

class DashboardController extends BaseController
{
        /**
     * Página principal del dashboard.
     * 
     * - Verifica sesión activa.
     * - Verifica que el usuario tenga rol 'proteccion_integral'; si no, redirige a
     *   /desechos/registroSolicitudes con mensaje de error.
     * - Obtiene años disponibles (solo con registros 'Entregado').
     * - Selecciona el año (vía GET 'anio') o toma el más reciente.
     * - Selecciona trimestre (vía GET 'trimestre'), 0 = todos.
     * - Obtiene datos del gráfico por trimestre para el año seleccionado.
     * - Si se selecciona un trimestre específico, adapta labels y values a un único punto.
     * - Obtiene datos diarios (con o sin filtro de trimestre) y los agrupa por mes.
     * - Calcula el total general sumando los valores del gráfico.
     * - Prepara datos para la vista: años disponibles, año seleccionado, trimestre,
     *   labels y values en JSON, total, y datos mensuales.
     * 
     * @return mixed Vista 'dashboard/index' con datos estadísticos o redirección.
     * 
     * @example
     * GET /dashboard?anio=2026&trimestre=1   (solo primer trimestre de 2026)
     * GET /dashboard?anio=2025               (todos los trimestres de 2025)
     */
    public function index()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        
        if (session()->get('rol') !== 'proteccion_integral') return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'Acceso no autorizado.');
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