<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La traducción con IA se retiró: el navegador del lector cubre ese caso.
     */
    public function up(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->dropColumn(['traduccion_titulo', 'traduccion_contenido', 'traduccion_idioma']);
        });
    }

    public function down(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->string('traduccion_titulo')->nullable();
            $table->longText('traduccion_contenido')->nullable();
            $table->string('traduccion_idioma', 16)->nullable();
        });
    }
};
