<?php
namespace App\Http\Controllers;
use App\Models\Kebutuhan;
use Illuminate\Http\Request;

class KebutuhanController extends Controller
{
    public function index() {
        $kebutuhans = Kebutuhan::orderBy('id', 'asc')->paginate(10);
        return view('pages.kebutuhan.index', compact('kebutuhans'));
    }

    public function apiIndex()
    {
        return response()->json(Kebutuhan::all());
    }

    public function create() {
        return view('pages.kebutuhan.create');
    }
    public function store(Request $request) {
        $request->validate(['kebutuhan' => 'required']);
        Kebutuhan::create($request->all());
        return redirect()->route('kebutuhan.index')->with('success', 'Kebutuhan berhasil ditambahkan.');
    }
    public function edit(Kebutuhan $kebutuhan) {
        return view('pages.kebutuhan.edit', compact('kebutuhan'));
    }
    public function update(Request $request, Kebutuhan $kebutuhan) {
        $request->validate(['kebutuhan' => 'required']);
        $kebutuhan->update($request->all());
        return redirect()->route('kebutuhan.index')->with('success', 'Kebutuhan berhasil diperbarui.');
    }
    public function destroy(Kebutuhan $kebutuhan) {
        $kebutuhan->delete();
        return redirect()->route('kebutuhan.index')->with('success', 'Kebutuhan berhasil dihapus.');
    }
}