<?php

namespace App\Http\Controllers;

use App\Exports\Apar\RefillExport;
use App\Exports\Apar\RusakExport;
use App\Http\Controllers\Controller;
use App\Models\AparInspection;
use App\Models\AparInspectionDetail;
use App\Models\ItemCheck;
use App\Models\MasterApar;
use App\Models\AparInspeksiPhoto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\SpreadsheeT;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Transaksi;
use App\Models\HargaKebutuhan;
use App\Models\Vendor;
use App\Models\Kebutuhan;

class AparController extends Controller
{
    /**
     * Tampilan untuk laporan APAR yang rusak.
     */
    public function rusakIndex(Request $request)
    {
        $query = AparInspection::query();
        $query->whereHas('details', function ($query) {
            $query->where('value', '!=', 'B');
        });

        if ($request->filled(['start_date', 'end_date'])) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $aparInspections = $query->with([
            'masterApar.gedung',
            'details.itemCheck',
            'user'
        ])->orderBy('date', 'desc')->get();
        $totalData = $aparInspections->count();
        return view('pages.laporan.apar.rusak', compact('aparInspections', 'totalData'));
    }

    public function getRusakApars(Request $request)
    {
        $query = AparInspection::query();
        
        // Temukan ID inspeksi terbaru untuk setiap APAR yang memiliki item rusak
        $subQuery = AparInspection::select(DB::raw('MAX(id) as id'))
            ->groupBy('master_apar_id')
            ->whereHas('details', function ($q) {
                $q->where('value', '!=', 'B');
            });

        $query->whereIn('id', $subQuery);

        if ($request->filled(['start_date', 'end_date'])) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $aparInspections = $query->with([
            'masterApar.gedung',
            'details.itemCheck',
            'user'
        ])->get();

        $formattedData = $aparInspections->map(function ($inspection) {
            $rusakItems = $inspection->details->filter(function ($detail) {
                return $detail->value !== 'B';
            });

            return [
                'id' => $inspection->master_apar_id,
                'kode' => $inspection->masterApar->kode,
                'lokasi' => $inspection->masterApar->lokasi,
                'gedung' => $inspection->masterApar->gedung->nama ?? 'N/A',
                'terakhir_diperiksa' => Carbon::parse($inspection->date)->format('d-m-Y'),
                'item_rusak' => $rusakItems->map(function ($detail) {
                    return [
                        'nama' => $detail->itemCheck->name ?? 'N/A',
                        'nilai' => $detail->value,
                        'keterangan' => $detail->remark ?? '-',
                    ];
                })->values()->all(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data APAR rusak berhasil diambil',
            'data' => $formattedData,
            'total' => $formattedData->count(),
        ]);
    }

    /**
     * Mendapatkan data APAR berdasarkan kode.
     */
    public function getByCode(Request $request)
    {
        $kode = $request->query('kode'); 
        $apar = MasterApar::where('kode', $kode)
                            ->with('jenisPemadam', 'jenisIsi') // Eager load relasi
                            ->first();

        if (!$apar) {
            return response()->json([
                'success' => false,
                'message' => 'Data APAR tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $apar
        ]);
    }

    public function getHargaIsiUlang(Request $request)
    {
        // Validasi parameter yang diperlukan
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'kebutuhan_id' => 'required|exists:kebutuhans,id', // Kebutuhan "Isi Ulang"
            'jenis_pemadam_id' => 'required|exists:jenis_pemadams,id',
            'jenis_isi_id' => 'required|exists:jenis_isis,id',
        ]);

        // Cari harga yang cocok
        $harga = HargaKebutuhan::where('vendor_id', $request->vendor_id)
            ->where('kebutuhan_id', $request->kebutuhan_id)
            ->where('jenis_pemadam_id', $request->jenis_pemadam_id)
            ->where('jenis_isi_id', $request->jenis_isi_id)
            ->latest('tanggal_perubahan') // Ambil yang terbaru jika ada
            ->first();

        if (!$harga) {
            return response()->json([
                'success' => false,
                'message' => 'Harga tidak ditemukan untuk kombinasi ini.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $harga->biaya, // Mengembalikan biaya saja
        ]);
    }

    public function getAparStatusCounts()
    {
        // 1. Dapatkan ID inspeksi terbaru untuk setiap APAR
        $latestInspections = AparInspection::select('master_apar_id', DB::raw('MAX(id) as latest_inspection_id'))
            ->groupBy('master_apar_id');

        // 2. Temukan ID APAR yang memiliki setidaknya satu item yang 'tidak baik'
        $notGoodAparIds = AparInspectionDetail::joinSub($latestInspections, 'latest_inspections', function ($join) {
            $join->on('apar_inspection_details.apar_inspection_id', '=', 'latest_inspections.latest_inspection_id');
        })
        ->where('apar_inspection_details.value', '!=', 'B')
        ->pluck('latest_inspections.master_apar_id')
        ->unique();
        
        // 3. Dapatkan semua APAR yang aktif
        $totalActiveAparCount = MasterApar::where('is_active', true)->count();
        $notGoodAparCount = count($notGoodAparIds);
        $goodAparCount = $totalActiveAparCount - $notGoodAparCount;

        // 4. Kembalikan data dalam format JSON
        return response()->json([
            'good_count' => $goodAparCount,
            'not_good_count' => $notGoodAparCount,
        ]);
    }

    public function getVendorHargaKebutuhan(Request $request)
    {
        $vendors = Vendor::with([
            'hargaKebutuhans' => function ($query) {
                $query->with([
                    'kebutuhan:id,kebutuhan',
                    'itemCheck:id,name,is_active'
                ])->where('kebutuhan_id', 3) // Filter hanya untuk Ganti Komponen
                ->whereNotNull('item_check_id'); // Pastikan item_check_id tidak null
            }
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $vendors
        ]);
    }

    /**
     * Mengunggah foto inspeksi. Ini adalah fungsi yang terpisah.
     * Menggunakan Laravel Storage yang otomatis membuat folder.
     */
    public function uploadInspeksiPhoto(Request $request, $id)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5000',
            'item_check_id' => 'required|exists:item_checks,id'
        ]);

