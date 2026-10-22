<?php

namespace Database\Seeders;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\EstadoNoticia;
use App\Models\Medio;
use App\Models\Noticia;
use Illuminate\Database\Seeder;

class NoticiaSeeder extends Seeder
{
    /**
     * Noticias de demostración para el proyecto académico.
     *
     * Estrategia anti-duplicados: se utiliza updateOrCreate sobre el slug.
     * El seeder es reproducible, no borra noticias existentes y solo
     * actualiza los datos de las noticias que ya estén presentes.
     * Se apoyan en las categorías del CategoriaSeeder y en los autores
     * del AutorSeeder (que se siembran primero si las tablas están vacías).
     * El autor de cada noticia se resuelve por su slug desde la tabla autores.
     */
    public function run(): void
    {
        if (Categoria::count() === 0) {
            $this->call(CategoriaSeeder::class);
        }

        if (Autor::count() === 0) {
            $this->call(AutorSeeder::class);
        }

        $categoria = fn (string $slug): Categoria =>
            Categoria::where('slug', $slug)->firstOrFail();

        $autor = fn (string $slug): Autor =>
            Autor::where('slug', $slug)->firstOrFail();

        $estado = fn (string $codigo): int =>
            (int) EstadoNoticia::where('codigo', $codigo)->value('id');

        $sinImagen = null;
        $noticias = [
            [
                'categoria' => 'universidad',
                'titulo' => 'Universidad de Córdoba inaugura un nuevo laboratorio de biotecnología aplicada',
                'slug' => 'universidad-de-cordoba-inaugura-nuevo-laboratorio',
                'resumen' => 'La institución amplía su capacidad investigativa con un espacio moderno para estudiantes y docentes.',
                'contenido' => "La Universidad de Córdoba puso en funcionamiento un nuevo laboratorio de biotecnología aplicada, un espacio pensado para fortalecer los procesos académicos y científicos de la región.\n\nEl laboratorio cuenta con equipos de última generación que permitirán a los semilleros y grupos de investigación desarrollar proyectos relacionados con agricultura, salud y medio ambiente.\n\nLas directivas anunciaron que, durante el primer semestre, se priorizará el acceso de estudiantes de los programas de biología, agronomía y química, con acompañamiento de docentes especializados.",
                'autor' => 'redaccion-el-campus',
                'imagen' => 'imagenes/demo-universidad.svg',
                'fecha_publicacion' => now()->subDays(2)->setTime(8, 30),
                'estado' => 'publicada',
                'destacada' => true,
            ],
            [
                'categoria' => 'universidad',
                'titulo' => 'Apertura de matrículas para el periodo académico 2026-2',
                'slug' => 'apertura-de-matriculas-para-el-periodo-2026-2',
                'resumen' => 'Aspirantes y estudiantes de continuidad ya pueden formalizar su inscripción en la página oficial.',
                'contenido' => "La Oficina de Admisiones y Registro informó que se encuentran abiertas las matrículas para el segundo periodo académico de 2026.\n\nLos interesados deben completar el proceso en línea, presentar la documentación requerida y acercarse a las fechas de confirmación indicadas por cada programa académico.\n\nLa universidad recomienda adelantar el proceso con anticipación para evitar congestión en los últimos días del calendario establecido.",
                'autor' => 'oficina-de-admisiones',
                'imagen' => $sinImagen,
                'fecha_publicacion' => now()->subDays(5)->setTime(10, 15),
                'estado' => 'publicada',
                'destacada' => false,
            ],
            [
                'categoria' => 'investigacion',
                'titulo' => 'Estudiantes presentan proyecto sobre la recuperación del río Sinú',
                'slug' => 'estudiantes-presentan-proyecto-recuperacion-rio-sinu',
                'resumen' => 'Una iniciativa estudiantil busca monitorear la calidad del agua con sensores de bajo costo.',
                'contenido' => "Un grupo de estudiantes de ingeniería ambiental presentó una iniciativa que propone monitorear la calidad del agua del río Sinú mediante sensores de bajo costo.\n\nEl proyecto, desarrollado en el marco de un semillero de investigación, contempla estaciones de muestreo y un panel público para consultar los resultados en tiempo real.\n\nLos autores esperan vincular a las comunidades ribereñas en la etapa de validación de la propuesta durante el próximo año.",
                'autor' => 'semillero-de-investigacion-ambiental',
                'imagen' => 'imagenes/demo-investigacion.svg',
                'fecha_publicacion' => now()->subDay()->setTime(7, 45),
                'estado' => 'publicada',
                'destacada' => true,
            ],
            [
                'categoria' => 'investigacion',
                'titulo' => 'Semillero de investigación recibe reconocimiento en encuentro regional',
                'slug' => 'semillero-investigacion-recibe-reconocimiento-regional',
                'resumen' => 'El trabajo sobre bioplaguicidas fue destacado entre más de cuarenta ponencias participantes.',
                'contenido' => "El semillero de biotecnología vegetal recibió un reconocimiento en el encuentro regional de semilleros, donde su ponencia sobre bioplaguicidas para cultivos de la región fue destacada entre más de cuarenta trabajos.\n\nLa presentación condensó doce meses de ensayos en campo y laboratorio, y servirá de insumo para nuevas convocatorias institucionales.\n\nLa publicación completa del trabajo se proyecta para el próximo número de la revista científica universitaria.",
                'autor' => 'equipo-de-investigacion',
                'imagen' => $sinImagen,
                'fecha_publicacion' => now()->subDays(9)->setTime(9, 0),
                'estado' => 'borrador',
                'destacada' => false,
            ],
            [
                'categoria' => 'cultura',
                'titulo' => 'La universidad celebra la semana del teatro universitario',
                'slug' => 'universidad-celebra-semana-del-teatro-universitario',
                'resumen' => 'Agrupaciones estudiantiles presentarán una cartelera con obras clásicas y contemporáneas.',
                'contenido' => "La semana del teatro universitario se tomará los escenarios del campus con una cartelera que reúne obras clásicas y contemporáneas interpretadas por grupos de estudiantes.\n\nLas funciones serán de entrada libre y se realizarán en el auditorio principal y en espacios alternativos de la facultad de ciencias humanas.\n\nLa programación incluye talleres abiertos de actuación y una mesa redonda con docentes invitados sobre el teatro como herramienta pedagógica.",
                'autor' => 'seccion-cultural',
                'imagen' => 'imagenes/demo-cultura.svg',
                'fecha_publicacion' => now()->subDays(3)->setTime(16, 0),
                'estado' => 'publicada',
                'destacada' => false,
            ],
            [
                'categoria' => 'cultura',
                'titulo' => 'Convocatoria abierta para la bienal literaria 2026',
                'slug' => 'convocatoria-bienal-literaria-2026',
                'resumen' => 'Estudiantes, docentes y egresados podrán participar en las categorías de cuento y poesía.',
                'contenido' => "La bienal literaria universitaria abrió su convocatoria 2026 con categorías de cuento y poesía dirigidas a estudiantes, docentes y egresados.\n\nLos trabajos deben ser inéditos y podrán enviarse a través del formulario que publicará la oficina de bienestar institucional.\n\nLas obras ganadoras integrarán una antología impresa y digital que será presentada durante la feria editorial de la universidad.",
                'autor' => 'biblioteca-central',
                'imagen' => $sinImagen,
                'fecha_publicacion' => now()->subDays(6)->setTime(11, 20),
                'estado' => 'publicada',
                'destacada' => false,
            ],
            [
                'categoria' => 'deportes',
                'titulo' => 'El equipo representativo se corona campeón en los juegos interuniversitarios',
                'slug' => 'equipo-representativo-campeon-juegos-interuniversitarios',
                'resumen' => 'El conjunto de fútbol masculino venció en la final y aseguró su cupo a la fase nacional.',
                'contenido' => "El equipo representativo de fútbol masculino se coronó campeón de los juegos interuniversitarios regionales tras vencer en la final por un marcador de dos goles a uno.\n\nEl título asegura la participación de la delegación en la etapa nacional que se disputará a finales de año.\n\nLos jugadores fueron recibidos en el campus por compañeros y directivos, quienes celebraron el desempeño del conjunto durante todo el torneo.",
                'autor' => 'area-deportiva',
                'imagen' => 'imagenes/demo-deportes.svg',
                'fecha_publicacion' => now()->subDays(4)->setTime(13, 10),
                'estado' => 'publicada',
                'destacada' => true,
            ],
            [
                'categoria' => 'deportes',
                'titulo' => 'Apertura de la liga interna de fútbol sala',
                'slug' => 'apertura-liga-interna-futbol-sala',
                'resumen' => 'La inscripción de equipos estará habilitada durante las próximas dos semanas.',
                'contenido' => "El programa de deportes anunció la apertura de la liga interna de fútbol sala, dirigida a estudiantes de todos los programas académicos.\n\nLa inscripción de equipos se realizará en la oficina de bienestar deportivo durante las próximas dos semanas, con un máximo de doce jugadores por nómina.\n\nLa organización de los encuentros se sorteará una vez cerrado el número de equipos participantes.",
                'autor' => 'area-deportiva',
                'imagen' => $sinImagen,
                'fecha_publicacion' => null,
                'estado' => 'borrador',
                'destacada' => false,
            ],
            [
                'categoria' => 'eventos',
                'titulo' => 'Programa oficial de la semana de la ciencia 2026',
                'slug' => 'programa-oficial-semana-de-la-ciencia-2026',
                'resumen' => 'Conferencias, exposiciones y visitas abiertas componen la agenda central del evento.',
                'contenido' => "La semana de la ciencia 2026 presentará una agenda que combina conferencias magistrales, exposiciones de proyectos y visitas abiertas a los laboratorios del campus.\n\nEl evento será de entrada libre y está dirigido a la comunidad universitaria y al público general.\n\nLa programación completa y la asignación de aforos se publicarán en los canales oficiales de la universidad con una semana de anticipación.",
                'autor' => 'coordinacion-de-eventos',
                'imagen' => $sinImagen,
                'fecha_publicacion' => now()->subDays(2)->setTime(9, 45),
                'estado' => 'publicada',
                'destacada' => false,
            ],
            [
                'categoria' => 'opinion',
                'titulo' => 'Editorial: el papel de la universidad pública en la región',
                'slug' => 'editorial-papel-universidad-publica-en-la-region',
                'resumen' => 'Una reflexión editorial sobre el compromiso social de la educación pública en Córdoba.',
                'contenido' => "La universidad pública no solo forma profesionales: construye comunidad, genera pensamiento crítico e impulsa el desarrollo de la región.\n\nEn este editorial invitamos a la comunidad a reflexionar sobre el compromiso que asumimos como institución frente a los retos de nuestro territorio.\n\nRecuperar el diálogo con las regiones, fortalecer la investigación con vocación social y ampliar el acceso a la educación superior son tareas que nos convocan a todos.",
                'autor' => 'consejo-editorial',
                'imagen' => $sinImagen,
                'fecha_publicacion' => now()->subDays(30)->setTime(8, 0),
                'estado' => 'archivada',
                'destacada' => false,
            ],
            [
                'categoria' => 'opinion',
                'titulo' => 'Columna: la lectura compartida que construye ciudadanía universitaria',
                'slug' => 'columna-lectura-compartida-ciudadania-universitaria',
                'resumen' => 'Una reflexión sobre el papel de los clubes de lectura en la formación crítica de la comunidad universitaria.',
                'contenido' => "Los clubes de lectura del campus han demostrado que leer puede ser también un acto colectivo: reunirse para conversar sobre un libro transforma la manera en que entendemos nuestro entorno.\n\nEn estas sesiones no solo se discuten historias; se ejercita la escucha, el respeto por otras ideas y la construcción conjunta de criterio, valores esenciales para la vida universitaria.\n\nInvitamos a la comunidad a unirse a estas experiencias: un buen libro, compartido, siempre abre nuevas preguntas.",
                'autor' => 'consejo-editorial',
                'imagen' => $sinImagen,
                'fecha_publicacion' => now()->subDays(1)->setTime(9, 30),
                'estado' => 'publicada',
                'destacada' => false,
            ],
        ];

        foreach ($noticias as $d) {
            Noticia::updateOrCreate(
                ['slug' => $d['slug']],
                [
                    'categoria_id' => $categoria($d['categoria'])->id,
                    'autor_id' => $autor($d['autor'])->id,
                    'estado_id' => $estado($d['estado']),
                    'imagen_id' => Medio::desdeRuta($d['imagen'])?->id,
                    'titulo' => $d['titulo'],
                    'slug' => $d['slug'],
                    'resumen' => $d['resumen'],
                    'contenido' => $d['contenido'],
                    'fecha_publicacion' => $d['fecha_publicacion'],
                    'destacada' => $d['destacada'],
                ]
            );
        }
    }
}