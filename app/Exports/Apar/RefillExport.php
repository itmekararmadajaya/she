<?php

namespace App\Exports\Apar;

use App\Models\MasterApar;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RefillExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return MasterApar::with('gedung')->whereDate('tgl_refill', '<=', Carbon::now()->subYears(2))->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode APAR',
            'Gedung',
            'Lokasi',
            'Tanggal Refill',
        ];
    }

    public function map($apar): array
    {
        static $index = 1;

        return [
            $index++,
            $apar->kode,
            $apar->gedung->nama,
            $apar->lokasi,
            $apar->tgl_refill
        ];
    }
}
