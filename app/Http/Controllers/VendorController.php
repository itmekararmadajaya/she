<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Kebutuhan;
use App\Models\HargaKebutuhan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\JenisPemadam;
use App\Models\JenisIsi;
use App\Models\ItemCheck;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vendors = Vendor::orderBy('id', 'asc')->paginate(10);
        return view('pages.vendor.index', compact('vendors'));
    }

    public function apiIndex()
    {
        $vendors = Vendor::with([
            'hargaKebutuhans.kebutuhan',
            'hargaKebutuhans.jenisPemadam',
            'hargaKebutuhans.jenisIsi',
            'hargaKebutuhans.itemCheck'
        ])->get();

        return response()->json([
            'status' => 'success',
            'data' => $vendors
        ]);
    }

    public function apiShow($id)
    {
        $vendor = Vendor::with([
            'hargaKebutuhans.kebutuhan',
            'hargaKebutuhans.jenisPemadam',
            'hargaKebutuhans.jenisIsi',
            'hargaKebutuhans.itemCheck'
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $vendor
        ]);
    }

    public function show()
    {
    }

    public function getHargaKebutuhan()
    {
        $vendors = Vendor::with([
            'hargaKebutuhans.kebutuhan',
            'hargaKebutuhans.jenisPemadam',
            'hargaKebutuhans.jenisIsi',
            'hargaKebutuhans.itemCheck'
        ])->get();

        return response()->json([
            'status' => 'success',
            'data' => $vendors
        ]);
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kebutuhans = Kebutuhan::all();
        $jenisPemadams = JenisPemadam::all();
        $jenisIsis = JenisIsi::all();
        $itemChecks = ItemCheck::all();

        return view('pages.vendor.create', compact('kebutuhans', 'jenisPemadams', 'jenisIsis', 'itemChecks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // DEBUGGING: Tambahkan baris ini untuk melihat semua data yang masuk
        // Silakan salin outputnya dan kirimkan kepada saya.
        // Setelah selesai debugging, hapus baris ini.

        $request->validate([
            'nama_vendor' => 'required|string|max:255',
            'kontak' => 'nullable|string|max:255',
            'kebutuhan' => 'required|array',
            'kebutuhan.*.kebutuhan_id' => 'required|exists:kebutuhans,id',
            'kebutuhan.*.jenis_pemadam_id' => 'nullable|exists:jenis_pemadams,id',
            'kebutuhan.*.jenis_isi' => 'nullable|array',
            'kebutuhan.*.jenis_isi.*.jenis_isi_id' => 'required_with:kebutuhan.*.jenis_pemadam_id|exists:jenis_isis,id',
            'kebutuhan.*.jenis_isi.*.biaya' => 'required_with:kebutuhan.*.jenis_pemadam_id|numeric|min:0',
            'kebutuhan.*.jenis_isi.*.tanggal_perubahan' => 'required_with:kebutuhan.*.jenis_pemadam_id|date',
            'kebutuhan.*.komponen' => 'nullable|array',
            'kebutuhan.*.komponen.*.item_check_id' => 'required|exists:item_checks,id',
            'kebutuhan.*.komponen.*.biaya' => 'required|numeric|min:0',
            'kebutuhan.*.komponen.*.tanggal_perubahan' => 'required|date',
            'kebutuhan.*.biaya' => 'nullable|numeric|min:0',
            'kebutuhan.*.tanggal_perubahan' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $vendor = Vendor::create([
                'nama_vendor' => $request->nama_vendor,
                'kontak' => $request->kontak,
            ]);

            $kebutuhanNames = Kebutuhan::pluck('kebutuhan', 'id')->toArray();

            foreach ($request->kebutuhan as $kebutuhanItem) {
                $kebutuhanName = $kebutuhanNames[$kebutuhanItem['kebutuhan_id']];

                if ($kebutuhanName == 'Beli Baru' || $kebutuhanName == 'Isi Ulang') {
                    if (isset($kebutuhanItem['jenis_isi'])) {
                        foreach ($kebutuhanItem['jenis_isi'] as $isiItem) {
                            HargaKebutuhan::create([
                                'vendor_id' => $vendor->id,
                                'kebutuhan_id' => $kebutuhanItem['kebutuhan_id'],
                                'jenis_pemadam_id' => $kebutuhanItem['jenis_pemadam_id'],
                                'jenis_isi_id' => $isiItem['jenis_isi_id'],
                                'biaya' => $isiItem['biaya'],
                                'tanggal_perubahan' => $isiItem['tanggal_perubahan'],
                                'item_check_id' => null,
                            ]);
                        }
                    }
                } elseif ($kebutuhanName == 'Ganti Komponen') {
                    if (isset($kebutuhanItem['komponen'])) {
                        foreach ($kebutuhanItem['komponen'] as $komponenItem) {
                            HargaKebutuhan::create([
                                'vendor_id' => $vendor->id,
                                'kebutuhan_id' => $kebutuhanItem['kebutuhan_id'],
                                'jenis_pemadam_id' => null,
                                'jenis_isi_id' => null,
                                'item_check_id' => $komponenItem['item_check_id'],
                                'biaya' => $komponenItem['biaya'],
                                'tanggal_perubahan' => $komponenItem['tanggal_perubahan'],
                            ]);
                        }
                    }
                } else {
                    try {
                        $saved = HargaKebutuhan::create([
                            'vendor_id' => $vendor->id,
                            'kebutuhan_id' => $kebutuhanItem['kebutuhan_id'],
                            'jenis_pemadam_id' => null,
                            'jenis_isi_id' => null,
                            'item_check_id' => null,
                            'biaya' => $kebutuhanItem['biaya'] ?? 0,
                            'tanggal_perubahan' => $kebutuhanItem['tanggal_perubahan'] ?? Carbon::now(),
                        ]);

                    } catch (\Exception $e) {
                        dd($e->getMessage()); // 👈 tampilkan error DB kalau gagal
                    }
                }
            }

            DB::commit();

            return redirect()->route('vendor.index')->with('success', 'Vendor dan harga kebutuhan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vendor $vendor)
    {
        $kebutuhans = Kebutuhan::all();
        $jenisPemadams = JenisPemadam::all();
        $jenisIsis = JenisIsi::all();
        $itemChecks = ItemCheck::all();

        $existingData = [];
        
        $vendor->hargaKebutuhans->each(function ($item) use (&$existingData) {
            $kebutuhan = $item->kebutuhan;
            if ($kebutuhan->kebutuhan == 'Beli Baru' || $kebutuhan->kebutuhan == 'Isi Ulang') {
                $foundBlock = false;
                foreach ($existingData as &$block) {
                    if ($block['kebutuhan_id'] == $item->kebutuhan_id && $block['jenis_pemadam_id'] == $item->jenis_pemadam_id) {
                        $block['jenis_isi'][] = [
                            'id' => $item->id,
                            'jenis_isi_id' => $item->jenis_isi_id,
                            'biaya' => $item->biaya,
                            'tanggal_perubahan' => $item->tanggal_perubahan,
                        ];
                        $foundBlock = true;
                        break;
                    }
                }
                if (!$foundBlock) {
                    $existingData[] = [
                        'kebutuhan_id' => $item->kebutuhan_id,
                        'jenis_pemadam_id' => $item->jenis_pemadam_id,
                        'jenis_isi' => [[
                            'id' => $item->id,
                            'jenis_isi_id' => $item->jenis_isi_id,
                            'biaya' => $item->biaya,
                            'tanggal_perubahan' => $item->tanggal_perubahan,
                        ]]
                    ];
                }
            } elseif ($kebutuhan->kebutuhan == 'Ganti Komponen') {
                $foundBlock = false;
                foreach ($existingData as &$block) {
                    if ($block['kebutuhan_id'] == $item->kebutuhan_id) {
                        $block['komponen'][] = [
                            'id' => $item->id,
                            'item_check_id' => $item->item_check_id,
                            'biaya' => $item->biaya,
                            'tanggal_perubahan' => $item->tanggal_perubahan,
                        ];
                        $foundBlock = true;
                        break;
                    }
                }
                if (!$foundBlock) {
                    $existingData[] = [
                        'kebutuhan_id' => $item->kebutuhan_id,
                        'komponen' => [[
                            'id' => $item->id,
                            'item_check_id' => $item->item_check_id,
                            'biaya' => $item->biaya,
                            'tanggal_perubahan' => $item->tanggal_perubahan,
                        ]]
                    ];
                }
            } else {
                 $existingData[] = [
                     'id' => $item->id,
                     'kebutuhan_id' => $item->kebutuhan_id,
                     'biaya' => $item->biaya,
                     'tanggal_perubahan' => $item->tanggal_perubahan,
                 ];
            }
        });
        
        return view('pages.vendor.edit', compact(
            'vendor', 'kebutuhans', 'jenisPemadams', 'jenisIsis', 'itemChecks', 'existingData'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vendor $vendor)
    {
        // DEBUGGING: Tambahkan baris ini untuk melihat semua data yang masuk
        // Silakan salin outputnya dan kirimkan kepada saya.
        // Setelah selesai debugging, hapus baris ini.

        $request->validate([
            'nama_vendor' => 'required|string|max:255',
            'kontak' => 'nullable|string|max:255',
            'kebutuhan' => 'nullable|array',
            'kebutuhan.*.kebutuhan_id' => 'required|exists:kebutuhans,id',
            'kebutuhan.*.jenis_pemadam_id' => 'nullable|exists:jenis_pemadams,id',
            'kebutuhan.*.jenis_isi' => 'nullable|array',
            'kebutuhan.*.jenis_isi.*.id' => 'nullable|integer|exists:harga_kebutuhans,id',
            'kebutuhan.*.jenis_isi.*.jenis_isi_id' => 'required_with:kebutuhan.*.jenis_pemadam_id|exists:jenis_isis,id',
            'kebutuhan.*.jenis_isi.*.biaya' => 'required_with:kebutuhan.*.jenis_pemadam_id|numeric|min:0',
            'kebutuhan.*.jenis_isi.*.tanggal_perubahan' => 'required_with:kebutuhan.*.jenis_pemadam_id|date',
            'kebutuhan.*.komponen' => 'nullable|array',
            'kebutuhan.*.komponen.*.id' => 'nullable|integer|exists:harga_kebutuhans,id',
            'kebutuhan.*.komponen.*.item_check_id' => 'required|exists:item_checks,id',
            'kebutuhan.*.komponen.*.biaya' => 'required|numeric|min:0',
            'kebutuhan.*.komponen.*.tanggal_perubahan' => 'required|date',
            'kebutuhan.*.biaya' => 'nullable|numeric|min:0',
            'kebutuhan.*.tanggal_perubahan' => 'nullable|date',
        ]);
        
        try {
            DB::beginTransaction();

            $vendor->update([
                'nama_vendor' => $request->nama_vendor,
                'kontak' => $request->kontak,
            ]);

            $submittedIds = [];
            $kebutuhanNames = Kebutuhan::pluck('kebutuhan', 'id')->toArray();
            
            if ($request->has('kebutuhan')) {
                foreach ($request->kebutuhan as $kebutuhanItem) {
                    $kebutuhanName = $kebutuhanNames[$kebutuhanItem['kebutuhan_id']];
    
                    if ($kebutuhanName == 'Beli Baru' || $kebutuhanName == 'Isi Ulang') {
                        if (isset($kebutuhanItem['jenis_isi'])) {
                            foreach ($kebutuhanItem['jenis_isi'] as $isiItem) {
                                $dataToStore = [
                                    'vendor_id' => $vendor->id,
                                    'kebutuhan_id' => $kebutuhanItem['kebutuhan_id'],
                                    'jenis_pemadam_id' => $kebutuhanItem['jenis_pemadam_id'],
                                    'jenis_isi_id' => $isiItem['jenis_isi_id'],
                                    'biaya' => $isiItem['biaya'],
                                    'tanggal_perubahan' => $isiItem['tanggal_perubahan'],
                                    'item_check_id' => null,
                                ];
                                if (isset($isiItem['id'])) {
                                    HargaKebutuhan::where('id', $isiItem['id'])->update($dataToStore);
                                    $submittedIds[] = $isiItem['id'];
                                } else {
                                    $hargaKebutuhan = HargaKebutuhan::create($dataToStore);
                                    $submittedIds[] = $hargaKebutuhan->id;
                                }
                            }
                        }
                    } elseif ($kebutuhanName == 'Ganti Komponen') {
                        if (isset($kebutuhanItem['komponen'])) {
                            foreach ($kebutuhanItem['komponen'] as $komponenItem) {
                                $dataToStore = [
                                    'vendor_id' => $vendor->id,
                                    'kebutuhan_id' => $kebutuhanItem['kebutuhan_id'],
                                    'jenis_pemadam_id' => null,
                                    'jenis_isi_id' => null,
                                    'item_check_id' => $komponenItem['item_check_id'],
                                    'biaya' => $komponenItem['biaya'],
                                    'tanggal_perubahan' => $komponenItem['tanggal_perubahan'],
                                ];
                                if (isset($komponenItem['id'])) {
                                    HargaKebutuhan::where('id', $komponenItem['id'])->update($dataToStore);
                                    $submittedIds[] = $komponenItem['id'];
                                } else {
                                    $hargaKebutuhan = HargaKebutuhan::create($dataToStore);
                                    $submittedIds[] = $hargaKebutuhan->id;
                                }
                            }
                        }
                    } else {
                        if (isset($kebutuhanItem['id'])) {
                            HargaKebutuhan::where('id', $kebutuhanItem['id'])->update([
                                'vendor_id' => $vendor->id,
                                'kebutuhan_id' => $kebutuhanItem['kebutuhan_id'],
                                'jenis_pemadam_id' => null,
                                'jenis_isi_id' => null,
                                'item_check_id' => null,
                                'biaya' => $kebutuhanItem['biaya'] ?? 0,
                                'tanggal_perubahan' => $kebutuhanItem['tanggal_perubahan'] ?? Carbon::now(),
                            ]);
                            $submittedIds[] = $kebutuhanItem['id'];
                        } else {
                            $hargaKebutuhan = HargaKebutuhan::create([
                                'vendor_id' => $vendor->id,
                                'kebutuhan_id' => $kebutuhanItem['kebutuhan_id'],
                                'jenis_pemadam_id' => null,
                                'jenis_isi_id' => null,
                                'item_check_id' => null,
                                'biaya' => $kebutuhanItem['biaya'] ?? 0,
                                'tanggal_perubahan' => $kebutuhanItem['tanggal_perubahan'] ?? Carbon::now(),
                            ]);
                            $submittedIds[] = $hargaKebutuhan->id;
                        }
                    }
                }
            }
            
            HargaKebutuhan::where('vendor_id', $vendor->id)
                ->whereNotIn('id', $submittedIds)
                ->delete();

            DB::commit();

            return redirect()->route('vendor.index')->with('success', 'Vendor dan harga kebutuhan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return redirect()->route('vendor.index')->with('success', 'Vendor berhasil dihapus.');
    }
}
