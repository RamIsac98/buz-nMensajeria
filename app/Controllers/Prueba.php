<?php

namespace App\Controllers;

class Prueba extends BaseController
{
    public function probarConexion()
    {
        $session = session();

        // 1. SIMULACIÓN: Guardamos en la sesión el ID del usuario que creamos en el paso 1
        $session->set([
            'usuario_id' => 1,
            'username'   => 'admin',
            'rol'        => 'administrador'
        ]);

        // 2. Usamos nuestra nueva función global con una sola línea
        // Parámetros: (Accion, Tipo de Solicitud, Registro/Detalles)
        $this->registrarBitacora('Inició sesión en el sistema', 'Ninguna', 'El usuario admin ingresó al panel principal');

        echo "<h1>¡Bitácora Automatizada con Éxito! 🚀</h1>";
        echo "<p>Se ha simulado el login del usuario ID: 1 y se registró en la bitácora automáticamente.</p>";
    }
}