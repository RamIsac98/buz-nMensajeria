<?php
/**
 * Controlador para la gestión de centros (departamentos) y laboratorios.
 * 
 * Proporciona CRUD completo, listado paginado con filtros por departamento
 * y generación de reportes en PDF (estructura organizacional y listado de laboratorios).
 * 
 * Solo accesible para roles 'administrador' y 'proteccion_integral'.
 * Hereda de BaseController para autenticación y auditoría.
 * 
 * Dependencias:
 * - DepartamentoModel, LaboratorioModel para operaciones CRUD.
 * - UsuarioModel para reporte general (en generarPdfGeneral).
 * - Dompdf para generación de PDFs.
 * 
 * OBSERVACIONES:
 * - La validación de unicidad (is_unique) se maneja correctamente tanto para creación como edición.
 * - La paginación se construye manualmente sin usar el pager nativo de CI.
 * - Los métodos guardar/editar redirigen a la misma página manteniendo los parámetros de paginación.
 */
namespace App\Controllers;

use App\Models\DepartamentoModel;
use App\Models\LaboratorioModel;
use CodeIgniter\HTTP\RedirectResponse;
use Exception;

//Gestion Centro / Laboratorio
class GestionController extends BaseController
{
    private DepartamentoModel $deptModel;
    private LaboratorioModel $labModel;

    public function __construct()
    {
        $this->deptModel = new DepartamentoModel();
        $this->labModel  = new LaboratorioModel();
    }

        /**
     * Página principal de gestión de centros y laboratorios.
     * 
     * - Verifica sesión y rol (admin o protección integral).
     * - Obtiene listados paginados de departamentos y laboratorios (8 por página).
     * - Permite filtrar laboratorios por departamento (filtro_depto vía GET).
     * - Prepara datos de paginación para ambas tablas.
     * - Captura excepciones y registra error en log.
     * 
     * @return mixed Vista 'gestionDepartamento/gestion_departamento' o redirección.
     * 
     * @example
     * GET /gestion-departamento?page_dept=2&page_lab=1&filtro_depto=3
     */

