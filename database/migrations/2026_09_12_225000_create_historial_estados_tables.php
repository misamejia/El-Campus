<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bitácora de cambios de estado de noticias y eventos.
     *
     * estado_anterior_id es NULL en el alta del registro.
     */
    public function up(): void
    {
        Schema::create('historial_estados_noticia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noticia_id')->constrained('noticias')->cascadeOnDelete();
            $table->unsignedTinyInteger('estado_anterior_id')->nullable();
            $table->unsignedTinyInteger('estado_nuevo_id');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cambiado_en')->useCurrent();

            $table->foreign('estado_anterior_id')->references('id')->on('estados_noticia')->onUpdate('cascade');
            $table->foreign('estado_nuevo_id')->references('id')->on('estados_noticia')->onUpdate('cascade');

            $table->index(['noticia_id', 'cambiado_en']);
        });

        Schema::create('historial_estados_evento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->unsignedTinyInteger('estado_anterior_id')->nullable();
            $table->unsignedTinyInteger('estado_nuevo_id');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cambiado_en')->useCurrent();

            $table->foreign('estado_anterior_id')->references('id')->on('estados_evento')->onUpdate('cascade');
            $table->foreign('estado_nuevo_id')->references('id')->on('estados_evento')->onUpdate('cascade');

            $table->index(['evento_id', 'cambiado_en']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_estados_evento');
        Schema::dropIfExists('historial_estados_noticia');
    }
};
