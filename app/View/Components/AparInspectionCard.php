<?php

namespace App\View\Components;

use App\Models\AparInspectionDetail;
use App\Models\ItemCheck;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AparInspectionCard extends Component
{
    public $apar;
    public $year;
    public $finalData;
    public $currentMonth;

    public function __construct($apar, $year)
    {
        $this->apar = $apar;
        $this->year = $year;

        $this->currentMonth = now()->month;

        $this->generate();
    }

    public function generate(){
        // Ambil semua item check yang digunakan
        $itemChecks = ItemCheck::where('is_active', true)->get();

        $year = $this->year;
        $apar_id = $this->apar->id;
        $finalData = [];

        foreach ($itemChecks as $itemCheck) {
            $monthlyData = [];

            $nowMonth = now()->month;
            for ($month = 1; $month <= 12; $month++) {
                // Ambil detail inspeksi berdasarkan item_check_id dan bulan
                if($month > $nowMonth){
                    $status = "";
                    $remark = "";
                }else {
                    $details = AparInspectionDetail::where('item_check_id', $itemCheck->id)
                    ->whereHas('inspeksi', function ($query) use ($year, $month, $apar_id) {
                        $query->whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->where('master_apar_id', $apar_id);
                    })
                    ->first();

                    $status = empty($details) ? '-' : $details->value;
                    $remark = empty($details) ? '' : $details->remark;
                }

                $monthlyData[] = [
                    'bulan' => $month,
                    'value' => $status,
                    'remark' => $remark
                ];
            }
            
            $finalData[] = [
                'item_check' => $itemCheck->name, // ganti sesuai kolom di tabel
                'data' => $monthlyData,
            ];
        }

        $this->finalData = $finalData;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.apar-inspection-card');
    }
}
