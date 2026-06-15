<?php

namespace App\Models;

use CodeIgniter\Model;

class SolicitudBioseguridadModel extends Model
{
    protected $table      = 'solicitudes_bioseguridad';
    protected $primaryKey = 'id';

    public function generarCodigoUnico(): string
    {
        $prefix = "BIO-" . date('Y');
        $sql = "SELECT COUNT(id) as total FROM solicitudes_bioseguridad WHERE codigo_solicitud LIKE ?";
        $row = $this->db->query($sql, [$prefix . '%'])->getRowArray();
        $secuencia = str_pad(($row['total'] + 1), 4, '0', STR_PAD_LEFT);
        return $prefix . "-" . $secuencia;
    }

    public function insertarSolicitud(array $data): bool
    {
        $sql = "INSERT INTO solicitudes_bioseguridad 
            (codigo_solicitud, usuario_id, ext_telefono, contenedores_pulso_cantidad, 
             bolsas_rojas_pequena, bolsas_rojas_mediana, bolsas_rojas_grande, 
             quien_retira, nombre_otra_persona, estado_solicitud, ruta_pdf) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
        return $this->db->query($sql, [
            $data['codigo_solicitud'],
            $data['usuario_id'],
            $data['ext_telefono'],
            $data['contenedores_pulso_cantidad'] ?? 0,
            $data['bolsas_rojas_pequena'] ?? 0,
            $data['bolsas_rojas_mediana'] ?? 0,
            $data['bolsas_rojas_grande'] ?? 0,
            $data['quien_retira'] ?? 'mi_persona',
            $data['nombre_otra_persona'] ?? null,
            $data['estado_solicitud'] ?? 'Pendiente',
            $data['ruta_pdf'] ?? null
        ]);
    }

    // Filtros y paginación (similar a SolicitudDesechosModel)
    private function armarCondicionesFiltro($filtros, &$values)
    {
        $where = ["1=1"];

        if (!empty($filtros['buscar'])) {
            $where[] = "(codigo_solicitud LIKE ? OR usuario_id IN (SELECT id FROM usuarios WHERE username LIKE ?))";
            $values[] = '%' . $filtros['buscar'] . '%';
            $values[] = '%' . $filtros['buscar'] . '%';
        }

        if (!empty($filtros['estado_solicitud'])) {
            $where[] = "estado_solicitud = ?";
            $values[] = $filtros['estado_solicitud'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $where[] = "DATE(fecha_registro) >= ?";
            $values[] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $where[] = "DATE(fecha_registro) <= ?";
            $values[] = $filtros['fecha_hasta'];
        }

        return implode(" AND ", $where);
    }

    public function countSolicitudesFiltradas($filtros)
    {
        $values = [];
        $whereSql = $this->armarCondicionesFiltro($filtros, $values);
        $sql = "SELECT COUNT(id) as total FROM {$this->table} WHERE $whereSql";
        $resultado = $this->db->query($sql, $values)->getRowArray();
        return $resultado['total'];
    }

    public function getSolicitudesFiltradas($filtros, $limit, $offset)
    {
        $values = [];
        $whereSql = $this->armarCondicionesFiltro($filtros, $values);
        $sql = "SELECT s.*, u.username, l.nombre AS nombre_laboratorio, d.nombre AS nombre_departamento
                FROM solicitudes_bioseguridad s
                LEFT JOIN usuarios u ON s.usuario_id = u.id
                LEFT JOIN laboratorios l ON u.laboratorio_id = l.id
                LEFT JOIN departamentos d ON l.departamento_id = d.id
                WHERE $whereSql
                ORDER BY s.id DESC
                LIMIT ? OFFSET ?";
        $values[] = (int)$limit;
        $values[] = (int)$offset;
        return $this->db->query($sql, $values)->getResultArray();
    }

    public function actualizarEstado($id, $nuevoEstado)
    {
        $sql = "UPDATE solicitudes_bioseguridad SET estado_solicitud = ? WHERE id = ?";
        return $this->db->query($sql, [$nuevoEstado, $id]);
    }
}