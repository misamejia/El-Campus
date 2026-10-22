<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Noticia;
use App\Models\User;
use Database\Seeders\AutorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutorCrudTest extends TestCase
{
    use RefreshDatabase;

    protected Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoria = Categoria::create([
            'nombre' => 'Universidad',
            'slug' => 'universidad',
        ]);
    }

    private function usuario(): User
    {
        return User::factory()->admin()->create([
            'email_verified_at' => now(),
        ]);
    }

    private function datosAutor(array $sobreescribe = []): array
    {
        return array_merge([
            'nombre' => 'Redacción El Campus',
            'slug' => 'redaccion-el-campus',
            'descripcion' => 'Equipo editorial general del periódico universitario.',
            'email' => 'redaccion@elampus.test',
            'activo' => '1',
        ], $sobreescribe);
    }

    private function crearNoticiaPara(Autor $autor, string $slug = 'noticia-de-prueba-para-la-restriccion'): Noticia
    {
        return Noticia::create([
            'titulo' => ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'categoria_id' => $this->categoria->id,
            'autor_id' => $autor->id,
            'contenido' => 'Contenido de demostración.',
            'fecha_publicacion' => now()->format('Y-m-d H:i:s'),
            'estado_id' => $this->idEstadoNoticia('publicada'),
        ]);
    }

    public function test_el_visitante_no_autenticado_es_redirigido_al_login(): void
    {
        $this->get(route('autores.index'))
            ->assertRedirect(route('login'));
    }

    public function test_el_usuario_autenticado_puede_ver_el_listado(): void
    {
        $this->actingAs($this->usuario());

        $this->get(route('autores.index'))
            ->assertOk()
            ->assertSee('Autores');
    }

    public function test_el_usuario_autenticado_puede_crear_un_autor(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('autores.store'), $this->datosAutor())
            ->assertRedirect(route('autores.index'))
            ->assertSessionHas('exito', 'Autor creado correctamente.');

        $this->assertDatabaseHas('autores', [
            'nombre' => 'Redacción El Campus',
            'slug' => 'redaccion-el-campus',
            'activo' => true,
        ]);
    }

    public function test_un_autor_invalido_no_se_almacena(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('autores.store'), [
            'nombre' => '',
            'email' => 'correo-invalido',
        ])->assertSessionHasErrors(['nombre', 'email']);

        $this->assertDatabaseCount('autores', 0);
    }

    public function test_el_slug_debe_ser_unico(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('autores.store'), $this->datosAutor());
        $this->post(route('autores.store'), $this->datosAutor([
            'nombre' => 'Otra redacción',
            'slug' => 'redaccion-el-campus',
        ]))->assertSessionHasErrors('slug');

        $this->assertSame(1, Autor::count());
    }

    public function test_el_slug_se_genera_automaticamente_si_se_deja_vacio(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('autores.store'), $this->datosAutor(['slug' => '']))
            ->assertSessionHas('exito', 'Autor creado correctamente.');

        $this->assertDatabaseHas('autores', [
            'nombre' => 'Redacción El Campus',
            'slug' => 'redaccion-el-campus',
        ]);
    }

    public function test_el_email_es_opcional(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('autores.store'), $this->datosAutor(['email' => '']))
            ->assertSessionHas('exito', 'Autor creado correctamente.');

        $this->assertDatabaseHas('autores', [
            'nombre' => 'Redacción El Campus',
            'email' => null,
        ]);
    }

    public function test_un_autor_existente_puede_visualizarse(): void
    {
        $this->actingAs($this->usuario());

        $autor = Autor::create($this->datosAutor());

        $this->get(route('autores.show', $autor))
            ->assertOk()
            ->assertSee($autor->nombre)
            ->assertSee($autor->slug);
    }

    public function test_un_autor_puede_actualizarse(): void
    {
        $this->actingAs($this->usuario());

        $autor = Autor::create($this->datosAutor());

        $this->put(route('autores.update', $autor), [
            'nombre' => 'Redacción Universidad',
            'descripcion' => 'Cobertura institucional.',
            'activo' => '0',
        ])->assertRedirect(route('autores.index'))
            ->assertSessionHas('exito', 'Autor actualizado correctamente.');

        $this->assertDatabaseHas('autores', [
            'id' => $autor->id,
            'nombre' => 'Redacción Universidad',
            'slug' => 'redaccion-universidad',
            'activo' => false,
        ]);
    }

    public function test_un_autor_sin_noticias_puede_eliminarse(): void
    {
        $this->actingAs($this->usuario());

        $autor = Autor::create($this->datosAutor());

        $this->delete(route('autores.destroy', $autor))
            ->assertRedirect(route('autores.index'))
            ->assertSessionHas('exito', 'Autor eliminado correctamente.');

        $this->assertDatabaseMissing('autores', ['id' => $autor->id]);
    }

    public function test_un_autor_con_noticias_no_puede_eliminarse(): void
    {
        $this->actingAs($this->usuario());

        $autor = Autor::create($this->datosAutor());
        $this->crearNoticiaPara($autor);

        $this->delete(route('autores.destroy', $autor))
            ->assertRedirect(route('autores.index'))
            ->assertSessionHas('error', 'No se puede eliminar este autor porque tiene noticias asociadas.');

        $this->assertDatabaseHas('autores', ['id' => $autor->id]);
        $this->assertSame(1, Noticia::count());
    }

    public function test_el_autor_tiene_muchas_noticias(): void
    {
        $autor = Autor::create($this->datosAutor());
        $this->crearNoticiaPara($autor);
        $this->crearNoticiaPara($autor, 'noticia-dos');

        $this->assertCount(2, $autor->noticias);
    }

    public function test_la_noticia_pertenece_al_autor(): void
    {
        $autor = Autor::create($this->datosAutor());
        $noticia = $this->crearNoticiaPara($autor);

        $this->assertInstanceOf(Autor::class, $noticia->autor);
        $this->assertEquals($autor->id, $noticia->autor->id);
    }

    public function test_el_seeder_de_autores_es_reproducible(): void
    {
        $this->seed(AutorSeeder::class);
        $this->seed(AutorSeeder::class);

        $this->assertSame(9, Autor::count());

        $this->assertEquals(
            [
                'redaccion-el-campus',
                'oficina-de-admisiones',
                'semillero-de-investigacion-ambiental',
                'equipo-de-investigacion',
                'seccion-cultural',
                'biblioteca-central',
                'area-deportiva',
                'coordinacion-de-eventos',
                'consejo-editorial',
            ],
            Autor::orderBy('id')->pluck('slug')->all()
        );
    }
}
