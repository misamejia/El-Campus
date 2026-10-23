<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rol de solo lectura para las cuentas registradas públicamente.
     *
     * El registro abierto crea usuarios "lector"; los roles editor y
     * administrador solo se asignan desde la gestión de usuarios. El valor
     * por defecto de users.role_id se alinea con ese rol para que cualquier
     * alta que no lo indique explícitamente nazca sin permisos de panel.
     *
     * La migración es idempotente: puede ejecutarse sobre una base que ya
     * tenga el rol creado (por ejemplo tras un despliegue parcial).
     */
    public function up(): void
    {
        if (! DB::table('roles')->where('codigo', 'lector')->exists()) {
            if (DB::table('roles')->where('id', 3)->exists()) {
                throw new RuntimeException(
                    'No se puede crear el rol lector: el id 3 de roles ya está ocupado.'
                );
            }

            $ahora = now();

            DB::table('roles')->insert([
                'id' => 3,
                'codigo' => 'lector',
                'nombre' => 'Lector',
                'descripcion' => 'Consulta el periódico y su perfil; sin acceso al panel',
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('role_id')->default(3)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('role_id')->default(2)->change();
        });

        // Al revertir no puede quedar ninguna cuenta apuntando a un rol que
        // desaparece: los lectores pasan a editor, que era el valor anterior.
        DB::table('users')->where('role_id', 3)->update(['role_id' => 2]);
        DB::table('roles')->where('codigo', 'lector')->delete();
    }
};
