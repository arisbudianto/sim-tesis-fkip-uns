<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Persetujuan Revisi Tesis Menuju Wisuda</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.4; padding: 15px; }
        .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 15px; }
        .title { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; margin-bottom: 15px; }
        table.border { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.border th, table.border td { border: 1px solid #000; padding: 5px; }
    </style>
</head>
<body>
    <div class="kop">
        <strong>UNIVERSITAS SEBELAS MARET - FAKULTAS KEGURUAN DAN ILMU PENDIDIKAN</strong><br>
        Program Studi Magister Pendidikan Guru Vokasi<br>
        <small>Kampus V UNS Pabelan, Jl. A. Yani No. 200 A, Pabelan, Kartasura, Sukoharjo 57161</small>
    </div>

    <div class="title">PERSETUJUAN REVISI TESIS</div>

    <p>Yang bertandatangan di bawah ini Tim Penguji Tesis menerangkan bahwa mahasiswa :</p>
    <table>
        <tr><td width="25%">Nama</td><td width="3%">:</td><td>{{ $revisi->sidangUjian->pendaftaran->tesis->mahasiswa->name }}</td></tr>
        <tr><td>NIM</td><td>:</td><td>{{ $revisi->sidangUjian->pendaftaran->tesis->mahasiswa->mahasiswaProfile->nim }}</td></tr>
        <tr><td>Program Studi</td><td>:</td><td>{{ $revisi->sidangUjian->pendaftaran->tesis->mahasiswa->mahasiswaProfile->program_studi }}</td></tr>
        <tr><td>Judul Tesis</td><td>:</td><td><strong>{{ $revisi->judul_tesis_final }}</strong></td></tr>
    </table>

    <p>telah merevisi tesis sesuai masukan Tim Penguji dengan uraian sebagai berikut :</p>

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
            @foreach($revisi->sidangUjian->penguji as $idx => $p)
            <tr>
                <td align="center">{{ $idx + 1 }}</td>
                <td>{{ $p->dosen->name }}<br><small>NIP. {{ $p->dosen->dosenProfile->nip }}</small></td>
                <td>Sesuai catatan lembar evaluasi sidang ujian tesis.</td>
                <td>Telah disempurnakan pada naskah tesis final dan publikasi jurnal.</td>
                <td align="center"><strong>[ APPROVED ]</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p>Demikian pernyataan/persetujuan ini dibuat untuk dapat digunakan sebagai syarat kelulusan yudisium dan pendaftaran wisuda.</p>

    <table style="width: 100%; margin-top: 35px;">
        <tr>
            <td width="50%" align="center">
                Mengetahui,<br>Ketua Program Studi<br><br><br>
                <strong>Abdul Haris Setiawan, S.Pd., M.Pd., Ph.D.</strong><br>
                NIP. 198003242005011002
            </td>
            <td width="50%" align="center">
                Surakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Ketua Penguji,<br><br><br>
                <strong>{{ $revisi->sidangUjian->penguji->where('jabatan_tim', 'ketua_penguji')->first()->dosen->name ?? '-' }}</strong>
            </td>
        </tr>
    </table>
</body>
</html>
