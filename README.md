# IT App — Sistema de Gestión de Soporte Técnico

Sistema web interno desarrollado en PHP para la gestión de colaboradores, tickets de soporte técnico y usuarios, orientado a equipos de TI de pequeñas y medianas organizaciones.

---

## Descripción

IT App es una aplicación MVC construida con PHP puro que permite administrar el ciclo de vida de los soportes técnicos dentro de una empresa. Centraliza el registro de colaboradores por área, la asignación y seguimiento de tickets de soporte, y el control de acceso por roles. El sistema está diseñado para funcionar en servidores locales (Laragon, XAMPP) como herramienta interna del departamento de TI.

---

## Características principales

- **Gestión de usuarios** — Creación, edición y desactivación de cuentas con roles diferenciados (admin, consultor, usuario). Cambio seguro de contraseña con bcrypt.
- **Gestión de colaboradores** — Registro completo de empleados incluyendo cargo, país, área, equipo asignado, fechas de ingreso y salida.
- **Gestión de soportes técnicos** — Creación y seguimiento de tickets con niveles de atención (bajo, medio, alto, crítico) y estados (abierto, en proceso, cerrado).
- **Dashboard con métricas** — KPIs en tiempo real: tickets por estado, por nivel de atención, tendencia mensual de los últimos 12 meses, top 5 colaboradores del mes y últimos tickets abiertos urgentes.
- **Control de acceso por roles (RBAC)** — Tres niveles de acceso: `admin` (acceso total), `consultant` (dashboard, colaboradores, soportes), `user` (solo perfil propio).
- **Eliminación lógica (soft delete)** — Usuarios y colaboradores no se eliminan de la base de datos; se marcan como eliminados mediante `idstatus` y `deleted_at` para preservar la integridad referencial.
- **Sistema de estados** — Tabla catálogo `status` con tres estados posibles: activo, inactivo, eliminado. Reutilizada en usuarios y colaboradores.
- **Seguridad integrada** — Protección CSRF, session fingerprinting, sanitización de entradas, escapado de salidas HTML, cabeceras de seguridad HTTP y logging estructurado.

---

## Tecnologías utilizadas

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.1.10 (strict_types) |
| Base de datos | MySQL con PDO (utf8mb4) |
| Frontend / UI | AdminLTE 3 (Bootstrap 4) |
| Alertas | SweetAlert2 |
| Tablas interactivas | DataTables |
| Selectores dinámicos | Select2 |
| Gráficas | Chart.js |
| Servidor local | Laragon / XAMPP |

---

## Estructura del proyecto

```
itapp/
├── app/
│   ├── controllers/        # Controladores MVC (Auth, User, Collaborator, Support, Area, Dashboard, Profile)
│   ├── helpers/            # Utilidades: View, Session, Auth, Csrf, Sanitizer, Logger, Redirect
│   ├── middleware/         # Middleware de pipeline: AuthMiddleware, CsrfMiddleware, RoleMiddleware
│   └── models/             # Modelos de acceso a datos: User, Collaborator, Support, Area, Status
├── config/
│   ├── config.php          # Constantes de la aplicación (APP_NAME, APP_URL, APP_ENV, timezone)
│   └── database.php        # Conexión PDO singleton a MySQL
├── core/
│   └── Router.php          # Router ligero con soporte para parámetros y middleware por ruta
├── public/
│   ├── index.php           # Punto de entrada, bootstrap, autoloader, cabeceras de seguridad
│   └── assets/
│       ├── css/            # Hojas de estilo personalizadas
│       ├── img/            # Imágenes y logos
│       └── js/             # Scripts personalizados
├── routes/
│   └── web.php             # Definición de todas las rutas con sus middlewares
├── storage/
│   ├── logs/
│   │   └── app.log         # Log estructurado de la aplicación
│   └── migrations/         # Archivos SQL ordenados (000–005) para crear y evolucionar el esquema
└── views/
    ├── areas/              # Vistas CRUD de áreas
    ├── auth/               # Vista de login
    ├── collaborators/      # Vistas CRUD de colaboradores
    ├── dashboard/          # Vista del panel con KPIs y gráficas
    ├── errors/             # Páginas de error 403, 404 y 500
    ├── layouts/            # Layout principal (AdminLTE) y layout de autenticación
    ├── profile/            # Vista de perfil del usuario autenticado
    ├── supports/           # Vistas CRUD de tickets de soporte
    └── users/              # Vistas CRUD de usuarios
```

