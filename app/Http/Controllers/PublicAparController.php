<?php

namespace App\Http\Controllers;

use App\Models\MasterApar;
use Illuminate\Http\Request;

class PublicAparController extends Controller
{
    public function showPublicHistory($kode)
    {
        \Log::info("Mengakses riwayat publik untuk APAR dengan kode: {$kode}");
        
        // Tambahkan 'penggunaan' ke dalam relasi yang dimuat
        $apar = MasterApar::with(['gedung', 'jenisIsi', 'jenisPemadam', 'inspections.user', 'inspections.details.itemCheck', 'penggunaan'])
            ->where('kode', $kode)
            ->firstOrFail();
        
        // Urutkan inspeksi berdasarkan tanggal terbaru
        $apar->setRelation('inspections', $apar->inspections->sortByDesc('date'));

        \Log::info("Data APAR ditemukan: ", ['apar' => $apar->toArray()]);
        
        return view('public.apar.history', compact('apar'));
    }
}