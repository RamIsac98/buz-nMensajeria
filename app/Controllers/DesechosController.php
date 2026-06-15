<?php

namespace App\Controllers;

use App\Models\SolicitudDesechosModel;
use App\Models\UsuarioModel;
use App\Models\SolicitudBioseguridadModel;

use Dompdf\Dompdf;
use Dompdf\Options;

class DesechosController extends BaseController
{
    //solicitud desechos
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

    //registro en la BD
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

        //datos extra para el reporte
        $usuarioModel = new \App\Models\UsuarioModel();
        $usuario = $usuarioModel->findById(session()->get('usuario_id'));
        
        $pdfData = $insertData;
        $pdfData['usuario_nombre'] = $usuario['username'] ?? 'Usuario';
        $pdfData['departamento']   = $usuario['departamento'] ?? 'No asignado';
        $pdfData['laboratorio']    = $usuario['nombre_laboratorio'] ?? 'No asignado';
        $pdfData['fecha_registro'] = date('d/m/Y H:i:s');

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

    //configuracion del filtros SolicitudesRegistro
    public function registroSolicitudes()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $desechosModel = new \App\Models\SolicitudDesechosModel();
        $bioseguridadModel = new \App\Models\SolicitudBioseguridadModel();

        // Filtros
        $filtros = [
            'buscar'            => $this->request->getGet('buscar'),
            'tipo_solicitud'    => $this->request->getGet('tipo_solicitud'),
            'estado_solicitud'  => $this->request->getGet('estado_solicitud'),
            'fecha_desde'       => $this->request->getGet('fecha_desde'),
            'fecha_hasta'       => $this->request->getGet('fecha_hasta')
        ];

        $solicitudes = [];
        $total = 0;

        if (empty($filtros['tipo_solicitud']) || $filtros['tipo_solicitud'] == 'Desechos Biológicos') {
            $desechos = $desechosModel->getSolicitudesFiltradas($filtros, 999999, 0);
            foreach ($desechos as &$d) {
                $d['tipo_solicitud'] = 'Desechos Biológicos';
            }
            $solicitudes = array_merge($solicitudes, $desechos);
            $total += $desechosModel->countSolicitudesFiltradas($filtros);
        }
        
        if (empty($filtros['tipo_solicitud']) || $filtros['tipo_solicitud'] == 'Bioseguridad') {
            $bioseg = $bioseguridadModel->getSolicitudesFiltradas($filtros, 999999, 0);
            foreach ($bioseg as &$b) {
                $b['tipo_solicitud'] = 'Bioseguridad';
            }
            $solicitudes = array_merge($solicitudes, $bioseg);
            $total += $bioseguridadModel->countSolicitudesFiltradas($filtros);
        }

        usort($solicitudes, function($a, $b) {
            return strtotime($b['fecha_registro']) - strtotime($a['fecha_registro']);
        });

        // Paginación manual
        $porPagina = 10;
        $pagina = (int)($this->request->getGet('page') ?? 1);
        $offset = ($pagina - 1) * $porPagina;
        $solicitudesPag = array_slice($solicitudes, $offset, $porPagina);
        $totalPages = ceil($total / $porPagina);

        // Mantener filtros en URL
        $currentGet = $_GET;
        unset($currentGet['page']);
        $urlParams = !empty($currentGet) ? '&' . http_build_query($currentGet) : '';

        // Rango de páginas
        $startPage = max(1, $pagina - 1);
        $endPage = min($totalPages, $pagina + 1);
        if ($pagina == 1) $endPage = min($totalPages, 3);
        if ($pagina == $totalPages) $startPage = max(1, $totalPages - 2);

        $data = [
            'solicitudes'        => $solicitudesPag,
            'total'              => $total,
            'porPagina'          => $porPagina,
            'paginaActual'       => $pagina,
            'totalPages'         => $totalPages,
            'startPage'          => $startPage,
            'endPage'            => $endPage,
            'urlParams'          => $urlParams,
            'filtros'            => $filtros,
            'tiposSolicitud'     => ['Desechos Biológicos', 'Bioseguridad'],
            'estadosSolicitud'   => ['Pendiente', 'Entregado', 'Cancelado']
        ];

