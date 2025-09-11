<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
     public function loginForm()
    {
        return view('pages.login');
    }

    // Fungsi untuk memproses login website (MENGGUNAKAN REDIRECT)
    public function webLogin(Request $request)
    {
        $credentials = $request->validate([
            'nik' => ['required', 'digits_between:1,10'],
            'password' => ['required'],
        ]);

        $user = User::where('nik', $request->nik)->first();

        if ($user && \Hash::check($request->password, $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();

            if ($user->hasRole('admin')) {
                return redirect()->route('dashboard');
            } else if ($user->hasRole('user')) {
                return redirect()->route('login');
            }
        }

        return back()->withErrors([
            'nik' => 'NIK atau password salah.',
        ])->onlyInput('nik');
    }
 
    // Fungsi untuk memproses login API (untuk Flutter)
    public function apiLogin(Request $request)
    {
        $request->validate([
            'nik' => 'required|digits_between:1,10',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('nik', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Generate token API
            $token = $user->createToken('flutter-app')->plainTextToken;

            return response()->json([
                'message' => 'Login berhasil',
                'user' => $user,
                'token' => $token
            ], 200);
        }

        return response()->json([
            'message' => 'NIK atau password salah.',
        ], 401);
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        return redirect()->route('login');
    }
}