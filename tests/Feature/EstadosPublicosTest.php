<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\EstadoEvento;
use App\Models\EstadoNoticia;
use App\Models\Evento;
use App\Models\Noticia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstadosPublicosTest extends TestCase
{
    use RefreshDatabase;

    public function test_es_publico_de_cada_estado_de_noticia_coincide_con_las_constantes(): void
    {
        $estados = EstadoNoticia::all();

        $this->assertNotEmpty($estados);

        foreach ($estados as $estado) {
            $this->assertSame(
                (bool) $estado->es_publico,
                in_array($estado->codigo, Noticia::ESTADOS_PUBLICOS, true),
                "El estado de noticia {$estado->codigo} no coincide con Noticia::ESTADOS_PUBLICOS."
            );
        }
    }

    public function test_es_publico_de_cada_estado_de_evento_coincide_con_las_constantes(): void
    {
        $estados = EstadoEvento::all();

        $this->assertNotEmpty($estados);

        foreach ($estados as $estado) {
            $this->assertSame(
                (bool) $estado->es_publico,
                in_array($estado->codigo, Evento::ESTADOS_PUBLICOS, true),
                "El estado de evento {$estado->codigo} no coincide con Evento::ESTADOS_PUBLICOS."
            );
        }
    }

    public function test_el_scope_publicadas_solo_devuelve_noticias_publicadas(): void
    {
        [$categoria, $autor] = $this->categoriaYAutor();

        $publicada = $this->crearNoticia($categoria, $autor, 'publicada');
        $this->crearNoticia($categoria, $autor, 'borrador');
        $this->crearNoticia($categoria, $autor, 'archivada');

        $this->assertSame([$publicada->id], Noticia::publicadas()->pluck('id')->all());
    }

    public function test_el_scope_publicos_excluye_los_eventos_cancelados(): void
    {
        $programado = $this->crearEvento('programado');
        $realizado = $this->crearEvento('realizado');
        $this->crearEvento('cancelado');

        $this->assertEqualsCanonicalizing(
            [$programado->id, $realizado->id],
            Evento::publicos()->pluck('id')->all()
        );
    }

    /**
     * @return array{0: Categoria, 1: Autor}
     */
    private function categoriaYAutor(): array
    {
        return [
            Categoria::create(['nombre' => 'Universidad', 'slug' => 'universidad']),
            Autor::create(['nombre' => 'Redaccion El Campus', 'slug' => 'redaccion-el-campus']),
        ];
    }

    private function crearNoticia(Categoria $categoria, Autor $autor, string $codigo): Noticia
    {
        return Noticia::create([
            'titulo' => "Noticia {$codigo}",
            'slug' => "noticia-{$codigo}",
            'categoria_id' => $categoria->id,
            'autor_id' => $autor->id,
            'contenido' => 'Contenido de la noticia.',
            'fecha_publicacion' => now(),
            'estado_id' => $this->idEstadoNoticia($codigo),
        ]);
    }

    private function crearEvento(string $codigo): Evento
    {
        return Evento::create([
            'titulo' => "Evento {$codigo}",
            'slug' => "evento-{$codigo}",
            'descripcion' => 'Descripcion del evento.',
            'lugar' => 'Auditorio',
            'fecha_inicio' => now()->addDay(),
            'estado_id' => $this->idEstadoEvento($codigo),
        ]);
    }
}