        // Laravel Storage akan otomatis membuat folder 'apar_inspeksi' jika belum ada.
        $path = $request->file('photo')->store('apar_inspeksi', 'public');
        
        // Simpan ke database
        $photo = AparInspeksiPhoto::create([
            'inspeksi_id'   => $id, 
            'item_check_id' => $request->item_check_id,
            'foto_path'     => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Foto berhasil disimpan.',
            'data'      => $photo
        ], 201);
    }

    /**
     * Ekspor laporan APAR rusak ke Excel.
     */
    // public function exportExcelRusak(Request $request)
    // {
    //     $query = AparInspection::query();

    //     $query->whereHas('details', function ($query) {
    //         $query->where('value', '!=', 'B');
    //     });

    //     if ($request->filled(['start_date', 'end_date'])) {
    //         $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
    //         $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
    //         $query->whereBetween('date', [$startDate, $endDate]);
    //     }

    //     $aparInspections = $query->with([
    //         'masterApar.gedung',
    //         'details.itemCheck',
    //         'user'
    //     ])->orderBy('date', 'desc')->get();
        
    //     $spreadsheet = new Spreadsheet();
    //     $sheet = $spreadsheet->getActiveSheet();
        
    //     $sheet->setCellValue('A1', 'No');
    //     $sheet->setCellValue('B1', 'Kode');
    //     $sheet->setCellValue('C1', 'Gedung');
    //     $sheet->setCellValue('D1', 'Lokasi');
    //     $sheet->setCellValue('E1', 'Tgl Inspeksi');
    //     $sheet->setCellValue('F1', 'Kondisi Item');
    //     $sheet->setCellValue('G1', 'User');

    //     $row = 2;
    //     foreach ($aparInspections as $i => $inspection) {
    //         $sheet->setCellValue('A' . $row, $i + 1);
    //         $sheet->setCellValue('B' . $row, $inspection->masterApar->kode);
    //         $sheet->setCellValue('C' . $row, $inspection->masterApar->gedung->nama);
    //         $sheet->setCellValue('D' . $row, $inspection->masterApar->lokasi);
    //         $sheet->setCellValue('E' . $row, $inspection->dateFormatted);

    //         $kondisiItems = '';
    //         foreach ($inspection->details as $detail) {
    //             if ($detail->value != 'B') {
    //                 $kondisiItems .= $detail->itemCheck->name . ': ' . $detail->value;
    //                 if ($detail->remark) {
    //                     $kondisiItems .= ' (' . $detail->remark . ')';
    //                 }
    //                 $kondisiItems .= "\n";
    //             }
    //         }
    //         $sheet->setCellValue('F' . $row, $kondisiItems);
    //         $sheet->setCellValue('G' . $row, $inspection->user->name);

    //         $row++;
    //     }

    //     $writer = new Xlsx($spreadsheet);
    //     $fileName = 'Laporan-APAR-Rusak-' . Carbon::now()->format('Y-m-d') . '.xlsx';
        
    //     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //     header('Content-Disposition: attachment;filename="' . $fileName . '"');
    //     header('Cache-Control: max-age=0');
        
    //     $writer->save('php://output');
    //     exit;
    // }

    // Fungsionalitas Laporan Lainnya
    public function refill(Request $request)
    {
        $request->validate([
            'master_apar_id' => 'required|integer|exists:master_apars,id',
            'vendor_id' => 'required|integer|exists:vendors,id',
            'kebutuhan_id' => 'required|integer|exists:kebutuhans,id',
            'tanggal_pembelian' => 'required|date',
            'tgl_kadaluarsa' => 'nullable|date',
        ]);

        // Ambil biaya_id dari tabel harga_kebutuhans
        $hargaKebutuhan = HargaKebutuhan::where('vendor_id', $request->vendor_id)
            ->where('kebutuhan_id', $request->kebutuhan_id)
            ->latest('tanggal_perubahan') // ambil yang terbaru kalau ada banyak versi
            ->first();

        if (!$hargaKebutuhan) {
            return response()->json([
                'message' => 'Harga untuk vendor dan kebutuhan tersebut tidak ditemukan.'
            ], 404);
        }

        // Update tanggal kadaluarsa di master_apars
        $apar = MasterApar::find($request->master_apar_id);
        if ($request->filled('tgl_kadaluarsa')) {
            $apar->tgl_kadaluarsa = $request->tgl_kadaluarsa;
        }
        $apar->save();

        // Buat transaksi baru
        $transaksi = Transaksi::create([
            'master_apar_id' => $request->master_apar_id,
            'vendor_id' => $request->vendor_id,
            'kebutuhan_id' => $request->kebutuhan_id,
            'tanggal_pembelian' => $request->tanggal_pembelian,
            'biaya_id' => $hargaKebutuhan->id,
        ]);

        return response()->json([
            'message' => 'Update APAR dan transaksi berhasil',
            'transaksi' => $transaksi,
        ]);
    }

    public function exportExcelRefill(Request $request){
        return Excel::download(new RefillExport, 'laporan-apar-refill-'.Carbon::now()->format('Y-m').'.xlsx');
    }

    public function yearlyIndex(Request $request){
        $year = date('Y');
        if($request->filled('year')){
            $year = $request->year;
        }
        
        $kode = "";
        $apar = "";
        if($request->filled('kode')){
            $kode = $request->kode;
            $apar = MasterApar::where('kode', $kode)->first();
        }
        
        return view('pages.laporan.apar.yearly', [
            'year' => $year,
            'kode' => $kode,
            'apar' => $apar,
        ]);
    }
    
    // Fungsionalitas Riwayat Inspeksi
    public function riwayatInspeksiIndex(Request $request)
    {
        $query = AparInspection::with(['masterApar.gedung', 'user'])
            ->orderBy('date', 'desc');

        // Menambahkan logika filtering berdasarkan tanggal
        if ($request->filled(['start_date', 'end_date'])) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        // Menambahkan logika pencarian
        if ($request->filled('q')) {
            $search = $request->input('q');
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

        $aparInspections = $query->paginate(10);

        return view('pages.riwayat_inspeksi.index', compact('aparInspections'));
    }

    public function uninspected(Request $request)
    {
        // Mendapatkan bulan dan tahun dari request, default ke bulan dan tahun saat ini
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        // Ambil ID APAR yang sudah diinspeksi pada bulan dan tahun yang dipilih
        $inspectedAparIds = AparInspection::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->pluck('master_apar_id');

        // Ambil data APAR yang belum diinspeksi
        $uninspectedApars = MasterApar::whereNotIn('id', $inspectedAparIds)
            ->with('gedung') // Eager load relasi gedung untuk efisiensi
            ->get();
            
        // Hitung total data
        $totalData = $uninspectedApars->count();

        // Kirim data yang dibutuhkan ke view
        return view('pages.riwayat_inspeksi.uninspected', compact(
            'uninspectedApars',
            'totalData',
            'month',
            'year'
        ));
    }

    public function getUninspectedApars(Request $request)
    {
        // Ambil input bulan dan tahun dari request, jika tidak ada, gunakan bulan dan tahun saat ini
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $inspectedAparIds = AparInspection::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->pluck('master_apar_id');

        $uninspectedApars = MasterApar::with('gedung')
            ->whereNotIn('id', $inspectedAparIds)
            ->get();

        return view('pages.riwayat_inspeksi.uninspected', [
            'uninspectedApars' => $uninspectedApars,
            'totalData' => $uninspectedApars->count(),
            'month' => $month, // Kirimkan bulan yang dipilih ke view
            'year' => $year,   // Kirimkan tahun yang dipilih ke view
        ]);
    }

    public function getUninspectedAreaCounts(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $inspectedAparIds = AparInspection::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->pluck('master_apar_id');

        $uninspectedAparCounts = MasterApar::whereNotIn('id', $inspectedAparIds)
            ->with('gedung')
            ->select('gedung_id', DB::raw('count(*) as count'))
            ->groupBy('gedung_id')
            ->get();

        $response = $uninspectedAparCounts->map(function ($item) {
            return [
                'gedung_id' => $item->gedung_id,
                'nama_gedung' => $item->gedung->nama,
                'count' => $item->count,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $response
        ], 200);
    }

    public function apiUninspected(Request $request)
    {
        // Dapatkan bulan dan tahun dari request, default ke bulan dan tahun saat ini
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        // Ambil ID APAR yang sudah diinspeksi pada bulan dan tahun yang dipilih
        $inspectedAparIds = AparInspection::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->pluck('master_apar_id');

        // Ambil data APAR yang belum diinspeksi
        $uninspectedApars = MasterApar::whereNotIn('id', $inspectedAparIds)
            ->with('gedung') // Eager load relasi gedung untuk efisiensi
            ->get();
            
        // Kembalikan data dalam format JSON
        return response()->json([
            'success' => true,
            'data' => $uninspectedApars
        ], 200);
    }

    public function getUninspectedAparsByArea(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        $gedungId = $request->input('gedung_id');

        $inspectedAparIds = AparInspection::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->pluck('master_apar_id');

        $uninspectedApars = MasterApar::whereNotIn('id', $inspectedAparIds)
            ->where('gedung_id', $gedungId) // Filter berdasarkan gedung_id
            ->get();

        return response()->json([
            'success' => true,
            'data' => $uninspectedApars
        ], 200);
    }

    public function riwayatInspeksiRecycle(Request $request)
    {
        $validated = $request->validate([
            'detail_id' => 'required|exists:apar_inspection_details,id',
        ]);
    
        $detail = AparInspectionDetail::find($validated['detail_id']);
    
        if (!$detail) {
            return back()->with('error', 'Detail Inspeksi tidak ditemukan.');
        }
    
        $detail->value = 'B'; 
        $detail->remark = null;
        $detail->save();

        // Cari AparInspection terkait dan kosongkan final_foto_path
        if ($detail->aparInspection) {
            Storage::disk('public')->delete($detail->aparInspection->final_foto_path);
            $detail->aparInspection->update(['final_foto_path' => null]);
        }
    
        return back()->with('success', 'Item berhasil di-recycle.');
    }

    public function checkInspection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode' => 'required|string|exists:master_apars,kode',
            'date' => 'required|date_format:d-m-Y',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $kode = $request->input('kode');
        $date = Carbon::createFromFormat('d-m-Y', $request->input('date'));
        $yearMonth = $date->format('Y-m');

        $apar = MasterApar::where('kode', $kode)->first();
        if (!$apar) {
            return response()->json([
                'success' => false,
                'message' => 'APAR tidak ditemukan',
            ], 404);
        }

        $isInspected = AparInspection::where('master_apar_id', $apar->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->exists();

        return response()->json([
            'success' => true,
            'inspected' => $isInspected,
            'last_inspection_date' => $isInspected ? AparInspection::where('master_apar_id', $apar->id)
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->latest('date')
                ->value('date') : null,
        ], 200);
    }

    // Fungsionalitas API dan Lainnya
    public function searchApar(Request $request)
    {
        $kode = $request->query('kode');
        $tahun = $request->query('tahun');

        // Pastikan eager loading untuk relasi jenisIsi dan jenisPemadam
        $apar = MasterApar::with(['gedung', 'inspections.user', 'inspections.details.itemCheck', 'jenisIsi', 'jenisPemadam'])
            ->where('kode', $kode)
            ->first();

        if (!$apar) {
            return response()->json([
                'success' => false,
                'message' => 'APAR tidak ditemukan.'
            ], 404);
        }

        $isInspectedThisMonth = AparInspection::where('master_apar_id', $apar->id)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->exists();

        $namaGedung = $apar->gedung->nama ?? '-';
        $itemChecks = ItemCheck::where('is_active', true)->get(['id', 'name']);

        $inspections = [];
        if ($tahun) {
            $aparInspections = AparInspection::where('master_apar_id', $apar->id)
                ->whereYear('date', $tahun)
                ->with('user', 'details.itemCheck')
                ->get();

            foreach ($aparInspections as $inspection) {
                $inspections[] = [
                    'id' => $inspection->id,
                    'date' => $inspection->date,
                    'user_name' => $inspection->user->name ?? '-',
                    'status' => $inspection->details->every(fn($detail) => $detail->value === '1') ? 'Baik' : 'Rusak/Tidak Lengkap',
                    'details' => $inspection->details->map(function ($detail) {
                        return [
                            'item_check_id' => $detail->item_check_id,
                            'name' => $detail->itemCheck->name ?? '-',
                            'value' => $detail->value,
                        ];
                    })->all(),
                ];
            }
        }

        // --- Perubahan Penting di Sini ---
        // Pastikan Anda menyertakan ID dan nama dari relasi
        $jenisPemadam = [
            'id' => $apar->jenisPemadam->id ?? null,
            'name' => $apar->jenisPemadam->jenis_pemadam ?? '-'
        ];
        $jenisIsi = [
            'id' => $apar->jenisIsi->id ?? null,
            'name' => $apar->jenisIsi->jenis_isi ?? '-'
        ];

        $response = [
            'id' => $apar->id,
            'kode' => $apar->kode,
            'jenis' => $jenisIsi['name'], // Menggunakan nama jenis_isi
            'ukuran' => $apar->ukuran . ' ' . $apar->satuan,
            'lokasi' => $namaGedung . ' - ' . $apar->lokasi,
            'tgl_refill' => $apar->tgl_refill ?? '-',
            'tgl_kadaluarsa' => $apar->tgl_kadaluarsa ?? '-',
            
            // --- Ini adalah penambahan yang paling penting ---
            'jenis_pemadam_id' => $apar->jenis_pemadam_id,
            'jenis_isi_id' => $apar->jenis_isi_id,
            'jenis_pemadam' => $jenisPemadam,
            'jenis_isi' => $jenisIsi,
            // --- Akhir penambahan ---
            
            'item_checks' => $itemChecks,
            'inspections' => $inspections,
            'is_inspected' => $isInspectedThisMonth,
        ];

        return response()->json([
            'success' => true,
            'data' => $response
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'tgl_kadaluarsa' => 'required|date',
            'tgl_refill' => 'nullable|date',
        ]);

        $apar = MasterApar::find($id);

        if (!$apar) {
            return response()->json([
                'success' => false,
                'message' => 'APAR tidak ditemukan.'
            ], 404);
        }

        $updateData = [];
        if (isset($validated['tgl_kadaluarsa'])) {
            $updateData['tgl_kadaluarsa'] = $validated['tgl_kadaluarsa'];
        }
        if (isset($validated['tgl_refill'])) {
            $updateData['tgl_refill'] = $validated['tgl_refill'];
        }

        $apar->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Tanggal APAR berhasil diperbarui.',
            'data' => $apar
        ], 200);
    }
    
    public function getExpiringApars()
    {
        $thirtyDaysFromNow = Carbon::today()->addDays(30);

        $expiringApars = MasterApar::where('tgl_kadaluarsa', '<=', $thirtyDaysFromNow)
            ->orderBy('tgl_kadaluarsa', 'asc')
            ->get(['kode', 'tgl_kadaluarsa', 'lokasi']);

        return response()->json([
            'success' => true,
            'data' => $expiringApars
        ], 200);
    }

    public function getAllApars()
    {
        $apars = MasterApar::with('gedung')->get([
            'id',
            'kode',
            'jenis_pemadam',
            'jenis_isi',
            'ukuran',
            'satuan',
            'tgl_isi',
            'tgl_kadaluarsa',
            'lokasi',
            'gedung_id'
        ]);

        return response()->json([
            'success' => true,
            'data' => $apars
        ], 200);
    }
    
    /**
     * Menyimpan data inspeksi dan foto-foto terkait.
     * Menggunakan transaksi database untuk memastikan integritas data.
     * Menggunakan Laravel Storage untuk otomatis membuat folder.
     */
    public function storeInspeksi(Request $request)
    {
        try {
            // Validasi data yang diterima
            $validator = Validator::make($request->all(), [
                'master_apar_id' => 'required|exists:master_apars,id',
                'user_id' => 'required',
                'date' => 'required|date_format:Y-m-d',
                'details' => 'required|json',
                'final_photo' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validasi Gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Memulai transaksi database
            DB::beginTransaction();

            $tanggal_inspeksi = Carbon::createFromFormat('Y-m-d', $request->input('date'));

            // 1. Proses dan simpan foto akhir.
            // Storage::put() secara otomatis membuat folder jika belum ada.
            $finalPhotoBase64 = $request->input('final_photo');
            $finalPhotoData = base64_decode($finalPhotoBase64);
            $finalPhotoName = 'inspeksi_akhir_' . time() . '_' . Str::random(10) . '.png';
            $finalPhotoPath = 'inspeksi_akhir/' . $finalPhotoName;

            Storage::disk('public')->put($finalPhotoPath, $finalPhotoData);

            // 2. Buat entri inspeksi baru di tabel `apar_inspections`
            $inspeksi = AparInspection::create([
                'master_apar_id' => $request->input('master_apar_id'),
                'user_id' => $request->input('user_id'),
                'date' => $tanggal_inspeksi,
                'final_foto_path' => $finalPhotoPath, // Menyimpan path foto akhir
            ]);

            // Decode data details
            $details = json_decode($request->input('details'), true);

            // 3. Simpan detail inspeksi dan foto-foto detailnya
            foreach ($details as $detail) {
                // Buat entri detail inspeksi di tabel `apar_inspection_details`
                $aparInspectionDetail = AparInspectionDetail::create([
                    'apar_inspection_id' => $inspeksi->id,
                    'item_check_id' => $detail['item_check_id'],
                    'value' => $detail['value'],
                    'remark' => $detail['remark'] ?? null,
                ]);

                // Cek jika ada foto detail dan simpan
                if (!empty($detail['photo'])) {
                    $photoData = base64_decode($detail['photo']);
                    $photoName = 'inspeksi_detail_' . time() . '_' . Str::random(10) . '.png';
                    // Menyimpan foto detail ke sub-folder 'apar_inspeksi_detail'
                    $photoPath = 'apar_inspeksi_detail/' . $photoName; 

                    // Storage::put() akan otomatis membuat folder `apar_inspeksi_detail` jika belum ada.
                    Storage::disk('public')->put($photoPath, $photoData);

                    // Simpan entri foto detail ke tabel `apar_inspeksi_photos`
                    AparInspeksiPhoto::create([
                        'inspeksi_id' => $inspeksi->id,
                        'item_check_id' => $detail['item_check_id'],
                        'foto_path' => $photoPath, // Menyimpan path foto detail
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Data inspeksi berhasil disimpan.',
                'data' => $inspeksi
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan data inspeksi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getInspectionYears()
    {
        $years = AparInspection::select(DB::raw("strftime('%Y', date) as year"))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $years = $years->map(function ($year) {
            return (string) $year;
        });

        return response()->json([
            'success' => true,
            'data' => $years
        ], 200);
    }
    
    /**
     * API untuk mendapatkan riwayat inspeksi APAR berdasarkan kode.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiAparHistory(Request $request)
    {
        $kode = $request->query('kode');
        $tahun = $request->query('tahun');

        // Menambahkan eager loading untuk relasi 'jenisIsi'
        $apar = MasterApar::where('kode', $kode)
            ->with('gedung', 'jenisIsi')
            ->first();

        // 1. Cek jika APAR tidak ditemukan
        if (!$apar) {
            return response()->json([
                'success' => false,
                'message' => 'APAR tidak ditemukan.'
            ], 404);
        }

        // 2. Ambil inspeksi terakhir
        $latestInspection = AparInspection::where('master_apar_id', $apar->id)
            ->with(['details.itemCheck', 'user'])
            ->orderBy('date', 'desc')
            ->first();

        $latestDate = '-';

        if ($latestInspection) {
            // Tentukan berdasarkan detail inspeksi terakhir
            $isOK = $latestInspection->details->every(function ($detail) {
                return $detail->value === 'B';
            });
            $latestDate = Carbon::parse($latestInspection->date)->format('d-m-Y');
        }

        // 3. Ambil riwayat inspeksi
        $historyQuery = AparInspection::where('master_apar_id', $apar->id)
            ->with(['details.itemCheck', 'user'])
            ->orderBy('date', 'desc');

        if ($tahun) {
            $historyQuery->whereYear('date', $tahun);
        }

        $history = $historyQuery->get();

        // 4. Format data riwayat
        $formattedHistory = $history->map(function ($inspection) {
            $details = $inspection->details->map(function ($detail) {
                return [
                    'item_check' => $detail->itemCheck->name ?? '-',
                    'value' => $detail->value,
                    'remark' => $detail->remark,
                ];
            });

            return [
                'id' => $inspection->id,
                'date' => Carbon::parse($inspection->date)->format('d-m-Y'),
                'user_name' => $inspection->user->name ?? '-',
                'details' => $details,
            ];
        });

        // 5. Susun respons akhir
        $response = [
            'apar' => [
                'kode' => $apar->kode,
                'lokasi' => $apar->lokasi,
                // Mengambil jenis dari relasi jenisIsi
                'jenis' => $apar->jenisIsi->jenis_isi ?? '-',
                'ukuran' => $apar->ukuran . ' ' . $apar->satuan,
            ],
            'latest_inspection' => [
                'date' => $latestDate,
            ],
            'history' => $formattedHistory,
        ];

        return response()->json([
            'success' => true,
            'data' => $response
        ], 200);
    }
}