        return view('desechos/registroSolicitudes', $data);
    }

    public function gestionSolicitudes()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));
        
        // Solo administradores pueden gestionar estados
        if (session()->get('rol') !== 'administrador') {
            return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'No tiene permisos para gestionar solicitudes.');
        }

        $desechosModel = new \App\Models\SolicitudDesechosModel();
        $bioseguridadModel = new \App\Models\SolicitudBioseguridadModel();

        $filtros = [
            'buscar'            => $this->request->getGet('buscar'),
            'tipo_solicitud'    => $this->request->getGet('tipo_solicitud'),
            'estado_solicitud'  => $this->request->getGet('estado_solicitud'),
            'fecha_desde'       => $this->request->getGet('fecha_desde'),
            'fecha_hasta'       => $this->request->getGet('fecha_hasta')
        ];

        $solicitudes = [];
        $total = 0;

        if (empty($filtros['tipo_solicitud']) || $filtros['tipo_solicitud'] == 'Desechos Biológicos') {
            $desechos = $desechosModel->getSolicitudesFiltradas($filtros, 999999, 0);
            foreach ($desechos as &$d) {
                $d['tipo_solicitud'] = 'Desechos Biológicos';
                $d['tabla_origen'] = 'desechos';
            }
            $solicitudes = array_merge($solicitudes, $desechos);
            $total += $desechosModel->countSolicitudesFiltradas($filtros);
        }

        if (empty($filtros['tipo_solicitud']) || $filtros['tipo_solicitud'] == 'Bioseguridad') {
            $bioseg = $bioseguridadModel->getSolicitudesFiltradas($filtros, 999999, 0);
            foreach ($bioseg as &$b) {
                $b['tipo_solicitud'] = 'Bioseguridad';
                $b['tabla_origen'] = 'bioseguridad';
            }
            $solicitudes = array_merge($solicitudes, $bioseg);
            $total += $bioseguridadModel->countSolicitudesFiltradas($filtros);
        }

        usort($solicitudes, function($a, $b) {
            return strtotime($b['fecha_registro']) - strtotime($a['fecha_registro']);
        });

        $porPagina = 10;
        $pagina = (int)($this->request->getGet('page') ?? 1);
        $offset = ($pagina - 1) * $porPagina;
        $solicitudesPag = array_slice($solicitudes, $offset, $porPagina);
        $totalPages = ceil($total / $porPagina);

        $currentGet = $_GET;
        unset($currentGet['page']);
        $urlParams = !empty($currentGet) ? '&' . http_build_query($currentGet) : '';

        $startPage = max(1, $pagina - 1);
        $endPage = min($totalPages, $pagina + 1);
        if ($pagina == 1) $endPage = min($totalPages, 3);
        if ($pagina == $totalPages) $startPage = max(1, $totalPages - 2);

        $data = [
            'solicitudes'        => $solicitudesPag,
            'total'              => $total,
            'porPagina'          => $porPagina,
            'paginaActual'       => $pagina,
            'totalPages'         => $totalPages,
            'startPage'          => $startPage,
            'endPage'            => $endPage,
            'urlParams'          => $urlParams,
            'filtros'            => $filtros,
            'tiposSolicitud'     => ['Desechos Biológicos', 'Bioseguridad'],
            'estadosSolicitud'   => ['Pendiente', 'Entregado', 'Cancelado']
        ];

        return view('desechos/gestion_solicitudes', $data);
    }
    public function actualizarEstado()
    {
        if (!$this->estaLogueado()) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }
        if (session()->get('rol') !== 'administrador') {
            return $this->response->setJSON(['error' => 'Permisos insuficientes']);
        }

        $id = $this->request->getPost('id');
        $tipo = $this->request->getPost('tipo');
        $nuevoEstado = $this->request->getPost('estado');

        if (!in_array($nuevoEstado, ['Pendiente', 'Entregado', 'Cancelado'])) {
            return $this->response->setJSON(['error' => 'Estado no válido']);
        }

        try {
            if ($tipo == 'desechos') {
                $model = new \App\Models\SolicitudDesechosModel();
                $actualizado = $model->actualizarEstado($id, $nuevoEstado);
            } elseif ($tipo == 'bioseguridad') {
                $model = new \App\Models\SolicitudBioseguridadModel();
                $actualizado = $model->actualizarEstado($id, $nuevoEstado);
            } else {
                return $this->response->setJSON(['error' => 'Tipo de solicitud inválido']);
            }

            if ($actualizado) {
                $this->registrarBitacora('Cambio de estado', 'Gestión de Solicitudes', "Se cambió la solicitud ID $id a estado $nuevoEstado");
                return $this->response->setJSON(['success' => true]);
            } else {
                return $this->response->setJSON(['error' => 'No se pudo actualizar el estado']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Error: ' . $e->getMessage()]);
        }
    }
}