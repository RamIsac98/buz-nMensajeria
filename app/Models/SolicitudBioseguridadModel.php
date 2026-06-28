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
        
        // Obtener el último código generado con ese prefijo (ordenado por ID)
        $sql = "SELECT codigo_solicitud FROM solicitudes_desechos 
                WHERE codigo_solicitud LIKE ? 
                ORDER BY id DESC LIMIT 1";
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
        $where = [];

        $configuracionFiltros = [
            'buscar'           => ['d.nombre LIKE ?', fn($v) => "%{$v}%"],
            'estado_solicitud' => ['s.estado_solicitud = ?', fn($v) => $v],
            'fecha_desde'      => ['s.fecha_registro >= ?', fn($v) => $v . ' 00:00:00'],
            'fecha_hasta'      => ['s.fecha_registro <= ?', fn($v) => $v . ' 23:59:59'],
        ];

        foreach ($configuracionFiltros as $campo => [$sql, $transformer]) {
            if (!empty($filtros[$campo])) {
                $where[] = $sql;
                $values[] = $transformer($filtros[$campo]);
            }
        }

        return empty($where) ? "1=1" : implode(" AND ", $where);
    }


    public function countSolicitudesFiltradas($filtros)
    {
        $values = [];
        $whereSql = $this->armarCondicionesFiltro($filtros, $values);
        
        $sql = "SELECT COUNT(s.id) as total 
                FROM solicitudes_bioseguridad s
                LEFT JOIN usuarios u ON s.usuario_id = u.id
                LEFT JOIN laboratorios l ON u.laboratorio_id = l.id
                LEFT JOIN departamentos d ON l.departamento_id = d.id
                WHERE $whereSql";
                
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