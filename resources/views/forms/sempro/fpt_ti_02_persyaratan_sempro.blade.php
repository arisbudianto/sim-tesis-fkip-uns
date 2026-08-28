<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FPT-TI-02 - Persyaratan Administrasi Sempro</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; padding: 20px; }
        .header-doc { text-align: right; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { vertical-align: top; padding: 4px; }
        .checklist { margin-top: 15px; margin-left: 20px; }
        .note { font-size: 10pt; font-style: italic; margin-top: 30px; border-top: 1px solid #999; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header-doc">FPT-TI-02</div>
    <p>Hal : Persyaratan Administrasi Ujian Tesis 1 (Seminar dan Ujian Proposal)</p>
    <p>Kepada Yth. Kepala Program Studi S2 Pendidikan Guru Vokasi<br>
    Pascasarjana Fakultas Keguruan dan Ilmu Pendidikan<br>Universitas Sebelas Maret Surakarta</p>

    <p>Bersama ini kami :</p>
    <table>
        <tr><td width="25%">Nama</td><td width="3%">:</td><td>{{ $pendaftaran->tesis->mahasiswa->name }}</td></tr>
        <tr><td>NIM</td><td>:</td><td>{{ $pendaftaran->tesis->mahasiswa->mahasiswaProfile->nim }}</td></tr>
        <tr><td>Program Studi</td><td>:</td><td>{{ $pendaftaran->tesis->mahasiswa->mahasiswaProfile->program_studi }}</td></tr>
        <tr><td>Pembimbing</td><td>:</td><td>1. {{ $pendaftaran->tesis->pembimbing1->name }}<br>2. {{ $pendaftaran->tesis->pembimbing2->name }}</td></tr>
        <tr><td>Judul Proposal</td><td>:</td><td><strong>{{ $pendaftaran->judul_proposal }}</strong></td></tr>
    </table>

    <p>Telah memenuhi persyaratan administrasi untuk menempuh ke tahap Ujian Tesis 1 (Seminar dan Ujian Proposal). Terkait hal itu, kami lampirkan persyaratan administrasi yaitu :</p>
    <div class="checklist">
        [✓] 1. Bukti pembayaran SPP semester I sampai semester terakhir.<br>
        [✓] 2. Kartu Hasil Studi (KHS).<br>
        [✓] 3. Logbook bimbingan tesis.<br>
        [✓] 4. Proposal tesis beserta instrumen penelitian yang sudah disetujui Tim Pembimbing.
    </div>

    <p>Demikian persyaratan administrasi ini kami sampaikan. Atas perhatiannya diucapkan terima kasih.</p>

    <table style="margin-top: 35px;">
        <tr>
            <td width="50%" align="center">
                Mengetahui,<br>Pembimbing Utama<br><br>
                <img src="data:image/png;base64,{{ $qrPembimbing1 }}" width="80"><br>
                <strong>{{ $pendaftaran->tesis->pembimbing1->name }}</strong><br>
                NIP. {{ $pendaftaran->tesis->pembimbing1->dosenProfile->nip }}
            </td>
            <td width="50%" align="center">
                Surakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Mahasiswa,<br><br>
                <img src="data:image/png;base64,{{ $qrMahasiswa }}" width="80"><br>
                <strong>{{ $pendaftaran->tesis->mahasiswa->name }}</strong><br>
                NIM. {{ $pendaftaran->tesis->mahasiswa->mahasiswaProfile->nim }}
            </td>
        </tr>
    </table>
    <div class="note">Catatan: Pemrosesan berkas seminar dan Ujian Proposal membutuhkan waktu minimal 2 minggu dari saat memasukkan persyaratan.</div>
</body>
</html>
