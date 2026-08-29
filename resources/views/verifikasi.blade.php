<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen - SIM-TESIS FKIP UNS</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; margin: 0; padding: 30px 15px; }
        .card { max-width: 480px; margin: 0 auto; background: white; border-radius: 10px; padding: 28px; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
        .badge { display: inline-block; padding: 6px 14px; border-radius: 20px; font-weight: bold; font-size: 13px; margin-bottom: 16px; }
        .badge.valid { background: #dcfce7; color: #166534; }
        .badge.invalid { background: #fee2e2; color: #991b1b; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        .row b { color: #64748b; font-weight: 600; }
        h1 { font-size: 17px; margin: 0 0 4px 0; }
        p.sub { color: #64748b; font-size: 13px; margin: 0 0 18px 0; }
    </style>
</head>
<body>
<div class="card">
    <h1>Verifikasi Dokumen</h1>
    <p class="sub">SIM-TESIS FKIP UNS &mdash; Sistem Informasi Manajemen Tesis</p>

    @if($valid)
        <div class="badge valid">&#10003; Dokumen Sah &amp; Terverifikasi</div>
        <div class="row"><span><b>Kode Dokumen</b></span><span>{{ $dokumen->kode_dokumen }}</span></div>
        @if($dokumen->nomor_dokumen)
        <div class="row"><span><b>Nomor</b></span><span>{{ $dokumen->nomor_dokumen }}</span></div>
        @endif
        <div class="row"><span><b>Dicetak Pada</b></span><span>{{ $dokumen->dicetak_at->translatedFormat('d F Y, H:i') }} WIB</span></div>
        @if($dokumen->dicetakOleh)
        <div class="row"><span><b>Dicetak Oleh</b></span><span>{{ $dokumen->dicetakOleh->name }}</span></div>
        @endif
        <div class="row"><span><b>Hash Verifikasi</b></span><span style="font-size:11px; word-break:break-all;">{{ $dokumen->hash_verifikasi }}</span></div>
    @else
        <div class="badge invalid">&#10007; Dokumen Tidak Ditemukan</div>
        <p style="font-size: 13px; color: #555;">
            Kode verifikasi ini tidak cocok dengan catatan sistem SIM-TESIS.
            Dokumen mungkin tidak sah, sudah dicabut, atau tautan/QR yang dipindai keliru.
        </p>
    @endif
</div>
</body>
</html>
