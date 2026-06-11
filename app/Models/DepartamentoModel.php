<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartamentoModel extends Model
{
    protected $table      = 'departamentos';
    protected $primaryKey = 'id';

    public function getDepartamentos(): array
    {
        $sql = "SELECT id, nombre FROM departamentos ORDER BY nombre ASC";
        return $this->db->query($sql)->getResultArray();
    }

    public function insertDepartamento(string $nombre): bool
    {
        $sql = "INSERT INTO departamentos (nombre) VALUES (?)";
        return $this->db->query($sql, [$nombre]);
    }

    public function getDepartamentosPaginados(int $limit, int $offset): array
    {
        $sql = "SELECT id, nombre FROM departamentos ORDER BY nombre ASC LIMIT ? OFFSET ?";
        return $this->db->query($sql, [$limit, $offset])->getResultArray();
    }

    public function countDepartamentos(): int
    {
        $sql = "SELECT COUNT(id) as total FROM departamentos";
        return (int)$this->db->query($sql)->getRowArray()['total'];
    }

    public function updateDepartamento(int $id, string $nombre): bool
    {
        $sql = "UPDATE departamentos SET nombre = ? WHERE id = ?";
        return $this->db->query($sql, [$nombre, $id]);
    }

    public function deleteItem(int $id): bool
    {
        $sql = "DELETE FROM departamentos WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function findDepartamento(int $id): ?array
    {
        $sql = "SELECT id, nombre FROM departamentos WHERE id = ? LIMIT 1";
        return $this->db->query($sql, [$id])->getRowArray();
    }

    
}