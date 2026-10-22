<?php

namespace Tests\Feature;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

/**
 * Comprueba el comportamiento del registro cuando la feature de Fortify
 * está desactivada.
 *
 * La aplicación se arranca con la feature apagada (antes de que los
 * proveedores registren las rutas) para reproducir el escenario real.
 */
class RegistroDesactivadoTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = require __DIR__.'/../../bootstrap/app.php';

        $app->booting(function () {
            // config/fortify.php declara las features como una lista de
            // nombres; para desactivar el registro se quita de la lista.
            config()->set('fortify.features', array_values(array_diff(
                config('fortify.features', []),
                [Features::registration()]
            )));
        });

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function test_la_registro_esta_desactivada(): void
    {
        $this->assertFalse(Features::enabled(Features::registration()));
    }

    public function test_el_formulario_de_registro_no_existe(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_el_post_de_registro_no_existe(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();
    }

    public function test_la_ruta_de_registro_no_esta_registrada(): void
    {
        $this->assertNull(app('router')->getRoutes()->getByName('register.store'));
    }
}
