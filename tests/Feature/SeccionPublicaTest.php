<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Noticia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeccionPublicaTest extends TestCase
{
    use RefreshDatabase;

    private const SECCIONES = ['universidad', 'investigacion', 'cultura', 'deportes', 'opinion'];

    protected function crearCategoria(string $nombre, string $slug, array $sobreescribe = []): Categoria
    {
        return Categoria::create(array_merge([
            'nombre' => $nombre,
            'slug' => $slug,
            'activa' => true,
        ], $sobreescribe));
    }

    protected function crearAutor(string $slug = 'redaccion-seccion'): Autor
    {
        return Autor::create([
            'nombre' => 'Redacción de la sección',
            'slug' => $slug,
        ]);
    }

    protected function crearNoticia(Categoria $categoria, Autor $autor, array $sobreescribe = []): Noticia
    {
        $estado = $sobreescribe['estado'] ?? 'publicada';
        unset($sobreescribe['estado']);

        return Noticia::create(array_merge([
            'titulo' => 'Noticia publicada de la sección',
            'slug' => 'noticia-publicada-de-la-seccion',
            'categoria_id' => $categoria->id,
            'autor_id' => $autor->id,
            'resumen' => 'Resumen de la noticia de demostración.',
            'contenido' => "Primer párrafo de la noticia.\n\nSegundo párrafo de la noticia.",
            'fecha_publicacion' => now()->subDay()->format('Y-m-d H:i:s'),
            'estado_id' => $this->idEstadoNoticia($estado),
            'destacada' => false,
        ], $sobreescribe));
    }

    public function test_todas_las_secciones_editoriales_responden_200(): void
    {
        $autor = $this->crearAutor();

        foreach (self::SECCIONES as $seccion) {
            $categoria = $this->crearCategoria(ucfirst($seccion), $seccion);
            $this->crearNoticia($categoria, $autor, [
                'titulo' => 'Publicada en ' . $seccion,
                'slug' => 'publicada-en-' . $seccion,
            ]);
        }

        foreach (self::SECCIONES as $seccion) {
            $this->get(route('seccion.' . $seccion))
                ->assertOk()
                ->assertSee('Publicada en ' . $seccion);
        }
    }

    public function test_sobre_el_campus_responde_y_muestra_la_presentacion(): void
    {
        $this->get(route('sobre-el-campus'))
            ->assertOk()
            ->assertSee('¿Qué es El Campus?')
            ->assertSee('Propósito')
            ->assertSee('Equipo editorial');
    }

    public function test_una_seccion_muestra_el_titulo_y_la_descripcion_de_su_categoria(): void
    {
        $categoria = $this->crearCategoria('Deportes', 'deportes', [
            'descripcion' => 'Actividad deportiva, competencias y vida saludable universitaria.',
        ]);
        $autor = $this->crearAutor();
        $this->crearNoticia($categoria, $autor, [
            'titulo' => 'Cronica de la final universitaria',
            'slug' => 'cronica-de-la-final-universitaria',
        ]);

        $this->get(route('seccion.deportes'))
            ->assertOk()
            ->assertSee('Deportes')
            ->assertSee('Actividad deportiva, competencias y vida saludable universitaria.')
            ->assertSee('Cronica de la final universitaria');
    }

    public function test_una_seccion_solo_muestra_las_noticias_publicadas_de_su_categoria(): void
    {
        $universidad = $this->crearCategoria('Universidad', 'universidad');
        $deportes = $this->crearCategoria('Deportes', 'deportes');
        $autor = $this->crearAutor();

        $this->crearNoticia($universidad, $autor, [
            'titulo' => 'Publicada visible', 'slug' => 'publicada-visible',
        ]);
        $this->crearNoticia($universidad, $autor, [
            'titulo' => 'Borrador oculto', 'slug' => 'borrador-oculto', 'estado' => 'borrador',
        ]);
        $this->crearNoticia($universidad, $autor, [
            'titulo' => 'Archivada oculta', 'slug' => 'archivada-oculta', 'estado' => 'archivada',
        ]);
        $this->crearNoticia($deportes, $autor, [
            'titulo' => 'De otra seccion', 'slug' => 'de-otra-seccion',
        ]);

        $this->get(route('seccion.universidad'))
            ->assertOk()
            ->assertSee('Publicada visible')
            ->assertDontSee('Borrador oculto')
            ->assertDontSee('Archivada oculta')
            ->assertDontSee('De otra seccion');
    }

    public function test_una_seccion_ordena_por_fecha_de_publicacion_descendente(): void
    {
        $categoria = $this->crearCategoria('Cultura', 'cultura');
        $autor = $this->crearAutor();

        $this->crearNoticia($categoria, $autor, [
            'titulo' => 'Publicacion antigua', 'slug' => 'publicacion-antigua',
            'fecha_publicacion' => now()->subDays(5)->format('Y-m-d H:i:s'),
        ]);
        $this->crearNoticia($categoria, $autor, [
            'titulo' => 'Publicacion reciente', 'slug' => 'publicacion-reciente',
            'fecha_publicacion' => now()->format('Y-m-d H:i:s'),
        ]);

        $this->get(route('seccion.cultura'))
            ->assertOk()
            ->assertSeeInOrder(['Publicacion reciente', 'Publicacion antigua']);
    }

    public function test_una_seccion_sin_noticias_publicadas_muestra_estado_vacio(): void
    {
        $this->crearCategoria('Opinión', 'opinion');

        $this->get(route('seccion.opinion'))
            ->assertOk()
            ->assertSee('Todavía no hay noticias publicadas en esta sección.');
    }

    public function test_una_categoria_inactiva_devuelve_404(): void
    {
        $this->crearCategoria('Universidad', 'universidad', ['activa' => false]);

        $this->get(route('seccion.universidad'))
            ->assertNotFound();
    }

    public function test_una_seccion_inexistente_devuelve_404(): void
    {
        $this->get('/tecnologia')
            ->assertNotFound();
    }

    public function test_la_navegacion_contiene_los_enlaces_de_las_secciones_y_el_indicador_activo(): void
    {
        $categoria = $this->crearCategoria('Universidad', 'universidad');
        $autor = $this->crearAutor();
        $this->crearNoticia($categoria, $autor);

        $this->get(route('inicio'))
            ->assertOk()
            ->assertSee(route('seccion.universidad'))
            ->assertSee(route('seccion.investigacion'))
            ->assertSee(route('seccion.cultura'))
            ->assertSee(route('seccion.deportes'))
            ->assertSee(route('seccion.opinion'))
            ->assertSee(route('sobre-el-campus'))
            ->assertSee(route('eventos.publica'))
            ->assertSee(route('buscar'));

        $this->get(route('seccion.universidad'))
            ->assertOk()
            ->assertSee('aria-current="page"', false);
    }
}