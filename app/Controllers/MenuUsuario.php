<?php

namespace App\Controllers; 

class MenuUsuario extends BaseController
{
    public function index()
    {
        if (!$this->estaLogueado()) {
            return redirect()->to(base_url('login'));
        }

        return view('InterfazInicial/MenuUsuario');
    }
}