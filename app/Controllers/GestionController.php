<?php

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


    public function index()
    {
        if (!$this->estaLogueado()) {
            return redirect()->to('login');
        }

        try {
            $perPage = 8;
            
            $pageDept = (int)($this->request->getGet('page_dept') ?? 1);
            $pageLab  = (int)($this->request->getGet('page_lab') ?? 1);

            if ($pageDept < 1) $pageDept = 1;
            if ($pageLab < 1) $pageLab = 1;

            $offsetDept = ($pageDept - 1) * $perPage;
            $offsetLab  = ($pageLab - 1) * $perPage;

            $data = [
                'departamentos'       => $this->deptModel->getDepartamentosPaginados($perPage, $offsetDept),
                'todos_departamentos' => $this->deptModel->getDepartamentos(),
                'laboratorios'        => $this->labModel->getLaboratoriosPaginados($perPage, $offsetLab),
                'pager_dept'          => [
                    'actual' => $pageDept, 
                    'total'  => (int)ceil($this->deptModel->countDepartamentos() / $perPage)
                ],
                'pager_lab'           => [
                    'actual' => $pageLab, 
                    'total'  => (int)ceil($this->labModel->countLaboratorios() / $perPage)
                ]
            ];

            return view('gestionDepartamento/gestion_departamento', $data);

        } catch (Exception $e) {
            log_message('error', '[GestionController::index] Error crítico: ' . $e->getMessage());
            return redirect()->to(base_url('gestion-departamento'))->with('error', 'Error interno al procesar la solicitud.');
        }
    }

    //CRUD CENTRO/LABORATORIO
    public function guardarDepartamento(): RedirectResponse
    {
        return $this->procesarGuardado(null, 'departamento');
    }

    public function editarDepartamento(): RedirectResponse
    {
        $id = $this->request->getPost('id');
        return $this->procesarGuardado(($id !== null) ? (int)$id : null, 'departamento');
    }

    public function guardarLaboratorio(): RedirectResponse
    {
        return $this->procesarGuardado(null, 'laboratorio');
    }

    public function editarLaboratorio(): RedirectResponse
    {
        $id = $this->request->getPost('id');
        return $this->procesarGuardado(($id !== null) ? (int)$id : null, 'laboratorio');
    }

    private function procesarGuardado(?int $id, string $tipo): RedirectResponse
    {
        if (!$this->estaLogueado()) {
            return redirect()->to('login');
        }

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
                        'required'   => 'El nombre del departamento es obligatorio.',
                        'min_length' => 'El nombre debe poseer al menos 3 caracteres.',
                        'max_length' => 'El nombre no puede exceder los 100 caracteres.',
                        'is_unique'  => '¡Advertencia! Ya se encuentra un departamento registrado con ese nombre.'
                    ]
                ]
            ];
            $msgSuccess = $esEdicion ? 'Departamento actualizado de forma exitosa.' : 'Departamento guardado exitosamente.';
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
                        'required'       => 'Debe seleccionar un departamento válido.',
                        'is_not_unique'  => 'El departamento seleccionado no existe en los registros maestros.'
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

    public function eliminarDepartamento($id): RedirectResponse
    {
        return $this->procesarEliminacion((int)$id, 'departamento');
    }

    public function eliminarLaboratorio($id): RedirectResponse
    {
        return $this->procesarEliminacion((int)$id, 'laboratorio');
    }

    private function procesarEliminacion(int $id, string $tipo): RedirectResponse
    {
        if (!$this->estaLogueado()) {
            return redirect()->to('login');
        }

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
                $nombre = $registro['nombre'];
                $this->registrarBitacora('Eliminar', ucfirst($tipo), "Se eliminó el $tipo: $nombre (ID: $id)");
                return redirect()->to($destinoRedireccion)->with('success', ucfirst($tipo) . " '$nombre' eliminado correctamente.");
            }

            return redirect()->to($destinoRedireccion)->with('error', "No se pudo eliminar el registro seleccionado.");

        } catch (Exception $e) {
            log_message('error', "[GestionController::procesarEliminacion] Fallo: " . $e->getMessage());
            return redirect()->to($destinoRedireccion)->with('error', 'No se puede eliminar el registro debido a dependencias activas.');
        }
    }

    public function generarPdfGeneral()
    {
        if (!$this->estaLogueado()) {
            return redirect()->to('login');
        }

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

    public function generarPdfLaboratorios()
    {
        if (!$this->estaLogueado()) {
            return redirect()->to('login');
        }

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