<?php

namespace Database\Seeders;

use App\Models\ItemCheck;
use App\Models\Kebutuhan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DataSeeder extends Seeder
{
    /**
     * Jalankan database seeds.
     */
    public function run(): void
    {
        // Masukkan data ke tabel item_checks
        $itemChecks = [
            'TABUNG',
            'PIN',
            'SEGEL PIN',
            'SELANG',
            'NOZZLE',
            'TEKANAN',
            'BRACKET/BOX',
            'TANDA LOKASI',
            'TANDA BARCODE',
        ];

        foreach ($itemChecks as $itemCheck) {
            ItemCheck::firstOrCreate(['name' => $itemCheck]);
        }

        // Masukkan data ke tabel kebutuhans
        $kebutuhans = [
            'Beli Baru',
            'Isi Ulang',
            'Ganti Komponen',
        ];

        foreach ($kebutuhans as $kebutuhan) {
            Kebutuhan::firstOrCreate(['kebutuhan' => $kebutuhan]);
        }
    }
}
