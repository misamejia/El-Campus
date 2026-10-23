<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Noticia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginacionListadosTest extends TestCase
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

    private function administrador(): User
    {
        return User::factory()->admin()->create([
            'name' => 'Cuenta raiz',
            'email_verified_at' => now(),
        ]);
    }

    public function test_el_listado_de_noticias_pagina_de_quince_en_quince(): void
    {
        for ($i = 1; $i <= 16; $i++) {
            Noticia::create([
                'titulo' => sprintf('Noticia %02d', $i),
                'slug' => sprintf('noticia-%02d', $i),
                'categoria_id' => $this->categoria->id,
                'autor_id' => $this->autor->id,
                'contenido' => 'Contenido de la noticia.',
                'fecha_publicacion' => now()->subMinutes(16 - $i),
                'estado_id' => $this->idEstadoNoticia('publicada'),
            ]);
        }

        $this->actingAs($this->administrador());

        $primera = $this->get(route('noticias.index'));

        $primera->assertOk()
            ->assertSee('Noticia 16')
            ->assertDontSee('Noticia 01');

        $this->assertSame(15, $primera->viewData('noticias')->count());
        $this->assertSame(16, $primera->viewData('noticias')->total());

        $segunda = $this->get(route('noticias.index', ['page' => 2]));

        $segunda->assertOk()
            ->assertSee('Noticia 01')
            ->assertDontSee('Noticia 16');

        $this->assertSame(1, $segunda->viewData('noticias')->count());
    }

    public function test_el_listado_de_eventos_pagina_de_quince_en_quince(): void
    {
        for ($i = 1; $i <= 16; $i++) {
            Evento::create([
                'titulo' => sprintf('Evento %02d', $i),
                'slug' => sprintf('evento-%02d', $i),
                'descripcion' => 'Descripcion del evento.',
                'lugar' => 'Auditorio',
                'fecha_inicio' => now()->addDays($i),
                'estado_id' => $this->idEstadoEvento('programado'),
            ]);
        }

        $this->actingAs($this->administrador());

        $primera = $this->get(route('eventos.index'));

        $primera->assertOk()
            ->assertSee('Evento 01')
            ->assertDontSee('Evento 16');

        $this->assertSame(15, $primera->viewData('eventos')->count());
        $this->assertSame(16, $primera->viewData('eventos')->total());

        $segunda = $this->get(route('eventos.index', ['page' => 2]));

        $segunda->assertOk()
            ->assertSee('Evento 16')
            ->assertDontSee('Evento 01');

        $this->assertSame(1, $segunda->viewData('eventos')->count());
    }

    public function test_el_listado_de_usuarios_pagina_de_quince_en_quince(): void
    {
        $administrador = $this->administrador();

        for ($i = 1; $i <= 15; $i++) {
            User::factory()->editor()->create([
                'name' => sprintf('Usuario %02d', $i),
                'email' => sprintf('usuario%02d@elcampus.test', $i),
                'email_verified_at' => now(),
            ]);
        }

        $this->actingAs($administrador);

        $primera = $this->get(route('usuarios.index'));

        $primera->assertOk()
            ->assertSee('usuario14@elcampus.test')
            ->assertDontSee('usuario15@elcampus.test');

        $this->assertSame(15, $primera->viewData('usuarios')->count());
        $this->assertSame(16, $primera->viewData('usuarios')->total());

        $segunda = $this->get(route('usuarios.index', ['page' => 2]));

        $segunda->assertOk()
            ->assertSee('usuario15@elcampus.test')
            ->assertDontSee('usuario14@elcampus.test');

        $this->assertSame(1, $segunda->viewData('usuarios')->count());
    }
}
