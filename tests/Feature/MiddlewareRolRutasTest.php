<?php

namespace Tests\Feature;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

class MiddlewareRolRutasTest extends TestCase
{
    /**
     * Acciones de controlador que reciben un Form Request de escritura.
     *
     * @var array<int, string>
     */
    private const ACCIONES = [
        'App\Http\Controllers\NoticiaController@store',
        'App\Http\Controllers\NoticiaController@update',
        'App\Http\Controllers\EventoController@store',
        'App\Http\Controllers\EventoController@update',
        'App\Http\Controllers\CategoriaController@store',
        'App\Http\Controllers\CategoriaController@update',
        'App\Http\Controllers\AutorController@store',
        'App\Http\Controllers\AutorController@update',
    ];

    public function test_todas_las_rutas_que_usan_form_requests_exigen_un_rol(): void
    {
        $rutas = $this->rutasDeEscritura();

        $this->assertCount(count(self::ACCIONES), $rutas, 'Hay rutas de escritura sin Form Request o acciones sin ruta.');

        foreach ($rutas as $ruta) {
            $this->assertTrue(
                $this->tieneMiddlewareDeRol($ruta),
                "La ruta {$ruta->uri()} ({$ruta->getActionName()}) no exige un rol."
            );
        }
    }

    public function test_cada_accion_de_escritura_tiene_su_ruta_protegida(): void
    {
        $acciones = $this->rutasDeEscritura()->map(fn (Route $ruta) => $ruta->getActionName())->all();

        foreach (self::ACCIONES as $accion) {
            $this->assertContains($accion, $acciones, "La acción {$accion} no está registrada en ninguna ruta.");
        }
    }

    /**
     * @return Collection<int, Route>
     */
    private function rutasDeEscritura()
    {
        return collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $ruta) => in_array($ruta->getActionName(), self::ACCIONES, true))
            ->values();
    }

    private function tieneMiddlewareDeRol(Route $ruta): bool
    {
        foreach ($ruta->gatherMiddleware() as $middleware) {
            if (is_string($middleware) && str_starts_with($middleware, 'role:')) {
                return true;
            }
        }

        return false;
    }
}
