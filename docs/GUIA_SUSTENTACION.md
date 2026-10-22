# Guía de Sustentación — El Campus

Periódico Universitario de la Universidad de Córdoba
Proyecto final — **Desarrollo de Aplicaciones Web II**

---

## 1. Presentación del proyecto

El Campus es un periódico universitario digital construido con **Laravel 12**. La aplicación tiene dos áreas: un **sitio público** que muestra las noticias y eventos de la universidad, y un **panel administrativo** donde un equipo editorial publica y organiza ese contenido.

La información sale directamente de la base de datos: la portada elige una noticia destacada, las secundarias, las últimas y los bloques por categoría; los eventos se publican con su fecha y lugar. Solo lo que está marcado como *publicado/programado* es visible, y un sistema de roles define quién puede editar qué. El proyecto demuestra los conceptos centrales de la asignatura: rutas, controladores, modelos Eloquent, migraciones, seeders, vistas Blade con componentes y layouts, formularios con validación y CSRF, autenticación con Jetstream y middleware de autorización.

## 2. Problema

La universidad no cuenta con un medio unificado y digital para comunicar sus noticias institucionales, académicas, culturales y deportivas. La información se dispersa en anuncios sueltos y redes sociales, sin una agenda confiable de eventos ni un archivo ordenado. Además, no existe un flujo editorial controlado: no queda claro qué se ha publicado, qué está en borrador o qué es un evento cancelado.

El Campus resuelve esa necesidad al centralizar noticias y eventos en una **base de datos única**, publicarlos desde un **panel editorial seguro** y mostrarlos en un sitio público ordenado y responsive.

## 3. Objetivo general

Desarrollar una aplicación web (Laravel + Blade) que funcione como periódico universitario digital: publicación de noticias y eventos, portada dinámica, buscador público y panel administrativo con roles, garantizando una interfaz en español, responsive y académicamente defendible.

## 4. Usuarios

- **Visitante (invitado)**: navega la portada, lee noticias publicadas, consulta eventos programados/realizados y usa el buscador. No accede al panel.
- **Editor**: entra al panel, administra noticias y eventos. No administra autores, categorías ni usuarios.
- **Administrador**: control total del panel: noticias, eventos, autores, categorías y usuarios/roles.

## 5. Arquitectura

El recorrido típico de una petición es:

```
Ruta (routes/web.php) → Controlador → Modelo / Eloquent → Base de datos → Vista Blade
```

- **Ruta**: asocia una URL y un método HTTP a un controlador.
- **Controlador**: recibe la petición, llama a los modelos y decide qué vista devolver.
- **Modelo / Eloquent**: consulta la base de datos de forma orientada a objetos.
- **Base de datos**: almacena el contenido (noticias, eventos, autores, categorías, usuarios).
- **Vista Blade**: genera el HTML que el navegador muestra al usuario.

Cuando la página no necesita datos (p. ej. `GET /login`), el recorrido es `Ruta → Controlador → Vista`.

El principio rector de la arquitectura es **una tabla funcional → un modelo → un controlador**. El mapeo real:

| Tabla | Modelo | Controlador | Rutas |
|---|---|---|---|
| users | `User` | `UsuarioController` | `/usuarios` (admin) |
| categories | `Categoria` | `CategoriaController` | CRUD `/categorias` (admin) + secciones públicas `/universidad`, `/investigacion`, `/cultura`, `/deportes`, `/opinion` |
| autores | `Autor` | `AutorController` | `/autores` (admin) |
| noticias | `Noticia` | `NoticiaController` | CRUD `/noticias` (admin/editor) + `/noticia/{slug}` público |
| eventos | `Evento` | `EventoController` | CRUD `/admin/eventos` (admin/editor) + `/eventos` y `/evento/{slug}` públicos |

Los métodos de cada entidad viven en su controlador: `NoticiaController@publica`, `EventoController@publicas`/`publico`, `CategoriaController@seccion`. `PaginaController` solo atiende páginas transversales y estáticas (`inicio`, `sobre-el-campus`, `perfil`); `BusquedaController` (buscador) y `DashboardController` (panel) son controladores de propósito específico. Las tablas internas de Laravel (sessions, cache, jobs, tokens, etc.) no son entidades del dominio, por lo que no llevan modelo ni controlador de aplicación.

