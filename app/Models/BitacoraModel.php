<?php

namespace App\Models;

use CodeIgniter\Model;

class BitacoraModel extends Model
{
    protected $table = 'bitacora';
    protected $primaryKey = 'id';


    public function insertRegistro($datos)
    {
        $sql = "INSERT INTO bitacora (usuario_id, tipo_solicitud, registro, ip, accion) VALUES (?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $datos['usuario_id'] ?? null,
            $datos['tipo_solicitud'],
            $datos['registro'],
            $datos['ip'],
            $datos['accion']
        ]);
        
        return $this->db->insertID();
    }

    private function armarCondicionesFiltro($filtros, &$values)
    {
        $where = ["1=1"];

        if (!empty($filtros['buscar'])) {
            $where[] = "(u.username LIKE ? OR b.ip LIKE ? OR b.registro LIKE ? OR b.accion LIKE ?)";
            $term = '%' . $filtros['buscar'] . '%';
            array_push($values, $term, $term, $term, $term);
        }

        if (!empty($filtros['tipo'])) {
            $where[] = "b.tipo_solicitud = ?";
            $values[] = $filtros['tipo'];
        }

        if (!empty($filtros['desde'])) {
            $where[] = "b.fecha >= ?";
            $values[] = $filtros['desde'] . ' 00:00:00';
        }

        if (!empty($filtros['hasta'])) {
            $where[] = "b.fecha <= ?";
            $values[] = $filtros['hasta'] . ' 23:59:59';
        }

        return implode(" AND ", $where);
    }

    public function countBitacora($filtros)
    {
        $values = [];
        $whereSql = $this->armarCondicionesFiltro($filtros, $values);
        
        $sql = "SELECT COUNT(b.id) as total 
                FROM bitacora b 
                LEFT JOIN usuarios u ON u.id = b.usuario_id 
                WHERE $whereSql";
                
        $resultado = $this->db->query($sql, $values)->getRowArray();
        return $resultado['total'];
    }

    public function getBitacoraFiltrada($filtros, $limit, $offset)
    {
        $values = [];
        $whereSql = $this->armarCondicionesFiltro($filtros, $values);
        
        $sql = "SELECT b.*, u.username 
                FROM bitacora b 
                LEFT JOIN usuarios u ON u.id = b.usuario_id 
                WHERE $whereSql 
                ORDER BY b.id DESC 
                LIMIT ? OFFSET ?";
                
        $values[] = (int)$limit;
        $values[] = (int)$offset;

        return $this->db->query($sql, $values)->getResultArray();
    }
}