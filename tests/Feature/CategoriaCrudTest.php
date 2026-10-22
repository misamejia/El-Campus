<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Noticia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaCrudTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(): User
    {
        return User::factory()->admin()->create([
            'email_verified_at' => now(),
        ]);
    }

    private function editor(): User
    {
        return User::factory()->editor()->create([
            'email_verified_at' => now(),
        ]);
    }

    private function datosCategoria(array $sobreescribe = []): array
    {
        return array_merge([
            'nombre' => 'Universidad',
            'slug' => 'universidad',
            'descripcion' => 'Vida académica y administrativa de la universidad.',
            'activa' => '1',
        ], $sobreescribe);
    }

    private function crearNoticiaPara(Categoria $categoria, ?Autor $autor = null, string $slug = 'noticia-de-prueba-para-la-restriccion'): Noticia
    {
        $autor ??= Autor::create([
            'nombre' => 'Redacción El Campus',
            'slug' => 'redaccion-el-campus',
        ]);

        return Noticia::create([
            'titulo' => ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'categoria_id' => $categoria->id,
            'autor_id' => $autor->id,
            'contenido' => 'Contenido de demostración.',
            'fecha_publicacion' => now()->format('Y-m-d H:i:s'),
            'estado_id' => $this->idEstadoNoticia('publicada'),
        ]);
    }

    public function test_el_visitante_no_autenticado_es_redirigido_al_login(): void
    {
        $this->get(route('categorias.index'))
            ->assertRedirect(route('login'));
    }

    public function test_el_usuario_autenticado_puede_ver_el_listado(): void
    {
        $this->actingAs($this->usuario());

        $this->get(route('categorias.index'))
            ->assertOk()
            ->assertSee('Categorías');
    }

    public function test_el_editor_recibe_403_al_acceder_a_categorias(): void
    {
        $this->actingAs($this->editor());

        $this->get(route('categorias.index'))
            ->assertForbidden();
    }

    public function test_el_usuario_autenticado_puede_crear_una_categoria(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('categorias.store'), $this->datosCategoria())
            ->assertRedirect(route('categorias.index'))
            ->assertSessionHas('exito', 'Categoría creada correctamente.');

        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Universidad',
            'slug' => 'universidad',
            'activa' => true,
        ]);
    }

    public function test_una_categoria_invalida_no_se_almacena(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('categorias.store'), [
            'nombre' => '',
        ])->assertSessionHasErrors('nombre');

        $this->assertDatabaseCount('categorias', 0);
    }

    public function test_el_slug_debe_ser_unico(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('categorias.store'), $this->datosCategoria());
        $this->post(route('categorias.store'), $this->datosCategoria([
            'nombre' => 'Otra universidad',
            'slug' => 'universidad',
        ]))->assertSessionHasErrors('slug');

        $this->assertSame(1, Categoria::count());
    }

    public function test_el_slug_se_genera_automaticamente_si_se_deja_vacio(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('categorias.store'), $this->datosCategoria(['slug' => '']))
            ->assertSessionHas('exito', 'Categoría creada correctamente.');

        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Universidad',
            'slug' => 'universidad',
        ]);
    }

    public function test_la_descripcion_es_opcional(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('categorias.store'), $this->datosCategoria(['descripcion' => '']))
            ->assertSessionHas('exito', 'Categoría creada correctamente.');

        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Universidad',
            'descripcion' => null,
        ]);
    }

    public function test_una_categoria_existente_puede_visualizarse(): void
    {
        $this->actingAs($this->usuario());

        $categoria = Categoria::create($this->datosCategoria());

        $this->get(route('categorias.show', $categoria))
            ->assertOk()
            ->assertSee($categoria->nombre)
            ->assertSee($categoria->slug);
    }

    public function test_una_categoria_puede_actualizarse(): void
    {
        $this->actingAs($this->usuario());

        $categoria = Categoria::create($this->datosCategoria());

        $this->put(route('categorias.update', $categoria), [
            'nombre' => 'Investigación',
            'descripcion' => 'Producción científica del campus.',
            'activa' => '0',
        ])->assertRedirect(route('categorias.index'))
            ->assertSessionHas('exito', 'Categoría actualizada correctamente.');

        $this->assertDatabaseHas('categorias', [
            'id' => $categoria->id,
            'nombre' => 'Investigación',
            'slug' => 'investigacion',
            'activa' => false,
        ]);
    }

    public function test_una_categoria_sin_noticias_puede_eliminarse(): void
    {
        $this->actingAs($this->usuario());

        $categoria = Categoria::create($this->datosCategoria());

        $this->delete(route('categorias.destroy', $categoria))
            ->assertRedirect(route('categorias.index'))
            ->assertSessionHas('exito', 'Categoría eliminada correctamente.');

        $this->assertDatabaseMissing('categorias', ['id' => $categoria->id]);
    }

    public function test_una_categoria_con_noticias_no_puede_eliminarse(): void
    {
        $this->actingAs($this->usuario());

        $categoria = Categoria::create($this->datosCategoria());
        $this->crearNoticiaPara($categoria);

        $this->delete(route('categorias.destroy', $categoria))
            ->assertRedirect(route('categorias.index'))
            ->assertSessionHas('error', 'No se puede eliminar esta categoría porque tiene noticias asociadas.');

        $this->assertDatabaseHas('categorias', ['id' => $categoria->id]);
        $this->assertSame(1, Noticia::count());
    }

    public function test_la_categoria_tiene_muchas_noticias(): void
    {
        $categoria = Categoria::create($this->datosCategoria());
        $autor = Autor::create([
            'nombre' => 'Redacción El Campus',
            'slug' => 'redaccion-el-campus',
        ]);
        $this->crearNoticiaPara($categoria, $autor);
        $this->crearNoticiaPara($categoria, $autor, 'noticia-dos');

        $this->assertCount(2, $categoria->noticias);
    }

    public function test_la_noticia_pertenece_a_la_categoria(): void
    {
        $categoria = Categoria::create($this->datosCategoria());
        $noticia = $this->crearNoticiaPara($categoria);

        $this->assertInstanceOf(Categoria::class, $noticia->categoria);
        $this->assertEquals($categoria->id, $noticia->categoria->id);
    }

    public function test_la_seccion_publica_sigue_respondiendo_por_su_controlador(): void
    {
        Categoria::create($this->datosCategoria());
        $this->crearNoticiaPara(Categoria::where('slug', 'universidad')->first());

        $this->get(route('seccion.universidad'))
            ->assertOk();
    }
}