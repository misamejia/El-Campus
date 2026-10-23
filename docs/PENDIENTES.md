# Pendientes conocidos

Deuda técnica y riesgos identificados durante la refactorización, deliberadamente fuera
del alcance de los cambios ya aplicados. No requieren acción inmediata, pero conviene
resolverlos antes de exponer el portal a internet.

## 1. Proxy inverso y `throttle:register`

**Riesgo:** el limitador del alta de cuentas (`FortifyServiceProvider`, 10/min) usa la IP
del cliente. `bootstrap/app.php` no llama a `trustProxies()`, así que detrás de Nginx,
Cloudflare o un balanceador todos los clientes comparten la IP del proxy: el límite se
vuelve global y puede bloquear registros legítimos.

**Dónde:** `bootstrap/app.php` (no hay `->trustProxies(...)`), `FortifyServiceProvider::boot`.

**Arreglo:** configurar `$middleware->trustProxies(at: [...])` con las IPs/CIDR reales del
proxy (nunca `*` en producción) y verificar que el proxy reescribe `X-Forwarded-For`.

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
