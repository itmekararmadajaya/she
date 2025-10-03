<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
    
        $users = $query->latest()->paginate(10);

        return view('pages.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.users.create', [
            'roles' => Role::pluck('name')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    // app/Http/Controllers/UserController.php

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Definisikan Data yang Akan Divalidasi
        $data = $request->validate([
            'nik'       => 'required|digits_between:1,10|unique:users,nik',
            'name'      => 'required|string|max:255',
            // Email diubah menjadi 'nullable' (karena bisa kosong) dan tetap 'unique'
            'email'     => 'nullable|email|unique:users,email',
            'password'  => 'required|min:6',
            'role'      => 'required|string|exists:roles,name',
        ]);

        // 2. Logika Pembuatan Email Otomatis
        // Jika kolom email kosong (null), buat email dari NIK
        if (empty($data['email'])) {
            $generatedEmail = $request->nik . '@gmail.com';

            // Lakukan validasi tambahan untuk email yang dibuat secara otomatis
            $request->validate([
                'nik' => [
                    function ($attribute, $value, $fail) use ($generatedEmail) {
                        if (User::where('email', $generatedEmail)->exists()) {
                            $fail("Email otomatis ($generatedEmail) sudah digunakan.");
                        }
                    },
                ],
            ]);
            
            $data['email'] = $generatedEmail;
        }
        
        // 3. Simpan User
        $user = User::create([
            'nik'       => $data['nik'],
            'name'      => $data['name'],
            'email'     => $data['email'], // Gunakan email yang sudah diproses
            'password'  => Hash::make($data['password']),
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // return view('pages.users.show', [
        //     'user' => $user,
        //     'roles' => Role::pluck('name')
        // ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('pages.users.edit', [
            'user' => $user,
            'roles' => Role::pluck('name')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'nik'      => 'required|digits_between:1,10|unique:users,nik,' . $user->id,
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
            'role'  => 'required|string|exists:roles,name',
        ]);

        $user->nik  = $request->nik;
        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
