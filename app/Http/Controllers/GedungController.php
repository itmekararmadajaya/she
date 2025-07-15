<?php

namespace App\Http\Controllers;

use App\Models\Gedung;
use Illuminate\Http\Request;

class GedungController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Gedung::query();

        if($request->filled('search')){
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        $gedungs = $query->latest()->paginate(10);
        return view('pages.gedung.index', compact('gedungs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.gedung.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        Gedung::create([
            'nama' => $request->nama,
        ]);

        return redirect()->route('gedung.index')->with('success', 'Gedung berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Gedung $gedung)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Gedung $gedung)
    {
        return view('pages.gedung.edit', compact('gedung'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Gedung $gedung)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $gedung->update([
            'nama' => $request->nama,
        ]);

        return redirect()->route('gedung.index')->with('success', 'Gedung berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gedung $gedung)
    {
        $gedung->delete();
        return redirect()->route('gedung.index')->with('success', 'Gedung berhasil dihapus');
    }
}
