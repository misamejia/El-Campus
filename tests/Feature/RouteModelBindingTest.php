<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Noticia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteModelBindingTest extends TestCase
{
    use RefreshDatabase;

    private Categoria $categoria;

    private Autor $autor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoria = Categoria::create(['nombre' => 'Universidad', 'slug' => 'universidad']);
        $this->autor = Autor::create(['nombre' => 'Redacción El Campus', 'slug' => 'redaccion-el-campus']);
    }

    public function test_las_rutas_admin_usan_el_singular_en_espanol(): void
    {
        $this->assertSame(['noticia'], Route::getRoutes()->getByName('noticias.show')->parameterNames());
        $this->assertSame(['evento'], Route::getRoutes()->getByName('eventos.show')->parameterNames());
        $this->assertSame(['categoria'], Route::getRoutes()->getByName('categorias.show')->parameterNames());
        $this->assertSame(['autor'], Route::getRoutes()->getByName('autores.show')->parameterNames());
    }

    public function test_un_id_inexistente_devuelve_404_en_cada_recurso(): void
    {
        $this->actingAs($this->administrador());

        $this->get(route('noticias.show', 999))->assertNotFound();
        $this->get(route('noticias.edit', 999))->assertNotFound();
        $this->get(route('eventos.show', 999))->assertNotFound();
        $this->get(route('eventos.edit', 999))->assertNotFound();
        $this->get(route('categorias.show', 999))->assertNotFound();
        $this->get(route('categorias.edit', 999))->assertNotFound();
        $this->get(route('autores.show', 999))->assertNotFound();
        $this->get(route('autores.edit', 999))->assertNotFound();
    }

    public function test_los_registros_existentes_se_abren_con_el_modelo_resuelto(): void
    {
        $this->actingAs($this->administrador());

        $noticia = $this->crearNoticia();
        $evento = $this->crearEvento();

        $this->get(route('noticias.show', $noticia->id))->assertOk()->assertSee($noticia->titulo);
        $this->get(route('noticias.edit', $noticia->id))->assertOk()->assertSee($noticia->titulo);
        $this->get(route('eventos.show', $evento->id))->assertOk()->assertSee($evento->titulo);
        $this->get(route('eventos.edit', $evento->id))->assertOk()->assertSee($evento->titulo);
        $this->get(route('categorias.show', $this->categoria->id))->assertOk()->assertSee($this->categoria->nombre);
        $this->get(route('categorias.edit', $this->categoria->id))->assertOk()->assertSee($this->categoria->nombre);
        $this->get(route('autores.show', $this->autor->id))->assertOk()->assertSee($this->autor->nombre);
        $this->get(route('autores.edit', $this->autor->id))->assertOk()->assertSee($this->autor->nombre);
    }

    public function test_actualizar_y_eliminar_usan_el_modelo_resuelto(): void
    {
        $this->actingAs($this->administrador());

        $categoria = Categoria::create(['nombre' => 'Cultura', 'slug' => 'cultura']);

        $this->put(route('categorias.update', $categoria->id), [
            'nombre' => 'Cultura y sociedad',
            'slug' => 'cultura-y-sociedad',
            'activa' => '1',
        ])->assertRedirect(route('categorias.index'));

        $this->assertDatabaseHas('categorias', [
            'id' => $categoria->id,
            'slug' => 'cultura-y-sociedad',
        ]);

        $this->delete(route('categorias.destroy', $categoria->id))
            ->assertRedirect(route('categorias.index'));

        $this->assertDatabaseMissing('categorias', ['id' => $categoria->id]);
    }

    private function administrador(): User
    {
        return User::factory()->admin()->create(['email_verified_at' => now()]);
    }

    private function crearNoticia(): Noticia
    {
        return Noticia::create([
            'titulo' => 'Noticia con binding de modelo',
            'slug' => 'noticia-con-binding-de-modelo',
            'categoria_id' => $this->categoria->id,
            'autor_id' => $this->autor->id,
            'estado_id' => $this->idEstado('estados_noticia', 'publicada'),
            'contenido' => 'Contenido de demostración.',
        ]);
    }

    private function crearEvento(): Evento
    {
        return Evento::create([
            'titulo' => 'Evento con binding de modelo',
            'slug' => 'evento-con-binding-de-modelo',
            'estado_id' => $this->idEstado('estados_evento', 'programado'),
            'descripcion' => 'Descripción del evento.',
            'lugar' => 'Auditorio central',
            'fecha_inicio' => now()->addDays(2),
        ]);
    }

    private function idEstado(string $tabla, string $codigo): int
    {
        return (int) DB::table($tabla)->where('codigo', $codigo)->value('id');
    }
}
