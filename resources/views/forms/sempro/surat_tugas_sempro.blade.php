<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Tugas Ujian Tesis 1</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.4; padding: 15px; }
        .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 15px; }
        .title { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; margin-bottom: 15px; }
        table.border { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.border th, table.border td { border: 1px solid #000; padding: 5px; text-align: left; }
        table.border th { background-color: #f2f2f2; text-align: center; }
    </style>
</head>
<body>
    <div class="kop">
        <strong>KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</strong><br>
        UNIVERSITAS SEBELAS MARET - FAKULTAS KEGURUAN DAN ILMU PENDIDIKAN<br>
        <small>Jalan Ir. Sutami 36A Surakarta 57126 | fkip@fkip.uns.ac.id | fkip.uns.ac.id</small>
    </div>

    <div class="title">SURAT TUGAS</div>
    <div style="text-align: center; margin-top: -10px; margin-bottom: 15px;">Nomor : {{ $sidang->nomor_surat_tugas_dekan }}</div>

    <p>Dekan Fakultas Keguruan dan Ilmu Pendidikan, menugaskan Dosen tersebut di bawah ini :</p>
    <table class="border">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th>Nama dan NIP</th>
                <th width="20%">Pangkat Gol./Ruang</th>
                <th width="25%">Jabatan dalam Tim</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sidang->penguji as $idx => $p)
            <tr>
                <td align="center">{{ $idx + 1 }}</td>
                <td><strong>{{ $p->dosen->name }}</strong><br>NIP. {{ $p->dosen->dosenProfile->nip }}</td>
                <td>{{ $p->dosen->dosenProfile->pangkat_golongan ?? '-' }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $p->jabatan_tim)) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 15px;">
        <tr><td width="15%">Acara</td><td width="3%">:</td><td>Ujian Tesis 1 (Seminar dan Ujian Proposal)</td></tr>
        <tr><td>Tempat</td><td width="3%">:</td><td>{{ $sidang->ruangan_atau_link }}</td></tr>
        <tr><td>Waktu</td><td width="3%">:</td><td>{{ \Carbon\Carbon::parse($sidang->jadwal_definitif)->translatedFormat('l, d F Y | H:i') }} WIB</td></tr>
        <tr><td>Tugas</td><td width="3%">:</td><td>Menguji Tesis 1 Mahasiswa: <strong>{{ $sidang->pendaftaran->tesis->mahasiswa->name }}</strong> (NIM. {{ $sidang->pendaftaran->tesis->mahasiswa->mahasiswaProfile->nim }})</td></tr>
    </table>

    <p>Harap dilaksanakan sebaik-baiknya, dan menyampaikan laporan setelah selesai melaksanakan tugas.</p>

    <table style="width: 100%; margin-top: 25px;">
        <tr>
            <td width="60%"></td>
            <td align="center">
                Surakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                a.n Dekan<br>Wakil Dekan Bidang Akademik dan Penelitian<br><br>
                <img src="data:image/png;base64,{{ $qrWadek }}" width="80"><br>
                <strong>Prof. Dr.paed. Nurma Yunita Indriyanti, M.Si., M.Sc.</strong><br>
                NIP. 198306262006042002
            </td>
        </tr>
    </table>
</body>
</html>
