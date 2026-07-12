# Vistas de Bitácora - Resumen

## Resumen general

Las vistas de la carpeta `Bitacora/` gestionan la visualización y exportación del historial de auditoría del sistema. Se componen de una vista principal con filtros, tabla y paginación, y una plantilla para la generación de reportes en PDF. Ambas están diseñadas para ser utilizadas por el rol `administrador` (acceso desde el menú principal).

---

## Estructura de archivos

| Archivo | Propósito | Controlador asociado |
|---------|-----------|---------------------|
| `bitacora.php` | Listado de eventos de auditoría con filtros y paginación. | `BaseController::bitacora()` (ruta `/usuarios/bitacora`) |
| `bitacora_pdf.php` | Plantilla para el PDF del reporte de bitácora. | `BaseController::generarPdfBitacora()` (ruta `/usuarios/generarPdfBitacora`) |

---

## Detalle por archivo

### `bitacora.php`
**Propósito:** Mostrar el historial de acciones registradas en el sistema con opciones de filtrado, paginación y exportación a PDF.  
**Extiende el layout:** `layouts/base` (contiene el encabezado, barra lateral y scripts comunes).

**Conexiones con el controlador:**
- **Formulario de filtros:** Envía GET a `base_url('usuarios/bitacora')` → `BaseController::bitacora()`.  
  Los parámetros GET (`buscar`, `desde`, `hasta`, `tipo`) son utilizados por el controlador para filtrar los resultados.
- **Enlace "Limpiar Filtros":** Redirige a `base_url('usuarios/bitacora')` sin parámetros → reinicia los filtros.
- **Paginación:** Los enlaces de página (`?page=N`) llaman al mismo controlador `BaseController::bitacora()` con el parámetro `page`.
- **Botón PDF:** Abre un modal que envía el formulario a `base_url('usuarios/generarPdfBitacora')` → `BaseController::generarPdfBitacora()`.  
  El modal incluye:
  - Campos ocultos con los filtros actuales (`buscar`, `desde`, `hasta`, `tipo`) para mantener consistencia.
  - Campos `pagina_inicio` y `pagina_fin` para seleccionar el rango de páginas a exportar.
- **Mensajes flash:** Se muestran via SweetAlert2 y son generados por el controlador en operaciones como guardar/editar/eliminar (aunque en esta vista no hay operaciones CRUD directas, los mensajes pueden venir de redirecciones).

**Datos recibidos del controlador:**
- `$bitacora` (array) – Lista de registros de auditoría.
- `$pager` (objeto) – Objeto de paginación de CodeIgniter con métodos `getTotal()`, `getPerPage()`, `getCurrentPage()`.

**Dependencias:**
- Layout `layouts/base` (incluye Bootstrap 5, estilos globales y scripts).
- SweetAlert2 (CDN) para mensajes flash.
- Logo y assets desde `public/img/`.

---

### `bitacora_pdf.php`
**Propósito:** Plantilla HTML para la generación del reporte PDF de la bitácora.  
**No extiende el layout base** (es una vista independiente, sin estilos externos).

**Conexiones con el controlador:**
- Es invocada por `BaseController::generarPdfBitacora()` para renderizar el HTML que luego se convierte a PDF mediante Dompdf.
- Recibe la variable `$bitacora` (array de registros) desde el controlador.

**Características:**
- Estilos CSS integrados (no usa Bootstrap para evitar dependencias externas en el PDF).
- Cabecera con título y fecha de generación.
- Tabla con los mismos campos que la vista principal: ID, Usuario, Tipo, Registro, Fecha, IP, Acción.
- Pie de página con mensaje institucional.
- El controlador define el tamaño de papel (A4, portrait) y la orientación.

**Dependencias:**
- Dompdf (librería PHP, no requiere assets externos).
- No utiliza Bootstrap ni JavaScript.

---

## Flujo de trabajo típico

1. **Acceso:** El usuario administrador navega a `/usuarios/bitacora`.
2. **Filtrado:** Aplica filtros (búsqueda, tipo, fechas) y presiona "Buscar" → Se envía GET al controlador con los parámetros.
3. **Paginación:** Navega entre páginas usando los botones de paginación → Se envía GET con `page=N` y los filtros actuales.
4. **Exportación a PDF:** 
   - Presiona el botón PDF → Se abre el modal.
   - Selecciona rango de páginas (inicio y fin) y presiona "Descargar PDF".
   - El controlador recibe los filtros y el rango, obtiene los registros y genera el PDF.
   - El PDF se descarga automáticamente (Attachment) con el nombre `Reporte_Bitacora_Paginas_X_al_Y.pdf`.

---

## Observaciones importantes

1. **Manejo de filtros:** Los filtros se mantienen en la URL mediante `$_GET`, lo que permite compartir enlaces con el estado actual de la búsqueda.
2. **Paginación manual:** La paginación se construye manualmente en la vista usando `$pager` y parámetros GET, con un máximo de 3 botones visibles alrededor de la página actual.
3. **PDF:** El modal de PDF incluye campos ocultos para preservar los filtros, asegurando que el reporte refleje la misma vista que el usuario está viendo.
4. **Mensajes flash:** Aunque `bitacora.php` incluye código para SweetAlert2, los mensajes de éxito/error no son generados directamente en esta vista, sino que pueden venir de redirecciones desde otros controladores (ej. al guardar/editar desde otras secciones).
5. **Rendimiento:** El controlador `BaseController::bitacora()` obtiene solo los registros de la página actual (8 por defecto), mientras que `generarPdfBitacora()` puede obtener múltiples páginas según el rango seleccionado.

---

## Dependencias compartidas

- **Bootstrap 5:** CSS y JS (desde el layout base).
- **SweetAlert2:** Para mensajes flash (cargado desde CDN en el layout o en la vista).
- **Dompdf:** Para generación de PDF (librería PHP, no visible en la vista).
- **Logo:** `public/img/logo.svg` (para la interfaz web, no en el PDF).

---

*Última actualización: 2026-07-12*