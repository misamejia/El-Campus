<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Noticia;
use App\Services\Noticias\Relacionadas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelacionadasLimiteTest extends TestCase
{
    use RefreshDatabase;

    private Categoria $categoria;

    private Autor $autor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoria = Categoria::create([
            'nombre' => 'Universidad',
            'slug' => 'universidad',
        ]);

        $this->autor = Autor::create([
            'nombre' => 'Redaccion El Campus',
            'slug' => 'redaccion-el-campus',
        ]);
    }

    private function crearNoticia(string $titulo, array $sobreescribe = []): Noticia
    {
        return Noticia::create(array_merge([
            'titulo' => $titulo,
            'slug' => str($titulo)->slug()->value(),
            'categoria_id' => $this->categoria->id,
            'autor_id' => $this->autor->id,
            'contenido' => 'Contenido de la noticia.',
            'fecha_publicacion' => now()->subMinutes(random_int(1, 10000)),
            'estado_id' => $this->idEstadoNoticia('publicada'),
        ], $sobreescribe));
    }

    private function conEmbedding(array $vector): array
    {
        return [
            'embedding' => $vector,
            'embedding_actualizado_en' => now(),
        ];
    }

    public function test_con_mas_de_cien_candidatas_devuelve_el_limite_y_excluye_la_actual(): void
    {
        $vector = [1.0, 0.0, 0.0, 0.0];

        $principal = $this->crearNoticia('Noticia principal', $this->conEmbedding($vector));

        for ($i = 1; $i <= 120; $i++) {
            $this->crearNoticia(sprintf('Candidata %03d', $i), $this->conEmbedding($vector));
        }

        $relacionadas = app(Relacionadas::class)->para($principal, 5);

        $this->assertCount(5, $relacionadas);
        $this->assertFalse($relacionadas->contains('id', $principal->id));
        $this->assertTrue(Noticia::count() > 100);
    }

    public function test_sin_embedding_cae_a_la_categoria_y_tambien_excluye_la_actual(): void
    {
        $principal = $this->crearNoticia('Noticia principal sin embedding');

        for ($i = 1; $i <= 120; $i++) {
            $this->crearNoticia(sprintf('Otra noticia %03d', $i));
        }

        $relacionadas = app(Relacionadas::class)->para($principal, 3);

        $this->assertCount(3, $relacionadas);
        $this->assertFalse($relacionadas->contains('id', $principal->id));
    }

    public function test_solo_se_puntuan_las_cien_candidatas_mas_recientes(): void
    {
        $vector = [1.0, 0.0, 0.0, 0.0];
        $distinto = [0.0, 1.0, 0.0, 0.0];

        $principal = $this->crearNoticia('Noticia principal', $this->conEmbedding($vector));

        $antiguas = collect();

        for ($i = 1; $i <= 20; $i++) {
            $antiguas->push($this->crearNoticia(sprintf('Antigua %03d', $i), $this->conEmbedding($vector) + [
                'fecha_publicacion' => now()->subDays(30)->subMinutes($i),
            ]));
        }

        for ($i = 1; $i <= 100; $i++) {
            $this->crearNoticia(sprintf('Reciente %03d', $i), $this->conEmbedding($distinto) + [
                'fecha_publicacion' => now()->subMinutes($i),
            ]);
        }

        $relacionadas = app(Relacionadas::class)->para($principal, 20);

        $this->assertCount(20, $relacionadas);
        $this->assertSame([], $relacionadas->pluck('id')->intersect($antiguas->pluck('id'))->all());
    }

    public function test_la_candidata_actual_nunca_se_incluye_aunque_sea_la_mas_similar(): void
    {
        $vector = [1.0, 0.0, 0.0, 0.0];

        $principal = $this->crearNoticia('Noticia principal', $this->conEmbedding($vector));

        $this->crearNoticia('Noticia identica', $this->conEmbedding($vector));

        $relacionadas = app(Relacionadas::class)->para($principal, 10);

        $this->assertCount(1, $relacionadas);
        $this->assertSame('Noticia identica', $relacionadas->first()->titulo);
    }
}
