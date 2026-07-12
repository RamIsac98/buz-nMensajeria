<?php

/**
 * Modelo para gestión de departamentos (CRUD básico).
 * 
 * Tabla: departamentos
 * Campos: id (INT, PK), nombre (VARCHAR)
 * 
 * Todas las consultas se realizan con SQL directo (no Query Builder).
 */

namespace App\Models;

use CodeIgniter\Model;

class DepartamentoModel extends Model
{
    protected $table      = 'departamentos';
    protected $primaryKey = 'id';

     /**
     * Obtiene todos los departamentos ordenados por nombre.
     * 
     * @return array Lista de registros con campos id, nombre.
     * 
     * @example
     * $model = new DepartamentoModel();
     * $departamentos = $model->getDepartamentos();
     */

    public function getDepartamentos(): array
    {
        $sql = "SELECT id, nombre FROM departamentos ORDER BY nombre ASC";
        return $this->db->query($sql)->getResultArray();
    }

        /**
     * Inserta un nuevo departamento.
     * 
     * @param string $nombre Nombre del departamento.
     * @return bool True si la inserción fue exitosa, false en caso contrario.
     * 
     * @example
     * $model->insertDepartamento('Recursos Humanos');
     */

    public function insertDepartamento(string $nombre): bool
    {
        $sql = "INSERT INTO departamentos (nombre) VALUES (?)";
        return $this->db->query($sql, [$nombre]);
    }

        /**
     * Obtiene departamentos paginados (para listados con limit y offset).
     * 
     * @param int $limit  Número de registros a obtener.
     * @param int $offset Desplazamiento.
     * @return array Lista de registros (id, nombre) ordenados por nombre ASC.
     * 
     * @example
     * $pagina1 = $model->getDepartamentosPaginados(10, 0);
     * $pagina2 = $model->getDepartamentosPaginados(10, 10);
     */

    public function getDepartamentosPaginados(int $limit, int $offset): array
    {
        $sql = "SELECT id, nombre FROM departamentos ORDER BY nombre ASC LIMIT ? OFFSET ?";
        return $this->db->query($sql, [$limit, $offset])->getResultArray();
    }

        /**
     * Cuenta el total de departamentos.
     * 
     * @return int Número total de registros.
     * 
     * @example
     * $total = $model->countDepartamentos(); // 25
     */

    public function countDepartamentos(): int
    {
        $sql = "SELECT COUNT(id) as total FROM departamentos";
        return (int)$this->db->query($sql)->getRowArray()['total'];
    }

        /**
     * Actualiza el nombre de un departamento existente.
     * 
     * @param int    $id     ID del departamento.
     * @param string $nombre Nuevo nombre.
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     * 
     * @example
     * $model->updateDepartamento(5, 'RRHH');
     */

    public function updateDepartamento(int $id, string $nombre): bool
    {
        $sql = "UPDATE departamentos SET nombre = ? WHERE id = ?";
        return $this->db->query($sql, [$nombre, $id]);
    }

        /**
     * Elimina un departamento por ID.
     * 
     * @param int $id ID del departamento.
     * @return bool True si la eliminación fue exitosa, false en caso contrario.
     * 
     * @example
     * $model->deleteItem(10);
     */

    public function deleteItem(int $id): bool
    {
        $sql = "DELETE FROM departamentos WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

        /**
     * Busca un departamento por su ID.
     * 
     * @param int $id ID del departamento.
     * @return array|null Registro con campos id, nombre o null si no existe.
     * 
     * @example
     * $dep = $model->findDepartamento(3);
     * // ['id'=>3, 'nombre'=>'Logística']
     */
    
    public function findDepartamento(int $id): ?array
    {
        $sql = "SELECT id, nombre FROM departamentos WHERE id = ? LIMIT 1";
        return $this->db->query($sql, [$id])->getRowArray();
    }

    
}