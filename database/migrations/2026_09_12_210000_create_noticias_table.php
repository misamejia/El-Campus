<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Noticias del periódico.
     *
     * Referencias: categoría (catálogo), autor (firma), estado (catálogo),
     * imagen (medios) y auditoría de creación/modificación (usuarios).
     */
    public function up(): void
    {
        Schema::create('noticias', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('categoria_id');
            $table->unsignedInteger('autor_id');
            $table->unsignedTinyInteger('estado_id')->default(1);
            $table->foreignId('imagen_id')->nullable();
            $table->foreignId('creado_por')->nullable();
            $table->foreignId('actualizado_por')->nullable();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->string('resumen', 500)->nullable();
            $table->mediumText('contenido');
            $table->boolean('destacada')->default(false);
            $table->dateTime('fecha_publicacion')->nullable();
            $table->timestamps();

            $table->foreign('categoria_id')->references('id')->on('categorias')->onUpdate('cascade');
            $table->foreign('autor_id')->references('id')->on('autores')->onUpdate('cascade');
            $table->foreign('estado_id')->references('id')->on('estados_noticia')->onUpdate('cascade');
            $table->foreign('imagen_id')->references('id')->on('medios')->nullOnDelete();
            $table->foreign('creado_por')->references('id')->on('users')->nullOnDelete();
            $table->foreign('actualizado_por')->references('id')->on('users')->nullOnDelete();

            $table->index(['estado_id', 'fecha_publicacion', 'id']);
            $table->index(['categoria_id', 'estado_id', 'fecha_publicacion']);
            $table->index(['destacada', 'estado_id', 'fecha_publicacion']);
            $table->index(['autor_id', 'fecha_publicacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('noticias');
    }
};
