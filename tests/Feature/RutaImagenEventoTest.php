<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Medio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RutaImagenEventoTest extends TestCase
{
    use RefreshDatabase;

    public function test_desde_ruta_rechaza_rutas_fuera_de_public_imagenes(): void
    {
        $this->assertNull(Medio::desdeRuta('../../.env'));
        $this->assertNull(Medio::desdeRuta('imagenes/../.env'));
        $this->assertNull(Medio::desdeRuta('/etc/passwd'));
        $this->assertDatabaseCount('medios', 0);
    }

    public function test_desde_ruta_acepta_las_imagenes_demo_existentes(): void
    {
        $medio = Medio::desdeRuta('imagenes/demo-cultura.svg');

        $this->assertNotNull($medio);
        $this->assertSame('imagenes/demo-cultura.svg', $medio->ruta);
        $this->assertSame('image/svg+xml', $medio->tipo_mime);
    }

    public function test_el_alta_de_evento_rechaza_rutas_de_imagen_no_permitidas(): void
    {
        $this->actingAs(User::factory()->admin()->create(['email_verified_at' => now()]));

        foreach (['../../.env', 'imagenes/../.env', '/etc/passwd', 'imagenes/no-existe.jpg', 'imagenes/sin-extension'] as $ruta) {
            $this->post(route('eventos.store'), $this->datosEvento(['imagen' => $ruta]))
                ->assertSessionHasErrors('imagen');
        }

        $this->assertDatabaseCount('eventos', 0);
    }

    public function test_el_alta_de_evento_acepta_una_imagen_demo_con_texto_alternativo(): void
    {
        $this->actingAs(User::factory()->admin()->create(['email_verified_at' => now()]));

        $this->post(route('eventos.store'), $this->datosEvento([
            'imagen' => 'imagenes/demo-cultura.svg',
            'imagen_alt' => 'Afiche de la jornada cultural',
        ]))->assertRedirect(route('eventos.index'));

        $medio = Medio::where('ruta', 'imagenes/demo-cultura.svg')->first();

        $this->assertNotNull($medio);
        $this->assertDatabaseHas('eventos', [
            'titulo' => 'Evento de prueba',
            'imagen_id' => $medio->id,
        ]);
    }

    public function test_la_edicion_de_evento_rechaza_rutas_de_imagen_no_permitidas(): void
    {
        $this->actingAs(User::factory()->admin()->create(['email_verified_at' => now()]));

        $evento = Evento::create([
            'titulo' => 'Evento existente',
            'slug' => 'evento-existente',
            'descripcion' => 'Descripción del evento.',
            'lugar' => 'Auditorio',
            'fecha_inicio' => now()->addDay(),
            'estado_id' => $this->idEstadoEvento('programado'),
        ]);

        $this->put(route('eventos.update', $evento), $this->datosEvento([
            'titulo' => 'Evento existente',
            'imagen' => 'imagenes/../.env',
        ]))->assertSessionHasErrors('imagen');
    }

    /**
     * @param  array<string, mixed>  $sobreescribe
     * @return array<string, mixed>
     */
    private function datosEvento(array $sobreescribe = []): array
    {
        return array_merge([
            'titulo' => 'Evento de prueba',
            'descripcion' => 'Descripción del evento de prueba.',
            'lugar' => 'Auditorio principal',
            'fecha_inicio' => now()->addDay()->toDateString(),
            'estado' => 'programado',
        ], $sobreescribe);
    }
}
