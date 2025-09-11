<?php

namespace Database\Seeders;

// Gunakan kelas seeder yang Anda buat
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan database seeds.
     */
    public function run(): void
    {
        // Panggil seeder lain yang ingin Anda jalankan
        $this->call([
            JenisSeeder::class,
            UserSeeder::class,
            DataSeeder::class,
        ]);
    }
}
