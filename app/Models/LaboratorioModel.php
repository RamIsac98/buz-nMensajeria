<?php

/**
 * Modelo para gestión de laboratorios.
 * 
 * Tabla: laboratorios
 * Campos: id (PK), departamento_id (FK), nombre
 * 
 * Todas las consultas utilizan SQL directo.
 * Relaciona con la tabla departamentos para obtener el nombre del departamento.
 */

namespace App\Models;

use CodeIgniter\Model;

class LaboratorioModel extends Model
{
    protected $table      = 'laboratorios';
    protected $primaryKey = 'id';

        /**
     * Inserta un nuevo laboratorio.
     * 
     * @param int    $departamento_id ID del departamento al que pertenece.
     * @param string $nombre          Nombre del laboratorio.
     * @return bool True si la inserción fue exitosa, false en caso contrario.
     * 
     * @example
     * $model->insertLaboratorio(3, 'Laboratorio de Microbiología');
     */

    public function insertLaboratorio(int $departamento_id, string $nombre): bool
    {
        $sql = "INSERT INTO laboratorios (departamento_id, nombre) VALUES (?, ?)";
        return $this->db->query($sql, [$departamento_id, $nombre]);
    }

        /**
     * Obtiene laboratorios paginados, opcionalmente filtrados por departamento.
     * 
     * Incluye el nombre del departamento mediante JOIN.
     * Ordena por id DESC.
     * 
     * @param int      $limit            Número de registros a obtener.
     * @param int      $offset           Desplazamiento.
     * @param int|null $departamento_id  ID del departamento (null = sin filtro).
     * @return array Lista de registros con campos de laboratorios + nombre_departamento.
     * 
     * @example
     * $pagina1 = $model->getLaboratoriosPaginados(10, 0);
     * $filtrados = $model->getLaboratoriosPaginados(10, 0, 2);
     */

    public function getLaboratoriosPaginados(int $limit, int $offset, ?int $departamento_id = null): array
    {
        $sql = "SELECT l.*, d.nombre as nombre_departamento 
                FROM laboratorios l 
                JOIN departamentos d ON l.departamento_id = d.id";
        $params = [];
        if ($departamento_id !== null) {
            $sql .= " WHERE l.departamento_id = ?";
            $params[] = $departamento_id;
        }
        $sql .= " ORDER BY l.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        return $this->db->query($sql, $params)->getResultArray();
    }

        /**
     * Cuenta el total de laboratorios, opcionalmente filtrados por departamento.
     * 
     * @param int|null $departamento_id ID del departamento (null = sin filtro).
     * @return int Número total de registros.
     * 
     * @example
     * $total = $model->countLaboratorios();     // Todos
     * $totalFiltrados = $model->countLaboratorios(2);
     */

    public function countLaboratorios(?int $departamento_id = null): int
    {
        $sql = "SELECT COUNT(id) as total FROM laboratorios";
        $params = [];
        if ($departamento_id !== null) {
            $sql .= " WHERE departamento_id = ?";
            $params[] = $departamento_id;
        }
        return (int)$this->db->query($sql, $params)->getRowArray()['total'];
    }

        /**
     * Elimina un laboratorio por ID.
     * 
     * @param int $id ID del laboratorio.
     * @return bool True si la eliminación fue exitosa, false en caso contrario.
     * 
     * @example
     * $model->deleteItem(7);
     */

    public function deleteItem(int $id): bool
    {
        $sql = "DELETE FROM laboratorios WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

        /**
     * Actualiza un laboratorio existente.
     * 
     * @param int    $id               ID del laboratorio.
     * @param int    $departamento_id  Nuevo ID de departamento.
     * @param string $nombre           Nuevo nombre.
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     * 
     * @example
     * $model->updateLaboratorio(2, 3, 'Laboratorio de Toxicología');
     */

    public function updateLaboratorio(int $id, int $departamento_id, string $nombre): bool
    {
        $sql = "UPDATE laboratorios SET nombre = ?, departamento_id = ? WHERE id = ?";
        return $this->db->query($sql, [$nombre, $departamento_id, $id]);
    }

        /**
     * Obtiene laboratorios filtrados por departamento (sin paginación).
     * 
     * Utiliza el valor 'todos' como sentinel para no aplicar filtro.
     * Incluye el nombre del departamento mediante JOIN.
     * Ordena por id DESC.
     * 
     * @param mixed $departamento_id Puede ser 'todos', string vacío, o un ID entero.
     * @return array Lista de registros (campos de laboratorios + nombre_departamento).
     * 
     * @example
     * $todos = $model->getLaboratoriosFiltrados('todos');
     * $filtrados = $model->getLaboratoriosFiltrados(2);
     */


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

        /**
     * Busca un laboratorio por su ID.
     * 
     * @param int $id ID del laboratorio.
     * @return array|null Registro con campos id, departamento_id, nombre, o null si no existe.
     * 
     * @example
     * $lab = $model->findLaboratorio(5);
     * // ['id'=>5, 'departamento_id'=>2, 'nombre'=>'Laboratorio de Física']
     */
    
    public function findLaboratorio(int $id): ?array
    {
        $sql = "SELECT id, departamento_id, nombre FROM laboratorios WHERE id = ? LIMIT 1";
        return $this->db->query($sql, [$id])->getRowArray();
    }
}