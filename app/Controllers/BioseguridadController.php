<?php

namespace App\Controllers;

use App\Models\SolicitudBioseguridadModel;
use App\Models\UsuarioModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class BioseguridadController extends BaseController
{
    //crear Solicitud
    public function crear()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $usuarioModel = new UsuarioModel();
        $solicitudModel = new SolicitudBioseguridadModel();

        $userId = session()->get('usuario_id');
        $usuario = $usuarioModel->findById($userId);
        $usuario['departamento'] = $usuario['departamento'] ?? 'No Asignado';
        $usuario['nombre_laboratorio'] = $usuario['nombre_laboratorio'] ?? 'No Asignado';

        $data = [
            'usuario_data'      => $usuario,
            'codigo_automatico' => $solicitudModel->generarCodigoUnico(),
            'fecha_automatica'  => date('d/m/Y')
        ];

        return view('bioseguridad/formulario', $data);
    }

    //formulario en la BD
    public function registrar()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $solicitudModel = new SolicitudBioseguridadModel();

        $contenedores = (int)$this->request->getPost('contenedores_pulso_cantidad');
        if ($contenedores > 3) {
            return redirect()->back()->with('error', 'La cantidad de Contenedores Pulso Cortante no puede superar 3.');
        }

        $peq = (int)$this->request->getPost('bolsas_rojas_pequena');
        $med = (int)$this->request->getPost('bolsas_rojas_mediana');
        $gra = (int)$this->request->getPost('bolsas_rojas_grande');

        if ($peq > 10 || $med > 10 || $gra > 10) {
            return redirect()->back()->with('error', 'Cada tamaño de bolsa roja tiene un límite máximo de 10 unidades.');
        }

        $quienRetira = $this->request->getPost('quien_retira');
        $nombreOtra = ($quienRetira === 'otra_persona') ? $this->request->getPost('nombre_otra_persona') : null;

        $codigo = $this->request->getPost('codigo_solicitud');
        if (empty($codigo)) {
            $codigo = $solicitudModel->generarCodigoUnico();
        }

        $insertData = [
            'codigo_solicitud'            => $codigo,
            'usuario_id'                  => session()->get('usuario_id'),
            'ext_telefono'                => $this->request->getPost('ext_telefono'),
            'contenedores_pulso_cantidad' => $contenedores,
            'bolsas_rojas_pequena'        => $peq,
            'bolsas_rojas_mediana'        => $med,
            'bolsas_rojas_grande'         => $gra,
            'quien_retira'                => $quienRetira,
            'nombre_otra_persona'         => $nombreOtra,
            // No se guarda ruta_pdf
        ];

        if ($solicitudModel->insertarSolicitud($insertData)) {
            $this->registrarBitacora('Registro de Solicitud', 'Servicio Bioseguridad', "Se generó la solicitud: " . $codigo);
            return redirect()->to(base_url('desechos/registroSolicitudes'))->with('success', 'Solicitud de Bioseguridad registrada correctamente.');
        } else {
            return redirect()->back()->with('error', 'Error al guardar en la base de datos.');
        }
    }

    public function generarPdf($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $solicitudModel = new SolicitudBioseguridadModel();
        $solicitud = $solicitudModel->find($id);

        if (!$solicitud) {
            return redirect()->back()->with('error', 'Solicitud no encontrada.');
        }

        $usuarioModel = new \App\Models\UsuarioModel();
        $usuario = $usuarioModel->findById($solicitud['usuario_id']);

        // ✅ Nombre completo
        $nombreCompleto = trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''));
        if (empty($nombreCompleto)) {
            $nombreCompleto = $usuario['username'] ?? 'Usuario';
        }

        $data = $solicitud;
        $data['usuario_nombre'] = $nombreCompleto;
        $data['departamento']   = $usuario['departamento'] ?? 'No asignado';
        $data['laboratorio']    = $usuario['nombre_laboratorio'] ?? 'No asignado';
        $data['fecha_registro'] = date('d/m/Y H:i:s', strtotime($solicitud['fecha_registro']));

        $html = view('bioseguridad/plantilla_pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream('bioseguridad_' . $solicitud['codigo_solicitud'] . '.pdf', ['Attachment' => false]);
        exit;
    }

    public function verPdf($nombreArchivo)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        $ruta = FCPATH . 'uploads/pdfs/' . $nombreArchivo;
        if (file_exists($ruta)) {
            if (ob_get_length()) ob_end_clean();
            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="' . $nombreArchivo . '"')
                ->setHeader('Content-Length', filesize($ruta))
                ->setBody(file_get_contents($ruta));
        } else {
            return "El archivo no existe en: " . $ruta;
        }
    }

    public function editar($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $solicitudModel = new SolicitudBioseguridadModel();
        $solicitud = $solicitudModel->find($id);

        if (!$solicitud) {
            return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'Solicitud no encontrada.');
        }

        if ($solicitud['editado'] == 1) {
        return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'Esta solicitud ya fue editada anteriormente. No se permite volver a editar.');
        }

        // Verificar permisos (creador o administrador)
        if (session()->get('usuario_id') != $solicitud['usuario_id'] && session()->get('rol') !== 'administrador') {
            return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'No tienes permiso para editar esta solicitud.');
        }


        if (session()->get('usuario_id') != $solicitud['usuario_id'] && session()->get('rol') !== 'administrador') {
            return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'No tienes permiso para editar esta solicitud.');
        }

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->findById($solicitud['usuario_id']);

        $data = [
            'usuario_data'      => $usuario,
            'solicitud'         => $solicitud,
            'codigo_automatico' => $solicitud['codigo_solicitud'],
            'fecha_automatica'  => date('d/m/Y', strtotime($solicitud['fecha_registro'])),
            'modo_edicion'      => true,
            'id_solicitud'      => $id
        ];

        return view('bioseguridad/editar', $data);
    }

    public function actualizar($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $solicitudModel = new SolicitudBioseguridadModel();
        $solicitud = $solicitudModel->find($id);

        if (!$solicitud) {
            return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'Solicitud no encontrada.');
        }

        if ($solicitud['editado'] == 1) {
        return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'Esta solicitud ya fue editada anteriormente.');
        }

        if (session()->get('usuario_id') != $solicitud['usuario_id'] && session()->get('rol') !== 'administrador') {
            return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'No tienes permiso para editar esta solicitud.');
        }

        if (session()->get('usuario_id') != $solicitud['usuario_id'] && session()->get('rol') !== 'administrador') {
            return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'No tienes permiso para editar esta solicitud.');
        }

        $contenedores = (int)$this->request->getPost('contenedores_pulso_cantidad');
        if ($contenedores > 3) {
            return redirect()->back()->with('error', 'La cantidad de Contenedores Pulso Cortante no puede superar 3.');
        }

        $peq = (int)$this->request->getPost('bolsas_rojas_pequena');
        $med = (int)$this->request->getPost('bolsas_rojas_mediana');
        $gra = (int)$this->request->getPost('bolsas_rojas_grande');

        if ($peq > 10 || $med > 10 || $gra > 10) {
            return redirect()->back()->with('error', 'Cada tamaño de bolsa roja tiene un límite máximo de 10 unidades.');
        }

        $quienRetira = $this->request->getPost('quien_retira');
        $nombreOtra = ($quienRetira === 'otra_persona') ? $this->request->getPost('nombre_otra_persona') : null;

        $updateData = [
            'ext_telefono'                => $this->request->getPost('ext_telefono'),
            'contenedores_pulso_cantidad' => $contenedores,
            'bolsas_rojas_pequena'        => $peq,
            'bolsas_rojas_mediana'        => $med,
            'bolsas_rojas_grande'         => $gra,
            'quien_retira'                => $quienRetira,
            'nombre_otra_persona'         => $nombreOtra,
            'editado' => 1,
        ];

        $solicitudModel->update($id, $updateData);

        $this->registrarBitacora('Edición de Solicitud', 'Servicio Bioseguridad', "Se editó la solicitud: " . $solicitud['codigo_solicitud']);

        return redirect()->to(base_url('desechos/registroSolicitudes'))->with('success', 'Solicitud actualizada correctamente.');
    }
}