<?php

namespace App\Http\Controllers;

use App\Models\Penggunaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PengisianAparExport;

class HistoryController extends Controller
{
    /**
     * Menampilkan halaman riwayat pengisian APAR.
     *
     * @return \Illuminate\View\View
     */
    public function pengisian()
    {
        // Mengubah query untuk sementara agar menampilkan semua data
        $refillHistory = Penggunaan::with(['masterApar', 'gedung'])->get();

        return view('pages.history.pengisian', compact('refillHistory'));
    }

    /**
     * Mengunduh data riwayat pengisian APAR dalam format Excel.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportPengisian(Request $request)
    {
        // Mengambil rentang tanggal dari request jika ada
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Query untuk mengambil semua data riwayat pengisian
        $query = Penggunaan::with(['user', 'masterApar', 'gedung']);
        
        // Jika ada rentang tanggal, tambahkan filter
        if ($startDate && $endDate) {
            $query->whereBetween('tanggal_penggunaan', [$startDate, $endDate]);
        }

        $refillHistory = $query->get();
        
        // Pastikan data user tersedia untuk kolom "Petugas" di file export
        // Kode `map()` kamu di PengisianAparExport sudah memanggil $item->user->name

        return Excel::download(new PengisianAparExport($refillHistory), 'riwayat-penggunaan-apar.xlsx');
    }

    /**
     * Memperbarui status APAR menjadi 'GOOD'.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatusToGood(Request $request, $id)
    {
        try {
            $penggunaan = Penggunaan::findOrFail($id);
            $penggunaan->status = 'GOOD';
            $penggunaan->save();

            return response()->json(['success' => true, 'message' => 'Status APAR berhasil diperbarui menjadi GOOD.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui status: ' . $e->getMessage()]);
        }
    }
}
