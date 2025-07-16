<?php

namespace App\Http\Controllers\Laporan;

use App\Exports\Apar\RefillExport;
use App\Exports\Apar\RusakExport;
use App\Http\Controllers\Controller;
use App\Models\AparInspection;
use App\Models\AparInspectionDetail;
use App\Models\ItemCheck;
use App\Models\MasterApar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AparController extends Controller
{
    public $start_date;
    public $end_date;
    public $month;

    public function __construct() {
        $this->start_date = Carbon::now()->startOfMonth()->toDateString();
        $this->end_date = Carbon::now()->toDateString();
        $this->month = Carbon::now()->format('Y-m');
    }

    public function rusakIndex(Request $request){
        if($request->filled('month')){
            $this->month = $request->month;
            $this->start_date = Carbon::parse($this->month.'-01')->startOfMonth()->toDateString();
            $this->end_date = Carbon::parse($this->month.'-01')->endOfMonth()->toDateString();
        }

        $aparInspections = AparInspection::
                            whereBetween('date', [$this->start_date, $this->end_date])
                            ->whereHas('details', function ($q) {
                                $q->where('value', '!=', 'B');
                            })
                            ->get();

        return view('pages.laporan.apar.rusak', [
            'month' => $this->month,
            'aparInspections' => $aparInspections,
            'totalData' => $aparInspections->count()
        ]);
    }

    public function exportExcelRusak(Request $request){
        return Excel::download(new RusakExport($this->start_date, $this->end_date), 'laporan-apar-rusak-'.$this->month.'.xlsx');
    }

    public function refillIndex(Request $request){
        $aparPerluRefill = MasterApar::whereDate('tgl_refill', '<=', Carbon::now()->subYears(2))->get();

        return view('pages.laporan.apar.refill',[
            'month' => $this->month,
            'aparPerluRefill' => $aparPerluRefill,
            'totalData' => $aparPerluRefill->count()
        ]);
    }

    public function exportExcelRefill(Request $request){
        return Excel::download(new RefillExport, 'laporan-apar-refill-'.$this->month.'.xlsx');
    }

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
        
        return view('pages.laporan.apar.yearly', [
            'year' => $year,
            'kode' => $kode,
            'apar' => $apar,
        ]);
    }
}