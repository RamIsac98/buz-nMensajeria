# Vistas de Bioseguridad - Resumen

## Resumen general

Las vistas de la carpeta `bioseguridad/` gestionan el ciclo de vida de las solicitudes de materiales de bioseguridad: creación, edición y generación de PDF. Están diseñadas con **Bootstrap 5** y utilizan validaciones en cliente para garantizar la integridad de los datos antes del envío al servidor.

---

## Estructura de archivos

| Archivo | Propósito | Controlador asociado |
|---------|-----------|---------------------|
| `formulario.php` | Formulario de creación de solicitud de bioseguridad. | `BioseguridadController::crear()` (GET) / `BioseguridadController::registrar()` (POST) |
| `editar.php` | Formulario de edición de solicitud existente (edición única). | `BioseguridadController::editar($id)` (GET) / `BioseguridadController::actualizar($id)` (POST) |
| `plantilla_pdf.php` | Plantilla HTML para el PDF de la solicitud. | `BioseguridadController::generarPdf($id)` (GET) |

---

## Detalle por archivo

### `formulario.php`
**Propósito:** Crear una nueva solicitud de materiales de bioseguridad (contenedores pulso cortante y bolsas rojas).  
**Extiende el layout:** `layouts/base`.

**Conexiones con el controlador:**
- **Carga inicial (GET):** `BioseguridadController::crear()` (ruta `/bioseguridad/crear`).  
  Recibe:
  - `$usuario_data` – datos del usuario (departamento, laboratorio, username).
  - `$codigo_automatico` – código único generado por `SolicitudBioseguridadModel::generarCodigoUnico()`.
  - `$fecha_automatica` – fecha actual formateada.
- **Envío del formulario (POST):** `action="<?= base_url('bioseguridad/registrar') ?>"` → `BioseguridadController::registrar()`.
- **Mensajes flash:** `success` o `error` (SweetAlert2) generados por el controlador tras el registro o validaciones fallidas.

**Validaciones en cliente:**
- Límite de contenedores (máximo 3 unidades).
- Límite de bolsas rojas (máximo 10 unidades por cada tamaño).
- Al menos un material seleccionado (contenedor o bolsa roja).
- Campo "otra persona" se habilita solo si se selecciona esa opción.
- Modal de confirmación antes del envío.

---

### `editar.php`
**Propósito:** Editar una solicitud existente de bioseguridad (solo se permite una única edición).  
**Extiende el layout:** `layouts/base`.

**Conexiones con el controlador:**
- **Carga inicial (GET):** `BioseguridadController::editar($id)` (ruta `/bioseguridad/editar/{id}`).  
  Recibe:
  - `$usuario_data` – datos del usuario (departamento, laboratorio, username).
  - `$solicitud` – datos actuales de la solicitud.
  - `$codigo_automatico` – código de la solicitud (solo lectura).
  - `$fecha_automatica` – fecha de registro formateada.
  - `$modo_edicion` – true (indica modo edición).
  - `$id_solicitud` – ID de la solicitud para el action del formulario.
- **Envío del formulario (POST):** `action="<?= base_url('bioseguridad/actualizar/' . $id_solicitud) ?>"` → `BioseguridadController::actualizar($id)`.
- **Botón "Cancelar":** redirige a `desechos/registroSolicitudes` → `DesechosController::registroSolicitudes()`.
- **Mensajes flash:** `success` o `error` (SweetAlert2) generados por el controlador tras la actualización o validaciones fallidas.
- **Modal de advertencia única:** Se muestra al cargar la página (contenido estático) y alerta al usuario que la solicitud solo puede editarse una vez (basado en el campo `editado` del modelo).

**Validaciones en cliente:** Mismas que en `formulario.php` (contenedores ≤3, bolsas ≤10, material requerido, campo condicional).

---

### `plantilla_pdf.php`
**Propósito:** Plantilla HTML para generar el PDF de una solicitud de bioseguridad.  
**No extiende el layout base** (vista independiente, sin estilos externos).

