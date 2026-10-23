<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_siembra_un_usuario_demo_por_rol(): void
    {
        $this->seed(UserSeeder::class);

        $this->assertSame(3, User::count());

        $administrador = User::where('email', 'admin@elcampus.com')->firstOrFail();
        $editor = User::where('email', 'editor@elcampus.com')->firstOrFail();
        $lector = User::where('email', 'lector@elcampus.com')->firstOrFail();

        $this->assertTrue($administrador->isAdministrador());
        $this->assertTrue($editor->isEditor());
        $this->assertTrue($lector->isLector());

        foreach ([$administrador, $editor, $lector] as $usuario) {
            $this->assertTrue($usuario->hasVerifiedEmail());
            $this->assertTrue(Hash::check('demo123', $usuario->password));
        }
    }

    public function test_es_idempotente_y_restaura_el_rol_esperado(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(UserSeeder::class);

        $this->assertSame(3, User::count());

        User::where('email', 'editor@elcampus.com')->firstOrFail()->forceFill([
            'role_id' => User::ROL_LECTOR_ID,
        ])->save();

        $this->seed(UserSeeder::class);

        $this->assertTrue(
            User::where('email', 'editor@elcampus.com')->firstOrFail()->isEditor()
        );
    }

    public function test_no_se_ejecuta_en_produccion(): void
    {
        $this->app['env'] = 'production';

        $this->artisan('db:seed', [
            '--class' => UserSeeder::class,
            '--force' => true,
        ])->assertSuccessful();

        $this->assertSame(0, User::count());
    }
}
