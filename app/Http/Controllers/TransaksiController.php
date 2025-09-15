<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Vendor;
use App\Models\Kebutuhan;
use App\Models\MasterApar;
use App\Models\HargaKebutuhan;
use App\Models\JenisPemadam;
use App\Models\JenisIsi;
use App\Models\ItemCheck;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    /**
     * Tampilkan daftar transaksi dengan opsi filter tanggal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Mulai query untuk model Transaksi dengan eager loading
        $query = Transaksi::with(['vendor', 'kebutuhan', 'masterApar', 'hargaKebutuhan']);

        // Ambil tanggal awal dari request, jika ada
        $startDate = $request->input('start_date');

        // Ambil tanggal akhir dari request, jika ada
    	$endDate = $request->input('end_date');

        // Jika tanggal awal dan tanggal akhir disediakan, tambahkan filter
        if ($startDate && $endDate) {
            $query->whereBetween('tanggal_pembelian', [$startDate, $endDate]);
        }

        // Ambil data transaksi yang telah difilter dan urutkan
        $transaksis = $query->orderBy('tanggal_pembelian', 'desc')->get();

        // Tampilkan view dengan data transaksi yang sudah difilter
        return view('pages.transaksi.index', compact('transaksis'));
    }

    public function storeApi(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'kebutuhan_id' => 'required|exists:kebutuhans,id',
            'master_apar_id' => 'nullable|exists:master_apars,id',
            'tanggal_pembelian' => 'required|date',
            'tanggal_pelunasan' => 'nullable|date',
            'biaya_id' => 'required|exists:harga_kebutuhans,id',
        ]);

        $hargaKebutuhan = HargaKebutuhan::findOrFail($request->harga_kebutuhan_id);
        $biaya = $hargaKebutuhan->biaya;

        $transaksi = Transaksi::create([
            'vendor_id' => $request->vendor_id,
            'kebutuhan_id' => $request->kebutuhan_id,
            'master_apar_id' => $request->master_apar_id,
            'tanggal_pembelian' => $request->tanggal_pembelian,
            'tanggal_pelunasan' => $request->tanggal_pelunasan,
            'biaya_id' => $hargaKebutuhan->id,
            'biaya' => $biaya,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi berhasil disimpan!',
            'data' => $transaksi
        ], 201);
    }

    /**
     * Tampilkan formulir untuk membuat transaksi baru.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $vendors = Vendor::all();
        $kebutuhans = Kebutuhan::all();
        $masterApars = MasterApar::all();
        $hargaKebutuhans = HargaKebutuhan::all();
        $jenisPemadams = JenisPemadam::all();
        $jenisIsis = JenisIsi::all();
        $itemChecks = ItemCheck::all();
        
        return view('pages.transaksi.create', compact('vendors', 'kebutuhans', 'masterApars', 'hargaKebutuhans', 'jenisPemadams', 'jenisIsis', 'itemChecks'));
    }

    /**
     * Simpan transaksi baru ke database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'kebutuhan_id' => 'required|exists:kebutuhans,id',
            'master_apar_id' => 'nullable|exists:master_apars,id',
            'biaya_id' => 'required|exists:harga_kebutuhans,id', // Perbaikan di sini
            'biaya' => 'required|numeric',
            'tanggal_pembelian' => 'required|date',
            // 'tanggal_pelunasan' => 'nullable|date|after_or_equal:tanggal_pembelian',
        ]);
    
        try {
            DB::beginTransaction();
    
            // Buat entri Transaksi
            Transaksi::create([
                'vendor_id' => $validatedData['vendor_id'],
                'kebutuhan_id' => $validatedData['kebutuhan_id'],
                'master_apar_id' => $validatedData['master_apar_id'],
                'biaya_id' => $validatedData['biaya_id'], // Perbaikan di sini
                'biaya' => $validatedData['biaya'],
                'tanggal_pembelian' => $validatedData['tanggal_pembelian'],
                // 'tanggal_pelunasan' => $validatedData['tanggal_pelunasan']
            ]);
    
            DB::commit();
    
            return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil ditambahkan.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menambahkan transaksi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Tampilkan formulir untuk mengedit transaksi.
     *
     * @param  \App\Models\Transaksi  $transaksi
     * @return \Illuminate\View\View
     */
    public function edit(Transaksi $transaksi)
    {
        $vendors = Vendor::all();
        $kebutuhans = Kebutuhan::all();
        $masterApars = MasterApar::all();
        $hargaKebutuhans = HargaKebutuhan::all();
        $jenisPemadams = JenisPemadam::all();
        $jenisIsis = JenisIsi::all();
        $itemChecks = ItemCheck::all();

        // Cari HargaKebutuhan yang terkait dengan transaksi ini
        $harga_kebutuhan = HargaKebutuhan::find($transaksi->biaya_id);

        return view('pages.transaksi.edit', compact(
            'transaksi',
            'vendors',
            'kebutuhans',
            'masterApars',
            'hargaKebutuhans',
            'jenisPemadams',
            'jenisIsis',
            'itemChecks',
            'harga_kebutuhan' // Kirim data harga_kebutuhan ke view
        ));
    }

    public function update(Request $request, Transaksi $transaksi)
    {
        $validatedData = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'kebutuhan_id' => 'required|exists:kebutuhans,id',
            'master_apar_id' => 'nullable|exists:master_apars,id',
            'biaya_id' => 'required|exists:harga_kebutuhans,id',
            'biaya' => 'required|numeric',
            'tanggal_pembelian' => 'required|date',
        ]);
    
        try {
            DB::beginTransaction();
    
            $transaksi->update([
                'vendor_id' => $validatedData['vendor_id'],
                'kebutuhan_id' => $validatedData['kebutuhan_id'],
                'master_apar_id' => $validatedData['master_apar_id'],
                'biaya_id' => $validatedData['biaya_id'],
                'biaya' => $validatedData['biaya'],
                'tanggal_pembelian' => $validatedData['tanggal_pembelian'],
            ]);
    
            DB::commit();
    
            return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil diperbarui.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui transaksi: ' . $e->getMessage()); // Log error untuk debugging
            return redirect()->back()->with('error', 'Gagal memperbarui transaksi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Hapus transaksi dari database.
     *
     * @param  \App\Models\Transaksi  $transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Transaksi $transaksi)
    {
        $transaksi->delete();

        return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil dihapus!');
    }

    /**
     * Dapatkan data biaya untuk vendor dan kebutuhan melalui AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBiaya(Request $request)
    {
        try {
            $query = HargaKebutuhan::where('vendor_id', $request->vendor_id)
                                   ->where('kebutuhan_id', $request->kebutuhan_id);
    
            if ($request->has('master_apar_id')) {
                $masterApar = MasterApar::find($request->master_apar_id);
                if ($masterApar) {
                    $query->where('jenis_pemadam_id', $masterApar->jenis_pemadam_id)
                          ->where('jenis_isi_id', $masterApar->jenis_isi_id);
                }
            } else if ($request->has('jenis_pemadam_id') && $request->has('jenis_isi_id')) {
                $query->where('jenis_pemadam_id', $request->jenis_pemadam_id)
                      ->where('jenis_isi_id', $request->jenis_isi_id);
            } else if ($request->has('item_check_id')) {
                $query->where('item_check_id', $request->item_check_id);
            }
    
            $harga = $query->first();
    
            if ($harga) {
                return response()->json($harga);
            }
    
            return response()->json(['biaya' => null, 'id' => null], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data'], 500);
        }
    }

    /**
     * Ekspor data transaksi ke file Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function exportExcel(Request $request)
    {
        $query = Transaksi::query()->with(['vendor', 'kebutuhan', 'masterApar', 'hargaKebutuhan']);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
            $query->whereBetween('tanggal_pembelian', [$startDate, $endDate]);
        }
        
        $transaksis = $query->orderBy('tanggal_pembelian', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $titleStartDate = $startDate ? Carbon::parse($startDate)->format('d F Y') : 'Awal';
        $titleEndDate = $endDate ? Carbon::parse($endDate)->format('d F Y') : 'Sekarang';
        
        // Merge cells disesuaikan menjadi A1:F1
        $sheet->setCellValue('A1', 'Laporan Riwayat Transaksi: ' . $titleStartDate . ' - ' . $titleEndDate);
        $sheet->mergeCells('A1:F1'); 
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Hapus 'Tanggal Pelunasan' dari headers
        $headers = ['No.', 'Kode APAR', 'Vendor', 'Kebutuhan', 'Tanggal Pembelian', 'Biaya'];
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '2', $header);
            $sheet->getStyle($column . '2')->getFont()->setBold(true);
            $column++;
        }
        
        $row = 3;
        $no = 1;
        $totalBiaya = 0;
        
        foreach ($transaksis as $transaksi) {
            $biaya = optional($transaksi->hargaKebutuhan)->biaya ?? 0;
            $kebutuhanType = optional($transaksi->kebutuhan)->kebutuhan;
            $ukuranApar = optional($transaksi->masterApar)->ukuran ?? 1;
            
            if (in_array($kebutuhanType, ['Beli Baru', 'Isi Ulang'])) {
                $biaya = $biaya * $ukuranApar;
            }

            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, optional($transaksi->masterApar)->kode);
            $sheet->setCellValue('C' . $row, optional($transaksi->vendor)->nama_vendor);
            $sheet->setCellValue('D' . $row, optional($transaksi->kebutuhan)->kebutuhan);
            $sheet->setCellValue('E' . $row, $transaksi->tanggal_pembelian);
            // Hapus baris berikut: $sheet->setCellValue('F' . $row, $transaksi->tanggal_pelunasan);
            $sheet->setCellValue('F' . $row, $biaya);
            
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');

            $totalBiaya += $biaya;
            $row++;
        }
        
        // Baris Total Biaya, merge cells disesuaikan menjadi A:E
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->setCellValue('A' . $row, 'Total Biaya');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('F' . $row, $totalBiaya);
        $sheet->getStyle('F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');

        // Baris Total Transaksi, merge cells disesuaikan menjadi A:E
        $row++;
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->setCellValue('A' . $row, 'Total Transaksi');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('F' . $row, $transaksis->count());
        $sheet->getStyle('F' . $row)->getFont()->setBold(true);

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A2:' . $highestColumn . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', $highestColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Laporan-Transaksi-' . Carbon::now()->format('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}