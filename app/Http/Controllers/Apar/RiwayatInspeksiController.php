<?php

namespace App\Http\Controllers\Apar;

use App\Http\Controllers\Controller;
use App\Models\AparInspection;
use App\Models\AparInspectionDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Penggunaan;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RiwayatInspeksiExport;

class RiwayatInspeksiController extends Controller
{
    /**
     * Menampilkan riwayat inspeksi APAR.
     * Menerapkan filter tanggal dan pencarian.
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Eager load relasi dan tambahkan relasi Penggunaan terbaru
        $query = AparInspection::with([
            'masterApar.gedung',
            'user',
            'masterApar.latestPenggunaan' // Menggunakan relasi baru
        ])
            ->orderBy('date', 'desc');

        $query->whereBetween('date', [$startDate, $endDate]);

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

    public function recycle(Request $request)
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
    
        return back()->with('success', 'Item berhasil di-recycle.');
    }

    public function export(Request $request)
    {
        // Mendapatkan tanggal dari request atau menggunakan tanggal default
        $startDate = $request->filled('start_date') ? Carbon::parse($request->input('start_date'))->translatedFormat('d-m-Y') : 'semua-tanggal';
        $endDate = $request->filled('end_date') ? Carbon::parse($request->input('end_date'))->translatedFormat('d-m-Y') : 'semua-tanggal';

        // Buat nama file yang dinamis
        $fileName = "riwayat-penggunaan-apar-{$startDate}-{$endDate}.xlsx";

        return Excel::download(new RiwayatInspeksiExport($request), $fileName);
    }

    public function updateItemStatus(AparInspectionDetail $detail)
    {
        $detail->update([
            'value' => 'B',
            'remark' => null,
        ]);

        return redirect()->back()->with('success', 'Status item check berhasil diubah menjadi "Baik".');
    }
}