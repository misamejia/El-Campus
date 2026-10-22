<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Medio;
use App\Models\Noticia;
use App\Services\Medios\GestorMedios;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
