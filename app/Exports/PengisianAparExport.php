<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class PengisianAparExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected $data;

    // Tambahkan constructor untuk menerima data dari controller
    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Gunakan data yang sudah disimpan di properti data
        return $this->data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No.',
            'Petugas',
            'Kode APAR',
            'Lokasi',
            'Tanggal Pengisian',
            'Alasan',
            'Status',
        ];
    }
    
    /**
     * @param mixed $item
     * @return array
     */
    public function map($item): array
    {
        return [
            $item->id,
            $item->user->name,
            $item->masterApar->kode,
            $item->gedung->nama . ' - ' . $item->lokasi,
            Carbon::parse($item->tanggal_penggunaan)->isoFormat('D MMMM YYYY'),
            $item->alasan,
            $item->status,
        ];
    }

    /**
     * Mendaftarkan event untuk menambahkan baris judul dan mengatur gaya.
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Perhatikan: bagian ini tetap mengambil dari request.
                // Jika ingin lebih robust, data tanggal bisa juga dikirim dari controller
                $startDate = request()->get('start_date');
                $endDate = request()->get('end_date');

                // Format tanggal untuk baris judul
                $startMonth = Carbon::parse($startDate)->isoFormat('MMMM YYYY');
                $endMonth = Carbon::parse($endDate)->isoFormat('MMMM YYYY');
                $title = "Periode: {$startMonth} - {$endMonth}";

                // Sisipkan baris baru di atas header
                $event->sheet->insertNewRowBefore(1, 1);
                // Set nilai untuk baris baru yang disisipkan
                $event->sheet->setCellValue('A1', $title);
                
                // Gabungkan sel dan atur gaya untuk baris judul
                $event->sheet->mergeCells('A1:G1');
                $event->sheet->getStyle('A1')->getFont()->setBold(true);

                // Atur ulang lebar kolom setelah penyisipan baris
                $event->sheet->getColumnDimension('A')->setWidth(8); // No.
                $event->sheet->getColumnDimension('B')->setWidth(20); // Petugas
                $event->sheet->getColumnDimension('C')->setWidth(20); // Kode APAR
                $event->sheet->getColumnDimension('D')->setWidth(40); // Lokasi
                $event->sheet->getColumnDimension('E')->setWidth(25); // Tanggal Pengisian
                $event->sheet->getColumnDimension('F')->setWidth(30); // Alasan
                $event->sheet->getColumnDimension('G')->setWidth(15); // Status
            },
        ];
    }
}
