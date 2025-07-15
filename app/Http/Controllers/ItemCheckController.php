<?php

namespace App\Http\Controllers;

use App\Models\ItemCheck;
use Illuminate\Http\Request;

class ItemCheckController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ItemCheck::query();

        if($request->filled('search')){
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $items = $query->paginate(10);
        
        return view('pages.item_check.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.item_check.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:item_checks,name',
        ]);

        ItemCheck::create([
            'name' => $request->name,
        ]);

        return redirect()->route('item-check.index')->with('success', 'Item Check berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(ItemCheck $itemCheck)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ItemCheck $itemCheck)
    {
        return view('pages.item_check.edit', compact('itemCheck'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ItemCheck $itemCheck)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:item_checks,name,'. $itemCheck->id,
        ]);

        $itemCheck->update([
            'name' => $request->name,
        ]);

        return redirect()->route('item-check.index')->with('success', 'Item Check berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItemCheck $itemCheck)
    {
        $itemCheck->update(['is_active' => false]); // Soft-delete
        return redirect()->route('item-check.index')->with('success', 'Item Check berhasil dinonaktifkan');
    }

    public function restore($id)
    {
        $item = ItemCheck::findOrFail($id);
        $item->update(['is_active' => true]);

        return redirect()->route('item-check.index')->with('success', 'Item Check diaktifkan kembali');
    }
}