    public function index()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $rol = session()->get('rol');
        if (!in_array(session()->get('rol'), ['administrador', 'proteccion_integral'])) return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'Acceso denegado.');

            try {
            $perPage = 8;
            $pageDept = (int)($this->request->getGet('page_dept') ?? 1);
            $pageLab  = (int)($this->request->getGet('page_lab') ?? 1);
            $filtroDepto = $this->request->getGet('filtro_depto');
            $filtroDepto = ($filtroDepto === 'todos' || empty($filtroDepto)) ? null : (int)$filtroDepto;

            if ($pageDept < 1) $pageDept = 1;
            if ($pageLab < 1) $pageLab = 1;

            $offsetDept = ($pageDept - 1) * $perPage;
            $offsetLab  = ($pageLab - 1) * $perPage;

            $data = [
                'departamentos'       => $this->deptModel->getDepartamentosPaginados($perPage, $offsetDept),
                'todos_departamentos' => $this->deptModel->getDepartamentos(),
                'laboratorios'        => $this->labModel->getLaboratoriosPaginados($perPage, $offsetLab, $filtroDepto),
                'pager_dept'          => [
                    'actual' => $pageDept,
                    'total'  => (int)ceil($this->deptModel->countDepartamentos() / $perPage)
                ],
                'pager_lab'           => [
                    'actual' => $pageLab,
                    'total'  => (int)ceil($this->labModel->countLaboratorios($filtroDepto) / $perPage)
                ],
                'filtro_depto'        => $filtroDepto
            ];

            return view('gestionDepartamento/gestion_departamento', $data);

        } catch (Exception $e) {
            log_message('error', '[GestionController::index] Error crítico: ' . $e->getMessage());
            return redirect()->to(base_url('gestion-departamento'))->with('error', 'Error interno al procesar la solicitud.');
        }
    }

        /**
     * Wrapper para guardar un nuevo centro.
     * 
     * NOTA: "DEPARTAMENTO Y CENTRO ES LO MISMO SOLO QUE EN LAS ULTIMAS FACES HUBO CAMBIO DE TERMINO"
     * 
     * @return RedirectResponse Redirección con mensaje de éxito o error.
     * 
     * @example
     * POST /gestion-departamento/guardarDepartamento (con campo 'nombre')
     */
    public function guardarDepartamento(): RedirectResponse
    {
        return $this->procesarGuardado(null, 'departamento');
    }

        /**
     * Wrapper para editar un departamento existente.
     * 
     * @return RedirectResponse Redirección con mensaje de éxito o error.
     * 
     * @example
     * POST /gestion-departamento/editarDepartamento (con campos 'id' y 'nombre')
     */

    public function editarDepartamento(): RedirectResponse
    {
        $id = $this->request->getPost('id');
        return $this->procesarGuardado(($id !== null) ? (int)$id : null, 'departamento');
    }

        /**
     * Wrapper para guardar un nuevo laboratorio.
     * 
     * @return RedirectResponse Redirección con mensaje de éxito o error.
     * 
     * @example
     * POST /gestion-departamento/guardarLaboratorio (con campos 'nombre_laboratorio' y 'departamento_id')
     */

    public function guardarLaboratorio(): RedirectResponse
    {
        return $this->procesarGuardado(null, 'laboratorio');
    }


        /**
     * Wrapper para editar un laboratorio existente.
     * 
     * @return RedirectResponse Redirección con mensaje de éxito o error.
     * 
     * @example
     * POST /gestion-departamento/editarLaboratorio (con campos 'id', 'nombre_laboratorio', 'departamento_id')
     */


    public function editarLaboratorio(): RedirectResponse
    {
        $id = $this->request->getPost('id');
        return $this->procesarGuardado(($id !== null) ? (int)$id : null, 'laboratorio');
    }

        /**
     * Procesa la creación o actualización de departamentos o laboratorios.
     * 
     * - Verifica sesión.
     * - Construye reglas de validación según el tipo (incluyendo unicidad).
     * - Para departamentos: solo valida 'nombre'.
     * - Para laboratorios: valida 'nombre_laboratorio' y 'departamento_id'.
     * - Si falla la validación, redirige con error y mantiene el input.
     * - En éxito, ejecuta el modelo correspondiente y registra en bitácora.
     * - Mantiene los parámetros de paginación en la redirección.
     * 
     * @param int|null $id   ID para edición (null para creación).
     * @param string   $tipo 'departamento' o 'laboratorio'.
     * @return RedirectResponse Redirección con mensaje.
     * 
     * @example
     * // Interno, no se llama directamente desde rutas.
     */

    private function procesarGuardado(?int $id, string $tipo): RedirectResponse
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $esEdicion = ($id !== null);
        $pageDept = (int)($this->request->getGet('page_dept') ?? 1);
        $pageLab  = (int)($this->request->getGet('page_lab') ?? 1);
        $destinoRedireccion = base_url("gestion-departamento?page_dept={$pageDept}&page_lab={$pageLab}");

        if ($tipo === 'departamento') {
            $nombre = trim((string)$this->request->getPost('nombre'));
            $reglaUnique = $esEdicion ? "is_unique[departamentos.nombre,id,{$id}]" : "is_unique[departamentos.nombre]";
            
            $reglas = [
                'nombre' => [
                    'rules'  => "required|min_length[3]|max_length[100]|$reglaUnique",
                    'errors' => [
                        'required'   => 'El nombre del Centro es obligatorio.',
                        'min_length' => 'El nombre debe poseer al menos 3 caracteres.',
                        'max_length' => 'El nombre no puede exceder los 100 caracteres.',
                        'is_unique'  => '¡Advertencia! Ya se encuentra un Centro registrado con ese nombre.'
                    ]
                ]
            ];
            $msgSuccess = $esEdicion ? 'Centro actualizado de forma exitosa.' : 'Centro guardado exitosamente.';
        } else {
            $nombre = trim((string)$this->request->getPost('nombre_laboratorio'));
            $deptoId = (int)$this->request->getPost('departamento_id');
            $reglaUnique = $esEdicion ? "is_unique[laboratorios.nombre,id,{$id}]" : "is_unique[laboratorios.nombre]";

            $reglas = [
                'nombre_laboratorio' => [
                    'rules'  => "required|min_length[3]|max_length[100]|$reglaUnique",
                    'errors' => [
                        'required'   => 'El nombre del laboratorio es obligatorio.',
                        'min_length' => 'El nombre del laboratorio debe contener al menos 3 caracteres.',
                        'max_length' => 'El nombre del laboratorio no puede exceder los 100 caracteres.',
                        'is_unique'  => '¡Advertencia! Ya se encuentra un laboratorio registrado con ese mismo nombre.'
                    ]
                ],
                'departamento_id' => [
                    'rules'  => 'required|is_not_unique[departamentos.id]',
                    'errors' => [
                        'required'       => 'Debe seleccionar un centro válido.',
                        'is_not_unique'  => 'El centro seleccionado no existe en los registros maestros.'
                    ]
                ]
            ];
            $msgSuccess = $esEdicion ? 'Laboratorio actualizado correctamente.' : 'Laboratorio guardado exitosamente.';
        }

        if (!$this->validate($reglas)) {
            $error = $this->validator->getError('nombre') 
                ?: $this->validator->getError('nombre_laboratorio') 
                ?: $this->validator->getError('departamento_id');
            return redirect()->to($destinoRedireccion)->withInput()->with('error', $error);
        }

        try {
            $ejecutado = false;
            if ($tipo === 'departamento') {
                $ejecutado = $esEdicion 
                    ? $this->deptModel->updateDepartamento($id, $nombre) 
                    : $this->deptModel->insertDepartamento($nombre);
            } else {
                $ejecutado = $esEdicion 
                    ? $this->labModel->updateLaboratorio($id, $deptoId, $nombre) 
                    : $this->labModel->insertLaboratorio($deptoId, $nombre);
            }

            if ($ejecutado) {
                $accion = $esEdicion ? 'Editar' : 'Crear';
                $detalle = $esEdicion ? "Actualizado ID: $id a: $nombre" : "Registrado: $nombre";
                $this->registrarBitacora("$accion " . ucfirst($tipo), 'Configuración', $detalle);

                return redirect()->to($destinoRedireccion)->with('success', $msgSuccess);
            }

            return redirect()->to($destinoRedireccion)->with('error', "Error inesperado de base de datos al procesar el/la $tipo.");

        } catch (Exception $e) {
            log_message('error', "[GestionController::procesarGuardado] Excepción: " . $e->getMessage());
            return redirect()->to($destinoRedireccion)->with('error', 'Error interno al guardar los cambios en el servidor.');
        }
    }

        /**
     * Wrapper para eliminar un departamento.
     * 
     * @param int $id ID del departamento.
     * @return RedirectResponse Redirección con mensaje.
     * 
     * @example
     * GET /gestion-departamento/eliminarDepartamento/5
     */

    public function eliminarDepartamento($id): RedirectResponse
    {
        return $this->procesarEliminacion((int)$id, 'departamento');
    }

        /**
     * Wrapper para eliminar un laboratorio.
     * 
     * @param int $id ID del laboratorio.
     * @return RedirectResponse Redirección con mensaje.
     * 
     * @example
     * GET /gestion-departamento/eliminarLaboratorio/12
     */

    public function eliminarLaboratorio($id): RedirectResponse
    {
        return $this->procesarEliminacion((int)$id, 'laboratorio');
    }

        /**
     * Procesa la eliminación de un departamento o laboratorio.
     * 
     * - Verifica sesión.
     * - Busca el registro antes de eliminar para obtener el nombre.
     * - Ejecuta la eliminación mediante el modelo correspondiente.
     * - Registra en bitácora si fue exitoso.
     * - Mantiene los parámetros de paginación en la redirección.
     * 
     * @param int    $id   ID del registro.
     * @param string $tipo 'departamento' o 'laboratorio'.
     * @return RedirectResponse Redirección con mensaje.
     * 
     * @example
     * // Interno.
     */

    private function procesarEliminacion(int $id, string $tipo): RedirectResponse
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));


        $pageDept = (int)($this->request->getGet('page_dept') ?? 1);
        $pageLab  = (int)($this->request->getGet('page_lab') ?? 1);
        $destinoRedireccion = base_url("gestion-departamento?page_dept={$pageDept}&page_lab={$pageLab}");

        try {
            if ($tipo === 'departamento') {
                $registro = $this->deptModel->findDepartamento($id);
                $eliminado = $registro ? $this->deptModel->deleteItem($id) : false;
            } else {
                $registro = $this->labModel->findLaboratorio($id);
                $eliminado = $registro ? $this->labModel->deleteItem($id) : false;
            }

            if ($registro && $eliminado) {
                $nombre = $registro['nombre'] ?? "ID: {$id}";
                $this->registrarBitacora('Eliminar', ucfirst($tipo), "Se eliminó el $tipo: $nombre (ID: $id)");
                return redirect()->to($destinoRedireccion)->with('success', ucfirst($tipo) . " '$nombre' eliminado correctamente.");
            }

            return redirect()->to($destinoRedireccion)->with('error', "No se pudo eliminar el registro seleccionado.");

        } catch (Exception $e) {
            log_message('error', "[GestionController::procesarEliminacion] Fallo: " . $e->getMessage());
            return redirect()->to($destinoRedireccion)->with('error', 'No se puede eliminar el registro debido a dependencias activas.');
        }
    }

        /**
     * Genera un PDF con el reporte general de estructura organizacional y usuarios.
     * 
     * - Verifica sesión.
     * - Obtiene el parámetro GET 'depto_id' (puede ser 'todos' o un ID).
     * - Llama a UsuarioModel::getReporteGeneral() para obtener datos jerárquicos.
     * - Renderiza vista 'gestionDepartamento/pdf_general_completo' en landscape.
     * - Descarga el PDF (Attachment).
     * 
     * @return void Descarga del PDF.
     * 
     * @example
     * GET /gestion-departamento/generarPdfGeneral?depto_id=todos
     */

    public function generarPdfGeneral()
    {
         if (!$this->estaLogueado()) return redirect()->to(base_url('login'));


        $depto_id = $this->request->getGet('depto_id');
        $usuarioModel = new \App\Models\UsuarioModel();
        
        $data = [
            'reporte'            => $usuarioModel->getReporteGeneral($depto_id),
            'depto_seleccionado' => $depto_id
        ];

        $html = view('gestionDepartamento/pdf_general_completo', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        $dompdf->stream("Reporte_General_Estructura_y_Usuarios.pdf", ["Attachment" => true]);
        exit();
    }

        /**
     * Genera un PDF con el listado de laboratorios, opcionalmente filtrado por departamento.
     * 
     * - Verifica sesión.
     * - Obtiene el parámetro GET 'depto_id' (puede ser 'todos' o un ID).
     * - Obtiene laboratorios filtrados mediante LaboratorioModel::getLaboratoriosFiltrados().
     * - Renderiza vista 'gestionDepartamento/pdf_laboratorios' en portrait.
     * - Descarga el PDF (Attachment).
     * 
     * @return void Descarga del PDF.
     * 
     * @example
     * GET /gestion-departamento/generarPdfLaboratorios?depto_id=2
     */
    public function generarPdfLaboratorios()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $depto_id = $this->request->getGet('depto_id');
        $labModel = new \App\Models\LaboratorioModel();
        $data['laboratorios'] = $labModel->getLaboratoriosFiltrados($depto_id);
        $data['depto_seleccionado'] = $depto_id;
        $html = view('gestionDepartamento/pdf_laboratorios', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("Laboratorios_Reporte.pdf", ["Attachment" => true]);
        exit();
    }
}