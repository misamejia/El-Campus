<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columnas para las funciones de IA de las noticias:
     * puntos clave, etiquetas sugeridas, traducción del titular y el
     * embedding (vector) usado para calcular noticias relacionadas.
     */
    public function up(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->json('puntos_clave')->nullable()->after('resumen');
            $table->json('etiquetas_ia')->nullable()->after('puntos_clave');
            $table->string('traduccion_titulo')->nullable()->after('etiquetas_ia');
            $table->string('traduccion_idioma', 16)->nullable()->after('traduccion_titulo');
            $table->json('embedding')->nullable()->after('traduccion_idioma');
            $table->timestamp('embedding_actualizado_en')->nullable()->after('embedding');
        });
    }

    public function down(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->dropColumn([
                'puntos_clave',
                'etiquetas_ia',
                'traduccion_titulo',
                'traduccion_idioma',
                'embedding',
                'embedding_actualizado_en',
            ]);
        });
    }
};
