<?php

namespace App\Exports\Apar;

use App\Models\AparInspection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RusakExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public $start_date;
    public $end_date;

    public function __construct($start_date, $end_date) {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return AparInspection::
                with(['masterApar.gedung', 'user', 'details'])
                ->whereBetween('date', [$this->start_date, $this->end_date])
                ->whereHas('details', function ($q) {
                    $q->where('value', '!=', 'B');
                })
                ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode APAR',
            'Gedung',
            'Lokasi',
            'Tanggal',
            'User',
        ];
    }

    public function map($inspection): array
    {
        static $index = 1;

        return [
            $index++,
            optional($inspection->masterApar)->kode,
            optional($inspection->masterApar->gedung)->nama,
            optional($inspection->masterApar)->lokasi,
            $inspection->dateFormatted,
            optional($inspection->user)->name,
        ];
    }
}
