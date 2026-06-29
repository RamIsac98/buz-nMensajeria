<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $table = 'solicitudes_desechos';

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

    //valores de cada columna
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