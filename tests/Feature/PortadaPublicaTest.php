<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Noticia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortadaPublicaTest extends TestCase
{
    use RefreshDatabase;

    protected Categoria $categoria;

    protected Autor $autor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoria = Categoria::create([
            'nombre' => 'Investigación',
            'slug' => 'investigacion',
        ]);

        $this->autor = Autor::create([
            'nombre' => 'Redacción Investigación',
            'slug' => 'redaccion-investigacion',
        ]);
    }

    private function crearNoticia(array $sobreescribe = []): Noticia
    {
        $estado = $sobreescribe['estado'] ?? 'publicada';
        unset($sobreescribe['estado']);

        return Noticia::create(array_merge([
            'titulo' => 'Noticia publicada de demostración',
            'slug' => 'noticia-publicada-de-demostracion',
            'categoria_id' => $this->categoria->id,
            'autor_id' => $this->autor->id,
            'resumen' => 'Resumen de demostración para la portada.',
            'contenido' => "Primer párrafo de la noticia de demostración.\n\nSegundo párrafo de la noticia.",
            'fecha_publicacion' => now()->subDay()->format('Y-m-d H:i:s'),
            'estado_id' => $this->idEstadoNoticia($estado),
            'destacada' => false,
        ], $sobreescribe));
    }

    public function test_la_portada_responde_correctamente(): void
    {
        $this->get(route('inicio'))
            ->assertOk();
    }

    public function test_una_noticia_publicada_aparece_en_la_portada(): void
    {
        $this->crearNoticia([
            'titulo' => 'La ciencia avanza en el campus',
            'slug' => 'la-ciencia-avanza-en-el-campus',
        ]);

        $this->get(route('inicio'))
            ->assertOk()
            ->assertSee('La ciencia avanza en el campus');
    }

    public function test_una_noticia_borrador_no_aparece_en_la_portada(): void
    {
        $this->crearNoticia([
            'titulo' => 'Borrador secreto del periodico',
            'slug' => 'borrador-secreto-del-periodico',
            'estado' => 'borrador',
        ]);

        $this->get(route('inicio'))
            ->assertOk()
            ->assertDontSee('Borrador secreto del periodico');
    }

    public function test_una_noticia_archivada_no_aparece_en_la_portada(): void
    {
        $this->crearNoticia([
            'titulo' => 'Noticia archivada antigua',
            'slug' => 'noticia-archivada-antigua',
            'estado' => 'archivada',
        ]);

        $this->get(route('inicio'))
            ->assertOk()
            ->assertDontSee('Noticia archivada antigua');
    }

    public function test_la_destacada_publicada_se_convierte_en_principal(): void
    {
        $this->crearNoticia([
            'titulo' => 'Destacada de la semana',
            'slug' => 'destacada-de-la-semana',
            'destacada' => true,
            'fecha_publicacion' => now()->subDays(2)->format('Y-m-d H:i:s'),
        ]);

        $this->crearNoticia([
            'titulo' => 'Otra noticia mas reciente',
            'slug' => 'otra-noticia-mas-reciente',
            'fecha_publicacion' => now()->format('Y-m-d H:i:s'),
        ]);

        $this->get(route('inicio'))
            ->assertOk()
            ->assertSeeInOrder(['Destacada de la semana', 'Otra noticia mas reciente']);
    }

    public function test_sin_destacadas_la_portada_usa_la_noticia_mas_reciente(): void
    {
        $this->crearNoticia([
            'titulo' => 'Noticia mas antigua',
            'slug' => 'noticia-mas-antigua',
            'fecha_publicacion' => now()->subDays(5)->format('Y-m-d H:i:s'),
        ]);

        $this->crearNoticia([
            'titulo' => 'Noticia mas reciente',
            'slug' => 'noticia-mas-reciente',
            'fecha_publicacion' => now()->format('Y-m-d H:i:s'),
        ]);

        $this->get(route('inicio'))
            ->assertOk()
            ->assertSeeInOrder(['Noticia mas reciente', 'Noticia mas antigua']);
    }

    public function test_la_portada_accede_a_las_relaciones_categoria_y_autor(): void
    {
        $this->crearNoticia([
            'titulo' => 'Relaciones en la portada',
            'slug' => 'relaciones-en-la-portada',
        ]);

        $this->get(route('inicio'))
            ->assertOk()
            ->assertSee('Relaciones en la portada')
            ->assertSee($this->categoria->nombre)
            ->assertSee($this->autor->nombre);
    }

    public function test_la_portada_funciona_sin_noticias_publicadas(): void
    {
        $this->crearNoticia([
            'titulo' => 'Borrador de ejemplo',
            'slug' => 'borrador-de-ejemplo',
            'estado' => 'borrador',
        ]);
        $this->crearNoticia([
            'titulo' => 'Archivado de ejemplo',
            'slug' => 'archivado-de-ejemplo',
            'estado' => 'archivada',
        ]);

        $this->get(route('inicio'))
            ->assertOk();
    }

    public function test_una_noticia_publicada_puede_verse_publicamente(): void
    {
        $this->crearNoticia([
            'titulo' => 'Laboratorio abre sus puertas',
            'slug' => 'laboratorio-abre-sus-puertas',
            'resumen' => 'Resumen visible de la noticia.',
        ]);

        $this->get(route('noticia.publica', 'laboratorio-abre-sus-puertas'))
            ->assertOk()
            ->assertSee('Laboratorio abre sus puertas')
            ->assertSee($this->categoria->nombre)
            ->assertSee($this->autor->nombre)
            ->assertSee('Primer párrafo de la noticia de demostración');
    }

    public function test_un_borrador_no_es_visible_publicamente(): void
    {
        $this->crearNoticia([
            'titulo' => 'Borrador oculto',
            'slug' => 'borrador-oculto',
            'estado' => 'borrador',
        ]);

        $this->get(route('noticia.publica', 'borrador-oculto'))
            ->assertNotFound();
    }

    public function test_una_archivada_no_es_visible_publicamente(): void
    {
        $this->crearNoticia([
            'titulo' => 'Archivada oculta',
            'slug' => 'archivada-oculta',
            'estado' => 'archivada',
        ]);

        $this->get(route('noticia.publica', 'archivada-oculta'))
            ->assertNotFound();
    }

    public function test_un_slug_inexistente_devuelve_404(): void
    {
        $this->get(route('noticia.publica', 'slug-que-no-existe'))
            ->assertNotFound();
    }

    public function test_una_noticia_publicada_sin_fecha_no_genera_error(): void
    {
        $this->crearNoticia([
            'titulo' => 'Noticia sin fecha definida',
            'slug' => 'noticia-sin-fecha-definida',
            'fecha_publicacion' => null,
        ]);

        $this->get(route('noticia.publica', 'noticia-sin-fecha-definida'))
            ->assertOk()
            ->assertSee('Noticia sin fecha definida');
    }
}
