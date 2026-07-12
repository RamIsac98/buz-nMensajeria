# Vistas de Desechos Biológicos - Resumen

## Resumen general

Las vistas de la carpeta `desechos/` gestionan el ciclo de vida de las solicitudes de desechos biológicos: creación, edición, visualización en PDF, historial de solicitudes (para usuarios regulares) y panel de gestión (para administradores y protección integral). Están diseñadas con **Bootstrap 5** y utilizan validaciones en cliente complejas (tipos, variantes, estados físicos, pesos, empaques, etc.) para garantizar la integridad de los datos antes del envío al servidor.

---

## Estructura de archivos

| Archivo | Propósito | Controlador asociado |
|---------|-----------|---------------------|
| `formulario.php` | Creación de una nueva solicitud de desechos. | `DesechosController::crear()` (GET) / `DesechosController::registrar()` (POST) |
| `editar.php` | Edición de una solicitud existente (edición única). | `DesechosController::editar($id)` (GET) / `DesechosController::actualizar($id)` (POST) |
| `plantilla_pdf.php` | Plantilla HTML para el PDF de la solicitud. | `DesechosController::generarPdf($id)` (GET) |
| `registroSolicitudes.php` | Historial de solicitudes (acceso para usuarios regulares). | `DesechosController::registroSolicitudes()` (GET) |
| `gestion_solicitudes.php` | Panel de gestión de solicitudes (solo administradores y protección integral). | `DesechosController::gestionSolicitudes()` (GET) / `DesechosController::actualizarEstado()` (POST/AJAX) / `DesechosController::obtenerPeso()` (GET/AJAX) / `DesechosController::actualizarPeso()` (POST/AJAX) |

---

## Detalle por archivo

### `formulario.php`
**Propósito:** Crear una nueva solicitud de desechos biológicos (tipos B, C, D, variantes, estado físico, pesos, empaques, motivo, etc.).  
**Extiende el layout:** `layouts/base`.

**Conexiones con el controlador:**
- **Carga inicial (GET):** `DesechosController::crear()` (ruta `/desechos/crear`).  
  Recibe:
  - `$usuario_data` – datos del usuario (departamento, laboratorio, username).
  - `$codigo_automatico` – código único generado por `SolicitudDesechosModel::generarCodigoUnico()`.
  - `$fecha_automatica` – fecha actual formateada.
- **Envío del formulario (POST):** `action="<?= base_url('desechos/registrar') ?>"` → `DesechosController::registrar()`.
- **Campo oculto:** `<input type="hidden" name="codigo_solicitud" value="<?= $codigo_automatico ?>">` → se envía al controlador (si no se envía, el controlador genera uno nuevo).
- **Mensajes flash:** `success` o `error` (SweetAlert2) generados por el controlador tras el registro o validaciones fallidas.

**Validaciones en cliente (con modal de errores):**
- Selección de al menos un tipo de desecho (B, C, D).
- Selección de al menos una variante para los tipos elegidos (se actualizan dinámicamente).
- Selección de al menos un estado físico (Líquido/Sólido).
- Campos de peso habilitados/deshabilitados según estado seleccionado.
- Selección de al menos un tipo de empaque (B, C, CPC, O).
- Si se selecciona "O (Otros)", el campo de descripción se habilita y es obligatorio.
- Extensión telefónica numérica.
- Motivo obligatorio.
- Modal de confirmación antes del envío.

**Módulos JavaScript:**
- `dicVariantes` – diccionario de variantes por tipo.
- `rebuildVariants()` – regenera la lista de variantes según los tipos seleccionados.
- `actualizarPesos()` – habilita/deshabilita campos de peso según estados.
- Control de "Otros" empaque.
- Modal de advertencia para empaque "B" (Bolsas) con recordatorio de etiquetado.

---

### `editar.php`
**Propósito:** Editar una solicitud existente de desechos (solo se permite una única edición).  
**Extiende el layout:** `layouts/base`.

