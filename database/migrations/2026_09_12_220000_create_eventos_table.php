<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agenda de eventos universitarios.
     *
     * fecha_inicio es obligatoria; fecha_fin es opcional y, cuando existe,
     * debe ser igual o posterior (validado en la aplicación).
     */
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('organizador_id')->nullable();
            $table->unsignedTinyInteger('estado_id')->default(1);
            $table->foreignId('imagen_id')->nullable();
            $table->foreignId('creado_por')->nullable();
            $table->foreignId('actualizado_por')->nullable();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->text('descripcion');
            $table->string('lugar');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin')->nullable();
            $table->timestamps();

            $table->foreign('organizador_id')->references('id')->on('organizadores')->onUpdate('cascade');
            $table->foreign('estado_id')->references('id')->on('estados_evento')->onUpdate('cascade');
            $table->foreign('imagen_id')->references('id')->on('medios')->nullOnDelete();
            $table->foreign('creado_por')->references('id')->on('users')->nullOnDelete();
            $table->foreign('actualizado_por')->references('id')->on('users')->nullOnDelete();

            $table->index(['estado_id', 'fecha_inicio', 'id']);
            $table->index('organizador_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
