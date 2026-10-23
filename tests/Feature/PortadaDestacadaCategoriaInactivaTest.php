<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Noticia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortadaDestacadaCategoriaInactivaTest extends TestCase
{
    use RefreshDatabase;

    public function test_una_destacada_de_una_categoria_inactiva_sigue_siendo_la_principal(): void
    {
        $inactiva = Categoria::create([
            'nombre' => 'Opinion',
            'slug' => 'opinion',
            'activa' => false,
        ]);

        $activa = Categoria::create([
            'nombre' => 'Universidad',
            'slug' => 'universidad',
        ]);

        $autor = Autor::create([
            'nombre' => 'Redaccion El Campus',
            'slug' => 'redaccion-el-campus',
        ]);

        $destacada = Noticia::create([
            'titulo' => 'Destacada de una seccion desactivada',
            'slug' => 'destacada-de-una-seccion-desactivada',
            'categoria_id' => $inactiva->id,
            'autor_id' => $autor->id,
            'contenido' => 'Contenido de la destacada.',
            'fecha_publicacion' => now()->subDay(),
            'estado_id' => $this->idEstadoNoticia('publicada'),
            'destacada' => true,
        ]);

        Noticia::create([
            'titulo' => 'Noticia reciente de una seccion activa',
            'slug' => 'noticia-reciente-de-una-seccion-activa',
            'categoria_id' => $activa->id,
            'autor_id' => $autor->id,
            'contenido' => 'Contenido de la noticia reciente.',
            'fecha_publicacion' => now(),
            'estado_id' => $this->idEstadoNoticia('publicada'),
        ]);

        $respuesta = $this->get(route('inicio'));

        $respuesta->assertOk()
            ->assertSee('Destacada de una seccion desactivada')
            ->assertSee('Noticia reciente de una seccion activa');

        $this->assertSame($destacada->id, $respuesta->viewData('principal')->id);
    }
}