**Conexiones con el controlador:**
- **Carga inicial (GET):** `DesechosController::editar($id)` (ruta `/desechos/editar/{id}`).  
  Recibe:
  - `$usuario_data` – datos del usuario.
  - `$solicitud` – datos actuales de la solicitud (incluye `editado`, `tipos_desecho`, `variantes_desecho`, `estado`, `peso_kg`, `peso_l`, etc.).
  - `$codigo_automatico` – código de la solicitud (solo lectura).
  - `$fecha_automatica` – fecha de registro formateada.
  - `$modo_edicion` – true.
  - `$id_solicitud` – ID para el action del formulario.
- **Envío del formulario (POST):** `action="<?= base_url('desechos/actualizar/' . $id_solicitud) ?>"` → `DesechosController::actualizar($id)`.
  - Incluye campo oculto `_method="PUT"` (opcional).
- **Botón "Cancelar":** `<a href="<?= base_url('desechos/registroSolicitudes') ?>">` → `DesechosController::registroSolicitudes()`.
- **Mensajes flash:** `success`/`error` (SweetAlert2) generados por el controlador tras la actualización o validaciones fallidas.
- **Modal de advertencia única:** Se muestra al cargar la página (contenido estático) y alerta que solo se puede editar una vez (basado en el campo `editado` del modelo).

**Precarga de datos:**
- Los campos se llenan con valores de `$solicitud`: `ext_telefono`, `motivo`, `peso_kg`, `peso_l`, y los checkboxes de tipos, estados, empaques se marcan usando `in_array()` con los valores separados por coma de la BD.
- Las variantes guardadas se inyectan en JavaScript como `variantesGuardadas` para que al reconstruir la lista se marquen las que estaban seleccionadas.

**Validaciones en cliente:** Mismas que en `formulario.php`, con la adición de que los pesos se precargan y los campos se habilitan según el estado físico guardado.

---

### `plantilla_pdf.php`
**Propósito:** Plantilla HTML para generar el PDF de una solicitud de desechos.  
**No extiende el layout base** (vista independiente, sin estilos externos).

**Conexiones con el controlador:**
- **Generación del PDF (GET):** `DesechosController::generarPdf($id)` (ruta `/desechos/generarPdf/{id}`).  
  El controlador:
  - Obtiene la solicitud y los datos del usuario.
  - Prepara variables: `codigo_solicitud`, `usuario_nombre`, `departamento`, `laboratorio`, `ext_telefono`, `tipos_desecho`, `variantes_desecho`, `esterilizado`, `motivo`, `estado`, `peso_kg`, `peso_l`, `tipo_empaque`, `empaque_otro_descripcion`, `fecha_registro`.
  - Renderiza esta vista, la convierte a PDF con Dompdf y la muestra en el navegador (`Attachment = false`).
- **Variables recibidas:** Todas las anteriores se utilizan para llenar la tabla del PDF.
- **Estilos CSS integrados** (no usa Bootstrap) para evitar dependencias externas en el PDF.

---

### `registroSolicitudes.php`
**Propósito:** Historial de solicitudes (acceso para usuarios regulares: PAI, TAI, Jefe de Laboratorio, Auxiliar).  
**Extiende el layout:** `layouts/base`.

**Conexiones con el controlador:**
- **Carga inicial y filtros (GET):** `DesechosController::registroSolicitudes()` (ruta `/desechos/registroSolicitudes`).  
  Recibe:
  - `$solicitudes` – lista combinada de solicitudes de desechos y bioseguridad.
  - `$filtros` – valores de filtros aplicados (buscar, tipo_solicitud, estado_solicitud, fecha_desde, fecha_hasta).
  - `$tiposSolicitud` – opciones para el filtro de tipo (`['Desechos Biológicos', 'Bioseguridad']`).
  - `$estadosSolicitud` – opciones para el filtro de estado (`['Pendiente', 'Entregado', 'Cancelado']`).
  - Variables de paginación: `$paginaActual`, `$totalPages`, `$startPage`, `$endPage`, `$urlParams`, `$porPagina`, `$total`.
