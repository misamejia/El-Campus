<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Usuarios de demostración del panel administrativo.
     *
     * Se crean con la contraseña "demo123" (el cast "hashed" del modelo la
     * cifra al guardar) y con el correo verificado para poder iniciar sesión
     * de inmediato. Es idempotente: si el usuario ya existe, lo actualiza.
     */
    public function run(): void
    {
        // 1 = administrador, 2 = editor (ids definidos en la migración de roles).
        $this->crear('admin@elcampus.com', 'Administrador', 1);
        $this->crear('misaelenrique2400@gmail.com', 'misael', 2);
    }

    /**
     * Crea (o actualiza) un usuario con el rol indicado.
     */
    private function crear(string $email, string $name, int $roleId): void
    {
        $user = User::firstOrNew(['email' => $email]);

        $user->forceFill([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => 'demo123',
            'role_id' => $roleId,
        ]);

        $user->save();
    }
}
