# Vistas de Layouts - Resumen

## Resumen general

La carpeta `layouts/` contiene las vistas que definen la estructura común de todas las páginas del sistema después del inicio de sesión. Proporcionan el **layout base** (`base.php`) que extienden todas las vistas hijas, y la **barra de navegación** (`_navbar.php`) que se incluye en el layout y genera dinámicamente el menú según el rol del usuario. Estas vistas no se invocan directamente desde los controladores, sino que son el contenedor de las vistas que sí son servidas por los controladores.

---

## Estructura de archivos

| Archivo | Propósito | Controlador asociado |
|---------|-----------|---------------------|
| `base.php` | Layout principal del sistema (HTML, head, body, inclusión de navbar y scripts). | No se invoca directamente; es extendido por todas las vistas hijas. |
| `_navbar.php` | Barra de navegación con menú dinámico según rol, cambio de contraseña y cierre de sesión. | Contiene enlaces a múltiples controladores (ver detalles abajo). |

---

## Detalle por archivo

### `base.php`
**Propósito:** Layout base del sistema. Define la estructura HTML común (doctype, head, body, navbar, footer implícito) y las secciones que las vistas hijas pueden llenar (`title`, `styles`, `content`, `scripts`).  
**No se invoca directamente desde un controlador**; es el contenedor de todas las vistas renderizadas por los controladores (cualquier vista que use `<?= $this->extend('layouts/base') ?>` hereda de este layout).

**Conexiones con el controlador:**
- No llama directamente a ningún controlador, pero es el punto de entrada para la renderización de todas las vistas del sistema.
- Incluye la vista parcial `_navbar.php`, que contiene los enlaces a los controladores.
- Los assets (Bootstrap, logo) se cargan desde la carpeta `public/`.
- Las vistas hijas (que extienden este layout) reciben datos de los controladores correspondientes (ej. `DesechosController`, `BioseguridadController`, `GestionController`, `Usuarios`, etc.).

**Características:**
- Define variables CSS globales (`--azul-claro`, `--azul-oscuro`, `--amarillo`).
- Incluye Bootstrap 5 (CSS y JS) y el logo del sistema.
- Secciones dinámicas para estilos (`styles`) y scripts (`scripts`) que las vistas hijas pueden sobrescribir.
- Cuerpo principal: `<?= view('layouts/_navbar') ?>` y `<?= $this->renderSection('content') ?>`.

---

### `_navbar.php`
**Propósito:** Barra de navegación principal que se muestra en todas las páginas después del inicio de sesión. Genera el menú dinámicamente según el rol del usuario (obtenido de la sesión) y proporciona acceso a las funcionalidades principales del sistema. También incluye el modal para cambiar contraseña y el enlace de cierre de sesión.

**Conexiones con el controlador (enlaces y acciones):**
- **Menú "Solicitud de Recolección de Desechos Biológicos":** Enlace a `desechos/formulario` → `DesechosController::crear()`.
- **Menú "Solicitud de Materiales de Bioseguridad":** Enlace a `solicitud_bioseguridad` (ruta definida en Routes) → `BioseguridadController::crear()`.
- **Menú "Registro":** Enlace a `desechos/registroSolicitudes` → `DesechosController::registroSolicitudes()`.
- **Menú "Gestión Solicitudes":** Enlace a `desechos/gestionSolicitudes` → `DesechosController::gestionSolicitudes()` (solo para administradores y protección integral).
- **Menú "Peso Trimestral DB":** Enlace a `dashboard` → `DashboardController::index()` (solo para protección integral).
- **Menú "Gestión Usuarios":** Enlace a `usuarios` → `Usuarios::index()` (solo para administradores y protección integral).
- **Menú "Gestión Centros y Laboratorios":** Enlace a `gestion-departamento` → `GestionController::index()` (solo para administradores y protección integral).
- **Menú "Bitácora":** Enlace a `usuarios/bitacora` → `BaseController::bitacora()` (solo para administradores).
- **Submenú "Configuración":** Dropdown que agrupa los enlaces de Gestión Usuarios, Gestión Centros y Laboratorios, y Bitácora (solo para administradores).
- **Cerrar sesión:** Enlace a `login/salir` → `Login::salir()`.
- **Cambiar contraseña:** Formulario en modal que envía POST a `usuarios/cambiar_password_post` → `Usuarios::cambiar_password_post()`.
  - Incluye validación en cliente (sin espacios, coincidencia de contraseñas) con modal de error.
