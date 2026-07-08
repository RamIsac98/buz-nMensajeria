<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\DepartamentoModel;
use App\Models\LaboratorioModel;

class Usuarios extends BaseController
{
    // Método para verificar acceso (administrador o protección integral)
    private function verificarAccesoGestion(): bool
    {
        $rol = session()->get('rol');
        return ($rol === 'administrador' || $rol === 'proteccion_integral');
    }

    public function index()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $rol = session()->get('rol');
        if (!in_array(session()->get('rol'), ['administrador', 'proteccion_integral'])) return redirect()->to(base_url('interfaz_usuario_inicial'))->with('error', 'Acceso denegado: No tienes permisos.');

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

    public function generarPdfUsuarios()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        if (!$this->verificarAccesoGestion()) {
            return redirect()->to(base_url('interfaz_usuario_inicial'))
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

        $html = view('usuarios/usuarios_pdf', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $dompdf->stream("Reporte_Usuarios_Paginas_{$paginaInicio}_al_{$paginaFin}.pdf", ["Attachment" => true]);
    }
    public function editar($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        if (!$this->verificarAccesoGestion()) {
            return redirect()->to(base_url('interfaz_usuario_inicial'))
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

        public function actualizar($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        if (!$this->verificarAccesoGestion()) {
            return redirect()->to(base_url('interfaz_usuario_inicial'))
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
        if (!ctype_digit($cedula) || strlen($cedula) !== 8) return redirect()->back()->with('error', 'La cédula debe ser un número de 8 dígitos.')->withInput();

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
            return redirect()->back()->with('error', "La cédula '{$cedula}' ya pertenece a otro usuario registrado.");
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

    public function deshabilitar($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        if (!$this->verificarAccesoGestion()) return redirect()->to(base_url('interfaz_usuario_inicial'))->with('error', 'Acceso denegado.');

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

    public function eliminar($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        if (!$this->verificarAccesoGestion()) return redirect()->to(base_url('interfaz_usuario_inicial'))->with('error', 'Acceso denegado.');

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

    public function crear()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        if (!$this->verificarAccesoGestion()) return redirect()->to(base_url('interfaz_usuario_inicial'))->with('error', 'Acceso denegado.');

        $deptModel = new DepartamentoModel();
        $data['departamentos'] = $deptModel->getDepartamentos();

        return view('usuarios/crear', $data);
    }

    public function guardar()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        if (!$this->verificarAccesoGestion()) return redirect()->to(base_url('interfaz_usuario_inicial'))->with('error', 'Acceso denegado.');

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
        if (!ctype_digit($cedula) || strlen($cedula) !== 8) return redirect()->back()->with('error', 'La cédula debe ser un número de 8 dígitos.')->withInput();

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
            return redirect()->back()->with('error', "Error: La cédula '{$cedula}' ya se encuentra registrada.")->withInput();
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

    public function configurar_pregunta()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        return view('login/configurar_pregunta');
    }

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

        return redirect()->back()->with('password_success', '¡Tu contraseña ha sido actualizada correctamente!');
    }

    public function solicitudDesechos()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        return view('Usuarios/SolicitudDesechos');
    }

    public function procesarSolicitud()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        return redirect()->back()->with('success', 'Solicitud registrada correctamente.');
    }
}