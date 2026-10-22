<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Firmas de las noticias (no son usuarios del sistema).
     */
    public function up(): void
    {
        Schema::create('autores', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 150)->unique();
            $table->string('slug', 160)->unique();
            $table->string('descripcion', 500)->nullable();
            $table->string('email')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autores');
    }
};
