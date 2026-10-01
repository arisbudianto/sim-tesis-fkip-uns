<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
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
        return redirect()->route('login')->with('error', 'Pendaftaran akun mandiri ditutup. Hubungi Admin Prodi / Kaprodi untuk dibuatkan akun.');
    }

    public function register(Request $request)
    {
        return redirect()->route('login')->with('error', 'Pendaftaran akun mandiri ditutup. Hubungi Admin Prodi / Kaprodi.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('public.index');
    }

    /**
     * === Reset Password (Modul 3, Tugas 1) ===
     * Memakai Password broker bawaan Laravel (Illuminate\Support\Facades\Password)
     * — bukan Breeze secara harfiah, tapi mekanisme intinya sama persis
     * (token sekali pakai, expire 60 menit, dikirim ke email pengguna).
     */
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
