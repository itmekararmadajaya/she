<?php

namespace App\Http\Controllers\Apar;

use App\Http\Controllers\Controller;
use App\Models\MasterApar;
use App\Models\AparInspection;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * Menampilkan laporan tahunan. Metode ini sudah ada sebelumnya.
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
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
        
        return view('pages.apar.laporan.yearly', [
            'year' => $year,
            'kode' => $kode,
            'apar' => $apar,
        ]);
    }

    /**
     * Menampilkan laporan APAR rusak dengan fitur pencarian dan filter.
     * Metode baru untuk rute laporan.apar.rusak-index.
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function rusakIndex(Request $request)
    {
        // Mulai query dasar untuk AparInspection.
        // Asumsi model AparInspection memiliki relasi ke MasterApar.
        $query = AparInspection::whereHas('details', function ($q) {
            $q->where('value', '!=', 'B');
        })->with('masterApar.gedung', 'details.itemCheck');

        // Mengambil parameter dari request
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // --- Logika Pencarian (Search) ---
        // Jika parameter 'search' ada, terapkan filter pencarian.
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Mencari di tabel MasterApar berdasarkan kode, gedung, atau lokasi
                $q->whereHas('masterApar', function ($qApar) use ($search) {
                    $qApar->where('kode', 'like', "%{$search}%")
                          ->orWhere('lokasi', 'like', "%{$search}%")
                          ->orWhereHas('gedung', function ($qGedung) use ($search) {
                              $qGedung->where('nama', 'like', "%{$search}%");
                          });
                });
                
                // Mencari di tabel details berdasarkan nama item atau remark (kerusakan)
                // Ini akan menemukan "segel pin" dan kerusakan lainnya.
                $q->orWhereHas('details', function ($qDetails) use ($search) {
                    $qDetails->where('value', '!=', 'B')
                             ->where(function($qItem) use ($search) {
                                 $qItem->whereHas('itemCheck', function ($qCheck) use ($search) {
                                     $qCheck->where('name', 'like', "%{$search}%");
                                 })->orWhere('remark', 'like', "%{$search}%");
                             });
                });
            });
        }

        // --- Logika Filter Tanggal ---
        // Jika parameter 'start_date' ada, tambahkan filter tanggal mulai.
        if ($startDate) {
            $query->whereDate('tanggal_inspeksi', '>=', $startDate);
        }

        // Jika parameter 'end_date' ada, tambahkan filter tanggal selesai.
        if ($endDate) {
            $query->whereDate('tanggal_inspeksi', '<=', $endDate);
        }

        // Ambil data yang sudah difilter dan hitung totalnya
        $aparInspections = $query->get();
        $totalData = $aparInspections->count();

        // Mengembalikan tampilan dengan data yang sudah difilter
        return view('pages.apar.laporan.rusak', compact('aparInspections', 'totalData'));
    }
}
