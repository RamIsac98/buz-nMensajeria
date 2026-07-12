# Vistas de Dashboard - Resumen

## Resumen general

Las vistas de la carpeta `dashboard/` muestran estadísticas y gráficos del peso de desechos biológicos aprobados (estado `Entregado`). La vista principal presenta un gráfico de barras trimestral y una tabla detallada por día/mes, con filtros interactivos por año y trimestre. Está diseñada exclusivamente para el rol `proteccion_integral`.

---

## Estructura de archivos

| Archivo | Propósito | Controlador asociado |
|---------|-----------|---------------------|
| `index.php` | Dashboard de peso trimestral con gráfico y tabla detallada. | `DashboardController::index()` (ruta `/dashboard`) |

---

## Detalle por archivo

### `index.php`
**Propósito:** Mostrar el peso total de desechos biológicos aprobados, agrupado por trimestre, con filtros por año y trimestre, gráfico de barras y tabla detallada por día/mes.  
**Extiende el layout:** `layouts/base` (contiene el encabezado, barra lateral, Bootstrap y Chart.js).

**Conexiones con el controlador:**
- **Acceso:** Solo para rol `proteccion_integral`. Si otro rol intenta acceder, el controlador redirige a `/desechos/registroSolicitudes` con mensaje de error.
- **Datos recibidos del controlador:**
  - `$anios_disponibles` (array) – años con registros de desechos entregados.
  - `$anio_seleccionado` (int) – año actualmente seleccionado (por GET o el más reciente).
  - `$trimestre_seleccionado` (int) – trimestre filtrado (0 = todos, 1-4 = específico).
  - `$labels` (JSON) – etiquetas para el gráfico (ej. `["Q1","Q2","Q3","Q4"]` o `["Q1"]` si se filtra).
  - `$values` (JSON) – valores numéricos correspondientes (ej. `[1250.5, 980.0, 0, 0]`).
  - `$total_trimestres` (float) – suma total de kg del período filtrado.
  - `$meses` (array) – datos diarios agrupados por mes para la tabla detallada.
- **Filtros (interacción con el controlador):**
  - Los selects de año y trimestre permiten al usuario cambiar los parámetros.
  - Al hacer clic en **"Actualizar"**, se ejecuta JavaScript que redirige a `DashboardController::index()` con parámetros GET:

- El controlador recibe estos parámetros, recalcula los datos y vuelve a renderizar la vista.
- **Mensajes flash:** Se muestran via SweetAlert2 y son generados por el controlador (aunque en esta vista no hay operaciones que generen mensajes directamente, pueden venir de redirecciones desde otras acciones).

**Características principales:**
- **Gráfico de barras:** Renderizado con **Chart.js** (cargado probablemente en el layout base).
- Los datos `$labels` y `$values` se inyectan directamente en JavaScript como JSON.
- El gráfico se actualiza automáticamente al recargar la página con nuevos parámetros.
- **Tabla detallada:**
- Muestra los días con `N° Registros` (cantidad de solicitudes) y `Peso (kg)`.
- Agrupación por meses con nombres en español (enero a diciembre).
- Fila de total general al final.
- Si no hay datos, muestra mensaje "No hay solicitudes aprobadas en este período".
- **Total acumulado:**
- Caja destacada en la parte superior que muestra el total de kg del período seleccionado.
- Cambia el título entre "Total Anual" o "Total Q1/Q2/etc." según el filtro.

**Dependencias:**
- Layout `layouts/base` (incluye Bootstrap 5, estilos globales y Chart.js).
- SweetAlert2 (CDN) para mensajes flash.
- Chart.js (vía CDN, desde el layout o cargado en scripts).

---

## Flujo de trabajo típico

1. **Acceso:** El usuario con rol `proteccion_integral` navega a `/dashboard`.
2. **Carga inicial:**
- El controlador obtiene los años disponibles.
- Selecciona el año más reciente (o el indicado por GET).
- Obtiene los datos del gráfico y la tabla.
- Renderiza la vista con los datos.
3. **Filtrado:**
- El usuario selecciona un año y/o trimestre.
- Presiona "Actualizar".
- JavaScript redirige a la misma URL con parámetros GET.
- El controlador procesa los parámetros, recalcula los datos y vuelve a renderizar.
4. **Visualización:**
- El gráfico de barras muestra los totales por trimestre (o un solo trimestre si está filtrado).
- La tabla detallada muestra los días con sus registros y pesos.

---

## Observaciones importantes

1. **Manejo de filtros:**
- El filtro de trimestre envía `0` para "Todos" o `1-4` para un trimestre específico.
- El controlador usa `$trimestreSeleccionado` para decidir si mostrar todos los trimestres o solo uno en el gráfico.
- `$labels` y `$values` se adaptan en el controlador: si se selecciona un trimestre, solo muestran un punto (`["Q1"]` y `[valor]`).

2. **Adaptación del gráfico:**
- Si `$trimestre_seleccionado` > 0, el controlador transforma `$labels` y `$values` para mostrar solo ese trimestre.
- Esto permite que el gráfico sea dinámico y muestre datos relevantes al filtro aplicado.

3. **Tabla detallada:**
- `$meses` es un array estructurado donde cada clave es el número de mes (01-12) y el valor es un array de días.
- Cada día contiene: `dia` (número), `fecha` (Y-m-d), `total_kg` (float), `cantidad` (int).
- La vista usa `array_column` para sumar los pesos por mes y mostrar totales parciales.

4. **Mensajes flash:**
- Aunque `index.php` incluye SweetAlert2 para `success` y `error`, esta vista no genera mensajes directamente. Los mensajes pueden venir de redirecciones desde otros controladores (ej. al guardar datos desde otras secciones).

5. **Rendimiento:**
- El controlador `DashboardController::index()` realiza múltiples consultas a la base de datos:
 - `getAniosDisponibles()` – años con datos.
 - `getDatosGrafico()` – totales por trimestre.
 - `getDatosDiarios()` – datos diarios (con o sin filtro de trimestre).
 - `agruparPorMes()` – reorganiza los datos para la tabla.

---

## Dependencias compartidas

- **Bootstrap 5:** CSS y JS (desde el layout base).
- **Chart.js:** Para el gráfico (vía CDN, probablemente incluido en el layout).
- **SweetAlert2:** Para mensajes flash (cargado desde CDN en la vista o en el layout).
- **Logo:** `public/img/logo.svg` (para la interfaz web).

---

*Última actualización: 2026-07-12*