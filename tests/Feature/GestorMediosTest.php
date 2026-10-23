<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Medio;
use App\Models\Noticia;
use App\Models\User;
use App\Services\Medios\GestorMedios;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class GestorMediosTest extends TestCase
{
    use RefreshDatabase;

    private GestorMedios $gestor;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->gestor = new GestorMedios;
    }

    public function test_guardar_crea_el_medio_con_el_prefijo_storage_y_el_texto_alternativo(): void
    {
        $request = Request::create('/noticias', 'POST', ['imagen_alt' => 'Portada del campus'], [], [
            'imagen' => UploadedFile::fake()->create('portada.jpg', 10, 'image/jpeg'),
        ]);

        $medio = $this->gestor->guardar($request, 'imagenes/noticias');

        $this->assertStringStartsWith('storage/imagenes/noticias/', $medio->ruta);
        $this->assertSame('public', $medio->disco);
        $this->assertSame('Portada del campus', $medio->texto_alternativo);
        $this->assertTrue(Storage::disk('public')->exists(str_replace('storage/', '', $medio->ruta)));
    }

    public function test_desde_entrada_acepta_una_ruta_de_texto_y_actualiza_el_texto_alternativo(): void
    {
        $medio = Medio::create([
            'disco' => 'public',
            'ruta' => 'imagenes/eventos/demo.jpg',
            'tipo_mime' => 'image/jpeg',
            'tamano_bytes' => 10,
        ]);

        $request = Request::create('/eventos', 'POST', [
            'imagen' => 'imagenes/eventos/demo.jpg',
            'imagen_alt' => 'Charla inaugural',
        ]);

        $id = $this->gestor->desdeEntrada($request, 'imagenes/eventos/demo.jpg', 'imagenes/eventos');

        $this->assertSame($medio->id, $id);
        $this->assertSame('Charla inaugural', $medio->fresh()->texto_alternativo);
    }

    public function test_limpiar_no_borra_los_medios_demo_sin_prefijo_storage(): void
    {
        $medio = Medio::create([
            'disco' => 'public',
            'ruta' => 'imagenes/demo.jpg',
            'tipo_mime' => 'image/jpeg',
            'tamano_bytes' => 10,
        ]);

        $this->gestor->limpiar($medio);

        $this->assertDatabaseHas('medios', ['id' => $medio->id]);
    }

    public function test_limpiar_no_borra_el_medio_que_siguen_usando_noticias_o_eventos(): void
    {
        $medio = $this->medioSubido();
        $categoria = Categoria::create(['nombre' => 'Universidad', 'slug' => 'universidad']);
        $autor = Autor::create(['nombre' => 'Redacción El Campus', 'slug' => 'redaccion-el-campus']);

        Noticia::create([
            'titulo' => 'Noticia con imagen',
            'slug' => 'noticia-con-imagen',
            'categoria_id' => $categoria->id,
            'autor_id' => $autor->id,
            'estado_id' => $this->idEstado('estados_noticia', 'publicada'),
            'contenido' => 'Contenido de demostración.',
            'imagen_id' => $medio->id,
        ]);

        $this->gestor->limpiar($medio);

        $this->assertDatabaseHas('medios', ['id' => $medio->id]);
        $this->assertTrue(Storage::disk('public')->exists('imagenes/noticias/demo.jpg'));
    }

    public function test_limpiar_borra_el_archivo_y_el_registro_cuando_nadie_lo_usa(): void
    {
        $medio = $this->medioSubido();

        $this->gestor->limpiar($medio);

        $this->assertDatabaseMissing('medios', ['id' => $medio->id]);
        $this->assertFalse(Storage::disk('public')->exists('imagenes/noticias/demo.jpg'));
    }

    public function test_si_falla_al_crear_la_noticia_se_borra_el_archivo_y_el_medio(): void
    {
        $this->actingAs(User::factory()->admin()->create(['email_verified_at' => now()]));
        config(['app.debug' => false]);

        $categoria = Categoria::create(['nombre' => 'Universidad', 'slug' => 'universidad']);
        $autor = Autor::create(['nombre' => 'Redacción El Campus', 'slug' => 'redaccion-el-campus']);

        Noticia::creating(function () {
            throw new RuntimeException('Fallo simulado al guardar la noticia.');
        });

        $this->post(route('noticias.store'), [
            'titulo' => 'Noticia que no se guarda',
            'categoria_id' => $categoria->id,
            'autor_id' => $autor->id,
            'contenido' => 'Contenido de la noticia que falla.',
            'estado' => 'publicada',
            'imagen' => UploadedFile::fake()->create('portada.jpg', 10, 'image/jpeg'),
            'imagen_alt' => 'Portada de la noticia',
        ])->assertStatus(500);

        $this->assertDatabaseCount('medios', 0);
        $this->assertDatabaseCount('noticias', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_si_falla_al_crear_el_evento_se_borra_el_archivo_y_el_medio(): void
    {
        $this->actingAs(User::factory()->admin()->create(['email_verified_at' => now()]));
        config(['app.debug' => false]);

        Evento::creating(function () {
            throw new RuntimeException('Fallo simulado al guardar el evento.');
        });

        $this->post(route('eventos.store'), [
            'titulo' => 'Evento que no se guarda',
            'descripcion' => 'Descripción del evento que falla.',
            'lugar' => 'Auditorio principal',
            'fecha_inicio' => now()->addDay()->toDateString(),
            'organizador' => 'Vicerrectoría',
            'estado' => 'programado',
            'imagen' => UploadedFile::fake()->create('evento.jpg', 10, 'image/jpeg'),
            'imagen_alt' => 'Afiche del evento',
        ])->assertStatus(500);

        $this->assertDatabaseCount('medios', 0);
        $this->assertDatabaseCount('eventos', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_si_falla_el_registro_del_medio_se_borra_el_archivo_recien_escrito(): void
    {
        Medio::creating(function () {
            throw new RuntimeException('Fallo simulado al registrar el medio.');
        });

        $request = Request::create('/noticias', 'POST', ['imagen_alt' => 'Portada del campus'], [], [
            'imagen' => UploadedFile::fake()->create('portada.jpg', 10, 'image/jpeg'),
        ]);

        try {
            $this->gestor->guardar($request, 'imagenes/noticias');
            $this->fail('Se esperaba la excepción del registro del medio.');
        } catch (RuntimeException $e) {
            $this->assertSame('Fallo simulado al registrar el medio.', $e->getMessage());
        }

        $this->assertDatabaseCount('medios', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    private function medioSubido(): Medio
    {
        Storage::disk('public')->put('imagenes/noticias/demo.jpg', 'contenido');

        return Medio::create([
            'disco' => 'public',
            'ruta' => 'storage/imagenes/noticias/demo.jpg',
            'tipo_mime' => 'image/jpeg',
            'tamano_bytes' => 10,
        ]);
    }

    private function idEstado(string $tabla, string $codigo): int
    {
        return (int) DB::table($tabla)->where('codigo', $codigo)->value('id');
    }
}
