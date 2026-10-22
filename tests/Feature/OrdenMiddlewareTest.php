<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprueba el orden de los middleware tras introducir el binding de modelo.
 *
 * El control de rol debe ejecutarse ANTES de resolver el modelo de la ruta:
 * si no, un usuario sin permisos recibiría un 404 (modelo inexistente) en
 * lugar de un 403, lo que además revela qué identificadores existen.
 */
class OrdenMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_editor_no_puede_autor_inexistente_recibe_403_y_no_404(): void
    {
        $this->actingAs($this->editor());

        $this->get(route('autores.show', 9999))->assertForbidden();
    }

    public function test_un_editor_no_puede_editar_autor_inexistente(): void
    {
        $this->actingAs($this->editor());

        $this->get(route('autores.edit', 9999))->assertForbidden();
    }

    public function test_un_editor_no_puede_categoria_inexistente(): void
    {
        $this->actingAs($this->editor());

        $this->get(route('categorias.show', 9999))->assertForbidden();
    }

    public function test_un_editor_no_puede_editar_categoria_inexistente(): void
    {
        $this->actingAs($this->editor());

        $this->get(route('categorias.edit', 9999))->assertForbidden();
    }

    public function test_un_editor_no_puede_actualizar_el_rol_de_un_usuario_inexistente(): void
    {
        $this->actingAs($this->editor());

        $this->patch(route('usuarios.rol.actualizar', 9999), [
            'role_id' => 1,
        ])->assertForbidden();
    }

    public function test_un_editor_no_puede_ver_el_listado_de_usuarios(): void
    {
        $this->actingAs($this->editor());

        $this->get(route('usuarios.index'))->assertForbidden();
    }

    public function test_un_administrador_recibe_404_si_el_registro_no_existe(): void
    {
        $this->actingAs(User::factory()->admin()->create(['email_verified_at' => now()]));

        $this->get(route('autores.show', 9999))->assertNotFound();
    }

    private function editor(): User
    {
        return User::factory()->editor()->create(['email_verified_at' => now()]);
    }
}
