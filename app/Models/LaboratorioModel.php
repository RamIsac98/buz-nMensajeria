<?php

namespace App\Models;

use CodeIgniter\Model;

class LaboratorioModel extends Model
{
    protected $table      = 'laboratorios';
    protected $primaryKey = 'id';

    public function insertLaboratorio(int $departamento_id, string $nombre): bool
    {
        $sql = "INSERT INTO laboratorios (departamento_id, nombre) VALUES (?, ?)";
        return $this->db->query($sql, [$departamento_id, $nombre]);
    }

    public function getLaboratoriosPaginados(int $limit, int $offset): array
    {
        $sql = "SELECT l.*, d.nombre as nombre_departamento 
                FROM laboratorios l 
                JOIN departamentos d ON l.departamento_id = d.id 
                ORDER BY l.id DESC LIMIT ? OFFSET ?";
        return $this->db->query($sql, [$limit, $offset])->getResultArray();
    }

    public function countLaboratorios(): int
    {
        $sql = "SELECT COUNT(id) as total FROM laboratorios";
        return (int)$this->db->query($sql)->getRowArray()['total'];
    }

    // CORREGIDO: Removida la instrucción nativa preexistente
    public function deleteItem(int $id): bool
    {
        $sql = "DELETE FROM laboratorios WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function updateLaboratorio(int $id, int $departamento_id, string $nombre): bool
    {
        $sql = "UPDATE laboratorios SET nombre = ?, departamento_id = ? WHERE id = ?";
        return $this->db->query($sql, [$nombre, $departamento_id, $id]);
    }

    public function getLaboratoriosFiltrados($departamento_id): array
    {
        $sql = "SELECT l.*, d.nombre as nombre_departamento 
                FROM laboratorios l 
                JOIN departamentos d ON l.departamento_id = d.id";
        
        $params = [];
        
        if ($departamento_id !== 'todos' && !empty($departamento_id)) {
            $sql .= " WHERE l.departamento_id = ?";
            $params[] = (int)$departamento_id;
        }

        $sql .= " ORDER BY l.id DESC";
        
        return $this->db->query($sql, $params)->getResultArray();
    }

    public function findLaboratorio(int $id): ?array
    {
        $sql = "SELECT id, departamento_id, nombre FROM laboratorios WHERE id = ? LIMIT 1";
        return $this->db->query($sql, [$id])->getRowArray();
    }
}