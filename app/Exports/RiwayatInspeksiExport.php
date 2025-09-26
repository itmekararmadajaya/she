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
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RiwayatInspeksiExport implements FromCollection, WithStyles, WithHeadings, WithEvents, ShouldAutoSize
{
    protected $request;
    protected $itemChecks;
    protected $inspections;

    public function __construct(Request $request = null)
    {
        // Pastikan $this->request tidak pernah null
        $this->request = $request ?? new Request();

        // Ganti 'urutan' dengan 'id' atau 'name' karena kolom 'urutan' tidak ada di tabel.
        $this->itemChecks = ItemCheck::orderBy('id')->get();

        $query = AparInspection::with(['masterApar.gedung', 'user', 'details'])
            ->orderBy('date', 'desc');

        if ($this->request->filled(['start_date', 'end_date'])) {
            $startDate = Carbon::parse($this->request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($this->request->input('end_date'))->endOfDay();
            $query->whereBetween('date', [$startDate, $endDate]);
        }

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

        $this->inspections = $query->get();
    }

    public function collection()
    {
        return $this->inspections->map(function ($row) {
            $rowData = [
                $row->masterApar->kode,
                $row->masterApar->gedung->nama . ' - ' . $row->masterApar->lokasi,
                $row->user->name,
                Carbon::parse($row->date)->translatedFormat('d F Y'),
                $row->status,
            ];

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

            $rowData[] = $row->final_foto_path;
            $rowData[] = pathinfo($row->final_foto_path, PATHINFO_BASENAME);

            return $rowData;
        });
    }

    public function headings(): array
    {
        $mainHeadings = [
            'Kode APAR',
            'Lokasi',
            'Petugas',
            'Tanggal',
            'Status',
        ];

        $itemCheckNames = $this->itemChecks->pluck('name')->toArray();

        $allHeadings = array_merge($mainHeadings, $itemCheckNames, ['Foto', 'Nama Foto']);

        return [
            $allHeadings
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A2:' . $sheet->getHighestColumn() . '2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE5E7EB'],
            ],
        ]);

        $sheet->getStyle('A3:' . $sheet->getHighestColumn() . ($sheet->getHighestRow()))->getAlignment()->setWrapText(true);
        $sheet->getStyle('A3:' . $sheet->getHighestColumn() . ($sheet->getHighestRow()))->getAlignment()->setVertical('top');

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(15);

        $itemCheckWidth = 15;
        $startColumnIndex = 6;
        foreach ($this->itemChecks as $index => $itemCheck) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColumnIndex + $index);
            $sheet->getColumnDimension($column)->setWidth($itemCheckWidth);
        }

        $photoColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColumnIndex + $this->itemChecks->count());
        $sheet->getColumnDimension($photoColumn)->setWidth(50);

        $nameColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColumnIndex + $this->itemChecks->count() + 1);
        $sheet->getColumnDimension($nameColumn)->setWidth(25);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $startDate = $this->request->filled('start_date') ? Carbon::parse($this->request->input('start_date'))->translatedFormat('d F Y') : '-';
                $endDate = $this->request->filled('end_date') ? Carbon::parse($this->request->input('end_date'))->translatedFormat('d F Y') : '-';
                $titleText = "Laporan Riwayat Inspeksi APAR: {$startDate} - {$endDate}";

                $sheet->insertNewRowBefore(1, 1);

                $sheet->setCellValue('A1', $titleText);

                $highestColumn = $sheet->getHighestColumn();
                $sheet->mergeCells('A1:' . $highestColumn . '1');

                $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $highestRow = $sheet->getHighestRow();
                $startRow = 3;

                // Pastikan $this->inspections tidak kosong dan indeks sesuai
                if ($this->inspections->isEmpty()) {
                    return; // Hentikan eksekusi jika tidak ada data
                }

                $photoColumnIndex = 6 + $this->itemChecks->count();
                $photoColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($photoColumnIndex);

                for ($row = $startRow; $row <= $highestRow; $row++) {
                    $index = $row - $startRow;
                    if (isset($this->inspections[$index])) {
                        $inspection = $this->inspections[$index];

                        // Pewarnaan sel Status Inspeksi (Kolom E)
                        $status = $inspection->status;
                        $statusCell = 'E' . $row;
                        $fillColor = null;
                        if ($status == 'Lolos') {
                            $fillColor = Color::COLOR_GREEN;
                        } elseif ($status == 'Tidak Lolos' || $status == 'Dalam Perbaikan') {
                            $fillColor = Color::COLOR_RED;
                        }

                        if ($fillColor) {
                            $sheet->getStyle($statusCell)->applyFromArray([
                                'font' => ['bold' => true],
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['argb' => 'FF' . substr($fillColor, 2)],
                                ],
                            ]);
                        }

                        // Pewarnaan sel untuk setiap item check
                        $itemCheckStartColumnIndex = 6;
                        foreach ($this->itemChecks as $itemIndex => $itemCheck) {
                            $detail = $inspection->details->where('item_check_id', $itemCheck->id)->first();
                            $cellColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($itemCheckStartColumnIndex + $itemIndex);
                            $cell = $cellColumn . $row;
                            $fillColor = null;

                            if ($detail) {
                                $value = $detail->value;
                                if ($value == 'B') {
                                    $fillColor = Color::COLOR_GREEN;
                                } else {
                                    $fillColor = Color::COLOR_RED;
                                }
                            } else {
                                $fillColor = Color::COLOR_RED;
                            }

                            if ($fillColor) {
                                $sheet->getStyle($cell)->applyFromArray([
                                    'font' => ['bold' => true],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'startColor' => ['argb' => 'FF' . substr($fillColor, 2)],
                                    ],
                                ]);
                            }
                        }

                        // Proses penyematan gambar
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

                            $sheet->setCellValue($photoColumn . $row, null);
                        }
                    }
                }
            },
        ];
    }
}