# Vistas de Usuarios - Resumen

## Resumen general

Las vistas de la carpeta `usuarios/` gestionan la administración de usuarios del sistema. Proporcionan funcionalidades completas: listado con filtros y paginación, creación, edición, cambio de estado (habilitar/deshabilitar), eliminación física de usuarios y generación de reportes en PDF. Todas las vistas extienden el layout base y utilizan **Bootstrap 5** para la interfaz, **SweetAlert2** para mensajes flash y **AJAX** para carga dinámica de laboratorios.

---

## Estructura de archivos

| Archivo | Propósito | Controlador asociado |
|---------|-----------|---------------------|
| `index.php` | Listado de usuarios con filtros, paginación y acciones (editar, habilitar/deshabilitar, eliminar, PDF). | `Usuarios::index()` (GET), `Usuarios::editar()`, `Usuarios::deshabilitar()`, `Usuarios::eliminar()`, `Usuarios::generarPdfUsuarios()` |
| `crear.php` | Formulario de creación de nuevo usuario. | `Usuarios::crear()` (GET), `Usuarios::guardar()` (POST), `Usuarios::obtener_laboratorios_por_depto()` (AJAX) |
| `editar.php` | Formulario de edición de usuario existente (con cambio de contraseña opcional y eliminación de pregunta de seguridad). | `Usuarios::editar($id)` (GET), `Usuarios::actualizar($id)` (POST), `Usuarios::obtener_laboratorios_por_depto()` (AJAX) |
| `usuarios_pdf.php` | Plantilla HTML para el PDF del reporte de usuarios. | `Usuarios::generarPdfUsuarios()` (GET) |

---

## Detalle por archivo

### `index.php`
**Propósito:** Listado paginado de usuarios con filtros y acciones.  
**Extiende el layout:** `layouts/base`.

**Conexiones con el controlador:**
- **Carga y filtros (GET):** `Usuarios::index()` (ruta `/usuarios`).  
  Filtros vía `$_GET`: `buscar`, `rol`, `estado`.  
  Paginación vía `page`.
- **Crear usuario:** Botón "Crear Nuevo Usuario" → `base_url('usuarios/crear')` → `Usuarios::crear()`.
- **Editar usuario:** Botón lápiz → `base_url('usuarios/editar/'.$user['id'])` → `Usuarios::editar($id)`.
- **Cambiar estado:** Slider → modal con `data-url` a `usuarios/deshabilitar/{id}` → `Usuarios::deshabilitar($id)` (GET).
- **Eliminar usuario:** Botón papelera → modal con `data-url` a `usuarios/eliminar/{id}` → `Usuarios::eliminar($id)` (GET).
- **Generar PDF:** Modal PDF → formulario GET a `usuarios/generarPdfUsuarios` → `Usuarios::generarPdfUsuarios()`.
- **Limpiar filtros:** Enlace a `usuarios` sin parámetros → `Usuarios::index()`.
- **Mensajes flash:** `success`, `error`, `usuario_eliminado` (SweetAlert2).
- **Protección de cuenta propia:** No muestra acciones para el usuario autenticado.

**Características:**
- Tabla con columnas: ID, Nombre Completo, Username, Cédula, Rol, Pregunta de Seguridad, Acciones.
- Barra de filtros con búsqueda por texto, rol y estado (Activo/Inactivo).
- Paginación con estilo personalizado (3 botones visibles alrededor de la página actual).
- Botón "Limpiar Filtros" con ícono de refresco.
- Modal de confirmación para cambio de estado y eliminación (con mensajes específicos).

---

### `crear.php`
**Propósito:** Formulario para registrar un nuevo usuario en el sistema.  
**Extiende el layout:** `layouts/base`.

**Conexiones con el controlador:**
- **Carga inicial (GET):** `Usuarios::crear()` (ruta `/usuarios/crear`).  
  Recibe: `$departamentos` – lista de centros para el selector.
- **Envío del formulario (POST):** `action="<?= base_url('usuarios/guardar') ?>"` → `Usuarios::guardar()`.
- **Carga dinámica de laboratorios (AJAX GET):**  
  URL: `<?= site_url('usuarios/obtener_laboratorios_por_depto') ?>/${deptoId}` → `Usuarios::obtener_laboratorios_por_depto($departamento_id)`.  
  Se dispara al cambiar el select de "Centro".
- **Cancelar:** Enlace `usuarios` → `Usuarios::index()`.
- **Mensajes flash:** `success` o `error` (SweetAlert2) generados por `Usuarios::guardar()`.

**Validaciones en cliente (modal de errores):**
- Nombre, apellido: obligatorios, mínimo 6, máximo 25, solo letras y espacios.
- Username: obligatorio, mínimo 3, sin espacios, solo letras.
- Tipo de cédula: obligatorio.
- Cédula: obligatoria, solo números, entre 6 y 10 dígitos.
- Rol, Centro, Laboratorio: obligatorios.
- Contraseña: obligatoria, mínimo 6 caracteres, sin espacios.
- Modal de confirmación antes del envío.

---

### `editar.php`
**Propósito:** Editar los datos de un usuario existente, con opción de cambiar contraseña y eliminar la pregunta de seguridad.  
**Extiende el layout:** `layouts/base`.

**Conexiones con el controlador:**
- **Carga inicial (GET):** `Usuarios::editar($id)` (ruta `/usuarios/editar/{id}`).  
  Recibe: `$usuario`, `$departamentos`, `$id_departamento_actual`, `$laboratorios` (precargados).
