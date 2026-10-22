<?php

namespace Tests\Feature;

use App\Models\Categoria;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\UniqueConstraintViolationException;
use Tests\TestCase;

class CategoriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_categoria_puede_crearse_y_recuperarse(): void
    {
        $categoria = Categoria::create([
            'nombre' => 'Tecnología',
            'slug' => 'tecnologia',
            'descripcion' => 'Nuevas tecnologías en el entorno universitario.',
            'activa' => true,
        ]);

        $recuperada = Categoria::find($categoria->id);

        $this->assertInstanceOf(Categoria::class, $recuperada);
        $this->assertSame('Tecnología', $recuperada->nombre);
        $this->assertSame('tecnologia', $recuperada->slug);
    }

    public function test_el_slug_debe_ser_unico(): void
    {
        Categoria::create([
            'nombre' => 'Universidad',
            'slug' => 'universidad',
        ]);

        $this->expectException(UniqueConstraintViolationException::class);

        Categoria::create([
            'nombre' => 'Vida Universitaria',
            'slug' => 'universidad',
        ]);
    }

    public function test_el_campo_activa_funciona_correctamente(): void
    {
        $activa = Categoria::create([
            'nombre' => 'Cultura',
            'slug' => 'cultura',
        ]);

        $inactiva = Categoria::create([
            'nombre' => 'Archivo',
            'slug' => 'archivo',
            'activa' => false,
        ]);

        $this->assertEquals(true, $activa->refresh()->activa);
        $this->assertEquals(false, $inactiva->refresh()->activa);

        $this->assertSame(1, Categoria::where('activa', true)->count());
    }

    public function test_el_seeder_inserta_las_seis_categorias_iniciales(): void
    {
        $this->seed(CategoriaSeeder::class);

        $this->assertSame(6, Categoria::count());

        $this->assertEquals(
            ['universidad', 'investigacion', 'cultura', 'deportes', 'eventos', 'opinion'],
            Categoria::orderBy('id')->pluck('slug')->all()
        );
    }

    public function test_el_seeder_no_genera_duplicados(): void
    {
        $this->seed(CategoriaSeeder::class);
        $this->seed(CategoriaSeeder::class);

        $this->assertSame(6, Categoria::count());
    }
}