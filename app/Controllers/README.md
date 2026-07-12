# Controladores del Sistema - Resumen

## Resumen general

Los controladores manejan la lógica de negocio, autenticación, gestión de sesiones y operaciones CRUD del sistema. Heredan de `BaseController` que centraliza la verificación de sesión y el registro de auditoría en bitácora. Todos los controladores utilizan SQL directo a través de los modelos y emplean **Dompdf** para generación de reportes en PDF.

---

## Controladores

### `Login` (Login.php)
**Ruta:** `App\Controllers\Login`  
**Propósito:** Autenticación, gestión de sesiones y recuperación de contraseña mediante pregunta de seguridad.

**Funciones importantes:**
- `index()` – Muestra el formulario de inicio de sesión.
- `autenticar()` – Valida credenciales, verifica estado del usuario (status), crea sesión y redirige según rol.
  - Roles: `proteccion_integral` → `/dashboard`; `administrador` → `/usuarios/bitacora`; otros → `/desechos/registroSolicitudes`.
  - Si el usuario no tiene pregunta de seguridad configurada → redirige a `usuarios/configurar_pregunta`.
- `salir()` – Registra cierre de sesión y destruye la sesión.
- `olvideContrasena()` – Muestra formulario para ingresar cédula.
- `validarCedula()` – Busca usuario por tipo y número de cédula, valida que tenga pregunta de seguridad configurada.
- `nuevaClave()` – Valida respuesta de seguridad, actualiza contraseña y registra en bitácora.
- `preguntaSegError()` – Método privado para reenviar vista de pregunta con mensaje de error.

---

### `BaseController` (BaseController.php)
**Ruta:** `App\Controllers\BaseController` (abstracto)  
**Propósito:** Controlador base que provee funcionalidades comunes a todos los controladores del sistema.

**Funciones importantes:**
- `estaLogueado()` – Retorna `true` si existe la clave `logged_in` en sesión.
- `registrarBitacora()` – Registra eventos en la bitácora con IP, usuario, acción y tipo.
- `bitacora()` – Muestra el historial de eventos con filtros (buscar, tipo, desde, hasta) y paginación (8 registros por página).
- `generarPdfBitacora()` – Genera PDF de la bitácora para un rango de páginas seleccionado.

---

### `BioseguridadController` (BioseguridadController.php)
**Ruta:** `App\Controllers\BioseguridadController`  
**Propósito:** Gestión de solicitudes de bioseguridad (contenedores, bolsas rojas, quien retira).

**Funciones importantes:**
- `crear()` – Muestra formulario de creación con código automático y datos del usuario.
- `registrar()` – Procesa el registro con validaciones (contenedores ≤ 3, bolsas ≤ 10 por tamaño).
- `generarPdf()` – Genera PDF de una solicitud específica con datos del usuario y fecha formateada.
- `verPdf()` – Muestra un PDF previamente guardado en el servidor (modo inline).
- `editar()` – Muestra formulario de edición; verifica que no esté editada previamente y permisos (creador o administrador).
- `actualizar()` – Procesa la actualización y marca `editado = 1` para evitar múltiples ediciones.

---

### `DashboardController` (DashboardController.php)
**Ruta:** `App\Controllers\DashboardController`  
**Propósito:** Muestra estadísticas de solicitudes de desechos entregados para el rol `proteccion_integral`.

**Funciones importantes:**
- `index()` – Página principal del dashboard.
  - Solo accesible para rol `proteccion_integral`; otros roles redirigen a `/desechos/registroSolicitudes`.
  - Obtiene años disponibles y selecciona el año (vía GET `anio`) o el más reciente.
  - Filtra por trimestre (GET `trimestre`) o muestra todos.
  - Obtiene datos del gráfico trimestral (labels y values en JSON para JavaScript).
  - Obtiene datos diarios y los agrupa por mes para tabla detallada.
  - Calcula el total general sumando los valores del gráfico.

---

### `DesechosController` (DesechosController.php)
**Ruta:** `App\Controllers\DesechosController`  
**Propósito:** Gestión completa de solicitudes de desechos biológicos (creación, edición, cambio de estado, PDF, gestión combinada con bioseguridad).

