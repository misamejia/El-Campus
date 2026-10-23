# El Campus

Periódico Universitario de la Universidad de Córdoba (Colombia).

## Descripción

**El Campus** es una aplicación web desarrollada con Laravel que representa un periódico universitario digital. El sitio publica noticias y eventos de la vida universitaria (investigación, cultura, deportes, opinión, etc.) y cuenta con un panel administrativo para gestionar el contenido editorial.

La interfaz está en español y su identidad visual se inspiró conceptualmente en periódicos universitarios y editoriales, sin copiar diseños, textos ni marcas propietarias.

## Objetivo

Centralizar la publicación y administración del contenido informativo de la universidad: permitir que la comunidad acceda a noticias y eventos publicados y que un equipo editorial (editores y administradores) gestione dicho contenido de forma segura y controlada, desde la portada dinámica hasta el detalle de cada publicación.

## Tecnologías

- Laravel 12
- PHP 8.2
- MySQL (MariaDB)
- Blade
- Jetstream
- Livewire
- Tailwind CSS (Vite)
- Vite
- Eloquent ORM
- PHPUnit

## Funcionalidades

### Sitio público

- **Portada dinámica** (`GET /`): noticia destacada, noticias secundarias, últimas noticias y bloques por categoría. Solo muestra noticias publicadas.
- **Noticias** (`GET /noticia/{slug}`): detalle de cada noticia publicada.
- **Eventos** (`GET /eventos` y `GET /evento/{slug}`): agenda con eventos programados y realizados; los cancelados no aparecen públicamente.
- **Buscador** (`GET /buscar`): busca en título, resumen y contenido de noticias publicadas, así como en la categoría y el autor; y en título, descripción, lugar y organizador de eventos programados/realizados.

### Administración

- **Dashboard** (`GET /dashboard`): estadísticas generales, estados del contenido, últimas noticias y próximos eventos.
- **Noticias**: CRUD completo (crear, listar, mostrar, editar, eliminar) con estados `publicada`, `borrador` y `archivada`, y marcado de noticia destacada.
- **Eventos**: CRUD completo bajo `/admin/eventos` con estados `programado`, `realizado` y `cancelado`.
- **Autores**: CRUD de autores del periódico (normalización de la antigua columna de texto).
- **Categorías**: CRUD de categorías editoriales (nombre, slug único, descripción y estado activo); una categoría con noticias asociadas no se puede eliminar.
- **Usuarios y roles**: gestión del rol de cada usuario (`administrador` o `editor`).

## Roles

- **Administrador**: acceso a la totalidad del panel (dashboard, noticias, eventos, autores, categorías y usuarios/roles).
- **Editor**: acceso al dashboard, a noticias y a eventos; no puede administrar autores, categorías ni usuarios/roles.

La autorización se implementa con un middleware propio (`EnsureUserHasRole`) que recibe los roles permitidos en cada grupo de rutas. No se utiliza un paquete externo de roles.

## Instalación

Requisitos: PHP 8.2+, Composer, MySQL/MariaDB y Node.js (con npm).

```bash
composer install

# Copiar la configuración y ajustarla (APP_KEY, base de datos, URL)
copy .env.example .env
# En Windows, editar .env:
#   DB_CONNECTION=mysql
#   DB_HOST=127.0.0.1
#   DB_PORT=3306
#   DB_DATABASE=el_campus
#   DB_USERNAME=<usuario>
#   DB_PASSWORD=<contraseña>
#   APP_URL=http://127.0.0.1:8000

php artisan key:generate
php artisan migrate
php artisan db:seed

npm install
npm run build

php artisan serve
```

La aplicación queda disponible en `http://127.0.0.1:8000`.

> **Nota sobre los roles**: los usuarios recién registrados se crean con rol `editor`. Para disponer del primer `administrador`, promuévalo desde una sesión de administrador existente (sección *Usuarios*) o, en una instalación nueva, asígnelo directamente en la base de datos:
>
> ```sql
> UPDATE users SET role = 'administrador' WHERE email = '...';
> ```

## Base de datos

Entidades principales:

- **users** — usuarios del sistema (nombre, correo, rol: `administrador`/`editor`).
- **categories** — categorías editoriales de las noticias (Universidad, Investigación, Cultura, Deportes, Eventos, Opinión).
- **autores** — autores o equipos editoriales de las noticias.
- **noticias** — publicaciones con título, slug, resumen, contenido, imagen, fecha, estado y marca de destacada; relacionadas con una categoría y un autor.
- **eventos** — acontecimientos con título, slug, descripción, lugar, fechas, organizador y estado.

Relaciones principales:

- `Categoria` (1) — (N) `Noticia` (`hasMany` / `belongsTo`).
- `Autor` (1) — (N) `Noticia` (`hasMany` / `belongsTo`).

El estado de `noticias` y `eventos` restringe la visibilidad pública: solo se muestran noticias `publicada` y eventos `programado`/`realizado`.

## Arquitectura MVC

Cada **tabla funcional** de la aplicación tiene su propio **modelo Eloquent** y su propio **controlador** (patrón Ruta → Controlador → Modelo → Base de datos → Vista). El mapeo completo es:

| Tabla | Modelo | Controlador | Ubicación de las rutas |
|---|---|---|---|
| users | `User` | `UsuarioController` | `/usuarios` (solo administrador) |
| categories | `Categoria` | `CategoriaController` | CRUD `/categorias` (solo administrador) + secciones públicas `/universidad`, `/investigacion`, `/cultura`, `/deportes`, `/opinion` |
| autores | `Autor` | `AutorController` | `/autores` (solo administrador) |
| noticias | `Noticia` | `NoticiaController` | CRUD `/noticias` + página pública `/noticia/{slug}` |
| eventos | `Evento` | `EventoController` | CRUD `/admin/eventos` + portada `/eventos` y detalle `/evento/{slug}` |

Los métodos públicos de cada entidad viven en un controlador dedicado: `NoticiaPublicController@publica`, `EventoPublicController@publicas`/`publico` y `SeccionController@seccion`. `PaginaController` se limita a páginas transversales y estáticas (`inicio`, `sobre-el-campus`, `perfil`), `BusquedaController` es el buscador transversal y `DashboardController` el panel.

Las tablas internas del framework (migraciones, password_reset_tokens, sessions, cache, cache_locks, jobs, job_batches, personal_access_tokens, passkeys) no representan entidades del dominio y no reciben modelo ni controlador de aplicación.

## Autenticación

La autenticación la proporciona **Laravel Jetstream** (con Livewire): registro, inicio y cierre de sesión, recuperación de contraseña, verificación de correo, perfil, actualización de contraseña y confirmación de contraseña. Las rutas del panel exigen sesión iniciada y correo verificado.

## Pruebas

La suite de pruebas (PHPUnit vía `php artisan test`) cubre autenticación, roles, CRUD de noticias/categorías/autores/eventos, portada pública, buscador, secciones editoriales y errores:

```bash
php artisan test
```

## Desarrollo del frontend

Tailwind CSS y JavaScript se compilan con Vite:

```bash
npm install
npm run dev     # durante el desarrollo
npm run build   # para producción
```

## Producción

Recomendaciones generales para un despliegue seguro:

- `APP_ENV=production`
- `APP_DEBUG=false`
- Usar credenciales de base de datos restringidas.
- Usar HTTPS/SSL en el servidor.
- No versionar `.env` (ya está excluido en `.gitignore`).
- Ejecutar `php artisan config:cache`, `php artisan route:cache` y `php artisan view:cache` tras desplegar.