- **Mensaje de bienvenida:** Se muestra con SweetAlert2 al cargar la página si existe la flashdata `mostrar_bienvenida`, establecida por `Login::autenticar()` al iniciar sesión.

**Características:**
- Menú dinámico según el rol del usuario (obtenido de la sesión):
  - **Administrador:** Solicitudes, Gestión Solicitudes, Configuración (submenú con Gestión Usuarios, Gestión Centros/Laboratorios, Bitácora).
  - **Protección Integral:** Peso Trimestral DB, Gestión Solicitudes, Gestión Usuarios, Gestión Centros/Laboratorios.
  - **Otros roles (PAI, TAI, Jefe_Laboratorio, Auxiliar):** Solicitud de Recolección de Desechos Biológicos, Solicitud de Materiales de Bioseguridad, Registro.
- Estilos personalizados para la barra (gradiente, efectos hover, menú desplegable con animación).
- Muestra el nombre de usuario formateado (primera letra + espacio + resto) en el dropdown de usuario.
- Modal para cambio de contraseña con validación en cliente (sin espacios, coincidencia).
- SweetAlert2 para mensaje de bienvenida (flashdata).

---

## Flujo de trabajo típico

1. **Acceso al sistema:** El usuario inicia sesión a través de `Login::autenticar()`, que establece la sesión y la flashdata `mostrar_bienvenida`.
2. **Carga del layout:** Cualquier controlador (ej. `DesechosController::crear()`) renderiza una vista que extiende `base.php`. El layout incluye automáticamente `_navbar.php`.
3. **Generación del menú:** `_navbar.php` lee el rol de la sesión y construye el menú correspondiente.
4. **Navegación:** El usuario hace clic en un enlace del menú, que invoca al controlador correspondiente (ej. `DesechosController::registroSolicitudes()`).
5. **Cambio de contraseña:** El usuario abre el modal desde el dropdown de usuario, completa el formulario y envía POST a `Usuarios::cambiar_password_post()`.
6. **Cierre de sesión:** El usuario hace clic en "Cerrar sesión" → `Login::salir()` → destruye la sesión y redirige a login.

---

## Observaciones importantes

1. **Menú dinámico:** La lógica del menú está completamente en la vista `_navbar.php` usando arrays PHP, lo que la hace fácil de modificar. No depende de un modelo ni controlador para generar los ítems.
2. **Mantenimiento de sesión:** El nombre de usuario y el rol se obtienen directamente de la sesión (`session()->get('rol')` y `session()->get('username')`).
3. **Validación en cliente de cambio de contraseña:** Se implementa un modal de error (en lugar de alert) para mostrar mensajes de validación (espacios, coincidencia).
4. **Mensaje de bienvenida:** Se muestra solo cuando la flashdata `mostrar_bienvenida` existe (establecida en `Login::autenticar()`), y se limpia automáticamente después de la primera visualización.
5. **Rutas de los menús:** Algunas rutas (como `solicitud_bioseguridad`) deben estar definidas en el archivo `app/Config/Routes.php` para que funcionen correctamente.
6. **Acceso restringido:** Algunos ítems del menú solo se muestran para ciertos roles, pero la seguridad final recae en los controladores (que verifican sesión y permisos).

---

## Dependencias compartidas

- **Bootstrap 5:** CSS y JS (desde `public/bootstrap5/`).
- **SweetAlert2:** Para mensaje de bienvenida (cargado desde CDN).
- **Logo y assets:** `public/img/logo.svg`, `public/img/user.svg`.
- **CSRF Protection:** Incluido en el formulario de cambio de contraseña mediante `<?= csrf_field() ?>`.

---

*Última actualización: 2026-07-12*