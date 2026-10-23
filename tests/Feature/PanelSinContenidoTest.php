<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelSinContenidoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_panel_muestra_ceros_cuando_no_hay_contenido(): void
    {
        $this->actingAs(User::factory()->admin()->create(['email_verified_at' => now()]));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Total de Noticias: 0', false)
            ->assertSee('Total de Autores: 0', false)
            ->assertSee('Total de Eventos: 0', false)
            ->assertSee('0 en total', false);
    }
}
