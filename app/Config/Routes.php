<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
//login - interfaz inicial
$routes->get('/', 'Home::index');
$routes->get('prueba/probarconexion', 'Prueba::probarConexion');
$routes->get('login', 'Login::index');
$routes->post('login/autenticar', 'Login::autenticar');
$routes->get('login/salir', 'Login::salir');
$routes->get('interfazinicial/menuusuario', 'MenuUsuario::index');

// gestion de usuarios
$routes->get('usuarios', 'Usuarios::index');
$routes->get('usuarios/crear', 'Usuarios::crear');
$routes->post('usuarios/guardar', 'Usuarios::guardar');
$routes->get('usuarios/editar/(:num)', 'Usuarios::editar/$1');
$routes->post('usuarios/actualizar/(:num)', 'Usuarios::actualizar/$1');
$routes->get('usuarios/deshabilitar/(:num)', 'Usuarios::deshabilitar/$1');
$routes->get('usuarios/eliminar/(:num)', 'Usuarios::eliminar/$1'); 
$routes->post('usuarios/cambiar_password_post', 'Usuarios::cambiar_password_post');

// pregunta de seguridad
$routes->get('usuarios/configurar_pregunta', 'Usuarios::configurar_pregunta');
$routes->post('usuarios/guardar_pregunta', 'Usuarios::guardar_pregunta');

// recuperación de contraseña
$routes->get('login/olvide_contrasena', 'Login::olvideContrasena');
$routes->post('login/validar_usuario', 'Login::validarCedula');
$routes->post('login/guardar_nueva_clave', 'Login::nuevaClave');

// bitacora
$routes->get('usuarios/Bitacora/bitacora', 'Usuarios::bitacora');
$routes->get('usuarios/bitacora', 'Usuarios::bitacora');
$routes->get('usuarios/generarPdfBitacora', 'Usuarios::generarPdfBitacora');
$routes->get('usuarios/generarPdfUsuarios', 'Usuarios::generarPdfUsuarios');

// Gestion de Centro/Departamento
$routes->get('gestion-departamento', 'GestionController::index');

// Procesamiento de formularios
$routes->post('gestion-departamento/guardar-departamento', 'GestionController::guardarDepartamento');
$routes->post('gestion-departamento/guardar-laboratorio', 'GestionController::guardarLaboratorio');
$routes->post('gestion-departamento/editar-departamento', 'GestionController::editarDepartamento');
$routes->post('gestion-departamento/editar-laboratorio', 'GestionController::editarLaboratorio');
$routes->post('gestion-departamento/eliminar-departamento/(:num)', 'GestionController::eliminarDepartamento/$1');
$routes->post('gestion-departamento/eliminar-laboratorio/(:num)', 'GestionController::eliminarLaboratorio/$1');
$routes->get('gestion-departamento/generar-pdf', 'GestionController::generarPdfLaboratorios');
$routes->get('usuarios/obtener_laboratorios_por_depto/(:num)', 'Usuarios::obtener_laboratorios_por_depto/$1');
$routes->get('gestion-departamento/generar-pdf-general', 'GestionController::generarPdfGeneral');

//solicitud de Desechos
$routes->get('desechos/formulario', 'DesechosController::crear');
$routes->post('desechos/registrar', 'DesechosController::registrar');
$routes->get('desechos/registroSolicitudes', 'DesechosController::registroSolicitudes');

//solicitud de Bioseguridad
$routes->get('solicitud_bioseguridad', 'BioseguridadController::crear');
$routes->post('bioseguridad/registrar', 'BioseguridadController::registrar');
$routes->get('bioseguridad/formulario', 'BioseguridadController::crear');

$routes->get('desechos/gestionSolicitudes', 'DesechosController::gestionSolicitudes');
$routes->post('desechos/actualizarEstado', 'DesechosController::actualizarEstado');

$routes->get('desechos/generarPdf/(:num)', 'DesechosController::generarPdf/$1');
$routes->get('bioseguridad/generarPdf/(:num)', 'BioseguridadController::generarPdf/$1');


$routes->get('desechos/editar/(:num)', 'DesechosController::editar/$1');
$routes->get('bioseguridad/editar/(:num)', 'BioseguridadController::editar/$1');
$routes->match(['post', 'put'], 'desechos/actualizar/(:num)', 'DesechosController::actualizar/$1');
$routes->match(['post', 'put'], 'bioseguridad/actualizar/(:num)', 'BioseguridadController::actualizar/$1');

// Ruta para obtener datos en JSON (GET)
$routes->get('desechos/obtenerPeso/(:num)', 'DesechosController::obtenerPeso/$1');
// Ruta para actualizar peso (POST)
$routes->post('desechos/actualizarPeso/(:num)', 'DesechosController::actualizarPeso/$1');