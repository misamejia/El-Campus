<?php

use App\Http\Controllers\AutorController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\PaginaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;

// Fortify registra el alta de cuentas sin ningún límite de intentos. Se
// redefine la misma ruta (mismo método, URI y nombre) para añadir el
// limitador 'register': 5 registros por minuto y por IP.
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware(['guest:'.config('fortify.guard'), 'throttle:register'])
    ->name('register.store');

Route::get('/', [PaginaController::class, 'inicio'])->name('inicio');

Route::get('/buscar', [BusquedaController::class, 'index'])->name('buscar');

// El slug se restringe a minúsculas, números y guiones: las rutas solo
// aceptan slugs reales generados por Str::slug, y las URLs basura terminan
// en 404 sin llegar a consultar la base de datos.
// El método de la entidad vive en NoticiaController (una tabla, un controlador).
Route::get('/noticia/{slug}', [NoticiaController::class, 'publica'])
    ->name('noticia.publica')
    ->where('slug', '[a-z0-9-]+');

Route::get('/eventos', [EventoController::class, 'publicas'])->name('eventos.publica');

Route::get('/evento/{slug}', [EventoController::class, 'publico'])
    ->name('evento.publica')
    ->where('slug', '[a-z0-9-]+');

// Secciones editoriales del periódico: cada ruta estática muestra las
// noticias publicadas de su categoría mediante una única vista reutilizable,
// atendida por CategoriaController (el controlador de la tabla categories).
// El slug de la categoría se inyecta con defaults() porque el path es fijo.
Route::get('/universidad', [CategoriaController::class, 'seccion'])->name('seccion.universidad')->defaults('seccion', 'universidad');
Route::get('/investigacion', [CategoriaController::class, 'seccion'])->name('seccion.investigacion')->defaults('seccion', 'investigacion');
Route::get('/cultura', [CategoriaController::class, 'seccion'])->name('seccion.cultura')->defaults('seccion', 'cultura');
Route::get('/deportes', [CategoriaController::class, 'seccion'])->name('seccion.deportes')->defaults('seccion', 'deportes');
Route::get('/opinion', [CategoriaController::class, 'seccion'])->name('seccion.opinion')->defaults('seccion', 'opinion');

Route::get('/sobre-el-campus', [PaginaController::class, 'sobreElCampus'])->name('sobre-el-campus');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/perfil', [PaginaController::class, 'perfil'])->name('perfil');

    // Administrador y editor: noticias y gestión administrativa de eventos.
    Route::middleware('role:administrador,editor')->group(function () {
        Route::resource('noticias', NoticiaController::class);

        // El CRUD administrativo de eventos se agrupa bajo /admin para evitar
        // conflictos con la ruta pública GET /eventos (portada de eventos).
        Route::prefix('admin')->group(function () {
            Route::resource('eventos', EventoController::class);
        });
    });

    // Solo administrador: gestión de autores, categorías y de roles de usuarios.
    Route::middleware('role:administrador')->group(function () {
        Route::resource('autores', AutorController::class);
        Route::resource('categorias', CategoriaController::class);

        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::patch('/usuarios/{usuario}/rol', [UsuarioController::class, 'actualizarRol'])->name('usuarios.rol.actualizar');
    });
});
