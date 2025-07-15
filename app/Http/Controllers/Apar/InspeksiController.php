<?php

namespace App\Http\Controllers\Apar;

use App\Http\Controllers\Controller;
use App\Models\AparInspection;
use App\Models\AparInspectionDetail;
use App\Models\ItemCheck;
use App\Models\MasterApar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InspeksiController extends Controller
{
    public $codeApar = "";
    public $apar = [];

    public function index(Request $request) {
        if ($request->filled('kode_apar')) {
            $this->codeApar = $request->kode_apar;
            $this->apar = MasterApar::where('kode', $this->codeApar)->first();
        }
        
        $itemChecks = ItemCheck::all();

        return view('pages.apar.inspeksi.inspeksi', [
            'codeApar' => $this->codeApar,
            'apar' => $this->apar,
            'itemChecks' => $itemChecks
        ]);
    }

    public function inspeksi(Request $request){
            $request->validate([
                'kode' => 'required|string',
                'checks' => 'required|array',
                'checks.*.item_check_id' => 'required|exists:item_checks,id',
                'checks.*.value' => 'required|in:B,R,T/A',
                'checks.*.remark' => 'nullable|string',
            ]);

            DB::beginTransaction();
            try {
                $apar = MasterApar::where('kode', $request->kode)->firstOrFail();

                $aparInspection = AparInspection::create([
                    'master_apar_id' => $apar->id,
                    'user_id' => Auth::user()->id,
                    'date' => Carbon::now()
                ]);

                foreach($request->checks as $check){
                    AparInspectionDetail::create([
                        'apar_inspection_id' => $aparInspection->id,
                        'item_check_id' => $check['item_check_id'],
                        'value' => $check['value'],
                        'remark' => $check['remark'],
                    ]);
                }
                DB::commit();

                return redirect()->route('apar.index')->with('success', 'Inspeksi berhasil disimpan');
            } catch (\Throwable $th) {
                DB::rollBack();
                Log::info($th->getMessage());

                return redirect()->route('apar.index')->with('error', 'Terjadi kesalahan saat menyimpan hasil inspeksi, silahkan hubungi IT');
            }
    }
}