## 6. Laravel

- **routes/web.php**: define las rutas de la aplicación; las públicas no llevan middleware y las administrativas están protegidas por autenticación y rol.
- **Controladores**: organizan la lógica; cada tabla funcional tiene el suyo (`NoticiaController`, `EventoController`, `CategoriaController`, `AutorController`, `UsuarioController`) y existen controladores transversales (`PaginaController` para páginas estáticas, `BusquedaController`, `DashboardController`).
- **Modelos**: representan entidades (`User`, `Categoria`, `Noticia`, `Autor`, `Evento`).
- **Migraciones**: definen el esquema de la base de datos de forma versionada.
- **Seeders**: llenan datos iniciales de forma reproducible (`firstOrCreate`/`updateOrCreate`).
- **Blade**: motor de plantillas de Laravel para las vistas.
- **Componentes Blade**: piezas reutilizables (`cabecera`, `navegacion`, `tarjeta-noticia`, `tarjeta-evento`, `titular`, `etiqueta`, `boton`, `alerta`, `pie`).
- **Eloquent ORM**: consultas (`where`, `orderBy`, `with` para relaciones) y relaciones entre modelos.
- **Middleware**: `auth` (sesión), `verified` (correo) y el middleware propio `EnsureUserHasRole` (roles).
- **Validación**: reglas en el servidor para noticias, autores, eventos y roles.
- **CSRF**: token obligatorio en todos los formularios que modifican datos.

## 7. Blade

- Los archivos terminan en `.blade.php`; son vistas con sintaxis de Laravel compiladas a HTML.
- `{{ $variable }}` **escapa** el valor (previene XSS); `{!! !!}` solo se usa cuando hace falta renderizar HTML ya saneado (p. ej. `nl2br(e(...))` o el QR de Jetstream).
- **Componentes**: `<x-cabecera />`, `<x-tarjeta-noticia />` reciben datos y reutilizan marcado.
- **Layouts**: plantilla base que envuelve el contenido (`layouts/public.blade.php`, `layouts/app.blade.php`).
- **Slots y props**: los componentes reciben contenido (`slot`) y atributos (`props`) desde la vista que los usa.
- `@extends('layouts.public')` hereda un layout; `@section('titulo', ...)` define un bloque y `@yield('titulo')` lo muestra en la plantilla base.

## 8. CRUD (con Noticias como ejemplo)

Flujo: **ruta → controlador → modelo → base de datos → vista**.

- **Create**: `GET /noticias/create` (formulario) y `POST /noticias` (`store` valida y guarda).
- **Read**: `GET /noticias` (`index` lista) y `GET /noticias/{id}` (`show`, con `findOrFail`).
- **Update**: `GET /noticias/{id}/edit` (formulario) y `PUT /noticias/{id}` (`update` valida y guarda).
- **Delete**: `DELETE /noticias/{id}` (`destroy`, con confirmación y CSRF).

Las reglas se validan **en el servidor**: título obligatorio, slug único, categoría y autor existentes, estado permitido y fecha válida. El mismo patrón se repite en los demás CRUD (autores, categorías y eventos), cada uno en su propio controlador; las páginas públicas de esas entidades también las atiende su controlador (p. ej. `CategoriaController@seccion` para las secciones editoriales).

## 9. Relaciones

- `Categoria` **hasMany** `Noticia` / `Noticia` **belongsTo** `Categoria` (una noticia tiene una categoría; una categoría tiene muchas noticias).
- `Autor` **hasMany** `Noticia` / `Noticia` **belongsTo** `Autor` (normalización: la antigua columna de texto `autor` se reemplazó por `autor_id`).

Se usa `with(['categoria', 'autor'])` (eager loading) para evitar problemas N+1 en listados.

## 10. Autenticación (Jetstream)

Jetstream (con Livewire) implementa el registro, inicio/cierre de sesión, recuperación de contraseña, verificación de correo, actualización de perfil y contraseña, y confirmación de contraseña. El modelo `User` implementa `MustVerifyEmail`; las rutas del panel exigen sesión (`auth:sanctum`) y correo verificado (`verified`).

