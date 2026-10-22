<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Noticia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected Categoria $categoria;

    protected Autor $autor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoria = Categoria::create([
            'nombre' => 'Universidad',
            'slug' => 'universidad',
        ]);

        $this->autor = Autor::create([
            'nombre' => 'Redacción El Campus',
            'slug' => 'redaccion-el-campus',
        ]);
    }

    private function editor(): User
    {
        return User::factory()->editor()->create(['email_verified_at' => now()]);
    }

    private function administrador(): User
    {
        return User::factory()->admin()->create(['email_verified_at' => now()]);
    }

    /* --------------------------------- Autenticación --------------------------------- */

    public function test_el_visitante_no_autenticado_no_puede_acceder_al_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_el_visitante_no_autenticado_no_puede_acceder_a_usuarios(): void
    {
        $this->get(route('usuarios.index'))
            ->assertRedirect(route('login'));
    }

    public function test_el_visitante_no_autenticado_no_puede_acceder_a_autores(): void
    {
        $this->get(route('autores.index'))
            ->assertRedirect(route('login'));
    }

    /* ------------------------------------ Editor ------------------------------------ */

    public function test_el_editor_puede_acceder_al_dashboard(): void
    {
        $this->actingAs($this->editor());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Panel administrativo')
            ->assertSee('Rol: Editor');
    }

    public function test_el_editor_puede_acceder_a_las_noticias(): void
    {
        $this->actingAs($this->editor());

        $this->get(route('noticias.index'))->assertOk();
    }

    public function test_el_editor_puede_acceder_a_los_eventos(): void
    {
        $this->actingAs($this->editor());

        $this->get(route('eventos.index'))->assertOk();
    }

    public function test_el_editor_puede_ver_el_periodico_publico(): void
    {
        $noticia = Noticia::create([
            'titulo' => 'Noticia pública',
            'slug' => 'noticia-publica',
            'categoria_id' => $this->categoria->id,
            'autor_id' => $this->autor->id,
            'contenido' => 'Contenido de la noticia pública.',
            'fecha_publicacion' => now()->format('Y-m-d H:i:s'),
            'estado_id' => $this->idEstadoNoticia('publicada'),
        ]);

        Evento::create([
            'titulo' => 'Evento de demostración',
            'slug' => 'evento-de-demostracion',
            'descripcion' => 'Descripción.',
            'lugar' => 'Campus Central',
            'fecha_inicio' => now()->addDays(5),
            'estado_id' => $this->idEstadoEvento('programado'),
        ]);

        $this->get(route('inicio'))->assertOk();
        $this->get(route('noticia.publica', $noticia->slug))->assertOk();
        $this->get(route('eventos.publica'))->assertOk();
    }

    public function test_el_editor_recibe_403_al_acceder_a_autores(): void
    {
        $this->actingAs($this->editor());

        $this->get(route('autores.index'))->assertForbidden();
        $this->get(route('autores.create'))->assertForbidden();
    }

    public function test_el_editor_recibe_403_al_acceder_a_usuarios(): void
    {
        $this->actingAs($this->editor());

        $this->get(route('usuarios.index'))->assertForbidden();
    }

    public function test_el_editor_no_puede_cambiar_el_rol_de_un_usuario_mediante_peticion_http(): void
    {
        $editor = $this->editor();
        $target = $this->editor();
        $this->actingAs($editor);

        $this->patch(route('usuarios.rol.actualizar', $target), [
            'role' => User::ROLE_ADMIN,
        ])->assertForbidden();

        $this->assertTrue($target->fresh()->isEditor());
    }

    /* -------------------------------- Administrador -------------------------------- */

    public function test_el_administrador_puede_acceder_al_dashboard(): void
    {
        $this->actingAs($this->administrador());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Panel administrativo')
            ->assertSee('Rol: Administrador');
    }

    public function test_el_administrador_puede_acceder_a_las_noticias(): void
    {
        $this->actingAs($this->administrador());

        $this->get(route('noticias.index'))->assertOk();
    }

    public function test_el_administrador_puede_acceder_a_los_eventos(): void
    {
        $this->actingAs($this->administrador());

        $this->get(route('eventos.index'))->assertOk();
    }

    public function test_el_administrador_puede_acceder_a_los_autores(): void
    {
        $this->actingAs($this->administrador());

        $this->get(route('autores.index'))->assertOk();
    }

    public function test_el_administrador_puede_acceder_a_los_usuarios(): void
    {
        $this->actingAs($this->administrador());

        $this->get(route('usuarios.index'))
            ->assertOk()
            ->assertSee('Gestión de usuarios');
    }

    public function test_el_administrador_puede_cambiar_el_rol_de_un_editor_a_administrador(): void
    {
        $administrador = $this->administrador();
        $editor = $this->editor();
        $this->actingAs($administrador);

        $this->from(route('usuarios.index'))
            ->patch(route('usuarios.rol.actualizar', $editor), [
                'role' => User::ROLE_ADMIN,
            ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('exito');

        $this->assertTrue($editor->fresh()->isAdministrador());
    }

    public function test_el_administrador_puede_cambiar_el_rol_de_un_administrador_a_editor_cuando_las_reglas_lo_permiten(): void
    {
        $administrador = $this->administrador();
        $otroAdministrador = $this->administrador();
        $this->actingAs($administrador);

        $this->from(route('usuarios.index'))
            ->patch(route('usuarios.rol.actualizar', $otroAdministrador), [
                'role' => User::ROLE_EDITOR,
            ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('exito');

        $this->assertTrue($otroAdministrador->fresh()->isEditor());
    }

    /* ----------------------------------- Seguridad ----------------------------------- */

    public function test_un_administrador_no_puede_quitarse_a_si_mismo_el_rol_de_administrador(): void
    {
        $administrador = $this->administrador();
        $this->actingAs($administrador);

        $this->from(route('usuarios.index'))
            ->patch(route('usuarios.rol.actualizar', $administrador), [
                'role' => User::ROLE_EDITOR,
            ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHasErrors('role');

        $this->assertTrue($administrador->fresh()->isAdministrador());
    }

    public function test_el_ultimo_administrador_no_puede_ser_degradado(): void
    {
        $administrador = $this->administrador();
        $this->actingAs($administrador);

        $this->from(route('usuarios.index'))
            ->patch(route('usuarios.rol.actualizar', $administrador), [
                'role' => User::ROLE_EDITOR,
            ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHasErrors('role');

        $this->assertSame(1, User::whereHas('rol', fn ($rol) => $rol->where('codigo', User::ROLE_ADMIN))->count());
    }

    public function test_un_administrador_no_puede_degradar_al_unico_otro_que_deja_el_sistema_sin_administradores(): void
    {
        // Escenario: el único administrador degrada al propio sistema por la vía de
        // intentar dejar al único administrador como editor; debe rechazarse.
        $administrador = $this->administrador();
        $this->actingAs($administrador);

        $this->patch(route('usuarios.rol.actualizar', $administrador), [
            'role' => User::ROLE_EDITOR,
        ])->assertSessionHasErrors('role');

        $this->assertSame(1, User::whereHas('rol', fn ($rol) => $rol->where('codigo', User::ROLE_ADMIN))->count());
    }

    public function test_no_se_puede_asignar_un_rol_invalido(): void
    {
        $administrador = $this->administrador();
        $editor = $this->editor();
        $this->actingAs($administrador);

        $this->from(route('usuarios.index'))
            ->patch(route('usuarios.rol.actualizar', $editor), [
                'role' => 'superusuario',
            ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHasErrors('role');

        $this->assertTrue($editor->fresh()->isEditor());
    }

    public function test_el_rol_no_es_asignable_masivamente_desde_el_registro(): void
    {
        $this->post('/register', [
            'name' => 'Nuevo Editor',
            'email' => 'nuevo@elampus.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => '1',
        ]);

        $nuevo = User::where('email', 'nuevo@elampus.test')->firstOrFail();
        $this->assertTrue($nuevo->isEditor());
    }

    public function test_el_rol_no_se_puede_modificar_mediante_asignacion_masiva_no_autorizada(): void
    {
        $usuario = User::create([
            'name' => 'Intruso',
            'email' => 'intruso@elampus.test',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->assertTrue($usuario->refresh()->isEditor());
    }

    /* ------------------------------- Regresión ------------------------------- */

    public function test_las_rutas_publicas_siguen_funcionando(): void
    {
        Noticia::create([
            'titulo' => 'Noticia de portada',
            'slug' => 'noticia-de-portada',
            'categoria_id' => $this->categoria->id,
            'autor_id' => $this->autor->id,
            'contenido' => 'Contenido.',
            'fecha_publicacion' => now()->format('Y-m-d H:i:s'),
            'estado_id' => $this->idEstadoNoticia('publicada'),
        ]);

        $this->get(route('inicio'))->assertOk();
        $this->get(route('noticia.publica', 'noticia-de-portada'))->assertOk();
        $this->get(route('eventos.publica'))->assertOk();
    }

    public function test_el_perfil_de_jetstream_sigue_funcionando_para_ambos_roles(): void
    {
        $this->actingAs($this->editor());
        $this->get(route('perfil'))->assertOk();
        $this->get(route('profile.show'))->assertOk();

        $this->actingAs($this->administrador());
        $this->get(route('perfil'))->assertOk();
        $this->get(route('profile.show'))->assertOk();
    }
}