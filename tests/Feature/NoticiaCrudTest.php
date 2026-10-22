<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Noticia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class NoticiaCrudTest extends TestCase
{
    use RefreshDatabase;

    protected Categoria $categoria;

    protected Autor $autor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoria = Categoria::create([
            'nombre' => 'Universidad',
            'slug' => 'universidad',
        ]);

        $this->autor = Autor::create([
            'nombre' => 'Redacción El Campus',
            'slug' => 'redaccion-el-campus',
        ]);
    }

    private function usuario(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }

    private function datosNoticia(array $sobreescribe = []): array
    {
        return array_merge([
            'titulo' => 'Universidad de Córdoba inaugura un nuevo laboratorio',
            'slug' => 'universidad-de-cordoba-inaugura-un-nuevo-laboratorio',
            'categoria_id' => $this->categoria->id,
            'autor_id' => $this->autor->id,
            'resumen' => 'Un resumen de demostración para la prueba.',
            'contenido' => "Primer párrafo de contenido.\n\nSegundo párrafo de contenido.",
            'imagen' => null,
            'fecha_publicacion' => now()->format('Y-m-d H:i:s'),
            'estado' => 'publicada',
            'destacada' => '1',
        ], $sobreescribe);
    }

    private function crearNoticia(array $sobreescribe = []): Noticia
    {
        return Noticia::create(array_merge([
            'titulo' => 'Universidad de Córdoba inaugura un nuevo laboratorio',
            'slug' => 'universidad-de-cordoba-inaugura-un-nuevo-laboratorio',
            'categoria_id' => $this->categoria->id,
            'autor_id' => $this->autor->id,
            'resumen' => 'Un resumen de demostración para la prueba.',
            'contenido' => "Primer párrafo de contenido.\n\nSegundo párrafo de contenido.",
            'fecha_publicacion' => now(),
            'estado_id' => $this->idEstadoNoticia('publicada'),
            'destacada' => true,
        ], $sobreescribe));
    }

    public function test_el_visitante_no_autenticado_es_redirigido_al_login(): void
    {
        $this->get(route('noticias.index'))
            ->assertRedirect(route('login'));
    }

    public function test_el_usuario_autenticado_puede_ver_el_listado(): void
    {
        $this->actingAs($this->usuario());

        $this->get(route('noticias.index'))
            ->assertOk()
            ->assertSee('Noticias');
    }

    public function test_el_usuario_autenticado_puede_crear_una_noticia_con_autor(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('noticias.store'), $this->datosNoticia())
            ->assertRedirect(route('noticias.index'))
            ->assertSessionHas('exito', 'Noticia creada correctamente.');

        $this->assertDatabaseHas('noticias', [
            'titulo' => 'Universidad de Córdoba inaugura un nuevo laboratorio',
            'slug' => 'universidad-de-cordoba-inaugura-un-nuevo-laboratorio',
            'autor_id' => $this->autor->id,
        ]);
    }

    public function test_una_noticia_invalida_no_se_almacena(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('noticias.store'), [
            'titulo' => '',
            'categoria_id' => '',
            'autor_id' => '',
            'contenido' => '',
            'estado' => 'publicada',
        ])->assertSessionHasErrors(['titulo', 'categoria_id', 'autor_id', 'contenido']);

        $this->assertDatabaseCount('noticias', 0);
    }

    public function test_una_noticia_no_acepta_un_autor_inexistente(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('noticias.store'), $this->datosNoticia([
            'autor_id' => 99999,
        ]))->assertSessionHasErrors('autor_id');

        $this->assertDatabaseCount('noticias', 0);
    }

    public function test_el_estado_solo_acepta_valores_permitidos(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('noticias.store'), $this->datosNoticia([
            'estado' => 'borrada',
        ]))->assertSessionHasErrors('estado');

        $this->assertDatabaseCount('noticias', 0);
    }

    public function test_una_noticia_existente_puede_visualizarse(): void
    {
        $this->actingAs($this->usuario());

        $noticia = $this->crearNoticia();

        $this->get(route('noticias.show', $noticia))
            ->assertOk()
            ->assertSee('Universidad de Córdoba inaugura un nuevo laboratorio')
            ->assertSee($this->autor->nombre);
    }

    public function test_una_noticia_puede_actualizarse_con_su_autor(): void
    {
        $this->actingAs($this->usuario());

        $noticia = $this->crearNoticia();

        $this->put(route('noticias.update', $noticia), [
            'titulo' => 'Título actualizado de la noticia',
            'categoria_id' => $noticia->categoria_id,
            'autor_id' => $noticia->autor_id,
            'contenido' => 'Contenido nuevo de la noticia.',
            'fecha_publicacion' => now()->format('Y-m-d H:i:s'),
            'estado' => 'archivada',
        ])->assertRedirect(route('noticias.index'))
            ->assertSessionHas('exito', 'Noticia actualizada correctamente.');

        $this->assertDatabaseHas('noticias', [
            'id' => $noticia->id,
            'titulo' => 'Título actualizado de la noticia',
            'slug' => 'titulo-actualizado-de-la-noticia',
            'autor_id' => $this->autor->id,
            'estado_id' => $this->idEstadoNoticia('archivada'),
        ]);
    }

    public function test_el_autor_de_una_noticia_puede_cambiarse(): void
    {
        $this->actingAs($this->usuario());

        $otroAutor = Autor::create([
            'nombre' => 'Consejo Editorial',
            'slug' => 'consejo-editorial',
        ]);

        $noticia = $this->crearNoticia();

        $this->put(route('noticias.update', $noticia), [
            'titulo' => $noticia->titulo,
            'categoria_id' => $noticia->categoria_id,
            'autor_id' => $otroAutor->id,
            'contenido' => $noticia->contenido,
            'estado' => $noticia->estado?->codigo,
        ])->assertSessionHas('exito', 'Noticia actualizada correctamente.');

        $this->assertDatabaseHas('noticias', [
            'id' => $noticia->id,
            'autor_id' => $otroAutor->id,
        ]);
    }

    public function test_una_noticia_puede_eliminarse(): void
    {
        $this->actingAs($this->usuario());

        $noticia = $this->crearNoticia();

        $this->delete(route('noticias.destroy', $noticia))
            ->assertRedirect(route('noticias.index'))
            ->assertSessionHas('exito', 'Noticia eliminada correctamente.');

        $this->assertDatabaseMissing('noticias', ['id' => $noticia->id]);
    }

    public function test_la_noticia_pertenece_a_una_categoria(): void
    {
        $noticia = $this->crearNoticia(['categoria_id' => $this->categoria->id]);

        $this->assertInstanceOf(Categoria::class, $noticia->categoria);
        $this->assertEquals($this->categoria->id, $noticia->categoria->id);
    }

    public function test_la_noticia_pertenece_a_un_autor(): void
    {
        $noticia = $this->crearNoticia();

        $this->assertInstanceOf(Autor::class, $noticia->autor);
        $this->assertEquals($this->autor->id, $noticia->autor->id);
    }

    public function test_la_categoria_tiene_muchas_noticias(): void
    {
        $this->crearNoticia(['slug' => 'noticia-uno']);
        $this->crearNoticia(['slug' => 'noticia-dos', 'titulo' => 'Otra noticia de prueba']);

        $this->assertCount(2, $this->categoria->noticias);
    }

    public function test_el_listado_muestra_el_nombre_del_autor(): void
    {
        $this->actingAs($this->usuario());

        $this->crearNoticia();

        $this->get(route('noticias.index'))
            ->assertOk()
            ->assertSee($this->autor->nombre);
    }

    public function test_el_slug_generado_es_unico(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('noticias.store'), $this->datosNoticia());
        $this->post(route('noticias.store'), $this->datosNoticia());

        $this->assertSame(2, Noticia::count());
        $this->assertEquals(
            ['universidad-de-cordoba-inaugura-un-nuevo-laboratorio', 'universidad-de-cordoba-inaugura-un-nuevo-laboratorio-2'],
            Noticia::orderBy('id')->pluck('slug')->all()
        );
    }

    public function test_el_database_seeder_es_reproducible_y_conserva_datos(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(6, Categoria::count());
        $this->assertSame(9, Autor::count());
        $this->assertSame(11, Noticia::count());
        $this->assertSame(0, Noticia::whereNull('autor_id')->count());
        $this->assertSame(0, Noticia::whereNull('categoria_id')->count());
    }

    public function test_el_formulario_incluye_la_subida_de_imagen(): void
    {
        $this->actingAs($this->usuario());

        $this->get(route('noticias.create'))
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('type="file"', false)
            ->assertSee('name="imagen"', false);

        $noticia = $this->crearNoticia(['imagen_id' => $this->idMedio('imagenes/demo-universidad.svg')]);

        $this->get(route('noticias.edit', $noticia))
            ->assertOk()
            ->assertSee('name="quitar_imagen"', false)
            ->assertSee('Imagen actual de la noticia');
    }

    /**
     * Archivo PNG válido de 1x1 píxel construido a partir de contenido sin
     * depender de la librería GD.
     */
    private function archivoImagen(string $nombre = 'laboratorio.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $nombre,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
        );
    }

    /** Almacena una noticia con imagen subida y devuelve la noticia creada. */
    private function crearNoticiaConImagen(): Noticia
    {
        Storage::fake('public');

        $this->actingAs($this->usuario());
        $this->post(route('noticias.store'), $this->datosNoticia([
            'imagen' => $this->archivoImagen(),
            'imagen_alt' => 'Laboratorio de la Universidad de Córdoba',
        ]))->assertRedirect(route('noticias.index'));

        return Noticia::firstOrFail();
    }

    public function test_una_noticia_puede_crearse_con_una_imagen_subida(): void
    {
        $noticia = $this->crearNoticiaConImagen();

        $this->assertStringStartsWith('storage/imagenes/noticias/', $noticia->imagen);
        Storage::disk('public')->assertExists(Str::after($noticia->imagen, 'storage/'));
    }

    public function test_la_imagen_exige_texto_alternativo(): void
    {
        Storage::fake('public');
        $this->actingAs($this->usuario());

        $this->post(route('noticias.store'), $this->datosNoticia([
            'imagen' => $this->archivoImagen(),
        ]))->assertSessionHasErrors('imagen_alt');

        $this->assertDatabaseCount('noticias', 0);
    }

    public function test_el_texto_alternativo_se_guarda_en_el_medio(): void
    {
        $noticia = $this->crearNoticiaConImagen();

        $this->assertSame('Laboratorio de la Universidad de Córdoba', $noticia->medio->texto_alternativo);
        $this->assertSame('Laboratorio de la Universidad de Córdoba', $noticia->imagen_alt);
    }

    public function test_la_vista_publica_usa_el_texto_alternativo_y_el_titulo_como_respaldo(): void
    {
        $noticia = $this->crearNoticiaConImagen();

        $this->get(route('noticia.publica', $noticia->slug))
            ->assertOk()
            ->assertSee('alt="Laboratorio de la Universidad de Córdoba"', false);

        $noticia->medio->update(['texto_alternativo' => null]);

        $this->get(route('noticia.publica', $noticia->slug))
            ->assertOk()
            ->assertSee('alt="'.$noticia->titulo.'"', false);
    }

    public function test_una_noticia_no_acepta_un_archivo_que_no_es_imagen(): void
    {
        Storage::fake('public');
        $this->actingAs($this->usuario());

        $this->post(route('noticias.store'), $this->datosNoticia([
            'imagen' => UploadedFile::fake()->create('documento.txt', 10),
        ]))->assertSessionHasErrors('imagen');

        $this->assertDatabaseCount('noticias', 0);
        Storage::disk('public')->assertDirectoryEmpty('imagenes/noticias');
    }

    public function test_actualizar_la_imagen_reemplaza_y_borra_la_anterior(): void
    {
        $noticia = $this->crearNoticiaConImagen();
        $imagenAnterior = $noticia->imagen;

        $this->put(route('noticias.update', $noticia), [
            'titulo' => $noticia->titulo,
            'categoria_id' => $noticia->categoria_id,
            'autor_id' => $noticia->autor_id,
            'contenido' => $noticia->contenido,
            'estado' => $noticia->estado?->codigo,
            'imagen' => $this->archivoImagen('nueva-foto.png'),
            'imagen_alt' => 'Nueva fotografía del laboratorio',
        ])->assertRedirect(route('noticias.index'));

        $noticia->refresh();
        $this->assertNotSame($imagenAnterior, $noticia->imagen);
        $this->assertStringStartsWith('storage/imagenes/noticias/', $noticia->imagen);
        Storage::disk('public')->assertMissing(Str::after($imagenAnterior, 'storage/'));
        Storage::disk('public')->assertExists(Str::after($noticia->imagen, 'storage/'));
    }

    public function test_quitar_la_imagen_borra_el_archivo_y_la_ruta(): void
    {
        $noticia = $this->crearNoticiaConImagen();
        $imagenAnterior = $noticia->imagen;

        $this->put(route('noticias.update', $noticia), [
            'titulo' => $noticia->titulo,
            'categoria_id' => $noticia->categoria_id,
            'autor_id' => $noticia->autor_id,
            'contenido' => $noticia->contenido,
            'estado' => $noticia->estado?->codigo,
            'quitar_imagen' => '1',
        ])->assertRedirect(route('noticias.index'));

        $this->assertDatabaseHas('noticias', ['id' => $noticia->id, 'imagen_id' => null]);
        Storage::disk('public')->assertMissing(Str::after($imagenAnterior, 'storage/'));
    }

    public function test_eliminar_una_noticia_borra_su_imagen_subida(): void
    {
        $noticia = $this->crearNoticiaConImagen();

        $this->delete(route('noticias.destroy', $noticia))
            ->assertRedirect(route('noticias.index'));

        $this->assertDatabaseMissing('noticias', ['id' => $noticia->id]);
        Storage::disk('public')->assertDirectoryEmpty('imagenes/noticias');
    }
}