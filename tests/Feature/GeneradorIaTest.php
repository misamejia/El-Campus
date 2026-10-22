<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Noticia;
use App\Models\User;
use App\Services\Ai\AiManager;
use App\Services\Noticias\GeneradorIa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class GeneradorIaTest extends TestCase
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

    public function test_el_generador_resume_y_etiqueta_una_noticia_publicada(): void
    {
        $noticia = $this->crearNoticia();

        app(GeneradorIa::class)->generar($noticia, 'publicada', contenidoCambio: true);

        $noticia = $noticia->fresh();

        $this->assertNotEmpty($noticia->resumen);
        $this->assertNotEmpty($noticia->etiquetas_ia);
        $this->assertNotEmpty($noticia->embedding);
    }

    public function test_el_generador_no_falla_cuando_el_proveedor_de_ia_falla(): void
    {
        $this->app->instance(AiManager::class, new class extends AiManager
        {
            public function embedding(string $texto): array
            {
                throw new RuntimeException('Proveedor de IA no disponible.');
            }

            public function resumir(string $titulo, string $contenido): array
            {
                throw new RuntimeException('Proveedor de IA no disponible.');
            }

            public function etiquetas(string $titulo, string $contenido, int $cantidad = 6): array
            {
                throw new RuntimeException('Proveedor de IA no disponible.');
            }
        });

        $noticia = $this->crearNoticia();

        app(GeneradorIa::class)->generar($noticia, 'publicada', contenidoCambio: true);

        $this->assertNull($noticia->fresh()->resumen);
    }

    public function test_guardar_una_noticia_no_devuelve_error_500_si_la_ia_falla(): void
    {
        $this->app->instance(AiManager::class, new class extends AiManager
        {
            public function embedding(string $texto): array
            {
                throw new RuntimeException('Proveedor de IA no disponible.');
            }
        });

        $this->actingAs(User::factory()->admin()->create(['email_verified_at' => now()]));

        $this->post(route('noticias.store'), [
            'titulo' => 'Noticia publicada sin IA',
            'categoria_id' => $this->categoria->id,
            'autor_id' => $this->autor->id,
            'contenido' => 'Contenido de la noticia de prueba.',
            'estado' => 'publicada',
        ])->assertRedirect(route('noticias.index'));

        $this->assertDatabaseHas('noticias', ['titulo' => 'Noticia publicada sin IA']);
    }

    private function crearNoticia(): Noticia
    {
        return Noticia::create([
            'titulo' => 'Noticia con IA',
            'slug' => 'noticia-con-ia',
            'categoria_id' => $this->categoria->id,
            'autor_id' => $this->autor->id,
            'estado_id' => $this->idEstado('estados_noticia', 'publicada'),
            'contenido' => 'Contenido de demostración para probar la generación automática.',
        ]);
    }

    private function idEstado(string $tabla, string $codigo): int
    {
        return (int) DB::table($tabla)->where('codigo', $codigo)->value('id');
    }
}
