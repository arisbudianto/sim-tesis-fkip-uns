<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FPT-TI-03 - Penilaian Ujian Tesis 1</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; padding: 15px; }
        .header-doc { text-align: right; font-weight: bold; }
        .title { text-align: center; font-weight: bold; font-size: 12pt; margin-bottom: 15px; }
        table.border { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10.5pt; }
        table.border th, table.border td { border: 1px solid #000; padding: 4px; }
    </style>
</head>
<body>
    <div class="header-doc">FPT-TI-03</div>
    <div class="title">PENILAIAN UJIAN TESIS 1 (SEMINAR DAN UJIAN PROPOSAL)</div>

    <table style="width: 100%; margin-bottom: 10px;">
        <tr><td width="20%">Nama</td><td width="3%">:</td><td>{{ $penilaian->penguji->sidangSempro->pendaftaran->tesis->mahasiswa->name }}</td></tr>
        <tr><td>NIM</td><td>:</td><td>{{ $penilaian->penguji->sidangSempro->pendaftaran->tesis->mahasiswa->mahasiswaProfile->nim }}</td></tr>
        <tr><td>Judul Proposal</td><td>:</td><td><strong>{{ $penilaian->penguji->sidangSempro->pendaftaran->judul_proposal }}</strong></td></tr>
    </table>

    <table class="border">
        <thead>
            <tr bgcolor="#eee">
                <th width="5%">No</th>
                <th width="30%">Aspek Penilaian</th>
                <th>Uraian</th>
                <th width="15%">Nilai (0-100)</th>
            </tr>
        </thead>
        <tbody>
            <tr><td rowspan="7" align="center">I.</td><td rowspan="7"><strong>Kualitas Rencana Penelitian</strong></td><td>Bahasa, ketepatan, dan kejelasan redaksi</td><td align="center">{{ $penilaian->skor_bahasa }}</td></tr>
            <tr><td>Sistematika dan format penulisan</td><td align="center">{{ $penilaian->skor_sistematika }}</td></tr>
            <tr><td>Perumusan masalah dan tujuan penelitian</td><td align="center">{{ $penilaian->skor_rumusan_masalah }}</td></tr>
            <tr><td>Kedalaman kajian teori</td><td align="center">{{ $penilaian->skor_kajian_teori }}</td></tr>
            <tr><td>Metodologi penelitian</td><td align="center">{{ $penilaian->skor_metodologi }}</td></tr>
            <tr><td>Keaslian dan kebaruan penelitian</td><td align="center">{{ $penilaian->skor_kebaruan }}</td></tr>
            <tr><td>Kemanfaatan penelitian</td><td align="center">{{ $penilaian->skor_kemanfaatan }}</td></tr>
            <tr><td rowspan="3" align="center">II.</td><td rowspan="3"><strong>Kualitas Presentasi</strong></td><td>Efektivitas presentasi proposal</td><td align="center">{{ $penilaian->skor_presentasi }}</td></tr>
            <tr><td>Kemampuan menangkap dan menjawab pertanyaan</td><td align="center">{{ $penilaian->skor_tanya_jawab }}</td></tr>
            <tr><td>Kedalaman dan keluasan wawasan keilmuan</td><td align="center">{{ $penilaian->skor_wawasan }}</td></tr>
            <tr bgcolor="#f9f9f9">
                <td colspan="3" align="right"><strong>Nilai Akhir = Jumlah Nilai / 10</strong></td>
                <td align="center"><strong>{{ $penilaian->nilai_akhir_individu }}</strong></td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 25px;">
        <tr>
            <td width="60%"></td>
            <td align="center">
                Surakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Penguji,<br><br>
                <img src="data:image/png;base64,{{ $qrPenguji }}" width="80"><br>
                <strong>{{ $penilaian->penguji->dosen->name }}</strong><br>
                NIP. {{ $penilaian->penguji->dosen->dosenProfile->nip }}
            </td>
        </tr>
    </table>
</body>
</html>
