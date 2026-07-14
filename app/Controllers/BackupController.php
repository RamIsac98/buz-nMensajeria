<?php
// app/Controllers/BackupController.php

namespace App\Controllers;

use CodeIgniter\Controller;

class BackupController extends Controller
{
    public function __construct()
    {
        helper('backup');
    }

    /**
     * Verifica si el usuario tiene permisos de administrador
     * Siguiendo la misma lógica que en Usuarios.php
     */
    private function esAdministrador(): bool
    {
        $rol = session()->get('rol');
        return ($rol === 'administrador');
    }

    /**
     * Verifica si el usuario está logueado
     */
    private function estaLogueado(): bool
    {
        return session()->get('logged_in') === true;
    }

    /**
     * Vista principal (opcional)
     */
    public function index()
    {
        if (!$this->estaLogueado()) {
            return redirect()->to(base_url('login'))->with('error', 'Debes iniciar sesión.');
        }
        if (!$this->esAdministrador()) {
            return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'Acceso denegado: No tienes permisos.');
        }

        $backups = list_backups();
        return view('backup/index', ['backups' => $backups]);
    }

    /**
     * Crear un nuevo backup (vía AJAX)
     */
    public function create()
    {
        // Verificar autenticación y permisos
        if (!$this->estaLogueado()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debes iniciar sesión.'
            ]);
        }
        if (!$this->esAdministrador()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.'
            ]);
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Petición no válida'
            ]);
        }

        try {
            $filename = create_database_backup();
            
            // Limpiar backups antiguos (mantener solo los últimos 30)
            clean_old_backups(30);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Backup creado exitosamente',
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Listar backups (vía AJAX)
     */
    public function list()
    {
        if (!$this->estaLogueado()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debes iniciar sesión.'
            ]);
        }
        if (!$this->esAdministrador()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.'
            ]);
        }

        $backups = list_backups();
        return $this->response->setJSON($backups);
    }

    /**
     * Descargar un backup
     */
    public function download($filename = null)
    {
        if (!$this->estaLogueado()) {
            return redirect()->to(base_url('login'))->with('error', 'Debes iniciar sesión.');
        }
        if (!$this->esAdministrador()) {
            return redirect()->to(base_url('desechos/registroSolicitudes'))->with('error', 'Acceso denegado: No tienes permisos.');
        }

        if (!$filename) {
            $backups = list_backups();
            if (empty($backups)) {
                return redirect()->back()->with('error', 'No hay backups disponibles');
            }
            $filename = $backups[0]['filename'];
        }

        $filename = basename($filename);
        if (!preg_match('/^backup_.*\.sql(\.gz)?$/', $filename)) {
            return redirect()->back()->with('error', 'Archivo no válido');
        }

        $path = WRITEPATH . 'backups/' . $filename;
        
        if (file_exists($path)) {
            return $this->response->download($path, null)
                                  ->setFileName($filename)
                                  ->setContentType('application/sql');
        }
        
        return redirect()->back()->with('error', 'Archivo no encontrado');
    }

    /**
     * Eliminar un backup
     */
    public function delete($filename)
    {
        if (!$this->estaLogueado()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debes iniciar sesión.'
            ]);
        }
        if (!$this->esAdministrador()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.'
            ]);
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Petición no válida'
            ]);
        }

        $filename = basename($filename);
        if (!preg_match('/^backup_.*\.sql(\.gz)?$/', $filename)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Archivo no válido'
            ]);
        }

        if (delete_backup($filename)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Backup eliminado correctamente'
            ]);
        }
        
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Error al eliminar el backup'
        ]);
    }
}