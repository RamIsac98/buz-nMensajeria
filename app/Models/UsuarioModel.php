<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    
    public function getRolesDisponibles()
    {
        $sql = "SELECT DISTINCT rol FROM usuarios";
        $query = $this->db->query($sql);
        $roles = [];
        foreach ($query->getResultArray() as $row) {
            if (!empty($row['rol'])) {
                $roles[] = $row['rol'];
            }
        }
        return $roles;
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM usuarios WHERE id = ? LIMIT 1";
        return $this->db->query($sql, [$id])->getRowArray();
    }

    public function findByUsername($username)
    {
        $sql = "SELECT * FROM usuarios WHERE username = ? LIMIT 1";
        return $this->db->query($sql, [$username])->getRowArray();
    }

    public function findByCedula($cedula)
    {
        $sql = "SELECT * FROM usuarios WHERE cedula = ? LIMIT 1";
        return $this->db->query($sql, [$cedula])->getRowArray();
    }

    
    public function existeCedula(string $cedula): bool
    {
        $sql = "SELECT 1 FROM usuarios WHERE cedula = ? LIMIT 1";
        $resultado = $this->db->query($sql, [$cedula])->getRowArray();
        return !empty($resultado);
    }


    public function existeCedulaExcluyendoId(string $cedula, $id): bool
    {
        $sql = "SELECT 1 FROM usuarios WHERE cedula = ? AND id != ? LIMIT 1";
        $resultado = $this->db->query($sql, [$cedula, $id])->getRowArray();
        return !empty($resultado);
    }

    public function insertUsuario($datos)
    {
        // SQL corregido eliminando id_departamento
        $sql = "INSERT INTO usuarios (username, password, rol, cedula, laboratorio_id, status) VALUES (?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $datos['username'], 
            $datos['password'], 
            $datos['rol'], 
            $datos['cedula'], 
            $datos['laboratorio_id'] ?? null,
            $datos['status'] ?? 1
        ]);
        return $this->db->insertID();
    }

    public function updateUsuario($id, $datos)
    {
        if (empty($datos)) return false;
        
        $setClause = [];
        $values = [];
        foreach ($datos as $columna => $valor) {
            $setClause[] = "$columna = ?";
            $values[] = $valor;
        }
        $values[] = $id; // ID para el WHERE

        $sql = "UPDATE usuarios SET " . implode(', ', $setClause) . " WHERE id = ?";
        return $this->db->query($sql, $values);
    }

    public function deleteUsuario($id)
    {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    private function armarCondicionesFiltro($filtros, &$values)
    {
        $where = ["1=1"]; // Base para concatenar AND

        if (!empty($filtros['buscar'])) {
            $where[] = "(username LIKE ? OR cedula LIKE ?)";
            $values[] = '%' . $filtros['buscar'] . '%';
            $values[] = '%' . $filtros['buscar'] . '%';
        }

        if (!empty($filtros['rol'])) {
            $where[] = "rol LIKE ?";
            $values[] = '%' . $filtros['rol'] . '%';
        }

        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $where[] = "status = ?";
            $values[] = $filtros['estado'];
        }

        return implode(" AND ", $where);
    }

    public function countUsuarios($filtros)
    {
        $values = [];
        $whereSql = $this->armarCondicionesFiltro($filtros, $values);
        
        $sql = "SELECT COUNT(id) as total FROM usuarios WHERE $whereSql";
        $resultado = $this->db->query($sql, $values)->getRowArray();
        
        return $resultado['total'];
    }

    public function getUsuariosFiltrados($filtros, $limit, $offset)
    {
        $values = [];
        $whereSql = $this->armarCondicionesFiltro($filtros, $values);
        
        $sql = "SELECT * FROM usuarios WHERE $whereSql ORDER BY id DESC LIMIT ? OFFSET ?";
        
        
        $values[] = (int)$limit;
        $values[] = (int)$offset;

        return $this->db->query($sql, $values)->getResultArray();
    }

    public function getReporteGeneral($departamento_id): array
    {
        $sql = "SELECT 
                    d.nombre AS nombre_departamento,
                    l.nombre AS nombre_laboratorio,
                    u.username AS nombre_usuario,
                    u.cedula AS cedula_usuario,
                    u.rol AS rol_usuario,
                    u.status AS estado_usuario
                FROM departamentos d
                LEFT JOIN laboratorios l ON l.departamento_id = d.id
                LEFT JOIN usuarios u ON u.laboratorio_id = l.id";
        
        $params = [];
        if ($departamento_id !== 'todos' && !empty($departamento_id)) {
            $sql .= " WHERE d.id = ?";
            $params[] = (int)$departamento_id;
        }

        $sql .= " ORDER BY d.nombre ASC, l.nombre ASC, u.username ASC";
        
        return $this->db->query($sql, $params)->getResultArray();
    }
}