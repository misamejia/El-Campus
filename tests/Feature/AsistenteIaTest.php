<?php

namespace Tests\Feature;

use App\Livewire\Noticias\AsistenteIa;
use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Noticia;
use App\Models\User;
use App\Services\Ai\AiManager;
use App\Services\Ai\Gateways\FakeGateway;
use App\Support\Similitud;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class AsistenteIaTest extends TestCase
{
    use RefreshDatabase;

    protected Categoria $categoria;

    protected Autor $autor;

    protected function setUp(): void
    {
        parent::setUp();

        // Los tests nunca deben llamar a la red: forzamos el driver local.
        config()->set('ai.driver', 'fake');

        $this->categoria = Categoria::create(['nombre' => 'Universidad', 'slug' => 'universidad']);
        $this->autor = Autor::create(['nombre' => 'Redacción El Campus', 'slug' => 'redaccion-el-campus']);
    }

    private function usuario(): User
    {
        return User::factory()->editor()->create(['email_verified_at' => now()]);
    }

    private function crearNoticia(array $sobreescribe = []): Noticia
    {
        return Noticia::create(array_merge([
            'titulo' => 'La universidad inaugura un laboratorio de investigación',
            'slug' => 'la-universidad-inaugura-un-laboratorio-'.$this->fakerSlug(),
            'categoria_id' => $this->categoria->id,
            'autor_id' => $this->autor->id,
            'resumen' => 'La universidad puso en marcha un nuevo laboratorio.',
            'contenido' => 'La universidad inauguró un laboratorio de investigación científica. '
                .'El laboratorio permitirá desarrollar nuevos proyectos académicos.',
            'fecha_publicacion' => now(),
            'estado_id' => $this->idEstadoNoticia('publicada'),
            'destacada' => false,
        ], $sobreescribe));
    }

    private function fakerSlug(): string
    {
        return 'noticia-'.uniqid();
    }

    public function test_el_driver_por_defecto_es_demo_sin_api_key(): void
    {
        $ai = app(AiManager::class);

        $this->assertFalse($ai->esReal());
        $this->assertSame('demo', $ai->nombre());
        $this->assertInstanceOf(FakeGateway::class, $ai->gateway());
    }

    public function test_el_resumen_devuelve_resumen_y_puntos_clave(): void
    {
        $resultado = app(AiManager::class)->resumir(
            'Titular de prueba',
            'Primera frase del cuerpo. Segunda frase del cuerpo. Tercera frase del cuerpo.',
        );

        $this->assertArrayHasKey('resumen', $resultado);
        $this->assertArrayHasKey('puntos_clave', $resultado);
        $this->assertNotSame('', $resultado['resumen']);
        $this->assertNotEmpty($resultado['puntos_clave']);
    }

    public function test_las_etiquetas_y_los_titulares_son_listas_no_vacias(): void
    {
        $ai = app(AiManager::class);
        $cuerpo = 'La universidad inauguró un laboratorio de investigación científica en Montería. '
            .'El laboratorio permitirá investigaciones avanzadas.';

        $this->assertNotEmpty($ai->titulares('Titular de la noticia', $cuerpo));
        $this->assertNotEmpty($ai->etiquetas('Titular de la noticia', $cuerpo));
    }

    public function test_el_embedding_es_un_vector_normalizado(): void
    {
        $vector = app(AiManager::class)->embedding('texto de prueba para el embedding');

        $this->assertNotEmpty($vector);

        $norma = sqrt(array_sum(array_map(fn (float $valor) => $valor * $valor, $vector)));
        $this->assertEqualsWithDelta(1.0, $norma, 0.0001);
    }

    public function test_la_similitud_es_mayor_entre_textos_parecidos(): void
    {
        $ai = app(AiManager::class);

        $base = $ai->embedding('La universidad inaugura un laboratorio de ciencias');
        $parecido = $ai->embedding('La universidad inaugura un laboratorio de investigación');
        $distinto = $ai->embedding('El equipo de fútbol ganó el torneo regional');

        $this->assertGreaterThan(
            Similitud::coseno($base, $distinto),
            Similitud::coseno($base, $parecido),
        );
    }

    public function test_el_asistente_genera_un_resumen(): void
    {
        Livewire::test(AsistenteIa::class)
            ->call('generarResumen', 'Titular de prueba', 'Primera frase. Segunda frase. Tercera frase.')
            ->assertDispatched('ia-resumen')
            ->assertSet('resumen', fn ($valor) => is_string($valor) && $valor !== '')
            ->assertSet('puntos', fn ($puntos) => is_array($puntos) && $puntos !== []);
    }

    public function test_el_asistente_sugiere_titulares_y_etiquetas(): void
    {
        $cuerpo = 'La universidad inauguró un laboratorio de investigación científica y académica.';

        Livewire::test(AsistenteIa::class)
            ->call('sugerirTitulares', 'Titular', $cuerpo)
            ->assertSet('titulares', fn ($titulares) => is_array($titulares) && $titulares !== []);

        Livewire::test(AsistenteIa::class)
            ->call('sugerirEtiquetas', 'Titular', $cuerpo)
            ->assertSet('etiquetas', fn ($etiquetas) => is_array($etiquetas) && $etiquetas !== []);
    }

    public function test_el_asistente_revisa_el_estilo(): void
    {
        Livewire::test(AsistenteIa::class)
            ->call('revisarEstilo', 'Titular', 'Contenido del texto periodístico.')
            ->assertSet('estilo', fn ($estilo) => is_array($estilo) && $estilo !== [])
            ->assertSet('estiloResumen', fn ($valor) => is_string($valor) && $valor !== '');
    }

    public function test_el_asistente_aparece_en_el_formulario_de_creacion(): void
    {
        $this->actingAs($this->usuario())
            ->get(route('noticias.create'))
            ->assertOk()
            ->assertSee('Asistente de redacción')
            ->assertSee('name="puntos_clave"', false)
            ->assertSee('name="etiquetas_ia"', false)
            ->assertSee('$wire.generarResumen', false)
            ->assertSee('ponerEnCampo', false);
    }

    public function test_publicar_genera_resumen_embedding_y_etiquetas(): void
    {
        $this->actingAs($this->usuario())->post(route('noticias.store'), [
            'titulo' => 'La universidad inaugura un laboratorio',
            'categoria_id' => $this->categoria->id,
            'autor_id' => $this->autor->id,
            'contenido' => 'La universidad inauguró un laboratorio de investigación. '
                .'El laboratorio permitirá nuevas investigaciones científicas.',
            'estado' => 'publicada',
        ])->assertRedirect(route('noticias.index'));

        $noticia = Noticia::firstOrFail();

        $this->assertNotNull($noticia->resumen);
        $this->assertNotEmpty($noticia->embedding);
        $this->assertNotNull($noticia->embedding_actualizado_en);
        $this->assertNotEmpty($noticia->etiquetas_ia);
    }

    public function test_un_fallo_de_ia_no_impide_guardar_la_noticia(): void
    {
        $this->instance(AiManager::class, new class extends AiManager
        {
            public function embedding(string $texto): array
            {
                throw new RuntimeException('fallo simulado de IA');
            }
        });

        $this->actingAs($this->usuario())->post(route('noticias.store'), [
            'titulo' => 'Noticia que debe guardarse pese al fallo',
            'categoria_id' => $this->categoria->id,
            'autor_id' => $this->autor->id,
            'contenido' => 'Contenido de la noticia.',
            'estado' => 'borrador',
        ])->assertRedirect(route('noticias.index'));

        $this->assertDatabaseHas('noticias', [
            'titulo' => 'Noticia que debe guardarse pese al fallo',
        ]);
    }

    public function test_el_comando_ai_backfill_rellena_los_campos(): void
    {
        $noticia = $this->crearNoticia();
        $this->assertNull($noticia->embedding);

        $this->artisan('ai:backfill', ['--solo' => 'todo'])->assertSuccessful();

        $noticia->refresh();
        $this->assertNotEmpty($noticia->embedding);
        $this->assertNotEmpty($noticia->etiquetas_ia);
        $this->assertNotEmpty($noticia->puntos_clave);
    }

    public function test_la_noticia_publica_muestra_claves_etiquetas_y_el_boton_de_escucha(): void
    {
        $noticia = $this->crearNoticia([
            'puntos_clave' => ['Punto clave número uno', 'Punto clave número dos'],
            'etiquetas_ia' => ['educacion', 'laboratorio'],
        ]);

        $this->get(route('noticia.publica', $noticia->slug))
            ->assertOk()
            ->assertSee('Claves de la noticia')
            ->assertSee('Punto clave número uno')
            ->assertSee('Escuchar noticia')
            ->assertSee('#educacion')
            ->assertSee('speechSynthesis', false);
    }

    public function test_la_noticia_muestra_relacionadas_por_similitud(): void
    {
        $ai = app(AiManager::class);

        $principal = $this->crearNoticia([
            'titulo' => 'Laboratorio de ciencias de la universidad',
            'contenido' => 'La universidad inaugura un laboratorio de ciencias para la investigación.',
            'embedding' => $ai->embedding('universidad laboratorio ciencias investigación académica'),
            'embedding_actualizado_en' => now(),
        ]);

        $relacionada = $this->crearNoticia([
            'titulo' => 'Nueva investigación científica en la universidad',
            'contenido' => 'La investigación avanza en el laboratorio de ciencias.',
            'embedding' => $ai->embedding('universidad laboratorio ciencias investigación académica avanza'),
            'embedding_actualizado_en' => now(),
        ]);

        $this->crearNoticia([
            'titulo' => 'El equipo de fútbol ganó el torneo regional',
            'contenido' => 'El equipo celebró la victoria en el torneo.',
            'embedding' => $ai->embedding('futbol equipo torneo victoria deporte regional'),
            'embedding_actualizado_en' => now(),
        ]);

        $this->get(route('noticia.publica', $principal->slug))
            ->assertOk()
            ->assertSee('También te puede interesar')
            ->assertSee($relacionada->titulo);
    }
}
