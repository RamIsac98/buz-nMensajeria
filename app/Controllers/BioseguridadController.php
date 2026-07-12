<?php

/**
 * Controlador para la gestión de solicitudes de bioseguridad.
 * 
 * Permite crear, editar, registrar, actualizar y generar PDF de solicitudes
 * de bioseguridad (contenedores, bolsas rojas, quien retira, etc.).
 * 
 * Hereda de BaseController para usar verificación de sesión y registro de bitácora.
 * 
 * Dependencias:
 * - SolicitudBioseguridadModel para operaciones CRUD.
 * - UsuarioModel para obtener datos del usuario.
 * - Dompdf para generación de PDF.
 */

namespace App\Controllers;

use App\Models\SolicitudBioseguridadModel;
use App\Models\UsuarioModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class BioseguridadController extends BaseController
{
        /**
     * Muestra el formulario para crear una nueva solicitud de bioseguridad.
     * 
     * - Verifica sesión activa.
     * - Obtiene los datos del usuario actual (incluyendo departamento y laboratorio).
     * - Genera automáticamente un código único de solicitud y la fecha actual.
     * 
     * @return mixed Vista 'bioseguridad/formulario' con datos del usuario, código y fecha, o redirección a login.
     * 
     * @example
     * GET /bioseguridad/crear
     */
    public function crear()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $usuarioModel = new UsuarioModel();
        $solicitudModel = new SolicitudBioseguridadModel();

        $userId = session()->get('usuario_id');
        //busca el usuario actual en la BD y ver relacion con el departamento/laboratorio
        $usuario = $usuarioModel->findById($userId);
        $usuario['departamento'] = $usuario['departamento'] ?? 'No Asignado';
        $usuario['nombre_laboratorio'] = $usuario['nombre_laboratorio'] ?? 'No Asignado';
        $data = [
            'usuario_data'      => $usuario,
            //carga automatica en el formulario
            'codigo_automatico' => $solicitudModel->generarCodigoUnico(),
            'fecha_automatica'  => date('d/m/Y')
        ];
        return view('bioseguridad/formulario', $data);
    }

        /**
     * Procesa el registro de una nueva solicitud de bioseguridad en la base de datos.
     * 
     * - Verifica sesión activa.
     * - Aplica validaciones: contenedores ≤ 3, cada tipo de bolsa ≤ 10.
     * - Si 'quien_retira' es 'otra_persona', guarda el nombre; en caso contrario, null.
     * - Genera código automático si no se envía.
     * - Inserta en la tabla solicitudes_bioseguridad.
     * - Registra en bitácora la operación.
     * 
     * @return mixed Redirección con mensaje de éxito o error.
     * 
     * @example
     * POST /bioseguridad/registrar (con datos del formulario)
     */
    public function registrar()
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $solicitudModel = new SolicitudBioseguridadModel();
        $contenedores = (int)$this->request->getPost('contenedores_pulso_cantidad');

        //condiciones en el form
        if ($contenedores > 3) return redirect()->back()->with('error', 'La cantidad de Contenedores Pulso Cortante no puede superar 3.');

        $peq = (int)$this->request->getPost('bolsas_rojas_pequena');
        $med = (int)$this->request->getPost('bolsas_rojas_mediana');
        $gra = (int)$this->request->getPost('bolsas_rojas_grande');

        if ($peq > 10 || $med > 10 || $gra > 10) return redirect()->back()->with('error', 'Cada tamaño de bolsa roja tiene un límite máximo de 10 unidades.');

        $quienRetira = $this->request->getPost('quien_retira');
        //condicion si es otra persona
        $nombreOtra = ($quienRetira === 'otra_persona') ? $this->request->getPost('nombre_otra_persona') : null;
        $codigo = $this->request->getPost('codigo_solicitud');

        if (empty($codigo)) $codigo = $solicitudModel->generarCodigoUnico();

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

        if ($solicitudModel->insertarSolicitud($insertData)) {
            $this->registrarBitacora('Registro de Solicitud', 'Servicio Bioseguridad', "Se generó la solicitud: " . $codigo);
            return redirect()->to(base_url('desechos/registroSolicitudes'))->with('success', 'Solicitud de Bioseguridad registrada correctamente.');
        } else {
            return redirect()->back()->with('error', 'Error al guardar en la base de datos.');
        }
    }
    
       /**
     * Genera un PDF de una solicitud de bioseguridad específica y lo muestra en el navegador.
     * 
     * - Verifica sesión activa.
     * - Obtiene la solicitud por ID (usa método find() de Model).
     * - Obtiene los datos del usuario asociado.
     * - Construye el nombre completo del usuario (nombre + apellido o username).
     * - Renderiza la vista 'bioseguridad/plantilla_pdf' y genera el PDF con Dompdf.
     * - El PDF se muestra en línea (Attachment = false).
     * 
     * @param int $id ID de la solicitud.
     * @return void Descarga o visualización del PDF.
     * 
     * @example
     * GET /bioseguridad/generarPdf/5
     */
    public function generarPdf($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $solicitudModel = new SolicitudBioseguridadModel();
        $solicitud = $solicitudModel->find($id);

        if (!$solicitud) {
            return redirect()->back()->with('error', 'Solicitud no encontrada.');
        }

        $usuarioModel = new \App\Models\UsuarioModel();
        $usuario = $usuarioModel->findById($solicitud['usuario_id']);

        $nombreCompleto = trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''));
        if (empty($nombreCompleto)) {
            $nombreCompleto = $usuario['username'] ?? 'Usuario';
        }

        $data = $solicitud;
        $data['usuario_nombre'] = $nombreCompleto;
        $data['departamento']   = $usuario['departamento'] ?? 'No asignado';
        $data['laboratorio']    = $usuario['nombre_laboratorio'] ?? 'No asignado';
        $data['fecha_registro'] = date('d/m/Y H:i:s', strtotime($solicitud['fecha_registro']));

        $html = view('bioseguridad/plantilla_pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream('bioseguridad_' . $solicitud['codigo_solicitud'] . '.pdf', ['Attachment' => false]);
        exit;
    }
        /**
     * Muestra un archivo PDF previamente guardado en el servidor (en modo inline).
     * 
     * - Verifica sesión activa.
     * - Busca el archivo en la carpeta 'uploads/pdfs/' dentro del directorio público.
     * - Si existe, limpia el buffer y envía el PDF con cabeceras adecuadas para visualización.
     * - Si no existe, retorna un mensaje de error.
     * 
     * @param string $nombreArchivo Nombre del archivo PDF a mostrar.
     * @return mixed Contenido del PDF con cabeceras o mensaje de error.
     * 
     * @example
     * GET /bioseguridad/verPdf/mi_archivo.pdf
     */
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

        /**
     * Muestra el formulario de edición de una solicitud de bioseguridad.
     * 
     * - Verifica sesión activa.
     * - Busca la solicitud por ID.
     * - Verifica que no haya sido editada previamente (editado == 1).
     * - Verifica permisos: solo el usuario creador o administrador pueden editar.
     * - Prepara los datos del usuario y de la solicitud para la vista de edición.
     * 
     * 
     * @param int $id ID de la solicitud.
     * @return mixed Vista 'bioseguridad/editar' con datos o redirección con error.
     * 
     * @example
     * GET /bioseguridad/editar/10
     */
    public function editar($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $solicitudModel = new SolicitudBioseguridadModel();
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

        return view('bioseguridad/editar', $data);
    }

        /**
     * Procesa la actualización de una solicitud de bioseguridad existente.
     * 
     * - Verifica sesión activa.
     * - Busca la solicitud por ID.
     * - Valida que no esté editada previamente y que el usuario tenga permisos.
     * - Aplica las mismas validaciones de cantidad (contenedores ≤3, bolsas ≤10).
     * - Actualiza los campos y marca el campo 'editado' como 1.
     * - Registra la operación en bitácora.
     * 
     * 
     * @param int $id ID de la solicitud.
     * @return mixed Redirección con mensaje de éxito o error.
     * 
     * @example
     * POST /bioseguridad/actualizar/10 (con datos del formulario)
     */
    public function actualizar($id)
    {
        if (!$this->estaLogueado()) return redirect()->to(base_url('login'));

        $solicitudModel = new SolicitudBioseguridadModel();
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

        $contenedores = (int)$this->request->getPost('contenedores_pulso_cantidad');
        
        if ($contenedores > 3) return redirect()->back()->with('error', 'La cantidad de Contenedores Pulso Cortante no puede superar 3.');

        $peq = (int)$this->request->getPost('bolsas_rojas_pequena');
        $med = (int)$this->request->getPost('bolsas_rojas_mediana');
        $gra = (int)$this->request->getPost('bolsas_rojas_grande');

        if ($peq > 10 || $med > 10 || $gra > 10) return redirect()->back()->with('error', 'Cada tamaño de bolsa roja tiene un límite máximo de 10 unidades.');

        $quienRetira = $this->request->getPost('quien_retira');
        $nombreOtra = ($quienRetira === 'otra_persona') ? $this->request->getPost('nombre_otra_persona') : null;

        $updateData = [
            'ext_telefono'                => $this->request->getPost('ext_telefono'),
            'contenedores_pulso_cantidad' => $contenedores,
            'bolsas_rojas_pequena'        => $peq,
            'bolsas_rojas_mediana'        => $med,
            'bolsas_rojas_grande'         => $gra,
            'quien_retira'                => $quienRetira,
            'nombre_otra_persona'         => $nombreOtra,
            'editado' => 1,
        ];

        $solicitudModel->update($id, $updateData);
        $this->registrarBitacora('Edición de Solicitud', 'Servicio Bioseguridad', "Se editó la solicitud: " . $solicitud['codigo_solicitud']);

        return redirect()->to(base_url('desechos/registroSolicitudes'))->with('success', 'Solicitud actualizada correctamente.');
    }
}