- **Limpiar filtros:** Redirige a `/desechos/registroSolicitudes` sin parámetros → `DesechosController::registroSolicitudes()`.
- **Generación de PDF (botón PDF):**
  - Para desechos: `/desechos/generarPdf/{id}` → `DesechosController::generarPdf($id)`.
  - Para bioseguridad: `/bioseguridad/generarPdf/{id}` → `BioseguridadController::generarPdf($id)`.
  - Ambos abren en nueva pestaña (`target="_blank"`).
- **Edición de solicitudes (botón lápiz):**
  - Solo visible si `(usuario_actual == sol['usuario_id'] || es_admin) && editado == 0`.
  - Para desechos: `/desechos/editar/{id}` → `DesechosController::editar($id)`.
  - Para bioseguridad: `/bioseguridad/editar/{id}` → `BioseguridadController::editar($id)`.
  - El controlador verifica permisos y restricción de edición única (campo `editado`).
- **Mensajes flash:** `success` o `error` (SweetAlert2) generados por el controlador tras operaciones como edición, registro, etc.

**Características:**
- Acceso restringido: no permite acceso a `proteccion_integral` (redirige a gestión).
- Paginación manual (10 registros por página) con botones de navegación.
- Filtros por tipo de solicitud, estado y rango de fechas.
- Badges de estado con colores: amarillo (Pendiente), verde (Entregado), rojo (Cancelado).

---

### `gestion_solicitudes.php`
**Propósito:** Panel de gestión de solicitudes (acceso exclusivo para administradores y protección integral).  
**Extiende el layout:** `layouts/base`.

**Conexiones con el controlador:**
- **Carga inicial y filtros (GET):** `DesechosController::gestionSolicitudes()` (ruta `/desechos/gestionSolicitudes`).  
  Recibe:
  - `$solicitudes` – lista combinada con campo adicional `tabla_origen` ('desechos' o 'bioseguridad').
  - `$filtros`, `$tiposSolicitud`, `$estadosSolicitud`, variables de paginación (igual que `registroSolicitudes`).
- **Limpiar filtros:** Redirige a `/desechos/gestionSolicitudes` sin parámetros → `DesechosController::gestionSolicitudes()`.
- **Cambio de estado (POST/AJAX):** `DesechosController::actualizarEstado()` (ruta `/desechos/actualizarEstado`).  
  Llamada vía `fetch` con parámetros: `id`, `tipo` ('desechos'/'bioseguridad'), `estado` ('Pendiente'/'Entregado'/'Cancelado').  
  Retorna JSON `{ success: true/false, error: mensaje }`.  
  Incluye token CSRF para seguridad.
- **Editar peso (GET/AJAX):** `DesechosController::obtenerPeso($id)` (ruta `/desechos/obtenerPeso/{id}`).  
  Llamada vía `fetch` al abrir el modal de edición de peso.  
  Retorna JSON con `id`, `codigo`, `peso_kg`, `peso_l`, `estado_fisico`.
- **Actualizar peso (POST/AJAX):** `DesechosController::actualizarPeso($id)` (ruta `/desechos/actualizarPeso/{id}`).  
  Llamada vía `fetch` con FormData (`peso_kg`, `peso_l`).  
  Retorna JSON `{ success: true/false, error/message }`.
- **Generación de PDF (botón PDF):** Mismo comportamiento que en `registroSolicitudes`.
- **Mensajes flash:** `success` o `error` (SweetAlert2) generados por el controlador.

**Características:**
- Acceso restringido: solo `administrador` y `proteccion_integral`.
- Cambio de estado mediante select + botón "Actualizar" (AJAX).
- Edición de peso en modal (solo para solicitudes de desechos).
- Los campos de peso en el modal se habilitan/deshabilitan según el estado físico (Sólido/Líquido) de la solicitud.
- Paginación manual (10 registros por página).
- Filtros por tipo, estado y rango de fechas.
- Badges de estado con colores: amarillo (Pendiente), verde (Entregado), rojo (Cancelado).

---

## Flujo de trabajo típico

1. **Creación de solicitud:**
   - Usuario navega a `/desechos/crear`.
   - Completa el formulario (tipos, variantes, estados, pesos, empaques, motivo).
   - Presiona "ENVIAR SOLICITUD DE DESECHOS" → validación en cliente → modal de confirmación.
   - Al confirmar, envía POST a `/desechos/registrar`.
   - El controlador valida (extensión numérica, pesos, empaques, etc.), registra en BD y redirige a `/desechos/registroSolicitudes` con mensaje de éxito o error.
   - Se registra en bitácora la acción.

