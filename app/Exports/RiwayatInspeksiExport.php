<?php

namespace App\Exports;

use App\Models\AparInspection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RiwayatInspeksiExport implements FromCollection, WithStyles, WithHeadings, WithEvents, ShouldAutoSize
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = AparInspection::with(['masterApar.gedung', 'user', 'details'])
            ->orderBy('date', 'desc');

        // Terapkan logika filter tanggal
        if ($this->request->filled(['start_date', 'end_date'])) {
            $startDate = Carbon::parse($this->request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($this->request->input('end_date'))->endOfDay();
            
            $query->whereBetween('date', [$startDate, $endDate]);
        }
        
        // Terapkan logika pencarian
        if ($this->request->filled('q')) {
            $search = $this->request->input('q');
            $query->where(function ($q) use ($search) {
                $q->whereHas('masterApar', function ($qApar) use ($search) {
                    $qApar->where('kode', 'like', "%{$search}%")
                            ->orWhere('lokasi', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function ($qUser) use ($search) {
                    $qUser->where('name', 'like', "%{$search}%");
                });
            });
        }
        
        $inspections = $query->get();
        
        // Kembalikan data tanpa baris judul
        return $inspections->map(function ($row) {
            $detailsString = $row->details->map(function ($detail) {
                $statusText = 'Tidak Ada';
                if ($detail->value == 'B') {
                    $statusText = 'Baik';
                } elseif ($detail->value == 'R') {
                    $statusText = 'Rusak';
                } elseif ($detail->value == 'Over') {
                    $statusText = 'Over Pressure';
                } elseif ($detail->value == 'Low') {
                    $statusText = 'Low Pressure';
                }
                
                $remark = $detail->remark ?: '-';
                
                return "{$detail->itemCheck->name}: {$statusText} ({$remark})";
            })->implode(', ');
            
            return [
                $row->masterApar->kode,
                $row->masterApar->gedung->nama . ' - ' . $row->masterApar->lokasi,
                $row->user->name,
                Carbon::parse($row->date)->translatedFormat('d F Y'),
                $row->status,
                $detailsString,
                $row->final_foto_path,
            ];
        });
    }
    
    // Menentukan judul kolom
    public function headings(): array
    {
        return [
            'Kode APAR',
            'Lokasi',
            'Petugas',
            'Tanggal',
            'Status',
            'Hasil Inspeksi',
            'Foto Akhir',
        ];
    }
    
    // Menambahkan styling pada Excel
    public function styles(Worksheet $sheet)
    {
        // Gaya untuk baris kedua (headings)
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE5E7EB'],
            ],
        ]);
        
        // Gaya untuk baris data
        $sheet->getStyle('A3:G' . ($sheet->getHighestRow()))->getAlignment()->setWrapText(true);
        $sheet->getStyle('A3:G' . ($sheet->getHighestRow()))->getAlignment()->setVertical('top');
        
        // Set lebar kolom
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(50);
        $sheet->getColumnDimension('G')->setWidth(25);
    }
    
    // Menangani event setelah spreadsheet dibuat untuk menyematkan gambar
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Tambahkan baris judul di atas header
                $startDate = $this->request->filled('start_date') ? Carbon::parse($this->request->input('start_date'))->translatedFormat('d F Y') : 'N/A';
                $endDate = $this->request->filled('end_date') ? Carbon::parse($this->request->input('end_date'))->translatedFormat('d F Y') : 'N/A';
                $titleText = 'Laporan Riwayat Inspeksi APAR: ' . $startDate . ' - ' . $endDate;
                
                // Sisipkan baris baru di atas baris header (baris 1)
                $sheet->insertNewRowBefore(1, 1);
                
                // Setel nilai sel judul dan gabungkan sel-selnya
                $sheet->setCellValue('A1', $titleText);
                $sheet->mergeCells('A1:G1');

                // Proses penyematan gambar, dimulai dari baris ke-3
                $highestRow = $sheet->getHighestRow();
                $startRow = 3;

                for ($row = $startRow; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell('G' . $row)->getValue();
                    
                    if (!empty($cellValue) && Storage::disk('public')->exists($cellValue)) {
                        $imagePath = Storage::disk('public')->path($cellValue);
                        
                        $sheet->getRowDimension($row)->setRowHeight(150);

                        $drawing = new Drawing();
                        $drawing->setName('Final Photo');
                        $drawing->setDescription('Final APAR Inspection Photo');
                        $drawing->setPath($imagePath);
                        $drawing->setHeight(150);
                        $drawing->setCoordinates('G' . $row);
                        $drawing->setWorksheet($sheet);
                    }
                }
            },
        ];
    }
}
