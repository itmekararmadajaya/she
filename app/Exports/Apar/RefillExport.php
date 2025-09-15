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
     * Mengambil koleksi data APAR yang sudah atau akan kadaluarsa.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $today = Carbon::now();
        $thirtyDaysFromNow = $today->copy()->addDays(30);

        return MasterApar::with('gedung')
            ->where(function ($query) use ($today, $thirtyDaysFromNow) {
                $query->whereDate('tgl_kadaluarsa', '<', $today)
                      ->orWhereDate('tgl_kadaluarsa', '<=', $thirtyDaysFromNow);
            })
            ->orderBy('tgl_kadaluarsa', 'asc')
            ->get();
    }

    /**
     * Menentukan judul kolom untuk file Excel.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Kode APAR',
            'Lokasi',
            'Lokasi',
            'Tanggal Kadaluarsa',
            'Status',
        ];
    }

    /**
     * Memetakan data dari setiap baris APAR ke dalam format yang diinginkan untuk ekspor.
     *
     * @param mixed $apar
     * @return array
     */
    public function map($apar): array
    {
        static $index = 1;

        // Menentukan status berdasarkan tanggal kadaluarsa
        $tglKadaluarsa = Carbon::parse($apar->tgl_kadaluarsa);
        $status = 'H-30'; // Default status
        if ($tglKadaluarsa->isPast()) {
            $status = 'EXP';
        }

        return [
            $index++,
            $apar->kode,
            $apar->gedung->nama,
            $apar->lokasi,
            $tglKadaluarsa->translatedFormat('d F Y'),
            $status,
        ];
    }
}
