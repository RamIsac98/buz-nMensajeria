<?php

/**
 * Controlador para la gestión de solicitudes de desechos biológicos.
 * 
 * Proporciona funcionalidades completas: creación, registro, validación,
 * edición, actualización, cambio de estado, generación de PDF, listado
 * combinado con solicitudes de bioseguridad, y gestión de pesos.
 * 
 * Hereda de BaseController para autenticación y auditoría.
 * 
 * Dependencias:
 * - SolicitudDesechosModel, SolicitudBioseguridadModel, UsuarioModel
 * - Dompdf para generación de PDF
 * 
 * OBSERVACIONES:
 * - Los métodos registroSolicitudes() y gestionSolicitudes() tienen código
 *   muy similar (duplicado) que podría extraerse a un método privado.
 * - La paginación se maneja manualmente con array_slice, no con el pager nativo.
 * - Se usan constantes de clase para validación de valores permitidos.
*/
namespace App\Controllers;

use App\Models\SolicitudDesechosModel;
use App\Models\UsuarioModel;
use App\Models\SolicitudBioseguridadModel;

use Dompdf\Dompdf;
use Dompdf\Options;

class DesechosController extends BaseController
{

     // Valores permitidos en el formulario
    const TIPOS_PERMITIDOS = ['B', 'C', 'D'];
    const ESTADOS_PERMITIDOS = ['Líquido', 'Sólido'];
    const EMPAQUES_PERMITIDOS = ['B', 'C', 'F', 'O'];
    const ESTERILIZADO_PERMITIDOS = ['Sí', 'No'];
    
    
       /**
     * Muestra el formulario para crear una nueva solicitud de desechos.
     * 
     * - Verifica sesión activa.
     * - Obtiene datos del usuario (departamento, laboratorio).
     * - Genera código automático y fecha actual.
     * 
     * @return mixed Vista 'desechos/formulario' con datos, o redirección a login.
     * 
     * @example
     * GET /desechos/crear
     */
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

       /**
     * Procesa el registro de una nueva solicitud de desechos.
     * 
     * - Verifica sesión activa.
     * - Valida los datos con validarDatosSolicitud().
     * - Convierte arrays de selección múltiple a strings separados por coma.
     * - Genera código único si no se proporciona o ya existe.
     * - Inserta en la tabla solicitudes_desechos.
     * - Registra en bitácora.
     * 
     * @return mixed Redirección con mensaje de éxito o error.
     * 
     * @example
     * POST /desechos/registrar (con datos del formulario)
     */
    public function registrar()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $post = $this->request->getPost();
        $validacion = $this->validarDatosSolicitud($post);
        if ($validacion !== true) {
            return redirect()->back()->with('error', implode('<br>', $validacion))->withInput();
        }

        $solicitudModel = new SolicitudDesechosModel();

        $postTipos     = $post['tipo_desecho'] ?? [];
        $postVariantes = $post['variante_desecho'] ?? [];
        $postEstado    = $post['estado_fisico'] ?? [];
        $postEmpaque   = $post['tipo_empaque'] ?? [];

        $codigoSolicitud = $post['codigo_solicitud'] ?? '';
        if (empty($codigoSolicitud)) {
            $codigoSolicitud = $solicitudModel->generarCodigoUnico();
        } else {
            $existe = $solicitudModel->where('codigo_solicitud', $codigoSolicitud)->first();
            if ($existe) $codigoSolicitud = $solicitudModel->generarCodigoUnico();
        }

        $insertData = [
            'codigo_solicitud'         => $codigoSolicitud,
            'usuario_id'               => session()->get('usuario_id'),
            'ext_telefono'             => trim($post['ext_telefono']),
            'tipos_desecho'            => is_array($postTipos) ? implode(', ', $postTipos) : '',
            'variantes_desecho'        => is_array($postVariantes) ? implode(', ', $postVariantes) : '',
            'esterilizado'             => ($post['esterilizado'] ?? 'No') === 'Sí' ? 1 : 0,
            'motivo'                   => trim($post['motivo']),
            'estado'                   => is_array($postEstado) ? implode(', ', $postEstado) : '',
            'peso_kg'                  => isset($post['peso_kg']) && $post['peso_kg'] !== '' ? (float)$post['peso_kg'] : null,
            'peso_l'                   => isset($post['peso_l']) && $post['peso_l'] !== '' ? (float)$post['peso_l'] : null,
            'tipo_empaque'             => is_array($postEmpaque) ? implode(', ', $postEmpaque) : '',
            'empaque_otro_descripcion' => trim($post['empaque_otro_descripcion'] ?? ''),
        ];

