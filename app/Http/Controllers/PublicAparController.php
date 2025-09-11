<?php

namespace App\Http\Controllers;

use App\Models\MasterApar;
use Illuminate\Http\Request;

class PublicAparController extends Controller
{
    public function showPublicHistory($kode)
    {
        \Log::info("Mengakses riwayat publik untuk APAR dengan kode: {$kode}");
        $apar = MasterApar::with(['gedung', 'inspections.user', 'inspections.details.itemCheck'])
            ->where('kode', $kode)
            ->firstOrFail();

        \Log::info("Data APAR ditemukan: ", ['apar' => $apar->toArray()]);
        return view('public.apar.history', compact('apar'));
    }
}