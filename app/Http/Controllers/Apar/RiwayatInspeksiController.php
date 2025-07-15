<?php

namespace App\Http\Controllers\Apar;

use App\Http\Controllers\Controller;
use App\Models\AparInspection;
use Illuminate\Http\Request;

class RiwayatInspeksiController extends Controller
{
    public function index(){
        $aparInspections = AparInspection::with('details')->latest()->paginate(10);
        return view('pages.riwayat_inspeksi.index', [
            'aparInspections' => $aparInspections
        ]);
    }
}
