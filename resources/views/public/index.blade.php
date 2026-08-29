<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIM-TESIS FKIP UNS - Portal Publik</title>
    <style>
        :root { --primary: #1e3a8a; --accent: #2563eb; --bg: #f8fafc; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: var(--bg); margin: 0; color: #1e293b; }
        .navbar { background: white; padding: 16px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.08); position: sticky; top: 0; z-index: 100; }
        .navbar-brand { font-size: 18px; font-weight: 700; color: var(--primary); text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .navbar-nav { display: flex; gap: 12px; align-items: center; }
        .nav-link { color: #475569; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 14px; border-radius: 6px; transition: 0.2s; }
        .nav-link:hover { color: var(--accent); background: #f1f5f9; }
        .btn-login { background: #f1f5f9; color: var(--primary); }
        .btn-register { background: var(--accent); color: white !important; }
        .btn-register:hover { background: #1d4ed8 !important; }
        
        .hero { background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); color: white; padding: 70px 20px; text-align: center; }
        .hero h1 { font-size: 34px; margin: 0 0 12px 0; font-weight: 800; }
        .hero p { font-size: 16px; opacity: 0.9; max-width: 650px; margin: 0 auto 28px auto; line-height: 1.6; }
        
        .container { max-width: 1100px; margin: -40px auto 40px auto; padding: 0 20px; }
        .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
        .card-stat { background: white; padding: 22px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06); text-align: center; border-bottom: 4px solid var(--accent); }
        .card-stat h3 { margin: 0 0 6px 0; font-size: 13px; color: #64748b; text-transform: uppercase; }
        .card-stat .val { font-size: 28px; font-weight: 700; color: #0f172a; }

        .features { background: white; border-radius: 12px; padding: 36px; margin-top: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.04); }
        .feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-top: 24px; }
        .feature-item { padding: 18px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .feature-item h4 { margin: 0 0 8px 0; color: var(--primary); font-size: 16px; }
        .feature-item p { margin: 0; color: #64748b; font-size: 13.5px; line-height: 1.5; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="{{ route('public.index') }}" class="navbar-brand">
            🏛️ SIM-TESIS FKIP UNS
        </a>
        <div class="navbar-nav">
            <a href="{{ route('public.index') }}" class="nav-link">Beranda</a>
            <a href="{{ route('public.panduan') }}" class="nav-link">Panduan SOP</a>
            @auth
                <a href="{{ route('dashboard') }}" class="nav-link btn-register">Dashboard Saya</a>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="nav-link btn-login" style="border:none; cursor:pointer;">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="nav-link btn-login">Masuk (Login)</a>
                <a href="{{ route('register') }}" class="nav-link btn-register">Daftar Akun</a>
            @endauth
        </div>
    </nav>

    <div class="hero">
        <h1>Manajemen Terpadu 4 Tahap Tesis Magister</h1>
        <p>Sistem digitalisasi siklus akademik pascasarjana: Alokasi Pembimbing, Seminar Proposal, Seminar Hasil, Ujian Tesis, hingga Kelulusan/Yudisium.</p>
        <div>
            @auth
                <a href="{{ route('dashboard') }}" class="nav-link btn-register" style="padding: 12px 24px; font-size: 15px;">Masuk ke Dashboard Sistem</a>
            @else
                <a href="{{ route('login') }}" class="nav-link btn-register" style="padding: 12px 24px; font-size: 15px;">Mulai Akses SIM-TESIS</a>
            @endauth
        </div>
    </div>

    <div class="container">
        <div class="grid-stats">
            <div class="card-stat">
                <h3>Tahap 1: Bimbingan</h3>
                <div class="val">{{ $stats['tahap_1_bimbingan'] }}</div>
            </div>
            <div class="card-stat">
                <h3>Tahap 2: Sempro</h3>
                <div class="val">{{ $stats['tahap_2_sempro'] }}</div>
            </div>
            <div class="card-stat">
                <h3>Tahap 3: Semhas</h3>
                <div class="val">{{ $stats['tahap_3_semhas'] }}</div>
            </div>
            <div class="card-stat">
                <h3>Tahap 4: Ujian Tesis</h3>
                <div class="val">{{ $stats['tahap_4_ujian'] }}</div>
            </div>
            <div class="card-stat" style="border-bottom-color: #16a34a;">
                <h3>Siap Yudisium</h3>
                <div class="val">{{ $stats['selesai_yudisium'] }}</div>
            </div>
        </div>

        <div class="features">
            <h2 style="margin: 0; font-size: 20px; color: #0f172a;">4 Pilar Tahapan Tesis FKIP UNS</h2>
            <div class="feature-grid">
                <div class="feature-item">
                    <h4>1. Penetapan Pembimbing</h4>
                    <p>Alokasi Pembimbing 1 (Bidang Studi) dan Pembimbing 2 (Kependidikan) dengan validasi kuota otomatis oleh Komisi Tesis.</p>
                </div>
                <div class="feature-item">
                    <h4>2. Seminar Proposal (Sempro)</h4>
                    <p>Pendaftaran minimal H-14, verifikasi berkas FPT-TI-01 s.d 09, dan pengesahan izin riset lapangan.</p>
                </div>
                <div class="feature-item">
                    <h4>3. Seminar Hasil (Semhas)</h4>
                    <p>Telaah komprehensif Bab I–V serta validasi luaran artikel ilmiah (min. 2 draf & 1 under review jurnal terakreditasi).</p>
                </div>
                <div class="feature-item">
                    <h4>4. Ujian Tesis & Yudisium</h4>
                    <p>Plotting 4 Dewan Penguji bebas bentrok jadwal, rubrik 4 dimensi evaluasi, BAP daring, dan matriks revisi kelulusan.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
