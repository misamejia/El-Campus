<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategoriaSeeder::class,
            AutorSeeder::class,
            OrganizadorSeeder::class,
            NoticiaSeeder::class,
            EventoSeeder::class,
            UserSeeder::class,
        ]);
    }
}
