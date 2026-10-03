<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectAfterLogin();
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

        // Coba login via NIM/NIP/Username, lalu via Email.
        if (Auth::attempt(['identifier' => $credentials['identifier'], 'password' => $credentials['password']], $remember)
            || Auth::attempt(['email' => $credentials['identifier'], 'password' => $credentials['password']],$remember)) {
            $request->session()->regenerate();
            return $this->redirectAfterLogin();
        }

        return back()->withErrors([
            'identifier' => 'NIM/NIP atau kata sandi yang Anda masukkan tidak sesuai.',
        ])->onlyInput('identifier');
    }

    /**
     * Komisi Tesis langsung diarahkan ke menu "Dosen" (sidebar manajemen
     * akun & jadwal sidang) setelah login, karena itu tugas harian mereka.
     * Dashboard lengkap (Ringkasan + tab FR-01..FR-10) tetap bisa dibuka
     * lewat link "Dashboard Lengkap" di sidebar, jadi tidak hilang akses.
     *
     * Route::has() dicek dulu supaya login tidak 500 kalau halaman
     * "komisi.dosen" belum/sedang dibangun — begitu route-nya sudah
     * didaftarkan di routes/web.php, redirect ini otomatis aktif tanpa
     * perlu ubah file ini lagi.
     */
    protected function redirectAfterLogin()
    {
        if (Auth::user()->hasRole('komisi_tesis') && Route::has('komisi.dosen')) {
            return redirect()->route('komisi.dosen');
        }

        return redirect()->route('dashboard');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectAfterLogin();
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'identifier' => 'required|string|max:50|unique:users,identifier',
            'email' => 'required|string|email|max:255|unique:users,email',
            'nomor_wa' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:mahasiswa,dosen',
            'bidang_keahlian' => 'nullable|string',
        ]);

        $user = User::create([
            'id' => (string) Str::uuid(),
            'name' => $validated['name'],
            'identifier' => $validated['identifier'],
            'email' => $validated['email'],
            'nomor_wa' => $validated['nomor_wa'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'bidang_keahlian' => $validated['role'] === 'dosen' ? ($validated['bidang_keahlian'] ?? 'studi') : null,
            'kuota_bimbingan_maks' => $validated['role'] === 'dosen' ? 8 : 0,
        ]);

        $user->assignRole($validated['role']);

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

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('info', 'Tautan reset password sudah dikirim ke email Anda.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('info', 'Password berhasil direset. Silakan masuk dengan password baru Anda.')
            : back()->withErrors(['email' => __($status)]);
    }
}
