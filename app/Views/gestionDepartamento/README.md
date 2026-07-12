# Vistas de Gestión de Departamentos y Laboratorios - Resumen

AVISO: EN ETAPAS FINALES FUE CAMBIADO EL TERMINO DE DEPARTAMENTO POR CENTRO, DEPARTAMENTO = CENTRO.

## Resumen general

Las vistas de la carpeta `gestionDepartamento/` gestionan la administración de la estructura organizacional del sistema: centros (departamentos) y laboratorios. Proporcionan un panel completo con listados paginados, operaciones CRUD (crear, editar, eliminar), filtros por departamento y generación de reportes en PDF (estructura general y listado de laboratorios). Están diseñadas con **Bootstrap 5** y utilizan JavaScript para la interacción con modales y filtros.

---

## Estructura de archivos

| Archivo | Propósito | Controlador asociado |
|---------|-----------|---------------------|
| `gestion_departamento.php` | Panel principal de gestión de centros y laboratorios (listados, filtros, CRUD). | `GestionController::index()` (GET), `guardarDepartamento()`, `editarDepartamento()`, `eliminarDepartamento()`, `guardarLaboratorio()`, `editarLaboratorio()`, `eliminarLaboratorio()` (POST/GET) |
| `pdf_general_completo.php` | Plantilla PDF para reporte general de estructura (centros, laboratorios, usuarios). | `GestionController::generarPdfGeneral()` (GET) |
| `pdf_laboratorios.php` | Plantilla PDF para reporte de laboratorios (listado con filtro opcional). | `GestionController::generarPdfLaboratorios()` (GET) |

---

## Detalle por archivo

### `gestion_departamento.php`
**Propósito:** Panel principal de administración de centros y laboratorios.  
**Extiende el layout:** `layouts/base`.

**Conexiones con el controlador:**
- **Carga inicial y filtros (GET):** `GestionController::index()` (ruta `/gestion-departamento`).  
  Recibe:
  - `$departamentos` – listado paginado de departamentos.
  - `$todos_departamentos` – todos los departamentos (para selects y filtros).
  - `$laboratorios` – listado paginado de laboratorios (filtrado por departamento si aplica).
  - `$pager_dept` – datos de paginación para departamentos (actual, total).
  - `$pager_lab` – datos de paginación para laboratorios (actual, total).
  - `$filtro_depto` – ID del departamento filtrado (o 'todos').
- **Crear departamento (POST):** `GestionController::guardarDepartamento()` (ruta `/gestion-departamento/guardar-departamento`).  
  - Action en modal `#modalNuevoDepartamento`.
  - Incluye parámetros de paginación en URL para mantener el estado.
- **Editar departamento (POST):** `GestionController::editarDepartamento()` (ruta `/gestion-departamento/editar-departamento`).  
  - Action en modal `#modalEditarDepto`, con campos ocultos `id` y `nombre`.
- **Eliminar departamento (GET):** `GestionController::eliminarDepartamento($id)` (ruta `/gestion-departamento/eliminar-departamento/{id}`).  
  - Formulario en modal `#modalEliminar`; la URL se construye dinámicamente en JavaScript.
- **Crear laboratorio (POST):** `GestionController::guardarLaboratorio()` (ruta `/gestion-departamento/guardar-laboratorio`).  
  - Action en modal `#modalNuevoLaboratorio`, con campos `departamento_id` y `nombre_laboratorio`.
- **Editar laboratorio (POST):** `GestionController::editarLaboratorio()` (ruta `/gestion-departamento/editar-laboratorio`).  
  - Action en modal `#modalEditarLab`; precarga datos existentes y lista de departamentos.
- **Eliminar laboratorio (GET):** `GestionController::eliminarLaboratorio($id)` (ruta `/gestion-departamento/eliminar-laboratorio/{id}`).  
  - Formulario en modal `#modalEliminar` (similar a departamento).
- **Filtro por departamento (GET):** Select `#filtroDepartamento` → recarga la página con `filtro_depto` en URL (reinicia `page_lab` a 1).
- **Reporte General PDF (GET):** Botón "Reporte General" → `window.open('<?= base_url('gestion-departamento/generar-pdf-general') ?>?depto_id=...', '_blank')` → `GestionController::generarPdfGeneral()`.
- **Reporte de Laboratorios PDF (GET):** Botón PDF específico → `window.location.href = '<?= base_url('gestion-departamento/generar-pdf') ?>?depto_id=...'` → `GestionController::generarPdfLaboratorios()`.
- **Mensajes flash:** `success` y `error` (SweetAlert2) generados por el controlador tras operaciones CRUD o validaciones fallidas.

**Características principales:**
- Dos columnas: izquierda para departamentos, derecha para laboratorios (paginación independiente).
- Filtro de laboratorios por departamento (con redirección y reinicio de página).
- Botones de acción en cada fila: editar (lápiz) y eliminar (basura) con modales de confirmación.
- Paginación manual con botones "Anterior" y "Siguiente" (no usa el pager nativo).
- Modales para creación y edición con validación de unicidad (en el controlador).

---

### `pdf_general_completo.php`
**Propósito:** Plantilla HTML para el PDF del reporte general de estructura organizacional.  
**No extiende el layout base** (vista independiente, sin estilos externos).

