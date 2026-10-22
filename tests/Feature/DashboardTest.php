<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Noticia;
use App\Models\User;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
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

    private function usuario(bool $verificado = true): User
    {
        return User::factory()->admin()->create([
            'email_verified_at' => $verificado ? now() : null,
        ]);
    }

    private function crearNoticia(array $sobreescribe = []): Noticia
    {
        $estado = $sobreescribe['estado'] ?? 'publicada';
        unset($sobreescribe['estado']);

        return Noticia::create(array_merge([
            'titulo' => 'Noticia de demostración',
            'slug' => 'noticia-de-demostracion',
            'categoria_id' => $this->categoria->id,
            'autor_id' => $this->autor->id,
            'contenido' => 'Contenido de demostración.',
            'fecha_publicacion' => now()->format('Y-m-d H:i:s'),
            'estado_id' => $this->idEstadoNoticia($estado),
        ], $sobreescribe));
    }

    private function crearEvento(array $sobreescribe = []): Evento
    {
        $estado = $sobreescribe['estado'] ?? 'programado';
        unset($sobreescribe['estado']);

        return Evento::create(array_merge([
            'titulo' => 'Evento de demostración',
            'slug' => 'evento-de-demostracion',
            'descripcion' => 'Descripción del evento.',
            'lugar' => 'Campus Central',
            'fecha_inicio' => now()->addDays(10),
            'estado_id' => $this->idEstadoEvento($estado),
        ], $sobreescribe));
    }

    /* -------------------------------- Acceso -------------------------------- */

    public function test_el_visitante_no_autenticado_es_redirigido_al_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_un_usuario_no_verificado_es_remitido_a_la_verificacion(): void
    {
        $this->actingAs($this->usuario(verificado: false));

        $this->get(route('dashboard'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_un_usuario_autenticado_y_verificado_puede_ver_el_panel(): void
    {
        $this->actingAs($this->usuario());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Panel administrativo')
            ->assertSee('Gestiona el contenido de El Campus');
    }

    /* ----------------------------- Estadísticas ----------------------------- */

    public function test_el_panel_muestra_el_total_y_los_estados_de_las_noticias(): void
    {
        $this->crearNoticia(['estado' => 'publicada', 'titulo' => 'Publicada uno', 'slug' => 'publicada-uno']);
        $this->crearNoticia(['estado' => 'publicada', 'titulo' => 'Publicada dos', 'slug' => 'publicada-dos']);
        $this->crearNoticia(['estado' => 'borrador', 'titulo' => 'Borrador uno', 'slug' => 'borrador-uno']);
        $this->crearNoticia(['estado' => 'borrador', 'titulo' => 'Borrador dos', 'slug' => 'borrador-dos']);
        $this->crearNoticia(['estado' => 'borrador', 'titulo' => 'Borrador tres', 'slug' => 'borrador-tres']);
        $this->crearNoticia(['estado' => 'archivada', 'titulo' => 'Archivada uno', 'slug' => 'archivada-uno']);

        $this->actingAs($this->usuario());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Total de Noticias: 6', false)
            ->assertSee('Publicadas: 2', false)
            ->assertSee('En borrador: 3', false)
            ->assertSee('Archivadas: 1', false);
    }

    public function test_el_panel_muestra_la_cantidad_de_autores(): void
    {
        for ($i = 2; $i <= 4; $i++) {
            Autor::create([
                'nombre' => "Autor de prueba $i",
                'slug' => "autor-de-prueba-$i",
            ]);
        }

        $this->actingAs($this->usuario());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Total de Autores: 4', false);
    }

    public function test_el_panel_muestra_la_cantidad_de_categorias(): void
    {
        $this->seed(CategoriaSeeder::class);

        $this->actingAs($this->usuario());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Total de Categorías: 6', false);
    }

    public function test_el_panel_muestra_el_total_y_los_estados_de_los_eventos(): void
    {
        $this->crearEvento(['titulo' => 'Evento programado uno', 'slug' => 'evento-programado-uno']);
        $this->crearEvento(['titulo' => 'Evento programado dos', 'slug' => 'evento-programado-dos']);
        $this->crearEvento(['titulo' => 'Evento realizado uno', 'slug' => 'evento-realizado-uno', 'estado' => 'realizado']);
        $this->crearEvento(['titulo' => 'Evento cancelado uno', 'slug' => 'evento-cancelado-uno', 'estado' => 'cancelado']);

        $this->actingAs($this->usuario());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Total de Eventos: 4', false)
            ->assertSee('Programados: 2', false)
            ->assertSee('Realizados: 1', false)
            ->assertSee('Cancelados: 1', false);
    }

    /* ---------------------------- Actividad reciente ---------------------------- */

    public function test_el_panel_muestra_las_noticias_recientes(): void
    {
        $this->crearNoticia([
            'titulo' => 'Noticia antigua',
            'slug' => 'noticia-antigua',
            'fecha_publicacion' => now()->subDays(10)->format('Y-m-d H:i:s'),
        ]);
        $this->crearNoticia([
            'titulo' => 'Noticia reciente',
            'slug' => 'noticia-reciente',
            'fecha_publicacion' => now()->format('Y-m-d H:i:s'),
        ]);

        $this->actingAs($this->usuario());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['Noticia reciente', 'Noticia antigua']);
    }

    public function test_el_panel_muestra_los_proximos_eventos_ordenados(): void
    {
        $this->crearEvento([
            'titulo' => 'Evento lejano',
            'slug' => 'evento-lejano',
            'fecha_inicio' => now()->addDays(20),
        ]);
        $this->crearEvento([
            'titulo' => 'Evento cercano',
            'slug' => 'evento-cercano',
            'fecha_inicio' => now()->addDay(),
        ]);

        $this->actingAs($this->usuario());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['Evento cercano', 'Evento lejano']);
    }

    /* ----------------------------- Enlaces y accesos ----------------------------- */

    public function test_los_enlaces_principales_apuntan_a_las_rutas_reales(): void
    {
        $this->actingAs($this->usuario());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('noticias.index'))
            ->assertSee(route('autores.index'))
            ->assertSee(route('eventos.index'))
            ->assertSee(route('inicio'))
            ->assertSee('Gestionar noticias')
            ->assertSee('Gestionar autores')
            ->assertSee('Gestionar eventos')
            ->assertSee('Ver portada');
    }

    /* -------------------------------- Integridad -------------------------------- */

    public function test_el_panel_no_rompe_las_rutas_publicas(): void
    {
        $this->get(route('inicio'))
            ->assertOk();

        $this->get(route('eventos.publica'))
            ->assertOk();
    }

    public function test_el_panel_no_rompe_los_crud_existentes(): void
    {
        $this->actingAs($this->usuario());

        $this->get(route('noticias.index'))->assertOk();
        $this->get(route('autores.index'))->assertOk();
        $this->get(route('eventos.index'))->assertOk();
        $this->get(route('perfil'))->assertOk();
    }
}