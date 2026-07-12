<?php

/**
 * Modelo para gestión de solicitudes de desechos.
 * 
 * Tabla: solicitudes_desechos
 * Campos: id (PK), codigo_solicitud, usuario_id, ext_telefono, tipos_desecho,
 *         variantes_desecho, esterilizado, motivo, estado, peso_kg, peso_l,
 *         tipo_empaque, empaque_otro_descripcion, estado_solicitud, fecha_registro, editado
 * 
 * Relaciona con usuarios, laboratorios y departamentos para obtener datos completos.
 * Todas las consultas usan SQL directo.
 */

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

        /**
     * Genera un código único para la solicitud con formato "SOL-YYYY-XXXX".
     * 
     * Obtiene el último código de la tabla solicitudes_desechos con prefijo "SOL-YYYY"
     * y extrae la secuencia numérica para incrementarla.
     * Si no hay registros, comienza en 0001.
     * 
     * @return string Código generado (ej. "SOL-2026-0012")
     * 
     * @example
     * $model = new SolicitudDesechosModel();
     * $codigo = $model->generarCodigoUnico(); // "SOL-2026-0015"
     */

    public function generarCodigoUnico(): string
    {
        $prefix = "SOL-" . date('Y');
        
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

        /**
     * Inserta una nueva solicitud de desechos.
     * 
     * Los valores por defecto: peso_kg y peso_l = 0.00, empaque_otro_descripcion = null,
     * estado_solicitud = 'Pendiente'.
     * 
     * @param array $data Arreglo con claves: codigo_solicitud, usuario_id, ext_telefono,
     *                    tipos_desecho, variantes_desecho, esterilizado, motivo, estado,
     *                    peso_kg (opcional), peso_l (opcional), tipo_empaque,
     *                    empaque_otro_descripcion (opcional), estado_solicitud (opcional).
     * @return bool True si la inserción fue exitosa, false en caso contrario.
     * 
     * @example
     * $data = [
     *     'codigo_solicitud' => $model->generarCodigoUnico(),
     *     'usuario_id' => 3,
     *     'ext_telefono' => '5678',
     *     'tipos_desecho' => 'Químicos',
     *     'variantes_desecho' => 'Líquido',
     *     'esterilizado' => 'Sí',
     *     'motivo' => 'Descarte',
     *     'estado' => 'Nuevo',
     *     'peso_kg' => 25.5,
     *     'tipo_empaque' => 'Otro',
     *     'empaque_otro_descripcion' => 'Bidón plástico'
     * ];
     * $model->insertarSolicitud($data);
     */

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

        /**
     * Construye la cláusula WHERE para los filtros.
     * 
     * Filtros soportados:
     *   - 'buscar': búsqueda en nombre de departamento (LIKE)
     *   - 'tipo_desecho': búsqueda en tipos_desecho (LIKE)
     *   - 'estado_solicitud': coincidencia exacta
     *   - 'fecha_desde': fecha >= (solo DATE, sin hora)
     *   - 'fecha_hasta': fecha <= (solo DATE, sin hora)
     * 
     * @param array $filtros Arreglo con claves opcionales: buscar, tipo_desecho, estado_solicitud, fecha_desde, fecha_hasta.
     * @param array &$values Arreglo para llenar con valores (pasado por referencia).
     * @return string Cláusula WHERE (ej. "1=1 AND d.nombre LIKE ? AND s.estado_solicitud = ?").
     * 
     * @example
     * $values = [];
     * $where = $model->armarCondicionesFiltro(['estado_solicitud'=>'Pendiente'], $values);
     * // $where = "1=1 AND s.estado_solicitud = ?"
     * // $values = ['Pendiente']
     */

    private function armarCondicionesFiltro($filtros, &$values)
    {
        $where = ["1=1"];

        if (!empty($filtros['buscar'])) {
            $where[] = "d.nombre LIKE ?";
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

        /**
     * Cuenta el total de solicitudes que cumplen los filtros.
     * 
     * Utiliza LEFT JOIN con usuarios, laboratorios y departamentos para
     * poder aplicar el filtro de búsqueda por nombre de departamento.
     * 
     * @param array $filtros Mismos filtros que en armarCondicionesFiltro.
     * @return int Número total de registros.
     * 
     * @example
     * $total = $model->countSolicitudesFiltradas(['estado_solicitud'=>'Aprobado']);
     */

    public function countSolicitudesFiltradas($filtros)
    {
        $values = [];
        $whereSql = $this->armarCondicionesFiltro($filtros, $values);
        
        $sql = "SELECT COUNT(s.id) as total 
                FROM solicitudes_desechos s
                LEFT JOIN usuarios u ON s.usuario_id = u.id
                LEFT JOIN laboratorios l ON u.laboratorio_id = l.id
                LEFT JOIN departamentos d ON l.departamento_id = d.id
                WHERE $whereSql";
                
        $resultado = $this->db->query($sql, $values)->getRowArray();
        return $resultado['total'];
    }

        /**
     * Obtiene solicitudes filtradas y paginadas.
     * 
     * Incluye datos adicionales: username, cedula, nombre_departamento, nombre_laboratorio.
     * Ordena por id DESC.
     * 
     * @param array $filtros Mismos filtros que en armarCondicionesFiltro.
     * @param int   $limit   Número de registros a obtener.
     * @param int   $offset  Desplazamiento.
     * @return array Lista de registros de solicitudes con datos relacionados.
     * 
     * @example
     * $solicitudes = $model->getSolicitudesFiltradas(['tipo_desecho'=>'Biológico'], 10, 0);
     */

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

        /**
     * Obtiene todas las solicitudes sin paginación ni filtros.
     * 
     * Incluye datos adicionales: username, cedula, nombre_departamento, nombre_laboratorio.
     * Ordena por id DESC.
     * 
     * !!! ADVERTENCIA: En la cláusula LEFT JOIN con laboratorios se usa "s.usuario_id = l.id",
     * lo cual es incorrecto porque s.usuario_id es ID de usuario, no de laboratorio.
     * Debería ser "u.laboratorio_id = l.id" como en getSolicitudesFiltradas.
     * Esto probablemente devuelve NULL en nombre_laboratorio para todas las filas.
     * 
     * @return array Lista de todos los registros de solicitudes con datos relacionados.
     * 
     * @example
     * $todas = $model->getSolicitudes();
     */

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

        /**
     * Actualiza el estado de una solicitud específica.
     * 
     * @param int    $id          ID de la solicitud.
     * @param string $nuevoEstado Nuevo estado (ej. 'Aprobado', 'Rechazado').
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     * 
     * @example
     * $model->actualizarEstado(8, 'Entregado');
     */

    public function actualizarEstado($id, $nuevoEstado)
    {
        $sql = "UPDATE solicitudes_desechos SET estado_solicitud = ? WHERE id = ?";
        return $this->db->query($sql, [$nuevoEstado, $id]);
    }
}