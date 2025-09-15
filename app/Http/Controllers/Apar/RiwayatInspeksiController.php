<?php

namespace App\Http\Controllers\Apar;

use App\Http\Controllers\Controller;
use App\Models\AparInspection;
use App\Models\AparInspectionDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel; // Tambahkan baris ini
use App\Exports\RiwayatInspeksiExport; // Tambahkan baris ini

class RiwayatInspeksiController extends Controller
{
    /**
     * Menampilkan riwayat inspeksi APAR.
     * Menerapkan filter tanggal dan pencarian.
     */
    public function index(Request $request)
    {
        // 1. Tambahkan nilai default untuk tanggal
        // Jika parameter tidak ada, gunakan awal dan akhir bulan saat ini.
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $query = AparInspection::with(['masterApar.gedung', 'user'])
            ->orderBy('date', 'desc');

        // 2. Selalu terapkan filter tanggal dengan nilai yang sudah ditentukan di atas
        $query->whereBetween('date', [$startDate, $endDate]);

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
        return Excel::download(new RiwayatInspeksiExport($request), 'riwayat_inspeksi.xlsx');
    }

    public function updateItemStatus(AparInspectionDetail $detail)
    {
        // Perbarui nilai item check menjadi 'B' (Baik) dan kosongkan remark.
        $detail->update([
            'value' => 'B',
            'remark' => null, 
        ]);

        return redirect()->back()->with('success', 'Status item check berhasil diubah menjadi "Baik".');
    }
}
