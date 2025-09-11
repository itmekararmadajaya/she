<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JenisPemadam;
use App\Models\JenisIsi;

class JenisSeeder extends Seeder
{
    /**
     * Jalankan database seeds.
     */
    public function run(): void
    {
        // Masukkan data ke tabel jenis_pemadams menggunakan model
        JenisPemadam::create(['jenis_pemadam' => 'APAR']);
        JenisPemadam::create(['jenis_pemadam' => 'APAB']);

        // Masukkan data ke tabel jenis_isis menggunakan model
        JenisIsi::create(['jenis_isi' => 'POWDER']);
        JenisIsi::create(['jenis_isi' => 'CO2']);
        JenisIsi::create(['jenis_isi' => 'FOAM']);
        JenisIsi::create(['jenis_isi' => 'HCFC']);
    }
}
