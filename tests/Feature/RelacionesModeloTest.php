<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Medio;
use App\Models\Noticia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RelacionesModeloTest extends TestCase
{
    use RefreshDatabase;

    public function test_las_relaciones_de_medio_son_has_many(): void
    {
        $medio = Medio::create([
            'subido_por' => null,
            'disco' => 'public',
            'ruta' => 'imagenes/prueba.jpg',
            'tipo_mime' => 'image/jpeg',
            'tamano_bytes' => 1024,
            'ancho' => 800,
            'alto' => 600,
        ]);

        $this->assertInstanceOf(HasMany::class, $medio->noticias());
        $this->assertInstanceOf(HasMany::class, $medio->eventos());
        $this->assertSame('imagen_id', $medio->noticias()->getForeignKeyName());
        $this->assertSame('imagen_id', $medio->eventos()->getForeignKeyName());
    }

    public function test_las_relaciones_de_autor_y_medio_devuelven_los_registros_asociados(): void
    {
        $autor = Autor::create(['nombre' => 'Redacción', 'slug' => 'redaccion', 'activo' => true]);
        $categoria = Categoria::create(['nombre' => 'Investigación', 'slug' => 'investigacion', 'activa' => true]);
        $medio = Medio::create([
            'disco' => 'public',
            'ruta' => 'imagenes/portada.jpg',
            'tipo_mime' => 'image/jpeg',
            'tamano_bytes' => 2048,
        ]);

        $noticia = Noticia::create([
            'titulo' => 'Noticia relacionada',
            'slug' => 'noticia-relacionada',
            'estado_id' => $this->estadoPublicada(),
            'categoria_id' => $categoria->id,
            'autor_id' => $autor->id,
            'imagen_id' => $medio->id,
            'contenido' => 'Contenido de la noticia.',
        ]);

        $evento = Evento::create([
            'titulo' => 'Evento relacionado',
            'slug' => 'evento-relacionado',
            'estado_id' => $this->estadoProgramado(),
            'imagen_id' => $medio->id,
            'descripcion' => 'Descripción del evento.',
            'lugar' => 'Auditorio central',
            'fecha_inicio' => now()->addDays(3),
        ]);

        $this->assertTrue($autor->noticias->contains($noticia));
        $this->assertTrue($medio->noticias->contains($noticia));
        $this->assertTrue($medio->eventos->contains($evento));
    }

    private function estadoPublicada(): int
    {
        return (int) DB::table('estados_noticia')->where('codigo', 'publicada')->value('id');
    }

    private function estadoProgramado(): int
    {
        return (int) DB::table('estados_evento')->where('codigo', 'programado')->value('id');
    }
}
