# Pendientes conocidos

Deuda técnica y riesgos identificados durante la refactorización, deliberadamente fuera
del alcance de los cambios ya aplicados. No requieren acción inmediata, pero conviene
resolverlos antes de exponer el portal a internet.

## 1. Proxy inverso y `throttle:register` — pendiente permanente

**Decisión:** el proyecto se ejecuta en un único equipo local, sin proxy inverso, así que
**no se implementa**. Queda documentado por si algún día se despliega detrás de Nginx,
Cloudflare o un balanceador.

**Riesgo:** el limitador del alta de cuentas (`FortifyServiceProvider`, 10/min) usa la IP
del cliente. `bootstrap/app.php` no llama a `trustProxies()`, así que detrás de un proxy
todos los clientes comparten la IP del proxy: el límite se vuelve global y puede bloquear
registros legítimos.

**Dónde:** `bootstrap/app.php` (no hay `->trustProxies(...)`), `FortifyServiceProvider::boot`.

**Arreglo (solo si se despliega con proxy):** configurar `$middleware->trustProxies(at: [...])`
con las IPs/CIDR reales del proxy (nunca `*` en producción) y verificar que el proxy
reescribe `X-Forwarded-For`.

## 2. `POST /register` definido dos veces

**Riesgo:** Fortify registra su propia ruta de alta y `routes/web.php:21` la redefine con
el mismo método, URI y nombre (`register.store`) solo para añadir `throttle:register`.
Hoy gana la versión de `web.php` (la que se ve en `route:list` con el throttle), pero
depende del orden de registro: si Fortify pasa a registrarse después, o cambia su `prefix`,
el alta queda sin límite de intentos sin que nada falle visiblemente.

**Dónde:** `routes/web.php:21-24`, `config/fortify.php`.

**Arreglo:** dejar que Fortify registre la ruta y aplicar el limitador por configuración o
en un middleware global sobre `register`, en lugar de duplicar la definición. Si se
mantiene la duplicación, cubrirla con un test que compruebe que la ruta efectiva conserva
`ThrottleRequests:register` (hoy no existe).

## 3. Carrera en `SlugUnico`

**Riesgo:** `SlugUnico::generar()` comprueba la existencia del slug y lo devuelve; la fila
se inserta después, en otra consulta. Dos peticiones simultáneas con el mismo título pueden
elegir el mismo slug: la segunda choca con el índice único y responde 500 en lugar de
reintentar con otro sufijo.

**Dónde:** `app/Support/SlugUnico.php:31-36`, usado por noticias, eventos, categorías y
autores.

**Arreglo:** capturar la violación de unicidad al guardar y reintentar con el siguiente
sufijo, o hacer la reserva del slug en la misma transacción que el `insert`.

## 4. `exists` no exige que la categoría esté activa

**Riesgo:** `StoreNoticiaRequest`/`UpdateNoticiaRequest` validan `categoria_id` con
`exists:categorias,id`, sin comprobar `activa = true`. Se puede publicar una noticia en una
categoría desactivada: la noticia es visible, pero no aparece en la sección de esa
categoría, lo que produce contenido "huérfano" de navegación.

**Dónde:** `app/Http/Requests/StoreNoticiaRequest.php:25`,
`app/Http/Requests/UpdateNoticiaRequest.php:25`.

**Arreglo:** `Rule::exists('categorias', 'id')->where('activa', true)` (y el equivalente
para autores si aplica), o una regla propia con mensaje en español.

## 5. Duplicación entre Form Requests

**Riesgo:** las parejas `Store*`/`Update*` repiten reglas, mensajes y helpers
(`tendraImagen()`), y la validación de la ruta de texto de la imagen está copiada en
`StoreEventoRequest` y `UpdateEventoRequest` (regex + closure `is_file`). Un cambio en una
copia deja la otra desincronizada.

**Dónde:** `app/Http/Requests/`, sobre todo los dos de eventos.

**Arreglo:** extraer reglas compartidas a una clase de reglas (`App\Rules\RutaImagenPublica`)
y los mensajes a un trait o a un `messages()` común, sin cambiar el comportamiento.

## 6. Historial de estados en SQLite

**Situación:** las filas de `historial_estados_noticia` y `historial_estados_evento` las
crean triggers definidos en MySQL (`2026_09_12_235000_create_triggers_and_views.php`). En
SQLite los triggers no existen, por lo que `tests/Feature/HistorialEstadosShowTest.php`
inserta las filas directamente con los modelos para poder verificar el render.

