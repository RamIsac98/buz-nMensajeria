# Vistas de Login - Resumen

## Resumen general

Las vistas de la carpeta `login/` gestionan el flujo de autenticación, recuperación de contraseña y configuración de seguridad. Cada vista está diseñada con **Bootstrap 5** y sigue la identidad corporativa (azul/amarillo). Los mensajes flash se manejan mediante **SweetAlert2** (alertas interactivas) o alertas nativas de Bootstrap.

---

## Estructura de archivos

| Archivo | Propósito | Controlador asociado |
|---------|-----------|---------------------|
| `login.php` | Formulario de inicio de sesión. | `Login::autenticar()` |
| `olvide_contrasena.php` | Ingreso de cédula para recuperación. | `Login::validarCedula()` (o ruta `validar_usuario`) |
| `responder_pregunta.php` | Validación de seguridad y nuevo password. | `Login::nuevaClave()` (o ruta `guardar_nueva_clave`) |
| `configurar_pregunta.php` | Configuración de pregunta de seguridad (primer ingreso). | `Usuarios::guardar_pregunta()` |

---

## Detalle por archivo

### `login.php`
**Propósito:** Punto de entrada al sistema.  
**Muestra:** Formulario con campos `username` y `password`.  
**Conexión con controlador:**
- **Formulario:** `POST /login/autenticar` → `Login::autenticar()`
- **Enlace:** `¿Olvidaste tu contraseña?` → `Login::olvideContrasena()`

**Mensajes flash:**
- `success` → SweetAlert2 (icono éxito, timer 4s).
- `error` → SweetAlert2 (icono error, timer 5s, botón confirmar).

**Dependencias:** Bootstrap 5, SweetAlert2, logo.svg.

---

### `olvide_contrasena.php`
**Propósito:** Solicitar datos de identificación (tipo y número de cédula) para iniciar la recuperación.  
**Muestra:** Selector de tipo de cédula (V/E) y campo numérico (6-10 dígitos).  
**Conexión con controlador:**
- **Formulario:** `POST /login/validar_usuario` → **NOTA:** La ruta definida en la vista es `validar_usuario`, pero el método en el controlador es `validarCedula()`. Debe existir una ruta que mapee `validar_usuario` → `Login::validarCedula`.
- **Botón:** `Volver al Login` → `Login::index()` (vía `base_url('login')`).

**Mensajes flash:**
- `error` → SweetAlert2 (icono error).

**Dependencias:** Bootstrap 5, SweetAlert2, logo.svg.

---

### `responder_pregunta.php`
**Propósito:** Mostrar la pregunta de seguridad del usuario y permitir ingresar respuesta + nueva contraseña.  
**Muestra:** Pregunta de seguridad (desde controlador), campo respuesta, nueva contraseña y confirmación.  
**Conexión con controlador:**
- **Formulario:** `POST /login/guardar_nueva_clave` → **NOTA:** La ruta definida en la vista es `guardar_nueva_clave`, pero el método en el controlador es `nuevaClave()`. Debe existir una ruta que mapee `guardar_nueva_clave` → `Login::nuevaClave`.
- **Datos recibidos del controlador:**
  - `$usuario_id` (hidden)
  - `$pregunta` (texto visible)
  - `$error` (opcional, si hay fallo en validación)
- **Botón:** `Cancelar operación` → `Login::index()`.

**Validaciones del controlador:** Compara respuesta (hasheada), valida coincidencia de contraseñas y actualiza en BD.

**Dependencias:** Bootstrap 5, logo.svg.

---

### `configurar_pregunta.php`
**Propósito:** Configurar pregunta y respuesta de seguridad (solo para usuarios que inician sesión por primera vez sin pregunta registrada).  
**Muestra:** Selector de preguntas predefinidas, campo de respuesta.  
**Conexión con controlador:**
- **Formulario:** `POST /usuarios/guardar_pregunta` → `Usuarios::guardar_pregunta()`.
- **Redirección post-guardado:**
  - `proteccion_integral` → `/dashboard`
  - `administrador` → `/usuarios/bitacora`
  - Otros roles → `/desechos/registroSolicitudes`

**Mensajes flash:**
- `error` → Alerta Bootstrap (rojo).
- `info` → Alerta Bootstrap (azul información).

**Dependencias:** Bootstrap 5, logo.svg.

---

## Observaciones importantes

1. **Rutas en vistas vs controladores:**
   - `olvide_contrasena.php` usa `validar_usuario` pero el método del controlador es `validarCedula()`.
   - `responder_pregunta.php` usa `guardar_nueva_clave` pero el método del controlador es `nuevaClave()`.


2. **Manejo de mensajes flash:**
   - `login.php` usa SweetAlert2 para `success` y `error`.
   - `olvide_contrasena.php` usa SweetAlert2 para `error`.
   - `responder_pregunta.php` usa alerta Bootstrap nativa para `$error` (variable pasada por el controlador).
   - `configurar_pregunta.php` usa alertas Bootstrap nativas para `error` e `info`.

3. **Estilos:**
   - Todas las vistas comparten la misma identidad visual (gradiente azul, tarjetas blancas con sombra, colores corporativos).
   - Los estilos están definidos en `<style>` dentro de cada vista (no se usa archivo CSS externo).

4. **Seguridad:**
   - Todas incluyen `<?= csrf_field() ?>` para protección CSRF.
   - Las contraseñas se hashean en el controlador antes de almacenar en BD.

---

## Dependencias compartidas

- **Bootstrap 5:** CSS y JS (desde `public/bootstrap5/`).
- **SweetAlert2:** Solo en vistas que manejan mensajes flash de éxito/error (cargado desde CDN).
- **Logo:** `public/img/logo.svg`.

---

*Última actualización: 2026-07-12*