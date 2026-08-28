<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        $remember = $request->filled('remember');

        // Coba login via NIM/NIP/Username
        if (Auth::attempt(['identifier' => $credentials['identifier'], 'password' => $credentials['password']], $remember)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        // Coba login via Email
        if (Auth::attempt(['email' => $credentials['identifier'], 'password' => $credentials['password']], $remember)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'identifier' => 'NIM/NIP atau kata sandi yang Anda masukkan tidak sesuai.',
        ])->onlyInput('identifier');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'identifier' => 'required|string|max:50|unique:users,identifier',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:mahasiswa,dosen',
            'bidang_keahlian' => 'nullable|string',
        ]);

        $user = User::create([
            'id' => (string) Str::uuid(),
            'name' => $validated['name'],
            'identifier' => $validated['identifier'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'bidang_keahlian' => $validated['role'] === 'dosen' ? ($validated['bidang_keahlian'] ?? 'studi') : null,
            'kuota_bimbingan_maks' => $validated['role'] === 'dosen' ? 8 : 0,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('public.index');
    }
}
