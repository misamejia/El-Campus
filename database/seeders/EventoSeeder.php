<?php

namespace Database\Seeders;

use App\Models\EstadoEvento;
use App\Models\Evento;
use App\Models\Medio;
use App\Models\Organizador;
use Illuminate\Database\Seeder;

class EventoSeeder extends Seeder
{
    /**
     * Eventos universitarios de demostración para El Campus.
     *
     * Estrategia anti-duplicados: updateOrCreate sobre el slug. El seeder es
     * reproducible, no borra eventos existentes y solo actualiza los datos
     * de aquellos que ya estén presentes.
     *
     * Los estados se reparten entre programado, realizado y cancelado:
     * en la parte pública solo se muestran los dos primeros.
     */
    public function run(): void
    {
        $eventos = [
            [
                'titulo' => 'Feria Universitaria de Ciencia y Tecnología 2026',
                'slug' => 'feria-universitaria-de-ciencia-y-tecnologia',
                'descripcion' => "La feria reunirá los proyectos de los semilleros de investigación, muestras interactivas de laboratorio y conferencias breves a cargo de docentes de la institución.\n\nEs un espacio abierto para que la comunidad universitaria y el público general conozcan de cerca el trabajo científico que se desarrolla en la Universidad de Córdoba.",
                'lugar' => 'Campus Central — Ciudadela Universitaria',
                'fecha_inicio' => now()->addDays(35)->setTime(9, 0),
                'fecha_fin' => now()->addDays(37)->setTime(18, 0),
                'imagen' => 'imagenes/demo-investigacion.svg',
                'organizador' => 'Vicerrectoría de Investigación',
                'estado' => 'programado',
            ],
            [
                'titulo' => 'Semana Cultural Universitaria',
                'slug' => 'semana-cultural-universitaria',
                'descripcion' => "Una semana dedicada a las expresiones artísticas del campus: presentaciones musicales, muestras de teatro, exposiciones de artes plásticas y recitales de poesía.\n\nLa programación se construye con la participación de los grupos culturales estudiantiles y docentes invitados.",
                'lugar' => 'Auditorio Central y espacios del campus',
                'fecha_inicio' => now()->addDays(22)->setTime(15, 0),
                'fecha_fin' => now()->addDays(26)->setTime(21, 0),
                'imagen' => 'imagenes/demo-cultura.svg',
                'organizador' => 'Bienestar Universitario',
                'estado' => 'programado',
            ],
            [
                'titulo' => 'Torneo Deportivo Interfacultades',
                'slug' => 'torneo-deportivo-interfacultades',
                'descripcion' => "Competición deportiva entre las facultades de la universidad en disciplinas como fútbol, baloncesto, voleibol y atletismo.\n\nLos equipos representan a sus programas académicos y los encuentros se disputan durante las tardes en las canchas del campus.",
                'lugar' => 'Coliseo y canchas del campus',
                'fecha_inicio' => now()->addDays(12)->setTime(14, 0),
                'fecha_fin' => now()->addDays(40)->setTime(18, 0),
                'imagen' => 'imagenes/demo-deportes.svg',
                'organizador' => 'Área Deportiva',
                'estado' => 'programado',
            ],
            [
                'titulo' => 'Seminario de Investigación: Métodos Mixtos para Estudios Regionales',
                'slug' => 'seminario-investigacion-metodos-mixtos',
                'descripcion' => "El seminario abordará el uso combinado de metodologías cuantitativas y cualitativas en investigaciones sobre la región del Sinú.\n\nIncluye sesiones teóricas, talleres prácticos y la presentación de experiencias de los grupos de investigación de la universidad.",
                'lugar' => 'Auditorio de la Facultad de Ciencias Humanas',
                'fecha_inicio' => now()->addDays(8)->setTime(8, 30),
                'fecha_fin' => now()->addDays(9)->setTime(17, 0),
                'imagen' => null,
                'organizador' => 'Equipo de Investigación',
                'estado' => 'programado',
            ],
            [
                'titulo' => 'Jornada de Bienestar Universitario',
                'slug' => 'jornada-de-bienestar-universitario',
                'descripcion' => "Actividades de promoción de la salud física y mental para la comunidad estudiantil: valoraciones médicas, talleres de manejo del estrés y espacios recreativos.\n\nLa jornada se desarrolló en coordinación con el programa institucional de acompañamiento integral.",
                'lugar' => 'Plazoleta principal del campus',
                'fecha_inicio' => now()->subDays(5)->setTime(8, 0),
                'fecha_fin' => now()->subDays(5)->setTime(13, 0),
                'imagen' => null,
                'organizador' => 'Bienestar Universitario',
                'estado' => 'realizado',
            ],
            [
                'titulo' => 'Encuentro de Egresados 2026',
                'slug' => 'encuentro-de-egresados-2026',
                'descripcion' => "Reencuentro de profesionales graduados de la universidad con sus programas académicos, incluyendo conversatorios y la elección de los representantes de la promoción.\n\nFue una jornada de celebración y de fortalecimiento de los vínculos entre la institución y sus egresados.",
                'lugar' => 'Auditorio Central y campus',
                'fecha_inicio' => now()->subDays(18)->setTime(9, 0),
                'fecha_fin' => now()->subDays(18)->setTime(18, 0),
                'imagen' => null,
                'organizador' => 'Oficina de Egresados',
                'estado' => 'realizado',
            ],
            [
                'titulo' => 'Festival de Arte Universitario',
                'slug' => 'festival-de-arte-universitario',
                'descripcion' => "El festival reunió muestras de danza, música, fotografía y artes visuales creadas por estudiantes de todos los programas.\n\nLas obras fueron seleccionadas por un jurado académico y permanecieron expuestas durante la semana del evento.",
                'lugar' => 'Centro de Convenciones del campus',
                'fecha_inicio' => now()->subDays(42)->setTime(16, 0),
                'fecha_fin' => now()->subDays(42)->setTime(20, 0),
                'imagen' => null,
                'organizador' => 'Sección Cultural',
                'estado' => 'realizado',
            ],
            [
                'titulo' => 'Conferencia: Inteligencia Artificial en la Educación Superior',
                'slug' => 'conferencia-inteligencia-artificial-educacion-superior',
                'descripcion' => "Conferencia magistral sobre las oportunidades y retos de la inteligencia artificial en los procesos de enseñanza y aprendizaje universitarios.\n\nEl evento fue aplazado por decisión de la organización y se anunciará una nueva fecha próximamente.",
                'lugar' => 'Auditorio de la Facultad de Ingenierías',
                'fecha_inicio' => now()->addDays(10)->setTime(10, 0),
                'fecha_fin' => now()->addDays(10)->setTime(12, 0),
                'imagen' => null,
                'organizador' => 'Facultad de Ingenierías',
                'estado' => 'cancelado',
            ],
        ];

        if (Organizador::count() === 0) {
            $this->call(OrganizadorSeeder::class);
        }

        $estado = fn (string $codigo): int =>
            (int) EstadoEvento::where('codigo', $codigo)->value('id');

        $organizador = fn (?string $nombre): ?int =>
            $nombre === null ? null : Organizador::firstOrCreate(['nombre' => $nombre], ['activo' => true])->id;

        foreach ($eventos as $d) {
            Evento::updateOrCreate(
                ['slug' => $d['slug']],
                [
                    'organizador_id' => $organizador($d['organizador']),
                    'estado_id' => $estado($d['estado']),
                    'imagen_id' => Medio::desdeRuta($d['imagen'])?->id,
                    'titulo' => $d['titulo'],
                    'slug' => $d['slug'],
                    'descripcion' => $d['descripcion'],
                    'lugar' => $d['lugar'],
                    'fecha_inicio' => $d['fecha_inicio'],
                    'fecha_fin' => $d['fecha_fin'],
                ]
            );
        }
    }
}