        if ($solicitudModel->insertarSolicitud($insertData)) {
            $this->registrarBitacora('Registro de Solicitud', 'Servicio Desechos', "Se generó la solicitud: " . $insertData['codigo_solicitud']);
            return redirect()->to(base_url('desechos/registroSolicitudes'))->with('success', 'Solicitud registrada correctamente.');
        } else {
            return redirect()->back()->with('error', 'Ocurrió un error en la base de datos al guardar.')->withInput();
        }
    }
    
        /**
     * Valida los datos de una solicitud de desechos (creación o edición).
     * 
     * Comprueba:
     * - Campos requeridos: ext_telefono, motivo.
     * - Al menos un tipo de desecho (B, C, D) seleccionado.
     * - Al menos un estado físico (Líquido/Sólido) seleccionado.
     * - Al menos un tipo de empaque (B, C, F, O) seleccionado.
     *   - Si se selecciona 'O', requiere descripción.
     * - Esterilizado debe ser 'Sí' o 'No'.
     * - Al menos una variante para el tipo de desecho seleccionado.
     * - Si estado físico incluye 'Sólido', peso_kg debe ser numérico ≥ 0.
     * - Si estado físico incluye 'Líquido', peso_l debe ser numérico ≥ 0.
     * - Extensión telefónica debe ser numérica.
     * 
     * @param array $post        Datos del formulario.
     * @param bool  $esEdicion   Indica si es edición (no se usa actualmente).
     * @return bool|array True si es válido, o array de mensajes de error.
     * 
     * @example
     * $errores = $this->validarDatosSolicitud($_POST);
     * if ($errores !== true) { ... }
     */
    private function validarDatosSolicitud(array $post, bool $esEdicion = false)
    {
        $errores = [];

        // 1. Validar campos requeridos generales
        $required = ['ext_telefono', 'motivo'];
        foreach ($required as $campo) {
            if (empty(trim($post[$campo] ?? ''))) {
                $errores[] = "El campo '" . ucfirst(str_replace('_', ' ', $campo)) . "' es obligatorio.";
            }
        }


        // 2. Validar que al menos un tipo de desecho esté seleccionado
        $tipos = $post['tipo_desecho'] ?? [];
        if (empty($tipos) || !is_array($tipos)) {
            $errores[] = 'Debe seleccionar al menos un tipo de desecho.';
        } else {
            foreach ($tipos as $tipo) {
                if (!in_array($tipo, self::TIPOS_PERMITIDOS, true)) {
                    $errores[] = "Tipo de desecho '$tipo' no es válido.";
                }
            }
        }

        // 3. Validar estado físico (al menos uno)
        $estados = $post['estado_fisico'] ?? [];
        if (empty($estados) || !is_array($estados)) {
            $errores[] = 'Debe seleccionar al menos un estado físico.';
        } else {
            foreach ($estados as $estado) {
                if (!in_array($estado, self::ESTADOS_PERMITIDOS, true)) {
                    $errores[] = "Estado físico '$estado' no es válido.";
                }
            }
        }

        // 4. Validar tipo de empaque (al menos uno)
        $empaques = $post['tipo_empaque'] ?? [];
        if (empty($empaques) || !is_array($empaques)) {
            $errores[] = 'Debe seleccionar al menos un tipo de empaque.';
        } else {
            foreach ($empaques as $empaque) {
                if (!in_array($empaque, self::EMPAQUES_PERMITIDOS, true)) {
                    $errores[] = "Tipo de empaque '$empaque' no es válido.";
                }
            }
            if (in_array('O', $empaques, true)) {
                $descOtros = trim($post['empaque_otro_descripcion'] ?? '');
                if (empty($descOtros)) {
                    $errores[] = 'Debe especificar la descripción del empaque "Otros".';
                }
            }
        }

        // 5. Validar esterilizado
        $esterilizado = $post['esterilizado'] ?? '';
        if (!in_array($esterilizado, self::ESTERILIZADO_PERMITIDOS, true)) {
            $errores[] = "Valor de esterilizado no válido.";
        }

        // 6. Validar variantes (opcional, pero si vienen deben ser textos conocidos)
        if (!empty($tipos)) {
            $variantes = $post['variante_desecho'] ?? [];
            if (empty($variantes) || !is_array($variantes) || count($variantes) === 0) {
                $errores[] = 'Debe seleccionar al menos una especificación (variante) para el tipo de desecho elegido.';
            } else {
                foreach ($variantes as $v) {
                    if (!is_string($v) || trim($v) === '') {
                        $errores[] = 'Las variantes de desecho deben ser textos válidos.';
                        break;
                    }
                }
            }
        }

        // 7. Validar pesos (opcionales, pero si se envían deben ser numéricos >= 0)
        if (in_array('Sólido', $estados)) {
            $pesoKg = $post['peso_kg'] ?? null;
            if ($pesoKg === '' || $pesoKg === null || !is_numeric($pesoKg) || $pesoKg < 0) {
                $errores[] = 'El peso en kg es obligatorio cuando se selecciona "Sólido" y debe ser un número ≥ 0.';
            }
        }
        if (in_array('Líquido', $estados)) {
            $pesoL = $post['peso_l'] ?? null;
            if ($pesoL === '' || $pesoL === null || !is_numeric($pesoL) || $pesoL < 0) {
                $errores[] = 'El peso en litros es obligatorio cuando se selecciona "Líquido" y debe ser un número ≥ 0.';
            }
        }

        // 8. Validar extensión telefónica (debe ser numérico)
        $ext = trim($post['ext_telefono'] ?? '');
        if ($ext !== '' && !ctype_digit($ext)) {
            $errores[] = 'La extensión telefónica debe ser un número.';
        }

        // 9. Motivo (ya validado arriba, pero reforzamos)
        if (empty(trim($post['motivo'] ?? ''))) {
            $errores[] = 'El motivo es obligatorio.';
        }

        return empty($errores) ? true : $errores;
    }

        /**
     * Genera un PDF de una solicitud de desechos específica.
     * 
     * - Verifica sesión activa.
     * - Obtiene solicitud por ID y datos del usuario.
     * - Renderiza vista 'desechos/plantilla_pdf' y genera PDF con Dompdf.
     * - Muestra en línea (Attachment = false).
     * 
     * @param int $id ID de la solicitud.
     * @return void Descarga o visualización del PDF.
     * 
     * @example
     * GET /desechos/generarPdf/5
     */
    public function generarPdf($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $solicitudModel = new SolicitudDesechosModel();
        $solicitud = $solicitudModel->find($id);

        if (!$solicitud) return redirect()->back()->with('error', 'Solicitud no encontrada.');

        $usuarioModel = new \App\Models\UsuarioModel();
        $usuario = $usuarioModel->findById($solicitud['usuario_id']);
        $nombreCompleto = trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''));

        $nombreCompleto = !empty($nombreCompleto) ? $nombreCompleto : ($usuario['username'] ?? 'Usuario');

        $data = $solicitud;
        $data['usuario_nombre'] = $nombreCompleto;
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
        /**
     * Muestra el historial de solicitudes (registro) para usuarios no administradores.
     * 
     * - Verifica sesión activa.
     * - Si el rol es 'proteccion_integral', redirige a gestión (acceso denegado).
     * - Combina solicitudes de desechos y bioseguridad según filtros.
     * - Ordena por fecha descendente.
     * - Paginación manual con array_slice (10 por página).
     * - Prepara datos para la vista: solicitudes, total, filtros, etc.
     * 
     * Código similar a gestionSolicitudes().
     * 
     * @return mixed Vista 'desechos/registroSolicitudes' con datos.
     * 
     * @example
     * GET /desechos/registroSolicitudes?buscar=quimico&tipo_solicitud=Desechos Biológicos&page=2
     */
    public function registroSolicitudes()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        if (session()->get('rol') === 'proteccion_integral') return redirect()->to(base_url('desechos/gestionSolicitudes'))->with('error', 'No tienes acceso al historial de solicitudes.');

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

        return view('desechos/registroSolicitudes', $data);
    }

        /**
     * Panel de gestión de solicitudes (solo administradores y protección integral).
     * 
     * - Verifica sesión y rol (administrador o proteccion_integral).
     * - Combina solicitudes de desechos y bioseguridad con filtros.
     * - Agrega campo 'tabla_origen' para identificar el tipo.
     * - Paginación manual con array_slice (10 por página).
     * - Prepara datos para la vista de gestión.
     * 
     *  Código muy similar a registroSolicitudes().
     * 
     * @return mixed Vista 'desechos/gestion_solicitudes' con datos.
     * 
     * @example
     * GET /desechos/gestionSolicitudes?estado_solicitud=Pendiente&page=1
     */
    public function gestionSolicitudes()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $rol = session()->get('rol');
        if (!in_array(session()->get('rol'), ['administrador', 'proteccion_integral'])) return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'No tiene permisos para gestionar solicitudes.');
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

        /**
     * Actualiza el estado de una solicitud (vía AJAX).
     * 
     * - Verifica sesión y rol (administrador o proteccion_integral).
     * - Recibe por POST: id, tipo (desechos/bioseguridad), estado.
     * - Valida que el estado sea permitido.
     * - Llama al modelo correspondiente para actualizar.
     * - Registra en bitácora.
     * - Retorna JSON con éxito o error.
     * 
     * @return \CodeIgniter\HTTP\Response Respuesta JSON.
     * 
     * @example
     * POST /desechos/actualizarEstado (con id, tipo, estado)
     */
    public function actualizarEstado()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));


        
        $rol = session()->get('rol');
        if (!in_array(session()->get('rol'), ['administrador', 'proteccion_integral'])) return $this->response->setJSON(['error' => 'Permisos insuficientes']);

        $id = $this->request->getPost('id');
        $tipo = $this->request->getPost('tipo');
        $nuevoEstado = $this->request->getPost('estado');

        if (!in_array($nuevoEstado, ['Pendiente', 'Entregado', 'Cancelado'])) return $this->response->setJSON(['error' => 'Estado no válido']);

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

        /**
     * Muestra el formulario de edición de una solicitud de desechos.
     * 
     * - Verifica sesión activa.
     * - Busca la solicitud por ID.
     * - Verifica que no esté editada (editado != 1).
     * - Verifica permisos: creador o administrador.
     * - Prepara datos para la vista de edición.
     * 
     * @param int $id ID de la solicitud.
     * @return mixed Vista 'desechos/editar' o redirección con error.
     * 
     * @example
     * GET /desechos/editar/10
     */
    public function editar($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $solicitudModel = new SolicitudDesechosModel();
        $solicitud = $solicitudModel->find($id);

        if (!$solicitud) return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'Solicitud no encontrada.');

        if ($solicitud['editado'] == 1) return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'Esta solicitud ya fue editada anteriormente.');

        if (session()->get('usuario_id') != $solicitud['usuario_id'] && session()->get('rol') !== 'administrador') return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'No tienes permiso para editar esta solicitud.');

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

        /**
     * Procesa la actualización de una solicitud de desechos existente.
     * 
     * - Verifica sesión activa.
     * - Busca la solicitud por ID.
     * - Valida permisos y que no esté editada.
     * - Valida datos con validarDatosSolicitud().
     * - Convierte arrays a strings.
     * - Actualiza y marca editado = 1.
     * - Registra en bitácora.
     * 
     * @param int $id ID de la solicitud.
     * @return mixed Redirección con mensaje de éxito o error.
     * 
     * @example
     * POST /desechos/actualizar/10
     */
    public function actualizar($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

         $solicitudModel = new SolicitudDesechosModel();
        $solicitud = $solicitudModel->find($id);
        if (!$solicitud) return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'Solicitud no encontrada.');
        if ($solicitud['editado'] == 1) return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'Esta solicitud ya fue editada anteriormente.');
        if (session()->get('usuario_id') != $solicitud['usuario_id'] && session()->get('rol') !== 'administrador') {
            return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'No tienes permiso para editar esta solicitud.');
        }

        $post = $this->request->getPost();
        $validacion = $this->validarDatosSolicitud($post, true);
        if ($validacion !== true) {
            return redirect()->back()->with('error', implode('<br>', $validacion))->withInput();
        }

        $postTipos     = $post['tipo_desecho'] ?? [];
        $postVariantes = $post['variante_desecho'] ?? [];
        $postEstado    = $post['estado_fisico'] ?? [];
        $postEmpaque   = $post['tipo_empaque'] ?? [];

        $updateData = [
            'ext_telefono'             => trim($post['ext_telefono']),
            'tipos_desecho'            => is_array($postTipos) ? implode(', ', $postTipos) : '',
            'variantes_desecho'        => is_array($postVariantes) ? implode(', ', $postVariantes) : '',
            'esterilizado'             => ($post['esterilizado'] ?? 'No') === 'Sí' ? 1 : 0,
            'motivo'                   => trim($post['motivo']),
            'estado'                   => is_array($postEstado) ? implode(', ', $postEstado) : '',
            'peso_kg'                  => isset($post['peso_kg']) && $post['peso_kg'] !== '' ? (float)$post['peso_kg'] : null,
            'peso_l'                   => isset($post['peso_l']) && $post['peso_l'] !== '' ? (float)$post['peso_l'] : null,
            'tipo_empaque'             => is_array($postEmpaque) ? implode(', ', $postEmpaque) : '',
            'empaque_otro_descripcion' => trim($post['empaque_otro_descripcion'] ?? ''),
            'editado'                  => 1,
        ];

        $solicitudModel->update($id, $updateData);
        $this->registrarBitacora('Edición de Solicitud', 'Servicio Desechos', "Se editó la solicitud: " . $solicitud['codigo_solicitud']);
        return redirect()->to(base_url('desechos/registroSolicitudes'))->with('success', 'Solicitud actualizada correctamente.');
    }

        /**
     * Obtiene los datos de peso de una solicitud (vía AJAX).
     * 
     * - Verifica sesión y rol (administrador o proteccion_integral).
     * - Busca la solicitud por ID.
     * - Retorna JSON con id, código, peso_kg, peso_l, estado_fisico.
     * 
     * @param int $id ID de la solicitud.
     * @return \CodeIgniter\HTTP\Response Respuesta JSON.
     * 
     * @example
     * GET /desechos/obtenerPeso/5
     */
    public function obtenerPeso($id)
    {
        if (!$this->estaLogueado()) return $this->response->setJSON(['error' => 'No autorizado']);

        $rol = session()->get('rol');
        if (!in_array($rol, ['administrador', 'proteccion_integral'])) {
            return $this->response->setJSON(['error' => 'Sin permisos']);
        }

        $solicitudModel = new SolicitudDesechosModel();
        $solicitud = $solicitudModel->find($id);

        if (!$solicitud) {
            return $this->response->setJSON(['error' => 'Solicitud no encontrada']);
        }

        return $this->response->setJSON([
            'id'         => $solicitud['id'],
            'codigo'     => $solicitud['codigo_solicitud'],
            'peso_kg'    => $solicitud['peso_kg'],
            'peso_l'     => $solicitud['peso_l'],
            'estado_fisico' => $solicitud['estado']
        ]);
    }

        /**
     * Actualiza los pesos de una solicitud (vía AJAX).
     * 
     * - Verifica sesión y rol (administrador o proteccion_integral).
     * - Valida que los valores sean numéricos ≥ 0.
     * - Restringe según estado físico: si solo Sólido, solo permite kg; si solo Líquido, solo litros.
     * - No permite cambiar a 0 si el valor actual es > 0 (protección contra borrado accidental).
     * - Actualiza y registra en bitácora.
     * 
     * @param int $id ID de la solicitud.
     * @return \CodeIgniter\HTTP\Response Respuesta JSON con éxito o error.
     * 
     * @example
     * POST /desechos/actualizarPeso/5 (con peso_kg y/o peso_l)
     */
    public function actualizarPeso($id)
{
    // Verificar autenticación
    if (!$this->estaLogueado()) {
        return $this->response->setJSON(['success' => false, 'error' => 'No autenticado']);
    }

    // Verificar rol
    $rol = session()->get('rol');
    if (!in_array($rol, ['administrador', 'proteccion_integral'])) {
        return $this->response->setJSON(['success' => false, 'error' => 'Permisos insuficientes']);
    }

    $solicitudModel = new SolicitudDesechosModel();
    $solicitud = $solicitudModel->find($id);

    if (!$solicitud) {
        return $this->response->setJSON(['success' => false, 'error' => 'Solicitud no encontrada']);
    }

    // Obtener valores actuales
    $valorActualKg = $solicitud['peso_kg']; // puede ser null o float
    $valorActualL  = $solicitud['peso_l'];

    // Obtener valores enviados
    $peso_kg = $this->request->getPost('peso_kg');
    $peso_l  = $this->request->getPost('peso_l');

    // Convertir a float solo si no está vacío ('' o null)
    $peso_kg = ($peso_kg !== '' && $peso_kg !== null) ? (float)$peso_kg : null;
    $peso_l  = ($peso_l !== '' && $peso_l !== null) ? (float)$peso_l : null;

    // Validar que si se envía un valor, sea numérico y >= 0
    if ($peso_kg !== null && (!is_numeric($peso_kg) || $peso_kg < 0)) {
        return $this->response->setJSON(['success' => false, 'error' => 'El peso en kg debe ser un número mayor o igual a 0']);
    }
    if ($peso_l !== null && (!is_numeric($peso_l) || $peso_l < 0)) {
        return $this->response->setJSON(['success' => false, 'error' => 'El volumen en litros debe ser un número mayor o igual a 0']);
    }

    // Obtener el estado físico
    $estadoFisico = $solicitud['estado'] ?? '';
    $esSólido = strpos($estadoFisico, 'Sólido') !== false;
    $esLíquido = strpos($estadoFisico, 'Líquido') !== false;

    // Validación por estado físico (solo se permite editar el campo que aplica)
    if ($esSólido && !$esLíquido) {
        // Solo Sólido: solo se permite kg
        if ($peso_l !== null) {
            return $this->response->setJSON(['success' => false, 'error' => 'No se puede actualizar el volumen en litros porque la solicitud es solo de tipo Sólido.']);
        }
        // Si no envía kg, lo dejamos como null (no se actualiza)
    } elseif ($esLíquido && !$esSólido) {
        // Solo Líquido: solo se permite litros
        if ($peso_kg !== null) {
            return $this->response->setJSON(['success' => false, 'error' => 'No se puede actualizar el peso en kg porque la solicitud es solo de tipo Líquido.']);
        }
        // Si no envía litros, lo dejamos como null
    }
    // Si contiene ambos, permitimos ambos campos sin restricción adicional

    // No permitir cambiar a 0 si el valor actual es > 0
    if ($peso_kg !== null && $valorActualKg !== null && $valorActualKg > 0 && $peso_kg == 0) {
        return $this->response->setJSON(['success' => false, 'error' => 'No se puede establecer el peso en 0 porque actualmente tiene un valor mayor a 0.']);
    }
    if ($peso_l !== null && $valorActualL !== null && $valorActualL > 0 && $peso_l == 0) {
        return $this->response->setJSON(['success' => false, 'error' => 'No se puede establecer el volumen en 0 porque actualmente tiene un valor mayor a 0.']);
    }

    // Preparar datos para actualizar
    $data = [
        'peso_kg' => $peso_kg,
        'peso_l'  => $peso_l
    ];

    // Actualizar
    $actualizado = $solicitudModel->update($id, $data);

    if ($actualizado) {
        $this->registrarBitacora('Edición de Peso', 'Servicio Desechos', "Se actualizó el peso de la solicitud ID $id a Kg: $peso_kg, L: $peso_l");
        return $this->response->setJSON(['success' => true, 'message' => 'Peso actualizado correctamente']);
    }

    $errors = $solicitudModel->errors();
    $errorMsg = !empty($errors) ? implode(', ', $errors) : 'No se pudo actualizar el peso. Verifica los datos.';
    return $this->response->setJSON(['success' => false, 'error' => $errorMsg]);
}


}