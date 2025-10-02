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

        // Ambil data transaksi yang telah difilter dan urutkan berdasarkan ID secara menaik
        $transaksis = $query->orderBy('id', 'asc')->get();

        // Tampilkan view dengan data transaksi yang sudah difilter
        return view('pages.transaksi.index', compact('transaksis'));
    }

    public function storeApi(Request $request)
    {
        $validatedData = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'kebutuhan_id' => 'required|exists:kebutuhans,id',
            'master_apar_id' => 'nullable|exists:master_apars,id',
            'tanggal_pembelian' => 'required|date',
            'tanggal_pelunasan' => 'nullable|date',
            'biaya_id' => 'required|exists:harga_kebutuhans,id',
        ]);
        
        try {
            DB::beginTransaction();

            $hargaKebutuhan = HargaKebutuhan::findOrFail($validatedData['biaya_id']);
            $biayaFinal = $hargaKebutuhan->biaya; // Ambil biaya dasar

            // Tambahkan logika perhitungan biaya berdasarkan kebutuhan
            // Logika ini mirip dengan yang sudah Anda lakukan di MasterAparController
            if ($validatedData['master_apar_id'] && ($validatedData['kebutuhan_id'] == 1 || $validatedData['kebutuhan_id'] == 2)) {
                $masterApar = MasterApar::findOrFail($validatedData['master_apar_id']);
                $biayaFinal = $biayaFinal * $masterApar->ukuran;
            }

            $jenisPemadamId = null;
            $jenisIsiId = null;

            if ($validatedData['master_apar_id']) {
                $masterApar = MasterApar::findOrFail($validatedData['master_apar_id']);
                $jenisPemadamId = $masterApar->jenis_pemadam_id;
                $jenisIsiId = $masterApar->jenis_isi_id;
            }

            $transaksi = Transaksi::create([
                'vendor_id' => $validatedData['vendor_id'],
                'kebutuhan_id' => $validatedData['kebutuhan_id'],
                'master_apar_id' => $validatedData['master_apar_id'],
                'jenis_pemadam_id' => $jenisPemadamId,
                'jenis_isi_id' => $jenisIsiId,
                'biaya_id' => $hargaKebutuhan->id,
                'biaya' => $biayaFinal, // Gunakan biaya final yang sudah dihitung
                'tanggal_pembelian' => $validatedData['tanggal_pembelian'],
                'tanggal_pelunasan' => $validatedData['tanggal_pelunasan'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil disimpan!',
                'data' => $transaksi
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan transaksi via API: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menyimpan transaksi: ' . $e->getMessage()], 500);
        }
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
        // Log data yang diterima langsung dari request
        Log::info('Data dari request:', $request->all());

        $validatedData = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'kebutuhan_id' => 'required|exists:kebutuhans,id',
            'master_apar_id' => 'nullable|exists:master_apars,id',
            'biaya_id' => 'required|exists:harga_kebutuhans,id',
            'tanggal_pembelian' => 'required|date',
            'tanggal_pelunasan' => 'nullable|date|after_or_equal:tanggal_pembelian',
        ]);
        
        // Log data yang sudah lolos validasi
        Log::info('Data yang divalidasi:', $validatedData);

        try {
            DB::beginTransaction();

            $hargaKebutuhan = HargaKebutuhan::findOrFail($validatedData['biaya_id']);
            $biaya = $hargaKebutuhan->biaya;

            // Cek jika master_apar_id ada, ambil data APAR untuk menghitung biaya
            if ($validatedData['master_apar_id'] && $validatedData['kebutuhan_id'] == 2) { 
                $masterApar = MasterApar::findOrFail($validatedData['master_apar_id']);
                $biaya = $biaya * $masterApar->ukuran;
            }

            // Siapkan data untuk disimpan, TIDAK MENYERTKAN jenis_pemadam_id dan jenis_isi_id
            $dataToStore = array_merge($validatedData, [
                'biaya' => $biaya,
            ]);
            
            // Log data final sebelum disimpan ke database
            Log::info('Data yang akan disimpan:', $dataToStore);

            // Buat entri Transaksi dengan semua data yang relevan
            Transaksi::create($dataToStore);

            DB::commit();

            return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menambahkan transaksi: ', ['error' => $e->getMessage(), 'request_data' => $request->all()]);
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

            $jenisPemadamId = null;
            $jenisIsiId = null;

            // Ambil jenis_pemadam_id dan jenis_isi_id jika master_apar_id ada
            if ($validatedData['master_apar_id']) {
                $masterApar = MasterApar::findOrFail($validatedData['master_apar_id']);
                $jenisPemadamId = $masterApar->jenis_pemadam_id;
                $jenisIsiId = $masterApar->jenis_isi_id;
            }

            // Siapkan data untuk update
            $dataToUpdate = [
                'vendor_id' => $validatedData['vendor_id'],
                'kebutuhan_id' => $validatedData['kebutuhan_id'],
                'master_apar_id' => $validatedData['master_apar_id'],
                'jenis_pemadam_id' => $jenisPemadamId,
                'jenis_isi_id' => $jenisIsiId,
                'biaya_id' => $validatedData['biaya_id'],
                'biaya' => $validatedData['biaya'],
                'tanggal_pembelian' => $validatedData['tanggal_pembelian'],
            ];

            $transaksi->update($dataToUpdate);
    
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
        Log::info('Request getBiaya masuk', $request->all());

        $query = HargaKebutuhan::where('vendor_id', $request->vendor_id)
            ->where('kebutuhan_id', $request->kebutuhan_id);

        Log::info('Base Query dibuat', [
            'vendor_id' => $request->vendor_id,
            'kebutuhan_id' => $request->kebutuhan_id
        ]);

        // kebutuhan 1 & 2: filter master_apar
        if (in_array((int)$request->kebutuhan_id, [1, 2]) && $request->filled('master_apar_id')) {
            $masterApar = MasterApar::find($request->master_apar_id);

            if ($masterApar) {
                $query->where('jenis_pemadam_id', $masterApar->jenis_pemadam_id)
                    ->where('jenis_isi_id', $masterApar->jenis_isi_id);

                Log::info('Filter MasterApar dipakai', [
                    'jenis_pemadam_id' => $masterApar->jenis_pemadam_id,
                    'jenis_isi_id' => $masterApar->jenis_isi_id
                ]);
            }
        }

        // kebutuhan 3: filter item_check_id
        elseif ((int)$request->kebutuhan_id === 3 && $request->filled('item_check_id')) {
            $query->where('item_check_id', $request->item_check_id);

            Log::info('Filter item_check_id dipakai', [
                'item_check_id' => $request->item_check_id
            ]);
        }

        // kebutuhan lain (misalnya 4, 5, dst)
        else {
            Log::info('Kebutuhan lain, tanpa filter tambahan');
        }

        // Debug query
        Log::info('SQL yang dieksekusi', [
            'sql'      => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        $harga = $query->first();

        $response = null;
        if ($harga) {
            $response = [
                'biaya_id' => $harga->id,
                'biaya'    => $harga->biaya,
            ];
        }

        Log::info('Hasil Query getBiaya', ['response' => $response]);

        return response()->json($response);
    }

    /**
     * Ekspor data transaksi ke file Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function exportExcel(Request $request)
    {
        $query = Transaksi::query()->with(['vendor', 'kebutuhan', 'masterApar']);
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
        
        // Perbarui mergeCells untuk judul karena jumlah kolom berkurang
        $sheet->setCellValue('A1', 'Laporan Riwayat Transaksi: ' . $titleStartDate . ' - ' . $titleEndDate);
        $sheet->mergeCells('A1:F1'); // Berubah dari 'A1:G1' menjadi 'A1:F1'
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Hapus header "Tanggal Pelunasan"
        $headers = ['No.', 'Kode APAR', 'Vendor', 'Kebutuhan', 'Tanggal Pembelian', 'Biaya']; // Dihapus
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
            $biaya = $transaksi->biaya;

            // Tulis data ke sheet
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, optional($transaksi->masterApar)->kode);
            $sheet->setCellValue('C' . $row, optional($transaksi->vendor)->nama_vendor);
            $sheet->setCellValue('D' . $row, optional($transaksi->kebutuhan)->kebutuhan);
            $sheet->setCellValue('E' . $row, $transaksi->tanggal_pembelian);
            // Baris untuk tanggal pelunasan dihapus
            $sheet->setCellValue('F' . $row, $biaya);
            
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0'); // Perbarui kolom menjadi 'F'

            $totalBiaya += $biaya;
            $row++;
        }
        
        // Baris Total Biaya
        $sheet->mergeCells('A' . $row . ':E' . $row); // Berubah dari 'A:F' menjadi 'A:E'
        $sheet->setCellValue('A' . $row, 'Total Biaya');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('F' . $row, $totalBiaya); // Berubah dari 'G' menjadi 'F'
        $sheet->getStyle('F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');

        // Baris Total Transaksi
        $row++;
        $sheet->mergeCells('A' . $row . ':E' . $row); // Berubah dari 'A:F' menjadi 'A:E'
        $sheet->setCellValue('A' . $row, 'Total Transaksi');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('F' . $row, $transaksis->count()); // Berubah dari 'G' menjadi 'F'
        $sheet->getStyle('F' . $row)->getFont()->setBold(true);

        // Styling Border
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A2:' . $highestColumn . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Auto size kolom
        foreach (range('A', $highestColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set header untuk download dengan nama file dinamis
        $formattedStartDate = $startDate ? Carbon::parse($startDate)->format('Y-m-d') : 'semua-tanggal';
        $formattedEndDate = $endDate ? Carbon::parse($endDate)->format('Y-m-d') : 'semua-tanggal';
        $fileName = "riwayat-penggunaan-apar-{$formattedStartDate}-{$formattedEndDate}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
