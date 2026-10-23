<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\EstadoEvento;
use App\Models\EstadoNoticia;
use App\Models\Evento;
use App\Models\HistorialEstadoEvento;
use App\Models\HistorialEstadoNoticia;
use App\Models\Noticia;
use App\Models\Organizador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HistorialEstadosShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Categoria::firstOrCreate(['nombre' => 'Universidad'], ['slug' => 'universidad']);
        Autor::firstOrCreate(['nombre' => 'Redacción El Campus'], ['slug' => 'redaccion-el-campus']);
        Organizador::firstOrCreate(['nombre' => 'Vicerrectoría de Investigación']);
    }

    private function administrador(): User
    {
        return User::factory()->admin()->create(['email_verified_at' => now()]);
    }

    public function test_la_vista_de_noticia_muestra_historial_cuando_existe_y_no_cuando_no_existe(): void
    {
        $admin = $this->administrador();
        $estadoAnterior = EstadoNoticia::where('codigo', 'borrador')->firstOrFail();
        $estadoNuevo = EstadoNoticia::where('codigo', 'publicada')->firstOrFail();

        $noticia = Noticia::create([
            'titulo' => 'Noticia con historial',
            'slug' => 'noticia-con-historial',
            'categoria_id' => Categoria::first()->id,
            'autor_id' => Autor::first()->id,
            'resumen' => 'Resumen',
            'contenido' => 'Contenido',
            'fecha_publicacion' => now(),
            'estado_id' => $estadoNuevo->id,
            'destacada' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('noticias.show', $noticia))
            ->assertStatus(200)
            ->assertDontSee('Historial de estados');

        $fecha = now();
        HistorialEstadoNoticia::create([
            'noticia_id' => $noticia->id,
            'estado_anterior_id' => $estadoAnterior->id,
            'estado_nuevo_id' => $estadoNuevo->id,
            'usuario_id' => $admin->id,
            'cambiado_en' => $fecha,
        ]);

        $this->actingAs($admin)
            ->get(route('noticias.show', $noticia))
            ->assertStatus(200)
            ->assertSee('Historial de estados')
            ->assertSee($estadoAnterior->nombre)
            ->assertSee($estadoNuevo->nombre)
            ->assertSee($admin->name)
            ->assertSee($fecha->format('d/m/Y H:i'));
    }

    public function test_la_vista_de_evento_muestra_historial_cuando_existe_y_no_cuando_no_existe(): void
    {
        $admin = $this->administrador();
        $estadoAnterior = EstadoEvento::where('codigo', 'programado')->firstOrFail();
        $estadoNuevo = EstadoEvento::where('codigo', 'realizado')->firstOrFail();

        $evento = Evento::create([
            'titulo' => 'Evento con historial',
            'slug' => 'evento-con-historial',
            'descripcion' => 'Descripción',
            'lugar' => 'Aula',
            'fecha_inicio' => now()->addDays(5),
            'fecha_fin' => null,
            'organizador_id' => Organizador::first()->id,
            'estado_id' => $estadoNuevo->id,
        ]);

        $this->actingAs($admin)
            ->get(route('eventos.show', $evento))
            ->assertStatus(200)
            ->assertDontSee('Historial de estados');

        $fecha = now();
        HistorialEstadoEvento::create([
            'evento_id' => $evento->id,
            'estado_anterior_id' => $estadoAnterior->id,
            'estado_nuevo_id' => $estadoNuevo->id,
            'usuario_id' => $admin->id,
            'cambiado_en' => $fecha,
        ]);

        $this->actingAs($admin)
            ->get(route('eventos.show', $evento))
            ->assertStatus(200)
            ->assertSee('Historial de estados')
            ->assertSee($estadoAnterior->nombre)
            ->assertSee($estadoNuevo->nombre)
            ->assertSee($admin->name)
            ->assertSee($fecha->format('d/m/Y H:i'));
    }

    public function test_no_hay_n1_en_historial_de_noticias(): void
    {
        $admin = $this->administrador();
        $estadoAnterior = EstadoNoticia::where('codigo', 'borrador')->firstOrFail();
        $estadoNuevo = EstadoNoticia::where('codigo', 'publicada')->firstOrFail();

        $noticia1 = Noticia::create([
            'titulo' => 'Noticia 1',
            'slug' => 'noticia-1',
            'categoria_id' => Categoria::first()->id,
            'autor_id' => Autor::first()->id,
            'resumen' => 'Resumen',
            'contenido' => 'Contenido',
            'fecha_publicacion' => now(),
            'estado_id' => $estadoNuevo->id,
            'destacada' => false,
        ]);

        $noticia2 = Noticia::create([
            'titulo' => 'Noticia 2',
            'slug' => 'noticia-2',
            'categoria_id' => Categoria::first()->id,
            'autor_id' => Autor::first()->id,
            'resumen' => 'Resumen',
            'contenido' => 'Contenido',
            'fecha_publicacion' => now(),
            'estado_id' => $estadoNuevo->id,
            'destacada' => false,
        ]);

        HistorialEstadoNoticia::create([
            'noticia_id' => $noticia1->id,
            'estado_anterior_id' => $estadoAnterior->id,
            'estado_nuevo_id' => $estadoNuevo->id,
            'usuario_id' => $admin->id,
            'cambiado_en' => now(),
        ]);

        for ($i = 0; $i < 4; $i++) {
            HistorialEstadoNoticia::create([
                'noticia_id' => $noticia2->id,
                'estado_anterior_id' => $estadoAnterior->id,
                'estado_nuevo_id' => $estadoNuevo->id,
                'usuario_id' => $admin->id,
                'cambiado_en' => now()->addSeconds($i),
            ]);
        }

        $this->actingAs($admin)->get(route('noticias.show', $noticia1));

        $consultas = 0;
        DB::listen(function () use (&$consultas) {
            $consultas++;
        });

        $this->actingAs($admin)->get(route('noticias.show', $noticia1));
        $c1 = $consultas;
        $consultas = 0;
        $this->actingAs($admin)->get(route('noticias.show', $noticia2));
        $c2 = $consultas;
        DB::getEventDispatcher()->forget('Illuminate\Database\Events\QueryExecuted');

        $this->assertSame($c1, $c2);
    }

    public function test_no_hay_n1_en_historial_de_eventos(): void
    {
        $admin = $this->administrador();
        $estadoAnterior = EstadoEvento::where('codigo', 'programado')->firstOrFail();
        $estadoNuevo = EstadoEvento::where('codigo', 'realizado')->firstOrFail();

        $evento1 = Evento::create([
            'titulo' => 'Evento 1',
            'slug' => 'evento-1',
            'descripcion' => 'Descripción',
            'lugar' => 'Aula',
            'fecha_inicio' => now()->addDays(5),
            'fecha_fin' => null,
            'organizador_id' => Organizador::first()->id,
            'estado_id' => $estadoNuevo->id,
        ]);

        $evento2 = Evento::create([
            'titulo' => 'Evento 2',
            'slug' => 'evento-2',
            'descripcion' => 'Descripción',
            'lugar' => 'Aula',
            'fecha_inicio' => now()->addDays(6),
            'fecha_fin' => null,
            'organizador_id' => Organizador::first()->id,
            'estado_id' => $estadoNuevo->id,
        ]);

        HistorialEstadoEvento::create([
            'evento_id' => $evento1->id,
            'estado_anterior_id' => $estadoAnterior->id,
            'estado_nuevo_id' => $estadoNuevo->id,
            'usuario_id' => $admin->id,
            'cambiado_en' => now(),
        ]);

        for ($i = 0; $i < 4; $i++) {
            HistorialEstadoEvento::create([
                'evento_id' => $evento2->id,
                'estado_anterior_id' => $estadoAnterior->id,
                'estado_nuevo_id' => $estadoNuevo->id,
                'usuario_id' => $admin->id,
                'cambiado_en' => now()->addSeconds($i),
            ]);
        }

        $this->actingAs($admin)->get(route('eventos.show', $evento1));

        $consultas = 0;
        DB::listen(function () use (&$consultas) {
            $consultas++;
        });

        $this->actingAs($admin)->get(route('eventos.show', $evento1));
        $c1 = $consultas;
        $consultas = 0;
        $this->actingAs($admin)->get(route('eventos.show', $evento2));
        $c2 = $consultas;
        DB::getEventDispatcher()->forget('Illuminate\Database\Events\QueryExecuted');

        $this->assertSame($c1, $c2);
    }
}
