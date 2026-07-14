# Módulo de Backup para CodeIgniter 4

Sistema de respaldo de base de datos (MySQL/MariaDB) integrado en la vista de bitácora, con control de acceso por roles y limpieza automática de archivos antiguos.

## Estructura de archivos

| Archivo | Propósito |
|---------|-----------|
| `app/Helpers/backup_helper.php` | Funciones para crear, listar, eliminar y limpiar backups (PHP puro). |
| `app/Controllers/BackupController.php` | Controlador con acciones `create`, `list`, `download`, `delete`. Verifica permisos de administrador. |
| `app/Config/Routes.php` | Rutas para los endpoints del backup. |
| `app/Views/bitacora.php` | Vista que incluye el botón y dropdown de backup (integrado en la barra de filtros). |
| `writable/backups/` | Directorio donde se almacenan los archivos `.sql` (y `.gz` si se comprimen). |

## Configuración

1. **Registrar el helper** en `app/Config/Autoload.php`:
   ```php
   public $helpers = ['backup'];