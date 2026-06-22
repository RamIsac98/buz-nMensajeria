<?php

namespace App\Models;

use CodeIgniter\Model;

class SolicitudBioseguridadModel extends Model
{
    protected $table      = 'solicitudes_bioseguridad';
    protected $primaryKey = 'id';

    protected $allowedFields = [
    'ext_telefono',
    'contenedores_pulso_cantidad',
    'bolsas_rojas_pequena',
    'bolsas_rojas_mediana',
    'bolsas_rojas_grande',
    'quien_retira',
    'nombre_otra_persona',
    'estado_solicitud',
    'editado'
    ];

    public function generarCodigoUnico(): string
    {
        $prefix = "BIO-" . date('Y');
        // Obtener el último código generado con ese prefijo
        $sql = "SELECT codigo_solicitud FROM solicitudes_bioseguridad WHERE codigo_solicitud LIKE ? ORDER BY id DESC LIMIT 1";
        $row = $this->db->query($sql, [$prefix . '%'])->getRowArray();
        
        if ($row) {
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
        $sql = "INSERT INTO solicitudes_bioseguridad 
            (codigo_solicitud, usuario_id, ext_telefono, contenedores_pulso_cantidad, 
             bolsas_rojas_pequena, bolsas_rojas_mediana, bolsas_rojas_grande, 
             quien_retira, nombre_otra_persona, estado_solicitud) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
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
                FROM solicitudes_bioseguridad s
                LEFT JOIN usuarios u ON s.usuario_id = u.id
                WHERE $whereSql";
        $resultado = $this->db->query($sql, $values)->getRowArray();
        return $resultado['total'];
    }

    // ✅ CORREGIDO: se agregó JOIN y se eliminó ruta_pdf
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