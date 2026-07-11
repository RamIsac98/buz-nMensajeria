<?php
//bitacora
namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\BitacoraModel;

abstract class BaseController extends Controller
{
    protected $request;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {  
        parent::initController($request, $response, $logger);
    }

    // Verificación sesión activa
    protected function estaLogueado(): bool
    {
        return (bool) session()->get('logged_in');
    }

    //Registra movimientos Bitacora
    protected function registrarBitacora(string $accion, string $tipo_solicitud = 'Ninguna', string $registro = ''): void
    {
        $bitacoraModel = new BitacoraModel();
        
        $datos = [
            'usuario_id'     => session()->get('usuario_id') ?? null,
            'tipo_solicitud' => $tipo_solicitud,
            'registro'       => $registro,
            'ip'             => $this->request->getIPAddress(),
            'accion'         => $accion
        ];

        $bitacoraModel->insertRegistro($datos);
    }

    public function bitacora()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $bitacoraModel = new BitacoraModel();

        $filtros = [
            'buscar' => $this->request->getGet('buscar'),
            'tipo'   => $this->request->getGet('tipo'),
            'desde'  => $this->request->getGet('desde'),
            'hasta'  => $this->request->getGet('hasta')
        ];

        $page    = (int)($this->request->getGet('page') ?? 1);
        $perPage = 8;
        $offset  = ($page - 1) * $perPage;

        // Consultas con SQL Clásico
        $data['bitacora'] = $bitacoraModel->getBitacoraFiltrada($filtros, $perPage, $offset);
        $total            = $bitacoraModel->countBitacora($filtros);

        $pager = \Config\Services::pager();
        $pager->store('default', $page, $perPage, $total);
        $data['pager'] = $pager;

        return view('Bitacora/bitacora', $data);
    }


    public function generarPdfBitacora()
    {
        if (!$this->estaLogueado()) {
            return redirect()->to(base_url('login'));
        }

        $bitacoraModel = new BitacoraModel();

        $filtros = [
            'buscar' => $this->request->getGet('buscar'),
            'tipo'   => $this->request->getGet('tipo'),
            'desde'  => $this->request->getGet('desde'),
            'hasta'  => $this->request->getGet('hasta')
        ];

        $paginaInicio = (int)($this->request->getGet('pagina_inicio') ?? 1);
        $paginaFin    = (int)($this->request->getGet('pagina_fin') ?? $paginaInicio);
        
        $porPagina = 8; 
        $offset    = ($paginaInicio - 1) * $porPagina;
        $limite    = (($paginaFin - $paginaInicio) + 1) * $porPagina;

        $data['bitacora'] = $bitacoraModel->getBitacoraFiltrada($filtros, $limite, $offset);

        session_write_close();

        while (ob_get_level()) {
            ob_end_clean();
        }

        $html = view('Bitacora/bitacora_pdf', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $dompdf->stream("Reporte_Bitacora_Paginas_{$paginaInicio}_al_{$paginaFin}.pdf", ["Attachment" => true]);
    }
}