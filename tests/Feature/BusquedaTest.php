<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Noticia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusquedaTest extends TestCase
{
    use RefreshDatabase;

    protected Categoria $categoria;

    protected Autor $autor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoria = Categoria::create([
            'nombre' => 'Investigación',
            'slug' => 'investigacion',
        ]);

        $this->autor = Autor::create([
            'nombre' => 'Redacción Ciencia',
            'slug' => 'redaccion-ciencia',
        ]);
    }

    private function crearNoticia(array $sobreescribe = []): Noticia
    {
        $estado = $sobreescribe['estado'] ?? 'publicada';
        unset($sobreescribe['estado']);

        return Noticia::create(array_merge([
            'titulo' => 'Semana de la ciencia inaugura el campus',
            'slug' => 'semana-de-la-ciencia-inaugura-el-campus',
            'categoria_id' => $this->categoria->id,
            'autor_id' => $this->autor->id,
            'resumen' => 'La comunidad académica presenta sus avances en los laboratorios.',
            'contenido' => "La biblioteca central abre sus puertas a todos los estudiantes.\n\nInvestigación universitaria al alcance de la comunidad.",
            'fecha_publicacion' => now()->subDay()->format('Y-m-d H:i:s'),
            'estado_id' => $this->idEstadoNoticia($estado),
            'destacada' => false,
        ], $sobreescribe));
    }

    private function crearEvento(array $sobreescribe = []): Evento
    {
        $estado = $sobreescribe['estado'] ?? 'programado';
        $organizador = $sobreescribe['organizador'] ?? 'Vicerrectoría de Investigación';
        unset($sobreescribe['estado'], $sobreescribe['organizador']);

        return Evento::create(array_merge([
            'titulo' => 'Semillero de investigación',
            'slug' => 'semillero-de-investigacion',
            'descripcion' => 'Encuentro mensual del semillero universitario.',
            'lugar' => 'Auditorio Central',
            'fecha_inicio' => now()->addDays(10),
            'organizador_id' => $this->idOrganizador($organizador),
            'estado_id' => $this->idEstadoEvento($estado),
        ], $sobreescribe));
    }

    /* ------------------------------------- Rutas ------------------------------------- */

    public function test_la_ruta_de_busqueda_responde_200(): void
    {
        $this->get(route('buscar'))
            ->assertOk();
    }

    public function test_la_busqueda_con_parametro_responde_200(): void
    {
        $this->get(route('buscar', ['q' => 'universidad']))
            ->assertOk();
    }

    public function test_la_busqueda_es_publica_y_no_requiere_autenticacion(): void
    {
        $this->crearNoticia([
            'titulo' => 'Noticia pública sin necesidad de iniciar sesión',
            'slug' => 'noticia-publica-sin-login',
        ]);

        $this->get(route('buscar', ['q' => 'sesión']))
            ->assertOk()
            ->assertSee('Noticia pública sin necesidad de iniciar sesión');
    }

    public function test_la_busqueda_sin_termino_muestra_la_pantalla_inicial(): void
    {
        $this->get(route('buscar'))
            ->assertOk()
            ->assertSee('Buscar en El Campus');
    }

    public function test_un_termino_vacio_o_solo_espacios_se_trata_como_busqueda_sin_termino(): void
    {
        $this->get(route('buscar', ['q' => '   ']))
            ->assertOk()
            ->assertSee('Buscar en El Campus');
    }

    /* ------------------------------------ Noticias ----------------------------------- */

    public function test_encuentra_una_noticia_publicada_por_titulo(): void
    {
        $noticia = $this->crearNoticia([
            'titulo' => 'El telescopio solar ya observa el patio central',
            'slug' => 'el-telescopio-solar',
        ]);

        $this->get(route('buscar', ['q' => 'telescopio']))
            ->assertOk()
            ->assertSee($noticia->titulo);
    }

    public function test_encuentra_una_noticia_publicada_por_resumen(): void
    {
        $noticia = $this->crearNoticia([
            'titulo' => 'Renovación del inventario florístico',
            'slug' => 'renovacion-inventario-floristico',
            'resumen' => 'El herbario universitario renueva su inventario de especies.',
        ]);

        $this->get(route('buscar', ['q' => 'herbario']))
            ->assertOk()
            ->assertSee($noticia->titulo);
    }

    public function test_encuentra_una_noticia_publicada_por_contenido(): void
    {
        $noticia = $this->crearNoticia([
            'titulo' => 'El observatorio registra una tormenta tropical',
            'slug' => 'observatorio-tormenta-tropical',
            'contenido' => 'El observatorio meteorológico registró la tormenta de anoche.',
        ]);

        $this->get(route('buscar', ['q' => 'meteorológico']))
            ->assertOk()
            ->assertSee($noticia->titulo);
    }

    public function test_encuentra_una_noticia_publicada_por_categoria(): void
    {
        $noticia = $this->crearNoticia([
            'titulo' => 'Beca para jóvenes investigadores',
            'slug' => 'beca-jovenes-investigadores',
            'resumen' => 'Convocatoria abierta para estudiantes.',
        ]);

        $this->get(route('buscar', ['q' => 'Investigación']))
            ->assertOk()
            ->assertSee($noticia->titulo);
    }

    public function test_encuentra_una_noticia_publicada_por_autor(): void
    {
        $noticia = $this->crearNoticia([
            'titulo' => 'Nuevo convenio entre facultades',
            'slug' => 'nuevo-convenio-entre-facultades',
            'resumen' => 'Firma del convenio interinstitucional.',
        ]);

        $this->get(route('buscar', ['q' => 'Ciencia']))
            ->assertOk()
            ->assertSee($noticia->titulo);
    }

    public function test_no_muestra_borradores_ni_archivadas(): void
    {
        $publicada = $this->crearNoticia([
            'titulo' => 'Laboratorio abre sus puertas',
            'slug' => 'laboratorio-abre-sus-puertas',
        ]);

        $this->crearNoticia([
            'titulo' => 'Laboratorio en revisión (borrador)',
            'slug' => 'laboratorio-en-revision-borrador',
            'estado' => 'borrador',
        ]);

        $this->crearNoticia([
            'titulo' => 'Laboratorio histórico (archivada)',
            'slug' => 'laboratorio-historico-archivada',
            'estado' => 'archivada',
        ]);

        $this->get(route('buscar', ['q' => 'Laboratorio']))
            ->assertOk()
            ->assertSee($publicada->titulo)
            ->assertDontSee('(borrador)')
            ->assertDontSee('(archivada)');
    }

    public function test_no_muestra_una_noticia_archivada(): void
    {
        $publicada = $this->crearNoticia([
            'titulo' => 'Quiosco de prensa estrena sección local',
            'slug' => 'quiosco-de-prensa-seccion-local',
        ]);

        $this->crearNoticia([
            'titulo' => 'Quiosco de prensa (borrador)',
            'slug' => 'quiosco-de-prensa-borrador',
            'estado' => 'borrador',
        ]);

        $this->crearNoticia([
            'titulo' => 'Quiosco de prensa (archivada)',
            'slug' => 'quiosco-de-prensa-archivada',
            'estado' => 'archivada',
        ]);

        $this->get(route('buscar', ['q' => 'Quiosco']))
            ->assertOk()
            ->assertSee($publicada->titulo)
            ->assertDontSee('(archivada)');
    }

    public function test_las_noticias_se_ordenan_por_fecha_de_publicacion_descendente(): void
    {
        $reciente = $this->crearNoticia([
            'titulo' => 'Anuario estadístico 2026',
            'slug' => 'anuario-estadistico-2026',
            'fecha_publicacion' => now()->subDay()->format('Y-m-d H:i:s'),
        ]);

        $anterior = $this->crearNoticia([
            'titulo' => 'Anuario estadístico 2025',
            'slug' => 'anuario-estadistico-2025',
            'fecha_publicacion' => now()->subDays(3)->format('Y-m-d H:i:s'),
        ]);

        $this->get(route('buscar', ['q' => 'Anuario']))
            ->assertOk()
            ->assertSeeInOrder([$reciente->titulo, $anterior->titulo]);
    }

    public function test_la_busqueda_funciona_con_frases(): void
    {
        $noticia = $this->crearNoticia([
            'titulo' => 'Restauración de la sala de lectura',
            'slug' => 'restauracion-sala-de-lectura',
            'contenido' => 'En la biblioteca central descansan los archivos de la facultad.',
        ]);

        $this->get(route('buscar', ['q' => 'biblioteca central']))
            ->assertOk()
            ->assertSee($noticia->titulo);
    }

    public function test_la_busqueda_funciona_con_tildes(): void
    {
        $noticia = $this->crearNoticia([
            'titulo' => 'Investigación universitaria sobre biotecnología',
            'slug' => 'investigacion-biotecnologia',
            'resumen' => 'Avances del semillero de biotecnología.',
        ]);

        $this->get(route('buscar', ['q' => 'biotecnología']))
            ->assertOk()
            ->assertSee($noticia->titulo);
    }

    /* ------------------------------------ Eventos ------------------------------------ */

    public function test_encuentra_un_evento_programado(): void
    {
        $evento = $this->crearEvento();

        $this->get(route('buscar', ['q' => 'semillero']))
            ->assertOk()
            ->assertSee($evento->titulo)
            ->assertSee('Programado');
    }

    public function test_encuentra_un_evento_realizado(): void
    {
        $evento = $this->crearEvento([
            'titulo' => 'Feria de la ciencia universitaria',
            'slug' => 'feria-de-la-ciencia-universitaria',
            'fecha_inicio' => now()->subDays(5),
            'estado' => 'realizado',
        ]);

        $this->get(route('buscar', ['q' => 'feria']))
            ->assertOk()
            ->assertSee($evento->titulo)
            ->assertSee('Realizado');
    }

    public function test_no_muestra_un_evento_cancelado(): void
    {
        $programado = $this->crearEvento([
            'titulo' => 'Coloquio de historia regional',
            'slug' => 'coloquio-de-historia-regional',
        ]);

        $realizado = $this->crearEvento([
            'titulo' => 'Coloquio de periodismo cultural',
            'slug' => 'coloquio-de-periodismo-cultural',
            'fecha_inicio' => now()->subDays(10),
            'estado' => 'realizado',
        ]);

        $this->crearEvento([
            'titulo' => 'Coloquio cancelado por mantenimiento',
            'slug' => 'coloquio-cancelado-mantenimiento',
            'fecha_inicio' => now()->addDays(2),
            'estado' => 'cancelado',
        ]);

        $this->get(route('buscar', ['q' => 'Coloquio']))
            ->assertOk()
            ->assertSee($programado->titulo)
            ->assertSee($realizado->titulo)
            ->assertDontSee('cancelado por mantenimiento');
    }

    public function test_los_eventos_programados_aparecen_antes_de_los_realizados(): void
    {
        $programado = $this->crearEvento([
            'titulo' => 'Simposio de derecho (programado)',
            'slug' => 'simposio-de-derecho-programado',
            'fecha_inicio' => now()->addDays(30),
        ]);

        $realizado = $this->crearEvento([
            'titulo' => 'Simposio de derecho (realizado)',
            'slug' => 'simposio-de-derecho-realizado',
            'fecha_inicio' => now()->subDays(1),
            'estado' => 'realizado',
        ]);

        $this->get(route('buscar', ['q' => 'Simposio']))
            ->assertOk()
            ->assertSeeInOrder([$programado->titulo, $realizado->titulo]);
    }

    /* ----------------------------------- Resultados ---------------------------------- */

    public function test_una_busqueda_inexistente_muestra_mensaje_de_resultados_vacios(): void
    {
        $this->get(route('buscar', ['q' => 'tecnología-inexistente-zzzz']))
            ->assertOk()
            ->assertSee('No encontramos resultados para tu búsqueda.');
    }

    public function test_el_termino_buscado_aparece_en_la_vista(): void
    {
        $this->get(route('buscar', ['q' => 'cultura']))
            ->assertOk()
            ->assertSee('Resultados para: cultura');
    }

    public function test_los_resultados_enlazan_a_las_rutas_publicas_correctas(): void
    {
        $noticia = $this->crearNoticia([
            'titulo' => 'Seminario taller de prensa',
            'slug' => 'seminario-taller-de-prensa',
        ]);

        $evento = $this->crearEvento([
            'titulo' => 'Seminario de edición inmersiva',
            'slug' => 'seminario-de-edicion-inmersiva',
        ]);

        $this->get(route('buscar', ['q' => 'Seminario']))
            ->assertOk()
            ->assertSee(route('noticia.publica', $noticia->slug))
            ->assertSee(route('evento.publica', $evento->slug));
    }

    public function test_noticias_y_eventos_aparecen_en_sus_secciones(): void
    {
        $noticia = $this->crearNoticia([
            'titulo' => 'Taller de oratoria estudiantil',
            'slug' => 'taller-de-oratoria-estudiantil',
        ]);

        $evento = $this->crearEvento([
            'titulo' => 'Taller de escritura creativa',
            'slug' => 'taller-de-escritura-creativa',
        ]);

        $this->get(route('buscar', ['q' => 'Taller']))
            ->assertOk()
            ->assertSee('Noticias encontradas')
            ->assertSee('Eventos encontrados')
            ->assertSee($noticia->titulo)
            ->assertSee($evento->titulo)
            ->assertSeeInOrder(['Noticias encontradas', 'Eventos encontrados']);
    }

    /* ------------------------------------ Seguridad ---------------------------------- */

    public function test_un_termino_con_caracteres_especiales_no_genera_error(): void
    {
        $this->get(route('buscar', ['q' => '100% de avance — investigación & ciencia']))
            ->assertOk();
    }

    public function test_una_entrada_html_no_se_interpreta_como_html(): void
    {
        $this->get(route('buscar', ['q' => '<script>alert("x")</script>']))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert', false);
    }

    public function test_una_entrada_excesivamente_larga_es_rechazada(): void
    {
        $this->get(route('buscar', ['q' => str_repeat('a', 150)]))
            ->assertSessionHasErrors('q');
    }

    public function test_un_termino_de_longitud_maxima_es_aceptado(): void
    {
        $this->get(route('buscar', ['q' => str_repeat('a', 100)]))
            ->assertOk();
    }

    /* ------------------------------------ Regresión ---------------------------------- */

    public function test_la_portada_publica_sigue_funcionando(): void
    {
        $this->get(route('inicio'))
            ->assertOk();
    }

    public function test_la_pagina_publica_de_noticia_sigue_funcionando(): void
    {
        $noticia = $this->crearNoticia();

        $this->get(route('noticia.publica', $noticia->slug))
            ->assertOk()
            ->assertSee($noticia->titulo);
    }

    public function test_la_pagina_publica_de_eventos_sigue_funcionando(): void
    {
        $this->get(route('eventos.publica'))
            ->assertOk();
    }

    public function test_la_pagina_publica_de_evento_sigue_funcionando(): void
    {
        $evento = $this->crearEvento();

        $this->get(route('evento.publica', $evento->slug))
            ->assertOk()
            ->assertSee($evento->titulo);
    }

    public function test_el_login_sigue_funcionando(): void
    {
        $this->get(route('login'))
            ->assertOk();
    }

    public function test_el_dashboard_sigue_protegido(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_las_rutas_administrativas_siguen_protegidas(): void
    {
        $this->get(route('noticias.index'))->assertRedirect(route('login'));
        $this->get(route('autores.index'))->assertRedirect(route('login'));
        $this->get(route('usuarios.index'))->assertRedirect(route('login'));
        $this->get(route('eventos.index'))->assertRedirect(route('login'));
    }
}
