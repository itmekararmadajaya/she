<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public $start_date;
    public $end_date;

    public function __construct() {
        $this->start_date = Carbon::now()->startOfMonth()->toDateString();
        $this->end_date = Carbon::now()->toDateString();
    }

    public function index(Request $request) {

        if($request->filled('start_date')){
            $this->start_date = $request->start_date;
        }

        if($request->filled('end_date')){
            $this->end_date = $request->end_date;
        }

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
        $data = [5, 8, 3, 7, 6, 9]; // Jumlah inspeksi tiap bulan

        return view('pages.dashboard', [
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'labels' => $labels,
            'data' => $data
        ]);
    }
}
