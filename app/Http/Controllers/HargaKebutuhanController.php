<?php

namespace App\Http\Controllers;

use App\Models\HargaKebutuhan;
use App\Models\Vendor;
use App\Models\Kebutuhan;
use Illuminate\Http\Request;

class HargaKebutuhanController extends Controller
{
    public function index()
    {
        $hargaKebutuhans = HargaKebutuhan::with([
            'vendor', 
            'kebutuhan',
            'jenisIsi',
            'jenisPemadam',
            'itemCheck'
        ])
        ->latest()
        ->paginate(10);

        return view('pages.harga_kebutuhan.index', compact('hargaKebutuhans'));
    }

    public function create()
    {
        $vendors = Vendor::all();
        $kebutuhans = Kebutuhan::all();

        return view('pages.harga_kebutuhan.create', compact('vendors', 'kebutuhans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'kebutuhan_id' => 'required|exists:kebutuhans,id',
            'biaya' => 'required|numeric',
            'tanggal_perubahan' => 'required|date',
        ]);

        HargaKebutuhan::create($request->all());

        return redirect()->route('harga_kebutuhan.index')
                         ->with('success', 'Harga kebutuhan berhasil ditambahkan.');
    }

    public function show(HargaKebutuhan $hargaKebutuhan)
    {
        return view('pages.harga_kebutuhan.show', compact('hargaKebutuhan'));
    }

    public function edit(HargaKebutuhan $hargaKebutuhan)
    {
        $vendors = Vendor::all();
        $kebutuhans = Kebutuhan::all();

        return view('pages.harga_kebutuhan.edit', compact('hargaKebutuhan', 'vendors', 'kebutuhans'));
    }

    public function update(Request $request, HargaKebutuhan $hargaKebutuhan)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'kebutuhan_id' => 'required|exists:kebutuhans,id',
            'biaya' => 'required|numeric',
            'tanggal_perubahan' => 'required|date',
        ]);

        $hargaKebutuhan->update($request->all());

        return redirect()->route('harga_kebutuhan.index')
                         ->with('success', 'Harga kebutuhan berhasil diperbarui.');
    }

    public function destroy(HargaKebutuhan $hargaKebutuhan)
    {
        $hargaKebutuhan->delete();

        return redirect()->route('harga_kebutuhan.index')
                         ->with('success', 'Harga kebutuhan berhasil dihapus.');
    }

    /**
     * ✅ API Endpoint: Ambil kebutuhan berdasarkan vendor
     */
    public function getKebutuhanByVendor($vendorId)
    {
        $hargaKebutuhans = HargaKebutuhan::with([
            'kebutuhan',
            'jenisIsi',
            'jenisPemadam',
            'itemCheck'
        ])
        ->where('vendor_id', $vendorId)
        ->get();

        return response()->json($hargaKebutuhans);
    }

}