## 11. Roles

Dos roles: `administrador` y `editor`. Los usuarios se registran como `editor` por defecto; el cambio de rol se hace solo desde la sección *Usuarios* del panel.

El middleware propio `EnsureUserHasRole` recibe los roles permitidos (`role:administrador,editor`) y:
- si no hay sesión → redirige al login;
- si el rol no coincide → responde **403**.

Reglas adicionales en `UsuarioController`: un administrador no puede quitarse su propio rol, la operación no puede dejar el sistema sin administradores y el rol debe ser uno de los permitidos (validación `Rule::in`). El campo `role` no está en `$fillable` (sin asignación masiva).

## 12. Buscador

`GET /buscar` recibe `q` (opcional, máximo 100 caracteres), se normaliza con `trim` y colapso de espacios, y consulta con `LIKE` **parametrizado** (sin SQL manual):

- **Noticias** (solo `publicada`): título, resumen, contenido, categoría y autor.
- **Eventos** (solo `programado`/`realizado`): título, descripción, lugar y organizador; los **cancelados** quedan excluidos.

El término se muestra en la vista con `{{ }}` (escapado).

## 13. Seguridad

- **CSRF**: `@csrf` en todos los formularios POST/PATCH/DELETE.
- **Validación en el servidor**: reglas para noticias (slug único, categoría/autor existentes, estado permitido), autores, categorías (slug único, estado permitido), eventos (fechas coherentes, estado permitido) y roles (`Rule::in`).
- **Escaping**: `{{ }}` por defecto; `{!! !!}` solo donde es necesario y con datos saneados.
- **Autenticación**: Jetstream con verificación de correo.
- **Autorización**: middleware de roles que responde 403 para roles no permitidos.
- **Contenido no publicado**: la portada solo muestra `publicada`; los borradores, archivas y slugs inexistentes devuelven **404**; los eventos cancelados no son públicos.
- **Cabeceras de seguridad**: todas las respuestas (incluidas las de error) envían `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff` y `Referrer-Policy: strict-origin-when-cross-origin`.
- **Slugs restrictivos**: las rutas públicas `/noticia/{slug}` y `/evento/{slug}` solo aceptan el patrón `[a-z0-9-]+`, así las URLs basura terminan en 404 sin consultar la base de datos.
- **Sin secreto**: `.env` está en `.gitignore`; `.env.example` no contiene credenciales reales.

## 14. Responsive

La interfaz se comprobó a **375, 768, 1024 y 1440 px** en las páginas públicas (portada, buscador, eventos, noticia, evento, login y registro) midiendo el ancho real del contenido: **0 desbordes horizontales**. El menú móvil, las tarjetas y las tablas admin (con `overflow-x-auto`) se ajustan correctamente.

## 15. Pruebas

Resultado real de la suite completa (`php artisan test`) en la auditoría final:

- **Passed:** 198
- **Failed:** 0
- **Skipped:** 7
- **Assertions:** 533
- **Duración:** ~27 s

Complementan la verificación las comprobaciones HTTP reales (todas 200/302/404 esperados) y el `npm run build`/`php artisan view:cache` sin errores.

## 16. Base de datos

Estado real verificado (solo lectura) al finalizar la auditoría:

| Entidad | Cantidad |
|---|---|
| users | 2 |
| categories | 6 |
| autores | 9 |
| noticias | 13 |
| eventos | 8 |

Estados de noticias: **10 publicadas, 2 borradores, 1 archivada** · Estados de eventos: **4 programados, 3 realizados, 1 cancelado**.

Sin registros huérfanos: 0 noticias sin categoría, 0 sin autor, 9 autores con noticias, 0 autores y 0 categorías sin noticias.

## 17. Demostración práctica (10–15 minutos)

