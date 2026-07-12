<?php


/**
 * Modelo para gestión de usuarios.
 * 
 * Tabla: usuarios
 * Campos: id (PK), username, password, rol, cedula, tipo_cedula, nombre, apellido,
 *         laboratorio_id, status, pregunta_seguridad, respuesta_seguridad, etc.
 * 
 * Proporciona métodos CRUD, búsqueda por diferentes campos, filtrado y paginación.
 * Utiliza SQL directo y formatea el username para mostrar con inicial separada.
 * 
 * La función formatearUsername añade un espacio después del primer carácter
 */

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id';

        /**
     * Formatea el username insertando un espacio después del primer carácter.
     * 
     * Si el username tiene más de 1 carácter, devuelve: primer_caracter + ' ' + resto.
     * Ejemplo: "jperez" → "j perez"
     * 
     * !!! Esta función asume que el username tiene una estructura específica
     * (ej. inicial + apellido), pero no hay validación de que así sea.
     * 
     * @param string $username Username original.
     * @return string Username formateado.
     * 
     * @example
     * $model->formatearUsername('mpaz'); // "m paz"
     */
    private function formatearUsername(string $username): string
    {
        if (strlen($username) <= 1) {
            return $username;
        }
        return $username[0] . ' ' . substr($username, 1);
    }

        /**
     * Obtiene todos los roles únicos existentes en la tabla usuarios.
     * 
     * @return array Lista de strings con los roles (ej. ['admin', 'user']).
     * 
     * @example
     * $roles = $model->getRolesDisponibles(); // ['administrador', 'proteccion_integral']
     */
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

        /**
     * Busca un usuario por ID, incluyendo datos de laboratorio y departamento.
     * 
     * Agrega el campo 'display_username' con el username formateado.
     * 
     * @param int $id ID del usuario.
     * @return array|null Registro del usuario con datos relacionados, o null si no existe.
     * 
     * @example
     * $user = $model->findById(5);
     * // ['id'=>5, 'username'=>'jperez', 'display_username'=>'j perez', 'nombre_laboratorio'=>'Lab1', ...]
     */
    public function findById($id)
    {
        $sql = "SELECT u.*, 
                        l.nombre AS nombre_laboratorio, 
                        d.nombre AS departamento,
                        d.id AS departamento_id
                    FROM usuarios u
                    LEFT JOIN laboratorios l ON u.laboratorio_id = l.id
                    LEFT JOIN departamentos d ON l.departamento_id = d.id
                    WHERE u.id = ? 
                    LIMIT 1";

        $result = $this->db->query($sql, [$id])->getRowArray();
        if ($result) {
            $result['display_username'] = $this->formatearUsername($result['username']);
        }
        return $result;
    }

        /**
     * Busca un usuario por username exacto.
     * 
     * @param string $username Nombre de usuario.
     * @return array|null Registro completo o null.
     * 
     * @example
     * $user = $model->findByUsername('jperez');
     */
    public function findByUsername($username)
    {
        $sql = "SELECT * FROM usuarios WHERE username = ? LIMIT 1";
        return $this->db->query($sql, [$username])->getRowArray();
    }

        /**
     * Busca un usuario por número de cédula exacto.
     * 
     * @param string $cedula Número de cédula.
     * @return array|null Registro completo o null.
     * 
     * @example
     * $user = $model->findByCedula('12345678');
     */
    public function findByCedula($cedula)
    {
        $sql = "SELECT * FROM usuarios WHERE cedula = ? LIMIT 1";
        return $this->db->query($sql, [$cedula])->getRowArray();
    }

        /**
     * Busca un usuario por tipo de cédula y número.
     * 
     * @param string $tipo   Tipo de cédula (ej. 'V', 'E').
     * @param string $cedula Número de cédula.
     * @return array|null Registro completo o null.
     * 
     * @example
     * $user = $model->findByTipoCedula('V', '12345678');
     */
    public function findByTipoCedula(string $tipo, string $cedula)
    {
        $sql = "SELECT * FROM usuarios WHERE tipo_cedula = ? AND cedula = ? LIMIT 1";
        return $this->db->query($sql, [$tipo, $cedula])->getRowArray();
    }

        /**
     * Verifica si existe un usuario con una cédula dada.
     * 
     * @param string $cedula Número de cédula.
     * @return bool True si existe, false en caso contrario.
     * 
     * @example
     * $existe = $model->existeCedula('12345678');
     */
    public function existeCedula(string $cedula): bool
    {
        $sql = "SELECT 1 FROM usuarios WHERE cedula = ? LIMIT 1";
        $resultado = $this->db->query($sql, [$cedula])->getRowArray();
        return !empty($resultado);
    }

        /**
     * Verifica si existe una cédula excluyendo un ID específico (para edición).
     * 
     * @param string $cedula Número de cédula.
     * @param int    $id     ID del usuario a excluir.
     * @return bool True si existe otro usuario con esa cédula, false en caso contrario.
     * 
     * @example
     * $existe = $model->existeCedulaExcluyendoId('12345678', 10);
     */
    public function existeCedulaExcluyendoId(string $cedula, $id): bool
    {
        $sql = "SELECT 1 FROM usuarios WHERE cedula = ? AND id != ? LIMIT 1";
        $resultado = $this->db->query($sql, [$cedula, $id])->getRowArray();
        return !empty($resultado);
    }

        /**
     * Inserta un nuevo usuario.
     * 
     * @param array $datos Arreglo con claves: username, password, rol, cedula,
     *                     tipo_cedula (opcional, default 'V'), nombre (opcional),
     *                     apellido (opcional), laboratorio_id (opcional),
     *                     status (opcional, default 1).
     * @return bool True si la inserción fue exitosa, false en caso contrario.
     * 
     * @example
     * $model->insertUsuario([
     *     'username' => 'jperez',
     *     'password' => password_hash('1234', PASSWORD_DEFAULT),
     *     'rol' => 'auxiliar',
     *     'cedula' => '12345678',
     *     'tipo_cedula' => 'V',
     *     'nombre' => 'Juan',
     *     'apellido' => 'Pérez',
     *     'laboratorio_id' => 3
     * ]);
     */
    public function insertUsuario($datos)
    {
        $sql = "INSERT INTO usuarios (username, password, rol, cedula, tipo_cedula, nombre, apellido, laboratorio_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        return $this->db->query($sql, [
            $datos['username'],
            $datos['password'],
            $datos['rol'],
            $datos['cedula'],
            $datos['tipo_cedula'] ?? 'V',
            $datos['nombre'] ?? null,
            $datos['apellido'] ?? null,
            $datos['laboratorio_id'] ?? null,
            $datos['status'] ?? 1
        ]);
    }
        /**
     * Actualiza un usuario existente con un arreglo de datos dinámico.
     * 
     * Construye la cláusula SET a partir de las claves del arreglo.
     * No hay validación de columnas, por lo que se deben pasar solo columnas válidas.
     * 
     * @param int   $id    ID del usuario.
     * @param array $datos Arreglo asociativo columna => valor.
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     * 
     * @example
     * $model->updateUsuario(5, ['status' => 0, 'rol' => 'admin']);
     */
    public function updateUsuario($id, $datos)
    {
        if (empty($datos)) return false;
        
        $setClause = [];
        $values = [];
        foreach ($datos as $columna => $valor) {
            $setClause[] = "$columna = ?";
            $values[] = $valor;
        }
        $values[] = $id;

        $sql = "UPDATE usuarios SET " . implode(', ', $setClause) . " WHERE id = ?";
        return $this->db->query($sql, $values);
    }
        /**
     * Obtiene el nombre completo (nombre + apellido) de un usuario por ID.
     * 
     * @param int $id ID del usuario.
     * @return string Nombre completo o 'Usuario' si no existe.
     * 
     * @example
     * $nombre = $model->getNombreCompleto(5); // "Juan Pérez"
     */
    public function getNombreCompleto($id)
    {
        $user = $this->findById($id);
        if ($user) {
            return trim($user['nombre'] . ' ' . $user['apellido']);
        }
        return 'Usuario';
    }

       /**
     * Elimina un usuario por ID.
     * 
     * @param int $id ID del usuario.
     * @return bool True si la eliminación fue exitosa, false en caso contrario.
     * 
     * @example
     * $model->deleteUsuario(10);
     */
    public function deleteUsuario($id)
    {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    /**
     * Construye la cláusula WHERE para filtros de usuarios.
     * 
     * Filtros soportados:
     *   - 'buscar': búsqueda en username o cedula (LIKE).
     *   - 'rol': búsqueda en rol (LIKE).
     *   - 'estado': coincidencia exacta en status (0 o 1), solo si el valor no está vacío.
     * 
     * @param array $filtros Arreglo con claves opcionales: buscar, rol, estado.
     * @param array &$values Arreglo para llenar con valores (pasado por referencia).
     * @return string Cláusula WHERE (ej. "1=1 AND (username LIKE ? OR cedula LIKE ?) AND rol LIKE ?").
     * 
     * @example
     * $values = [];
     * $where = $model->armarCondicionesFiltro(['rol'=>'admin', 'estado'=>1], $values);
     * // $where = "1=1 AND rol LIKE ? AND status = ?"
     * // $values = ['%admin%', 1]
     */
    private function armarCondicionesFiltro($filtros, &$values)
    {
        $where = ["1=1"];

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

        /**
     * Cuenta el total de usuarios que cumplen los filtros.
     * 
     * @param array $filtros Mismos filtros que en armarCondicionesFiltro.
     * @return int Número total de usuarios.
     * 
     * @example
     * $total = $model->countUsuarios(['estado'=>1]);
     */
    public function countUsuarios($filtros)
    {
        $values = [];
        $whereSql = $this->armarCondicionesFiltro($filtros, $values);
        
        $sql = "SELECT COUNT(id) as total FROM usuarios WHERE $whereSql";
        $resultado = $this->db->query($sql, $values)->getRowArray();
        
        return $resultado['total'];
    }

        /**
     * Obtiene usuarios filtrados y paginados, con username formateado.
     * 
     * @param array $filtros Mismos filtros que en armarCondicionesFiltro.
     * @param int   $limit   Número de registros a obtener.
     * @param int   $offset  Desplazamiento.
     * @return array Lista de registros de usuarios con campo 'display_username' adicional.
     * 
     * @example
     * $usuarios = $model->getUsuariosFiltrados(['rol'=>'admin'], 10, 0);
     */
    public function getUsuariosFiltrados($filtros, $limit, $offset)
    {
        $values = [];
        $whereSql = $this->armarCondicionesFiltro($filtros, $values);
        
        $sql = "SELECT * FROM usuarios WHERE $whereSql ORDER BY id DESC LIMIT ? OFFSET ?";
        $values[] = (int)$limit;
        $values[] = (int)$offset;

        $resultados = $this->db->query($sql, $values)->getResultArray();
        foreach ($resultados as &$row) {
            $row['display_username'] = $this->formatearUsername($row['username']);
        }
        return $resultados;
    }

        /**
     * Genera un reporte general de usuarios agrupados por departamento y laboratorio.
     * 
     * Devuelve todos los departamentos, con sus laboratorios y usuarios asociados.
     * Si se pasa un departamento_id específico (no 'todos'), filtra por ese departamento.
     * 
     * @param string|int $departamento_id Puede ser 'todos' (sin filtro) o un ID entero.
     * @return array Lista de registros con campos: nombre_departamento, nombre_laboratorio,
     *               nombre_usuario, apellido_usuario, username_usuario, cedula_usuario,
     *               rol_usuario, estado_usuario. Ordenado por departamento, laboratorio, username.
     * 
     * @example
     * $reporte = $model->getReporteGeneral('todos');
     * $reporteFiltrado = $model->getReporteGeneral(2);
     */
    public function getReporteGeneral($departamento_id): array
    {
        $sql = "SELECT 
                    d.nombre AS nombre_departamento,
                    l.nombre AS nombre_laboratorio,
                    u.nombre AS nombre_usuario,
                    u.apellido AS apellido_usuario,
                    u.username AS username_usuario,
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