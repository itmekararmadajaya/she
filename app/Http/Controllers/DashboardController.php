<?php

namespace App\Http\Controllers;

use App\Models\AparInspection;
use App\Models\Gedung;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public $start_date;
    public $end_date;
    public $month;

    public function __construct() {
        $this->start_date = Carbon::now()->startOfMonth()->toDateString();
        $this->end_date = Carbon::now()->toDateString();
        $this->month = Carbon::now()->format('Y-m');
    }

    public function index(Request $request) {
        if($request->filled('month')){
            $this->month = $request->month;
            $this->start_date = Carbon::parse($this->month.'-01')->startOfMonth()->toDateString();
            $this->end_date = Carbon::parse($this->month.'-01')->endOfMonth()->toDateString();
        }

        return view('pages.dashboard', [
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'month' => $this->month,
            'generateGraphAparPerGedung' => $this->generateGraphAparPerGedung()
        ]);
    }

    public function generateGraphAparPerGedung(){
        $gedungs = Gedung::get();

        $aparInspections = AparInspection::
            select('apar_inspections.*', 'master_apars.gedung_id')
            ->leftJoin('master_apars', 'master_apars.id', '=', 'apar_inspections.master_apar_id')
            ->whereBetween('date', [$this->start_date, $this->end_date])->get();

        $data = [];
        foreach($gedungs as $gedung){
            $data[] = [
                'gedung' => $gedung->nama,
                'OK' => $aparInspections->where('gedung_id', $gedung->id)->where('status', 'OK')->count(),
                'NOK' => $aparInspections->where('gedung_id', $gedung->id)->where('status', 'NOK')->count(),
            ];
        }
        
        return $data;
    }
}