1. **Portada pública** (`/`): destacada, secundarias, últimas y bloques por categoría.
2. **Navegación**: enlaces e identidad visual; menú móvil si se desea.
3. **Noticia destacada**: señalar cómo se marca y cómo se prioriza.
4. **Detalle de noticia**: slug en la URL, contenido, categoría y autor.
5. **Eventos** (`/eventos`): próximos y realizados; mostrar un detalle.
6. **Buscador**: buscar un término (p. ej. "universidad") y mostrar los resultados filtrados.
7. **Login**: iniciar sesión como administrador.
8. **Dashboard**: estadísticas y accesos rápidos.
9. **CRUD de noticia**: crear → se muestra en la portada → editar → eliminar.
10. **CRUD de evento**: crear → aparece en eventos → editar → eliminar.
11. **Autores**: listado y protección (un autor con noticias no se puede eliminar).
12. **Categorías**: CRUD y protección (una categoría con noticias no se puede eliminar); mostrar cómo las secciones públicas dependen de estas categorías.
13. **Usuarios/roles**: promover a editor y mostrar la diferencia.
14. **Diferencia administrador/editor**: cerrar sesión, entrar como editor y probar que **no** accede a Autores, Categorías ni Usuarios (403).
15. **Volver al sitio público** y cerrar la demostración.

## 18. Preguntas posibles del profesor (respuestas cortas adaptadas al proyecto)

- **¿Qué es Laravel?** Framework de PHP para desarrollo web con rutas, Eloquent, Blade y herramientas de seguridad integradas.
- **¿Qué es MVC?** Separación en Modelo (datos), Vista (presentación) y Controlador (lógica); Laravel implementa este patrón.
- **¿Qué hace routes/web.php?** Relaciona cada URL y método HTTP con un controlador.
- **¿Qué es un controlador?** Clase que procesa la petición, consulta los modelos y devuelve una vista. Cada tabla funcional tiene el suyo: `NoticiaController`, `EventoController`, `CategoriaController`, `AutorController`, `UsuarioController`.
- **¿Qué es un modelo?** Clase que representa una tabla; en el proyecto, `Noticia`, `Evento`, `Autor`, `Categoria`, etc.
- **¿Qué es Eloquent?** El ORM de Laravel que permite consultar con métodos en lugar de SQL crudo.
- **¿Qué es una migración?** Definición versionada del esquema de tablas.
- **¿Qué es un seeder?** Código que siembra datos iniciales de forma reproducible (`firstOrCreate`).
- **¿Qué es Blade?** El motor de plantillas de Laravel.
- **¿Qué es un componente Blade?** Un `.blade.php` reutilizable con props y slots (p. ej. `tarjeta-noticia`).
- **¿Qué es CSRF?** Un token que garantiza que el formulario viene de la misma aplicación; Laravel lo verifica en cada POST/PATCH/DELETE con `@csrf`.
- **¿Qué es middleware?** Código que se ejecuta antes del controlador (p. ej. `auth`, `verified`, `role`).
- **¿Cómo funciona la autenticación?** Jetstream/Livewire gestiona sesión y verificación de correo; el panel exige sesión y rol.
- **¿Cómo funcionan los roles?** Columna `role` en `users` + middleware `EnsureUserHasRole` que permite 403 a roles no autorizados.
- **¿Cómo se relacionan las tablas?** `Categoria`/`Autor` (1) — `Noticia` (N) con claves foráneas `categoria_id` y `autor_id`.
- **¿Por qué se normalizó Autor?** Para eliminar la repetición del texto del autor y poder referenciarlo con su ID.
- **¿Cómo se evita mostrar noticias borrador?** El consulta pública filtra `estado = 'publicada'`; los demás estados devuelven 404.
- **¿Cómo funciona el buscador?** `q` opcional (máx. 100), se normaliza y se busca con `LIKE` parametrizado en campos permitidos.
- **¿Cómo se protege el panel?** Middleware de autenticación, verificación de correo y roles (`administrador`/`editor`).
- **¿Cómo se validan los formularios?** Con reglas del lado del servidor en cada controlador (`Request::validate`).
- **¿Cómo se realizaron las pruebas?** Con PHPUnit (`php artisan test`) más comprobaciones HTTP reales y mediciones responsive.

---

## Guion de exposición

> Guion hablado como guía, no para leer palabra por palabra.

