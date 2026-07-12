<?php
/**
 * Controlador de autenticación y gestión de sesiones.
 * 
 * Maneja el inicio de sesión, cierre de sesión, recuperación de contraseña
 * mediante pregunta de seguridad y redirección basada en roles.
 * 
 * Flujo típico:
 * 1. El usuario accede a /login (index) para ver el formulario.
 * 2. Envía credenciales a /autenticar.
 * 3. Si es exitoso y es primer ingreso sin pregunta seguridad, se redirige a configurar_pregunta.
 * 4. Si tiene pregunta, se redirige según rol.
 * 5. Si olvida contraseña, va a /olvideContrasena -> envía cédula a /validarCedula -> responde pregunta en /responder_pregunta -> envía nueva clave a /nuevaClave.
 * 6. Cierre de sesión en /salir.
 */
namespace App\Controllers;

use App\Models\UsuarioModel;

class Login extends BaseController
{
    /**
     * Muestra el formulario de inicio de sesión.
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse|string Vista 'login/login'
     */
    public function index()
    {
        return view('login/login');
    }
     /**
     * Procesa las credenciales de login.
     * 
     * - Valida username y password contra la base de datos.
     * - Verifica estado del usuario (0 = deshabilitado).
     * - Crea sesión con datos del usuario.
     * - Registra bitácora de inicio exitoso.
     * - Si el usuario no tiene pregunta de seguridad configurada (primer ingreso),
     *   redirige a configurar pregunta.
     * - Redirige según el rol:
     *   - 'proteccion_integral' → /dashboard
     *   - 'administrador' → /usuarios/bitacora
     *   - Otros (PAI, TAI, Jefe_Laboratorio, Auxiliar) → /desechos/registroSolicitudes
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse Redirección según resultado.
     * 
     * @example
     * POST /login/autenticar con campos 'username' y 'password'
     */
    public function autenticar()
    {
        $session = session();
        $usuarioModel = new UsuarioModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $usuario = $usuarioModel->findByUsername($username);

        if ($usuario && password_verify($password, $usuario['password'])) 
        {
            if ($usuario['status'] == 0) {
                return redirect()->back()->with('error', 'Tu usuario está deshabilitado. Contacta al administrador.');
            }

            $session->set([
                'usuario_id' => $usuario['id'],
                'username'   => $usuario['username'],
                'rol'        => $usuario['rol'],
                'logged_in'  => true
            ]);

            $session->setFlashdata('mostrar_bienvenida', true);

            $this->registrarBitacora('Inició sesión con éxito', 'Sesión', "El usuario {$username} ingresó al sistema.");

            // Verificar pregunta de seguridad
            if (empty($usuario['pregunta_seguridad']) || empty($usuario['respuesta_seguridad'])) {
                return redirect()->to(base_url('usuarios/configurar_pregunta'))
                                 ->with('info', 'Por ser tu primer ingreso, debes configurar una pregunta de seguridad.');
            }

            // Redirigir según el rol
            $rol = $usuario['rol'];
            switch ($rol) {
                case 'proteccion_integral':
                    return redirect()->to(base_url('dashboard'));
                case 'administrador':
                    return redirect()->to(base_url('usuarios/bitacora'));
                default:
                    // PAI, TAI, Jefe_Laboratorio, Auxiliar
                    return redirect()->to(base_url('desechos/registroSolicitudes'));
            }
        }

        return redirect()->to(base_url('login'))->with('error', 'Usuario o contraseña incorrectos.');
    }
    /**
     * Cierra la sesión del usuario actual.
     * 
     * - Registra en bitácora el cierre de sesión.
     * - Destruye la sesión.
     * - Redirige a login.
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse Redirección a /login
     * 
     * @example
     * GET /login/salir
     */
    public function salir()
    {
        $session = session();
        $this->registrarBitacora('Cerró sesión', 'Sesión', "El usuario " . $session->get('username') . " salió del sistema.");
        $session->destroy();

        return redirect()->to(base_url('login'));
    }
    /**
     * Muestra el formulario para solicitar recuperación de contraseña.
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse|string Vista 'login/olvide_contrasena'
     */
    public function olvideContrasena()
    {
        return view('login/olvide_contrasena');
    }
    /**
     * Valida la cédula ingresada para recuperación de contraseña.
     * 
     * - Busca usuario por cédula.
     * - Si no existe, redirige con error.
     * - Si existe pero no tiene pregunta de seguridad configurada,
     *   registra en bitácora y redirige con error.
     * - Si tiene pregunta, muestra la vista 'login/responder_pregunta'
     *   con el id del usuario y la pregunta.
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse|string Redirección o vista con pregunta.
     * 
     * @example
     * POST /login/validarCedula con campo 'cedula'
     */
    public function validarCedula()
    {
        $usuarioModel = new UsuarioModel();
        $tipo = $this->request->getPost('tipo_cedula');
        $cedula = $this->request->getPost('cedula');

        if (empty($tipo) || empty($cedula)) {
            return redirect()->back()->with('error', 'Debe seleccionar el tipo de cédula y escribir el número.')->withInput();
        }

        // Validar que la cédula sea numérica y tenga entre 6 y 10 dígitos
        if (!ctype_digit($cedula) || strlen($cedula) < 6 || strlen($cedula) > 10) {
            return redirect()->back()->with('error', 'La cédula debe tener entre 6 y 10 dígitos numéricos.')->withInput();
        }

        // Buscar por tipo y número de cédula
        $usuario = $usuarioModel->findByTipoCedula($tipo, $cedula);

        if (!$usuario) {
            return redirect()->to(base_url('login'))->with('error', 'La cédula ingresada no coincide con ningún usuario registrado.');
        }

        if (empty($usuario['pregunta_seguridad']) || empty($usuario['respuesta_seguridad'])) {
            $this->registrarBitacora('Intento recuperar clave sin pregunta', 'Seguridad', "Cédula {$tipo}-{$cedula} no posee preguntas configuradas.");
            return redirect()->to(base_url('login'))->with('error', 'Tu usuario no posee una pregunta de seguridad registrada. No puedes restablecer tu clave ni iniciar sesión. Contacta al administrador.');
        }

        return view('login/responder_pregunta', [
            'usuario_id' => $usuario['id'],
            'pregunta'   => $usuario['pregunta_seguridad']
        ]);
    }
    /**
     * Procesa la respuesta a la pregunta de seguridad y establece nueva contraseña.
     * 
     * - Valida que todos los campos estén presentes.
     * - Busca el usuario por id.
     * - Verifica que nueva contraseña y confirmación coincidan.
     * - Verifica que la respuesta de seguridad sea correcta (password_verify).
     * - Actualiza la contraseña en base de datos (hash).
     * - Registra en bitácora la recuperación exitosa.
     * - Redirige a login con mensaje de éxito.
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse|string Redirección o vista con error (si falla verificación).
     * 
     * @example
     * POST /login/nuevaClave con campos:
     *   'usuario_id', 'respuesta_seguridad', 'password', 'confirm_password'
     */
    public function nuevaClave()
    {
        $usuarioModel = new UsuarioModel();

        $idUsuario        = $this->request->getPost('usuario_id');
        $respuestaEnviada = $this->request->getPost('respuesta_seguridad');
        $nuevaClave       = $this->request->getPost('password');
        $confirmarClave   = $this->request->getPost('confirm_password');

        if (empty($idUsuario) || empty($respuestaEnviada) || empty($nuevaClave)) {
            return redirect()->to(base_url('login'))->with('error', 'Datos de recuperación incompletos.');
        }

        $usuario = $usuarioModel->findById($idUsuario);
        if (!$usuario) {
            return redirect()->to(base_url('login'))->with('error', 'Usuario no válido.');
        }

        if ($nuevaClave !== $confirmarClave) {
            return $this->preguntaSegError($idUsuario, $usuario['pregunta_seguridad'], 'Las contraseñas ingresadas no coinciden.');
        }

        if (!password_verify($respuestaEnviada, $usuario['respuesta_seguridad'])) {
            return $this->preguntaSegError($idUsuario, $usuario['pregunta_seguridad'], 'La respuesta a la pregunta de seguridad es incorrecta.');
        }
       
        $usuarioModel->updateUsuario($idUsuario, [
            'password' => password_hash($nuevaClave, PASSWORD_DEFAULT)
        ]);

        $this->registrarBitacora(
            'Recuperación de Contraseña Exitosa', 
            'Seguridad', 
            "El usuario {$usuario['username']} restableció su contraseña correctamente mediante verificación de seguridad."
        );

        return redirect()->to(base_url('login'))->with('success', 'Contraseña restablecida con éxito. Ya puedes iniciar sesión con tu nueva clave.');
    }
    /**
     * Método privado para reenviar la vista de pregunta de seguridad con un mensaje de error.
     * 
     * @param int    $idUsuario    ID del usuario.
     * @param string $pregunta     Texto de la pregunta de seguridad.
     * @param string $mensajeError Mensaje de error a mostrar.
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse|string Vista 'login/responder_pregunta' con datos.
     * 
     * @example
     * $this->preguntaSegError(1, '¿Nombre de tu mascota?', 'Respuesta incorrecta');
     */
    private function preguntaSegError($idUsuario, $pregunta, $mensajeError)
    {
        return view('login/responder_pregunta', [
            'usuario_id' => $idUsuario,
            'pregunta'   => $pregunta,
            'error'      => $mensajeError
        ]);
    }
}