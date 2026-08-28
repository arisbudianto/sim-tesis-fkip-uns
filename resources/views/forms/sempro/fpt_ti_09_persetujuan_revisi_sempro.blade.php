<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FPT-TI-09 - Persetujuan Revisi Ujian Tesis 1</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.4; padding: 15px; }
        .header-doc { text-align: right; font-weight: bold; }
        .title { text-align: center; font-weight: bold; font-size: 12pt; margin-bottom: 15px; }
        table.border { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.border th, table.border td { border: 1px solid #000; padding: 5px; }
    </style>
</head>
<body>
    <div class="header-doc">FPT-TI-09</div>
    <div class="title">PERSETUJUAN REVISI TESIS 1<br>(SEMINAR DAN UJIAN PROPOSAL)</div>

    <p>Yang bertandatangan di bawah ini Tim Penguji Tesis 1 menerangkan bahwa mahasiswa :</p>
    <table>
        <tr><td width="25%">Nama</td><td width="3%">:</td><td>{{ $revisi->sidangSempro->pendaftaran->tesis->mahasiswa->name }}</td></tr>
        <tr><td>NIM</td><td>:</td><td>{{ $revisi->sidangSempro->pendaftaran->tesis->mahasiswa->mahasiswaProfile->nim }}</td></tr>
        <tr><td>Program Studi</td><td>:</td><td>{{ $revisi->sidangSempro->pendaftaran->tesis->mahasiswa->mahasiswaProfile->program_studi }}</td></tr>
        <tr><td>Judul Tesis</td><td>:</td><td><strong>{{ $revisi->sidangSempro->pendaftaran->judul_proposal }}</strong></td></tr>
        <tr><td>Judul Publikasi</td><td>:</td><td>{{ $revisi->judul_naskah_publikasi }}</td></tr>
    </table>

    <p>telah merevisi draft tesis dan naskah publikasi sesuai masukan Tim Penguji dengan uraian sebagai berikut :</p>

    <table class="border">
        <thead>
            <tr bgcolor="#eee">
                <th width="5%">No</th>
                <th width="25%">Nama Penguji</th>
                <th>Permintaan Revisi</th>
                <th>Hasil Revisi</th>
                <th width="15%">Status Approval</th>
            </tr>
        </thead>
        <tbody>
            @foreach($revisi->sidangSempro->penguji as $idx => $p)
            <tr>
                <td align="center">{{ $idx + 1 }}</td>
                <td>{{ $p->dosen->name }}<br><small>NIP. {{ $p->dosen->dosenProfile->nip }}</small></td>
                <td>Sesuai catatan lembar evaluasi sidang.</td>
                <td>Telah diperbaiki pada naskah tesis Bab I-III dan draf artikel publikasi.</td>
                <td align="center"><strong>[ APPROVED ]</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 35px;">
        <tr>
            <td width="50%" align="center">
                Mengetahui,<br>Ketua Program Studi<br><br><br>
                <strong>Abdul Haris Setiawan, S.Pd., M.Pd., Ph.D.</strong><br>
                NIP. 198003242005011002
            </td>
            <td width="50%" align="center">
                Surakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Ketua Penguji,<br><br><br>
                <strong>{{ $revisi->sidangSempro->penguji->where('jabatan_tim', 'ketua_penguji')->first()->dosen->name ?? '-' }}</strong>
            </td>
        </tr>
    </table>
</body>
</html>
