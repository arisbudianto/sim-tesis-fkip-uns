<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Permohonan Ujian Tesis</title>
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

    <p>Hal : Permohonan Ujian Tesis (Sidang Akhir)</p>
    <p>Yth. Ketua Program Studi Magister Pendidikan Guru Vokasi FKIP UNS</p>

    <p>Tesis dengan Judul :<br><strong>{{ $pendaftaran->judul_tesis }}</strong></p>

    <table>
        <tr><td width="25%">Nama</td><td width="3%">:</td><td>{{ $pendaftaran->tesis->mahasiswa->name }}</td></tr>
        <tr><td>NIM</td><td>:</td><td>{{ $pendaftaran->tesis->mahasiswa->mahasiswaProfile->nim }}</td></tr>
        <tr><td>Program Studi</td><td>:</td><td>{{ $pendaftaran->tesis->mahasiswa->mahasiswaProfile->program_studi }}</td></tr>
        <tr><td>Usulan Jadwal</td><td>:</td><td>{{ \Carbon\Carbon::parse($pendaftaran->usulan_tanggal)->translatedFormat('l, d F Y') }} | Pukul {{ $pendaftaran->usulan_waktu }} WIB</td></tr>
        <tr><td>Tempat / Media</td><td>:</td><td>{{ $pendaftaran->usulan_tempat }} (Zoom ID: {{ $pendaftaran->zoom_meeting_id ?? '-' }})</td></tr>
    </table>

    <p>Terlampir 9 persyaratan administrasi kelayakan:</p>
    <ol>
        <li>Naskah tesis lengkap yang sudah disetujui dosen pembimbing.</li>
        <li>Bukti lulus penilaian seminar hasil dan karya publikasi.</li>
        <li>Bukti publikasi artikel di jurnal Sinta 1/2 atau internasional.</li>
        <li>Bukti seminar internasional, prosiding, dan sertifikat pemakalah.</li>
        <li>Bukti kemampuan bahasa Inggris (EAP >= 65 / TOEFL >= 475): <strong>Skor {{ $pendaftaran->skor_bahasa }} ({{ $pendaftaran->jenis_bahasa }})</strong>.</li>
        <li>Bukti pembayaran SPP semester terakhir.</li>
        <li>Buku bimbingan tesis & KHS Kumulatif.</li>
        <li>Surat bebas plagiasi Turnitin: <strong>Similarity {{ $pendaftaran->persentase_similarity }}% (Maks. 25%)</strong>.</li>
    </ol>

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
