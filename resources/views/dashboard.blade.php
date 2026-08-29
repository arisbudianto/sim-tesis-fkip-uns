<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIM-TESIS FKIP UNS - Sistem Terpadu 4 Tahap Akademik</title>
    <style>
        :root { --primary: #1e3a8a; --accent: #2563eb; --bg: #f8fafc; --text: #0f172a; --card: #ffffff; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: var(--bg); margin: 0; padding: 20px; color: var(--text); }
        
        .topbar { background: white; padding: 12px 20px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .topbar-left { display: flex; gap: 14px; align-items: center; }
        .topbar-link { color: #475569; text-decoration: none; font-size: 13.5px; font-weight: 600; padding: 6px 12px; border-radius: 6px; }
        .topbar-link:hover { background: #f1f5f9; color: var(--accent); }
        .topbar-right { display: flex; gap: 10px; align-items: center; }
        .user-pill { font-size: 13px; font-weight: 600; color: #1e293b; background: #f1f5f9; padding: 6px 12px; border-radius: 9999px; }
        
        .header { background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); color: white; padding: 24px 30px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .header h1 { margin: 0 0 6px 0; font-size: 24px; font-weight: 700; letter-spacing: -0.5px; }
        .header p { margin: 0; font-size: 14px; opacity: 0.9; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .card { background: var(--card); padding: 20px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-left: 4px solid var(--accent); }
        .card h3 { margin: 0 0 8px 0; font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 600; }
        .card .count { font-size: 26px; font-weight: 700; color: #1e293b; }
        
        .nav-tabs { display: flex; gap: 8px; border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; overflow-x: auto; padding-bottom: 2px; }
        .nav-tab { padding: 10px 18px; border-radius: 8px 8px 0 0; background: transparent; border: none; font-size: 14px; font-weight: 600; color: #64748b; cursor: pointer; transition: all 0.2s; white-space: nowrap; }
        .nav-tab.active { background: #ffffff; color: var(--accent); border-bottom: 3px solid var(--accent); box-shadow: 0 -2px 4px rgba(0,0,0,0.02); }
        .nav-tab:hover:not(.active) { color: #1e293b; background: #e2e8f0; }

        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .box { background: var(--card); padding: 24px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; }
        .box-title { font-size: 18px; font-weight: 700; margin-top: 0; margin-bottom: 16px; color: #1e293b; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13.5px; }
        th, td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        tr:hover { background: #f8fafc; }
        
        .badge { display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 11.5px; font-weight: 600; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-purple { background: #f3e8ff; color: #6b21a8; }
        .badge-indigo { background: #e0e7ff; color: #3730a3; }
        .badge-green { background: #dcfce7; color: #166534; }
        
        .btn { padding: 8px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
        .btn-primary { background: var(--accent); color: white; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-success { background: #16a34a; color: white; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }

        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #334155; }
        .form-control { width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; }
        .form-control:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    </style>
</head>
<body>

    <!-- Bar Navigasi Publik & Profil -->
    <div class="topbar">
        <div class="topbar-left">
            <a href="{{ route('public.index') }}" class="topbar-link">🏠 Halaman Publik</a>
            <a href="{{ route('public.panduan') }}" class="topbar-link">📖 Panduan SOP 4-Tahap</a>
        </div>
        <div class="topbar-right">
            @auth
                <span class="user-pill">👤 {{ Auth::user()->name }} ({{ strtoupper(Auth::user()->role) }})</span>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger">Keluar (Logout)</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-sm btn-primary">Masuk (Login)</a>
                <a href="{{ route('register') }}" class="btn btn-sm btn-success">Daftar Akun</a>
            @endauth
        </div>
    </div>

    <div class="header">
        <h1>SIM-TESIS MAGISTER FKIP UNS</h1>
        <p>Digitalisasi Terpadu 4 Tahap Akademik: Penetapan Pembimbing, Sempro, Semhas, Ujian Tesis & Yudisium</p>
    </div>

    <div class="grid">
        <div class="card">
            <h3>Tahap 1: Bimbingan</h3>
            <div class="count">{{ $stats['tahap_1_bimbingan'] ?? 0 }}</div>
        </div>
        <div class="card">
            <h3>Tahap 2: Sempro</h3>
            <div class="count">{{ $stats['tahap_2_sempro'] ?? 0 }}</div>
        </div>
        <div class="card">
            <h3>Tahap 3: Semhas</h3>
            <div class="count">{{ $stats['tahap_3_semhas'] ?? 0 }}</div>
        </div>
        <div class="card">
            <h3>Tahap 4: Ujian Tesis</h3>
            <div class="count">{{ $stats['tahap_4_ujian'] ?? 0 }}</div>
        </div>
        <div class="card" style="border-left-color: #10b981;">
            <h3>Lulus / Yudisium</h3>
            <div class="count">{{ $stats['selesai_yudisium'] ?? 0 }}</div>
        </div>
    </div>

    <!-- Menu Navigasi Tab Modul -->
    <div class="nav-tabs">
        <button class="nav-tab active" onclick="switchTab('tab-overview')">Ringkasan Sistem</button>
        <button class="nav-tab" onclick="switchTab('tab-pengajuan')">FR-01: Usulan & Alokasi Pembimbing</button>
        {{-- Tab FR-02: Logbook Bimbingan — sementara dihapus/disembunyikan --}}
        <button class="nav-tab" onclick="switchTab('tab-pendaftaran')">FR-03,05,07: Pendaftaran Sidang (H-14)</button>
        <button class="nav-tab" onclick="switchTab('tab-sidang')">FR-04,06,08: Plotting Komisi Tesis</button>
        <button class="nav-tab" onclick="switchTab('tab-penilaian')">FR-09: Penilaian & BAP</button>
        <button class="nav-tab" onclick="switchTab('tab-revisi')">FR-10: Matriks Revisi & Yudisium</button>
    </div>

    <!-- TAB 1: Ringkasan & Matriks FR -->
    <div id="tab-overview" class="tab-content active">
        <div class="box">
            <div class="box-title">Daftar Pengajuan Tesis Berjalan</div>
            <table>
                <thead>
                    <tr>
                        <th>NIM / Mahasiswa</th>
                        <th>Judul Tesis</th>
                        <th>Pembimbing 1 & 2</th>
                        <th>Status Tahap</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                    <tr>
                        <td><strong>{{ $p->mahasiswa->identifier ?? '-' }}</strong><br>{{ $p->mahasiswa->name ?? '-' }}</td>
                        <td>{{ $p->judul_tesis }}<br><small style="color:#64748b;">Fokus: {{ $p->bidang_fokus }}</small></td>
                        <td>
                            1. {{ $p->pembimbing1->name ?? 'Belum Dialokasikan' }}<br>
                            2. {{ $p->pembimbing2->name ?? 'Belum Dialokasikan' }}
                        </td>
                        <td>
                            @if($p->status_tahap === 'tahap_1_bimbingan') <span class="badge badge-blue">Tahap 1: Bimbingan</span>
                            @elseif($p->status_tahap === 'tahap_2_sempro') <span class="badge badge-yellow">Tahap 2: Sempro</span>
                            @elseif($p->status_tahap === 'tahap_3_semhas') <span class="badge badge-purple">Tahap 3: Semhas</span>
                            @elseif($p->status_tahap === 'tahap_4_ujian') <span class="badge badge-indigo">Tahap 4: Ujian Tesis</span>
                            @else <span class="badge badge-green">Selesai / Siap Yudisium</span>
                            @endif
                        </td>
                        <td>
                            @if(Auth::check() && in_array(Auth::user()->role, ['komisi_tesis', 'kaprodi', 'admin_prodi']))
                                @if($p->status_tahap === 'tahap_1_bimbingan')
                                    <a href="{{ route('pengajuan.edit', $p->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('pengajuan.destroy', $p->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus pengajuan tesis {{ $p->mahasiswa->name ?? '' }}? Tindakan ini tidak bisa dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                @else
                                    <span style="color:#94a3b8; font-size:12px;">Terkunci (&gt; Tahap 1)</span>
                                @endif
                            @else
                                <span style="color:#94a3b8; font-size:12px;">&mdash;</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align: center; color: #64748b;">Belum ada data pengajuan tesis. Silakan isi form pada tab FR-01.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: FR-01 Form Pengajuan & Alokasi Pembimbing -->
    <div id="tab-pengajuan" class="tab-content">
        <div class="form-grid">
            <div class="box">
                <div class="box-title">Mahasiswa: Ajukan Judul Tesis (FR-01)</div>
                <form action="{{ route('pengajuan.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Pilih Mahasiswa:</label>
                        <select name="mahasiswa_id" class="form-control" required>
                            @foreach($mahasiswas as $m)
                                <option value="{{ $m->id }}">{{ $m->identifier }} - {{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Judul Tesis:</label>
                        <textarea name="judul_tesis" class="form-control" rows="3" required placeholder="Masukkan usulan judul penelitian tesis..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Bidang Fokus Keahlian:</label>
                        <input type="text" name="bidang_fokus" class="form-control" required placeholder="Contoh: Media Pembelajaran Vokasi">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Usulan Judul</button>
                </form>
            </div>

            @if(Auth::check() && in_array(Auth::user()->role, ['komisi_tesis', 'kaprodi', 'admin_prodi']))
            <div class="box">
                <div class="box-title">Komisi Tesis: Alokasi Pembimbing 1 & 2 (FR-01)</div>

                <table style="margin-bottom: 24px;">
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Judul Tesis</th>
                            <th>Pembimbing 1</th>
                            <th>Pembimbing 2</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajuans as $p)
                        <tr>
                            <td><strong>{{ $p->mahasiswa->identifier ?? '-' }}</strong><br>{{ $p->mahasiswa->name ?? '-' }}</td>
                            <td>{{ Str::limit($p->judul_tesis, 50) }}</td>
                            <td>
                                @if($p->pembimbing1)
                                    <span class="badge badge-green">{{ $p->pembimbing1->name }}</span>
                                @else
                                    <span class="badge badge-yellow">Belum Dialokasikan</span>
                                @endif
                            </td>
                            <td>
                                @if($p->pembimbing2)
                                    <span class="badge badge-green">{{ $p->pembimbing2->name }}</span>
                                @else
                                    <span class="badge badge-yellow">Belum Dialokasikan</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary" onclick="selectPengajuanForAlokasi('{{ $p->id }}')">
                                    {{ ($p->pembimbing1 && $p->pembimbing2) ? 'Ubah' : 'Alokasikan' }}
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align: center; color: #64748b;">Belum ada pengajuan tesis yang bisa dialokasikan.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <form id="form-alokasi" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Pilih Judul Mahasiswa:</label>
                        <select id="select-pengajuan" class="form-control" onchange="updateAlokasiAction(this.value)">
                            <option value="">-- Pilih Pengajuan Tesis (atau klik 'Alokasikan' di tabel atas) --</option>
                            @foreach($pengajuans as $p)
                                <option value="{{ $p->id }}">{{ $p->mahasiswa->name }} - {{ Str::limit($p->judul_tesis, 40) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Pembimbing 1 (Spesialis Studi):</label>
                        <select name="pembimbing_1_id" class="form-control" required>
                            @foreach($dosens as $d)
                                <option value="{{ $d->id }}">{{ $d->name }} (Kuota Maks: {{ $d->kuota_bimbingan_maks }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Pembimbing 2 (Spesialis Kependidikan):</label>
                        <select name="pembimbing_2_id" class="form-control" required>
                            @foreach($dosens as $d)
                                <option value="{{ $d->id }}">{{ $d->name }} (Bidang: {{ $d->bidang_keahlian ?? 'Kependidikan' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nomor SK Dekan:</label>
                        <input type="text" name="nomor_sk_pembimbing" class="form-control" value="SK/{{ date('Y') }}/FKIP-UNS" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Penetapan SK:</label>
                        <input type="date" name="tanggal_sk_pembimbing" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <button type="submit" class="btn btn-success">Tetapkan 2 Pembimbing (Cek Kuota)</button>
                </form>
            </div>
            @else
            <div class="box">
                <div class="box-title">Status Pembimbing 1 & 2</div>
                @forelse($pengajuans as $p)
                    <div style="padding: 14px 0; border-bottom: 1px solid #f1f5f9;">
                        <div style="font-size:13px; font-weight:700; color:#1e293b; margin-bottom:8px;">
                            {{ $p->mahasiswa->name ?? '-' }} <span style="font-weight:400; color:#64748b;">({{ $p->mahasiswa->identifier ?? '-' }})</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; padding:6px 0; font-size:13.5px;">
                            <span style="color:#64748b; font-weight:600;">Pembimbing 1</span>
                            @if($p->pembimbing1)
                                <span style="color:#166534; font-weight:600;">{{ $p->pembimbing1->name }}</span>
                            @else
                                <span class="badge badge-yellow">Belum Ditentukan</span>
                            @endif
                        </div>
                        <div style="display:flex; justify-content:space-between; padding:6px 0; font-size:13.5px;">
                            <span style="color:#64748b; font-weight:600;">Pembimbing 2</span>
                            @if($p->pembimbing2)
                                <span style="color:#166534; font-weight:600;">{{ $p->pembimbing2->name }}</span>
                            @else
                                <span class="badge badge-yellow">Belum Ditentukan</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p style="color:#64748b; font-size:13.5px;">Belum ada data pengajuan tesis.</p>
                @endforelse
            </div>
            @endif
        </div>
    </div>

    <!-- TAB 3: FR-02 Logbook Bimbingan -->
    {{-- TAB: FR-02 Logbook Bimbingan — sementara dihapus/disembunyikan
    <div id="tab-logbook" class="tab-content">
        <div class="box">
            <div class="box-title">Pencatatan & Digital Approval Logbook Bimbingan (FR-02)</div>
            <p style="color: #64748b; font-size: 13.5px;">Mahasiswa mencatat progres substansi riset secara berkala, dosen pembimbing melakukan persetujuan daring berstempel QR Signature.</p>
            <table>
                <thead>
                    <tr>
                        <th>Mahasiswa / Tesis</th>
                        <th>Dosen Pembimbing</th>
                        <th>Tanggal & Catatan Bimbingan</th>
                        <th>Status Approval</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuans as $p)
                        @foreach($p->logbooks as $log)
                        <tr>
                            <td>{{ $p->mahasiswa->name }}</td>
                            <td>{{ $log->dosen->name }}</td>
                            <td><strong>{{ $log->tanggal_bimbingan }}</strong><br>{{ $log->materi_bimbingan }}</td>
                            <td>
                                @if($log->status_approval === 'approved')
                                    <span class="badge badge-green">Approved (QR Verified)</span>
                                @else
                                    <form action="{{ route('logbook.approve', $log->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">ACC Dosen</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    --}}

    <!-- TAB 4: Pendaftaran Sidang H-14 -->
    <div id="tab-pendaftaran" class="tab-content">
        <div class="box">
            <div class="box-title">Pendaftaran Sidang Minimal H-14 (FR-03, FR-05, FR-07)</div>
            <table>
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Tahap Berjalan</th>
                        <th>Form Pendaftaran Sidang (Batas Minimal H+14 Hari)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuans as $p)
                    <tr>
                        <td><strong>{{ $p->mahasiswa->name }}</strong><br>{{ $p->mahasiswa->identifier }}</td>
                        <td><span class="badge badge-blue">{{ $p->status_tahap }}</span></td>
                        <td>
                            @if(Auth::check() && Auth::user()->id === $p->mahasiswa_id)
                                @if($p->status_tahap === 'tahap_1_bimbingan')
                                    @if($p->pembimbing_1_id && $p->pembimbing_2_id)
                                        <a href="{{ route('sempro.create', $p->id) }}" class="btn btn-sm btn-primary">Daftar Sempro (FR-03) &ndash; Unggah FPT-TI-01</a>
                                    @else
                                        <span style="color:#94a3b8; font-size:12px;">Menunggu Pembimbing 1 & 2 ditetapkan Komisi Tesis</span>
                                    @endif
                                @elseif($p->status_tahap === 'tahap_2_sempro' && $p->pendaftaranSempro?->status_verifikasi_admin === 'rejected')
                                    <div>
                                        <div style="color:#dc2626; font-size:12px; margin-bottom:6px;">Pendaftaran Sempro sebelumnya ditolak Admin/Komisi Tesis.</div>
                                        <a href="{{ route('sempro.create', $p->id) }}" class="btn btn-sm btn-primary">Daftar Ulang Sempro &ndash; Unggah FPT-TI-01</a>
                                    </div>
                                @elseif($p->status_tahap === 'tahap_2_sempro')
                                    <span style="color:#94a3b8; font-size:12px;">Sedang menjalani Seminar Proposal &mdash; menunggu sidang, rekap nilai, revisi &amp; pengesahan Kaprodi selesai</span>
                                @elseif($p->status_tahap === 'tahap_3_semhas' && !$p->pendaftaranSemhas)
                                    <a href="{{ route('semhas.create', $p->id) }}" class="btn btn-sm btn-primary">Daftar Semhas (FR-05) &ndash; Unggah Permohonan Seminar Hasil</a>
                                @elseif($p->status_tahap === 'tahap_3_semhas' && $p->pendaftaranSemhas?->status_verifikasi_admin === 'rejected')
                                    <div>
                                        <div style="color:#dc2626; font-size:12px; margin-bottom:6px;">Pendaftaran Semhas sebelumnya ditolak Admin/Komisi Tesis.</div>
                                        <a href="{{ route('semhas.create', $p->id) }}" class="btn btn-sm btn-primary">Daftar Ulang Semhas</a>
                                    </div>
                                @elseif($p->status_tahap === 'tahap_3_semhas')
                                    <span style="color:#94a3b8; font-size:12px;">Sedang menjalani Seminar Hasil &mdash; menunggu sidang, rekap nilai, revisi &amp; pengesahan Kaprodi selesai</span>
                                @elseif($p->status_tahap === 'tahap_4_ujian')
                                    <form action="{{ route('ujian.store', $p->id) }}" method="POST" style="display:flex; gap:8px; align-items:center;">
                                        @csrf
                                        <input type="date" name="jadwal_usulan_sidang" class="form-control" style="width:160px;" required value="{{ date('Y-m-d', strtotime('+15 days')) }}">
                                        <input type="hidden" name="naskah_tesis_lengkap_url" value="/docs/tesis.pdf">
                                        <input type="hidden" name="artikel_jurnal_url" value="/docs/jurnal.pdf">
                                        <input type="hidden" name="prosiding_seminar_url" value="/docs/prosiding.pdf">
                                        <input type="hidden" name="sertifikat_bahasa_url" value="/docs/toefl.pdf">
                                        <input type="hidden" name="skor_bahasa" value="500">
                                        <input type="hidden" name="bukti_spp_terakhir_url" value="/docs/spp.pdf">
                                        <input type="hidden" name="khs_kumulatif_url" value="/docs/khs.pdf">
                                        <input type="hidden" name="surat_bebas_plagiasi_url" value="/docs/turnitin.pdf">
                                        <input type="hidden" name="similarity_score" value="18.5">
                                        <button type="submit" class="btn btn-sm btn-success">Daftar Ujian Tesis (FR-07)</button>
                                    </form>
                                @else
                                    <span style="color:#166534; font-size:12px;">Pendaftaran Sidang Selesai</span>
                                @endif
                            @else
                                {{-- Selain mahasiswa yang bersangkutan (dosen/komisi tesis/kaprodi/admin
                                     prodi), tombol pendaftaran disembunyikan — pendaftaran sidang murni
                                     tindakan mahasiswa sendiri, admin cuma memverifikasi (lihat tabel
                                     Verifikasi di bawah). --}}
                                @if($p->status_tahap === 'tahap_1_bimbingan')
                                    <span style="color:#94a3b8; font-size:12px;">Menunggu mahasiswa mendaftar Sempro</span>
                                @elseif($p->status_tahap === 'tahap_2_sempro' && $p->pendaftaranSempro?->status_verifikasi_admin === 'rejected')
                                    <span style="color:#94a3b8; font-size:12px;">Menunggu mahasiswa mendaftar ulang Sempro (ditolak sebelumnya)</span>
                                @elseif($p->status_tahap === 'tahap_2_sempro')
                                    <span style="color:#94a3b8; font-size:12px;">Sedang menjalani Seminar Proposal</span>
                                @elseif($p->status_tahap === 'tahap_3_semhas' && !$p->pendaftaranSemhas)
                                    <span style="color:#94a3b8; font-size:12px;">Menunggu mahasiswa mendaftar Semhas</span>
                                @elseif($p->status_tahap === 'tahap_3_semhas' && $p->pendaftaranSemhas?->status_verifikasi_admin === 'rejected')
                                    <span style="color:#94a3b8; font-size:12px;">Menunggu mahasiswa mendaftar ulang Semhas (ditolak sebelumnya)</span>
                                @elseif($p->status_tahap === 'tahap_3_semhas')
                                    <span style="color:#94a3b8; font-size:12px;">Sedang menjalani Seminar Hasil</span>
                                @elseif($p->status_tahap === 'tahap_4_ujian')
                                    <span style="color:#94a3b8; font-size:12px;">Menunggu mahasiswa mendaftar Ujian Tesis</span>
                                @else
                                    <span style="color:#166534; font-size:12px;">Pendaftaran Sidang Selesai</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(Auth::check() && in_array(Auth::user()->role, ['komisi_tesis', 'kaprodi', 'admin_prodi']))
        <div class="box">
            <div class="box-title">Verifikasi Pendaftaran Sempro</div>
            <p style="color:#64748b; font-size:13.5px;">Setujui atau tolak pendaftaran Seminar Proposal sebelum Form Permohonan (FPT-TI-01) bisa diunduh mahasiswa.</p>
            <table>
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Jadwal Usulan</th>
                        <th>Form FPT-TI-01</th>
                        <th>Status Verifikasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                        @if($p->pendaftaranSempro && $p->pendaftaranSempro->status_verifikasi_admin !== 'rejected')
                        <tr>
                            <td><strong>{{ $p->mahasiswa->name }}</strong><br>{{ $p->mahasiswa->identifier }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->pendaftaranSempro->jadwal_usulan_sidang)->translatedFormat('d F Y, H:i') }}</td>
                            <td>
                                @if($p->pendaftaranSempro->form_fpt_ti_01_url)
                                    <a href="{{ $p->pendaftaranSempro->form_fpt_ti_01_url }}" target="_blank" class="btn btn-sm btn-primary">Lihat PDF</a>
                                @else
                                    <span style="color:#dc2626; font-size:12px;">Belum diunggah</span>
                                @endif
                            </td>
                            <td>
                                @if($p->pendaftaranSempro->status_verifikasi_admin === 'verified')
                                    <span class="badge badge-green">Disetujui</span>
                                @elseif($p->pendaftaranSempro->status_verifikasi_admin === 'rejected')
                                    <span class="badge badge-red" style="background:#fee2e2; color:#991b1b;">Ditolak</span>
                                @else
                                    <span class="badge badge-yellow">Menunggu Verifikasi</span>
                                @endif
                            </td>
                            <td>
                                @if($p->pendaftaranSempro->status_verifikasi_admin === 'pending')
                                    <form action="{{ route('sempro.verifikasi', $p->pendaftaranSempro->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="status_verifikasi_admin" value="verified">
                                        <button type="submit" class="btn btn-sm btn-success">Setujui</button>
                                    </form>
                                    <form action="{{ route('sempro.verifikasi', $p->pendaftaranSempro->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="status_verifikasi_admin" value="rejected">
                                        <button type="submit" class="btn btn-sm btn-danger">Tolak</button>
                                    </form>
                                @else
                                    <span style="color:#94a3b8; font-size:12px;">Selesai</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif

        <div class="box">
            <div class="box-title">Unduh Dokumen Sempro (PDF)</div>
            <p style="color:#64748b; font-size:13.5px;">Form Permohonan tersedia begitu pendaftaran disetujui. Surat Tugas & Undangan Penguji tersedia setelah Komisi Tesis melakukan plotting jadwal & dewan penguji.</p>
            <table>
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Form Permohonan (FPT-TI-01)</th>
                        <th>Surat Tugas</th>
                        <th>Undangan Penguji</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                        @if($p->pendaftaranSempro)
                        <tr>
                            <td><strong>{{ $p->mahasiswa->name }}</strong><br>{{ $p->mahasiswa->identifier }}</td>
                            <td>
                                @if($p->pendaftaranSempro->status_verifikasi_admin === 'verified')
                                    <a href="{{ $p->pendaftaranSempro->form_fpt_ti_01_url }}" class="btn btn-sm btn-primary" target="_blank">Unduh PDF</a>
                                @else
                                    <span style="color:#94a3b8; font-size:12px;">Belum disetujui</span>
                                @endif
                            </td>
                            @php
                                $sidangSempro = $p->aktivitasSidangs->firstWhere('tahap_sidang', 'sempro');
                                $pengujiLengkap = $sidangSempro && $sidangSempro->pengujiSidangs->count() >= 3;
                            @endphp
                            <td>
                                @if($pengujiLengkap)
                                    <a href="{{ route('dokumen.cetak', ['kode' => 'SURAT-TUGAS-SEMPRO', 'id' => $sidangSempro->id]) }}" class="btn btn-sm btn-primary" target="_blank">Unduh PDF</a>
                                @else
                                    <span style="color:#94a3b8; font-size:12px;">Menunggu plotting</span>
                                @endif
                            </td>
                            <td>
                                @if($pengujiLengkap)
                                    <a href="{{ route('dokumen.cetak', ['kode' => 'UNDANGAN-SEMPRO', 'id' => $sidangSempro->id]) }}" class="btn btn-sm btn-primary" target="_blank">Unduh PDF</a>
                                @else
                                    <span style="color:#94a3b8; font-size:12px;">Menunggu plotting</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(Auth::check() && in_array(Auth::user()->role, ['komisi_tesis', 'kaprodi', 'admin_prodi']))
        <div class="box">
            <div class="box-title">Verifikasi Pendaftaran Semhas</div>
            <p style="color:#64748b; font-size:13.5px;">Setujui atau tolak pendaftaran Seminar Hasil sebelum Permohonan Seminar Hasil Riset dan Karya Publikasi bisa diunduh mahasiswa.</p>
            <table>
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Jadwal Usulan</th>
                        <th>Permohonan Semhas</th>
                        <th>Status Verifikasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                        @if($p->pendaftaranSemhas && $p->pendaftaranSemhas->status_verifikasi_admin !== 'rejected')
                        <tr>
                            <td><strong>{{ $p->mahasiswa->name }}</strong><br>{{ $p->mahasiswa->identifier }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->pendaftaranSemhas->jadwal_usulan_sidang)->translatedFormat('d F Y, H:i') }}</td>
                            <td>
                                @if($p->pendaftaranSemhas->form_fpt_sh_01_url)
                                    <a href="{{ $p->pendaftaranSemhas->form_fpt_sh_01_url }}" target="_blank" class="btn btn-sm btn-primary">Lihat PDF</a>
                                @else
                                    <span style="color:#dc2626; font-size:12px;">Belum diunggah</span>
                                @endif
                            </td>
                            <td>
                                @if($p->pendaftaranSemhas->status_verifikasi_admin === 'verified')
                                    <span class="badge badge-green">Disetujui</span>
                                @elseif($p->pendaftaranSemhas->status_verifikasi_admin === 'rejected')
                                    <span class="badge badge-red" style="background:#fee2e2; color:#991b1b;">Ditolak</span>
                                @else
                                    <span class="badge badge-yellow">Menunggu Verifikasi</span>
                                @endif
                            </td>
                            <td>
                                @if($p->pendaftaranSemhas->status_verifikasi_admin === 'pending')
                                    <form action="{{ route('semhas.verifikasi', $p->pendaftaranSemhas->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="status_verifikasi_admin" value="verified">
                                        <button type="submit" class="btn btn-sm btn-success">Setujui</button>
                                    </form>
                                    <form action="{{ route('semhas.verifikasi', $p->pendaftaranSemhas->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="status_verifikasi_admin" value="rejected">
                                        <button type="submit" class="btn btn-sm btn-danger">Tolak</button>
                                    </form>
                                @else
                                    <span style="color:#94a3b8; font-size:12px;">Selesai</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif

        <div class="box">
            <div class="box-title">Unduh Dokumen Semhas (PDF)</div>
            <p style="color:#64748b; font-size:13.5px;">Form Permohonan tersedia begitu pendaftaran disetujui.</p>
            <table>
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Permohonan Seminar Hasil Riset dan Karya Publikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                        @if($p->pendaftaranSemhas)
                        <tr>
                            <td><strong>{{ $p->mahasiswa->name }}</strong><br>{{ $p->mahasiswa->identifier }}</td>
                            <td>
                                @if($p->pendaftaranSemhas->status_verifikasi_admin === 'verified')
                                    <a href="{{ $p->pendaftaranSemhas->form_fpt_sh_01_url }}" class="btn btn-sm btn-primary" target="_blank">Unduh PDF</a>
                                @else
                                    <span style="color:#94a3b8; font-size:12px;">Belum disetujui</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 5: Plotting Komisi Tesis -->
    <div id="tab-sidang" class="tab-content">
        <div class="box">
            <div class="box-title">Plotting Dewan Penguji & Deteksi Bentrok Jadwal (FR-04, FR-06, FR-08)</div>
            <table>
                <thead>
                    <tr>
                        <th>Tahap Sidang</th>
                        <th>Mahasiswa</th>
                        <th>Jadwal & Ruangan</th>
                        <th>Dewan Penguji Terplotting</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sidangs as $s)
                    <tr>
                        <td><strong>{{ strtoupper($s->tahap_sidang) }}</strong></td>
                        <td>{{ $s->pengajuanTesis->mahasiswa->name ?? '-' }}</td>
                        <td>{{ $s->waktu_mulai }} s.d {{ $s->waktu_selesai }}<br><small>Ruang: {{ $s->ruangan ?? 'Zoom Cloud' }}</small></td>
                        <td>
                            @foreach($s->pengujiSidangs as $ps)
                                • {{ $ps->dosen->name }} (<em>{{ $ps->peran_penguji }}</em>)<br>
                            @endforeach
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align: center; color: #64748b;">Belum ada plotting jadwal sidang yang aktif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 6: Penilaian Rubrik Digital & BAP -->
    <div id="tab-penilaian" class="tab-content">
        <div class="box">
            <div class="box-title">Penilaian Rubrik 4 Dimensi & Rekapitulasi BAP (FR-09)</div>
            <table>
                <thead>
                    <tr>
                        <th>Sidang</th>
                        <th>Penguji</th>
                        <th>Nilai 4 Dimensi (Naskah, Publikasi, Presentasi, Q&A)</th>
                        <th>Aksi Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sidangs as $s)
                        @foreach($s->pengujiSidangs as $ps)
                        <tr>
                            <td>{{ strtoupper($s->tahap_sidang) }} - {{ $s->pengajuanTesis->mahasiswa->name }}</td>
                            <td>{{ $ps->dosen->name }}</td>
                            <td>Total Angka: <strong>{{ $ps->nilai_total_angka ?? 'Belum Dinilai' }}</strong></td>
                            <td>
                                <form action="{{ route('sidang.submitNilai', $s->id) }}" method="POST" style="display:flex; gap:6px;">
                                    @csrf
                                    <input type="hidden" name="dosen_id" value="{{ $ps->dosen_id }}">
                                    <input type="number" name="nilai_dimensi_1_naskah" value="85" style="width:50px;" class="form-control">
                                    <input type="number" name="nilai_dimensi_2_publikasi" value="85" style="width:50px;" class="form-control">
                                    <input type="number" name="nilai_dimensi_3_presentasi" value="85" style="width:50px;" class="form-control">
                                    <input type="number" name="nilai_dimensi_4_tanyajawab" value="85" style="width:50px;" class="form-control">
                                    <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 7: Matriks Revisi & Yudisium -->
    <div id="tab-revisi" class="tab-content">
        <div class="box">
            <div class="box-title">Matriks Revisi Komparatif & Pengesahan Kaprodi (FR-10)</div>
            <p style="color: #64748b; font-size: 13.5px;">Wajib memenuhi persetujuan seluruh dewan penguji sebelum tombol Pengesahan Kaprodi membuka status yudisium/kelulusan.</p>
        </div>
    </div>

    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-tab').forEach(el => el.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            event.target.classList.add('active');
        }

        function updateAlokasiAction(pengajuanId) {
            if(pengajuanId) {
                document.getElementById('form-alokasi').action = '/pengajuan/' + pengajuanId + '/alokasi-pembimbing';
            }
        }

        function selectPengajuanForAlokasi(pengajuanId) {
            const select = document.getElementById('select-pengajuan');
            select.value = pengajuanId;
            updateAlokasiAction(pengajuanId);
            document.getElementById('form-alokasi').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    </script>
</body>
</html>
