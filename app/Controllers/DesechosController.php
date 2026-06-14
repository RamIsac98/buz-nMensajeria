<?php

namespace App\Controllers;

use App\Models\SolicitudDesechosModel;
use App\Models\UsuarioModel;

use Dompdf\Dompdf;
use Dompdf\Options;

class DesechosController extends BaseController
{
    public function crear()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $usuarioModel = new UsuarioModel();
        $solicitudModel = new SolicitudDesechosModel();

        $userId = session()->get('usuario_id');
        $usuario = $usuarioModel->findById($userId);

        $usuario['departamento'] = $usuario['departamento'] ?? 'No Asignado';
        $usuario['nombre_laboratorio'] = $usuario['nombre_laboratorio'] ?? 'No Asignado';

        $data = [
            'usuario_data'      => $usuario,
            'codigo_automatico' => $solicitudModel->generarCodigoUnico(),
            'fecha_automatica'  => date('d/m/Y')
        ];

        return view('desechos/formulario', $data);
    }

    public function registrar()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $solicitudModel = new SolicitudDesechosModel();

        $postTipos     = $this->request->getPost('tipo_desecho') ?? [];
        $postVariantes = $this->request->getPost('variante_desecho') ?? [];
        $postEstado    = $this->request->getPost('estado_fisico') ?? [];
        $postEmpaque   = $this->request->getPost('tipo_empaque') ?? [];
        
        $codigoSolicitud = $this->request->getPost('codigo_solicitud');

        $insertData = [
            'codigo_solicitud'         => $codigoSolicitud,
            'usuario_id'               => session()->get('usuario_id'),
            'ext_telefono'             => $this->request->getPost('ext_telefono'),
            'tipos_desecho'            => is_array($postTipos) ? implode(', ', $postTipos) : '',
            'variantes_desecho'        => is_array($postVariantes) ? implode(', ', $postVariantes) : '',
            'esterilizado'             => $this->request->getPost('esterilizado') == 'Sí' ? 1 : 0,
            'motivo'                   => $this->request->getPost('motivo'),
            'estado'                   => is_array($postEstado) ? implode(', ', $postEstado) : '',
            'peso_kg'                  => $this->request->getPost('peso_kg') ?: null,
            'peso_l'                   => $this->request->getPost('peso_l') ?: null,
            'tipo_empaque'             => is_array($postEmpaque) ? implode(', ', $postEmpaque) : '',
            'empaque_otro_descripcion' => $this->request->getPost('empaque_otro_descripcion'),
        ];


            // 1. Obtener datos adicionales del usuario para el PDF
        $usuarioModel = new \App\Models\UsuarioModel();
        $usuario = $usuarioModel->findById(session()->get('usuario_id'));
        
        // 2. datos con información extra
        $pdfData = $insertData;
        $pdfData['usuario_nombre'] = $usuario['username'] ?? 'Usuario';
        $pdfData['departamento']   = $usuario['departamento'] ?? 'No asignado';
        $pdfData['laboratorio']    = $usuario['nombre_laboratorio'] ?? 'No asignado';
        $pdfData['fecha_registro'] = date('d/m/Y H:i:s');

        // 3. Generar PDF pasando estos nuevos datos
        $nombrePdf = 'solicitud_' . $insertData['codigo_solicitud'] . '.pdf';
        $rutaRelativa = 'uploads/pdfs/' . $nombrePdf;   // Ruta pública
        $pdfData['ruta_pdf'] = $rutaRelativa;

        $insertData['ruta_pdf'] = $rutaRelativa;
        
        if ($solicitudModel->insertarSolicitud($insertData)) {
            $this->generarPDF($pdfData, $nombrePdf);
            $this->registrarBitacora('Registro de Solicitud', 'Servicio Desechos', "Se generó la solicitud: " . $insertData['codigo_solicitud']);
            return redirect()->to(base_url('desechos/registroSolicitudes'))->with('success', 'Solicitud y PDF registrados correctamente.');
        } else {
            return redirect()->back()->with('error', 'Ocurrió un error en la base de datos al guardar.');
        }
    }

    private function generarPDF($data, $nombrePdf)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $html = view('desechos/plantilla_pdf', $data);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $directorio = FCPATH . 'uploads/pdfs/';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }

        file_put_contents($directorio . $nombrePdf, $dompdf->output());
    }

    public function verPdf($nombreArchivo)
    {
        // 1. Verificación de seguridad: ¿Está logueado?
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        // 2. Ruta absoluta al archivo (asegúrate de que esta ruta sea la real donde se guardan)
        $ruta = FCPATH . 'uploads/pdfs/' . $nombreArchivo;

        // 3. Verificación de existencia real
        if (file_exists($ruta)) {
            // 4. Limpiamos cualquier salida previa (evita archivos corruptos)
            if (ob_get_length()) ob_end_clean();

            // 5. Servimos el archivo usando la respuesta de CodeIgniter
            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="' . $nombreArchivo . '"')
                ->setHeader('Content-Length', filesize($ruta))
                ->setBody(file_get_contents($ruta));
        } else {
            // Esto te dirá exactamente dónde está buscando si falla
            return "El archivo no existe en: " . $ruta;
        }
    }


    public function registroSolicitudes()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        
        $solicitudModel = new SolicitudDesechosModel();
        // Pasamos el array exactamente como lo espera el foreach de la vista
        $data['solicitudes_desechos'] = $solicitudModel->getSolicitudes();
        return view('desechos/registroSolicitudes', $data);
    }
}