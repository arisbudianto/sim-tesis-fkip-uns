<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Seminar Hasil - SIM-TESIS FKIP UNS</title>
    <style>
        :root { --primary: #1e3a8a; --accent: #2563eb; --bg: #f8fafc; --text: #0f172a; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: var(--bg); margin: 0; padding: 20px; color: var(--text); }
        .wrap { max-width: 720px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); color: white; padding: 22px 26px; border-radius: 12px; margin-bottom: 20px; }
        .header h1 { margin: 0 0 4px 0; font-size: 20px; }
        .header p { margin: 0; font-size: 13.5px; opacity: .9; }
        .box { background: white; padding: 24px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.06); margin-bottom: 20px; }
        .info-row { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid #f1f5f9; font-size: 13.5px; }
        .info-row b { color: #64748b; font-weight: 600; }
        .alert { padding: 14px 16px; border-radius: 8px; font-size: 13.5px; margin-bottom: 18px; }
        .alert-block { background: #fee2e2; color: #991b1b; }
        .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #334155; }
        .form-control { width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; }
        .hint { font-size: 11.5px; color: #64748b; margin-top: 4px; }
        .btn { padding: 10px 20px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; background: var(--accent); color: white; }
        .btn-back { display: inline-block; margin-bottom: 14px; color: #64748b; text-decoration: none; font-size: 13.5px; }
    </style>
</head>
<body>
<div class="wrap">
    <a href="{{ route('dashboard') }}" class="btn-back">&larr; Kembali ke Dashboard</a>

    <div class="header">
        <h1>Pendaftaran Seminar Hasil (Semhas)</h1>
        <p>FR-05 &mdash; Wajib diajukan minimal H-14 sebelum tanggal sidang</p>
    </div>

    <div class="box">
        <div class="info-row"><span><b>Nama Mahasiswa</b></span><span>{{ $tesis->mahasiswa->name }}</span></div>
        <div class="info-row"><span><b>NIM</b></span><span>{{ $tesis->mahasiswa->identifier }}</span></div>
        <div class="info-row"><span><b>Judul Tesis</b></span><span>{{ $tesis->judul_tesis }}</span></div>
        <div class="info-row"><span><b>Pembimbing 1</b></span><span>{{ $tesis->pembimbing1->name ?? '(belum ditetapkan)' }}</span></div>
        <div class="info-row"><span><b>Pembimbing 2</b></span><span>{{ $tesis->pembimbing2->name ?? '(belum ditetapkan)' }}</span></div>
    </div>

    @if($blockReason)
        <div class="alert alert-block">&#9888; {{ $blockReason }}</div>
    @else
        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <form action="{{ route('semhas.store', $tesis->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="box">
                <div class="form-group">
                    <label for="jadwal_usulan_sidang">Tanggal Usulan Sidang <span style="color:#dc2626">*</span></label>
                    <input type="date" id="jadwal_usulan_sidang" name="jadwal_usulan_sidang" class="form-control" required>
                    <div class="hint">Wajib minimal 14 hari dari hari ini ({{ now()->addDays(14)->translatedFormat('d F Y') }} atau setelahnya).</div>
                </div>

                <div class="form-group">
                    <label for="form_fpt_sh_01">Permohonan Seminar Hasil Riset dan Karya Publikasi &ndash; sudah ditandatangani Pembimbing Utama (PDF, maks. 1MB) <span style="color:#dc2626">*</span></label>
                    <input type="file" id="form_fpt_sh_01" name="form_fpt_sh_01" class="form-control" accept=".pdf" required>
                    <div class="hint">Berkas ini yang akan ditinjau Admin Prodi/Komisi Tesis/Kaprodi sebelum menyetujui pendaftaran Anda.</div>
                </div>

                <div class="form-group">
                    <label for="naskah_bab_1_5">Naskah Bab 1&ndash;5 (PDF, maks. 35MB) <span style="color:#dc2626">*</span></label>
                    <input type="file" id="naskah_bab_1_5" name="naskah_bab_1_5" class="form-control" accept=".pdf" required>
                </div>

                <div class="form-group">
                    <label for="draf_artikel_ilmiah">Draf Artikel Ilmiah &mdash; minimal 2 berkas (PDF, maks. 20MB/berkas) <span style="color:#dc2626">*</span></label>
                    <input type="file" id="draf_artikel_ilmiah" name="draf_artikel_ilmiah[]" class="form-control" accept=".pdf" multiple required>
                    <div class="hint">Pilih minimal 2 file sekaligus (tahan Ctrl/Cmd saat memilih).</div>
                </div>

                <div class="form-group">
                    <label for="bukti_status_under_review">Bukti Status Under Review Jurnal/Prosiding (PDF/JPG/PNG, maks. 10MB) <span style="color:#dc2626">*</span></label>
                    <input type="file" id="bukti_status_under_review" name="bukti_status_under_review" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>

                <div class="form-group">
                    <label for="bukti_spp">Bukti Pembayaran SPP (PDF/JPG/PNG, maks. 10MB) <span style="color:#dc2626">*</span></label>
                    <input type="file" id="bukti_spp" name="bukti_spp" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
            </div>

            <button type="submit" class="btn">Ajukan Pendaftaran Semhas</button>
        </form>
    @endif
</div>
</body>
</html>