**Funciones importantes:**
- `crear()` – Muestra formulario de creación con código automático y datos del usuario.
- `registrar()` – Procesa el registro con validación extensiva (`validarDatosSolicitud`) y manejo de arrays múltiples.
- `validarDatosSolicitud()` – Valida campos requeridos, tipos de desecho (B, C, D), estados físicos (Líquido/Sólido), empaques, esterilizado, variantes, pesos y extensión telefónica.
- `generarPdf()` – Genera PDF de una solicitud específica.
- `registroSolicitudes()` – Muestra historial combinado de desechos y bioseguridad con filtros y paginación manual.
- `gestionSolicitudes()` – Panel de gestión (solo administradores y protección integral) con opción de cambio de estado.
- `actualizarEstado()` – Endpoint AJAX para cambiar estado de solicitud (Pendiente/Entregado/Cancelado).
- `editar()` / `actualizar()` – Edición de solicitudes con bloqueo tras primera edición (`editado = 1`).
- `obtenerPeso()` / `actualizarPeso()` – Endpoints AJAX para obtener y actualizar pesos con validación por estado físico (Sólido/Líquido) y protección contra cambios a 0.



---

### `GestionController` (GestionController.php)
**Ruta:** `App\Controllers\GestionController`  
**Propósito:** CRUD de centros (departamentos) y laboratorios con gestión de relaciones y reportes PDF.

**Funciones importantes:**
- `index()` – Página principal con listados paginados de departamentos y laboratorios (8 por página) y filtro por departamento.
- `guardarDepartamento()` / `editarDepartamento()` – Wrappers para guardar/editar departamentos.
- `guardarLaboratorio()` / `editarLaboratorio()` – Wrappers para guardar/editar laboratorios.
- `procesarGuardado()` – Método privado que maneja validación (incluyendo unicidad `is_unique`) y operaciones CRUD para ambos tipos.
- `eliminarDepartamento()` / `eliminarLaboratorio()` – Wrappers para eliminación.
- `procesarEliminacion()` – Método privado que maneja la eliminación y registro en bitácora.
- `generarPdfGeneral()` – Genera PDF con estructura organizacional (departamentos, laboratorios, usuarios) en landscape.
- `generarPdfLaboratorios()` – Genera PDF con listado de laboratorios filtrados por departamento en portrait.



---

### `Usuarios` (Usuarios.php)
**Ruta:** `App\Controllers\Usuarios`  
**Propósito:** Gestión completa de usuarios (CRUD, filtros, PDF, seguridad, cambio de contraseña).

**Funciones importantes:**
- `verificarAccesoGestion()` – Método privado que valida que el usuario tenga rol `administrador` o `proteccion_integral`.
- `index()` – Listado de usuarios con filtros (buscar, rol, estado) y paginación (8 por página).
- `generarPdfUsuarios()` – Genera PDF del listado de usuarios para un rango de páginas seleccionado.
- `editar()` – Muestra formulario de edición con carga de departamentos y laboratorios.
- `actualizar()` – Procesa actualización con validaciones exhaustivas (username, cédula, nombre, apellido, rol, laboratorio) y permite cambio de contraseña o eliminación de pregunta de seguridad.
- `deshabilitar()` – Cambia estado (activo/inactivo) del usuario; no permite deshabilitar la propia cuenta.
- `eliminar()` – Elimina físicamente un usuario; no permite eliminar la propia cuenta.
- `crear()` / `guardar()` – Creación de nuevos usuarios con validación completa y verificación de cédula única.
- `obtener_laboratorios_por_depto()` – Endpoint AJAX para obtener laboratorios por departamento (para selectores dinámicos).
- `configurar_pregunta()` – Muestra formulario para configurar pregunta de seguridad (primer ingreso).
- `guardar_pregunta()` – Guarda pregunta y respuesta (hasheada) en la base de datos.
- `cambiar_password_post()` – Cambio de contraseña del usuario autenticado (requiere contraseña actual).
- `solicitudDesechos()` / `procesarSolicitud()` – Métodos placeholder/aparentemente 



---

## Observaciones generales

- Todos los controladores extienden `BaseController` para heredar autenticación y auditoría.
- Se utiliza **SQL directo** en los modelos y **Dompdf** para generación de PDFs.
- La mayoría de los controladores implementan filtros y paginación, aunque algunos usan `array_slice` manual en lugar del pager nativo de CodeIgniter.
- Se recomienda refactorizar código duplicado (especialmente en `DesechosController`).
- Los métodos `getSolicitudes()` en el modelo `SolicitudDesechosModel` tiene un JOIN incorrecto que debe corregirse si se utiliza.

---

*Última actualización: 2026-07-12*