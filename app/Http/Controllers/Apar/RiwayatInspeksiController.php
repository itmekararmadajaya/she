<?php

namespace App\Http\Controllers\Apar;

use App\Http\Controllers\Controller;
use App\Models\AparInspection;
use App\Models\AparInspectionDetail;
use Illuminate\Http\Request;

class RiwayatInspeksiController extends Controller
{
    public function index(){
        $aparInspections = AparInspection::with('details')->latest()->paginate(10);
        return view('pages.riwayat_inspeksi.index', [
            'aparInspections' => $aparInspections
        ]);
    }

    public function recycle(Request $request){
        $id = $request->detail_id;
        $detail = AparInspectionDetail::where('id', $id)->first();
        $detail->value = 'B';
        $detail->remark = '';
        $detail->save();
        
        return redirect()->route('riwayat-inspeksi')->with('success', 'Proses berhasil!');;
    }
}
