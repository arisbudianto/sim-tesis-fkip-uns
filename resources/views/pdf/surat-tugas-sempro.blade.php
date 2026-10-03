@extends('pdf.layout')

@php
    // CATATAN PENTING: sejak Sempro bisa punya 3 penguji (Sekretaris Penguji
    // opsional — lihat KomisiTesisController@createSidangWithPenguji),
    // $penguji->get('sekretaris_penguji') BISA null untuk sidang Sempro.
    // Versi sebelumnya dari file ini mengakses ->dosen->name langsung tanpa
    // operator null-safe (?->) pada SETIAP baris termasuk baris Sekretaris
    // yang kosong itu — PHP memunculkan warning "Attempt to read property
    // on null" yang di beberapa konfigurasi PHP ikut tercetak ke output
    // HTML dan membuat dompdf gagal merender sisa dokumen (PDF jadi kosong
    // setelah kop+judul, persis seperti dilaporkan). Perbaikan di bawah:
    // (1) semua akses properti dosen pakai ?-> (null-safe), (2) baris
    // Sekretaris Penguji otomatis DILEWATI kalau memang belum diisi untuk
    // Sempro, bukan ditampilkan sebagai baris kosong bertitik-titik.
    $sidang = $record; // AktivitasSidang (tahap_sidang = sempro ATAU semhas)
    $tesis = $sidang->pengajuanTesis;

    $labelAcara = $sidang->tahap_sidang === 'semhas'
        ? 'Ujian Tesis 2 (Seminar Hasil)'
        : 'Ujian Tesis 1 (Seminar dan Ujian Proposal)';

    $penguji = $sidang->pengujiSidangs->keyBy('peran_penguji');
    $baris = collect([
        ['peran' => 'Ketua Penguji', 'ket' => '', 'p' => $penguji->get('ketua_penguji')],
        ['peran' => 'Sekretaris Penguji', 'ket' => '', 'p' => $penguji->get('sekretaris_penguji')],
        ['peran' => 'Anggota 1', 'ket' => 'Pembimbing I', 'p' => $penguji->get('pembimbing_1')],
        ['peran' => 'Anggota 2', 'ket' => 'Pembimbing II', 'p' => $penguji->get('pembimbing_2')],
    ])
        // Sekretaris Penguji yang kosong pada Sempro memang opsional —
        // lewati barisnya saja. Untuk peran lain yang seharusnya selalu
        // terisi, baris tetap ditampilkan dengan placeholder titik-titik
        // (tanda data belum lengkap) alih-alih disembunyikan diam-diam.
        ->reject(fn ($b) => $b['peran'] === 'Sekretaris Penguji' && !$b['p'])
        ->values();
@endphp

@section('konten')
<p style="text-align:center; font-weight:bold; text-decoration:underline; font-size:12.5px;">SURAT TUGAS</p>
<p style="text-align:center; margin-top:-6px;">Nomor : {{ $nomorDokumen ?? '................................' }}</p>

<p style="margin-top:16px;">Dekan Fakultas Keguruan dan Ilmu Pendidikan, menugaskan Dosen tersebut di bawah ini :</p>

<table class="content-table">
<tr>
    <th style="width:6%;">No.</th>
    <th>Nama dan NIP</th>
    <th style="width:18%;">Pangkat Gol./Ruang</th>
    <th style="width:16%;">Jabatan dalam tim</th>
    <th style="width:16%;">Keterangan</th>
</tr>
@foreach($baris as $i => $b)
<tr>
    <td>{{ $i + 1 }}</td>
    <td>
        {{ $b['p']?->dosen?->name ?? '..........................' }}<br>
        NIP. {{ $b['p']?->dosen?->identifier ?? '..........................' }}
    </td>
    <td>{{ $b['p']?->dosen?->pangkat_golongan ?? '-' }}</td>
    <td>{{ $b['peran'] }}</td>
    <td>{{ $b['ket'] }}</td>
</tr>
@endforeach
</table>

<table class="content-table" style="margin-top:14px;">
<tr><th style="width:15%;">Acara</th><td>{{ $labelAcara }}</td></tr>
<tr><th>Tempat</th><td>{{ $sidang->ruangan ?? $sidang->link_zoom ?? '-' }}</td></tr>
<tr><th>Tanggal</th><td>{{ $sidang->waktu_mulai?->translatedFormat('l, d F Y') ?? '-' }}</td></tr>
<tr><th>Waktu</th><td>{{ $sidang->waktu_mulai?->format('H:i') ?? '-' }} &ndash; {{ $sidang->waktu_selesai?->format('H:i') ?? '-' }} WIB</td></tr>
<tr>
    <th>Tugas</th>
    <td>
        Menguji {{ $labelAcara }} Fakultas Keguruan dan Ilmu Pendidikan Universitas Sebelas Maret yakni :<br><br>
        Nama : {{ $tesis?->mahasiswa?->name ?? '-' }}<br>
        NIM : {{ $tesis?->mahasiswa?->identifier ?? '-' }}<br>
        Prodi : S2 Pendidikan Guru Vokasi<br>
        Judul Tesis : {{ $tesis?->judul_tesis ?? '-' }}
    </td>
</tr>
</table>

<p style="margin-top:16px;">Harap dilaksanakan sebaik-baiknya, dan menyampaikan laporan setelah selesai melaksanakan tugas.</p>

<table style="margin-top:20px;">
<tr>
    <td style="width:55%;">&nbsp;</td>
    <td style="width:45%; vertical-align:top;">
        Surakarta, {{ $dicetakAt->translatedFormat('d F Y') }}<br>
        a.n Dekan<br>
        Wakil Dekan Bidang Akademik dan Penelitian
        <div style="height:88px;"></div>
        <span style="border-top:1px solid #333; display:inline-block; padding-top:3px;">Prof. Dr.paed. Nurma Yunita Indriyanti, S.Pd., M.Si., M.Sc.</span><br>
        NIP. 198306262006042002
    </td>
</tr>
</table>

<p style="margin-top:20px; font-size:10px;">
    Tembusan :<br>
    1. Yth. Dekan<br>
    2. Yth. Ketua Program Studi<br>
    3. Ybs<br>
    4. Arsip
</p>

<p style="font-size:10px;">
    Catatan :<br>
    1. Mohon hadir tepat waktu<br>
    2. Surat tugas ini sekaligus sebagai undangan
</p>
@endsection
