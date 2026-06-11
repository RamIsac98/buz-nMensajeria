<?php

namespace App\Controllers;

use App\Models\DepartamentoModel;
use App\Models\LaboratorioModel;
use CodeIgniter\HTTP\RedirectResponse;

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
        if (!$this->estaLogueado()) return redirect()->to('login');

        $perPage = 8;
        
        $pageDept = (int)($this->request->getGet('page_dept') ?? 1);
        $pageLab  = (int)($this->request->getGet('page_lab') ?? 1);

        $offsetDept = ($pageDept - 1) * $perPage;
        $offsetLab  = ($pageLab - 1) * $perPage;

        $data = [
            'departamentos' => $this->deptModel->getDepartamentosPaginados($perPage, $offsetDept),
            'laboratorios'  => $this->labModel->getLaboratoriosPaginados($perPage, $offsetLab),
            'pager_dept'    => ['actual' => $pageDept, 'total' => ceil($this->deptModel->countDepartamentos() / $perPage)],
            'pager_lab'     => ['actual' => $pageLab, 'total' => ceil($this->labModel->countLaboratorios() / $perPage)]
        ];

        return view('gestionDepartamento/gestion_departamento', $data);
    }

    public function guardarDepartamento(): RedirectResponse
    {
        return $this->procesarGuardado(null, 'departamento');
    }

    public function editarDepartamento(): RedirectResponse
    {
        $id = (int)$this->request->getPost('id');
        return $this->procesarGuardado($id, 'departamento');
    }

    public function guardarLaboratorio(): RedirectResponse
    {
        return $this->procesarGuardado(null, 'laboratorio');
    }

    public function editarLaboratorio(): RedirectResponse
    {
        $id = (int)$this->request->getPost('id');
        return $this->procesarGuardado($id, 'laboratorio');
    }

    private function procesarGuardado(?int $id, string $tipo): RedirectResponse
    {
        if (!$this->estaLogueado()) return redirect()->to('login');

        $esEdicion = ($id !== null);

        if ($tipo === 'departamento') {
            $nombre = trim($this->request->getPost('nombre'));
            $reglaUnique = $esEdicion ? "is_unique[departamentos.nombre,id,{$id}]" : "is_unique[departamentos.nombre]";
            
            $reglas = [
                'nombre' => [
                    'rules'  => "required|$reglaUnique",
                    'errors' => [
                        'required'  => 'El nombre del departamento es obligatorio.',
                        'is_unique' => $esEdicion ? '¡Advertencia! Ya existe otro departamento con ese mismo nombre.' : '¡Advertencia! El departamento que intenta registrar ya se encuentra en el sistema.'
                    ]
                ]
            ];
            $msgSuccess = $esEdicion ? 'Departamento actualizado.' : 'Departamento guardado exitosamente.';
        } else {
            $nombre = trim($this->request->getPost('nombre_laboratorio'));
            $deptoId = (int)$this->request->getPost('departamento_id');
            $reglaUnique = $esEdicion ? "is_unique[laboratorios.nombre,id,{$id}]" : "is_unique[laboratorios.nombre]";

            $reglas = [
                'nombre_laboratorio' => [
                    'rules'  => "required|$reglaUnique",
                    'errors' => [
                        'required'  => 'El nombre del laboratorio es obligatorio.',
                        'is_unique' => $esEdicion ? '¡Advertencia! Ya existe otro laboratorio con ese mismo nombre.' : '¡Advertencia! El laboratorio que intenta registrar ya se encuentra en el sistema.'
                    ]
                ],
                'departamento_id' => [
                    'rules'  => 'required',
                    'errors' => ['required' => 'Debe seleccionar un departamento válido.']
                ]
            ];
            $msgSuccess = $esEdicion ? 'Laboratorio actualizado correctamente.' : 'Laboratorio guardado exitosamente.';
        }

        if (!$this->validate($reglas)) {
            $error = $this->validator->getError('nombre') ?: $this->validator->getError('nombre_laboratorio') ?: $this->validator->getError('departamento_id');
            return redirect()->back()->withInput()->with('error', $error);
        }

        // Llamadas explícitas a SQL crudo según corresponda
        $ejecutado = false;
        if ($tipo === 'departamento') {
            $ejecutado = $esEdicion ? $this->deptModel->updateDepartamento($id, $nombre) : $this->deptModel->insertDepartamento($nombre);
        } else {
            $ejecutado = $esEdicion ? $this->labModel->updateLaboratorio($id, $deptoId, $nombre) : $this->labModel->insertLaboratorio($deptoId, $nombre);
        }

        if ($ejecutado) {
            $accion = $esEdicion ? 'Editar' : 'Crear';
            $detalle = $esEdicion ? "Actualizado ID: $id a: $nombre" : "Registrado: $nombre";
            $this->registrarBitacora("$accion " . ucfirst($tipo), 'Configuración', $detalle);

            return redirect()->back()->with('success', $msgSuccess);
        }

        return redirect()->back()->with('error', "Error al procesar el/la $tipo.");
    }

    public function eliminarDepartamento($id): RedirectResponse
    {
        return $this->procesarEliminacion((int)$id, 'departamento');
    }

    public function eliminarLaboratorio($id): RedirectResponse
    {
        return $this->procesarEliminacion((int)$id, 'laboratorio');
    }

    /**
     * MÉTODO OPTIMIZADO: Centralizador de borrado bajo SQL clásico
     */
    private function procesarEliminacion(int $id, string $tipo): RedirectResponse
    {
        if (!$this->estaLogueado()) return redirect()->to('login');

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
            return redirect()->back()->with('success', ucfirst($tipo) . " '$nombre' eliminado correctamente.");
        }

        return redirect()->back()->with('error', "No se pudo encontrar o eliminar el $tipo.");
    }

    public function generarPdfGeneral()
    {
        if (!$this->estaLogueado()) return redirect()->to('login');

        // Capturamos el filtro del departamento desde el navegador
        $depto_id = $this->request->getGet('depto_id');
        
        // Instanciamos el modelo de usuarios para ejecutar la consulta SQL nativa
        $usuarioModel = new \App\Models\UsuarioModel();
        
        $data = [
            'reporte'            => $usuarioModel->getReporteGeneral($depto_id),
            'depto_seleccionado' => $depto_id
        ];

        // Renderizamos la vista destinada al diseño del PDF
        $html = view('gestionDepartamento/pdf_general_completo', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape'); // Orientación horizontal para albergar cómodamente todas las columnas
        $dompdf->render();
        
        $dompdf->stream("Reporte_General_Estructura_y_Usuarios.pdf", ["Attachment" => true]);
    }

    public function generarPdfLaboratorios()
{
    if (!$this->estaLogueado()) return redirect()->to('login');

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
}
}