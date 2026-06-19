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
        if (empty($codigoSolicitud)) {
            $codigoSolicitud = $solicitudModel->generarCodigoUnico();
        }

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
            // No se guarda ruta_pdf
        ];

        if ($solicitudModel->insertarSolicitud($insertData)) {
            $this->registrarBitacora('Registro de Solicitud', 'Servicio Desechos', "Se generó la solicitud: " . $insertData['codigo_solicitud']);
            return redirect()->to(base_url('desechos/registroSolicitudes'))->with('success', 'Solicitud registrada correctamente.');
        } else {
            return redirect()->back()->with('error', 'Ocurrió un error en la base de datos al guardar.');
        }
    }

    public function generarPdf($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $solicitudModel = new SolicitudDesechosModel();
        $solicitud = $solicitudModel->find($id);

        if (!$solicitud) {
            return redirect()->back()->with('error', 'Solicitud no encontrada.');
        }

        $usuarioModel = new \App\Models\UsuarioModel();
        $usuario = $usuarioModel->findById($solicitud['usuario_id']);

        $data = $solicitud;
        $data['usuario_nombre'] = $usuario['username'] ?? 'Usuario';
        $data['departamento']   = $usuario['departamento'] ?? 'No asignado';
        $data['laboratorio']    = $usuario['nombre_laboratorio'] ?? 'No asignado';
        $data['fecha_registro'] = date('d/m/Y H:i:s', strtotime($solicitud['fecha_registro']));

        $html = view('desechos/plantilla_pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream('solicitud_' . $solicitud['codigo_solicitud'] . '.pdf', ['Attachment' => false]);
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

    public function editar($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $solicitudModel = new SolicitudDesechosModel();
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

        // Verificar que el usuario sea el creador O administrador
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

        return view('desechos/editar', $data);
    }

    public function actualizar($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $solicitudModel = new SolicitudDesechosModel();
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

        // Permiso: solo creador o administrador
        if (session()->get('usuario_id') != $solicitud['usuario_id'] && session()->get('rol') !== 'administrador') {
            return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'No tienes permiso para editar esta solicitud.');
        }

        // Recoger datos del POST (igual que en registrar)
        $postTipos     = $this->request->getPost('tipo_desecho') ?? [];
        $postVariantes = $this->request->getPost('variante_desecho') ?? [];
        $postEstado    = $this->request->getPost('estado_fisico') ?? [];
        $postEmpaque   = $this->request->getPost('tipo_empaque') ?? [];

        $updateData = [
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
            'editado' => 1,
        ];

        $solicitudModel->update($id, $updateData);

        $this->registrarBitacora('Edición de Solicitud', 'Servicio Desechos', "Se editó la solicitud: " . $solicitud['codigo_solicitud']);

        return redirect()->to(base_url('desechos/registroSolicitudes'))->with('success', 'Solicitud actualizada correctamente.');
    }

}