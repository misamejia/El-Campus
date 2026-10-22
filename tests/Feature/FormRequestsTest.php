<?php

namespace Tests\Feature;

use App\Http\Requests\StoreAutorRequest;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\StoreEventoRequest;
use App\Http\Requests\StoreNoticiaRequest;
use App\Http\Requests\UpdateAutorRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Http\Requests\UpdateEventoRequest;
use App\Http\Requests\UpdateNoticiaRequest;
use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Noticia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FormRequestsTest extends TestCase
{
    use RefreshDatabase;

    private Categoria $categoria;

    private Autor $autor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoria = Categoria::create(['nombre' => 'Universidad', 'slug' => 'universidad']);
        $this->autor = Autor::create(['nombre' => 'Redacción El Campus', 'slug' => 'redaccion-el-campus']);
    }

    public function test_los_forms_requests_autorizan_sin_restricciones(): void
    {
        $requests = [
            StoreNoticiaRequest::class,
            UpdateNoticiaRequest::class,
            StoreEventoRequest::class,
            UpdateEventoRequest::class,
            StoreCategoriaRequest::class,
            UpdateCategoriaRequest::class,
            StoreAutorRequest::class,
            UpdateAutorRequest::class,
        ];

        foreach ($requests as $request) {
            $this->assertTrue((new $request)->authorize());
        }
    }

    public function test_la_noticia_exige_titulo_contenido_categoria_autor_y_estado(): void
    {
        $this->actingAs($this->administrador());

        $this->post(route('noticias.store'), [])
            ->assertSessionHasErrors(['titulo', 'contenido', 'categoria_id', 'autor_id', 'estado']);
    }

    public function test_el_texto_alternativo_es_obligatorio_cuando_se_sube_imagen(): void
    {
        $this->actingAs($this->administrador());

        $respuesta = $this->post(route('noticias.store'), $this->datosNoticia([
            'imagen' => UploadedFile::fake()->create('noticia.jpg', 20, 'image/jpeg'),
        ]));

        $respuesta->assertSessionHasErrors('imagen_alt');
        $this->assertSame(
            'El texto alternativo es obligatorio cuando la noticia tiene una imagen.',
            session('errors')->first('imagen_alt')
        );
    }

    public function test_el_texto_alternativo_no_es_obligatorio_sin_imagen(): void
    {
        $this->actingAs($this->administrador());

        $this->post(route('noticias.store'), $this->datosNoticia())
            ->assertSessionHasNoErrors();
    }

    public function test_el_slug_de_la_noticia_no_se_puede_duplicar(): void
    {
        $this->actingAs($this->administrador());

        $this->crearNoticia('slug-ocupado');
        $otra = $this->crearNoticia('slug-propio');

        $this->put(route('noticias.update', $otra->id), $this->datosNoticia([
            'slug' => 'slug-ocupado',
            'estado' => 'borrador',
        ]))->assertSessionHasErrors('slug');
    }

    public function test_la_noticia_puede_conservar_su_propio_slug(): void
    {
        $this->actingAs($this->administrador());

        $noticia = $this->crearNoticia('slug-propio');

        $this->put(route('noticias.update', $noticia->id), $this->datosNoticia([
            'slug' => 'slug-propio',
            'estado' => 'borrador',
        ]))->assertSessionHasNoErrors();
    }

    public function test_el_evento_exige_lugar_descripcion_y_fechas_validas(): void
    {
        $this->actingAs($this->administrador());

        $this->post(route('eventos.store'), [])
            ->assertSessionHasErrors(['titulo', 'descripcion', 'lugar', 'fecha_inicio', 'estado']);

        $this->post(route('eventos.store'), $this->datosEvento([
            'fecha_fin' => now()->subDay()->toDateString(),
        ]))->assertSessionHasErrors('fecha_fin');
    }

    public function test_el_slug_del_evento_no_se_puede_duplicar_pero_puede_conservar_el_suyo(): void
    {
        $this->actingAs($this->administrador());

        $this->crearEvento('evento-ocupado');
        $otro = $this->crearEvento('evento-propio');

        $this->put(route('eventos.update', $otro->id), $this->datosEvento([
            'slug' => 'evento-ocupado',
        ]))->assertSessionHasErrors('slug');

        $this->put(route('eventos.update', $otro->id), $this->datosEvento([
            'slug' => 'evento-propio',
            'titulo' => 'Evento renombrado',
        ]))->assertSessionHasNoErrors();
    }

    public function test_la_categoria_exige_nombre_y_la_categoria_ignora_su_propio_slug(): void
    {
        $this->actingAs($this->administrador());

        $this->post(route('categorias.store'), [])
            ->assertSessionHasErrors('nombre');

        $this->put(route('categorias.update', $this->categoria->id), [
            'nombre' => 'Universidad',
            'slug' => 'universidad',
        ])->assertSessionHasNoErrors();
    }

    public function test_el_autor_exige_nombre_y_valida_el_correo(): void
    {
        $this->actingAs($this->administrador());

        $this->post(route('autores.store'), [])
            ->assertSessionHasErrors('nombre');

        $this->post(route('autores.store'), [
            'nombre' => 'Autor sin correo',
            'email' => 'correo-invalido',
        ])->assertSessionHasErrors('email');
    }

    public function test_la_noticia_rechaza_archivo_no_imagen_supera_tamano_o_svg(): void
    {
        $this->actingAs($this->administrador());

        $this->post(route('noticias.store'), $this->datosNoticia([
            'imagen' => UploadedFile::fake()->create('documento.pdf', 10, 'application/pdf'),
            'imagen_alt' => 'Texto alternativo',
        ]))->assertSessionHasErrors('imagen');

        $this->post(route('noticias.store'), $this->datosNoticia([
            'imagen' => UploadedFile::fake()->create('grande.jpg', 3000, 'image/jpeg'),
            'imagen_alt' => 'Texto alternativo',
        ]))->assertSessionHasErrors('imagen');

        $this->post(route('noticias.store'), $this->datosNoticia([
            'imagen' => UploadedFile::fake()->create('icono.svg', 5, 'image/svg+xml'),
            'imagen_alt' => 'Texto alternativo',
        ]))->assertSessionHasErrors('imagen');
    }

    public function test_el_evento_rechaza_archivo_no_imagen_supera_tamano_o_svg(): void
    {
        $this->actingAs($this->administrador());

        $this->post(route('eventos.store'), $this->datosEvento([
            'imagen' => UploadedFile::fake()->create('documento.pdf', 10, 'application/pdf'),
            'imagen_alt' => 'Texto alternativo',
        ]))->assertSessionHasErrors('imagen');

        $this->post(route('eventos.store'), $this->datosEvento([
            'imagen' => UploadedFile::fake()->create('grande.jpg', 3000, 'image/jpeg'),
            'imagen_alt' => 'Texto alternativo',
        ]))->assertSessionHasErrors('imagen');

        $this->post(route('eventos.store'), $this->datosEvento([
            'imagen' => UploadedFile::fake()->create('icono.svg', 5, 'image/svg+xml'),
            'imagen_alt' => 'Texto alternativo',
        ]))->assertSessionHasErrors('imagen');
    }

    public function test_en_eventos_una_ruta_de_texto_en_imagen_se_acepta_con_imagen_alt(): void
    {
        $this->actingAs($this->administrador());

        $this->post(route('eventos.store'), $this->datosEvento([
            'imagen' => '/imagenes/eventos/demo.jpg',
            'imagen_alt' => 'Imagen de demostracion',
        ]))->assertSessionHasNoErrors();
    }

    private function administrador(): User
    {
        return User::factory()->admin()->create(['email_verified_at' => now()]);
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function datosNoticia(array $extra = []): array
    {
        return array_merge([
            'titulo' => 'Noticia de prueba',
            'categoria_id' => $this->categoria->id,
            'autor_id' => $this->autor->id,
            'contenido' => 'Contenido de la noticia de prueba.',
            'estado' => 'publicada',
        ], $extra);
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function datosEvento(array $extra = []): array
    {
        return array_merge([
            'titulo' => 'Evento de prueba',
            'descripcion' => 'Descripción del evento de prueba.',
            'lugar' => 'Auditorio central',
            'fecha_inicio' => now()->addDays(3)->toDateString(),
            'estado' => 'programado',
        ], $extra);
    }

    private function crearNoticia(string $slug): Noticia
    {
        return Noticia::create([
            'titulo' => 'Noticia existente',
            'slug' => $slug,
            'categoria_id' => $this->categoria->id,
            'autor_id' => $this->autor->id,
            'estado_id' => $this->idEstado('estados_noticia', 'publicada'),
            'contenido' => 'Contenido de demostración.',
        ]);
    }

    private function crearEvento(string $slug): Evento
    {
        return Evento::create([
            'titulo' => 'Evento existente',
            'slug' => $slug,
            'estado_id' => $this->idEstado('estados_evento', 'programado'),
            'descripcion' => 'Descripción del evento.',
            'lugar' => 'Auditorio central',
            'fecha_inicio' => now()->addDays(2),
        ]);
    }

    private function idEstado(string $tabla, string $codigo): int
    {
        return (int) DB::table($tabla)->where('codigo', $codigo)->value('id');
    }
}
