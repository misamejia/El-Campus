<?php

namespace Database\Seeders;

use App\Models\Autor;
use Illuminate\Database\Seeder;

class AutorSeeder extends Seeder
{
    /**
     * Autores editoriales y de sección de El Campus.
     *
     * Se crean a partir de los autores realmente presentes en las noticias
     * existentes. Se utiliza firstOrCreate sobre el slug: el seeder es
     * reproducible y no genera duplicados.
     */
    public function run(): void
    {
        $autores = [
            [
                'nombre' => 'Redacción El Campus',
                'slug' => 'redaccion-el-campus',
                'descripcion' => 'Equipo editorial general del periódico universitario.',
                'email' => 'redaccion@elampus.test',
            ],
            [
                'nombre' => 'Oficina de Admisiones',
                'slug' => 'oficina-de-admisiones',
                'descripcion' => 'Información oficial sobre matrículas e ingresos.',
                'email' => 'admisiones@elampus.test',
            ],
            [
                'nombre' => 'Semillero de Investigación Ambiental',
                'slug' => 'semillero-de-investigacion-ambiental',
                'descripcion' => 'Estudiantes que investigan temas ambientales y de la región.',
                'email' => null,
            ],
            [
                'nombre' => 'Equipo de Investigación',
                'slug' => 'equipo-de-investigacion',
                'descripcion' => 'Cobertura de ciencia, innovación y resultados investigativos.',
                'email' => 'investigacion@elampus.test',
            ],
            [
                'nombre' => 'Sección Cultural',
                'slug' => 'seccion-cultural',
                'descripcion' => 'Arte, literatura, música y expresiones culturales del campus.',
                'email' => 'cultura@elampus.test',
            ],
            [
                'nombre' => 'Biblioteca Central',
                'slug' => 'biblioteca-central',
                'descripcion' => 'Servicios y convocatorias asociadas al sistema de bibliotecas.',
                'email' => 'biblioteca@elampus.test',
            ],
            [
                'nombre' => 'Área Deportiva',
                'slug' => 'area-deportiva',
                'descripcion' => 'Actividad deportiva, competencias y bienestar universitario.',
                'email' => 'deportes@elampus.test',
            ],
            [
                'nombre' => 'Coordinación de Eventos',
                'slug' => 'coordinacion-de-eventos',
                'descripcion' => 'Agenda y acontecimientos institucionales.',
                'email' => 'eventos@elampus.test',
            ],
            [
                'nombre' => 'Consejo Editorial',
                'slug' => 'consejo-editorial',
                'descripcion' => 'Análisis y columnas de opinión del periódico.',
                'email' => 'editorial@elampus.test',
            ],
        ];

        foreach ($autores as $autor) {
            Autor::firstOrCreate(
                ['slug' => $autor['slug']],
                [
                    'nombre' => $autor['nombre'],
                    'descripcion' => $autor['descripcion'],
                    'email' => $autor['email'],
                    'activo' => true,
                ]
            );
        }
    }
}