<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FPT-TI-06 - Rekomendasi Ujian Tesis 1</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.4; padding: 15px; }
        .header-doc { text-align: right; font-weight: bold; }
        .title { text-align: center; font-weight: bold; font-size: 12pt; margin-bottom: 15px; }
        table.border { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.border th, table.border td { border: 1px solid #000; padding: 5px; }
    </style>
</head>
<body>
    <div class="header-doc">FPT-TI-06</div>
    <div class="title">REKOMENDASI UJIAN TESIS 1 (SEMINAR DAN UJIAN PROPOSAL)</div>

    <p>Pada hari ini, tanggal {{ \Carbon\Carbon::parse($sidang->jadwal_definitif)->translatedFormat('d F Y') }} telah dilaksanakan Seminar dan Ujian Proposal Tesis mahasiswa :</p>
    <table>
        <tr><td width="25%">Nama</td><td width="3%">:</td><td>{{ $sidang->pendaftaran->tesis->mahasiswa->name }}</td></tr>
        <tr><td>NIM</td><td>:</td><td>{{ $sidang->pendaftaran->tesis->mahasiswa->mahasiswaProfile->nim }}</td></tr>
        <tr><td>Program Studi</td><td>:</td><td>{{ $sidang->pendaftaran->tesis->mahasiswa->mahasiswaProfile->program_studi }}</td></tr>
        <tr><td>Judul Proposal</td><td>:</td><td><strong>{{ $sidang->pendaftaran->judul_proposal }}</strong></td></tr>
    </table>

    <p>Hasil penilaian (keputusan rapat Tim penilai):</p>
    <div style="border: 1px solid #000; padding: 10px; font-weight: bold;">
        [ {{ $bap->rekomendasi == 'lulus_tanpa_revisi' ? 'X' : ' ' }} ] Lulus dan dapat dilanjutkan ke tahap pelaksanaan penelitian tanpa revisi.<br>
        [ {{ $bap->rekomendasi == 'lulus_dengan_revisi' ? 'X' : ' ' }} ] Lulus dan dapat dilanjutkan ke tahap penelitian dengan revisi dan catatan terlampir.<br>
        [ {{ $bap->rekomendasi == 'tidak_lulus' ? 'X' : ' ' }} ] Tidak lulus, memerlukan perbaikan naskah proposal tesis dengan catatan terlampir.
    </div>

    <p>Nilai Rata-rata: <strong>{{ $bap->nilai_rata_rata }} ({{ $bap->grade_huruf }})</strong> | Batas Waktu Revisi: <strong>{{ $bap->deadline_revisi_bulan }} Bulan</strong></p>

    <table class="border" style="margin-top: 20px;">
        <thead>
            <tr bgcolor="#eee">
                <th>No</th>
                <th>Nama Penguji</th>
                <th>Jabatan Tim</th>
                <th>Tanda Tangan / QR Valid</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sidang->penguji as $idx => $p)
            <tr>
                <td align="center">{{ $idx + 1 }}</td>
                <td>{{ $p->dosen->name }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $p->jabatan_tim)) }}</td>
                <td align="center"><small>[TTE Digital Valid]</small></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td width="50%" align="center">
                Mengetahui,<br>Kepala Program Studi<br><br><br>
                <strong>Abdul Haris Setiawan, S.Pd., M.Pd., Ph.D.</strong><br>
                NIP. 198003242005011002
            </td>
            <td width="50%" align="center">
                Surakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Ketua Tim Penguji<br><br><br>
                <strong>{{ $sidang->penguji->where('jabatan_tim', 'ketua_penguji')->first()->dosen->name ?? '-' }}</strong>
            </td>
        </tr>
    </table>
</body>
</html>
