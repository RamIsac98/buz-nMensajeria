<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class Login extends BaseController
{
    public function index()
    {
        return view('login/login');
    }

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

    public function salir()
    {
        $session = session();
        $this->registrarBitacora('Cerró sesión', 'Sesión', "El usuario " . $session->get('username') . " salió del sistema.");
        $session->destroy();

        return redirect()->to(base_url('login'));
    }

    public function olvideContrasena()
    {
        return view('login/olvide_contrasena');
    }

    public function validarCedula()
    {
        $usuarioModel = new UsuarioModel();
        $cedula = $this->request->getPost('cedula');

        if (empty($cedula)) {
            return redirect()->back()->with('error', 'El campo cédula es obligatorio.');
        }

        $usuario = $usuarioModel->findByCedula($cedula);

        if (!$usuario) {
            return redirect()->to(base_url('login'))->with('error', 'La cédula ingresada no coincide con ningún usuario registrado.');
        }

        if (empty($usuario['pregunta_seguridad']) || empty($usuario['respuesta_seguridad'])) {
            $this->registrarBitacora('Intento recuperar clave sin pregunta', 'Seguridad', "Cédula {$cedula} no posee preguntas configuradas.");
            return redirect()->to(base_url('login'))->with('error', 'Tu usuario no posee una pregunta de seguridad registrada. No puedes restablecer tu clave ni iniciar sesión. Contacta al administrador.');
        }

        return view('login/responder_pregunta', [
            'usuario_id' => $usuario['id'],
            'pregunta'   => $usuario['pregunta_seguridad']
        ]);
    }

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

    private function preguntaSegError($idUsuario, $pregunta, $mensajeError)
    {
        return view('login/responder_pregunta', [
            'usuario_id' => $idUsuario,
            'pregunta'   => $pregunta,
            'error'      => $mensajeError
        ]);
    }
}