**Conexiones con el controlador:**
- **Generación del PDF (GET):** `GestionController::generarPdfGeneral()` (ruta `/gestion-departamento/generar-pdf-general?depto_id={id}`).  
  El controlador:
  - Obtiene `$depto_id` de la URL (`todos` o ID de departamento).
  - Llama a `UsuarioModel::getReporteGeneral($depto_id)` para obtener los datos jerárquicos.
  - Prepara `$reporte` y `$depto_seleccionado`.
  - Renderiza la vista, convierte a PDF con Dompdf (orientación **landscape**) y descarga el PDF (`Attachment = true`).
- **Variables recibidas:**
  - `$reporte` (array) – cada fila contiene: `nombre_departamento`, `nombre_laboratorio`, `nombre_usuario`, `apellido_usuario`, `username_usuario`, `cedula_usuario`, `rol_usuario`, `estado_usuario`.
  - `$depto_seleccionado` (string|int) – valor del filtro para mostrar en la cabecera.

**Características:**
- Orientación **landscape** (horizontal) para mostrar 6 columnas.
- Agrupación visual: cada cambio de centro o laboratorio se marca con una línea divisoria (`linea-agrupador`).
- Las celdas de centro y laboratorio solo se muestran en la primera fila de cada grupo (usando variables `$last_centro` y `$last_lab`).
- Colores para estado: verde (Activo) y rojo (Inactivo).
- Pie de página fijo con "Sistema de Gestión Interno - Página 1".

---

### `pdf_laboratorios.php`
**Propósito:** Plantilla HTML para el PDF del listado de laboratorios.  
**No extiende el layout base** (vista independiente, sin estilos externos).

**Conexiones con el controlador:**
- **Generación del PDF (GET):** `GestionController::generarPdfLaboratorios()` (ruta `/gestion-departamento/generar-pdf?depto_id={id}`).  
  El controlador:
  - Obtiene `$depto_id` de la URL.
  - Llama a `LaboratorioModel::getLaboratoriosFiltrados($depto_id)` para obtener la lista.
  - Prepara `$laboratorios` y `$depto_seleccionado`.
  - Renderiza la vista, convierte a PDF con Dompdf (orientación **portrait**) y descarga el PDF (`Attachment = true`).
- **Variables recibidas:**
  - `$laboratorios` (array) – cada registro contiene: `id`, `nombre`, `nombre_departamento`.
  - `$depto_seleccionado` (string|int) – valor del filtro para mostrar en la cabecera (si es específico).

**Características:**
- Orientación **portrait** (vertical) con 3 columnas: ID, Nombre del Laboratorio, Centro.
- Agrupación por centro: el nombre del centro solo se muestra en la primera fila de cada grupo (usando `$last_centro`).
- Línea divisoria (`linea-agrupador`) al cambiar de centro.
- Filas alternas para mejor legibilidad (`tr:nth-child(even)`).
- Cabecera muestra la fecha de generación y el filtro aplicado (si existe).
- Pie de página con "Reporte Automatizado de Inventario de Laboratorios."

---

## Flujo de trabajo típico

1. **Acceso:** Usuario administrador o protección integral navega a `/gestion-departamento` (acceso restringido por el controlador).
2. **Visualización:** Se muestran listados paginados de departamentos y laboratorios. El filtro de departamento permite ver laboratorios de un centro específico.
3. **Creación:**
   - Presiona "+ Añadir Centro" → modal → ingresa nombre → envía POST a `guardar-departamento`.
   - Presiona "+ Añadir Laboratorio" → modal → selecciona centro, ingresa nombre → envía POST a `guardar-laboratorio`.
4. **Edición:** Botón lápiz en cada fila → modal con datos precargados → envía POST a `editar-departamento` o `editar-laboratorio`.
5. **Eliminación:** Botón basura → modal de confirmación → envía GET a `eliminar-departamento/{id}` o `eliminar-laboratorio/{id}`.
6. **Reportes PDF:**
   - "Reporte General" → genera PDF con toda la estructura (centros, laboratorios, usuarios) en landscape.
   - Botón PDF específico → genera PDF del listado de laboratorios según el filtro actual (portrait).

---

## Observaciones importantes

1. **Mantenimiento de paginación:** Todos los actions de los modales incluyen parámetros `page_dept` y `page_lab` en la URL para que, después de una operación, la vista vuelva a la misma página.
2. **Filtro por departamento:** Cambiar el filtro redirige con `filtro_depto` y reinicia `page_lab` a 1 (para mostrar la primera página del filtro).
3. **Validación de unicidad:** El controlador (GestionController) utiliza `is_unique` en las reglas de validación para evitar duplicados de nombres (tanto en departamentos como en laboratorios).
4. **Seguridad:** El controlador `GestionController` verifica sesión y rol (administrador o protección integral) en todos los métodos.
5. **Paginación manual:** No se utiliza el pager nativo de CodeIgniter; la vista construye los enlaces manualmente con `?page_dept=N&page_lab=M&filtro_depto=...`.
6. **Modales:** Se utilizan modales de Bootstrap para todas las operaciones CRUD, evitando recargas completas de página (aunque el formulario envía POST, la vista recarga con mensajes flash).
7. **Manejo de errores:** Los mensajes de error (como unicidad violada) se muestran via SweetAlert2.

---

## Dependencias compartidas

- **Bootstrap 5:** CSS y JS (desde el layout base).
- **SweetAlert2:** Para mensajes flash (cargado desde CDN en la vista).
- **Dompdf:** Para generación de PDF (librería PHP, no visible en la vista).
- **Logo y assets:** `public/img/logo.svg`, `public/img/pdf.svg`.

---

*Última actualización: 2026-07-12*