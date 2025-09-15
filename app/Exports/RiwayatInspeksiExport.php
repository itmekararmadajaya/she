<?php

namespace App\Exports;

use App\Models\AparInspection;
use App\Models\ItemCheck;
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
    protected $itemChecks;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->itemChecks = ItemCheck::orderBy('urutan')->get();
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
        
        return $inspections->map(function ($row) {
            $rowData = [
                $row->masterApar->kode,
                $row->masterApar->gedung->nama . ' - ' . $row->masterApar->lokasi,
                $row->user->name,
                Carbon::parse($row->date)->translatedFormat('d F Y'),
                $row->status,
            ];
            
            // Tambahkan kolom untuk setiap item check
            foreach ($this->itemChecks as $itemCheck) {
                $detail = $row->details->where('item_check_id', $itemCheck->id)->first();
                
                $statusText = 'Tidak Ada';
                $remark = '';

                if ($detail) {
                    if ($detail->value == 'B') {
                        $statusText = 'Baik';
                    } elseif ($detail->value == 'R') {
                        $statusText = 'Rusak';
                    } elseif ($detail->value == 'Over') {
                        $statusText = 'Over Pressure';
                    } elseif ($detail->value == 'Low') {
                        $statusText = 'Low Pressure';
                    }
                    $remark = $detail->remark ?: '';
                }

                $rowData[] = $statusText . ($remark ? " ($remark)" : '');
            }

            // Tambahkan kolom foto dan nama foto
            $rowData[] = $row->final_foto_path;
            $rowData[] = pathinfo($row->final_foto_path, PATHINFO_BASENAME);

            return $rowData;
        });
    }
    
    // Menentukan judul kolom
    public function headings(): array
    {
        $mainHeadings = [
            'Kode APAR',
            'Lokasi',
            'Petugas',
            'Tanggal',
            'Status',
        ];

        // Ambil nama-nama item check dari database
        $itemCheckNames = $this->itemChecks->pluck('name')->toArray();
        
        // Gabungkan semua heading
        $allHeadings = array_merge($mainHeadings, $itemCheckNames, ['Foto', 'Nama Foto']);

        return [
            $allHeadings
        ];
    }
    
    // Menambahkan styling pada Excel
    public function styles(Worksheet $sheet)
    {
        // Gaya untuk baris heading
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE5E7EB'],
            ],
        ]);
        
        // Gaya untuk baris data
        $sheet->getStyle('A2:' . $sheet->getHighestColumn() . ($sheet->getHighestRow()))->getAlignment()->setWrapText(true);
        $sheet->getStyle('A2:' . $sheet->getHighestColumn() . ($sheet->getHighestRow()))->getAlignment()->setVertical('top');

        // Set lebar kolom dinamis
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(15);
        
        // Lebar untuk setiap kolom item check
        $itemCheckWidth = 15;
        $startColumnIndex = 6;
        foreach ($this->itemChecks as $index => $itemCheck) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColumnIndex + $index);
            $sheet->getColumnDimension($column)->setWidth($itemCheckWidth);
        }

        // Lebar untuk kolom Foto dan Nama Foto
        $photoColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColumnIndex + $this->itemChecks->count());
        $sheet->getColumnDimension($photoColumn)->setWidth(50);
        
        $nameColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColumnIndex + $this->itemChecks->count() + 1);
        $sheet->getColumnDimension($nameColumn)->setWidth(25);
    }
    
    // Menangani event setelah spreadsheet dibuat untuk menyematkan gambar
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Tambahkan baris judul di atas header
                $startDate = $this->request->filled('start_date') ? Carbon::parse($this->request->input('start_date'))->translatedFormat('d F Y') : '-';
                $endDate = $this->request->filled('end_date') ? Carbon::parse($this->request->input('end_date'))->translatedFormat('d F Y') : '-';
                $titleText = "Laporan Riwayat Inspeksi APAR: {$startDate} - {$endDate}";
                
                // Sisipkan baris baru di atas baris header (baris 1)
                $sheet->insertNewRowBefore(1, 1);
                
                // Setel nilai sel judul dan gabungkan sel-selnya
                $sheet->setCellValue('A1', $titleText);
                
                $highestColumn = $sheet->getHighestColumn();
                $sheet->mergeCells('A1:' . $highestColumn . '1');
                
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Proses penyematan gambar, dimulai dari baris ke-2
                $highestRow = $sheet->getHighestRow();
                $startRow = 2;
                $photoColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(6 + $this->itemChecks->count());

                for ($row = $startRow; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell($photoColumn . $row)->getValue();
                    
                    if (!empty($cellValue) && Storage::disk('public')->exists($cellValue)) {
                        $imagePath = Storage::disk('public')->path($cellValue);
                        
                        $sheet->getRowDimension($row)->setRowHeight(150);

                        $drawing = new Drawing();
                        $drawing->setName('Final Photo');
                        $drawing->setDescription('Final APAR Inspection Photo');
                        $drawing->setPath($imagePath);
                        $drawing->setHeight(150);
                        $drawing->setCoordinates($photoColumn . $row);
                        $drawing->setWorksheet($sheet);

                        // Setelah gambar disematkan, hapus teks path-nya
                        $sheet->setCellValue($photoColumn . $row, null);
                    }
                }
            },
        ];
    }
}
