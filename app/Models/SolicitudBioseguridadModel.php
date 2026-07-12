<?php

/**
 * Modelo para gestión de solicitudes de bioseguridad.
 * 
 * Tabla: solicitudes_bioseguridad
 * Campos: id (PK), codigo_solicitud, usuario_id, ext_telefono,
 *         contenedores_pulso_cantidad, bolsas_rojas_pequena, bolsas_rojas_mediana,
 *         bolsas_rojas_grande, quien_retira, nombre_otra_persona,
 *         estado_solicitud, fecha_registro, editado
 * 
 * Relaciona con usuarios, laboratorios y departamentos para obtener datos completos.
 * Utiliza SQL directo.
 */
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

        /**
     * Genera un código único para la solicitud con formato "BIO-YYYY-XXXX".
     * 
     * Obtiene el último código de la tabla solicitudes_desechos (!!!)
     * con prefijo "BIO-YYYY" y extrae la secuencia numérica para incrementarla.
     * Si no hay registros, comienza en 0001.
     * 
     * !!! ADVERTENCIA: Esta función consulta la tabla 'solicitudes_desechos'
     * en lugar de 'solicitudes_bioseguridad'. ESTADO EN DEMO
     * 
     * @return string Código generado (ej. "BIO-2026-0005")
     * 
     * @example
     * $model = new SolicitudBioseguridadModel();
     * $codigo = $model->generarCodigoUnico(); // "BIO-2026-0007"
     */

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

        /**
     * Inserta una nueva solicitud de bioseguridad.
     * 
     * Los valores por defecto para cantidades son 0, para quien_retira es 'mi_persona',
     * para estado_solicitud es 'Pendiente', y nombre_otra_persona puede ser null.
     * 
     * @param array $data Arreglo con claves: codigo_solicitud, usuario_id, ext_telefono,
     *                    contenedores_pulso_cantidad (opcional), bolsas_rojas_pequena (opcional),
     *                    bolsas_rojas_mediana (opcional), bolsas_rojas_grande (opcional),
     *                    quien_retira (opcional), nombre_otra_persona (opcional),
     *                    estado_solicitud (opcional).
     * @return bool True si la inserción fue exitosa, false en caso contrario.
     * 
     * @example
     * $data = [
     *     'codigo_solicitud' => $model->generarCodigoUnico(),
     *     'usuario_id' => 5,
     *     'ext_telefono' => '1234',
     *     'contenedores_pulso_cantidad' => 2,
     *     'bolsas_rojas_pequena' => 10,
     *     'quien_retira' => 'otra_persona',
     *     'nombre_otra_persona' => 'Juan Pérez'
     * ];
     * $model->insertarSolicitud($data);
     */

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

        /**
     * Construye la cláusula WHERE para los filtros.
     * 
     * Utiliza una configuración de filtros con un arreglo asociativo que mapea
     * el nombre del filtro a una tupla: [condición SQL, función transformadora].
     * Los filtros soportados:
     *   - 'buscar': búsqueda en nombre de departamento (LIKE)
     *   - 'estado_solicitud': coincidencia exacta
     *   - 'fecha_desde': fecha >= con hora 00:00:00
     *   - 'fecha_hasta': fecha <= con hora 23:59:59
     * 
     * @param array $filtros Arreglo con claves opcionales: buscar, estado_solicitud, fecha_desde, fecha_hasta.
     * @param array &$values Arreglo para llenar con valores (pasado por referencia).
     * @return string Cláusula WHERE (ej. "1=1 AND s.estado_solicitud = ? AND s.fecha_registro >= ?").
     * 
     * @example
     * $values = [];
     * $where = $model->armarCondicionesFiltro(['estado_solicitud'=>'Pendiente'], $values);
     * // $where = "1=1 AND s.estado_solicitud = ?"
     * // $values = ['Pendiente']
     */
    
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
     * $total = $model->countSolicitudesFiltradas(['estado_solicitud'=>'Entregado']);
     */
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

    /**
     * Obtiene solicitudes filtradas y paginadas.
     * 
     * Incluye datos adicionales: username, nombre_laboratorio, nombre_departamento.
     * Ordena por id DESC.
     * 
     * @param array $filtros Mismos filtros que en armarCondicionesFiltro.
     * @param int   $limit   Número de registros a obtener.
     * @param int   $offset  Desplazamiento.
     * @return array Lista de registros de solicitudes con datos relacionados.
     * 
     * @example
     * $solicitudes = $model->getSolicitudesFiltradas(['buscar'=>'Química'], 10, 0);
     */
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

    /**
     * Actualiza el estado de una solicitud específica.
     * 
     * @param int    $id          ID de la solicitud.
     * @param string $nuevoEstado Nuevo estado (ej. 'Aprobado', 'Rechazado').
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     * 
     * @example
     * $model->actualizarEstado(12, 'Entregado');
     */
    public function actualizarEstado($id, $nuevoEstado)
    {
        $sql = "UPDATE solicitudes_bioseguridad SET estado_solicitud = ? WHERE id = ?";
        return $this->db->query($sql, [$nuevoEstado, $id]);
    }
}