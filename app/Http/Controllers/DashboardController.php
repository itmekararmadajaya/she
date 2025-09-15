<?php

namespace App\Http\Controllers;

use App\Models\Gedung;
use App\Models\MasterApar;
use App\Models\AparInspection;
use App\Models\Transaksi;
use App\Models\AparInspectionDetail;
use App\Models\Penggunaan; // Import model Penggunaan
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard utama dengan data ringkasan dan grafik.
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Jika tidak ada tanggal yang dipilih, gunakan rentang default 12 bulan terakhir.
        $displayStartDate = $startDate ?? Carbon::now()->subMonths(11)->startOfMonth()->toDateString();
        $displayEndDate = $endDate ?? Carbon::now()->endOfMonth()->toDateString();
        
        // Ambil semua data gedung untuk digunakan sebagai label.
        $gedungs = Gedung::all();
        $labels = $gedungs->pluck('nama')->toArray();

        // --- Logika Status APAR (GOOD/NOT GOOD) - Menggunakan data inspeksi terbaru ---
        
        // 1. Dapatkan ID APAR yang memiliki setidaknya satu item yang 'tidak baik' (NOT GOOD)
        // Berdasarkan detail inspeksi terbaru. Kami menggunakan subquery untuk menemukan
        // ID inspeksi terbaru (maksimum ID) untuk setiap master_apar.
        $notGoodAparIds = AparInspectionDetail::where('value', '!=', 'B')
            ->whereIn('apar_inspection_id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                      ->from('apar_inspections')
                      ->groupBy('master_apar_id');
            })
            ->join('apar_inspections', 'apar_inspection_details.apar_inspection_id', '=', 'apar_inspections.id')
            ->distinct()
            ->pluck('apar_inspections.master_apar_id');

        // 2. Dapatkan ID APAR dari tabel Penggunaan dengan status 'NOT GOOD'
        $notGoodPenggunaanIds = Penggunaan::where('status', 'NOT GOOD')
                                         ->pluck('master_apar_id');
        
        // 3. Gabungkan kedua koleksi ID dan ambil ID unik
        $finalNotGoodAparIds = $notGoodAparIds
                                         ->merge($notGoodPenggunaanIds)
                                         ->unique();

        // 4. Dapatkan semua APAR yang aktif
        $activeAparInGedungs = MasterApar::where('is_active', true)->get()->groupBy('gedung_id');
        
        $dataGood = [];
        $dataNotGood = [];
        
        // 5. Hitung jumlah GOOD dan NOT GOOD per gedung
        foreach ($gedungs as $gedung) {
            $aparInThisGedung = $activeAparInGedungs->get($gedung->id, collect());
            
            $goodCount = $aparInThisGedung->whereNotIn('id', $finalNotGoodAparIds)->count();
            $notGoodCount = $aparInThisGedung->whereIn('id', $finalNotGoodAparIds)->count();
            
            $dataGood[] = $goodCount;
            $dataNotGood[] = $notGoodCount;
        }

        $totalGood = array_sum($dataGood);
        $totalNotGood = array_sum($dataNotGood);
        $totalInspections = $totalGood + $totalNotGood;
        $totalNotGoodPercentage = ($totalInspections > 0) ? round(($totalNotGood / $totalInspections) * 100) : 0;
        
        // --- LOGIKA NOTIFIKASI KEDALUWARSA ---
        // Tidak terpengaruh filter tanggal karena ini adalah notifikasi terkini.
        $expiredApar = MasterApar::with('gedung')
            ->where('tgl_kadaluarsa', '<', Carbon::now()->toDateString())
            ->get();
        
        $expiringSoonApar = MasterApar::with('gedung')
            ->whereBetween('tgl_kadaluarsa', [Carbon::now()->toDateString(), Carbon::now()->addDays(30)->toDateString()])
            ->get();
            
        // --- Logika Notifikasi APAR Belum Diinspeksi Bulan Ini ---
        // Tidak terpengaruh filter tanggal.
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $inspectedAparIds = AparInspection::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->pluck('master_apar_id');
        $uninspectedApars = MasterApar::whereNotIn('id', $inspectedAparIds)
            ->where('is_active', true) // Filter APAR yang aktif saja
            ->with('gedung') 
            ->get();

        // --- Logika Notifikasi APAR Digunakan ---
        $usedApars = Penggunaan::with('masterApar.gedung')
            ->where('status', 'NOT GOOD')
            ->get()
            ->map(function ($penggunaan) {
                return $penggunaan->masterApar;
            });
            
        // --- Logika Tabel Rekap Inspeksi Per Area - Dioptimalkan ---
        // Menggunakan satu query untuk mendapatkan jumlah inspeksi per gedung.
        $inspectionsPerAreaCounts = AparInspection::select('master_apars.gedung_id', DB::raw('count(distinct master_apar_id) as inspected_count'))
            ->join('master_apars', 'apar_inspections.master_apar_id', '=', 'master_apars.id')
            ->whereBetween('apar_inspections.date', [$displayStartDate, $displayEndDate])
            ->groupBy('master_apars.gedung_id')
            ->get();

        $inspectionsPerArea = [];
        $totalInspected = 0;
        $totalUninspected = 0;
        
        foreach ($gedungs as $gedung) {
            $totalAparInGedung = $gedung->masterApars->where('is_active', true)->count();
            $inspectedCount = $inspectionsPerAreaCounts->where('gedung_id', $gedung->id)->first()->inspected_count ?? 0;
            $uninspectedCount = $totalAparInGedung - $inspectedCount;
            
            $inspectionsPerArea[] = [
                'nama' => $gedung->nama,
                'inspected' => $inspectedCount,
                'uninspected' => $uninspectedCount,
            ];
            $totalInspected += $inspectedCount;
            $totalUninspected += $uninspectedCount;
        }

        // --- MENGHITUNG TOTAL PENGELUARAN (Sesuai dengan logika baru) ---
        $totalPengeluaran = Transaksi::join('master_apars', 'transaksis.master_apar_id', '=', 'master_apars.id')
            ->join('harga_kebutuhans', 'transaksis.biaya_id', '=', 'harga_kebutuhans.id')
            ->whereBetween('transaksis.tanggal_pembelian', [$displayStartDate, $displayEndDate])
            ->sum(DB::raw('CASE WHEN transaksis.kebutuhan_id IN (1, 2) THEN harga_kebutuhans.biaya * master_apars.ukuran ELSE harga_kebutuhans.biaya END'));

        // --- LOGIKA GRAFIK KEUANGAN BULANAN (Perbaikan) ---
        // Mengganti DATE_FORMAT dengan strftime untuk kompatibilitas SQLite
        $monthlyPengeluaran = Transaksi::join('master_apars', 'transaksis.master_apar_id', '=', 'master_apars.id')
            ->join('harga_kebutuhans', 'transaksis.biaya_id', '=', 'harga_kebutuhans.id')
            ->select(
                DB::raw('SUM(CASE WHEN transaksis.kebutuhan_id IN (1, 2) THEN harga_kebutuhans.biaya * master_apars.ukuran ELSE harga_kebutuhans.biaya END) as total_biaya'),
                DB::raw('strftime("%Y-%m", transaksis.tanggal_pembelian) as month_year')
            )
            ->whereBetween('transaksis.tanggal_pembelian', [$displayStartDate, $displayEndDate])
            ->groupBy('month_year')
            ->orderBy('month_year', 'asc')
            ->get();
        
        $labelsPengeluaran = [];
        $dataPengeluaran = [];
        $dataMap = $monthlyPengeluaran->keyBy('month_year')->toArray();

        // Loop untuk mengisi array label dan data dengan data 12 bulan terakhir
        $currentMonth = Carbon::parse($displayStartDate);
        while ($currentMonth->lessThanOrEqualTo(Carbon::parse($displayEndDate))) {
            $monthYearKey = $currentMonth->format('Y-m');
            
            $labelsPengeluaran[] = $currentMonth->translatedFormat('F Y');
            $dataPengeluaran[] = $dataMap[$monthYearKey]['total_biaya'] ?? 0;
            
            $currentMonth->addMonth();
        }
        
        // Ringkasan data (tambahan untuk melengkapi dashboard)
        $totalApar = MasterApar::count();
        $totalAparAktif = MasterApar::where('is_active', true)->count();
        $kadaluarsa = MasterApar::whereDate('tgl_kadaluarsa', '<', Carbon::now())->count();

        return view('pages.dashboard', compact(
            'totalApar',
            'totalAparAktif',
            'kadaluarsa',
            'labels', 
            'dataGood', 
            'dataNotGood', 
            'totalNotGoodPercentage', 
            'startDate', 
            'endDate',
            'uninspectedApars',
            'inspectionsPerArea',
            'totalInspected',
            'totalUninspected',
            'expiredApar',
            'expiringSoonApar',
            'usedApars', // Tambahkan variabel ini
            'totalPengeluaran',
            'labelsPengeluaran',
            'dataPengeluaran'
        ));
    }
}
