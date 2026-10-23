<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolLectorTest extends TestCase
{
    use RefreshDatabase;

    private function lector(bool $verificado = true): User
    {
        return User::factory()->lector()->create([
            'email_verified_at' => $verificado ? now() : null,
        ]);
    }

    private function editor(bool $verificado = true): User
    {
        return User::factory()->editor()->create([
            'email_verified_at' => $verificado ? now() : null,
        ]);
    }

    private function administrador(): User
    {
        return User::factory()->administrador()->create([
            'email_verified_at' => now(),
        ]);
    }

    /* ------------------------------ Registro ------------------------------ */

    public function test_el_registro_crea_un_usuario_lector(): void
    {
        $this->post('/register', [
            'name' => 'Persona Lectora',
            'email' => 'lectora@elcampus.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $usuario = User::where('email', 'lectora@elcampus.test')->firstOrFail();

        $this->assertTrue($usuario->isLector());
        $this->assertFalse($usuario->isEditor());
        $this->assertFalse($usuario->isAdministrador());
    }

    public function test_el_registro_ignora_role_id_enviado_en_el_cuerpo(): void
    {
        foreach ([1, 2] as $roleId) {
            $email = "intruso{$roleId}@elcampus.test";

            $this->post('/logout');

            $this->post('/register', [
                'name' => 'Intruso',
                'email' => $email,
                'password' => 'password',
                'password_confirmation' => 'password',
                'role_id' => $roleId,
                'role' => $roleId === 1 ? User::ROLE_ADMIN : User::ROLE_EDITOR,
            ]);

            $this->assertTrue(
                User::where('email', $email)->firstOrFail()->isLector(),
                "El registro con role_id={$roleId} no debe asignar otro rol."
            );
        }
    }

    public function test_actualizar_el_perfil_no_cambia_el_rol(): void
    {
        $lector = $this->lector();

        $this->actingAs($lector)
            ->put(route('user-profile-information.update'), [
                'name' => 'Lector Actualizado',
                'email' => $lector->email,
                'role_id' => 1,
                'role' => User::ROLE_ADMIN,
            ]);

        $this->assertTrue($lector->fresh()->isLector());
    }

    public function test_role_id_no_es_asignable_masivamente(): void
    {
        $this->assertNotContains('role_id', (new User)->getFillable());
    }

    /* --------------------------- Acceso del lector --------------------------- */

    public function test_el_lector_recibe_403_en_las_rutas_del_panel(): void
    {
        $this->actingAs($this->lector());

        foreach ([
            route('dashboard'),
            route('noticias.index'),
            route('noticias.create'),
            route('eventos.index'),
            route('autores.index'),
            route('categorias.index'),
            route('usuarios.index'),
        ] as $ruta) {
            $this->get($ruta)->assertForbidden();
        }
    }

    public function test_el_lector_no_puede_enviar_post_a_las_rutas_de_escritura(): void
    {
        $this->actingAs($this->lector());

        foreach ([
            route('noticias.store'),
            route('eventos.store'),
            route('autores.store'),
            route('categorias.store'),
        ] as $ruta) {
            $this->post($ruta, [])->assertForbidden();
        }
    }

    public function test_el_lector_accede_a_la_portada_al_perfil_y_a_su_configuracion(): void
    {
        $this->actingAs($this->lector());

        $this->get(route('inicio'))->assertOk();
        $this->get(route('perfil'))->assertOk();
        $this->get(route('profile.show'))->assertOk();
    }

    public function test_el_lector_es_redirigido_a_la_portada_al_registrarse_y_al_iniciar_sesion(): void
    {
        $respuesta = $this->post('/register', [
            'name' => 'Nuevo Lector',
            'email' => 'nuevo-lector@elcampus.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $respuesta->assertRedirect(route('inicio'));

        $this->post('/logout');

        $lector = $this->lector();

        $this->post('/login', [
            'email' => $lector->email,
            'password' => 'password',
        ])->assertRedirect(route('inicio'));
    }

    /* ------------------------- Escalada de privilegios ------------------------- */

    public function test_el_editor_no_puede_cambiar_roles_ni_el_suyo_ni_el_de_otros(): void
    {
        $editor = $this->editor();
        $otroEditor = $this->editor();

        $this->actingAs($editor)
            ->patch(route('usuarios.rol.actualizar', $otroEditor), [
                'role' => User::ROLE_ADMIN,
            ])
            ->assertForbidden();

        $this->actingAs($editor)
            ->patch(route('usuarios.rol.actualizar', $editor), [
                'role' => User::ROLE_ADMIN,
            ])
            ->assertForbidden();

        $this->assertTrue($otroEditor->fresh()->isEditor());
        $this->assertTrue($editor->fresh()->isEditor());
    }

    public function test_el_lector_no_puede_cambiar_roles_ni_el_suyo_ni_el_de_otros(): void
    {
        $lector = $this->lector();
        $otroLector = $this->lector();

        $this->actingAs($lector)
            ->patch(route('usuarios.rol.actualizar', $otroLector), [
                'role' => User::ROLE_EDITOR,
            ])
            ->assertForbidden();

        $this->actingAs($lector)
            ->patch(route('usuarios.rol.actualizar', $lector), [
                'role' => User::ROLE_EDITOR,
            ])
            ->assertForbidden();

        $this->assertTrue($otroLector->fresh()->isLector());
        $this->assertTrue($lector->fresh()->isLector());
    }

    public function test_el_administrador_promueve_un_lector_verificado_y_este_accede_al_panel(): void
    {
        $administrador = $this->administrador();
        $lector = $this->lector();

        $this->actingAs($administrador)
            ->patch(route('usuarios.rol.actualizar', $lector), [
                'role' => User::ROLE_EDITOR,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('exito');

        $this->assertTrue($lector->fresh()->isEditor());

        $this->actingAs($lector->fresh())
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_el_administrador_puede_degradar_un_editor_a_lector(): void
    {
        $administrador = $this->administrador();
        $editor = $this->editor();

        $this->actingAs($administrador)
            ->patch(route('usuarios.rol.actualizar', $editor), [
                'role' => User::ROLE_LECTOR,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('exito');

        $this->assertTrue($editor->fresh()->isLector());
    }

    public function test_el_administrador_no_puede_promover_cuentas_sin_correo_verificado(): void
    {
        $administrador = $this->administrador();
        $sinVerificar = $this->lector(verificado: false);

        foreach ([User::ROLE_EDITOR, User::ROLE_ADMIN] as $rol) {
            $this->actingAs($administrador)
                ->from(route('usuarios.index'))
                ->patch(route('usuarios.rol.actualizar', $sinVerificar), [
                    'role' => $rol,
                ])
                ->assertRedirect(route('usuarios.index'))
                ->assertSessionHasErrors('role');

            $this->assertTrue($sinVerificar->fresh()->isLector());
        }
    }

    public function test_el_ultimo_administrador_no_puede_degradarse_a_lector(): void
    {
        $administrador = $this->administrador();

        $this->actingAs($administrador)
            ->from(route('usuarios.index'))
            ->patch(route('usuarios.rol.actualizar', $administrador), [
                'role' => User::ROLE_LECTOR,
            ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHasErrors('role');

        $this->assertTrue($administrador->fresh()->isAdministrador());
    }

    /* ------------------------------- Visitante ------------------------------- */

    public function test_el_visitante_sigue_yendo_al_login_en_las_rutas_del_panel(): void
    {
        foreach ([
            route('dashboard'),
            route('noticias.index'),
            route('eventos.index'),
            route('autores.index'),
            route('categorias.index'),
            route('usuarios.index'),
        ] as $ruta) {
            $this->get($ruta)->assertRedirect(route('login'));
        }
    }
}
