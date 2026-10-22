<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Medio;
use App\Models\User;
use Database\Seeders\EventoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventoTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(bool $verificado = true): User
    {
        return User::factory()->create([
            'email_verified_at' => $verificado ? now() : null,
        ]);
    }

    private function datosEvento(array $sobreescribe = []): array
    {
        return array_merge([
            'titulo' => 'Feria Universitaria de Ciencia y Tecnología',
            'descripcion' => 'Muestra de proyectos de los semilleros de investigación, con talleres y conferencias.',
            'lugar' => 'Campus Central — Ciudadela Universitaria',
            'fecha_inicio' => now()->addDays(20)->format('Y-m-d\TH:i'),
            'fecha_fin' => now()->addDays(20)->format('Y-m-d\TH:i'),
            'imagen' => '',
            'organizador' => 'Vicerrectoría de Investigación',
            'estado' => 'programado',
        ], $sobreescribe);
    }

    private function crearEvento(array $sobreescribe = []): Evento
    {
        $organizador = $sobreescribe['organizador'] ?? 'Vicerrectoría de Investigación';
        $estado = $sobreescribe['estado'] ?? 'programado';
        unset($sobreescribe['organizador'], $sobreescribe['estado'], $sobreescribe['imagen']);

        return Evento::create(array_merge([
            'titulo' => 'Feria Universitaria de Ciencia y Tecnología',
            'slug' => 'feria-universitaria-de-ciencia-y-tecnologia',
            'descripcion' => 'Muestra de proyectos de los semilleros de investigación.',
            'lugar' => 'Campus Central',
            'fecha_inicio' => now()->addDays(20),
            'fecha_fin' => null,
            'organizador_id' => $this->idOrganizador($organizador),
            'estado_id' => $this->idEstadoEvento($estado),
        ], $sobreescribe));
    }

    /* ------------------------------ Seguridad ------------------------------ */

    public function test_el_visitante_no_autenticado_es_redirigido_al_login(): void
    {
        $this->get(route('eventos.index'))
            ->assertRedirect(route('login'));

        $this->post(route('eventos.store'), $this->datosEvento())
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('eventos', 0);
    }

    public function test_un_usuario_no_verificado_no_puede_acceder_al_crud(): void
    {
        $this->actingAs($this->usuario(verificado: false));

        $this->get(route('eventos.index'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_un_usuario_autenticado_y_verificado_puede_acceder_al_crud(): void
    {
        $this->actingAs($this->usuario());

        $this->get(route('eventos.index'))
            ->assertOk()
            ->assertSee('Eventos');
    }

    /* --------------------------------- CRUD --------------------------------- */

    public function test_el_usuario_autenticado_puede_ver_el_formulario_de_creacion(): void
    {
        $this->actingAs($this->usuario());

        $this->get(route('eventos.create'))
            ->assertOk()
            ->assertSee('Crear evento');
    }

    public function test_un_usuario_autenticado_puede_crear_un_evento(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('eventos.store'), $this->datosEvento())
            ->assertRedirect(route('eventos.index'))
            ->assertSessionHas('exito', 'Evento creado correctamente.');

        $this->assertDatabaseHas('eventos', [
            'titulo' => 'Feria Universitaria de Ciencia y Tecnología',
            'slug' => 'feria-universitaria-de-ciencia-y-tecnologia',
            'lugar' => 'Campus Central — Ciudadela Universitaria',
            'estado_id' => $this->idEstadoEvento('programado'),
        ]);
    }

    public function test_un_evento_existente_puede_visualizarse(): void
    {
        $this->actingAs($this->usuario());

        $evento = $this->crearEvento();

        $this->get(route('eventos.show', $evento))
            ->assertOk()
            ->assertSee($evento->titulo)
            ->assertSee($evento->lugar)
            ->assertSee($evento->organizador->nombre);
    }

    public function test_el_formulario_de_edicion_se_muestra_con_los_datos(): void
    {
        $this->actingAs($this->usuario());

        $evento = $this->crearEvento();

        $this->get(route('eventos.edit', $evento))
            ->assertOk()
            ->assertSee('Editar evento')
            ->assertSee($evento->titulo);
    }

    public function test_un_evento_puede_actualizarse(): void
    {
        $this->actingAs($this->usuario());

        $evento = $this->crearEvento();

        $this->put(route('eventos.update', $evento), $this->datosEvento([
            'titulo' => 'Semana Cultural Universitaria',
            'estado' => 'realizado',
        ]))->assertRedirect(route('eventos.index'))
            ->assertSessionHas('exito', 'Evento actualizado correctamente.');

        $this->assertDatabaseHas('eventos', [
            'id' => $evento->id,
            'titulo' => 'Semana Cultural Universitaria',
            'slug' => 'semana-cultural-universitaria',
            'estado_id' => $this->idEstadoEvento('realizado'),
        ]);
    }

    public function test_un_evento_puede_eliminarse(): void
    {
        $this->actingAs($this->usuario());

        $evento = $this->crearEvento();

        $this->delete(route('eventos.destroy', $evento))
            ->assertRedirect(route('eventos.index'))
            ->assertSessionHas('exito', 'Evento eliminado correctamente.');

        $this->assertDatabaseMissing('eventos', ['id' => $evento->id]);
    }

    /* ------------------------------- Validación ------------------------------ */

    public function test_el_titulo_es_obligatorio(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('eventos.store'), $this->datosEvento(['titulo' => '']))
            ->assertSessionHasErrors('titulo');

        $this->assertDatabaseCount('eventos', 0);
    }

    public function test_la_descripcion_y_el_lugar_son_obligatorios(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('eventos.store'), $this->datosEvento([
            'descripcion' => '',
            'lugar' => '',
        ]))->assertSessionHasErrors(['descripcion', 'lugar']);

        $this->assertDatabaseCount('eventos', 0);
    }

    public function test_la_fecha_de_inicio_debe_ser_una_fecha_valida(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('eventos.store'), $this->datosEvento([
            'fecha_inicio' => 'no-es-una-fecha',
        ]))->assertSessionHasErrors('fecha_inicio');

        $this->assertDatabaseCount('eventos', 0);
    }

    public function test_la_fecha_de_fin_no_puede_ser_anterior_a_la_de_inicio(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('eventos.store'), $this->datosEvento([
            'fecha_inicio' => now()->addDays(10)->format('Y-m-d\TH:i'),
            'fecha_fin' => now()->addDays(5)->format('Y-m-d\TH:i'),
        ]))->assertSessionHasErrors('fecha_fin');

        $this->assertDatabaseCount('eventos', 0);
    }

    public function test_el_estado_solo_acepta_valores_permitidos(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('eventos.store'), $this->datosEvento([
            'estado' => 'inventado',
        ]))->assertSessionHasErrors('estado');

        $this->assertDatabaseCount('eventos', 0);
    }

    public function test_la_imagen_del_evento_exige_texto_alternativo(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('eventos.store'), $this->datosEvento([
            'imagen' => 'imagenes/demo-cultura.svg',
        ]))->assertSessionHasErrors('imagen_alt');

        $this->assertDatabaseCount('eventos', 0);
    }

    public function test_el_texto_alternativo_se_comparte_entre_eventos_con_la_misma_imagen(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('eventos.store'), $this->datosEvento([
            'imagen' => 'imagenes/demo-cultura.svg',
            'imagen_alt' => 'Estudiantes en una actividad cultural',
        ]))->assertRedirect(route('eventos.index'));

        $this->post(route('eventos.store'), $this->datosEvento([
            'titulo' => 'Segundo evento cultural de la universidad',
            'imagen' => 'imagenes/demo-cultura.svg',
            'imagen_alt' => 'Estudiantes en una actividad cultural',
        ]))->assertRedirect(route('eventos.index'));

        $medios = Medio::where('ruta', 'imagenes/demo-cultura.svg')->get();

        $this->assertCount(1, $medios);
        $this->assertSame('Estudiantes en una actividad cultural', $medios->first()->texto_alternativo);
        $this->assertSame(2, Evento::where('imagen_id', $medios->first()->id)->count());
    }

    public function test_un_duplicado_de_slug_recibe_un_slug_unicos(): void
    {
        $this->actingAs($this->usuario());

        $this->post(route('eventos.store'), $this->datosEvento());
        $this->post(route('eventos.store'), $this->datosEvento([
            'titulo' => 'Feria Universitaria de Ciencia y Tecnología',
        ]))->assertSessionHas('exito', 'Evento creado correctamente.');

        $this->assertSame(2, Evento::count());

        $this->assertEqualsCanonicalizing(
            ['feria-universitaria-de-ciencia-y-tecnologia', 'feria-universitaria-de-ciencia-y-tecnologia-2'],
            Evento::pluck('slug')->all()
        );
    }

    /* -------------------------------- Público -------------------------------- */

    public function test_la_pagina_publica_de_eventos_responde_correctamente(): void
    {
        $this->get(route('eventos.publica'))
            ->assertOk();
    }

    public function test_un_evento_programado_es_visible_en_la_portada_publica(): void
    {
        $this->crearEvento([
            'titulo' => 'Seminario de Investigación: Métodos Mixtos',
            'slug' => 'seminario-investigacion-metodos-mixtos',
        ]);

        $this->get(route('eventos.publica'))
            ->assertOk()
            ->assertSee('Seminario de Investigación: Métodos Mixtos');
    }

    public function test_un_evento_realizado_es_visible_en_la_portada_publica(): void
    {
        $this->crearEvento([
            'titulo' => 'Jornada de Bienestar Universitario',
            'slug' => 'jornada-de-bienestar-universitario',
            'estado' => 'realizado',
            'fecha_inicio' => now()->subDays(5),
        ]);

        $this->get(route('eventos.publica'))
            ->assertOk()
            ->assertSee('Jornada de Bienestar Universitario');
    }

    public function test_un_evento_cancelado_no_es_visible_en_la_portada_publica(): void
    {
        $this->crearEvento([
            'titulo' => 'Conferencia suspendida',
            'slug' => 'conferencia-suspendida',
            'estado' => 'cancelado',
        ]);

        $this->get(route('eventos.publica'))
            ->assertOk()
            ->assertDontSee('Conferencia suspendida');
    }

    public function test_un_evento_publico_puede_verse_individualmente(): void
    {
        $evento = $this->crearEvento([
            'titulo' => 'Torneo Deportivo Interfacultades',
            'slug' => 'torneo-deportivo-interfacultades',
            'lugar' => 'Coliseo del campus',
            'organizador' => 'Área Deportiva',
        ]);

        $this->get(route('evento.publica', 'torneo-deportivo-interfacultades'))
            ->assertOk()
            ->assertSee('Torneo Deportivo Interfacultades')
            ->assertSee('Coliseo del campus')
            ->assertSee('Área Deportiva')
            ->assertSee('Programado')
            ->assertSee('Muestra de proyectos de los semilleros de investigación.')
            ->assertSee('Volver a los eventos');
    }

    public function test_un_slug_inexistente_devuelve_404(): void
    {
        $this->get(route('evento.publica', 'evento-que-no-existe'))
            ->assertNotFound();
    }

    public function test_un_evento_cancelado_devuelve_404(): void
    {
        $this->crearEvento([
            'titulo' => 'Conferencia cancelada',
            'slug' => 'conferencia-cancelada',
            'estado' => 'cancelado',
        ]);

        $this->get(route('evento.publica', 'conferencia-cancelada'))
            ->assertNotFound();
    }

    /* -------------------------------- Seeder -------------------------------- */

    public function test_el_seeder_crea_los_eventos_esperados(): void
    {
        $this->seed(EventoSeeder::class);

        $this->assertSame(8, Evento::count());

        $this->assertEqualsCanonicalizing(
            [
                'feria-universitaria-de-ciencia-y-tecnologia',
                'semana-cultural-universitaria',
                'torneo-deportivo-interfacultades',
                'seminario-investigacion-metodos-mixtos',
                'jornada-de-bienestar-universitario',
                'encuentro-de-egresados-2026',
                'festival-de-arte-universitario',
                'conferencia-inteligencia-artificial-educacion-superior',
            ],
            Evento::orderBy('id')->pluck('slug')->all()
        );
    }

    public function test_el_seeder_no_genera_duplicados_al_ejecutarse_varias_veces(): void
    {
        $this->seed(EventoSeeder::class);
        $this->seed(EventoSeeder::class);
        $this->seed(EventoSeeder::class);

        $this->assertSame(8, Evento::count());
    }
}
