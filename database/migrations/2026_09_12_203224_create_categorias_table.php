<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Secciones editoriales del periódico (menú de navegación público).
     */
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('nombre', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->string('descripcion', 500)->nullable();
            $table->boolean('activa')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['activa', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
