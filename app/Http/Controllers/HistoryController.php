<?php

namespace App\Http\Controllers;

use App\Models\Penggunaan;
use App\Models\MasterApar;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PengisianAparExport;
use Illuminate\Support\Facades\Log;

class HistoryController extends Controller
{
    public function pengisian(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Penggunaan::with(['user', 'masterApar', 'gedung']);

        if ($startDate && $endDate) {
            $start = Carbon::createFromFormat('Y-m', $startDate)->startOfMonth();
            $end = Carbon::createFromFormat('Y-m', $endDate)->endOfMonth();
            $query->whereBetween('tanggal_penggunaan', [$start, $end]);
        }

        $refillHistory = $query->get();

        foreach ($refillHistory as $item) {
            if ($item->status === 'NOT GOOD') {
                $latestRefill = Transaksi::where('master_apar_id', $item->master_apar_id)
                                          ->where('kebutuhan_id', 2) // Kebutuhan_id untuk 'Refill'
                                          ->where('tanggal_pembelian', '>', $item->tanggal_penggunaan)
                                          ->orderBy('tanggal_pembelian', 'desc')
                                          ->first();
                
                if ($latestRefill) {
                    // Log peristiwa sebelum mengubah status
                    Log::info('Otomatis memperbarui status APAR.', [
                        'penggunaan_id' => $item->id,
                        'apar_kode' => optional($item->masterApar)->kode,
                        'status_lama' => 'NOT GOOD',
                        'status_baru' => 'GOOD',
                        'tanggal_refill' => $latestRefill->tanggal_pembelian,
                        'tanggal_penggunaan' => $item->tanggal_penggunaan,
                    ]);

                    $item->status = 'GOOD';
                    $item->save();
                }
            }
        }

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

        // Buat nama file yang dinamis
        // Jika tanggal tidak ada, gunakan placeholder 'semua-tanggal'
        $formattedStartDate = $startDate ? Carbon::parse($startDate)->format('Y-m-d') : 'semua-tanggal';
        $formattedEndDate = $endDate ? Carbon::parse($endDate)->format('Y-m-d') : 'semua-tanggal';
        
        $fileName = "riwayat-penggunaan-apar-{$formattedStartDate}-{$formattedEndDate}.xlsx";

        // Query untuk mengambil semua data riwayat pengisian
        $query = Penggunaan::with(['user', 'masterApar', 'gedung']);
        
        // Jika ada rentang tanggal, tambahkan filter
        if ($startDate && $endDate) {
            $query->whereBetween('tanggal_penggunaan', [$startDate, $endDate]);
        }

        $refillHistory = $query->get();
        
        // Unduh file dengan nama yang sudah dinamis
        return Excel::download(new PengisianAparExport($refillHistory), $fileName);
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
            // Temukan entri penggunaan yang ingin diubah
            $penggunaan = Penggunaan::findOrFail($id);

            // Dapatkan APAR yang terkait
            $apar = $penggunaan->masterApar;

            // Pastikan APAR ditemukan dan status penggunaan terakhirnya adalah 'NOT GOOD'
            if (!$apar || $penggunaan->status !== 'NOT GOOD') {
                return response()->json(['success' => false, 'message' => 'APAR tidak ditemukan atau statusnya sudah GOOD.']);
            }

            // Cari transaksi refill (kebutuhan_id = 2) yang lebih baru dari tanggal penggunaan
            $latestRefill = $apar->transaksis()
                                 ->where('kebutuhan_id', 2)
                                 ->where('tanggal_pembelian', '>', $penggunaan->tanggal_penggunaan)
                                 ->latest('tanggal_pembelian')
                                 ->first();

            if ($latestRefill) {
                // Perbarui status entri penggunaan menjadi 'GOOD'
                $penggunaan->status = 'GOOD';
                $penggunaan->save();

                // Perbarui status APAR di tabel master_apars
                $apar->status_penggunaan = 'BELUM DIPAKAI';
                $apar->save();

                return response()->json(['success' => true, 'message' => 'Status APAR berhasil diperbarui menjadi GOOD.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Refill APAR belum tercatat atau tidak lebih baru dari tanggal penggunaan.']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui status: ' . $e->getMessage()]);
        }
    }
}