2. **Visualización de historial:**
   - Usuario regular navega a `/desechos/registroSolicitudes`.
   - Aplica filtros y navega entre páginas.
   - Puede ver PDFs y editar sus propias solicitudes (si no han sido editadas antes).

3. **Gestión (admin/protección integral):**
   - Usuario navega a `/desechos/gestionSolicitudes`.
   - Puede cambiar el estado de cualquier solicitud (Pendiente → Entregado/Cancelado).
   - Puede editar el peso de solicitudes de desechos (modal con validación por estado físico).
   - Ve PDFs de todas las solicitudes.

4. **Edición de solicitud (única vez):**
   - Usuario o administrador accede a "Editar" desde el listado.
   - Se carga `/desechos/editar/{id}` con los datos precargados.
   - El controlador verifica que `editado == 0` y permisos adecuados.
   - Se muestra modal de advertencia de edición única.
   - Usuario modifica y confirma.
   - El controlador actualiza y marca `editado = 1`.
   - Registra en bitácora y redirige con mensaje.

5. **Generación de PDF:**
   - Usuario presiona el botón PDF (desde historial o gestión).
   - Se abre en nueva pestaña `/desechos/generarPdf/{id}` o `/bioseguridad/generarPdf/{id}`.
   - El controlador obtiene los datos, renderiza la plantilla y genera el PDF.
   - El PDF se muestra en el navegador (Attachment = false).

---

## Observaciones importantes

1. **Validación en cliente robusta:** Ambas vistas de formulario (creación y edición) incluyen un modal de errores que lista todos los problemas encontrados, evitando que el usuario envíe datos incompletos o incorrectos.

2. **Variantes dinámicas:** La lista de variantes se actualiza automáticamente según los tipos de desecho seleccionados (B, C, D), utilizando un diccionario en JavaScript inyectado desde el controlador.

3. **Pesos condicionales:** Los campos de peso (kg y L) se habilitan/deshabilitan según los estados físicos marcados (Sólido/Líquido), y la validación exige que tengan valor si el estado está seleccionado.

4. **Empaque "Otros":** Al seleccionar "O", el campo de descripción se habilita y se vuelve obligatorio tanto en cliente como en servidor (validación en `DesechosController::validarDatosSolicitud()`).

5. **Advertencia de bolsas:** Si se selecciona el empaque "B" (Bolsas), aparece un modal recordando que deben estar identificadas con el nombre del laboratorio y fecha.

6. **Edición única:** El controlador bloquea la edición si `editado == 1`; la vista refuerza esto con un modal informativo al cargar y ocultando el botón de edición en el historial/gestión si la solicitud ya fue editada.

7. **Seguridad en edición:** El controlador verifica que el usuario sea el creador o administrador; de lo contrario, redirige con error.

8. **AJAX para cambios de estado y peso:** En `gestion_solicitudes.php`, tanto el cambio de estado como la actualización de peso se realizan mediante peticiones AJAX, mejorando la experiencia de usuario sin recargas de página.

9. **CSRF Protection:** Todas las peticiones AJAX incluyen el token CSRF, extraído del meta tag `csrf-token`, garantizando seguridad en las operaciones.

10. **Paginación manual:** Ambas vistas de listado (`registroSolicitudes` y `gestionSolicitudes`) implementan paginación manual con `array_slice`, mostrando 10 registros por página. No utilizan el pager nativo de CodeIgniter.

---

## Dependencias compartidas

- **Bootstrap 5:** CSS y JS (desde el layout base).
- **SweetAlert2:** Para mensajes flash (cargado desde CDN en la vista o en el layout).
- **Dompdf:** Para generación de PDF (librería PHP, no visible en la vista).
- **Logo y assets:** `public/img/logo.svg`, `public/img/pdf.svg`, `public/img/pensil.png`.

---

*Última actualización: 2026-07-12*