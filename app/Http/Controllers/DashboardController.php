<?php

namespace App\Http\Controllers;

use App\Models\Gedung;
use App\Models\MasterApar;
use App\Models\AparInspection;
use App\Models\Transaksi;
use App\Models\AparInspectionDetail;
use App\Models\Penggunaan;
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
        
        $notGoodAparIds = AparInspectionDetail::where('value', '!=', 'B')
            ->whereIn('apar_inspection_id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                      ->from('apar_inspections')
                      ->groupBy('master_apar_id');
            })
            ->join('apar_inspections', 'apar_inspection_details.apar_inspection_id', '=', 'apar_inspections.id')
            ->distinct()
            ->pluck('apar_inspections.master_apar_id');

        $notGoodPenggunaanIds = Penggunaan::where('status', 'NOT GOOD')
                                         ->pluck('master_apar_id');
        
        $finalNotGoodAparIds = $notGoodAparIds
                                         ->merge($notGoodPenggunaanIds)
                                         ->unique();

        $activeAparInGedungs = MasterApar::where('is_active', true)->get()->groupBy('gedung_id');
        
        $dataGood = [];
        $dataNotGood = [];
        
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
        $expiredApar = MasterApar::with('gedung')
            ->where('tgl_kadaluarsa', '<', Carbon::now()->toDateString())
            ->get();
        
        $expiringSoonApar = MasterApar::with('gedung')
            ->whereBetween('tgl_kadaluarsa', [Carbon::now()->toDateString(), Carbon::now()->addDays(30)->toDateString()])
            ->get();
            
        // --- Logika Notifikasi APAR Belum Diinspeksi Bulan Ini ---
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $inspectedAparIds = AparInspection::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->pluck('master_apar_id');
        $uninspectedApars = MasterApar::whereNotIn('id', $inspectedAparIds)
            ->where('is_active', true)
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

        // --- MENGHITUNG TOTAL PENGELUARAN (Sesuai permintaan) ---
        // Mengubah logika menjadi hanya menjumlahkan kolom 'biaya' dari tabel 'transaksis'
        $totalPengeluaran = Transaksi::whereBetween('tanggal_pembelian', [$displayStartDate, $displayEndDate])
                                     ->sum('biaya');

        // --- LOGIKA GRAFIK KEUANGAN BULANAN (Perbaikan) ---
        // Mengubah logika menjadi hanya menjumlahkan kolom 'biaya' dari tabel 'transaksis'
        $monthlyPengeluaran = Transaksi::select(
                DB::raw('SUM(biaya) as total_biaya'),
                DB::raw('strftime("%Y-%m", tanggal_pembelian) as month_year')
            )
            ->whereBetween('tanggal_pembelian', [$displayStartDate, $displayEndDate])
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
            'usedApars',
            'totalPengeluaran',
            'labelsPengeluaran',
            'dataPengeluaran'
        ));
    }
}