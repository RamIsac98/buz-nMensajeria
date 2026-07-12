# Modelos del Sistema - Resumen

## Resumen general

Los modelos gestionan la capa de datos utilizando **SQL directo** (no Query Builder de CodeIgniter). Cada modelo se enfoca en una entidad principal y provee métodos CRUD, filtrado, paginación y consultas especializadas.

---

## Modelos

### `BitacoraModel`
**Tabla:** `bitacora`  
**Propósito:** Registro de auditoría de acciones de usuarios.  
**Funciones importantes:**
- `insertRegistro()` – Inserta evento con IP, tipo, acción y descripción.
- `countBitacora()` / `getBitacoraFiltrada()` – Filtrado por búsqueda (username, IP, registro, acción), tipo, rango de fechas; con paginación.

---

### `DashboardModel`
**Tabla:** `solicitudes_desechos` (solo registros `Entregado`)  
**Propósito:** Estadísticas para gráficos de desechos entregados.  
**Funciones importantes:**
- `getAniosDisponibles()` – Años con datos.
- `getDatosGrafico()` – Totales por trimestre (kg).
- `getDatosDiarios()` – Cantidad y peso por día (con filtro opcional de trimestre).
- `agruparPorMes()` – Reorganiza datos diarios en estructura por mes.

---

### `DepartamentoModel`
**Tabla:** `departamentos`  
**Propósito:** CRUD básico de departamentos.  
**Funciones importantes:**
- `getDepartamentos()` – Todos ordenados.
- `getDepartamentosPaginados()` / `countDepartamentos()` – Paginación.
- `insertDepartamento()`, `updateDepartamento()`, `deleteItem()` – Operaciones CRUD.
- `findDepartamento()` – Búsqueda por ID.

---

### `LaboratorioModel`
**Tabla:** `laboratorios` (FK a `departamentos`)  
**Propósito:** CRUD de laboratorios con relación a departamentos.  
**Funciones importantes:**
- `insertLaboratorio()`, `updateLaboratorio()`, `deleteItem()` – CRUD.
- `getLaboratoriosPaginados()` – Con JOIN a departamentos, filtro opcional por departamento.
- `getLaboratoriosFiltrados()` – Similar, pero sin paginación y usando 'todos' como sentinel.
- `countLaboratorios()` – Conteo con filtro.
- `findLaboratorio()` – Búsqueda por ID.

---

### `SolicitudBioseguridadModel`
**Tabla:** `solicitudes_bioseguridad`  
**Propósito:** Gestión de solicitudes de bioseguridad (insumos).  
**Funciones importantes:**
- `generarCodigoUnico()` – Crea código con formato `BIO-YYYY-XXXX` (⚠️ consulta la tabla `solicitudes_desechos` – posible error de diseño).
- `insertarSolicitud()` – Inserta nueva solicitud con valores por defecto.
- `armarCondicionesFiltro()` – Filtros por búsqueda (departamento), estado, fechas (con transformación de hora).
- `countSolicitudesFiltradas()` / `getSolicitudesFiltradas()` – Conteo y listado paginado con JOIN a usuarios, laboratorios, departamentos.
- `actualizarEstado()` – Cambia estado de la solicitud.

> ⚠️ **Advertencia:** `generarCodigoUnico()` usa `solicitudes_desechos` en lugar de su propia tabla; probablemente es un error. sigue en mantenimiento

---

### `SolicitudDesechosModel`
**Tabla:** `solicitudes_desechos`  
**Propósito:** Gestión de solicitudes de desechos (materiales).  
**Funciones importantes:**
- `generarCodigoUnico()` – Código `SOL-YYYY-XXXX` (bien implementado, sobre su propia tabla).
- `insertarSolicitud()` – Inserta con valores por defecto (pesos 0, estado 'Pendiente').
- `armarCondicionesFiltro()` – Filtros por departamento, tipo de desecho, estado, fechas (solo DATE).
- `countSolicitudesFiltradas()` / `getSolicitudesFiltradas()` – Conteo y paginación con JOIN a usuarios, laboratorios, departamentos.
- `actualizarEstado()` – Cambia estado.


---

### `UsuarioModel`
**Tabla:** `usuarios`  
**Propósito:** Gestión completa de usuarios (autenticación, perfiles, reportes).  
**Funciones importantes:**
- `formatearUsername()` – Inserta espacio tras el primer carácter (ej. `jperez` → `j perez`).
- `findById()`, `findByUsername()`, `findByCedula()`, `findByTipoCedula()` – Múltiples búsquedas.
- `existeCedula()` / `existeCedulaExcluyendoId()` – Validación de unicidad.
- `insertUsuario()`, `updateUsuario()`, `deleteUsuario()` – CRUD completo (update dinámico).
- `getRolesDisponibles()` – Lista de roles únicos.
- `getNombreCompleto()` – Concatena nombre + apellido.
- `armarCondicionesFiltro()` – Filtros por búsqueda (username/cedula), rol y estado (status).
- `countUsuarios()` / `getUsuariosFiltrados()` – Conteo y paginación con formato de username.
- `getReporteGeneral()` – Reporte jerárquico: departamento → laboratorio → usuario, con filtro opcional por departamento.

---

## Observaciones generales

- Todos los modelos usan **SQL directo** mediante `$this->db->query()`, lo que da control total pero requiere mantener las consultas manualmente.
- La mayoría implementa métodos de filtrado y paginación con un patrón similar (`armarCondicionesFiltro` + `count*` + `get*Filtrados`).


---

*Última actualización: 2026-07-11*