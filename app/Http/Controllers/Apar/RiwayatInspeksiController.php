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