**Consecuencia:** la suite no cubre el trigger de base de datos; solo cubre que las vistas
muestran y ocultan el historial y que no hay N+1. Si se migra de motor, hay que revisar los
triggers.

## 7. Documentación de controladores públicos

**Situación:** `README.md` y `docs/GUIA_SUSTENTACION.md` ya apuntan a
`NoticiaPublicController`, `EventoPublicController` y `SeccionController`. Si se añaden
nuevas páginas públicas, hay que mantener esa separación y no volver a los controladores del
panel.

## 8. Alta de editores sin pasar por el registro público

**Situación:** el administrador solo puede promover cuentas que ya se registraron y
verificaron su correo. Para dar acceso a un editor hay que pedirle que se registre (nace
lector), verifique su correo y después promoverlo desde `/usuarios`.

**Mejora propuesta:** formulario de alta directa para el administrador que cree la cuenta
con una contraseña temporal y obligue a cambiarla en el primer inicio de sesión (o envíe
una invitación por correo). Debe seguir sin permitir que el propio administrador se cambie
el rol ni que quede el sistema sin administradores.

## 9. Autenticación en dos pasos y API de Jetstream desactivadas

**Situación:** `config/jetstream.php` mantiene comentadas `Features::api()` y
`Features::twoFactorAuthentication()`; `accountDeletion` sí está activa. Por eso la suite
omite 7 tests: 3 de API (`ApiTokenPermissionsTest`, `CreateApiTokenTest`,
`DeleteApiTokenTest`), 3 de 2FA (`TwoFactorAuthenticationSettingsTest`) y 1 de
`RegistrationTest` que solo aplica cuando el registro está desactivado.

**Riesgo si se activan:** hoy `GET /api/user` solo exige `auth:sanctum`, así que cualquier
usuario con token podría consultarlo; al habilitar tokens hay que impedir que un lector cree
tokens y revisar qué expone la API. Al activar 2FA, probar el flujo completo con un lector.

**Dónde:** `config/jetstream.php`, `config/fortify.php`, `routes/api.php`, tests omitidos.

## 10. Soft deletes, `es_publico`, ancho/alto y borrado de histórico

**Situación (diferido):** `noticias` y `eventos` no usan `SoftDeletes`. Los medios tienen
columnas `ancho`/`alto` previstas pero no se usan para imponer reglas. `estados_noticia` y
`estados_evento` usan `es_publico` para filtrar.

**Razón:** los triggers `trg_noticias_ai_historial` / `trg_noticias_au_historial` /
`trg_eventos_*` crean filas en `historial_estados_noticia`/`historial_estados_evento` para
**toda** noticia/evento. Un `FOREIGN KEY` con `ON DELETE RESTRICT` en esas tablas de
historial **NO sirve** para impedir el borrado físico: el propio trigger de INSERT ya
genera el historial al crear el registro, por lo que intentar borrar quedaría bloqueado o
sería inviable. Además, si se usa `SoftDeletes` con `slug` único, el slug seguiría
retenido y dificultaría reutilizarlo. Por ello estas mejoras se dejan **diferidas** hasta
definir la política de archivo/borrado explícitamente.

**Acción propuesta (futura):** si se necesita archivo, valorar `SoftDeletes` sin índice
único estricto sobre slug en vigentes, o mover a otra tabla; revisar FKs del historial
antes de habilitarlo.

## 11. Vistas SQL y FULLTEXT

**Situación:** la migración MySQL crea 5 vistas (`v_noticias_publicas`,
`v_eventos_publicos`, `v_dashboard_resumen`, `v_noticias_por_estado`,
`v_eventos_por_estado`) y 2 índices `FULLTEXT` (`ft_noticias_busqueda`,
`ft_eventos_busqueda`). **Hoy no se usan** en el código de aplicación: el buscador usa
`LIKE` parametrizado (`BusquedaController`) y los listados/dashboard usan Eloquent.

**Destino:** depende del **enunciado de la materia**. No se borran en esta auditoría.
Conservadas para evaluación/documentación.

## 12. Ruta `/api/user`

**Situación:** `routes/api.php` expone `GET /user` con `auth:sanctum`. **Nada lo usa** en
el código. Cualquier usuario con sesión válida (incluido un **lector**) puede abrirla si
hay token/sesión en ese contexto. `Features::api()` está comentado en
`config/jetstream.php`.

**Decisión:** pendiente del dueño. No se elimina ni se restringe por rol en esta auditoría.
