<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Permohonan Seminar Hasil Riset & Publikasi</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.4; padding: 15px; }
        .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { vertical-align: top; padding: 4px; }
    </style>
</head>
<body>
    <div class="kop">
        <strong>UNIVERSITAS SEBELAS MARET - FAKULTAS KEGURUAN DAN ILMU PENDIDIKAN</strong><br>
        Program Studi Magister Pendidikan Guru Vokasi<br>
        <small>Kampus V UNS Pabelan, Jl. A. Yani No. 200 A, Pabelan, Kartasura, Sukoharjo 57161</small>
    </div>

    <p>Hal : Permohonan Seminar Hasil Riset dan Karya Publikasi</p>
    <p>Yth. Ketua Program Studi Magister Pendidikan Guru Vokasi FKIP UNS</p>

    <p>Tesis dengan Judul :<br><strong>{{ $pendaftaran->judul_tesis }}</strong></p>

    <table>
        <tr><td width="25%">Nama</td><td width="3%">:</td><td>{{ $pendaftaran->tesis->mahasiswa->name }}</td></tr>
        <tr><td>NIM</td><td>:</td><td>{{ $pendaftaran->tesis->mahasiswa->mahasiswaProfile->nim }}</td></tr>
        <tr><td>Program Studi</td><td>:</td><td>{{ $pendaftaran->tesis->mahasiswa->mahasiswaProfile->program_studi }}</td></tr>
        <tr><td>Usulan Jadwal</td><td>:</td><td>{{ \Carbon\Carbon::parse($pendaftaran->usulan_tanggal)->translatedFormat('l, d F Y') }} | Pukul {{ $pendaftaran->usulan_waktu }} WIB</td></tr>
        <tr><td>Tempat / Media</td><td>:</td><td>{{ $pendaftaran->usulan_tempat }} (Zoom ID: {{ $pendaftaran->zoom_meeting_id ?? '-' }})</td></tr>
    </table>

    <p>Terlampir persyaratan administrasi:</p>
    <ul>
        <li>Naskah tesis Bab I-V yang telah disetujui dosen pembimbing.</li>
        <li>Minimal telah tersusun 2 draft artikel ilmiah dan 1 artikel berstatus minimal under review.</li>
        <li>Bukti pembayaran biaya semester terakhir & KHS.</li>
        <li>Buku bimbingan tesis & Persetujuan revisi Sempro.</li>
    </ul>

    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td width="50%" align="center">
                Mengetahui,<br>Pembimbing Utama<br><br><br>
                <strong>{{ $pendaftaran->tesis->pembimbing1->name }}</strong><br>
                NIP. {{ $pendaftaran->tesis->pembimbing1->dosenProfile->nip }}
            </td>
            <td width="50%" align="center">
                Surakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Mahasiswa,<br><br><br>
                <strong>{{ $pendaftaran->tesis->mahasiswa->name }}</strong><br>
                NIM. {{ $pendaftaran->tesis->mahasiswa->mahasiswaProfile->nim }}
            </td>
        </tr>
    </table>
</body>
</html>
