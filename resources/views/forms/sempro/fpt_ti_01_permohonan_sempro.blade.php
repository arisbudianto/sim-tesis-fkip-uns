<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FPT-TI-01 - Permohonan Ujian Tesis 1</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; padding: 20px; }
        .header-doc { text-align: right; font-weight: bold; margin-bottom: 20px; }
        .title { text-align: center; font-weight: bold; font-size: 13pt; margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        td { vertical-align: top; padding: 4px; }
        .sign-table { margin-top: 40px; width: 100%; }
        .qr-box { text-align: center; width: 45%; }
    </style>
</head>
<body>
    <div class="header-doc">FPT-TI-01</div>
    <div class="title">
        PERMOHONAN UJIAN TESIS 1<br>(SEMINAR DAN UJIAN PROPOSAL)
    </div>

    <p>Kepada Yth. Kepala Program Studi S2 Pendidikan Guru Vokasi<br>
    Pascasarjana Fakultas Keguruan dan Ilmu Pendidikan<br>
    Universitas Sebelas Maret<br>
    Surakarta</p>

    <p>Proposal Tesis 1 (Seminar dan Ujian Proposal) dengan Judul :<br>
    <strong>{{ $pendaftaran->judul_proposal }}</strong></p>

    <p>Disusun oleh :</p>
    <table>
        <tr><td width="25%">Nama</td><td width="3%">:</td><td>{{ $pendaftaran->tesis->mahasiswa->name }}</td></tr>
        <tr><td>NIM</td><td>:</td><td>{{ $pendaftaran->tesis->mahasiswa->mahasiswaProfile->nim }}</td></tr>
        <tr><td>Program Studi</td><td>:</td><td>{{ $pendaftaran->tesis->mahasiswa->mahasiswaProfile->program_studi }}</td></tr>
    </table>

    <p>telah memenuhi syarat untuk dilanjutkan ke tahap seminar proposal tesis.<br>
    Berdasarkan kesepakatan dengan Tim pembimbing, seminar kami usulkan pada :</p>
    <table>
        <tr><td width="25%">Hari, tanggal</td><td width="3%">:</td><td>{{ \Carbon\Carbon::parse($pendaftaran->usulan_tanggal)->translatedFormat('l, d F Y') }}</td></tr>
        <tr><td>Pukul</td><td>:</td><td>{{ $pendaftaran->usulan_waktu }} WIB</td></tr>
        <tr><td>Tempat / Media</td><td>:</td><td>{{ $pendaftaran->usulan_tempat }} (Zoom ID: {{ $pendaftaran->zoom_meeting_id ?? '-' }})</td></tr>
    </table>

    <p>Demikian permohonan kami sampaikan. Atas perhatian dan kebijaksanaanya, kami ucapkan terima kasih.</p>

    <table class="sign-table">
        <tr>
            <td class="qr-box">
                Mengetahui,<br><strong>Pembimbing Utama</strong><br><br>
                @if($pendaftaran->approval_pembimbing_1)
                    <img src="data:image/png;base64,{{ $qrPembimbing1 }}" width="90"><br>
                    <strong>{{ $pendaftaran->tesis->pembimbing1->name }}</strong><br>
                    NIP. {{ $pendaftaran->tesis->pembimbing1->dosenProfile->nip }}
                @else
                    <br><br><br>( .................................................... )
                @endif
            </td>
            <td class="qr-box">
                Surakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                <strong>Mahasiswa,</strong><br><br>
                <img src="data:image/png;base64,{{ $qrMahasiswa }}" width="90"><br>
                <strong>{{ $pendaftaran->tesis->mahasiswa->name }}</strong><br>
                NIM. {{ $pendaftaran->tesis->mahasiswa->mahasiswaProfile->nim }}
            </td>
        </tr>
    </table>
</body>
</html>
