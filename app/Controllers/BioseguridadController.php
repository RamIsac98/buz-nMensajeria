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
        $totalBolsas = $peq + $med + $gra;
        if ($totalBolsas > 10) {
            return redirect()->back()->with('error', 'El total de Bolsas Rojas no puede superar 10 unidades.');
        }

        $quienRetira = $this->request->getPost('quien_retira');
        $nombreOtra = ($quienRetira === 'otra_persona') ? $this->request->getPost('nombre_otra_persona') : null;

        $codigo = $this->request->getPost('codigo_solicitud');
        
        if (empty($codigo)) {
            $codigo = $solicitudModel->generarCodigoUnico();
            log_message('warning', 'Código de solicitud vacío, se generó uno nuevo: ' . $codigo);
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
        ];

        // Datos para PDF
        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->findById(session()->get('usuario_id'));
        $pdfData = $insertData;
        $pdfData['usuario_nombre'] = $usuario['username'] ?? 'Usuario';
        $pdfData['departamento']   = $usuario['departamento'] ?? 'No asignado';
        $pdfData['laboratorio']    = $usuario['nombre_laboratorio'] ?? 'No asignado';
        $pdfData['fecha_registro'] = date('d/m/Y H:i:s');

        $nombrePdf = 'bioseguridad_' . $codigo . '.pdf';
        $rutaRelativa = 'uploads/pdfs/' . $nombrePdf;
        $pdfData['ruta_pdf'] = $rutaRelativa;
        $insertData['ruta_pdf'] = $rutaRelativa;

        if ($solicitudModel->insertarSolicitud($insertData)) {
            $this->generarPDF($pdfData, $nombrePdf);
            $this->registrarBitacora('Registro de Solicitud', 'Servicio Bioseguridad', "Se generó la solicitud: " . $codigo);
            return redirect()->to(base_url('desechos/registroSolicitudes'))->with('success', 'Solicitud de Bioseguridad y PDF registrados correctamente.');
        } else {
            return redirect()->back()->with('error', 'Error al guardar en la base de datos.');
        }
    }

    private function generarPDF($data, $nombrePdf)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $html = view('bioseguridad/plantilla_pdf', $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $directorio = FCPATH . 'uploads/pdfs/';
        if (!is_dir($directorio)) mkdir($directorio, 0777, true);
        file_put_contents($directorio . $nombrePdf, $dompdf->output());
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
}