---

## Instalación

### Requisitos previos

- PHP 8.1 o superior
- MySQL 5.7 o superior
- Laragon, XAMPP o servidor web con soporte para PHP
- El servidor debe servir la raíz del proyecto desde `/public`

### Pasos

**1. Clonar el repositorio**

```bash
git clone https://github.com/thsDave/itapp.git
cd itapp
```

**2. Configurar la conexión a la base de datos**

Editar el archivo `config/database.php` y ajustar las credenciales:

```php
private string $host     = 'localhost';
private string $dbname   = 'itapp';
private string $username = 'root';
private string $password = '';
```

**3. Crear la base de datos e importar las migraciones**

Desde phpMyAdmin o la línea de comandos de MySQL:

```sql
CREATE DATABASE itapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Luego importar los archivos SQL en orden desde `storage/migrations/`:

```bash
mysql -u root itapp < storage/migrations/000_initial_schema.sql
mysql -u root itapp < storage/migrations/001_add_updated_at.sql
mysql -u root itapp < storage/migrations/001_seed_data.sql
mysql -u root itapp < storage/migrations/002_areas_and_collaborator_update.sql
mysql -u root itapp < storage/migrations/003_status_table_and_user_soft_delete.sql
mysql -u root itapp < storage/migrations/004_collaborators_soft_delete.sql
mysql -u root itapp < storage/migrations/005_collaborators_idstatus.sql
```

> El archivo `001_seed_data.sql` es opcional. Carga usuarios, áreas, colaboradores y tickets de prueba.

**4. Configurar la URL de la aplicación**

En `config/config.php`, ajustar `APP_URL` según el entorno:

```php
define('APP_URL', 'http://localhost/itapp/public');
```

**5. Permisos de escritura**

Asegurarse de que la carpeta `storage/logs/` tenga permisos de escritura para el servidor web.

**6. Acceder a la aplicación**

```
http://localhost/itapp/public
```

Si se usó el seed de datos, las credenciales de acceso de prueba son:

| Email | Contraseña | Rol |
|---|---|---|
| alice@example.com | password123 | admin |
| bruno@example.com | password123 | consultant |
| carmen@example.com | password123 | user |

---

## Base de datos

### Tabla `status`

Catálogo centralizado de estados usado por usuarios y colaboradores:

| idstatus | status |
|---|---|
| 1 | active |
| 2 | inactive |
| 3 | deleted |

### Soft delete

Los registros de usuarios y colaboradores nunca se borran físicamente. Al eliminar:
- Se establece `idstatus = 3` (deleted)
- Se registra `deleted_at` con la fecha y hora de eliminación

Todas las consultas filtran automáticamente los registros con `idstatus = 3`, por lo que el registro desaparece de la interfaz pero se conserva en la base de datos para mantener la integridad de los tickets de soporte asociados.

### Relaciones principales

- `collaborators.area_id` → `areas.id` (ON DELETE SET NULL)
- `collaborators.idstatus` → `status.idstatus`
- `users.idstatus` → `status.idstatus`
- `supports.collaborator_id` → `collaborators.id` (ON DELETE RESTRICT)
- `supports.user_id` → `users.id` (ON DELETE SET NULL)

La restricción `ON DELETE RESTRICT` en `supports.collaborator_id` evita eliminar un colaborador que tenga tickets asociados, protegiendo la trazabilidad del historial de soporte.

---

## Uso del sistema

1. **Ingresar** con credenciales válidas en `/login`.
2. El sistema redirige automáticamente según el rol:
   - `admin` y `consultant` → Dashboard
   - `user` → Perfil propio
3. Desde el **Dashboard** se visualizan métricas generales y el estado actual de los tickets.
4. En **Colaboradores** se registran y administran los empleados de la empresa.
5. En **Soportes** se crean tickets asociados a un colaborador, se les asigna nivel de atención y se actualiza su estado conforme avanza la atención.
6. En **Usuarios** (solo admin) se administran las cuentas del sistema.
7. En **Áreas** (solo admin) se gestionan las unidades organizativas de la empresa.

---

## Autor

Desarrollado por **David Ramos** como sistema interno de gestión de soporte técnico.

- GitHub: [@thsDave](https://github.com/thsDave)
- Email: soporte@cristosal.org

---

## Licencia

Proyecto de uso interno. Sin licencia pública asignada.
