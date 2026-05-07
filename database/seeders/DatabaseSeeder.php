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
        // Panggil UserSeeder agar data akun default ikut dibuat
        $this->call([
            UserSeeder::class,
        ]);
    }
}
