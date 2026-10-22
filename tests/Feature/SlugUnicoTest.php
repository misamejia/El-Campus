<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Support\SlugUnico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlugUnicoTest extends TestCase
{
    use RefreshDatabase;

    public function test_normaliza_el_texto_base_con_str_slug(): void
    {
        $this->assertSame(
            'categoria-de-tecnologia',
            SlugUnico::generar('Categoría de Tecnología', Categoria::class)
        );
    }

    public function test_devuelve_el_slug_base_si_no_existe(): void
    {
        Categoria::create(['nombre' => 'Tecnología', 'slug' => 'tecnologia']);

        $this->assertSame(
            'deportes',
            SlugUnico::generar('Deportes', Categoria::class)
        );
    }

    public function test_anade_sufijos_numericos_hasta_encontrar_un_slug_libre(): void
    {
        Categoria::create(['nombre' => 'Tecnología', 'slug' => 'tecnologia']);
        Categoria::create(['nombre' => 'Tecnología 2', 'slug' => 'tecnologia-2']);

        $this->assertSame(
            'tecnologia-3',
            SlugUnico::generar('Tecnología', Categoria::class)
        );
    }

    public function test_ignora_el_registro_indicado_al_editar(): void
    {
        $categoria = Categoria::create(['nombre' => 'Tecnología', 'slug' => 'tecnologia']);

        $this->assertSame(
            'tecnologia',
            SlugUnico::generar('Tecnología', Categoria::class, $categoria->id)
        );
    }

    public function test_ignorar_un_registro_libera_su_propio_slug(): void
    {
        $primera = Categoria::create(['nombre' => 'Tecnología', 'slug' => 'tecnologia']);
        $segunda = Categoria::create(['nombre' => 'Tecnología 2', 'slug' => 'tecnologia-2']);

        $this->assertSame(
            'tecnologia',
            SlugUnico::generar('Tecnología', Categoria::class, $primera->id)
        );

        $this->assertSame(
            'tecnologia-2',
            SlugUnico::generar('Tecnología', Categoria::class, $segunda->id)
        );
    }

    public function test_el_sufijo_sigue_creciendo_si_otros_registros_ocupan_los_siguientes(): void
    {
        Categoria::create(['nombre' => 'Tecnología', 'slug' => 'tecnologia']);
        Categoria::create(['nombre' => 'Tecnología 2', 'slug' => 'tecnologia-2']);
        $otra = Categoria::create(['nombre' => 'Deportes', 'slug' => 'deportes']);

        $this->assertSame(
            'tecnologia-3',
            SlugUnico::generar('Tecnología', Categoria::class, $otra->id)
        );
    }
}
