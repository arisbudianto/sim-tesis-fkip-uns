<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengajuan Tesis - SIM-TESIS FKIP UNS</title>
    <style>
        :root { --primary: #1e3a8a; --accent: #2563eb; --bg: #f8fafc; --text: #0f172a; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: var(--bg); margin: 0; padding: 20px; color: var(--text); }
        .wrap { max-width: 640px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); color: white; padding: 22px 26px; border-radius: 12px; margin-bottom: 20px; }
        .header h1 { margin: 0 0 4px 0; font-size: 20px; }
        .header p { margin: 0; font-size: 13.5px; opacity: .9; }
        .box { background: white; padding: 24px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.06); margin-bottom: 20px; }
        .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; padding: 14px 16px; border-radius: 8px; font-size: 13.5px; margin-bottom: 18px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #334155; }
        .form-control { width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; font-family: inherit; }
        textarea.form-control { resize: vertical; min-height: 90px; }
        .btn { padding: 10px 20px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; background: var(--accent); color: white; }
        .btn-back { display: inline-block; margin-bottom: 14px; color: #64748b; text-decoration: none; font-size: 13.5px; }
    </style>
</head>
<body>
<div class="wrap">
    <a href="{{ route('dashboard') }}" class="btn-back">&larr; Kembali ke Dashboard</a>

    <div class="header">
        <h1>Edit Pengajuan Tesis</h1>
        <p>{{ $pengajuan->mahasiswa->name ?? '-' }} &middot; {{ $pengajuan->mahasiswa->identifier ?? '-' }}</p>
    </div>

    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    <form action="{{ route('pengajuan.update', $pengajuan->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="box">
            <div class="form-group">
                <label for="judul_tesis">Judul Tesis <span style="color:#dc2626">*</span></label>
                <input type="text" id="judul_tesis" name="judul_tesis" class="form-control" value="{{ old('judul_tesis', $pengajuan->judul_tesis) }}" required>
            </div>

            <div class="form-group">
                <label for="bidang_fokus">Bidang Fokus <span style="color:#dc2626">*</span></label>
                <input type="text" id="bidang_fokus" name="bidang_fokus" class="form-control" value="{{ old('bidang_fokus', $pengajuan->bidang_fokus) }}" required>
            </div>

            <div class="form-group">
                <label for="abstrak_rencana">Abstrak Rencana</label>
                <textarea id="abstrak_rencana" name="abstrak_rencana" class="form-control">{{ old('abstrak_rencana', $pengajuan->abstrak_rencana) }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn">Simpan Perubahan</button>
    </form>
</div>
</body>
</html>
