<?php
/**
 * Controlador base abstracto que provee funcionalidades comunes para toda la aplicación.
 * 
 * - Verificación de sesión activa.
 * - Registro de eventos en bitácora con IP y datos de usuario.
 * - Visualización y exportación PDF del historial de bitácora.
 * 
 * Todos los controladores del sistema deben extender esta clase para heredar
 * los métodos de auditoría y seguridad.
 * 
 * Dependencias:
 * - BitacoraModel para el registro y consulta de eventos.
 * - Dompdf para generación de PDF.
 * - Servicio de paginación de CodeIgniter.
 */
namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\BitacoraModel;

abstract class BaseController extends Controller
{
    protected $request;

        /**
     * Inicializa el controlador (llamado automáticamente por CodeIgniter).
     * 
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param LoggerInterface   $logger
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {  
        parent::initController($request, $response, $logger);
    }

    // Verificación sesión activa
    protected function estaLogueado(): bool
    {
        return (bool) session()->get('logged_in');
    }

        /**
     * Registra un evento en la bitácora del sistema.
     * 
     * Obtiene automáticamente el ID del usuario desde la sesión (puede ser null)
     * y la dirección IP desde la petición.
     * 
     * @param string $accion          Acción realizada (ej. 'Inició sesión').
     * @param string $tipo_solicitud  Categoría del evento (ej. 'Sesión', 'Seguridad'). Por defecto 'Ninguna'.
     * @param string $registro        Descripción detallada del evento. Por defecto ''.
     * 
     * @example
     * $this->registrarBitacora('Eliminó usuario', 'Gestión', "Usuario ID 5 eliminado por admin");
     */
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

        /**
     * Muestra la vista de bitácora con filtros y paginación.
     * 
     * - Solo accesible si el usuario está logueado.
     * - Filtros soportados vía GET: buscar (texto), tipo, desde, hasta (fechas).
     * - Paginación de 8 registros por página.
     * - Utiliza el modelo BitacoraModel para obtener datos filtrados y el total.
     * - Almacena la paginación en el servicio 'default' de pager.
     * 
     * @return mixed Vista 'Bitacora/bitacora' con datos o redirección a login.
     * 
     * @example
     * GET /base/bitacora?buscar=admin&tipo=Sesión&page=2
     */
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

        /**
     * Genera un PDF con el reporte de bitácora filtrado y rango de páginas.
     * 
     * - Solo accesible si el usuario está logueado.
     * - Obtiene los mismos filtros que bitacora() vía GET.
     * - Parámetros adicionales: pagina_inicio y pagina_fin (por defecto igual a inicio).
     * - Calcula límite y offset para obtener los registros de las páginas seleccionadas
     *   (8 registros por página).
     * - Limpia los buffers de salida para evitar corrupción del PDF.
     * - Renderiza la vista 'Bitacora/bitacora_pdf' y genera la descarga del PDF.
     * 
     * @return void Descarga forzada del archivo PDF (Attachment).
     * 
     * @example
     * GET /base/generarPdfBitacora?buscar=error&pagina_inicio=1&pagina_fin=3
     */
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