**Apertura (30–60 segundos).** "Hoy les presento El Campus, un periódico universitario digital para la Universidad de Córdoba. Es una aplicación web construida con Laravel que tiene dos caras: la que ve el lector, con noticias y eventos, y un panel interno donde un equipo edita y publica ese contenido. Todo se alimenta de una base de datos y nadie puede entrar al panel sin estar registrado y autorizado."

**La portada.** "Si entramos a la página principal, los datos no están escritos a mano en la plantilla: salen de la base de datos. El controlador toma las noticias publicadas, elige la destacada, acompaña algunas secundarias, las últimas y, más abajo, bloques por categoría. Esto muestra el patrón ruta → controlador → modelo → vista; los nombres en la URL son slugs generados automáticamente."

**Eventos y buscador.** "En la sección de eventos dividimos próximos y realizados; un evento cancelado nunca aparece. El buscador recibe el término, lo normaliza y consulta solo contenido publicado, tanto en noticias como en eventos, con consultas parametrizadas; notamos que el término aparece escapado en la vista."

**El panel (loguearse como administrador).** "Ahora entramos al panel. El dashboard resume el estado: cuántas noticias hay y en qué estado, cuántos eventos vienen, y da accesos rápidos. La gestión de noticias es un CRUD completo: crear, ver, editar y eliminar; al crearla, se valida, se genera el slug único y al siguiente paso ya la vemos en la portada. Lo mismo con los eventos."

**Autores y roles.** "Los autores son una tabla aparte: normalizamos el autor de texto a una relación, y por eso no se puede borrar un autor que tenga noticias. Lo mismo con las categorías, que además alimentan las secciones públicas. En usuarios/roles se cambia el rol de cada persona. Aquí quiero mostrar algo importante: si entro como editor, puedo trabajar noticias y eventos, pero la sección de usuarios, la de autores y la de categorías responden 403, porque un middleware de roles controla cada grupo de rutas."

**Seguridad (sin ahondar demasiado).** "Como protección: todos los formularios incluyen CSRF, la validación ocurre en el servidor, el contenido se muestra siempre escapado, y el rol no se asigna por formularios públicos."

**Pruebas y responsive.** "Antes de cerrar: la suite de pruebas pasa completa —198 pruebas, sin fallos— y la interfaz fue verificada en pantallas de 375 a 1440 píxeles sin desbordes horizontales. Para repetir todo en cualquier equipo: instalar dependencias, configurar la base de datos, correr las migraciones y el build, y listo."

**Cierre.** "En síntesis, El Campus integra lo que vimos en Desarrollo de Aplicaciones Web II: Laravel, rutas, controladores, Eloquent, migraciones, seeders, Blade, formularios, autenticación y roles, todo en una aplicación real y en español. Quedo atento a sus preguntas."

---

## Checklist final

### Funcionalidad

- [ ] Portada funciona
- [ ] Noticias funcionan
- [ ] Eventos funcionan
- [ ] Buscador funciona
- [ ] Login funciona
- [ ] Registro funciona
- [ ] Dashboard funciona
- [ ] CRUD noticias funciona
- [ ] CRUD eventos funciona
- [ ] CRUD autores funciona
- [ ] CRUD categorías funciona
- [ ] Roles funcionan
- [ ] Usuarios funcionan

### Seguridad

- [ ] CSRF
- [ ] Validación
- [ ] Middleware
- [ ] Roles
- [ ] Escaping
- [ ] Cabeceras de seguridad
- [ ] Slugs restrictivos en rutas públicas
- [ ] Noticias privadas protegidas
- [ ] Eventos cancelados protegidos

### Calidad

- [ ] Tests pasan
- [ ] Build pasa
- [ ] Blade cache pasa
- [ ] No hay debug statements
- [ ] No hay credenciales en código
- [ ] No hay archivos temporales
- [ ] No hay rutas duplicadas
- [ ] No hay errores conocidos

### Entrega

- [ ] README actualizado
- [ ] Guía de sustentación creada
- [ ] Base de datos consistente
- [ ] .env no contiene secretos versionados
- [ ] Proyecto ejecutable