- **Envío del formulario (POST):** `action="<?= base_url('usuarios/actualizar/'.$usuario['id']) ?>"` → `Usuarios::actualizar($id)`.
- **Carga dinámica de laboratorios:** Mismo AJAX que en `crear.php`.
- **Cancelar:** Enlace `usuarios` → `Usuarios::index()`.
- **Mensajes flash:** `success` o `error` (SweetAlert2) generados por `Usuarios::actualizar()`.

**Características adicionales:**
- Campo de contraseña **opcional**: si se deja vacío, no se actualiza; si se llena, debe tener mínimo 6 caracteres y sin espacios.
- Sección de seguridad: muestra la pregunta de seguridad actual (si existe) y un checkbox para eliminarla (campo `eliminar_pregunta`).  
  En el controlador, si este campo está presente, se establecen `pregunta_seguridad` y `respuesta_seguridad` como NULL.
- Validación en cliente similar a la de creación.

---

### `usuarios_pdf.php`
**Propósito:** Plantilla HTML para el PDF del reporte de usuarios.  
**No extiende el layout base** (vista independiente, sin estilos externos).

**Conexiones con el controlador:**
- **Generación del PDF (GET):** `Usuarios::generarPdfUsuarios()` (ruta `/usuarios/generarPdfUsuarios`).  
  Recibe filtros y rango de páginas, obtiene los registros mediante `UsuarioModel::getUsuariosFiltrados()` y pasa el array `$usuarios` a la vista.
- **Variables recibidas:** `$usuarios` – lista de usuarios a incluir en el PDF.
- **Salida:** PDF con formato A4 portrait, descarga automática (`Attachment = true`) con nombre `Reporte_Usuarios_Paginas_{inicio}_al_{fin}.pdf`.

**Características:**
- Tabla con: ID, Nombre Completo, Usuario, Cédula, Rol, Estado.
- Cabecera con título y fecha de generación.
- Pie de página fijo.

---

## Flujo de trabajo típico

1. **Acceso al listado:** Usuario con rol `administrador` o `proteccion_integral` navega a `/usuarios` (acceso restringido por el controlador).
2. **Filtrado y paginación:** Aplica filtros (buscar, rol, estado) y navega entre páginas.
3. **Creación de usuario:** Presiona "Crear Nuevo Usuario" → completa el formulario con validación en cliente → confirma → envía POST a `guardar` → el controlador valida, registra en BD y redirige con mensaje.
4. **Edición de usuario:** Botón lápiz → carga `editar/{id}` con datos precargados → modifica campos (contraseña opcional, eliminar pregunta de seguridad) → confirma → envía POST a `actualizar/{id}` → el controlador actualiza y registra en bitácora.
5. **Cambio de estado (habilitar/deshabilitar):** Slider → modal de confirmación → redirige a `deshabilitar/{id}` → el controlador cambia `status` y registra en bitácora.
6. **Eliminación de usuario:** Botón papelera → modal de confirmación (con advertencia de acción permanente) → redirige a `eliminar/{id}` → el controlador elimina físicamente el registro y registra en bitácora.
7. **Generación de PDF:** Botón PDF → modal para seleccionar rango de páginas → envía GET a `generarPdfUsuarios` con filtros y rango → el controlador obtiene los registros, genera el PDF y lo descarga.

---

## Observaciones importantes

1. **Seguridad y permisos:** Todos los métodos del controlador `Usuarios` verifican sesión y rol (`administrador` o `proteccion_integral`) mediante `verificarAccesoGestion()`. La vista también oculta acciones para la cuenta propia (protegida).
2. **Bitácora:** Las operaciones de creación, edición, cambio de estado y eliminación registran eventos en la bitácora mediante `registrarBitacora()` (heredado de `BaseController`).
3. **Carga dinámica de laboratorios:** Se utiliza `fetch` con `site_url()` para obtener los laboratorios de un centro seleccionado, mejorando la experiencia de usuario sin recarga de página.
4. **Validación en cliente:** Ambas vistas de formulario (crear y editar) incluyen validaciones rigurosas con modal de errores, reduciendo la carga del servidor.
5. **Mensajes flash:** Se usan SweetAlert2 para mostrar `success`, `error` y `usuario_eliminado`, reemplazando las alertas Bootstrap tradicionales.
6. **Memoria de filtros:** `index.php` incluye lógica JavaScript con `sessionStorage` para recordar los filtros entre recargas de página (no afecta al controlador).
7. **Paginación manual:** La paginación se construye manualmente con enlaces que incluyen `page` y los parámetros GET de filtros.
8. **Eliminación vs deshabilitación:** Deshabilitar solo cambia `status` (0/1), mientras que eliminar borra físicamente el registro de la BD. La vista diferencia ambas acciones con colores y mensajes de advertencia.

---

## Dependencias compartidas

- **Bootstrap 5:** CSS y JS (desde el layout base).
- **SweetAlert2:** Para mensajes flash (cargado desde CDN).
- **Dompdf:** Para generación de PDF (librería PHP, no visible en la vista).
- **Logo y assets:** `public/img/logo.svg`, `public/img/pdf.svg`, `public/img/pencil-square.svg`, `public/img/trash-x.svg`.

---

*Última actualización: 2026-07-12*