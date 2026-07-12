<?php
/**
 * Controlador de gestión de usuarios (administración y protección integral).
 * 
 * Proporciona operaciones CRUD, filtrado, generación de PDF, gestión de seguridad
 * (pregunta de recuperación y cambio de contraseña) y bitácora de acciones.
 * 
 * Solo accesible para roles 'administrador' y 'proteccion_integral'.
 * Hereda de BaseController para autenticación y auditoría.
 * 
 * Dependencias:
 * - UsuarioModel, DepartamentoModel, LaboratorioModel.
 * - Dompdf para generación de PDFs.
 * - Servicio de paginación de CodeIgniter.
 * 
 * @package App\Controllers
 */
namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\DepartamentoModel;
use App\Models\LaboratorioModel;

class Usuarios extends BaseController
{
        /**
     * Verifica si el rol de la sesión tiene permisos de gestión.
     * Solo 'administrador' o 'proteccion_integral' pueden acceder.
     *
     * @return bool True si tiene acceso, False en otro caso.
     */
    private function verificarAccesoGestion(): bool
    {
        $rol = session()->get('rol');
        return ($rol === 'administrador' || $rol === 'proteccion_integral');
    }

    /**
     * Muestra el listado de usuarios con filtros y paginación.
     * 
     * Filtros GET:
     * - buscar (string) : búsqueda por username, nombre, apellido, cedula
     * - rol (string)    : filtro por rol
     * - estado (string) : filtro por estado (activo/inactivo)
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse|string Vista renderizada o redirección.
     * 
     * @example
     * GET /usuarios?buscar=juan&rol=admin&page=2
     */
    public function index()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $rol = session()->get('rol');
        if (!in_array(session()->get('rol'), ['administrador', 'proteccion_integral'])) return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'Acceso denegado: No tienes permisos.');

        $usuarioModel = new UsuarioModel();

        $filtros = [
            'buscar' => $this->request->getGet('buscar'),
            'rol'    => $this->request->getGet('rol'),
            'estado' => $this->request->getGet('estado')
        ];

        $page    = (int)($this->request->getGet('page') ?? 1);
        $perPage = 8;
        $offset  = ($page - 1) * $perPage;

        $data['roles_disponibles'] = $usuarioModel->getRolesDisponibles();
        $data['usuarios']          = $usuarioModel->getUsuariosFiltrados($filtros, $perPage, $offset);
        $total                     = $usuarioModel->countUsuarios($filtros);

        $pager = \Config\Services::pager();
        $pager->store('default', $page, $perPage, $total);
        $data['pager'] = $pager;

        return view('usuarios/index', $data);
    }

    /**
     * Genera y descarga un PDF con el listado de usuarios filtrado.
     * 
     * Permite seleccionar un rango de páginas (cada página = 8 registros).
     * Parámetros GET:
     * - buscar, rol, estado (idem index)
     * - pagina_inicio (int) : página desde la cual comenzar (por defecto 1)
     * - pagina_fin    (int) : página final (por defecto igual a inicio)
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse|void Redirige si no hay permisos o descarga el PDF.
     * 
     * @example
     * GET /usuarios/generarPdfUsuarios?buscar=admin&pagina_inicio=1&pagina_fin=3
     */

    public function generarPdfUsuarios()
    {
        if (!$this->estaLogueado()) {
            return redirect()->to(base_url('login'));
        }
        if (!$this->verificarAccesoGestion()) {
            return redirect()->to(base_url('desechos/registroSolicitudes'))
                            ->with('error', 'Acceso denegado.');
        }

        $usuarioModel = new UsuarioModel();

        $filtros = [
            'buscar' => $this->request->getGet('buscar'),
            'rol'    => $this->request->getGet('rol'),
            'estado' => $this->request->getGet('estado')
        ];

        $paginaInicio = (int)($this->request->getGet('pagina_inicio') ?? 1);
        $paginaFin    = (int)($this->request->getGet('pagina_fin') ?? $paginaInicio);
        
        $porPagina = 8;
        $offset    = ($paginaInicio - 1) * $porPagina;
        $limite    = (($paginaFin - $paginaInicio) + 1) * $porPagina;

        $data['usuarios'] = $usuarioModel->getUsuariosFiltrados($filtros, $limite, $offset);

        // Cerrar sesión y liberar buffers
        session_write_close();

        while (ob_get_level()) {
            ob_end_clean();
        }

        $html = view('usuarios/usuarios_pdf', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $dompdf->stream("Reporte_Usuarios_Paginas_{$paginaInicio}_al_{$paginaFin}.pdf", ["Attachment" => true]);
        // El stream ya incluye exit, por lo que no es necesario agregar más código.
    }

    
    /**
     * Muestra el formulario de edición de un usuario específico.
     * 
     * Carga los departamentos y laboratorios asociados.
     * 
     * @param int $id ID del usuario a editar.
     * @return \CodeIgniter\HTTP\RedirectResponse|string Vista o redirección.
     * 
     * @example
     * GET /usuarios/editar/5
     */

    public function editar($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        if (!$this->verificarAccesoGestion()) {
            return redirect()->to(base_url('desechos/registroSolicitudes'))
                             ->with('error', 'Acceso denegado.');
        }

        $usuarioModel = new UsuarioModel();
        $data['usuario'] = $usuarioModel->findById($id);

        if (!$data['usuario']) {
            return redirect()->to(base_url('usuarios'))->with('error', 'Usuario no encontrado.');
        }

        $labModel   = new LaboratorioModel();
        $deptoModel = new DepartamentoModel();

        $data['departamentos'] = $deptoModel->getDepartamentos();
        $data['id_departamento_actual'] = null;

        if (!empty($data['usuario']['laboratorio_id'])) {
            $labActual = $labModel->findLaboratorio((int)$data['usuario']['laboratorio_id']);
            if ($labActual) {
                $data['id_departamento_actual'] = $labActual['departamento_id'];
            }
        }

        if (!empty($data['id_departamento_actual'])) {
            $data['laboratorios'] = $labModel->getLaboratoriosFiltrados($data['id_departamento_actual']);
        } else {
            $data['laboratorios'] = [];
        }

        return view('usuarios/editar', $data);
    }

    /**
     * Procesa la actualización de los datos de un usuario.
     * 
     * Valida campos, verifica duplicados de cédula y permite cambiar contraseña
     * o eliminar la pregunta de seguridad.
     *
     * @param int $id ID del usuario a actualizar.
     * @return \CodeIgniter\HTTP\RedirectResponse Redirección con mensaje de éxito/error.
     * 
     * @example
     * POST /usuarios/actualizar/5 (con datos del formulario)
     */
        public function actualizar($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        if (!$this->verificarAccesoGestion()) {
            return redirect()->to(base_url('desechos/registroSolicitudes'))
                             ->with('error', 'Acceso denegado.');
        }

        $usuarioModel = new UsuarioModel();
        $usuarioActual = $usuarioModel->findById($id);

        if (!$usuarioActual) {
            return redirect()->to(base_url('usuarios'))->with('error', 'Usuario no encontrado.');
        }

        $username       = trim($this->request->getPost('username'));
        $cedula         = trim($this->request->getPost('cedula'));
        $tipo_cedula    = $this->request->getPost('tipo_cedula');
        $nombre         = trim($this->request->getPost('nombre'));
        $apellido       = trim($this->request->getPost('apellido'));
        $rol            = $this->request->getPost('rol');
        $nuevaClave     = $this->request->getPost('password');
        $eliminarPregunta = $this->request->getPost('eliminar_pregunta');
        $id_laboratorio = $this->request->getPost('id_laboratorio') ?? null;

        // ---- Validaciones ----
        if (empty($username)) return redirect()->back()->with('error', 'El campo Username es obligatorio.')->withInput();
        if (strlen($username) < 3) return redirect()->back()->with('error', 'El nombre de usuario debe tener al menos 3 caracteres.')->withInput();
        if (strpos($username, ' ') !== false) return redirect()->back()->with('error', 'El nombre de usuario no puede contener espacios.')->withInput();

        if (empty($cedula)) return redirect()->back()->with('error', 'El campo Cédula es obligatorio.')->withInput();
        if (!ctype_digit($cedula) || strlen($cedula) < 6 || strlen($cedula) > 10) {
        return redirect()->back()->with('error', 'La cédula debe tener entre 6 y 10 dígitos numéricos.')->withInput();
        }

        if (empty($tipo_cedula)) return redirect()->back()->with('error', 'Debe seleccionar el tipo de cédula.')->withInput();
        if (!in_array($tipo_cedula, ['V', 'E'])) return redirect()->back()->with('error', 'Tipo de cédula inválido.')->withInput();

        if (empty($nombre)) return redirect()->back()->with('error', 'El campo Nombre es obligatorio.')->withInput();
        if (strlen($nombre) > 25) return redirect()->back()->with('error', 'El nombre no puede exceder los 25 caracteres.')->withInput();

        if (empty($apellido)) return redirect()->back()->with('error', 'El campo Apellido es obligatorio.')->withInput();
        if (strlen($apellido) > 25) return redirect()->back()->with('error', 'El apellido no puede exceder los 25 caracteres.')->withInput();

        if (empty($rol)) return redirect()->back()->with('error', 'Debe seleccionar un rol.')->withInput();
        if (empty($id_laboratorio)) return redirect()->back()->with('error', 'Debe seleccionar un laboratorio.')->withInput();

        if (!empty($nuevaClave) && strlen($nuevaClave) < 6) {
            return redirect()->back()->with('error', 'La nueva contraseña debe tener al menos 6 caracteres.')->withInput();
        }

        if ($usuarioModel->existeCedulaExcluyendoId($cedula, $id)) {
            return redirect()->back()->with('error', "La cédula {$cedula} ya pertenece a otro usuario registrado.");
        }

        if (!empty($nuevaClave) && strpos($nuevaClave, ' ') !== false) {
            return redirect()->back()->with('error', 'La nueva contraseña no puede contener espacios en blanco.')->withInput();
        }

        $datosUpdate = [
            'username'       => $username,
            'cedula'         => $cedula,
            'tipo_cedula'    => $tipo_cedula,
            'nombre'         => $nombre,
            'apellido'       => $apellido,
            'rol'            => $rol,
            'laboratorio_id' => (int)$id_laboratorio 
        ];

        if (!empty($nuevaClave)) {
            $datosUpdate['password'] = password_hash($nuevaClave, PASSWORD_DEFAULT);
        }

        $logPregunta = "";
        if ($eliminarPregunta === '1') {
            $datosUpdate['pregunta_seguridad']  = null;
            $datosUpdate['respuesta_seguridad'] = null;
            $logPregunta = " y se eliminaron sus credenciales de seguridad";
        }

        $usuarioModel->updateUsuario($id, $datosUpdate);

        $this->registrarBitacora('Modificación Completa de Usuario', 'Administración', "Se actualizaron los datos del usuario '" . $username . "'" . $logPregunta);

        return redirect()->to(base_url('usuarios'))->with('success', 'Información de usuario actualizada con éxito.');
    }

    /**
     * Cambia el estado (habilitar/deshabilitar) de un usuario.
     * 
     * No permite deshabilitar la propia cuenta.
     *
     * @param int $id ID del usuario.
     * @return \CodeIgniter\HTTP\RedirectResponse Redirección con mensaje.
     * 
     * @example
     * GET /usuarios/deshabilitar/5
     */

    public function deshabilitar($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        if (!$this->verificarAccesoGestion()) return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'Acceso denegado.');

        if ($id == session()->get('usuario_id')) return redirect()->to(base_url('usuarios'))->with('error', 'Acción denegada: No puedes deshabilitar tu propia cuenta.');

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->findById($id);

        if (!$usuario) return redirect()->to(base_url('usuarios'))->with('error', 'Usuario no registrado.');

        $nuevoEstado = ($usuario['status'] == 1) ? 0 : 1;
        $accionTexto = ($nuevoEstado == 1) ? 'Habilitar' : 'Deshabilitar';

        $usuarioModel->updateUsuario($id, ['status' => $nuevoEstado]);

        $this->registrarBitacora(
            $accionTexto . ' Usuario', 
            'Administración', 
            "Se cambió el estado de " . $usuario['username'] . " a " . ($nuevoEstado == 1 ? 'Activo' : 'Inactivo')
        );

        return redirect()->to(base_url('usuarios'))->with('success', "Estado modificado a '" . ($nuevoEstado == 1 ? 'Activo' : 'Inactivo') . "' con éxito.");
    }   

    /**
     * Elimina físicamente un usuario de la base de datos.
     * 
     * No permite eliminar la propia cuenta.
     *
     * @param int $id ID del usuario a eliminar.
     * @return \CodeIgniter\HTTP\RedirectResponse Redirección con mensaje.
     * 
     * @example
     * GET /usuarios/eliminar/5
     */

    public function eliminar($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        if (!$this->verificarAccesoGestion()) return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'Acceso denegado.');

        if ($id == session()->get('usuario_id')) return redirect()->to(base_url('usuarios'))->with('error', 'Acción denegada: No puedes eliminar tu propia cuenta del sistema.');

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->findById($id);

        if (!$usuario) return redirect()->to(base_url('usuarios'))->with('error', 'El usuario que intentas eliminar no existe.');

        $usuarioModel->deleteUsuario($id);

        $this->registrarBitacora(
            'Eliminar Usuario Definitivamente', 
            'Administración', 
            "Se eliminó de forma permanente al usuario: " . $usuario['username']
        );

        return redirect()->to(base_url('usuarios'))->with('usuario_eliminado', 'El expediente del usuario ha sido borrado físicamente del sistema.');
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     * 
     * Carga la lista de departamentos para el selector.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string Vista o redirección.
     * 
     * @example
     * GET /usuarios/crear
     */

    public function crear()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        if (!$this->verificarAccesoGestion()) return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'Acceso denegado.');

        $deptModel = new DepartamentoModel();
        $data['departamentos'] = $deptModel->getDepartamentos();

        return view('usuarios/crear', $data);
    }

    /**
     * Procesa la creación de un nuevo usuario.
     * 
     * Valida todos los campos, verifica cédula única y guarda en la BD.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse Redirección con mensaje.
     * 
     * @example
     * POST /usuarios/guardar (con datos del formulario)
     */

    public function guardar()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        if (!$this->verificarAccesoGestion()) return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'Acceso denegado.');

        $usuarioModel = new UsuarioModel();

        $username       = trim($this->request->getPost('username') ?? '');
        $cedula         = trim($this->request->getPost('cedula') ?? '');
        $tipo_cedula    = $this->request->getPost('tipo_cedula') ?? '';
        $nombre         = trim($this->request->getPost('nombre') ?? '');
        $apellido       = trim($this->request->getPost('apellido') ?? '');
        $rol            = $this->request->getPost('rol') ?? '';
        $password       = $this->request->getPost('password') ?? '';
        $id_laboratorio = $this->request->getPost('id_laboratorio') ?? '';

        // ---- Validaciones de servidor ----
        if (empty($username)) return redirect()->back()->with('error', 'Falta el campo: Nombre de Usuario (username)')->withInput();
        if (strlen($username) < 3) return redirect()->back()->with('error', 'El nombre de usuario debe tener al menos 3 caracteres.')->withInput();
        if (strpos($username, ' ') !== false) return redirect()->back()->with('error', 'El nombre de usuario no puede contener espacios.')->withInput();

        if (empty($cedula)) return redirect()->back()->with('error', 'Falta el campo: Cédula de Identidad (cedula)')->withInput();
        if (!ctype_digit($cedula) || strlen($cedula) < 6 || strlen($cedula) > 10) {
        return redirect()->back()->with('error', 'La cédula debe tener entre 6 y 10 dígitos numéricos.')->withInput();
        }

        if (empty($tipo_cedula)) return redirect()->back()->with('error', 'Falta el campo: Tipo de Cédula (tipo_cedula)')->withInput();
        if (!in_array($tipo_cedula, ['V', 'E'])) return redirect()->back()->with('error', 'Tipo de cédula inválido. Debe ser V o E.')->withInput();

        if (empty($nombre)) return redirect()->back()->with('error', 'Falta el campo: Nombre')->withInput();
        if (strlen($nombre) > 25) return redirect()->back()->with('error', 'El nombre no puede exceder los 25 caracteres.')->withInput();

        if (empty($apellido)) return redirect()->back()->with('error', 'Falta el campo: Apellido')->withInput();
        if (strlen($apellido) > 25) return redirect()->back()->with('error', 'El apellido no puede exceder los 25 caracteres.')->withInput();

        if (empty($rol)) return redirect()->back()->with('error', 'Falta el campo: Rol / Permiso (rol)')->withInput();
        if (empty($password)) return redirect()->back()->with('error', 'Falta el campo: Contraseña (password)')->withInput();
        if (strlen($password) < 6) return redirect()->back()->with('error', 'La contraseña debe tener al menos 6 caracteres.')->withInput();

        if (empty($id_laboratorio)) return redirect()->back()->with('error', 'Falta el campo: Laboratorio (id_laboratorio)')->withInput();

        if ($usuarioModel->existeCedula($cedula)) {
            return redirect()->back()->with('error', "Error: La cédula {$cedula} ya se encuentra registrada.")->withInput();
        }
        if (strpos($password, ' ') !== false) {
            return redirect()->back()->with('error', 'La contraseña no puede contener espacios en blanco.')->withInput();
        }

        $datosNuevo = [
            'username'       => $username,
            'cedula'         => $cedula,
            'tipo_cedula'    => $tipo_cedula,
            'nombre'         => $nombre,
            'apellido'       => $apellido,
            'rol'            => $rol,
            'password'       => password_hash($password, PASSWORD_DEFAULT),
            'laboratorio_id' => (int)$id_laboratorio,
            'status'         => 1
        ];

        $usuarioModel->insertUsuario($datosNuevo);

        $this->registrarBitacora('Creación de Usuario', 'Administración', "Se creó exitosamente el usuario: " . $username);

        return redirect()->to(base_url('usuarios'))->with('success', 'Usuario creado e incorporado con éxito.');
    }

    /**
     * Endpoint AJAX para obtener los laboratorios de un departamento.
     * 
     * Retorna JSON con la lista de laboratorios.
     *
     * @param int|null $departamento_id ID del departamento.
     * @return \CodeIgniter\HTTP\Response JSON.
     * 
     * @example
     * GET /usuarios/obtener_laboratorios_por_depto/3
     */

    public function obtener_laboratorios_por_depto($departamento_id = null)
    {
        if (ob_get_length()) ob_clean();

        $this->response->setContentType('application/json');
        $id = (int)$departamento_id;
        
        if ($id <= 0) return $this->response->setJSON([]);

        $labModel = new LaboratorioModel();
        $laboratorios = $labModel->getLaboratoriosFiltrados($id);

        $resultado = [];
        if (!empty($laboratorios)) {
            foreach ($laboratorios as $lab) {
                $resultado[] = [
                    'id'     => $lab['id'],
                    'nombre' => $lab['nombre']
                ];
            }
        }

        return $this->response->setJSON($resultado);
    }

    /**
     * Muestra el formulario para configurar la pregunta de seguridad.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string Vista o redirección.
     * 
     * @example
     * GET /usuarios/configurar_pregunta
     */
    public function configurar_pregunta()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        return view('login/configurar_pregunta');
    }

 
    /**
     * Guarda la pregunta y respuesta de seguridad del usuario autenticado.
     * 
     * La respuesta se almacena hasheada.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse Redirección según el rol.
     * 
     * @example
     * POST /usuarios/guardar_pregunta (con pregunta_seguridad y respuesta_seguridad)
     */
    public function guardar_pregunta()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $session = session();
        $idUsuario = $session->get('usuario_id');

        $pregunta  = $this->request->getPost('pregunta_seguridad');
        $respuesta = $this->request->getPost('respuesta_seguridad');

        if (empty($pregunta) || empty($respuesta)) return redirect()->back()->with('error', 'Debes seleccionar una pregunta y escribir tu respuesta.');

        $usuarioModel = new UsuarioModel();
        $usuarioModel->updateUsuario($idUsuario, [
            'pregunta_seguridad'  => $pregunta,
            'respuesta_seguridad' => password_hash($respuesta, PASSWORD_DEFAULT)
        ]);

        $this->registrarBitacora(
            'Configuración Inicial de Seguridad', 
            'Seguridad', 
            "El usuario " . $session->get('username') . " estableció su pregunta de recuperación con éxito."
        );

        $rol = session()->get('rol');
        switch ($rol) {
            case 'proteccion_integral':
                $destino = 'dashboard';
                break;
            case 'administrador':
                $destino = 'usuarios/bitacora';
                break;
            default:
                $destino = 'desechos/registroSolicitudes';
        }

        return redirect()->to(base_url($destino))->with('success', 'Pregunta de seguridad guardada correctamente.');
    }

    /**
     * Procesa el cambio de contraseña del usuario autenticado.
     * 
     * Verifica la contraseña actual, valida coincidencia de nueva y confirmación,
     * y actualiza el hash en la base de datos.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse Redirección con mensaje.
     * 
     * @example
     * POST /usuarios/cambiar_password_post (con current_password, new_password, confirm_password)
     */
    public function cambiar_password_post()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $session = session();
        $userId = $session->get('usuario_id');

        $currentPassword = $this->request->getPost('current_password');
        $newPassword     = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return redirect()->back()->with('password_error', 'Todos los campos son obligatorios.');
        }

        if ($newPassword !== $confirmPassword) {
            return redirect()->back()->with('password_error', 'La nueva contraseña y su confirmación no coinciden.');
        }

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->findById($userId);

        if (!$usuario || !password_verify($currentPassword, $usuario['password'])) {
            return redirect()->back()->with('password_error', 'La contraseña actual es incorrecta.');
        }

        $usuarioModel->updateUsuario($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);

        $this->registrarBitacora(
            'Cambio de Contraseña',
            'Seguridad',
            "El usuario '" . $usuario['username'] . "' modificó con éxito sus credenciales de acceso."
        );

        return redirect()->back()->with('success', '¡Tu contraseña ha sido actualizada correctamente!');
    }

        /**
     * !!! Muestra una vista de solicitud de desechos.
     * 
     * Actualmente solo retorna la vista 'Usuarios/SolicitudDesechos' sin lógica adicional.
     * Posiblemente es un método heredado o no implementado completamente.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string Vista o redirección.
     * 
     * @example
     * GET /usuarios/solicitudDesechos
     */
    public function solicitudDesechos()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        return view('Usuarios/SolicitudDesechos');
    }

        /**
     * !!! Procesa una solicitud de desechos (método placeholder).
     * 
     * Solo redirige hacia atrás con mensaje de éxito, sin realizar ninguna operación.
     * Probablemente es un stub o no está implementado.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse Redirección con mensaje.
     * 
     * @example
     * POST /usuarios/procesarSolicitud
     */
    public function procesarSolicitud()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        return redirect()->back()->with('success', 'Solicitud registrada correctamente.');
    }
}