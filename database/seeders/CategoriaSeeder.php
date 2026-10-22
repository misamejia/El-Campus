<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Categorías editoriales de El Campus.
     *
     * Se utiliza firstOrCreate sobre el slug para que el seeder sea
     * reproducible: si la categoría ya existe no se crea un duplicado.
     */
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Universidad',
                'slug' => 'universidad',
                'descripcion' => 'Noticias institucionales, académicas y de la comunidad universitaria.',
                'orden' => 1,
            ],
            [
                'nombre' => 'Investigación',
                'slug' => 'investigacion',
                'descripcion' => 'Ciencia, innovación, proyectos y resultados de investigación.',
                'orden' => 2,
            ],
            [
                'nombre' => 'Cultura',
                'slug' => 'cultura',
                'descripcion' => 'Arte, patrimonio, literatura, música y expresiones culturales.',
                'orden' => 3,
            ],
            [
                'nombre' => 'Deportes',
                'slug' => 'deportes',
                'descripcion' => 'Actividad deportiva, competencias y vida saludable universitaria.',
                'orden' => 4,
            ],
            [
                'nombre' => 'Eventos',
                'slug' => 'eventos',
                'descripcion' => 'Agenda, actividades, encuentros y acontecimientos de interés universitario.',
                'orden' => 5,
            ],
            [
                'nombre' => 'Opinión',
                'slug' => 'opinion',
                'descripcion' => 'Análisis, columnas y perspectivas sobre temas de interés para la comunidad.',
                'orden' => 6,
            ],
        ];

        foreach ($categorias as $categoria) {
            Categoria::firstOrCreate(
                ['slug' => $categoria['slug']],
                [
                    'nombre' => $categoria['nombre'],
                    'descripcion' => $categoria['descripcion'],
                    'activa' => true,
                    'orden' => $categoria['orden'],
                ]
            );
        }
    }
}
