<?php

namespace App\Http\Controllers\Apar;

use App\Http\Controllers\Controller;
use App\Models\MasterApar;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function yearlyIndex(Request $request){
        $year = date('Y');
        if($request->filled('year')){
            $year = $request->year;
        }
        
        $kode = "";
        $apar = "";
        if($request->filled('kode')){
            $kode = $request->kode;
            $apar = MasterApar::where('kode', $kode)->first();
        }
        
        return view('pages.apar.laporan.yearly', [
            'year' => $year,
            'kode' => $kode,
            'apar' => $apar,
        ]);
    }
}