**Conexiones con el controlador:**
- **Generación del PDF (GET):** `BioseguridadController::generarPdf($id)` (ruta `/bioseguridad/generarPdf/{id}`).  
  El controlador:
  - Obtiene la solicitud y los datos del usuario.
  - Prepara todas las variables (ver lista abajo).
  - Renderiza esta vista, la convierte a PDF con Dompdf y la muestra en línea (`Attachment = false`).
- **Variables recibidas del controlador:**
  - `$codigo_solicitud`, `$usuario_nombre`, `$departamento`, `$laboratorio`, `$ext_telefono`,
  - `$contenedores_pulso_cantidad`, `$bolsas_rojas_pequena`, `$bolsas_rojas_mediana`, `$bolsas_rojas_grande`,
  - `$quien_retira`, `$nombre_otra_persona`, `$fecha_registro`.

**Características:**
- Estilos CSS integrados (no usa Bootstrap para evitar dependencias externas en el PDF).
- Tabla simple con todos los datos de la solicitud.
- El controlador define el tamaño de papel (A4, portrait).

---

## Flujo de trabajo típico

1. **Creación de solicitud:**
   - Usuario navega a `/bioseguridad/crear`.
   - Completa el formulario (materiales, quién retira).
   - Presiona "Enviar Solicitud" → validación en cliente → modal de confirmación.
   - Al confirmar, envía POST a `/bioseguridad/registrar`.
   - El controlador valida (contenedores ≤3, bolsas ≤10), registra en BD y redirige a `/desechos/registroSolicitudes` con mensaje de éxito o error.
   - Se registra en bitácora la acción.

2. **Edición de solicitud:**
   - Usuario accede a la opción "Editar" desde el listado de solicitudes (en `DesechosController::registroSolicitudes()`).
   - Se carga `/bioseguridad/editar/{id}` con los datos precargados.
   - El controlador verifica:
     - que la solicitud exista,
     - que no haya sido editada anteriormente (`editado = 0`),
     - que el usuario tenga permisos (creador o administrador).
   - Se muestra un modal de advertencia sobre la edición única.
   - Usuario modifica los datos y confirma.
   - El controlador actualiza y marca `editado = 1` para bloquear futuras ediciones.
   - Registra en bitácora y redirige con mensaje.

3. **Generación de PDF:**
   - Usuario presiona "Ver PDF" desde el listado de solicitudes.
   - Se llama a `/bioseguridad/generarPdf/{id}`.
   - El controlador obtiene los datos, renderiza la plantilla y genera el PDF.
   - El PDF se abre en el navegador (Attachment = false).

---

## Observaciones importantes

1. **Validación de edición única:** El controlador `BioseguridadController` verifica el campo `editado` en la solicitud (0 = editable, 1 = bloqueado). La vista muestra un modal de advertencia para reforzar esta restricción.

2. **Seguridad en edición:** El controlador verifica permisos: el usuario debe ser el creador de la solicitud o tener rol `administrador`. Si no cumple, redirige con error.

3. **Manejo de mensajes flash:** Todas las vistas usan SweetAlert2 para mensajes de éxito/error, mejorando la experiencia de usuario respecto a las alertas Bootstrap tradicionales.

4. **PDF sin layout:** La `plantilla_pdf.php` no extiende el layout base, ya que el PDF debe ser autónomo y no incluir encabezados, barras laterales ni scripts externos.

5. **Campo "quien_retira":** Maneja dos opciones: "Mi persona" (no requiere nombre adicional) y "Otra persona" (requiere nombre completo). La vista muestra/oculta el campo dinámicamente con JavaScript.

---

## Dependencias compartidas

- **Bootstrap 5:** CSS y JS (desde el layout base, excepto en PDF).
- **SweetAlert2:** Para mensajes flash (cargado desde CDN en la vista o en el layout).
- **Dompdf:** Para generación de PDF (librería PHP, no visible en la vista).
- **Logo:** `public/img/logo.svg` (para la interfaz web, no en el PDF).

---

*Última actualización: 2026-07-12*