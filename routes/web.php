<?php

use App\Http\Controllers\AutorController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\EventoPublicController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\NoticiaPublicController;
use App\Http\Controllers\PaginaController;
use App\Http\Controllers\SeccionController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;

// Fortify registra el alta de cuentas sin ningún límite de intentos. Se
// redefine la misma ruta (mismo método, URI y nombre) para añadir el
// limitador 'register': 10 registros por minuto y por IP. El límite es
// alto porque la universidad accede a la red detrás de NAT, con many
// usuarios compartiendo la misma IP pública.
if (Features::enabled(Features::registration())) {
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware(['guest:'.config('fortify.guard'), 'throttle:register'])
        ->name('register.store');
}

Route::get('/', [PaginaController::class, 'inicio'])->name('inicio');

Route::get('/buscar', [BusquedaController::class, 'index'])->name('buscar');

// El slug se restringe a minúsculas, números y guiones: las rutas solo
// aceptan slugs reales generados por Str::slug, y las URLs basura terminan
// en 404 sin llegar a consultar la base de datos.
// El método de la entidad vive en NoticiaController (una tabla, un controlador).
// El slug se restringe a min\u00fasculas, n\u00fameros y guiones: las rutas solo
// aceptan slugs reales generados por Str::slug, y las URLs basura terminan
// en 404 sin llegar a consultar la base de datos.
Route::get('/noticia/{slug}', [NoticiaPublicController::class, 'publica'])
    ->name('noticia.publica')
    ->where('slug', '[a-z0-9-]+');

Route::get('/eventos', [EventoPublicController::class, 'publicas'])->name('eventos.publica');

Route::get('/evento/{slug}', [EventoPublicController::class, 'publico'])
    ->name('evento.publica')
    ->where('slug', '[a-z0-9-]+');

// Secciones editoriales del peri\u00f3dico: cada ruta est\u00e1tica muestra las
// noticias publicadas de su categor\u00eda mediante una \u00fanica vista reutilizable,
// atendida por SeccionController. Las rutas se registran en bucle desde una
// lista fija para mantener los nombres seccion.* y los defaults.
foreach (['universidad', 'investigacion', 'cultura', 'deportes', 'opinion'] as $seccion) {
    Route::get("/{$seccion}", [SeccionController::class, 'seccion'])
        ->name("seccion.{$seccion}")
        ->defaults('seccion', $seccion);
}

Route::get('/sobre-el-campus', [PaginaController::class, 'sobreElCampus'])->name('sobre-el-campus');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/perfil', [PaginaController::class, 'perfil'])->name('perfil');

    // El panel (dashboard, noticias y eventos) es solo para personal con
    // permisos de edición. Un lector autenticado y verificado conserva su
    // perfil, pero recibe 403 en estas rutas.
    Route::middleware('role:administrador,editor')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('noticias', NoticiaController::class);

        // El CRUD administrativo de eventos se agrupa bajo /admin para evitar
        // conflictos con la ruta pública GET /eventos (portada de eventos).
        Route::prefix('admin')->group(function () {
            Route::resource('eventos', EventoController::class);
        });
    });

    // Solo administrador: gestión de autores, categorías y de roles de usuarios.
    Route::middleware('role:administrador')->group(function () {
        // El parámetro por defecto sería {autore}; se fija {autor} para mantener
        // el singular en español, igual que {noticia}, {evento} y {categoria}.
        Route::resource('autores', AutorController::class)->parameters(['autores' => 'autor']);
        Route::resource('categorias', CategoriaController::class);

        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::patch('/usuarios/{usuario}/rol', [UsuarioController::class, 'actualizarRol'])->name('usuarios.rol.actualizar');
    });
});
