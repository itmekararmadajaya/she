<?php

namespace Database\Seeders;

use App\Models\Gedung;
use App\Models\ItemCheck;
use App\Models\MasterApar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class MasterAparSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Gedung::insert([
            ['nama' => 'Gedung A'],
            ['nama' => 'Gedung B'],
            ['nama' => 'Gedung C'],
            ['nama' => 'Gedung D'],
        ]);

        $gedungs = Gedung::all();

        foreach($gedungs as $gedung){
            for($i=0; $i<5; $i++){
                MasterApar::insert([
                    [
                        'kode' => random_int(1,100).$i+1,
                        'jenis_pemadam' => Arr::random(['APAR', 'APAB']),
                        'jenis_isi' => Arr::random(['Powder', 'CO2', 'HCFC']),
                        'ukuran' => random_int(1,10),
                        'satuan' => 'KG',
                        'gedung_id' => $gedung->id,
                        'lokasi' => 'Lantai 1 - Koridor',
                        'tgl_kadaluarsa' => $i % 2 == 0 ? now()->addMonth(2) : now()->subYear(1),
                        'tanda' => Arr::random(['Ada', 'Tidak Ada']),
                        'catatan' => null,
                        'tgl_refill' => $i % 2 == 0 ? now()->subMonths(6) : now()->subYear(2),
                        'keterangan' => 'Aktif',
                    ],
                ]);
            }
        }


        $items = ['TABUNG', 'PIN', 'SEGEL PIN', 'SELANG', 'NOZZEL', 'TEKANAN', 'BRACKET/BOX', 'TANDA LOKASI', 'TAMGGAL CHECK'];
        foreach ($items as $item) {
            ItemCheck::create([
                'name' => $item,
                'is_active' => true
            ]);
        }
    }
}
