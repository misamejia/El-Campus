<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Roles de acceso al panel administrativo y su vínculo con los usuarios.
     *
     * Los roles son dato de referencia fijo, por eso se insertan aquí con
     * identificadores deterministas (1 = administrador, 2 = editor).
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 60);
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });

        $ahora = now();

        DB::table('roles')->insert([
            [
                'id' => 1,
                'codigo' => 'administrador',
                'nombre' => 'Administrador',
                'descripcion' => 'Acceso total: noticias, eventos, autores, categorías, usuarios y roles',
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'id' => 2,
                'codigo' => 'editor',
                'nombre' => 'Editor',
                'descripcion' => 'Gestiona noticias y eventos; sin acceso a autores, categorías ni usuarios',
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('role_id')->default(2)->after('id');
            $table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::dropIfExists('roles');
    }
};
