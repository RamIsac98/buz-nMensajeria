<?php
/**
 * Modelo para obtener estadísticas del dashboard.
 * 
 * Trabaja con la tabla 'solicitudes_desechos', específicamente
 * con registros cuyo estado_solicitud = 'Entregado'.
 * Proporciona datos para gráficos por años, trimestres y días.
 * 
 * Dependencias: Usa query builder nativo de CodeIgniter.
 */

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $table = 'solicitudes_desechos';

        /**
     * Obtiene los años disponibles con registros entregados.
     * 
     * @return array Lista de años (enteros), ordenados descendente.
     *               Si no hay registros, devuelve [año actual].
     * 
     * @example
     * $model = new DashboardModel();
     * $anios = $model->getAniosDisponibles(); // [2026, 2025, 2024]
     */

    public function getAniosDisponibles(): array
    {
        $sql = "SELECT DISTINCT YEAR(fecha_registro) as anio 
                FROM solicitudes_desechos 
                WHERE estado_solicitud = 'Entregado'
                ORDER BY anio DESC";
        $query = $this->db->query($sql);
        $result = $query->getResultArray();
        $anios = array_column($result, 'anio');
        return empty($anios) ? [date('Y')] : $anios;
    }

        /**
     * Obtiene el peso total (kg) agrupado por trimestre para un año específico.
     * 
     * Suma peso_kg + peso_l (si existe, con IFNULL) para registros 'Entregado'.
     * Devuelve siempre un arreglo con índices 1..4, aunque no haya datos (valor 0).
     * 
     * @param int $anio Año a consultar.
     * @return array Arreglo asociativo con clave trimestre (1-4) y valor total en kg (float).
     * 
     * @example
     * $data = $model->getDatosGrafico(2026);
     * // Resultado: [1 => 1250.5, 2 => 980.0, 3 => 0, 4 => 0]
     */
    public function getDatosGrafico(int $anio): array
    {
        $sql = "SELECT QUARTER(fecha_registro) as trimestre,
                       SUM(peso_kg + IFNULL(peso_l, 0)) as total_kg
                FROM solicitudes_desechos
                WHERE YEAR(fecha_registro) = ?
                  AND estado_solicitud = 'Entregado'
                GROUP BY QUARTER(fecha_registro)
                ORDER BY trimestre ASC";
        $query = $this->db->query($sql, [$anio]);
        $result = $query->getResultArray();

        $trimestres = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        foreach ($result as $row) {
            $trimestres[$row['trimestre']] = (float)$row['total_kg'];
        }
        return $trimestres;
    }

        /**
     * Obtiene datos diarios (cantidad de solicitudes y peso total) para un año,
     * opcionalmente filtrado por trimestre.
     * 
     * @param int $anio Año a consultar.
     * @param int|null $trimestre Trimestre (1-4) o null para todos.
     * @return array Arreglo de registros con campos: fecha, cantidad, total_kg.
     *               Ordenados por fecha ascendente.
     * 
     * @example
     * // Todos los días del año 2026
     * $diario = $model->getDatosDiarios(2026);
     * // Solo trimestre 1
     * $diarioQ1 = $model->getDatosDiarios(2026, 1);
     */
    public function getDatosDiarios(int $anio, ?int $trimestre = null): array
    {
        $params = [$anio];
        $condicionTrimestre = '';
        if ($trimestre !== null && $trimestre >= 1 && $trimestre <= 4) {
            $condicionTrimestre = " AND QUARTER(fecha_registro) = ?";
            $params[] = $trimestre;
        }

        $sql = "SELECT DATE(fecha_registro) as fecha,
                       COUNT(*) as cantidad,
                       SUM(peso_kg + IFNULL(peso_l, 0)) as total_kg
                FROM solicitudes_desechos
                WHERE YEAR(fecha_registro) = ?
                  AND estado_solicitud = 'Entregado'
                  $condicionTrimestre
                GROUP BY DATE(fecha_registro)
                ORDER BY fecha ASC";
        $query = $this->db->query($sql, $params);
        return $query->getResultArray();
    }

        /**
     * Agrupa los datos diarios por mes.
     * 
     * Toma el resultado de getDatosDiarios y lo reorganiza en un arreglo
     * indexado por número de mes (01-12), cada uno con una lista de días.
     * Cada día incluye: dia (número), fecha (Y-m-d), total_kg (float), cantidad (int).
     * 
     * @param array $datosDiarios Arreglo de registros diarios (de getDatosDiarios).
     * @return array Arreglo multidimensional: mes => [ [dia, fecha, total_kg, cantidad], ... ].
     * 
     * @example
     * $diario = $model->getDatosDiarios(2026);
     * $porMes = $model->agruparPorMes($diario);
     * // $porMes['01'] contiene los días de enero, cada uno con su total_kg y cantidad.
     */
    public function agruparPorMes(array $datosDiarios): array
    {
        $meses = [];
        foreach ($datosDiarios as $row) {
            $fecha = $row['fecha'];
            $mesNum = date('m', strtotime($fecha));
            $dia = date('d', strtotime($fecha));
            $meses[$mesNum][] = [
                'dia'      => $dia,
                'fecha'    => $fecha,
                'total_kg' => $row['total_kg'],
                'cantidad' => (int)$row['cantidad']
            ];
        }
        return $meses;
    }
}