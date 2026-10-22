<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dependencias que organizan eventos (normaliza el texto libre).
     */
    public function up(): void
    {
        Schema::create('organizadores', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 200)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizadores');
    }
};
