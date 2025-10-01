<?php

namespace App\Http\Controllers;

// Pastikan semua model yang digunakan diimpor di sini
use App\Models\MasterApar;
use App\Models\Gedung;
use App\Models\JenisIsi;
use App\Models\JenisPemadam;
use App\Models\Vendor;
use App\Models\AparInspection;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use App\Models\Kebutuhan;
use App\Models\Transaksi;
use App\Models\HargaKebutuhan;

class MasterAparController extends Controller
{
    /**
     * Menampilkan daftar master APAR.
     */
    public function index(Request $request)
    {
        $apars = MasterApar::with('gedung')
            ->when($request->kode, fn($q) => $q->where('kode', 'like', '%' . $request->kode . '%'))
            ->when($request->gedung_id, fn($q) => $q->where('gedung_id', $request->gedung_id))
            ->when($request->lokasi, fn($q) => $q->where('lokasi', 'like', '%' . $request->lokasi . '%'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $gedungs = Gedung::all();
        return view('pages.master_apar.index', compact('apars', 'gedungs'));
    }

    /**
     * Menampilkan laporan APAR rusak dengan filter tanggal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function laporanAparRusakIndex(Request $request)
    {
        $query = AparInspection::query();

        // Filter data APAR yang kondisinya "NOT GOOD"
        $query->whereHas('details', function ($query) {
            $query->where('value', '!=', 'B');
        });

        // Terapkan filter tanggal jika input terisi
        if ($request->filled(['start_date', 'end_date'])) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        // Ambil data dengan eager loading relasi untuk performa yang lebih baik
        $aparInspections = $query->with([
            'masterApar.gedung',
            'details.itemCheck',
            'user'
        ])->orderBy('date', 'desc')->get();

        // Hitung total data
        $totalData = $aparInspections->count();

        // Kirimkan data ke view (rusak.blade.php)
        return view('laporan.apar.rusak', compact('aparInspections', 'totalData'));
    }

    /**
     * Tampilkan halaman riwayat inspeksi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function riwayatInspeksi(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $query = AparInspection::with(['masterApar.gedung', 'user', 'details.itemCheck']);

        // Menerapkan filter tanggal jika ada
        if ($request->filled(['start_date', 'end_date'])) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            $query->whereBetween('date', [$startDate, $endDate]);
        }
        
        \Log::info('Filter applied - Start Date: ' . $request->input('start_date') . ', End Date: ' . $request->input('end_date'));

        $aparInspections = $query->latest()->paginate(10)->withQueryString();
        return view('pages.riwayat_inspeksi.index', compact('aparInspections'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function refillReport(Request $request)
    {
        $expiredApar = MasterApar::with('gedung')
            ->whereDate('tgl_kadaluarsa', '<', Carbon::now())
            ->get();
        
        $expiringSoonApar = MasterApar::with('gedung')
            ->whereDate('tgl_kadaluarsa', '>=', Carbon::now())
            ->whereDate('tgl_kadaluarsa', '<=', Carbon::now()->addDays(30))
            ->get();
        
        $expiredApars = $expiredApar->merge($expiringSoonApar)->sortBy('tgl_kadaluarsa');
        $totalData = $expiredApars->count();
        
        return view('pages.laporan.apar.refill', compact('expiredApar', 'expiringSoonApar', 'expiredApars', 'totalData'));
    }

    public function create()
    {
        $gedungs = Gedung::all();
        $jenisIsis = JenisIsi::all();
        $jenisPemadams = JenisPemadam::all();
        
        // Tambahkan baris ini untuk mengambil data vendor dari database
        $vendors = Vendor::all(); 

        // Kirimkan semua data ke view
        return view('pages.master_apar.create', compact('gedungs', 'jenisIsis', 'jenisPemadams', 'vendors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    // app/Http/Controllers/MasterAparController.php

    public function store(Request $request)
    {
        try {
            \DB::beginTransaction();

            // 1. Validasi data yang masuk dari request
            $validated = $request->validate([
                'kode' => 'required|string|max:8|unique:master_apars,kode',
                'gedung_id' => 'required|exists:gedungs,id',
                'lokasi' => 'required|string',
                'tgl_kadaluarsa' => 'required|date',
                'ukuran' => 'required|numeric',
                'satuan' => 'required|string',
                'jenis_isi_id' => 'required|exists:jenis_isis,id',
                'jenis_pemadam_id' => 'required|exists:jenis_pemadams,id',
                'catatan' => 'nullable|string|max:255',
                'is_new_apar' => 'nullable', 
                'vendor_id' => 'required_if:is_new_apar,1|nullable|exists:vendors,id',
                'tanggal_pembelian' => 'required_if:is_new_apar,1|nullable|date',
            ]);
            
            // 2. Simpan data APAR baru ke tabel master_apars
            $masterApar = MasterApar::create([
                'kode' => $validated['kode'],
                'gedung_id' => $validated['gedung_id'],
                'lokasi' => $validated['lokasi'],
                'tgl_kadaluarsa' => $validated['tgl_kadaluarsa'],
                'ukuran' => $validated['ukuran'],
                'satuan' => $validated['satuan'],
                'jenis_isi_id' => $validated['jenis_isi_id'],
                'jenis_pemadam_id' => $validated['jenis_pemadam_id'],
                'catatan' => $validated['catatan'] ?? null,
                'is_active' => 1,
            ]);
            
            // 3. Jika ini APAR baru, buat juga data transaksinya
            if ($request->boolean('is_new_apar')) {
                
                $searchCriteria = [
                    'vendor_id' => $validated['vendor_id'],
                    'kebutuhan_id' => 1, // Kebutuhan ID untuk 'Beli Baru'
                    'jenis_pemadam_id' => $validated['jenis_pemadam_id'],
                    'jenis_isi_id' => $validated['jenis_isi_id'],
                ];

                // Log kriteria pencarian untuk debugging
                \Log::info('Mencari harga kebutuhan APAR baru dengan kriteria:', $searchCriteria);
                
                // Cari harga dasar dari tabel harga_kebutuhans
                $harga = \DB::table('harga_kebutuhans')
                    ->where($searchCriteria)
                    ->first();

                if (!$harga) {
                    // Catat error dengan kriteria pencarian sebelum melempar exception
                    \Log::error('Harga kebutuhan APAR baru tidak ditemukan.', $searchCriteria);
                    throw new \Exception('Harga kebutuhan tidak ditemukan untuk kombinasi tersebut.');
                }
                
                // --- BAGIAN INI SUDAH DIUBAH ---
                $biayaFinal = $harga->biaya;

                // Simpan data transaksi ke tabel transaksis
                Transaksi::create([
                    'master_apar_id' => $masterApar->id,
                    'vendor_id' => $validated['vendor_id'],
                    'kebutuhan_id' => 1, // Kebutuhan ID untuk 'Beli Baru'
                    'biaya_id' => $harga->id,
                    'biaya' => $biayaFinal,
                    'tanggal_pembelian' => $validated['tanggal_pembelian'],
                ]);
            }
            
            // 4. Commit transaksi database jika semua proses berhasil
            \DB::commit();

            return redirect()->route('master-apar.index')
                ->with('success', 'Data APAR dan transaksi berhasil disimpan.');
        } catch (\Exception $e) {
            // 5. Rollback transaksi jika terjadi error
            \DB::rollBack();
            
            \Log::error('Exception during store process: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menyimpan data. Pastikan semua data terisi dengan benar. Error: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MasterApar $masterApar)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MasterApar $masterApar)
    {
        // Eager load relasi jenis_isi dan jenis_pemadam untuk mencegah error "Attempt to read property 'jenis_isi' on null" di view
        $masterApar->load('jenisIsi', 'jenisPemadam');

        $gedungs = Gedung::all();
        // Mengambil semua data jenis isi dan jenis pemadam
        $jenisIsis = JenisIsi::all();
        $jenisPemadams = JenisPemadam::all();
        // Meneruskan data ke view
        return view('pages.master_apar.edit', compact('masterApar', 'gedungs', 'jenisIsis', 'jenisPemadams'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MasterApar $masterApar)
    {
        $request->validate([
            'kode' => 'required|string|max:8|unique:master_apars,kode,'.$masterApar->id,
            'gedung_id' => 'required|exists:gedungs,id',
            'lokasi' => 'required|string',
            'tgl_kadaluarsa' => 'required|date',
            'ukuran' => 'required|numeric',
            'satuan' => 'required|string',
            'jenis_isi_id' => 'required|exists:jenis_isis,id', // Diperbaiki: Menggunakan ID
            'jenis_pemadam_id' => 'required|exists:jenis_pemadams,id', // Diperbaiki: Menggunakan ID
            'catatan' => 'nullable|string|max:255',
        ]);

        $masterApar->update($request->all());

        return redirect()->route('master-apar.index')->with('success', 'Data APAR berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterApar $masterApar)
    {
        $masterApar->delete(); // Hard delete
        return redirect()->route('master-apar.index')->with('success', 'Data APAR berhasil dihapus');
    }

    public function restore($id)
    {
        $apar = MasterApar::findOrFail($id);
        $apar->update(['is_active' => true]);

        return redirect()->route('master-apar.index')->with('success', 'Data APAR diaktifkan kembali');
    }

    public function generateQr($id)
    {
        $apar = MasterApar::findOrFail($id);
        $writer = new PngWriter();
        // Create QR code
        $qrCode = new QrCode(
            data: $apar->kode,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 500,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        $result = $writer->write($qrCode);

        return Response::make(
            $result->getString(), 
            200, 
            [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'attachment; filename="qr-apar-'.$apar->kode.'.png"',
            ]
        );
    }

    // Tambahkan di dalam class MasterAparController
    public function showQrCode($id)
    {
        $apar = MasterApar::findOrFail($id);

        // Buat data yang akan di-encode ke dalam QR Code
        $qrData = json_encode([
            'kode' => $apar->kode,
            'lokasi' => $apar->lokasi,
        ]);

        // Buat QR code
        $writer = new PngWriter();
        $qrCode = new QrCode(
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 500,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        // Dapatkan data QR code dalam format string Base64
        $qrCodeDataUri = $writer->write($qrCode)->getDataUri();

        // Kirim data ke view
        return view('pages.master_apar.show_qr', compact('apar', 'qrCodeDataUri'));
    }

    public function downloadQr($id)
    {
        $apar = MasterApar::with('gedung')->findOrFail($id);
        $writer = new PngWriter();
        $qrData = route('public.apar.history', ['kode' => $apar->kode]); // Ubah ke kode
        $qrCode = new QrCode(
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 500,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );
        $qrCodeData = $writer->write($qrCode)->getString();

        $qrImage = imagecreatefromstring($qrCodeData);
        $qrWidth = imagesx($qrImage);
        $qrHeight = imagesy($qrImage);

        $fontSize = 30;
        $padding = 30;
        $textHeight = $fontSize * 3;
        $imageHeight = $qrHeight + $textHeight + $padding;
        $imageWidth = $qrWidth;

        $finalImage = imagecreatetruecolor($imageWidth, $imageHeight);
        $white = imagecolorallocate($finalImage, 255, 255, 255);
        $black = imagecolorallocate($finalImage, 0, 0, 0);

        imagefill($finalImage, 0, 0, $white);
        imagecopy($finalImage, $qrImage, 0, 0, 0, 0, $qrWidth, $qrHeight);
        imagedestroy($qrImage);

        $textKode = $apar->kode;
        $textLokasi = $apar->gedung->nama . " - " . $apar->lokasi;
        $fontPath = public_path('fonts/arial.ttf');

        $bboxKode = imagettfbbox($fontSize, 0, $fontPath, $textKode);
        $textWidthKode = $bboxKode[2] - $bboxKode[0];
        $xKode = ($imageWidth - $textWidthKode) / 2;
        $yKode = $qrHeight + $padding + 10;
        imagettftext($finalImage, $fontSize, 0, $xKode, $yKode, $black, $fontPath, $textKode);

        $bboxLokasi = imagettfbbox($fontSize, 0, $fontPath, $textLokasi);
        $textWidthLokasi = $bboxLokasi[2] - $bboxLokasi[0];
        $xLokasi = ($imageWidth - $textWidthLokasi) / 2;
        $yLokasi = $yKode + $fontSize + 20;
        imagettftext($finalImage, $fontSize, 0, $xLokasi, $yLokasi, $black, $fontPath, $textLokasi);

        ob_start();
        imagepng($finalImage);
        $imageContent = ob_get_clean();
        imagedestroy($finalImage);

        return Response::make(
            $imageContent,
            200,
            [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'attachment; filename="qr-apar-' . $apar->kode . '.png"',
            ]
        );
    }

    /**
     * Menangani pemindaian QR code untuk mengarahkan pengguna.
     * Menggunakan kode APAR yang diambil dari QR code.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $kode Kode APAR yang dipindai.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleQrScan(Request $request, $kode)
    {
        $isFromApp = $request->header('X-App-Access');
        
        if ($isFromApp) {
            // Find APAR by kode and fail if not found
            $apar = MasterApar::where('kode', $kode)->firstOrFail();
            return redirect()->route('inspeksi.form', ['apar_id' => $apar->id]);
        } else {
            return redirect()->route('public.apar.history', ['kode' => $kode]);
        }
    }
    
    // Metode baru untuk mendapatkan ukuran berdasarkan kode APAR
    public function getUkuran($id)
    {
        $apar = MasterApar::find($id);

        if (!$apar) {
            return response()->json(['error' => 'APAR tidak ditemukan'], 404);
        }

        // Asumsi kolom ukuran adalah 'ukuran' di tabel master_apars
        return response()->json($apar->ukuran);
    }
}
