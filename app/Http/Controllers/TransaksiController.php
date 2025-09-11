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
            'harga_kebutuhan_id' => 'required|exists:harga_kebutuhans,id',
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
        // 1. Validasi data form
        $request->validate([
            'kode' => 'required|string|max:8|unique:master_apars,kode',
            'gedung_id' => 'required|exists:gedungs,id',
            'lokasi' => 'required|string',
            'tgl_kadaluarsa' => 'required|date',
            'ukuran' => 'required|numeric',
            'satuan' => 'required|string',
            'jenis_isi_id' => 'required|exists:jenis_isis,id',
            'jenis_pemadam_id' => 'required|exists:jenis_pemadams,id',
            'catatan' => 'nullable|string|max:255',
            'is_new_apar' => 'nullable|boolean', // Validasi untuk checkbox
            'vendor_id' => 'required_if:is_new_apar,true|exists:vendors,id', // Wajib jika APAR BARU
            'tanggal_pembelian' => 'required_if:is_new_apar,true|date', // Wajib jika APAR BARU
        ]);

        // 2. Buat data APAR baru dan ambil ID-nya
        $apar = MasterApar::create($request->all());

        // 3. Cek apakah APAR BARU dicentang
        if ($request->has('is_new_apar')) {
            // 4. Ambil ID dari vendor, kebutuhan, dan biaya
            $kebutuhan = Kebutuhan::where('nama_kebutuhan', 'Beli Baru')->first();
            $biaya = HargaKebutuhan::where('vendor_id', $request->vendor_id)
                                    ->where('kebutuhan_id', $kebutuhan->id)
                                    ->where('jenis_pemadam_id', $request->jenis_pemadam_id)
                                    ->where('jenis_isi_id', $request->jenis_isi_id)
                                    ->first();
            
            // Periksa jika data yang dibutuhkan ditemukan
            if ($kebutuhan && $biaya) {
                // 5. Simpan transaksi ke tabel 'transaksis'
                Transaksi::create([
                    'master_apar_id' => $apar->id,
                    'vendor_id' => $request->vendor_id,
                    'kebutuhan_id' => $kebutuhan->id,
                    'biaya_id' => $biaya->id,
                    'tanggal_pembelian' => $request->tanggal_pembelian,
                ]);
            }
        }

        return redirect()->route('master-apar.index')->with('success', 'Data APAR berhasil ditambahkan');
    }

    /**
     * Tampilkan formulir untuk mengedit transaksi.
     *
     * @param  \App\Models\Transaksi  $transaksi
     * @return \Illuminate\View\View
     */
    public function edit(Transaksi $transaksi)
    {
        // Memuat relasi masterApar untuk mendapatkan jenis_pemadam_id dan jenis_isi_id
        $transaksi->load('masterApar');

        $vendors = Vendor::all();
        $kebutuhans = Kebutuhan::all();
        $masterApars = MasterApar::all();
        $hargaKebutuhans = HargaKebutuhan::all();
        // Menambahkan variabel yang diperlukan untuk tampilan edit
        $jenisPemadams = JenisPemadam::all();
        $jenisIsis = JenisIsi::all();
        $itemChecks = ItemCheck::all();
        return view('pages.transaksi.edit', compact('transaksi', 'vendors', 'kebutuhans', 'masterApars', 'hargaKebutuhans', 'jenisPemadams', 'jenisIsis', 'itemChecks'));
    }

    /**
     * Perbarui transaksi yang ada di database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transaksi  $transaksi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Transaksi $transaksi)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'kebutuhan_id' => 'required|exists:kebutuhans,id',
            'master_apar_id' => 'nullable|exists:master_apars,id',
            'tanggal_pembelian' => 'required|date',
            'tanggal_pelunasan' => 'nullable|date',
            'harga_kebutuhan_id' => 'required|exists:harga_kebutuhans,id',
        ]);

        $hargaKebutuhan = HargaKebutuhan::findOrFail($request->input('harga_kebutuhan_id'));
        $biaya = $hargaKebutuhan->biaya;

        $transaksi->update(array_merge($request->except('biaya_display'), [
            'biaya_id' => $hargaKebutuhan->id,
            'biaya' => $biaya,
        ]));

        return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil diperbarui!');
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
        // Mulai query untuk model Transaksi
        $query = Transaksi::query()->with(['vendor', 'kebutuhan', 'masterApar', 'hargaKebutuhan']);

        // Ambil tanggal awal dari request, jika ada
        $startDate = $request->input('start_date');

        // Ambil tanggal akhir dari request, jika ada
        $endDate = $request->input('end_date');

        // Jika tanggal awal dan tanggal akhir disediakan, tambahkan filter
        if ($startDate && $endDate) {
            $query->whereBetween('tanggal_pembelian', [$startDate, $endDate]);
        }
        
        // Dapatkan data transaksi yang telah difilter
        $transaksis = $query->orderBy('tanggal_pembelian', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Format tanggal untuk judul
        $titleStartDate = $startDate ? Carbon::parse($startDate)->format('d F Y') : 'Awal';
        $titleEndDate = $endDate ? Carbon::parse($endDate)->format('d F Y') : 'Sekarang';
        
        // Baris 1: Informasi Tanggal dan judul
        $sheet->setCellValue('A1', 'Laporan Riwayat Transaksi: ' . $titleStartDate . ' - ' . $titleEndDate);
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Baris 2: Header Tabel
        $headers = ['No.', 'Kode APAR', 'Vendor', 'Kebutuhan', 'Tanggal Pembelian', 'Tanggal Pelunasan', 'Biaya'];
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
            
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, optional($transaksi->masterApar)->kode);
            $sheet->setCellValue('C' . $row, optional($transaksi->vendor)->nama_vendor);
            $sheet->setCellValue('D' . $row, optional($transaksi->kebutuhan)->kebutuhan);
            $sheet->setCellValue('E' . $row, $transaksi->tanggal_pembelian);
            $sheet->setCellValue('F' . $row, $transaksi->tanggal_pelunasan);
            $sheet->setCellValue('G' . $row, $biaya);
            
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');

            $totalBiaya += $biaya;
            $row++;
        }
        
        // Baris Total Biaya (dengan penggabungan sel A-F)
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->setCellValue('A' . $row, 'Total Biaya');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('G' . $row, $totalBiaya);
        $sheet->getStyle('G' . $row)->getFont()->setBold(true);
        $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');

        // Baris Total Transaksi (dengan penggabungan sel A-F)
        $row++;
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->setCellValue('A' . $row, 'Total Transaksi');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('G' . $row, $transaksis->count());
        $sheet->getStyle('G' . $row)->getFont()->setBold(true);

        // Styling Border
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A2:' . $highestColumn . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Auto size kolom
        foreach (range('A', $highestColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set header untuk download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Laporan-Transaksi-' . Carbon::now()->format('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
