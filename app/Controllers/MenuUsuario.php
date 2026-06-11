<?php

namespace App\Controllers; // 1. Se limpia el namespace porque el archivo está en la raíz de Controllers

class MenuUsuario extends BaseController
{
    public function index()
    {
        if (!$this->estaLogueado()) {
            return redirect()->to(base_url('login'));
        }

        // 2. Aquí SÍ se especifica la carpeta real de tu vista visual (app/Views/InterfazInicial/MenuUsuario.php)
        // Asegúrate de escribirlo respetando las mayúsculas/minúsculas exactas de tu carpeta en app/Views/
        return view('InterfazInicial/MenuUsuario');
    }
}