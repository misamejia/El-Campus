<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogos de estados (ciclo editorial de noticias y agenda de eventos).
     *
     * "es_publico" indica si el estado es visible en el sitio público.
     * El contenido es dato de referencia fijo, por eso se inserta aquí.
     */
    public function up(): void
    {
        Schema::create('estados_noticia', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 60);
            $table->boolean('es_publico')->default(false);
            $table->unsignedTinyInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('estados_evento', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 60);
            $table->boolean('es_publico')->default(false);
            $table->unsignedTinyInteger('orden')->default(0);
            $table->timestamps();
        });

        $ahora = now();

        DB::table('estados_noticia')->insert([
            ['id' => 1, 'codigo' => 'borrador', 'nombre' => 'Borrador', 'es_publico' => false, 'orden' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['id' => 2, 'codigo' => 'publicada', 'nombre' => 'Publicada', 'es_publico' => true, 'orden' => 2, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['id' => 3, 'codigo' => 'archivada', 'nombre' => 'Archivada', 'es_publico' => false, 'orden' => 3, 'created_at' => $ahora, 'updated_at' => $ahora],
        ]);

        DB::table('estados_evento')->insert([
            ['id' => 1, 'codigo' => 'programado', 'nombre' => 'Programado', 'es_publico' => true, 'orden' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['id' => 2, 'codigo' => 'realizado', 'nombre' => 'Realizado', 'es_publico' => true, 'orden' => 2, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['id' => 3, 'codigo' => 'cancelado', 'nombre' => 'Cancelado', 'es_publico' => false, 'orden' => 3, 'created_at' => $ahora, 'updated_at' => $ahora],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('estados_evento');
        Schema::dropIfExists('estados_noticia');
    }
};
