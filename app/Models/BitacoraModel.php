<?php
/**
 * Modelo para gestionar la bitácora de eventos del sistema.
 * 
 * Utiliza consultas SQL directas (no Query Builder) para mayor control.
 * Registra acciones de usuarios con IP y tipo de solicitud.
 * Permite filtrado y paginación de registros.
 * 
 * Tabla: bitacora
 * Campos: id, usuario_id, tipo_solicitud, registro, ip, accion, fecha
 */
namespace App\Models;

use CodeIgniter\Model;

class BitacoraModel extends Model
{
    protected $table = 'bitacora';
    protected $primaryKey = 'id';

        /**
     * Inserta un nuevo registro en la bitácora.
     * 
     * @param array $datos Arreglo asociativo con claves:
     *   - usuario_id (int, opcional): ID del usuario, puede ser null.
     *   - tipo_solicitud (string): Tipo de evento (ej. 'Sesión', 'Seguridad').
     *   - registro (string): Descripción detallada.
     *   - ip (string): Dirección IP del usuario.
     *   - accion (string): Acción realizada (ej. 'Inició sesión').
     * @return int|string ID del registro insertado.
     * 
     * @example
     * $model->insertRegistro([
     *     'usuario_id'    => 5,
     *     'tipo_solicitud'=> 'Sesión',
     *     'registro'      => 'Usuario admin ingresó',
     *     'ip'            => $this->request->getIPAddress(),
     *     'accion'        => 'Login'
     * ]);
     */

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

    /**
     * Construye la cláusula WHERE para filtros y llena el arreglo de valores por referencia.
     * 
     * Filtros soportados:
     * - 'buscar': búsqueda en username, ip, registro, accion (LIKE).
     * - 'tipo': tipo_solicitud exacto.
     * - 'desde': fecha mayor o igual (con hora 00:00:00).
     * - 'hasta': fecha menor o igual (con hora 23:59:59).
     * 
     * @param array $filtros Arreglo con claves opcionales: buscar, tipo, desde, hasta.
     * @param array &$values Arreglo que se llenará con los valores para bind (pasado por referencia).
     * @return string Cláusula WHERE armada (ej. "1=1 AND u.username LIKE ? AND b.tipo_solicitud = ?").
     * 
     * @example
     * $values = [];
     * $where = $this->armarCondicionesFiltro(['buscar'=>'admin', 'tipo'=>'Sesión'], $values);
     * // $where = "1=1 AND (u.username LIKE ? OR b.ip LIKE ? OR b.registro LIKE ? OR b.accion LIKE ?) AND b.tipo_solicitud = ?"
     * // $values = ['%admin%', '%admin%', '%admin%', '%admin%', 'Sesión']
     */

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

    /**
     * Cuenta el total de registros de bitácora aplicando los filtros dados.
     * 
     * @param array $filtros Mismos filtros que en armarCondicionesFiltro.
     * @return int Número total de registros.
     * 
     * @example
     * $total = $model->countBitacora(['tipo'=>'Sesión', 'desde'=>'2026-01-01']);
     */

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

    /**
     * Obtiene registros de bitácora filtrados y paginados.
     * 
     * @param array $filtros Mismos filtros que en armarCondicionesFiltro.
     * @param int $limit Cantidad de registros a obtener.
     * @param int $offset Desplazamiento (página actual * limit).
     * @return array Arreglo de registros con campos de bitácora y username del usuario.
     * 
     * @example
     * $registros = $model->getBitacoraFiltrada(['buscar'=>'error'], 10, 0);
     * // Devuelve los primeros 10 registros que contengan 'error' en username, ip, registro o accion.
     */
    
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