<?php

namespace App\Http\Controllers;

use App\Models\Gedung;
use App\Models\MasterApar;
use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;

class MasterAparController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $apars = MasterApar::with('gedung')
        ->when($request->kode, fn($q) => $q->where('kode', 'like', '%' . $request->kode . '%'))
        ->when($request->gedung_id, fn($q) => $q->where('gedung_id', $request->gedung_id))
        ->when($request->lokasi, fn($q) => $q->where('lokasi', 'like', '%' . $request->lokasi . '%'))
        ->where('is_active', true)
        ->latest()
        ->paginate(10)
        ->withQueryString();

        $gedungs = Gedung::all();
        return view('pages.master_apar.index', compact('apars', 'gedungs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $gedungs = Gedung::all();
        return view('pages.master_apar.create', compact('gedungs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:4|unique:master_apars,kode',
            'jenis_pemadam' => 'required|string',
            'jenis_isi' => 'required|string',
            'ukuran' => 'required|integer',
            'satuan' => 'required|string|max:2',
            'gedung_id' => 'required|exists:gedungs,id',
            'lokasi' => 'required|string',
            'tgl_kadaluarsa' => 'required|date',
            'tanda' => 'required|string',
            'catatan' => 'nullable|string',
            'tgl_refill' => 'required|date',
            'keterangan' => 'required|string',
        ]);

        MasterApar::create($request->all());

        return redirect()->route('master-apar.index')->with('success', 'Data APAR berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(MasterApar $masterApar)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MasterApar $masterApar)
    {
        $gedungs = Gedung::all();
        return view('pages.master_apar.edit', compact('masterApar', 'gedungs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MasterApar $masterApar)
    {
        $request->validate([
            'kode' => 'required|string|max:4|unique:master_apars,kode,'.$masterApar->id,
            'jenis_pemadam' => 'required|string',
            'jenis_isi' => 'required|string',
            'ukuran' => 'required|integer',
            'satuan' => 'required|string|max:2',
            'gedung_id' => 'required|exists:gedungs,id',
            'lokasi' => 'required|string',
            'tgl_kadaluarsa' => 'required|date',
            'tanda' => 'required|string',
            'catatan' => 'nullable|string',
            'tgl_refill' => 'required|date',
            'keterangan' => 'required|string',
        ]);

        $masterApar->update($request->all());

        return redirect()->route('master-apar.index')->with('success', 'Data APAR berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterApar $masterApar)
    {
        $masterApar->update(['is_active' => false]); // soft delete
        return redirect()->route('master-apar.index')->with('success', 'Data APAR berhasil dinonaktifkan');
    }

    public function restore($id)
    {
        $apar = MasterApar::findOrFail($id);
        $apar->update(['is_active' => true]);

        return redirect()->route('master-apar.index')->with('success', 'Data APAR diaktifkan kembali');
    }

    public function generateQr($id)
    {
        $apar = MasterApar::findOrFail($id);
        $writer = new PngWriter();
        // Create QR code
        $qrCode = new QrCode(
            data: $apar->kode,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 500,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        $result = $writer->write($qrCode);

        return Response::make(
            $result->getString(), 
            200, 
            [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'attachment; filename="qr-apar-'.$apar->kode.'.png"',
            ]
        );
    }
}
