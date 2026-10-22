<?php

namespace Database\Seeders;

use App\Models\Organizador;
use Illuminate\Database\Seeder;

class OrganizadorSeeder extends Seeder
{
    /**
     * Dependencias que organizan eventos en la agenda universitaria.
     *
     * Se utiliza firstOrCreate sobre el nombre: el seeder es reproducible.
     */
    public function run(): void
    {
        $organizadores = [
            'Vicerrectoría de Investigación',
            'Bienestar Universitario',
            'Área Deportiva',
            'Equipo de Investigación',
            'Oficina de Egresados',
            'Sección Cultural',
            'Facultad de Ingenierías',
        ];

        foreach ($organizadores as $nombre) {
            Organizador::firstOrCreate(
                ['nombre' => $nombre],
                ['activo' => true]
            );
        }
    }
}
