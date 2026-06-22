<?php

namespace App\Models;

use CodeIgniter\Model;

class SolicitudDesechosModel extends Model
{
    protected $table      = 'solicitudes_desechos';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'ext_telefono',
        'tipos_desecho',
        'variantes_desecho',
        'esterilizado',
        'motivo',
        'estado',
        'peso_kg',
        'peso_l',
        'tipo_empaque',
        'empaque_otro_descripcion',
        'estado_solicitud',
        'editado'
    ];

    public function generarCodigoUnico(): string
    {
        $prefix = "SOL-" . date('Y');
        // Obtener el último código generado con ese prefijo
        $sql = "SELECT codigo_solicitud FROM solicitudes_desechos WHERE codigo_solicitud LIKE ? ORDER BY id DESC LIMIT 1";
        $row = $this->db->query($sql, [$prefix . '%'])->getRowArray();
        
        if ($row) {
            // Extraer el número de secuencia del último código
            $parts = explode('-', $row['codigo_solicitud']);
            $lastSeq = (int)end($parts);
            $secuencia = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $secuencia = '0001';
        }
        
        return $prefix . "-" . $secuencia;
    }

    public function insertarSolicitud(array $data): bool
    {
        $sql = "INSERT INTO solicitudes_desechos 
            (codigo_solicitud, usuario_id, ext_telefono, tipos_desecho, variantes_desecho, esterilizado, motivo, estado, peso_kg, peso_l, tipo_empaque, empaque_otro_descripcion, estado_solicitud) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
        return $this->db->query($sql, [
            $data['codigo_solicitud'],
            $data['usuario_id'],
            $data['ext_telefono'],
            $data['tipos_desecho'],
            $data['variantes_desecho'],
            $data['esterilizado'],
            $data['motivo'],
            $data['estado'],
            $data['peso_kg'] ?? 0.00,
            $data['peso_l'] ?? 0.00,
            $data['tipo_empaque'],
            $data['empaque_otro_descripcion'] ?? null,
            $data['estado_solicitud'] ?? 'Pendiente'
        ]);
    }

    private function armarCondicionesFiltro($filtros, &$values)
    {
        $where = ["1=1"];

        if (!empty($filtros['buscar'])) {
            $where[] = "(s.codigo_solicitud LIKE ? OR u.username LIKE ?)";
            $values[] = '%' . $filtros['buscar'] . '%';
            $values[] = '%' . $filtros['buscar'] . '%';
        }

        if (!empty($filtros['tipo_desecho'])) {
            $where[] = "s.tipos_desecho LIKE ?";
            $values[] = '%' . $filtros['tipo_desecho'] . '%';
        }

        if (!empty($filtros['estado_solicitud'])) {
            $where[] = "s.estado_solicitud = ?";
            $values[] = $filtros['estado_solicitud'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $where[] = "DATE(s.fecha_registro) >= ?";
            $values[] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $where[] = "DATE(s.fecha_registro) <= ?";
            $values[] = $filtros['fecha_hasta'];
        }

        return implode(" AND ", $where);
    }

    public function countSolicitudesFiltradas($filtros)
    {
        $values = [];
        $whereSql = $this->armarCondicionesFiltro($filtros, $values);
        
        $sql = "SELECT COUNT(s.id) as total 
                FROM solicitudes_desechos s
                LEFT JOIN usuarios u ON s.usuario_id = u.id
                WHERE $whereSql";
                
        $resultado = $this->db->query($sql, $values)->getRowArray();
        return $resultado['total'];
    }

    public function getSolicitudesFiltradas($filtros, $limit, $offset)
    {
        $values = [];
        $whereSql = $this->armarCondicionesFiltro($filtros, $values);
        
        $sql = "SELECT s.*, s.usuario_id, u.username, u.cedula,
                    d.nombre AS nombre_departamento, 
                    l.nombre AS nombre_laboratorio
                FROM solicitudes_desechos s
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

    public function getSolicitudes()
    {
        $sql = "SELECT s.*, u.username, u.cedula,
                    d.nombre AS nombre_departamento, 
                    l.nombre AS nombre_laboratorio
                FROM solicitudes_desechos s
                LEFT JOIN usuarios u ON s.usuario_id = u.id
                LEFT JOIN laboratorios l ON s.usuario_id = l.id
                LEFT JOIN departamentos d ON l.departamento_id = d.id
                ORDER BY s.id DESC";
        return $this->db->query($sql)->getResultArray();
    }

    public function actualizarEstado($id, $nuevoEstado)
    {
        $sql = "UPDATE solicitudes_desechos SET estado_solicitud = ? WHERE id = ?";
        return $this->db->query($sql, [$nuevoEstado, $id]);
    }
}