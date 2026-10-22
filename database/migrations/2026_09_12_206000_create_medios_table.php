<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Imágenes subidas o incluidas con el sitio.
     *
     * "ruta" es la ruta relativa dentro del disco de Laravel; "disco" por
     * defecto es "public". Se guardan metadatos (mime, tamaño, dimensiones)
     * y el texto alternativo para accesibilidad.
     */
    public function up(): void
    {
        Schema::create('medios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disco', 20)->default('public');
            $table->string('ruta', 500);
            $table->string('tipo_mime', 50);
            $table->unsignedInteger('tamano_bytes');
            $table->unsignedSmallInteger('ancho')->nullable();
            $table->unsignedSmallInteger('alto')->nullable();
            $table->string('texto_alternativo', 255)->nullable();
            $table->timestamps();

            $table->unique(['disco', 'ruta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medios');
    }
};
