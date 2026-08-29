<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIM-TESIS FKIP UNS</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f1f5f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 16px; }
        .login-card { background: white; width: 100%; max-width: 400px; padding: 32px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.08); }
        .login-card h2 { margin: 0 0 6px 0; color: #1e3a8a; font-size: 22px; font-weight: 700; text-align: center; }
        .login-card p { margin: 0 0 24px 0; color: #64748b; font-size: 13.5px; text-align: center; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #334155; }
        .form-control { width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; }
        .form-control:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .btn-submit { width: 100%; padding: 11px; background: #2563eb; color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .btn-submit:hover { background: #1d4ed8; }
        .alert { padding: 10px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 16px; }
        .alert-danger { background: #fee2e2; color: #991b1b; }
        .alert-info { background: #e0f2fe; color: #075985; }
        .footer-link { text-align: center; margin-top: 20px; font-size: 13.5px; color: #64748b; }
        .footer-link a { color: #2563eb; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>SIM-TESIS FKIP UNS</h2>
        <p>Masuk ke Portal Akademik Tesis</p>

        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        @if(session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>NIM (Mahasiswa) / NIP (Dosen/Admin):</label>
                <input type="text" name="identifier" class="form-control" value="{{ old('identifier') }}" required placeholder="Contoh: S032608001 atau NIP" autofocus>
            </div>
            <div class="form-group">
                <label>Kata Sandi (Password):</label>
                <input type="password" name="password" class="form-control" required placeholder="Masukkan kata sandi...">
            </div>
            <button type="submit" class="btn-submit">Masuk ke Sistem</button>
        </form>

        <div class="footer-link">
            Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a><br>
            <a href="{{ route('public.index') }}" style="display:inline-block; margin-top:10px; color:#64748b;">← Kembali ke Halaman Publik</a>
        </div>
    </div>
</body>
</html>
