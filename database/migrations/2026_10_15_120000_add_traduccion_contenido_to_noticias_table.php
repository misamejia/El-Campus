<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Versión traducida del cuerpo del artículo, para la edición bilingüe.
     * El titular traducido ya vive en `traduccion_titulo` y el idioma en
     * `traduccion_idioma`.
     */
    public function up(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->longText('traduccion_contenido')->nullable()->after('traduccion_idioma');
        });
    }

    public function down(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->dropColumn('traduccion_contenido');
        });
    }
};
