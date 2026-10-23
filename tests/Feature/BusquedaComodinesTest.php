<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Noticia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusquedaComodinesTest extends TestCase
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
            'nombre' => 'Redacción Ciencia',
            'slug' => 'redaccion-ciencia',
        ]);
    }

    private function crearNoticia(string $titulo, string $slug, string $resumen = 'Resumen'): Noticia
    {
        return Noticia::create([
            'titulo' => $titulo,
            'slug' => $slug,
            'categoria_id' => $this->categoria->id,
            'autor_id' => $this->autor->id,
            'resumen' => $resumen,
            'contenido' => 'Contenido de la noticia.',
            'fecha_publicacion' => now()->subDay(),
            'estado_id' => $this->idEstadoNoticia('publicada'),
            'destacada' => false,
        ]);
    }

    public function test_buscar_100_por_ciento_no_devuelve_todo(): void
    {
        $noticia = $this->crearNoticia('Semana de la ciencia', 'semana-de-la-ciencia');

        $this->get(route('buscar', ['q' => '100%']))
            ->assertOk()
            ->assertSee('No encontramos resultados para tu búsqueda.')
            ->assertDontSee($noticia->titulo);
    }

    public function test_buscar_guion_bajo_no_devuelve_todo(): void
    {
        $noticia = $this->crearNoticia('Semana de la ciencia', 'semana-de-la-ciencia');

        $this->get(route('buscar', ['q' => '_']))
            ->assertOk()
            ->assertSee('No encontramos resultados para tu búsqueda.')
            ->assertDontSee($noticia->titulo);
    }

    public function test_buscar_barra_invertida_no_rompe_la_consulta(): void
    {
        $this->get(route('buscar', ['q' => '\\']))
            ->assertOk();
    }

    public function test_la_busqueda_normal_sigue_funcionando_con_acentos_y_mayusculas(): void
    {
        $noticia = $this->crearNoticia(
            'Universidad de Córdoba inaugura laboratorio',
            'universidad-de-cordoba-inaugura-laboratorio'
        );

        $this->get(route('buscar', ['q' => 'Córdoba']))
            ->assertOk()
            ->assertSee($noticia->titulo);

        $this->get(route('buscar', ['q' => 'UNIVERSIDAD']))
            ->assertOk()
            ->assertSee($noticia->titulo);
    }
}
