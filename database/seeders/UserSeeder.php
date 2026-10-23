<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Usuarios demo, uno por rol, para desarrollo y pruebas locales.
     *
     * Se crean con la contraseña "demo123" (el cast "hashed" del modelo la
     * cifra al guardar) y con el correo verificado para poder iniciar sesión
     * de inmediato. Es idempotente: si el usuario ya existe, lo actualiza.
     *
     * En producción no se ejecuta: las cuentas reales se crean a mano para no
     * dejar credenciales conocidas ni roles de demostración.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            logger()->warning('UserSeeder omitido en producción: crea las cuentas reales manualmente.');

            return;
        }

        $this->crear('admin@elcampus.com', 'Administrador', User::ROLE_ADMIN);
        $this->crear('editor@elcampus.com', 'Editor', User::ROLE_EDITOR);
        $this->crear('lector@elcampus.com', 'Lector', User::ROLE_LECTOR);
    }

    /**
     * Crea (o actualiza) un usuario con el rol indicado.
     */
    private function crear(string $email, string $name, string $codigoRol): void
    {
        $user = User::firstOrNew(['email' => $email]);

        $user->forceFill([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => 'demo123',
            'role_id' => Rol::where('codigo', $codigoRol)->value('id'),
        ]);

        $user->save();
    }
}
